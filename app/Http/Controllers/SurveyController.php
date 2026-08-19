<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\Value;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SurveyController extends Controller
{
    /**
     * عرض استبيان للإجابة
     */
    public function show(Survey $survey)
    {
        $user = Auth::user();

        // التحقق من أن الاستبيان نشط
        if (! $survey->isActive()) {
            return redirect()->back()->with('error', 'هذا الاستبيان غير متاح حالياً');
        }

        // التحقق من أن المستخدم مستهدف
        // نراعي كلا التنسيقين: role مباشر ('teacher') أو target_type ('teachers')
        $targetType = \App\Models\Survey::roleToTargetType($user->role);
        $isTargeted = in_array($user->role, $survey->target_roles ?? [])
                   || ($targetType && in_array($targetType, $survey->target_roles ?? []));

        if (! $isTargeted) {
            return redirect()->back()->with('error', 'هذا الاستبيان غير موجّه لك');
        }

        // التحقق من أن المستخدم لم يجب بعد
        if ($survey->hasUserResponded($user->id)) {
            return redirect()->back()->with('info', 'لقد أجبت على هذا الاستبيان مسبقاً');
        }

        $survey->load('questions');

        return view('surveys.show', compact('survey'));
    }

    /**
     * حفظ إجابات الاستبيان
     */
    public function submit(Request $request, Survey $survey)
    {
        $user = Auth::user(); // قد تكون null للاستبيانات العامة (guest)

        // نوع الاستجابة: JSON للنافذة المنبثقة (ajax)، وredirect للصفحة المستقلة (رابط/QR) — Issue 18
        $wantsJson = $request->expectsJson() || $request->routeIs('survey.ajax-submit');
        $fail = function (string $msg, int $code) use ($wantsJson) {
            return $wantsJson
                ? response()->json(['error' => $msg], $code)
                : back()->withInput()->with('error', $msg);
        };

        // إذا الاستبيان يتطلب تسجيل دخول والمستخدم غير مسجل → رفض
        if (($survey->requires_login ?? true) && ! $user) {
            return $fail('يجب تسجيل الدخول للإجابة على هذا الاستبيان', 401);
        }

        // التحقق من أن الاستبيان نشط
        if (! $survey->isActive()) {
            return $fail('هذا الاستبيان غير متاح حالياً', 400);
        }

        // بوّابة الاستهداف (كـshow): مستخدمٌ مسجَّل لا يُجيب استبياناً غير موجّه لدوره — كان submit
        // يتحقّق من النشاط فقط فيلوّث الطالبُ تقييماتِ القيمة/استبيانات أدوار أخرى. (الاستبيانات
        // العامّة بلا target_roles تبقى مفتوحة للضيوف/الجميع.)
        if ($user && ! empty($survey->target_roles)) {
            $targetType = \App\Models\Survey::roleToTargetType($user->role);
            $isTargeted = in_array($user->role, $survey->target_roles, true)
                || ($targetType && in_array($targetType, $survey->target_roles, true));
            if (! $isTargeted) {
                return $fail('هذا الاستبيان غير موجّه لك', 403);
            }
        }

        // بوّابة عزل المدارس (§4) — كانت غائبة تماماً: الاستهداف يفحص الدور لا المدرسة، وربط
        // المسار غير منطوق، فطالبُ مدرسةٍ يلوّث نتائج مدرسةٍ أخرى بنداء المسار مباشرةً. حجبُ
        // النافذة في المتصفّح لا يحمي شيئاً هنا (يُعطَّل JS أو يُنادى المسار من خارج المتصفّح).
        if ($user) {
            if ($survey->school_id && (int) $survey->school_id !== (int) $user->school_id) {
                return $fail('هذا الاستبيان غير متاح لمدرستك', 403);
            }

            // تقييمات القيمة/الدرس تُنشأ بـschool_id = NULL (Admin\SurveyController@store)، فعزلها
            // يمرّ عبر رؤية القيمة للمدرسة — نفس حارس عرض الدرس في StudentController::lesson.
            if ($survey->isAssessment()) {
                $valueId = $survey->value_id ?: optional(optional($survey->lesson)->concept)->value_id;
                if ($valueId && ! Value::visibleForSchool($user->school_id)->whereKey($valueId)->exists()) {
                    return $fail('هذا الاستبيان غير متاح لمدرستك', 403);
                }
            }
        }

        $survey->load('questions');

        // التحقق من الإجابات المطلوبة — نُرجِع **معرّفات** الأسئلة الناقصة لا رسالةً عامّة،
        // فيستطيع العميل تعليمها بالأحمر والانتقال إليها. رسالةٌ مبهمة تجعل الزرّ يبدو معطّلاً.
        $answers = $request->input('answers', []);
        $missing = [];
        foreach ($survey->questions as $question) {
            $value = $answers[$question->id] ?? null;
            $isEmpty = $value === null
                || (is_array($value) ? count($value) === 0 : trim((string) $value) === '');
            if ($question->is_required && $isEmpty) {
                $missing[] = (int) $question->id;
            }
        }

        if ($missing) {
            $msg = count($missing) === 1
                ? 'بقي سؤال واحد بلا إجابة'
                : 'بقيت ' . count($missing) . ' أسئلة بلا إجابة';

            return $wantsJson
                ? response()->json(['error' => $msg, 'missing_questions' => $missing], 422)
                : back()->withInput()->with('error', $msg);
        }

        // تنفيذ ذرّي: التحقق من duplicate + إنشاء response في معاملة واحدة
        // لمنع submit مزدوج عند الـ rapid double-click
        try {
            $duplicate = \Illuminate\Support\Facades\DB::transaction(function () use ($survey, $user, $answers) {
                if ($user) {
                    $exists = SurveyResponse::where('survey_id', $survey->id)
                        ->where('user_id', $user->id)
                        ->lockForUpdate()
                        ->exists();
                    if ($exists) {
                        return true; // duplicate
                    }
                }

                SurveyResponse::create([
                    'survey_id' => $survey->id,
                    'user_id' => $user?->id,
                    'answers' => $answers,
                    'completed_at' => now(),
                ]);

                return false;
            }, 3);
        } catch (\Throwable $e) {
            \Log::error('Survey submit failed', ['survey_id' => $survey->id, 'error' => $e->getMessage()]);

            return $fail('حدث خطأ أثناء حفظ الإجابات', 500);
        }

        if ($duplicate) {
            return $fail('لقد أجبت على هذا الاستبيان مسبقاً', 400);
        }

        // إزالة الاستبيان من الجلسة
        $pendingSurveys = session('pending_surveys', collect());
        $pendingSurveys = $pendingSurveys->filter(function ($s) use ($survey) {
            return $s->id !== $survey->id;
        });

        if ($pendingSurveys->isEmpty()) {
            session()->forget(['pending_surveys', 'show_survey_popup']);
        } else {
            session(['pending_surveys' => $pendingSurveys]);
        }

        if (! $wantsJson) {
            // إغلاق الاستبيان: انتقل مباشرةً للاستبيان المُعلَّق التالي (إن وُجد)، وإلّا لصفحة التعلّم.
            $next = $user ? Survey::getPendingSurveysForUser($user)->first() : null;
            if ($next && $next->id !== $survey->id) {
                return redirect()->route('survey.show', $next->id)
                    ->with('success', 'تم حفظ إجاباتك ✓ — إليك الاستبيان التالي');
            }

            // لا مزيد: الطالب ⟶ صفحة التعلّم، الأدوار الأخرى ⟶ لوحتها، الضيف ⟶ رجوع.
            if (! $user) {
                return redirect()->back()->with('success', 'شكراً لك! تم حفظ إجاباتك بنجاح');
            }

            // المتطلَّب #3: تقييمُ درسٍ يُعيد الطالب لدرسه ليُكمل، لا لصفحة التعلّم العامّة.
            $to = $this->returnUrlAfterSubmit($survey, $user, $request)
                ?? ($user->role === 'student' ? route('student.learn') : url('/dashboard'));

            return redirect($to)->with('success', 'شكراً لك! تم حفظ إجاباتك بنجاح');
        }

        return response()->json([
            'success' => true,
            'message' => 'شكراً لك! تم حفظ إجاباتك بنجاح',
            'has_more_surveys' => $pendingSurveys->isNotEmpty(),
        ]);
    }

    /**
     * رابط العودة بعد تعبئة تقييمٍ من الصفحة المستقلّة — درسُ الطالب ليُكمل من حيث وقف.
     *
     * §4: **يُمنع** قبول `return_to` أو أيّ رابط خام من العميل (إعادة توجيه مفتوحة). نقبل معرّفاً
     * فقط ثمّ نُعيد حلّه خادميّاً بالكامل: الدرس موجود ونشط، وقيمتُه مرئيّة لمدرسة الطالب — نفس
     * حارس StudentController::lesson. أيّ إخفاق ⟶ null فيسقط النداء للسلوك الافتراضيّ.
     */
    private function returnUrlAfterSubmit(Survey $survey, $user, Request $request): ?string
    {
        if ($user->role !== 'student') {
            return null;
        }

        $lessonId = $survey->lesson_id ?: (int) $request->input('return_lesson_id');
        if (! $lessonId) {
            return null;
        }

        $lesson = \App\Models\Lesson::with('concept')->find($lessonId);
        if (! $lesson || $lesson->status !== 'active') {
            return null;
        }

        $valueId = optional($lesson->concept)->value_id;
        if (! $valueId || ! Value::visibleForSchool($user->school_id)->whereKey($valueId)->exists()) {
            return null;
        }

        // تقييمُ قيمةٍ لا يعود إلى درسٍ من قيمة أخرى.
        if ($survey->value_id && (int) $survey->value_id !== (int) $valueId) {
            return null;
        }

        return route('student.lesson', $lesson->id);
    }

    /**
     * جلب الاستبيانات المعلقة للمستخدم (AJAX)
     */
    public function getPendingSurveys()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['surveys' => []]);
        }

        $pendingSurveys = Survey::getPendingSurveysForUser($user);

        return response()->json([
            'surveys' => $pendingSurveys,
            'has_pending' => $pendingSurveys->isNotEmpty(),
        ]);
    }
}
