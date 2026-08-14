<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** بريد المعلّم عند رفض الأدمن لنشاطه (خطّة أدوار البريد T4). */
class TeacherActivityRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $teacher, public string $activityTitle, public string $reason, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '↩️ نشاطك يحتاج تعديلًا: ' . $this->activityTitle);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.teacher.activity-rejected', with: [
            'teacher' => $this->teacher, 'activityTitle' => $this->activityTitle,
            'reason' => $this->reason, 'url' => $this->url,
            'unsubscribeUrl' => email_unsubscribe_url($this->teacher),
        ]);
    }
}
