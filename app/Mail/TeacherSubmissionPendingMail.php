<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** بريد المعلّم عند تسليم طالب نشاطًا بانتظار مراجعته اليدويّة (خطّة أدوار البريد T1). */
class TeacherSubmissionPendingMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $teacher, public User $student, public string $activityTitle, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📥 تسليم جديد بانتظار مراجعتك من ' . $this->student->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.teacher.submission-pending', with: [
            'teacher' => $this->teacher, 'student' => $this->student,
            'activityTitle' => $this->activityTitle, 'url' => $this->url,
            'unsubscribeUrl' => email_unsubscribe_url($this->teacher),
        ]);
    }
}
