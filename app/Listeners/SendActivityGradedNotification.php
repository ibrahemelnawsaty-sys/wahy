<?php

namespace App\Listeners;

use App\Events\ActivityGraded;
use App\Services\NotificationService;
use App\Mail\ActivityGradedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendActivityGradedNotification implements ShouldQueue
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
    public function handle(ActivityGraded $event): void
    {
        $submission = $event->submission;
        $student = $submission->student;
        $activity = $submission->activity;

        // إرسال إشعار للطالب
        $message = "تم تقييم نشاطك '{$activity->title}' - حصلت على {$event->grade} نقطة";
        if ($event->feedback) {
            $message .= "\nملاحظات المعلم: {$event->feedback}";
        }

        $this->notificationService->create(
            $student->id,
            'activity_graded',
            '📝 تم التقييم',
            $message,
            "/student/activities/{$activity->id}"
        );

        // إرسال بريد إلكتروني للطالب
        if ($student->email) {
            try {
                Mail::to($student->email)->send(new ActivityGradedMail($submission));
            } catch (\Exception $e) {
                \Log::error('Failed to send activity graded email: ' . $e->getMessage());
            }
        }

        // إرسال إشعار لولي الأمر إن وجد
        $parent = $student->parent;
        if ($parent) {
            $parentMessage = "تم تقييم نشاط ابنك/ابنتك {$student->name} في '{$activity->title}' - حصل على {$event->grade} نقطة";
            
            $this->notificationService->create(
                $parent->id,
                'child_activity_graded',
                '📊 تقييم جديد',
                $parentMessage,
                "/parent/child/{$student->id}/activities"
            );
        }
    }
}
