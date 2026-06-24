<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class RegistrationPendingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $roleName;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->roleName = $this->getRoleNameInArabic($user->role);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تم استلام طلب التسجيل - منصة قيمّ',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-pending',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get role name in Arabic
     */
    private function getRoleNameInArabic(string $role): string
    {
        return match ($role) {
            'school_admin' => 'مدير مدرسة',
            'teacher' => 'معلم',
            'student' => 'طالب',
            'parent' => 'ولي أمر',
            default => 'مستخدم',
        };
    }
}
