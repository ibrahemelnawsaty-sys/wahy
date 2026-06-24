<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class WelcomeStudentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'مرحباً بك في منصة قيمّ التعليمية',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-student',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
