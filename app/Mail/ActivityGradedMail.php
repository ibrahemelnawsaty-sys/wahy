<?php

namespace App\Mail;

use App\Models\ActivitySubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivityGradedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $submission;

    public function __construct(ActivitySubmission $submission)
    {
        $this->submission = $submission;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تم تقييم نشاطك - منصة قيمّ',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activity-graded',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
