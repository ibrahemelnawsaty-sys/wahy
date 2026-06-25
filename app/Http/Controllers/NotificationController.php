<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * عرض صفحة الإشعارات
     */
    public function index()
    {
        $notifications = Notification::where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', Auth::id())
            ->latest()
            ->paginate(20);

        $unreadCount = NotificationService::getUnreadCount(Auth::id());

        // تحديد الليوت المناسب حسب دور المستخدم
        $user = Auth::user();
        $role = $user->role ?? 'student';

        $layoutMap = [
            'super_admin' => 'layouts.admin',
            'school_admin' => 'layouts.school-admin',
            'teacher' => 'layouts.teacher',
            'parent' => 'layouts.parent',
            'student' => 'layouts.student-app',
        ];

        $layout = $layoutMap[$role] ?? 'layouts.student-app';

        return view('notifications.index', compact('notifications', 'unreadCount', 'layout'));
    }

    /**
     * جلب الإشعارات عبر AJAX
     */
    public function fetch(Request $request)
    {
        $notifications = Notification::where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', Auth::id())
            ->latest()
            ->limit(10)
            ->get();

        $unreadCount = NotificationService::getUnreadCount(Auth::id());

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * تحديد إشعار كمقروء
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', Auth::id())
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * تحديد كل الإشعارات كمقروءة
     */
    public function markAllAsRead()
    {
        NotificationService::markAllAsRead(Auth::id());

        return response()->json(['success' => true]);
    }

    /**
     * حذف إشعار
     */
    public function delete($id)
    {
        $notification = Notification::where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', Auth::id())
            ->findOrFail($id);

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
