<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * أداة تحقّق من إرسال البريد (المهمّة 25 + إغلاق عطل MAIL_PASSWORD).
 * الاستخدام: php artisan mail:test you@example.com
 * تُرسل رسالة اختبار وتُبلّغ بالنجاح أو بنصّ الخطأ الدقيق (مفيدة بعد تصحيح بيانات SMTP في .env).
 */
class TestMail extends Command
{
    protected $signature = 'mail:test {to : عنوان المستقبِل للاختبار}';

    protected $description = 'إرسال رسالة اختبار للتحقّق من إعداد SMTP';

    public function handle(): int
    {
        $to = (string) $this->argument('to');

        $this->line('المُرسِل: ' . config('mail.from.address') . ' عبر ' . config('mail.default')
            . ' (' . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port') . ')');
        $this->line('المستقبِل: ' . $to);

        try {
            Mail::raw(
                'رسالة اختبار من ' . setting('site_name', 'أثيل مكة') . " — إعداد البريد يعمل بنجاح.\n"
                    . 'الوقت: ' . now()->toDateTimeString(),
                function ($m) use ($to) {
                    $m->to($to)->subject('اختبار البريد — ' . setting('site_name', 'أثيل مكة'));
                }
            );
        } catch (\Throwable $e) {
            $this->error('✗ فشل الإرسال: ' . $e->getMessage());
            $this->line('راجِع MAIL_PASSWORD/MAIL_USERNAME في .env (تنبيه: كان MAIL_PASSWORD مطابقاً لـDB_PASSWORD).');

            return self::FAILURE;
        }

        $this->info('✓ أُرسِلت رسالة الاختبار. تحقّق من صندوق الوارد (ومجلّد السبام).');

        return self::SUCCESS;
    }
}
