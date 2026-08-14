<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** بريد الطالب عند تحدّيه في مبارزة PvP (خطّة أدوار البريد — student pvp invite). */
class StudentPvpInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $opponent, public User $challenger, public string $challengeTitle, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '⚔️ ' . $this->challenger->name . ' يتحدّاك في ' . setting('site_name', 'أثيل مكة'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.student.pvp-invite', with: [
            'opponent' => $this->opponent, 'challenger' => $this->challenger,
            'challengeTitle' => $this->challengeTitle, 'url' => $this->url,
            'unsubscribeUrl' => email_unsubscribe_url($this->opponent),
        ]);
    }
}
