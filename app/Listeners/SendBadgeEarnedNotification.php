<?php

namespace App\Listeners;

use App\Events\BadgeEarned;
use App\Mail\BadgeEarnedMail;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBadgeEarnedNotification implements ShouldQueue
{
    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(BadgeEarned $event): void
    {
        $user = $event->user;
        $badge = $event->badge;

        // إرسال إشعار للطالب
        $message = "🎖️ مبروك! حصلت على شارة جديدة: {$badge->name}\n{$badge->description}";

        $this->notificationService->create(
            $user->id,
            'badge_earned',
            '🏆 شارة جديدة',
            $message,
            '/student/badges',
        );

        // بريد عبر البوّابة المركزيّة (تحترم أعلام النوع وإلغاء الاشتراك — خطّة البريد P6)
        if ($user->email) {
            \App\Services\Mail\MailGate::send($user, 'badge_earned', 'event', new BadgeEarnedMail($user, $badge));
        }

        // إرسال إشعار لولي الأمر إن وجد
        if ($user->role === 'student' && $user->parent) {
            $parentMessage = "حصل ابنك/ابنتك {$user->name} على شارة جديدة: {$badge->name}";

            $this->notificationService->create(
                $user->parent->id,
                'child_badge_earned',
                '🎖️ إنجاز جديد',
                $parentMessage,
                "/parent/child/{$user->id}/achievements",
            );
        }
    }
}
