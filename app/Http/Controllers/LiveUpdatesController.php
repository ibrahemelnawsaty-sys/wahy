<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * LiveUpdatesController — ملخّص العدّادات الحيّة للمستخدم الحالي حسب دوره.
 * يستطلعه public/js/live-updates.js دورياً (Polling) لتحديث الشارات دون تحديث الصفحة.
 * كل عدّاد مُغلَّف بأمان: إن فشل استعلام (موديل/جدول مفقود) يُحذف بدل كسر الاستجابة.
 */
class LiveUpdatesController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['counts' => (object) []]);
        }

        $safe = function (callable $fn) {
            try {
                return (int) $fn();
            } catch (\Throwable $e) {
                return null;
            }
        };

        $counts = [];
        $role = $user->role ?? null;

        // ── مشترك لكل الأدوار: الرسائل الفردية غير المقروءة ──
        $counts['messages_unread'] = $safe(fn () => \App\Models\Message::where('receiver_id', $user->id)
            ->where('is_read', false)->count());

        // ── admin / super-admin (عدّادات عالمية) ──
        if ($role === 'admin' || $role === 'super_admin') {
            $pendingSubs = $safe(fn () => \App\Models\ActivitySubmission::where('status', 'pending')->count());
            $counts['admin_pending_submissions'] = $pendingSubs;
            $counts['header_new_submissions'] = $pendingSubs;
            $counts['activity_submissions_pending'] = $pendingSubs;

            $pendingReq = $safe(fn () => \App\Models\RegistrationRequest::where('status', 'pending')->count());
            $counts['header_new_users'] = $pendingReq;
            $counts['registration_requests_pending'] = $pendingReq;

            // الطابور النهائي للأدمن = أنشطة معلّمين مُعتمَدة مدرسياً بانتظار الاعتماد النهائي
            $adminPendingActivities = fn () => \App\Models\Activity::whereNotNull('created_by')
                ->where('approval_status', 'pending')
                ->where('school_approval_status', 'approved')
                ->whereHas('creator', fn ($q) => $q->where('role', 'teacher'))
                ->count();
            $counts['activity_approval_pending'] = $safe($adminPendingActivities);
            $counts['activity_bank_pending'] = $safe(fn () => (
                $adminPendingActivities()
                + \App\Models\QuestionBank::where('status', 'pending')->count()
            ));
        }

        // ── teacher ──
        if ($role === 'teacher') {
            $counts['bulk_messages_unread'] = $safe(fn () => \App\Models\BulkMessageRecipient::where('user_id', $user->id)
                ->whereNull('read_at')->count());
            $counts['parent_messages_unread'] = $safe(fn () => \App\Models\ParentTeacherMessage::where('teacher_id', $user->id)
                ->where('sender_type', 'parent')->where('is_read', false)->count());
        }

        // ── parent ──
        if ($role === 'parent') {
            $counts['bulk_messages_unread'] = $safe(fn () => \App\Models\BulkMessageRecipient::where('user_id', $user->id)
                ->whereNull('read_at')->count());
            $counts['family_activities_pending'] = $safe(fn () => \App\Models\FamilyActivitySubmission::where('parent_id', $user->id)
                ->where('status', 'pending')->count());
            $counts['parent_teacher_unread'] = $safe(fn () => \App\Models\ParentTeacherMessage::where('parent_id', $user->id)
                ->where('sender_type', 'teacher')->where('is_read', false)->count());
        }

        // ── school-admin ──
        if ($role === 'school_admin') {
            $counts['bulk_messages_unread'] = $safe(fn () => \App\Models\BulkMessageRecipient::where('user_id', $user->id)
                ->whereNull('read_at')->count());
            $counts['registration_requests_pending'] = $safe(fn () => \App\Models\RegistrationRequest::where('school_id', $user->school_id)
                ->where('status', 'pending')->count());
            $counts['school_activity_approvals_pending'] = $safe(fn () => \App\Models\Activity::whereNotNull('created_by')
                ->where('school_approval_status', 'pending')
                ->whereHas('creator', fn ($q) => $q->where('school_id', $user->school_id)->where('role', 'teacher'))
                ->count());
        }

        // ── student ──
        if ($role === 'student') {
            $counts['coins_total'] = $safe(fn () => (int) DB::table('coins')->where('user_id', $user->id)->sum('coins'));
            $counts['points_total'] = $safe(fn () => (int) DB::table('points')->where('user_id', $user->id)->sum('points'));
            $counts['streak_current'] = $safe(fn () => (int) (\App\Models\Streak::where('user_id', $user->id)->value('current_streak') ?? 0));
        }

        // ── إشعارات النظام غير المقروءة (لكل من لديه خدمة الإشعارات) ──
        if (class_exists(\App\Services\NotificationService::class)) {
            $counts['notifications_unread'] = $safe(fn () => (int) \App\Services\NotificationService::getUnreadCount($user->id));
        }

        // حذف العدّادات الفاشلة (null) كي لا تُصفّر شارات صحيحة
        $counts = array_filter($counts, fn ($v) => $v !== null);

        return response()->json([
            'counts' => (object) $counts,
            'ts' => now()->timestamp,
        ]);
    }
}
