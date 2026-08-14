<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * رسالة حملة واحدة لمستلِم واحد (خطّة البريد P5). مُصفَّفة، ترويساتها تربطها بالحملة/المستخدم
 * فيلتقطها مستمع التتبّع (email_logs).
 */
class CampaignMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $bodyHtml,
        public ?string $unsubscribeUrl,
        public int $campaignId,
        public ?int $userId,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.campaign',
            with: [
                'bodyHtml' => $this->bodyHtml,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }

    public function headers(): Headers
    {
        return new Headers(text: array_filter([
            'X-Wahy-Category' => 'campaign',
            'X-Wahy-Campaign' => (string) $this->campaignId,
            'X-Wahy-User' => $this->userId ? (string) $this->userId : null,
        ]));
    }
}
