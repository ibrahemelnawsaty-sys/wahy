<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * إنشاء إشعار جديد — مع:
     * - التحقق من وجود المستخدم لمنع orphan notifications
     * - dedup: لا نُكرّر نفس (user_id + type + title) خلال 5 دقائق لمنع spam
     */
    public static function create($userId, $type, $title, $message, $data = [], $actionUrl = null)
    {
        if (empty($userId) || ! User::where('id', $userId)->exists()) {
            \Log::warning('محاولة إنشاء إشعار لمستخدم غير موجود', ['user_id' => $userId, 'type' => $type]);

            return null;
        }

        // dedup: لو نفس الإشعار أُنشئ خلال آخر 5 دقائق، لا نُكرّره
        $recentDuplicate = Notification::where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $userId)
            ->where('type', $type)
            ->where('title', $title)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if ($recentDuplicate) {
            return null;
        }

        return Notification::create([
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
        ]);
    }

    /**
     * إرسال إشعار بتوقيع مبسّط (title, message, type, actionUrl).
     * Wrapper متوافق مع الاستدعاءات القديمة في الـ Controllers.
     */
    public static function send($userId, $title, $message, $type = 'general', $actionUrl = null, $data = [])
    {
        return self::create($userId, $type, $title, $message, $data, $actionUrl);
    }

    /**
     * إشعار إكمال نشاط
     */
    public static function activityCompleted($studentId, $activityTitle, $score, $xp, $coins)
    {
        return self::create(
            $studentId,
            'activity_completed',
            '🎉 تم إكمال النشاط!',
            "أحسنت! أكملت نشاط \"{$activityTitle}\" وحصلت على {$score}% - {$xp} XP و {$coins} عملة",
            [
                'activity' => $activityTitle,
                'score' => $score,
                'xp' => $xp,
                'coins' => $coins,
            ],
        );
    }

    /**
     * إشعار الترقية لمستوى جديد
     */
    public static function levelUp($studentId, $newLevel)
    {
        return self::create(
            $studentId,
            'level_up',
            '🎊 مبروك! ارتقيت لمستوى جديد!',
            "أصبحت الآن مستوى {$newLevel}! استمر في التقدم",
            ['level' => $newLevel],
        );
    }

    /**
     * إشعار حصول على وسام
     */
    public static function badgeEarned($studentId, $badgeName)
    {
        return self::create(
            $studentId,
            'badge_earned',
            '🏅 حصلت على وسام جديد!',
            "مبروك! حصلت على وسام \"{$badgeName}\"",
            ['badge' => $badgeName],
        );
    }

    /**
     * إشعار حافة سلسلة الإنجاز
     */
    public static function streakMilestone($studentId, $days)
    {
        return self::create(
            $studentId,
            'streak_milestone',
            '🔥 إنجاز رائع!',
            "واصلت السلسلة لمدة {$days} يوم متتالي! استمر في التقدم",
            ['days' => $days],
        );
    }

    /**
     * إشعار تقييم نشاط من المعلم
     */
    public static function activityGraded($studentId, $activityTitle, $score, $feedback)
    {
        return self::create(
            $studentId,
            'activity_graded',
            '✅ تم تقييم نشاطك',
            "تم تقييم \"{$activityTitle}\" - حصلت على {$score}%",
            [
                'activity' => $activityTitle,
                'score' => $score,
                'feedback' => $feedback,
            ],
        );
    }

    /**
     * إشعار ولي الأمر بنشاط الابن
     */
    public static function parentNotification($parentId, $childName, $message, $type = 'child_activity')
    {
        return self::create(
            $parentId,
            $type,
            "📢 تحديث عن {$childName}",
            $message,
            ['child' => $childName],
        );
    }

    /**
     * إشعار رسالة من المعلم
     */
    public static function teacherMessage($studentId, $teacherName, $message)
    {
        return self::create(
            $studentId,
            'teacher_message',
            "💬 رسالة من المعلم {$teacherName}",
            $message,
            ['teacher' => $teacherName],
        );
    }

    /**
     * إشعار نشاط جديد
     */
    public static function newActivity($studentId, $activityTitle, $type = 'نشاط', $dueDate = null)
    {
        $message = "نشاط جديد: \"{$activityTitle}\"";

        if ($dueDate) {
            $message .= ' - الموعد النهائي: ' . $dueDate->format('Y-m-d H:i');
        }

        return self::create(
            $studentId,
            'new_activity',
            '📝 ' . $type . ' جديد متاح',
            $message,
            [
                'activity' => $activityTitle,
                'type' => $type,
                'due_date' => $dueDate ? $dueDate->toDateTimeString() : null,
            ],
        );
    }

    /**
     * تذكير بواجب منزلي قريب الموعد
     */
    public static function homeworkReminder($studentId, $homeworkTitle, $dueDate)
    {
        $hoursLeft = now()->diffInHours($dueDate);

        return self::create(
            $studentId,
            'homework_reminder',
            '⏰ تذكير: واجب منزلي',
            "لا تنسى! واجب \"{$homeworkTitle}\" موعد تسليمه بعد {$hoursLeft} ساعة",
            [
                'homework' => $homeworkTitle,
                'due_date' => $dueDate->toDateTimeString(),
                'hours_left' => $hoursLeft,
            ],
        );
    }

    /**
     * تنبيه بواجب منزلي متأخر
     */
    public static function homeworkOverdue($studentId, $homeworkTitle, $dueDate)
    {
        return self::create(
            $studentId,
            'homework_overdue',
            '⚠️ واجب منزلي متأخر',
            "انتبه! فاتك موعد تسليم واجب \"{$homeworkTitle}\" - كان موعده {$dueDate->locale('ar')->diffForHumans()}",
            [
                'homework' => $homeworkTitle,
                'due_date' => $dueDate->toDateTimeString(),
                'activity_id' => null, // سيتم إضافته في Command
            ],
        );
    }

    /**
     * جلب إشعارات المستخدم
     */
    public static function getUserNotifications($userId, $limit = 10, $unreadOnly = false)
    {
        $query = Notification::where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $userId)
            ->latest();

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->limit($limit)->get();
    }

    /**
     * عدد الإشعارات غير المقروءة
     */
    public static function getUnreadCount($userId)
    {
        return Notification::where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $userId)
            ->unread()
            ->count();
    }

    /**
     * تحديد كل إشعارات المستخدم كمقروءة
     */
    public static function markAllAsRead($userId)
    {
        return Notification::where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * إشعار لمدير المدرسة
     */
    public static function schoolAdminNotification($adminId, $title, $message, $actionUrl = null)
    {
        return self::create(
            $adminId,
            'admin_notification',
            $title,
            $message,
            [],
            $actionUrl,
        );
    }

    /**
     * إشعار طلب تسجيل جديد لمدير المدرسة
     */
    public static function newRegistrationRequest($adminId, $studentName)
    {
        return self::create(
            $adminId,
            'registration_request',
            '📋 طلب تسجيل جديد',
            "طلب تسجيل جديد من الطالب: {$studentName}",
            ['student' => $studentName],
            route('school-admin.requests'),
        );
    }
}
