<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** بريد وليّ الأمر عند تصحيح نشاط ابنه (خطّة أدوار البريد P2). */
class ParentChildActivityGradedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $parent, public User $student, public string $activityTitle, public $grade) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📝 تم تقييم نشاط ' . $this->student->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.parent.child-activity-graded', with: [
            'parent' => $this->parent, 'student' => $this->student,
            'activityTitle' => $this->activityTitle, 'grade' => $this->grade,
            'unsubscribeUrl' => email_unsubscribe_url($this->parent),
        ]);
    }
}
