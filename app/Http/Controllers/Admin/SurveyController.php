<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\Value;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SurveyController extends Controller
{
    /**
     * عرض قائمة الاستبيانات
     */
    public function index(Request $request)
    {
        $query = Survey::with('creator', 'questions');

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // بحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $surveys = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.surveys.index', compact('surveys'));
    }

    /**
     * عرض صفحة إنشاء استبيان
     */
    public function create()
    {
        $lessons = Lesson::with('concept.value')->orderBy('title')->get();
        $values = Value::where('status', 'active')->orderBy('order')->get();

        return view('admin.surveys.create', compact('lessons', 'values'));
    }

    /**
     * حفظ استبيان جديد
     */
    public function store(Request $request)
    {
        $surveyType = $request->input('survey_type', 'general');
        $assessmentTarget = $request->input('assessment_target', 'lesson');

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_type' => 'required|array|min:1',
            'target_type.*' => 'in:schools,teachers,students,parents',
            'trigger_type' => 'required|in:on_platform_open,on_login,on_first_login,on_lesson_start,on_lesson_complete,on_activity_complete,manual',
            'requires_login' => 'boolean',
            'is_mandatory' => 'boolean',
            'is_popup' => 'boolean',
            'status' => 'required|in:draft,active,closed',
            'survey_type' => 'nullable|in:general,pre_post_assessment',
            'assessment_target' => 'nullable|in:lesson,value',
            'lesson_id' => 'nullable|exists:lessons,id',
            'value_id' => 'nullable|exists:values,id',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:500',
            'questions.*.question_type' => 'required|in:text,textarea,select,radio,checkbox,rating,scale',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*' => 'nullable|string|max:255',
            'questions.*.option_scores' => 'nullable|array',
            'questions.*.option_scores.*' => 'nullable|integer|min:0',
            'questions.*.is_required' => 'boolean',
            'questions.*.order' => 'nullable|integer|min:0',
        ];

        // إذا كان تقييم قبلي/بعدي، الهدف (درس أو قيمة) مطلوب والمستهدفون = الطلاب تلقائياً
        if ($surveyType === 'pre_post_assessment') {
            if ($assessmentTarget === 'value') {
                // الربط بقيمة: value_id مطلوب و lesson_id يبقى اختيارياً (null)
                $rules['value_id'] = 'required|exists:values,id';
                $rules['lesson_id'] = 'nullable|exists:lessons,id';
            } else {
                // الربط بدرس (الافتراضي): lesson_id مطلوب و value_id يبقى اختيارياً (null)
                $rules['lesson_id'] = 'required|exists:lessons,id';
                $rules['value_id'] = 'nullable|exists:values,id';
            }
            // تعيين المستهدفين تلقائياً إذا لم يتم إرسالها
            if (! $request->filled('target_type')) {
                $request->merge(['target_type' => ['students']]);
            }
        }

        $validated = $request->validate($rules, [
            'title.required' => 'عنوان الاستبيان مطلوب',
            'target_type.required' => 'يجب اختيار مستهدف واحد على الأقل',
            'target_type.min' => 'يجب اختيار مستهدف واحد على الأقل',
            'lesson_id.required' => 'يجب اختيار الدرس المرتبط بالتقييم',
            'value_id.required' => 'يجب اختيار القيمة المرتبطة بالتقييم',
            'questions.required' => 'يجب إضافة سؤال واحد على الأقل',
            'questions.min' => 'يجب إضافة سؤال واحد على الأقل',
            'questions.*.question_text.required' => 'نص السؤال مطلوب',
            'questions.*.question_type.required' => 'نوع السؤال مطلوب',
        ]);

        // التحقق من أن الأسئلة التي تحتاج خيارات لديها خيارات
        foreach ($validated['questions'] as $index => $question) {
            if (in_array($question['question_type'], ['select', 'radio', 'checkbox'])) {
                if (empty($question['options']) || count($question['options']) < 1) {
                    return back()->withErrors([
                        "questions.{$index}.options" => 'السؤال رقم ' . ($index + 1) . ' يحتاج إلى خيار واحد على الأقل',
                    ])->withInput();
                }

                $emptyOptions = array_filter($question['options'], function ($option) {
                    return empty(trim($option));
                });

                if (count($emptyOptions) > 0) {
                    return back()->withErrors([
                        "questions.{$index}.options" => 'السؤال رقم ' . ($index + 1) . ' يحتوي على خيارات فارغة',
                    ])->withInput();
                }
            }
        }

        DB::transaction(function () use ($validated, $surveyType, $assessmentTarget) {
            if ($surveyType === 'pre_post_assessment') {
                // تحديد عمود الهدف (درس أو قيمة) — متبادلان: واحد فقط غير-null
                $isValueTarget = $assessmentTarget === 'value';
                $lessonId = $isValueTarget ? null : ($validated['lesson_id'] ?? null);
                $valueId = $isValueTarget ? ($validated['value_id'] ?? null) : null;
                // مُشغِّلات السياق: القيمة (on_value_start/on_value_complete) أو الدرس (on_lesson_start/on_lesson_complete)
                $preTrigger = $isValueTarget ? 'on_value_start' : 'on_lesson_start';
                $postTrigger = $isValueTarget ? 'on_value_complete' : 'on_lesson_complete';

                // إنشاء استبيان قبلي
                $preSurvey = Survey::create([
                    'title' => $validated['title'] . ' (تقييم قبلي)',
                    'description' => $validated['description'] ?? null,
                    'target_roles' => $validated['target_type'],
                    'status' => $validated['status'],
                    'trigger_type' => $preTrigger,
                    'requires_login' => true,
                    'is_mandatory' => $validated['is_mandatory'] ?? true,
                    'is_popup' => $validated['is_popup'] ?? true,
                    'created_by' => Auth::id(),
                    'survey_type' => 'pre_post_assessment',
                    'lesson_id' => $lessonId,
                    'value_id' => $valueId,
                    'assessment_phase' => 'pre',
                ]);

                // إنشاء استبيان بعدي
                $postSurvey = Survey::create([
                    'title' => $validated['title'] . ' (تقييم بعدي)',
                    'description' => $validated['description'] ?? null,
                    'target_roles' => $validated['target_type'],
                    'status' => $validated['status'],
                    'trigger_type' => $postTrigger,
                    'requires_login' => true,
                    'is_mandatory' => $validated['is_mandatory'] ?? true,
                    'is_popup' => $validated['is_popup'] ?? true,
                    'created_by' => Auth::id(),
                    'survey_type' => 'pre_post_assessment',
                    'lesson_id' => $lessonId,
                    'value_id' => $valueId,
                    'assessment_phase' => 'post',
                    'linked_survey_id' => $preSurvey->id,
                ]);

                // ربط القبلي بالبعدي
                $preSurvey->update(['linked_survey_id' => $postSurvey->id]);

                // إنشاء نفس الأسئلة للاثنين
                foreach ($validated['questions'] as $index => $questionData) {
                    $questionPayload = [
                        'question_text' => $questionData['question_text'],
                        'question_type' => $questionData['question_type'],
                        'options' => $questionData['options'] ?? null,
                        'option_scores' => $questionData['option_scores'] ?? null,
                        'is_required' => $questionData['is_required'] ?? false,
                        'order' => $questionData['order'] ?? $index,
                    ];

                    SurveyQuestion::create(array_merge($questionPayload, ['survey_id' => $preSurvey->id]));
                    SurveyQuestion::create(array_merge($questionPayload, ['survey_id' => $postSurvey->id]));
                }
            } else {
                // استبيان عادي
                $survey = Survey::create([
                    'title' => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'target_roles' => $validated['target_type'],
                    'status' => $validated['status'],
                    'trigger_type' => $validated['trigger_type'],
                    'requires_login' => $validated['requires_login'] ?? true,
                    'is_mandatory' => $validated['is_mandatory'] ?? true,
                    'is_popup' => $validated['is_popup'] ?? true,
                    'created_by' => Auth::id(),
                    'survey_type' => 'general',
                ]);

                foreach ($validated['questions'] as $index => $questionData) {
                    SurveyQuestion::create([
                        'survey_id' => $survey->id,
                        'question_text' => $questionData['question_text'],
                        'question_type' => $questionData['question_type'],
                        'options' => $questionData['options'] ?? null,
                        'option_scores' => $questionData['option_scores'] ?? null,
                        'is_required' => $questionData['is_required'] ?? false,
                        'order' => $questionData['order'] ?? $index,
                    ]);
                }
            }
        });

        $msg = $surveyType === 'pre_post_assessment'
            ? 'تم إنشاء استبيان التقييم القبلي والبعدي بنجاح! ✅'
            : 'تم إنشاء الاستبيان بنجاح! ✅';

        return redirect()
            ->route('admin.surveys.index')
            ->with('success', $msg);
    }

    /**
     * عرض تفاصيل الاستبيان
     */
    public function show(Survey $survey)
    {
        $survey->load(['questions', 'responses.user', 'creator']);

        // إحصائيات
        $stats = [
            'total_responses' => $survey->responses()->distinct('user_id')->count(),
            'total_questions' => $survey->questions()->count(),
            'completion_rate' => 0,
        ];

        // رابط الاستبيان
        $surveyUrl = route('survey.show', ['survey' => $survey->id]);

        // QR Code - استخدام SVG بدلاً من PNG لتجنب مشكلة Imagick
        try {
            $qrCode = base64_encode(QrCode::format('svg')->size(200)->generate($surveyUrl));
            $qrCodeType = 'svg';
        } catch (\Exception $e) {
            // إذا فشل، استخدم API خارجي
            $qrCode = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($surveyUrl);
            $qrCodeType = 'url';
        }

        return view('admin.surveys.show', compact('survey', 'stats', 'surveyUrl', 'qrCode', 'qrCodeType'));
    }

    /**
     * عرض صفحة تعديل استبيان
     */
    public function edit(Survey $survey)
    {
        $survey->load('questions');

        return view('admin.surveys.edit', compact('survey'));
    }

    /**
     * تحديث استبيان
     */
    public function update(Request $request, Survey $survey)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'nullable|in:on_platform_open,on_login,on_first_login,on_lesson_start,on_lesson_complete,on_activity_complete,on_value_start,on_value_complete,manual',
            'requires_login' => 'boolean',
            'is_mandatory' => 'boolean',
            'is_popup' => 'boolean',
            'status' => 'required|in:draft,active,closed',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:500',
            'questions.*.question_type' => 'required|in:text,textarea,select,radio,checkbox,rating,scale',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*' => 'nullable|string|max:255',
            'questions.*.option_scores' => 'nullable|array',
            'questions.*.option_scores.*' => 'nullable|integer|min:0',
            'questions.*.is_required' => 'boolean',
            'questions.*.order' => 'nullable|integer|min:0',
        ];

        // التقييم القبلي/البعدي يستهدف الطلاب تلقائياً
        if ($survey->survey_type !== 'pre_post_assessment') {
            $rules['target_type'] = 'required|array|min:1';
            $rules['target_type.*'] = 'in:schools,teachers,students,parents';
        } else {
            $rules['target_type'] = 'nullable|array';
            $rules['target_type.*'] = 'in:schools,teachers,students,parents';
        }

        $validated = $request->validate($rules, [
            'title.required' => 'عنوان الاستبيان مطلوب',
            'target_type.required' => 'يجب اختيار مستهدف واحد على الأقل',
            'target_type.min' => 'يجب اختيار مستهدف واحد على الأقل',
            'questions.required' => 'يجب إضافة سؤال واحد على الأقل',
            'questions.min' => 'يجب إضافة سؤال واحد على الأقل',
            'questions.*.question_text.required' => 'نص السؤال مطلوب',
            'questions.*.question_type.required' => 'نوع السؤال مطلوب',
        ]);

        // التحقق من أن الأسئلة التي تحتاج خيارات لديها خيارات
        foreach ($validated['questions'] as $index => $question) {
            if (in_array($question['question_type'], ['select', 'radio', 'checkbox'])) {
                if (empty($question['options']) || count($question['options']) < 1) {
                    return back()->withErrors([
                        "questions.{$index}.options" => 'السؤال رقم ' . ($index + 1) . ' يحتاج إلى خيار واحد على الأقل',
                    ])->withInput();
                }

                // التحقق من أن الخيارات ليست فارغة
                $emptyOptions = array_filter($question['options'], function ($option) {
                    return empty(trim($option));
                });

                if (count($emptyOptions) > 0) {
                    return back()->withErrors([
                        "questions.{$index}.options" => 'السؤال رقم ' . ($index + 1) . ' يحتوي على خيارات فارغة',
                    ])->withInput();
                }
            }
        }

        // تحديد المستهدفين
        $targetRoles = $validated['target_type'] ?? ['students'];

        // استخراج قيم Boolean هنا (قبل الـ closure) لأن checkbox لا يرسل قيمة عند عدم التحديد
        $isMandatory = $request->boolean('is_mandatory');
        $isPopup = $request->boolean('is_popup');

        DB::transaction(function () use ($validated, $survey, $targetRoles, $isMandatory, $isPopup) {
            $survey->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'target_roles' => $targetRoles,
                // استبيانات التقييم تحتفظ بمُشغِّلها الأصلي (on_lesson_*/on_value_*) — لا نسمح لنموذج
                // التعديل بإفساده (كان يُعيده on_platform_open فيتحوّل لنافذة عامة إلزامية).
                'trigger_type' => $survey->isAssessment() ? $survey->trigger_type : ($validated['trigger_type'] ?? $survey->trigger_type),
                'requires_login' => $validated['requires_login'] ?? true,
                'is_mandatory' => $isMandatory,
                'is_popup' => $isPopup,
                'status' => $validated['status'],
            ]);

            // حذف الأسئلة القديمة
            $survey->questions()->delete();

            // إضافة الأسئلة الجديدة
            foreach ($validated['questions'] as $index => $questionData) {
                SurveyQuestion::create([
                    'survey_id' => $survey->id,
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'options' => $questionData['options'] ?? null,
                    'option_scores' => $questionData['option_scores'] ?? null,
                    'is_required' => $questionData['is_required'] ?? false,
                    'order' => $questionData['order'] ?? $index,
                ]);
            }
        });

        return redirect()
            ->route('admin.surveys.index')
            ->with('success', 'تم تحديث الاستبيان بنجاح! ✅');
    }

    /**
     * عرض إجابات الاستبيان
     */
    public function responses(Survey $survey)
    {
        try {
            $survey->load('questions');

            $responses = $survey->responses()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($item) {
                    return $item->user_id ?? 'guest_' . $item->id;
                });

            return view('admin.surveys.responses', compact('survey', 'responses'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Survey responses view failed', [
                'survey_id' => $survey->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('admin.surveys.index')
                ->with('error', 'تعذّر عرض إجابات الاستبيان');
        }
    }

    /**
     * حذف إجابات مستخدم محدد
     */
    public function deleteResponse(Survey $survey, $userId)
    {
        // إذا كان userId يبدأ بـ guest_، نحذف response واحد فقط
        if (str_starts_with($userId, 'guest_')) {
            $responseId = str_replace('guest_', '', $userId);
            $survey->responses()->where('id', $responseId)->delete();
        } else {
            // حذف جميع إجابات المستخدم
            $survey->responses()->where('user_id', $userId)->delete();
        }

        // التحقق من نوع الطلب
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الإجابات بنجاح! 🗑️',
            ]);
        }

        return back()->with('success', 'تم حذف الإجابات بنجاح! 🗑️');
    }

    /**
     * تصدير الإجابات إلى Excel
     */
    public function export(Survey $survey)
    {
        $responses = $survey->responses()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->user_id ?? 'guest_' . $item->id;
            });

        $questions = $survey->questions()->orderBy('order')->get();

        // إنشاء محتوى CSV
        $filename = 'survey_responses_' . $survey->id . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($responses, $questions) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // رأس الجدول
            $header = ['المستخدم', 'البريد الإلكتروني', 'التاريخ'];
            foreach ($questions as $question) {
                $header[] = $question->question_text;
            }
            fputcsv($file, $header);

            // البيانات — answers مخزنة كـ JSON: { question_id: value }
            foreach ($responses as $userId => $userResponses) {
                $firstResponse = $userResponses->first();
                $user = $firstResponse->user;
                $isGuest = is_null($user);

                $row = [
                    $isGuest ? 'زائر' : $user->name,
                    $isGuest ? '-' : ($user->email ?? '-'),
                    $firstResponse->created_at->format('Y-m-d H:i:s'),
                ];

                $answers = $firstResponse->answers ?? [];

                foreach ($questions as $question) {
                    $value = $answers[$question->id] ?? $answers[(string) $question->id] ?? null;

                    if (is_array($value)) {
                        $value = implode('، ', $value);
                    }

                    $row[] = $value !== null && $value !== '' ? $value : '-';
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * تقرير المقارنة بين التقييم القبلي والبعدي
     */
    public function comparisonReport(Survey $survey)
    {
        if (! $survey->isAssessment()) {
            return back()->with('error', 'هذا الاستبيان ليس من نوع التقييم القبلي/البعدي');
        }

        $survey->load(['lesson.concept.value', 'value', 'linkedSurvey', 'questions']);
        $comparisonData = $survey->getComparisonData();

        if (isset($comparisonData['error'])) {
            return back()->with('error', $comparisonData['error']);
        }

        return view('admin.surveys.comparison', compact('survey', 'comparisonData'));
    }

    /**
     * حذف استبيان
     */
    public function destroy(Survey $survey)
    {
        // التحقق من وجود إجابات
        if ($survey->responses()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الاستبيان لوجود إجابات مرتبطة به! ❌');
        }

        // حذف الاستبيان المرتبط إذا كان تقييم
        if ($survey->isAssessment() && $survey->linkedSurvey) {
            $linked = $survey->linkedSurvey;
            if ($linked->responses()->count() === 0) {
                $linked->update(['linked_survey_id' => null]);
                $survey->update(['linked_survey_id' => null]);
                $linked->delete();
            }
        }

        $survey->delete();

        return redirect()
            ->route('admin.surveys.index')
            ->with('success', 'تم حذف الاستبيان بنجاح! 🗑️');
    }
}
