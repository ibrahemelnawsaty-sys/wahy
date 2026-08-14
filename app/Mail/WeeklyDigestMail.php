<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * الملخّص الأسبوعيّ للطالب (خطّة البريد P7) — يُرسَل عبر MailGate (نوع weekly_digest، فئة digest).
 */
class WeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $student, public int $pointsThisWeek) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📊 ملخّص أسبوعك في ' . setting('site_name', 'أثيل مكة'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-digest',
            with: [
                'student' => $this->student,
                'points' => $this->pointsThisWeek,
                'unsubscribeUrl' => email_unsubscribe_url($this->student),
            ],
        );
    }
}
