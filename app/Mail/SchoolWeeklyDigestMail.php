<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** الملخّص الأسبوعيّ لمدير المدرسة (خطّة أدوار البريد SA6). */
class SchoolWeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $admin, public int $activeStudents, public int $totalStudents, public int $pendingActivities) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📊 ملخّص مدرستك الأسبوعيّ في ' . setting('site_name', 'أثيل مكة'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.schooladmin.weekly-digest', with: [
            'admin' => $this->admin, 'activeStudents' => $this->activeStudents,
            'totalStudents' => $this->totalStudents, 'pendingActivities' => $this->pendingActivities,
            'unsubscribeUrl' => email_unsubscribe_url($this->admin),
        ]);
    }
}
