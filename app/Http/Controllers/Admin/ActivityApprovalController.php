<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
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
        $query = Activity::where('is_activity_bank', true)
            ->whereNotNull('created_by')
            ->with(['creator.school', 'lesson.concept.value']);

        // تصفية حسب الحالة
        $status = $request->get('status', 'pending');
        if ($status !== 'all') {
            $query->where('approval_status', $status);
        }

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('creator', function($creatorQ) use ($search) {
                      $creatorQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(20);

        // إحصائيات
        $stats = [
            'pending' => Activity::where('is_activity_bank', true)->whereNotNull('created_by')->where('approval_status', 'pending')->count(),
            'approved' => Activity::where('is_activity_bank', true)->whereNotNull('created_by')->where('approval_status', 'approved')->count(),
            'rejected' => Activity::where('is_activity_bank', true)->whereNotNull('created_by')->where('approval_status', 'rejected')->count(),
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
        $activity->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // إرسال إشعار للمعلم
        if ($activity->created_by) {
            NotificationService::send(
                $activity->created_by,
                'تمت الموافقة على نشاطك',
                "تمت الموافقة على نشاط '{$activity->title}' وأصبح متاحاً في بنك الأنشطة لجميع المعلمين.",
                'activity_approved',
                route('teacher.activity-bank.index')
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
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $activity->update([
            'approval_status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // إرسال إشعار للمعلم
        if ($activity->created_by) {
            NotificationService::send(
                $activity->created_by,
                'تم رفض نشاطك',
                "تم رفض نشاط '{$activity->title}'. السبب: {$request->rejection_reason}",
                'activity_rejected',
                route('teacher.activity-bank.index')
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
            ->get();

        foreach ($activities as $activity) {
            $activity->update([
                'approval_status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // إرسال إشعار للمعلم
            if ($activity->created_by) {
                NotificationService::send(
                    $activity->created_by,
                    'تمت الموافقة على نشاطك',
                    "تمت الموافقة على نشاط '{$activity->title}' وأصبح متاحاً في بنك الأنشطة.",
                    'activity_approved',
                    route('teacher.activity-bank.index')
                );
            }
        }

        return redirect()->route('admin.activity-approval.index')
            ->with('success', 'تمت الموافقة على ' . count($activities) . ' نشاط بنجاح');
    }
}

