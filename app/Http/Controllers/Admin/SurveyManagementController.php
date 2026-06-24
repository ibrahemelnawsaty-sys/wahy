<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SurveyManagementController extends Controller
{
    /**
     * عرض قائمة الاستبيانات
     */
    public function index(Request $request)
    {
        $query = Survey::with(['creator', 'school'])
            ->withCount('responses');

        // تصفية حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $surveys = $query->orderBy('created_at', 'desc')->paginate(20);

        // إحصائيات
        $stats = [
            'total' => Survey::count(),
            'active' => Survey::where('status', 'active')->count(),
            'draft' => Survey::where('status', 'draft')->count(),
            'closed' => Survey::where('status', 'closed')->count(),
            'total_responses' => SurveyResponse::count(),
        ];

        return view('admin.surveys.index', compact('surveys', 'stats'));
    }

    /**
     * صفحة إنشاء استبيان جديد
     */
    public function create()
    {
        $schools = School::where('status', 'active')->orderBy('name')->get();
        $roles = [
            'student' => 'طالب',
            'teacher' => 'معلم',
            'parent' => 'ولي أمر',
            'school_admin' => 'مدير مدرسة',
        ];

        return view('admin.surveys.create', compact('schools', 'roles'));
    }

    /**
     * حفظ استبيان جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_roles' => 'required|array|min:1',
            'target_roles.*' => 'in:student,teacher,parent,school_admin',
            'school_id' => 'nullable|exists:schools,id',
            'is_mandatory' => 'boolean',
            'is_popup' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:draft,active,closed',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => 'required|in:text,textarea,radio,checkbox,select,rating,scale',
            'questions.*.options' => 'nullable|array',
            'questions.*.is_required' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // إنشاء الاستبيان
            $survey = Survey::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'target_roles' => $validated['target_roles'],
                'school_id' => $validated['school_id'] ?? null,
                'is_mandatory' => $validated['is_mandatory'] ?? true,
                'is_popup' => $validated['is_popup'] ?? true,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'],
                'created_by' => Auth::id(),
            ]);

            // إنشاء الأسئلة
            foreach ($validated['questions'] as $index => $questionData) {
                SurveyQuestion::create([
                    'survey_id' => $survey->id,
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'options' => $questionData['options'] ?? null,
                    'is_required' => $questionData['is_required'] ?? true,
                    'order' => $index,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.surveys.index')
                ->with('success', 'تم إنشاء الاستبيان بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Survey creation failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'حدث خطأ أثناء إنشاء الاستبيان');
        }
    }

    /**
     * عرض تفاصيل استبيان
     */
    public function show(Survey $survey)
    {
        $survey->load(['questions', 'responses.user', 'creator', 'school']);

        // إحصائيات الإجابات
        $responseStats = [
            'total' => $survey->responses->count(),
            'by_role' => $survey->responses->groupBy(function ($response) {
                return $response->user->role ?? 'unknown';
            })->map->count(),
        ];

        return view('admin.surveys.show', compact('survey', 'responseStats'));
    }

    /**
     * صفحة تعديل استبيان
     */
    public function edit(Survey $survey)
    {
        $survey->load('questions');
        $schools = School::where('status', 'active')->orderBy('name')->get();
        $roles = [
            'student' => 'طالب',
            'teacher' => 'معلم',
            'parent' => 'ولي أمر',
            'school_admin' => 'مدير مدرسة',
        ];

        return view('admin.surveys.edit', compact('survey', 'schools', 'roles'));
    }

    /**
     * تحديث استبيان
     */
    public function update(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_roles' => 'required|array|min:1',
            'target_roles.*' => 'in:student,teacher,parent,school_admin',
            'school_id' => 'nullable|exists:schools,id',
            'is_mandatory' => 'boolean',
            'is_popup' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:draft,active,closed',
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'nullable|exists:survey_questions,id',
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => 'required|in:text,textarea,radio,checkbox,select,rating,scale',
            'questions.*.options' => 'nullable|array',
            'questions.*.is_required' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // تحديث الاستبيان
            $survey->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'target_roles' => $validated['target_roles'],
                'school_id' => $validated['school_id'] ?? null,
                'is_mandatory' => $validated['is_mandatory'] ?? true,
                'is_popup' => $validated['is_popup'] ?? true,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'],
            ]);

            // حذف الأسئلة القديمة وإنشاء الجديدة
            $survey->questions()->delete();

            foreach ($validated['questions'] as $index => $questionData) {
                SurveyQuestion::create([
                    'survey_id' => $survey->id,
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'options' => $questionData['options'] ?? null,
                    'is_required' => $questionData['is_required'] ?? true,
                    'order' => $index,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.surveys.index')
                ->with('success', 'تم تحديث الاستبيان بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Survey update failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'حدث خطأ أثناء تحديث الاستبيان');
        }
    }

    /**
     * حذف استبيان
     */
    public function destroy(Survey $survey)
    {
        // التحقق من وجود إجابات
        if ($survey->responses()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الاستبيان لوجود إجابات مرتبطة به');
        }

        $survey->delete();

        return redirect()->route('admin.surveys.index')
            ->with('success', 'تم حذف الاستبيان بنجاح');
    }

    /**
     * تغيير حالة الاستبيان
     */
    public function toggleStatus(Survey $survey)
    {
        $newStatus = $survey->status === 'active' ? 'closed' : 'active';
        $survey->update(['status' => $newStatus]);

        return back()->with('success', 'تم تحديث حالة الاستبيان بنجاح');
    }

    /**
     * تصدير إجابات الاستبيان
     */
    public function exportResponses(Survey $survey)
    {
        $survey->load(['questions', 'responses.user']);

        $headers = ['المستخدم', 'الدور', 'تاريخ الإجابة'];
        foreach ($survey->questions as $question) {
            $headers[] = $question->question_text;
        }

        $rows = [];
        foreach ($survey->responses as $response) {
            $row = [
                $response->user->name ?? 'غير معروف',
                $response->user->role ?? 'غير معروف',
                $response->completed_at ? $response->completed_at->format('Y-m-d H:i') : '-',
            ];

            foreach ($survey->questions as $question) {
                $answer = $response->answers[$question->id] ?? '-';
                if (is_array($answer)) {
                    $answer = implode(', ', $answer);
                }
                $row[] = $answer;
            }

            $rows[] = $row;
        }

        // تصدير CSV
        $filename = 'survey_' . $survey->id . '_responses_' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
