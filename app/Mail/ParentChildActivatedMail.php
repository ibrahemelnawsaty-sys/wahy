<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** بريد وليّ الأمر عند تفعيل حساب ابنه (خطّة أدوار البريد P1). */
class ParentChildActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $parent, public User $student) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '✅ تم تفعيل حساب ' . $this->student->name . ' في ' . setting('site_name', 'أثيل مكة'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.parent.child-activated', with: [
            'parent' => $this->parent, 'student' => $this->student,
            'unsubscribeUrl' => email_unsubscribe_url($this->parent),
        ]);
    }
}
