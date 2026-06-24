<?php

namespace App\Listeners;

use App\Events\StudentRegistered;
use App\Services\NotificationService;
use App\Mail\WelcomeStudentMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeNotification implements ShouldQueue
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
    public function handle(StudentRegistered $event): void
    {
        $student = $event->student;

        // idempotency: استخدام Cache lock لمنع إرسال ترحيب مكرر عند replay الـ event
        $cacheKey = "welcome:sent:user:{$student->id}";
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return; // أُرسلت مسبقًا
        }
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addDays(30));

        // إرسال رسالة ترحيب للطالب
        $welcomeMessage = "🎉 أهلاً بك في منصة قِيَم التعليمية!\n";
        $welcomeMessage .= "نحن سعداء بانضمامك لمجتمعنا التعليمي.\n";
        $welcomeMessage .= "ابدأ الآن باستكشاف الأنشطة والقيم المختلفة وحقق إنجازاتك!";

        $this->notificationService->create(
            $student->id,
            'welcome',
            '🌟 مرحباً بك',
            $welcomeMessage,
            '/student/dashboard'
        );

        // إرسال بريد إلكتروني ترحيبي مع التحقق من صحة العنوان
        if (!empty($student->email) && filter_var($student->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($student->email)->send(new WelcomeStudentMail($student));
            } catch (\Exception $e) {
                \Log::error('فشل إرسال إيميل الترحيب', ['student_id' => $student->id, 'error' => $e->getMessage()]);
            }
        }

        // إرسال إشعار لولي الأمر
        if ($student->parent) {
            $parentMessage = "تم تفعيل حساب ابنك/ابنتك {$student->name} بنجاح على منصة قِيَم.\n";
            $parentMessage .= "يمكنك الآن متابعة تقدمه الدراسي وإنجازاته.";

            $this->notificationService->create(
                $student->parent->id,
                'child_registered',
                '✅ تم تفعيل الحساب',
                $parentMessage,
                "/parent/child/{$student->id}"
            );
        }
    }
}
