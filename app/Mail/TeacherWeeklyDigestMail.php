<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** الملخّص الأسبوعيّ للمعلّم (خطّة أدوار البريد T6) — يذكّره بالتسليمات بانتظار المراجعة. */
class TeacherWeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $teacher, public int $pendingCount, public int $studentCount) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📊 ملخّص أسبوعك في ' . setting('site_name', 'أثيل مكة'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.teacher.weekly-digest', with: [
            'teacher' => $this->teacher, 'pendingCount' => $this->pendingCount, 'studentCount' => $this->studentCount,
            'unsubscribeUrl' => email_unsubscribe_url($this->teacher),
        ]);
    }
}
