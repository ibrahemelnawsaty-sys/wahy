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

        // قائمة المدارس لمُنتقي «مدارس محدّدة» عند الاعتماد
        $schools = \App\Models\School::orderBy('name')->get(['id', 'name']);

        return view('admin.activity-approval.index', compact('activities', 'stats', 'status', 'schools'));
    }

    /**
     * عرض تفاصيل نشاط للمراجعة
     */
    public function show(Activity $activity)
    {
        $activity->load(['creator.school', 'lesson.concept.value', 'approver']);

        $schools = \App\Models\School::orderBy('name')->get(['id', 'name']);

        return view('admin.activity-approval.show', compact('activity', 'schools'));
    }

    /**
     * الموافقة على نشاط
     */
    public function approve(Request $request, Activity $activity)
    {
        // إنفاذ ترتيب المراحل: لا اعتماد نهائيّ قبل اعتماد مدير المدرسة (المرحلة الأولى)
        abort_unless($activity->school_approval_status === 'approved', 404);

        // نطاق النشر (كل المدارس افتراضيًّا / مدارس محدّدة) + وضع النشر (مباشر/بنك) — §متطلب 1+2
        $validated = $request->validate([
            'scope' => 'required|in:all,specific',
            'publish_mode' => 'required|in:bank,direct',
            'school_ids' => 'required_if:scope,specific|array',
            'school_ids.*' => 'integer|exists:schools,id',
        ]);

        app(\App\Services\ActivityPublishingService::class)->adminApprove(
            $activity,
            $validated['scope'],
            $validated['publish_mode'],
            $validated['school_ids'] ?? [],
            Auth::id(),
        );

        // نشاط درسٍ صار الآن ظاهراً للطلاب → أشعِر طلاب الفصل (كان يُرسَل عند الإنشاء، أُجِّل للاعتماد)
        $this->notifyClassroomStudentsOfApprovedActivity($activity);

        // إرسال إشعار للمعلم — نعتمد على **وضع النشر المختار** لا is_activity_bank (الذي يضبطه
        // adminApprove إلى true دائماً بالنقل التلقائيّ للبنك، فيجعل فرع «ظاهر لطلابك» ميّتاً).
        if ($activity->created_by) {
            $publishedDirect = $validated['publish_mode'] === 'direct';
            $target = $publishedDirect ? route('teacher.activities') : route('teacher.activity-bank.index');
            $body = $publishedDirect
                ? "تمت الموافقة على نشاط '{$activity->title}' وأصبح ظاهراً للطلاب."
                : "تمت الموافقة على نشاط '{$activity->title}' وأصبح متاحاً في بنك الأنشطة لجميع المعلمين.";
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

        // الرفض يسحب النشر: نشاط سبق نشره (لمدرسته أو للكل) لا يبقى مرئيًّا للطلاب بعد رفضه
        app(\App\Services\ActivityPublishingService::class)->revokePublishing($activity);

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
            'publish_mode' => 'nullable|in:bank,direct',
        ]);

        // الاعتماد المجمّع يَنشر «لكل المدارس» بالوضع المختار (افتراضيًّا مباشر — حفاظًا على السلوك السابق)
        $mode = $request->input('publish_mode', 'direct');
        $publisher = app(\App\Services\ActivityPublishingService::class);

        $activities = Activity::whereIn('id', $request->activity_ids)
            ->where('approval_status', 'pending')
            ->where('school_approval_status', 'approved')   // إنفاذ ترتيب المراحل
            ->get();

        foreach ($activities as $activity) {
            $publisher->adminApprove($activity, 'all', $mode, [], Auth::id());

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
        if (! $activity->classroom_id) {
            return;
        }
        $activity->loadMissing('classroom.students');
        $classroom = $activity->classroom;
        if (! $classroom) {
            return;
        }

        // لا نُشعِر إلا إن صار النشاط مرئيًّا مباشرةً لطلاب الفصل فعلاً: منشور «مباشر» لكل المدارس
        // أو لمدرسة الفصل صراحةً. وضع «بنك» (all_schools_mode='bank' أو صفّ activity_school بوضع
        // bank) لا يظهر تلقائيًّا للطلاب — فإشعار «نشاط جديد» عنه مضلِّل ويناقض دلالة الوضع.
        $directlyVisible = $activity->all_schools_mode === 'direct'
            || ($classroom->school_id && $activity->isDirectToSchool((int) $classroom->school_id));
        if (! $directlyVisible) {
            return;
        }

        foreach ($classroom->students as $student) {
            NotificationService::newActivity($student->id, $activity->title);
        }
    }
}
