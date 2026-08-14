<?php

namespace App\Listeners;

use App\Events\LevelUp;
use App\Mail\LevelUpMail;
use App\Services\Mail\MailGate;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * إشعار + بريد ترقية المستوى للطالب (خطّة البريد P6). البريد عبر MailGate (نوع level_up، فئة event).
 */
class SendLevelUpNotification implements ShouldQueue
{
    public function __construct(protected NotificationService $notifications) {}

    public function handle(LevelUp $event): void
    {
        $student = $event->student;

        $this->notifications->create(
            $student->id,
            'level_up',
            '⬆️ مستوى جديد',
            "🎉 ترقّيت إلى المستوى {$event->newLevel}! واصل تقدّمك.",
            '/student/dashboard',
        );

        if ($student->email) {
            MailGate::send($student, 'level_up', 'event', new LevelUpMail($student, $event->newLevel));
        }
    }
}
