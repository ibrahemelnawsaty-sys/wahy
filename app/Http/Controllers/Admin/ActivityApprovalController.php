<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityApprovalController extends Controller
{
    /**
     * عرض الأنشطة المعلقة للموافقة
     */
    public function index(Request $request)
    {
        // الطابور النهائي للأدمن = أنشطة المعلّمين (بنك ودرس على حدٍّ سواء) التي اعتمدها
        // مدير المدرسة أولاً. نستبعد أنشطة الأدمن الذاتية عبر تقييد دور المُنشئ بـteacher.
        $query = Activity::whereNotNull('created_by')
            ->where('school_approval_status', 'approved')
            ->whereHas('creator', fn ($q) => $q->where('role', 'teacher'))
            ->with(['creator.school', 'lesson.concept.value']);

        // تصفية حسب الحالة
        $status = $request->get('status', 'pending');
        if ($status !== 'all') {
            $query->where('approval_status', $status);
        }

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('creator', function ($creatorQ) use ($search) {
                        $creatorQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(20);

        // إحصائيات — نفس نطاق الطابور (معلّم + مُعتمَد مدرسياً)
        $base = fn () => Activity::whereNotNull('created_by')
            ->where('school_approval_status', 'approved')
            ->whereHas('creator', fn ($q) => $q->where('role', 'teacher'));
        $stats = [
            'pending' => $base()->where('approval_status', 'pending')->count(),
            'approved' => $base()->where('approval_status', 'approved')->count(),
            'rejected' => $base()->where('approval_status', 'rejected')->count(),
        ];

        return view('admin.activity-approval.index', compact('activities', 'stats', 'status'));
    }

    /**
     * عرض تفاصيل نشاط للمراجعة
     */
    public function show(Activity $activity)
    {
        $activity->load(['creator.school', 'lesson.concept.value', 'approver']);

        return view('admin.activity-approval.show', compact('activity'));
    }

    /**
     * الموافقة على نشاط
     */
    public function approve(Request $request, Activity $activity)
    {
        // إنفاذ ترتيب المراحل: لا اعتماد نهائيّ قبل اعتماد مدير المدرسة (المرحلة الأولى)
        abort_unless($activity->school_approval_status === 'approved', 404);

        $activity->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // نشاط درسٍ صار الآن ظاهراً للطلاب → أشعِر طلاب الفصل (كان يُرسَل عند الإنشاء، أُجِّل للاعتماد)
        $this->notifyClassroomStudentsOfApprovedActivity($activity);

        // إرسال إشعار للمعلم
        if ($activity->created_by) {
            $target = $activity->is_activity_bank ? route('teacher.activity-bank.index') : route('teacher.activities');
            $body = $activity->is_activity_bank
                ? "تمت الموافقة على نشاط '{$activity->title}' وأصبح متاحاً في بنك الأنشطة لجميع المعلمين."
                : "تمت الموافقة على نشاط '{$activity->title}' وأصبح ظاهراً لطلابك.";
            NotificationService::send(
                $activity->created_by,
                'تمت الموافقة على نشاطك',
                $body,
                'activity_approved',
                $target,
            );
        }

        return redirect()->route('admin.activity-approval.index')
            ->with('success', 'تمت الموافقة على النشاط بنجاح');
    }

    /**
     * رفض نشاط
     */
    public function reject(Request $request, Activity $activity)
    {
        // النشاط في طابور الأدمن فقط بعد اعتماد مدير المدرسة
        abort_unless($activity->school_approval_status === 'approved', 404);

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $activity->update([
            'approval_status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // إرسال إشعار للمعلم — يمكنه تعديله وإعادة إرساله
        if ($activity->created_by) {
            $target = $activity->is_activity_bank ? route('teacher.activity-bank.index') : route('teacher.activities');
            NotificationService::send(
                $activity->created_by,
                'تم رفض نشاطك',
                "تم رفض نشاط '{$activity->title}'. السبب: {$request->rejection_reason}. يمكنك تعديله وإعادة إرساله.",
                'activity_rejected',
                $target,
            );
        }

        return redirect()->route('admin.activity-approval.index')
            ->with('success', 'تم رفض النشاط');
    }

    /**
     * الموافقة المجمعة على أنشطة
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'activity_ids' => 'required|array',
            'activity_ids.*' => 'exists:activities,id',
        ]);

        $activities = Activity::whereIn('id', $request->activity_ids)
            ->where('approval_status', 'pending')
            ->where('school_approval_status', 'approved')   // إنفاذ ترتيب المراحل
            ->get();

        foreach ($activities as $activity) {
            $activity->update([
                'approval_status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            $this->notifyClassroomStudentsOfApprovedActivity($activity);

            // إرسال إشعار للمعلم
            if ($activity->created_by) {
                NotificationService::send(
                    $activity->created_by,
                    'تمت الموافقة على نشاطك',
                    "تمت الموافقة على نشاط '{$activity->title}' وأصبح متاحاً في بنك الأنشطة.",
                    'activity_approved',
                    route('teacher.activity-bank.index'),
                );
            }
        }

        return redirect()->route('admin.activity-approval.index')
            ->with('success', 'تمت الموافقة على ' . count($activities) . ' نشاط بنجاح');
    }

    /**
     * إشعار طلاب فصل «نشاط الدرس» بأنه صار متاحاً بعد الاعتماد النهائيّ (نشاط درس فقط، لا بنك).
     * كان يُرسَل عند الإنشاء، فأُجِّل هنا حتى لا يُشعَر الطلاب بنشاطٍ لم يُعتمَد بعد.
     */
    private function notifyClassroomStudentsOfApprovedActivity(Activity $activity): void
    {
        if ($activity->is_activity_bank || ! $activity->classroom_id) {
            return;
        }
        $activity->loadMissing('classroom.students');
        $students = optional($activity->classroom)->students ?? collect();
        foreach ($students as $student) {
            NotificationService::newActivity($student->id, $activity->title);
        }
    }
}
