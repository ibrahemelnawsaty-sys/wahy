<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Badge;
use App\Models\User;

class BadgeEarnedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $badge;

    public function __construct(User $user, Badge $badge)
    {
        $this->user = $user;
        $this->badge = $badge;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'مبروك! حصلت على وسام جديد 🏆',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.badge-earned',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
