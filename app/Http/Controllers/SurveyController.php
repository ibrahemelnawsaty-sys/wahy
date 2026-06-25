<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyResponse;
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

        $survey->load('questions');

        // التحقق من الإجابات المطلوبة
        $answers = $request->input('answers', []);
        foreach ($survey->questions as $question) {
            if ($question->is_required && empty($answers[$question->id])) {
                return $fail('يرجى الإجابة على جميع الأسئلة المطلوبة', 422);
            }
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
            return redirect()->back()->with('success', 'شكراً لك! تم حفظ إجاباتك بنجاح');
        }

        return response()->json([
            'success' => true,
            'message' => 'شكراً لك! تم حفظ إجاباتك بنجاح',
            'has_more_surveys' => $pendingSurveys->isNotEmpty(),
        ]);
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
