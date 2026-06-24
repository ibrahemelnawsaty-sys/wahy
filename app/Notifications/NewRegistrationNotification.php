<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RegistrationRequest;

class NewRegistrationNotification extends Notification
{
    use Queueable;

    protected $request;

    /**
     * Create a new notification instance.
     */
    public function __construct(RegistrationRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $roleNames = [
            'teacher' => 'معلم',
            'student' => 'طالب',
            'parent' => 'ولي أمر',
        ];

        return [
            'title' => 'طلب تسجيل جديد',
            'message' => 'تم استلام طلب تسجيل جديد من ' . $this->request->name . ' كـ' . $roleNames[$this->request->role],
            'icon' => 'bell',
            'color' => 'info',
            'action_url' => route('school-admin.requests'),
            'action_text' => 'مراجعة الطلب',
            'request_id' => $this->request->id,
            'request_name' => $this->request->name,
            'request_role' => $this->request->role,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
