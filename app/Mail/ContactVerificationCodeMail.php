<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * رمز تحقّق البريد لنموذج «تواصل معنا» — يُرسَل للعنوان الذي أدخله الزائر ليُثبت ملكيّته قبل
 * قبول الرسالة (يمنع القصف الآليّ بعناوين عشوائيّة/ضحايا). مُصفَّف (العامل CLI وحده يصل SMTP).
 */
class ContactVerificationCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public string $code)
    {
    }

    public function build()
    {
        return $this->subject('رمز تأكيد رسالتك — ' . setting('site_name', 'أثيل مكة'))
            ->view('emails.contact-verification-code', ['code' => $this->code]);
    }
}
