<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * بريد الطالب حين يميّز معلّمه تسليمه (خطّة أدوار البريد — student submission featured).
 *
 * ملاحظة مقصودة: **لا ShouldQueue** هنا. لا عامل طابور يعمل على الاستضافة، فالتصفيف يعني
 * بريداً لا يصل أبداً (انظر TwoFactorMailIsImmediateTest). يُرسَل متزامناً بعد commit.
 */
class StudentSubmissionFeaturedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $student,
        public string $activityTitle,
        public int $points,
        public ?string $reason,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '🌟 معلّمك ميّز عملك في ' . setting('site_name', 'أثيل مكة'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.student.submission-featured', with: [
            'student' => $this->student,
            'activityTitle' => $this->activityTitle,
            'points' => $this->points,
            'reason' => $this->reason,
            'url' => $this->url,
            'unsubscribeUrl' => email_unsubscribe_url($this->student),
        ]);
    }
}
