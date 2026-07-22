<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lesson;
use App\Models\QuestionBank;
use App\Models\Value;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityBankController extends Controller
{
    /**
     * عرض بنك الأنشطة الموحد (أنشطة + أسئلة)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // ─── الأنشطة ───────────────────────────────────────
        $activityQuery = Activity::with(['creator', 'lesson.concept.value', 'approver'])
            ->where('is_activity_bank', true);

        // فلتر الحالة للأنشطة
        if ($request->filled('activity_status')) {
            $activityQuery->where('approval_status', $request->activity_status);
        }

        // فلتر المدرسة (فقط للسوبر أدمن)
        if ($request->filled('school_id') && $user->isSuperAdmin()) {
            $activityQuery->whereHas('creator', fn ($q) => $q->where('school_id', $request->school_id));
        }

        $activities = $activityQuery->orderBy('created_at', 'desc')->paginate(20, ['*'], 'activities_page');

        // ─── الأسئلة ───────────────────────────────────────
        $questionQuery = QuestionBank::with(['creator', 'lesson.concept.value', 'approver'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('question_status')) {
            $questionQuery->where('status', $request->question_status);
        }

        $questions = $questionQuery->paginate(20, ['*'], 'questions_page');

        // ─── الإحصائيات ────────────────────────────────────
        $activityStats = [
            'total' => Activity::where('is_activity_bank', true)->count(),
            'pending' => Activity::where('is_activity_bank', true)->where('approval_status', 'pending')->count(),
            'approved' => Activity::where('is_activity_bank', true)->where('approval_status', 'approved')->count(),
            'rejected' => Activity::where('is_activity_bank', true)->where('approval_status', 'rejected')->count(),
        ];

        $questionStats = [
            'total' => QuestionBank::count(),
            'pending' => QuestionBank::where('status', 'pending')->count(),
            'approved' => QuestionBank::where('status', 'approved')->count(),
            'rejected' => QuestionBank::where('status', 'rejected')->count(),
        ];

        $lessons = Lesson::select('id', 'title')->orderBy('title')->get();
        $values = Value::select('id', 'name')->get();

        $activeTab = $request->get('tab', 'activities');

        return view('admin.activity-bank', compact(
            'activities',
            'questions',
            'activityStats',
            'questionStats',
            'lessons',
            'values',
            'activeTab',
        ));
    }

    /**
     * إضافة نشاط جديد من الأدمن (يُعتمد تلقائياً)
     */
    public function storeActivity(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:quiz,exercise,project,creative,image_order,homework,practice',
            'difficulty' => 'required|in:easy,medium,hard',
            'points' => 'required|integer|min:1|max:500',
            'coins' => 'nullable|integer|min:0|max:500',
            'lesson_id' => 'nullable|exists:lessons,id',
            'status' => 'required|in:active,draft,inactive',
        ]);

        $user = Auth::user();

        $activity = Activity::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'difficulty' => $validated['difficulty'],
            'points' => $validated['points'],
            'coins' => $validated['coins'] ?? 0,
            'lesson_id' => $validated['lesson_id'] ?? null,
            'status' => $validated['status'],
            'is_activity_bank' => true,
            'created_by' => $user->id,
            'approval_status' => 'approved',   // معتمد تلقائياً من الأدمن
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.activity-bank.index', ['tab' => 'activities'])
            ->with('success', "✅ تم إضافة النشاط \"{$activity->title}\" بنجاح!");
    }

    /**
     * الموافقة على نشاط مقترح من معلم
     */
    public function approveActivity(Request $request, $id)
    {
        $activity = Activity::where('is_activity_bank', true)->findOrFail($id);
        $user = Auth::user();

        // إنفاذ ترتيب المراحل: لا اعتماد نهائيّ قبل اعتماد مدير المدرسة (نفس قيد المسار الرسميّ)
        if ($activity->school_approval_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'يجب أن يعتمد مدير المدرسة النشاط أولاً قبل الاعتماد النهائيّ.',
            ], 422);
        }

        // المسار الموحّد: نشر لكل المدارس «مباشر» + نقل للبنك (هذه الصفحة بلا مُنتقي — الافتراض direct)
        app(\App\Services\ActivityPublishingService::class)
            ->adminApprove($activity, 'all', 'direct', [], $user->id);

        // إشعار المعلم
        if ($activity->created_by) {
            \App\Services\NotificationService::create(
                $activity->created_by,
                'activity_approved',
                '✅ تمت الموافقة على نشاطك',
                "تمت الموافقة على نشاطك في بنك الأنشطة: {$activity->title}",
                [],
                route('teacher.activity-bank.index'),
            );
        }

        return response()->json(['success' => true, 'message' => 'تمت الموافقة على النشاط بنجاح']);
    }

    /**
     * رفض نشاط مقترح من معلم
     */
    public function rejectActivity(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $activity = Activity::where('is_activity_bank', true)->findOrFail($id);
        $user = Auth::user();

        // الأعمدة الموجودة فعلاً هي approved_by/approved_at (لا rejected_by/at) — تسجّل المُراجِع والوقت
        $activity->update([
            'approval_status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => $request->reason,
        ]);

        // إشعار المعلم
        if ($activity->created_by) {
            \App\Services\NotificationService::create(
                $activity->created_by,
                'activity_rejected',
                '❌ تم رفض نشاطك',
                "تم رفض نشاطك: {$activity->title}" . ($request->reason ? ". السبب: {$request->reason}" : ''),
                [],
                route('teacher.activity-bank.index'),
            );
        }

        return response()->json(['success' => true, 'message' => 'تم رفض النشاط بنجاح']);
    }

    /**
     * الموافقة على سؤال من بنك الأسئلة
     */
    public function approveQuestion(Request $request, $id)
    {
        $question = QuestionBank::findOrFail($id);
        $user = Auth::user();

        $question->approve($user->id);

        if ($question->created_by) {
            \App\Services\NotificationService::create(
                $question->created_by,
                'question_approved',
                '✅ تمت الموافقة على سؤالك',
                "تمت الموافقة على سؤالك: {$question->title}",
                route('teacher.activity-bank.index'),
            );
        }

        return response()->json(['success' => true, 'message' => 'تمت الموافقة على السؤال بنجاح']);
    }

    /**
     * رفض سؤال من بنك الأسئلة
     */
    public function rejectQuestion(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $user = Auth::user();
        $question = QuestionBank::findOrFail($id);

        $question->reject($user->id, $request->reason);

        if ($question->created_by) {
            \App\Services\NotificationService::create(
                $question->created_by,
                'question_rejected',
                '❌ تم رفض سؤالك',
                "تم رفض سؤالك: {$question->title}" . ($request->reason ? ". السبب: {$request->reason}" : ''),
                route('teacher.activity-bank.index'),
            );
        }

        return response()->json(['success' => true, 'message' => 'تم رفض السؤال بنجاح']);
    }
}
