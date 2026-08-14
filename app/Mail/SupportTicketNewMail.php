<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** بريد فريق الدعم عند فتح تذكرة جديدة (خطّة أدوار البريد S1). */
class SupportTicketNewMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $recipient, public int $ticketId, public string $subjectLine, public string $requester, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '🎫 تذكرة دعم جديدة #' . $this->ticketId . ': ' . $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.support.ticket-new', with: [
            'recipient' => $this->recipient, 'ticketId' => $this->ticketId,
            'subjectLine' => $this->subjectLine, 'requester' => $this->requester, 'url' => $this->url,
            'unsubscribeUrl' => email_unsubscribe_url($this->recipient),
        ]);
    }
}
