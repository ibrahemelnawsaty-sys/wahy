<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** تأكيد للزائر بأنّ رسالته وصلت. مُصفَّف للسبب نفسه (الويب محجوب عن 587). */
class ContactConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @param array<string, mixed> $data */
    public function __construct(public array $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'تم استلام رسالتك - ' . setting('site_name', 'أثيل مكة'));
    }

    public function content(): Content
    {
        // متعدّد الأجزاء (HTML + نصّ) لتحسين وضع البريد في صندوق الوارد بدل السبام.
        return new Content(
            view: 'emails.contact-confirmation',
            text: 'emails.contact-confirmation-text',
            with: ['data' => $this->data],
        );
    }
}
