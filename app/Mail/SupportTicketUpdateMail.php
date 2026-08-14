<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** بريد صاحب التذكرة عند ردّ الدعم عليها أو إغلاقها (خطّة أدوار البريد S2/S4). */
class SupportTicketUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $recipient, public int $ticketId, public string $subjectLine, public string $headline, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->headline . ' #' . $this->ticketId);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.support.ticket-update', with: [
            'recipient' => $this->recipient, 'ticketId' => $this->ticketId,
            'subjectLine' => $this->subjectLine, 'headline' => $this->headline, 'url' => $this->url,
            'unsubscribeUrl' => email_unsubscribe_url($this->recipient),
        ]);
    }
}
