<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;

    public $userName;

    /**
     * Create a new message instance.
     */
    public function __construct($code, $userName)
    {
        $this->code = $code;
        $this->userName = $userName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // اسم الموقع من الإعدادات — كان مرمَّزاً «منصة قيمّ» بينما المُرسِل «أثيل مكة»؛ تعارُض
        // العلامة بين الموضوع والمُرسِل إشارةُ سبام/انعدام ثقة.
        return new Envelope(
            subject: 'كود التحقق - ' . setting('site_name', 'أثيل مكة'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // نسخة نصّيّة (text/plain) بجانب HTML: البريد متعدّد الأجزاء يُحسّن وضعه في صندوق الوارد
        // (البريد HTML-only يرفع نتيجة السبام).
        return new Content(
            view: 'emails.two-factor-code',
            text: 'emails.two-factor-code-text',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
