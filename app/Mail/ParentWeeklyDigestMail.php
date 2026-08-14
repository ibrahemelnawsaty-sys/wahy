<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** الملخّص الأسبوعيّ لوليّ الأمر عن أبنائه (خطّة أدوار البريد P8). */
class ParentWeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array<int,array{name:string,points:int}> $children */
    public function __construct(public User $parent, public array $children) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📊 ملخّص أسبوع أبنائك في ' . setting('site_name', 'أثيل مكة'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.parent.weekly-digest', with: [
            'parent' => $this->parent, 'children' => $this->children,
            'unsubscribeUrl' => email_unsubscribe_url($this->parent),
        ]);
    }
}
