<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * إشعار الإدارة برسالة «تواصل معنا» جديدة.
 *
 * **ShouldQueue إلزاميّ هنا**: عمليّة الويب على الاستضافة لا تستطيع فتح اتّصال صادر على 587
 * (Connection timed out)، فالإرسال المتزامن من الطلب يفشل دائمًا. العامل (CLI) وحده يصل SMTP.
 */
class ContactMessageReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @param array<string, mixed> $data */
    public function __construct(public array $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'رسالة تواصل جديدة من ' . ($this->data['full_name'] ?? 'زائر'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact', with: ['data' => $this->data]);
    }
}
