<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * بريد ترقية المستوى للطالب (خطّة البريد P6) — يُرسَل عبر MailGate على حدث LevelUp.
 */
class LevelUpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $student, public $newLevel) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '🎉 ترقّيت إلى المستوى ' . $this->newLevel . '!');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.level-up',
            with: [
                'student' => $this->student,
                'newLevel' => $this->newLevel,
                'unsubscribeUrl' => email_unsubscribe_url($this->student),
            ],
        );
    }
}
