<?php

namespace App\Mail;

use App\Models\RegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $request;

    public $temporaryPassword;

    /**
     * Create a new message instance.
     */
    public function __construct(RegistrationRequest $request, $temporaryPassword)
    {
        $this->request = $request;
        $this->temporaryPassword = $temporaryPassword;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: safe_mail_subject('🎉 مبروك! تمت الموافقة على طلبك - ' . setting('site_name', 'منصة قيمّ')),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-approved',
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
