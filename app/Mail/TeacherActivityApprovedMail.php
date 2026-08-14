<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** بريد المعلّم عند اعتماد الأدمن لنشاطه (خطّة أدوار البريد T3). */
class TeacherActivityApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $teacher, public string $activityTitle, public string $url, public bool $direct) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '✅ تمت الموافقة على نشاطك: ' . $this->activityTitle);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.teacher.activity-approved', with: [
            'teacher' => $this->teacher, 'activityTitle' => $this->activityTitle,
            'url' => $this->url, 'direct' => $this->direct,
            'unsubscribeUrl' => email_unsubscribe_url($this->teacher),
        ]);
    }
}
