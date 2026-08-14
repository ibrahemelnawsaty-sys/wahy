<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** بريد الطالب عند إتاحة نشاط جديد له (خطّة أدوار البريد — student new activity). */
class StudentNewActivityMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $student, public string $activityTitle) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '✨ نشاط جديد بانتظارك: ' . $this->activityTitle);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.student.new-activity', with: [
            'student' => $this->student, 'activityTitle' => $this->activityTitle,
            'unsubscribeUrl' => email_unsubscribe_url($this->student),
        ]);
    }
}
