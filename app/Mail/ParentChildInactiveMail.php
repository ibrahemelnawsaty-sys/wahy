<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** بريد وليّ الأمر عند خمول ابنه (لم يدخل المنصّة عدّة أيّام) — خطّة أدوار البريد P7. */
class ParentChildInactiveMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $parent, public User $student, public int $days) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '👀 لم يدخل ' . $this->student->name . ' المنصّة منذ ' . $this->days . ' أيّام');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.parent.child-inactive', with: [
            'parent' => $this->parent, 'student' => $this->student, 'days' => $this->days,
            'unsubscribeUrl' => email_unsubscribe_url($this->parent),
        ]);
    }
}
