<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

/**
 * تشخيص بريد كامل (خطّة البريد P1): يفحص الإعداد + جداول الطابور + يرسل رسالة اختبار،
 * وبـ--queue يختبر مسار الطابور صراحةً (يكشف إن كان العامل queue:work يعمل أصلاً).
 *
 *   php artisan mail:test you@example.com           # فحص + إرسال تزامنيّ
 *   php artisan mail:test you@example.com --queue    # إرسال عبر الطابور (يجب أن يفرّغه العامل)
 */
class TestMail extends Command
{
    protected $signature = 'mail:test {to : عنوان المستقبِل للاختبار} {--queue : إرسال عبر الطابور لاختبار العامل}';

    protected $description = 'تشخيص إعداد SMTP والطابور وإرسال رسالة اختبار';

    public function handle(): int
    {
        $to = (string) $this->argument('to');
        $ok = true;

        // ===== 1) الإعداد =====
        $this->info('── إعداد البريد ──');
        $mailer = config('mail.default');
        $this->line('  MAIL_MAILER : ' . $mailer);
        $this->line('  From        : ' . config('mail.from.address') . ' («' . config('mail.from.name') . '»)');
        if ($mailer === 'smtp') {
            $smtp = config('mail.mailers.smtp');
            $this->line('  SMTP        : ' . ($smtp['host'] ?? '—') . ':' . ($smtp['port'] ?? '—')
                . ' (' . ($smtp['scheme'] ?? $smtp['encryption'] ?? 'null') . ')');
            $this->line('  Username    : ' . ($smtp['username'] ?: '⚠ فارغ'));
            $host = (string) ($smtp['host'] ?? '');
            if ($host === '' || str_contains($host, '‹') || str_contains($host, 'مثال')) {
                $this->error('  ✗ MAIL_HOST غير مضبوط (ما زال placeholder). عدّل .env ثمّ: php artisan config:cache');
                $ok = false;
            }
        }
        if ($mailer === 'log') {
            $this->error('  ✗ MAIL_MAILER=log — البريد يُكتَب في storage/logs ولا يُرسَل فعلاً! اضبط smtp/ses في .env.');
            $ok = false;
        }

        // ===== 2) جداول الطابور =====
        $this->newLine();
        $this->info('── الطابور ──');
        $this->line('  QUEUE_CONNECTION : ' . config('queue.default'));
        try {
            foreach (['jobs', 'failed_jobs', 'job_batches'] as $t) {
                $exists = Schema::hasTable($t);
                $this->line('  جدول ' . str_pad($t, 12) . ' : ' . ($exists ? '✓ موجود' : '✗ مفقود (شغّل php artisan migrate)'));
                $ok = $ok && $exists;
            }
            $pending = DB::table('jobs')->count();
            $failed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
            $this->line('  مهامّ منتظِرة : ' . $pending . '   |   فاشلة : ' . $failed);
            if ($pending > 20) {
                $this->warn('  ⚠ تراكم في الطابور — غالباً العامل queue:work لا يعمل.');
            }
        } catch (\Throwable $e) {
            $this->warn('  ⚠ تعذّر فحص الطابور (قاعدة البيانات غير متاحة؟): ' . $e->getMessage());
            $ok = false;
        }

        // ===== 3) الإرسال =====
        $this->newLine();
        $this->info('── الإرسال ──');
        $this->line('  المستقبِل : ' . $to);

        $body = fn () => 'رسالة اختبار من ' . setting('site_name', 'أثيل مكة') . " — إعداد البريد يعمل.\n"
            . 'الوقت: ' . now()->toDateTimeString();

        try {
            if ($this->option('queue')) {
                // يُصفَّف؛ لا يصل إلّا إن كان العامل يعمل — اختبار حاسم للعامل.
                dispatch(function () use ($to, $body) {
                    Mail::raw($body(), function ($m) use ($to) {
                        $m->to($to)->subject('اختبار الطابور — ' . setting('site_name', 'أثيل مكة'));
                    });
                });
                $this->info('  ✓ صُفَّت رسالة الاختبار. إن لم تصل خلال دقيقة فالعامل لا يعمل:');
                $this->line('    جرّب يدويّاً: php artisan queue:work --once   (لو وصلت الآن فالعامل هو الناقص)');
            } else {
                Mail::raw($body(), function ($m) use ($to) {
                    $m->to($to)->subject('اختبار البريد — ' . setting('site_name', 'أثيل مكة'));
                });
                $this->info('  ✓ أُرسِلت تزامنيّاً. تحقّق من الوارد (والسبام).');
            }
        } catch (\Throwable $e) {
            $this->error('  ✗ فشل الإرسال: ' . $e->getMessage());
            $this->line('    راجِع MAIL_HOST/PORT/ENCRYPTION/USERNAME/PASSWORD في .env ثمّ: php artisan config:cache');

            return self::FAILURE;
        }

        $this->newLine();
        $this->line($ok ? '<info>الخلاصة: الإعداد سليم.</info>' : '<comment>الخلاصة: توجد تحذيرات أعلاه — عالِجها ليصل البريد.</comment>');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
