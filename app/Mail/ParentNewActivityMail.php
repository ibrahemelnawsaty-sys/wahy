<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** بريد وليّ الأمر عند إتاحة نشاط جديد لابنه (خطّة أدوار البريد P5). */
class ParentNewActivityMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $parent, public User $student, public string $activityTitle) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '✨ نشاط جديد لابنك ' . $this->student->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.parent.new-activity', with: [
            'parent' => $this->parent, 'student' => $this->student, 'activityTitle' => $this->activityTitle,
            'unsubscribeUrl' => email_unsubscribe_url($this->parent),
        ]);
    }
}
