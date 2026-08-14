<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * بريد «نشاط بانتظار الاعتماد» لمدير المدرسة/الأدمن (خطّة أدوار البريد SA4/A1).
 * ShouldQueue لأنّه يُطلَق تزامنيًّا من متحكّم المعلّم (لا مستمع مُصفَّف).
 */
class ActivityPendingApprovalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $recipient, public User $teacher, public string $activityTitle, public string $approvalUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📝 نشاط بانتظار اعتمادك من ' . $this->teacher->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.schooladmin.activity-pending', with: [
            'recipient' => $this->recipient, 'teacher' => $this->teacher,
            'activityTitle' => $this->activityTitle, 'approvalUrl' => $this->approvalUrl,
            'unsubscribeUrl' => email_unsubscribe_url($this->recipient),
        ]);
    }
}
