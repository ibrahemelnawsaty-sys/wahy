<?php

namespace App\Console\Commands;

use App\Models\ContactMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * تنظيف سبام نموذج «تواصل معنا» بعد حادثة القصف: يحذف صفوف contact_messages المطابقة للمعايير
 * **و** يُفرِغ مهامّ بريد التأكيد المعلّقة من طابور jobs (حذف الصفوف وحده لا يوقف العامل عن الإرسال).
 *
 * أمثلة:
 *   php artisan contact:purge-spam --pattern=share.google --dry
 *   php artisan contact:purge-spam --pattern=OZON
 *   php artisan contact:purge-spam --since="2026-08-31 11:00" --until="2026-08-31 12:00"
 *   php artisan contact:purge-spam --ip=1.2.3.4
 */
class PurgeContactSpam extends Command
{
    protected $signature = 'contact:purge-spam
        {--pattern= : حذف الرسائل الحاوية لهذا النصّ (LIKE على message/full_name)}
        {--since= : من هذا التاريخ (created_at >=)}
        {--until= : إلى هذا التاريخ (created_at <=)}
        {--ip= : حذف رسائل هذا الـIP فقط}
        {--dry : معاينة فقط بلا حذف}
        {--keep-jobs : لا تُفرِغ مهامّ بريد التأكيد المعلّقة}';

    protected $description = 'تنظيف سبام نموذج التواصل (صفوف contact_messages + مهامّ بريد التأكيد المعلّقة)';

    public function handle(): int
    {
        $pattern = $this->option('pattern');
        $since = $this->option('since');
        $until = $this->option('until');
        $ip = $this->option('ip');
        $dry = (bool) $this->option('dry');

        if (! $pattern && ! $since && ! $ip) {
            $this->error('حدّد معياراً واحداً على الأقلّ: --pattern أو --since أو --ip (لمنع حذفٍ شامل بالخطأ).');

            return self::FAILURE;
        }

        $query = ContactMessage::query();
        if ($pattern) {
            $query->where(function ($q) use ($pattern) {
                $q->where('message', 'like', '%' . $pattern . '%')
                    ->orWhere('full_name', 'like', '%' . $pattern . '%')
                    ->orWhere('email', 'like', '%' . $pattern . '%');
            });
        }
        if ($since) {
            $query->where('created_at', '>=', $since);
        }
        if ($until) {
            $query->where('created_at', '<=', $until);
        }
        if ($ip) {
            $query->where('ip_address', $ip);
        }

        $count = (clone $query)->count();
        $this->info("صفوف مطابقة للحذف: {$count}");
        if ($count > 0) {
            $this->line('عيّنة:');
            (clone $query)->latest()->take(3)->get(['email', 'ip_address', 'message'])->each(function ($m) {
                $this->line('  - ' . $m->email . ' [' . $m->ip_address . '] ' . mb_substr((string) $m->message, 0, 60));
            });
        }

        // مهامّ بريد التأكيد المعلّقة في الطابور (لا تُمسّ ببثّ حذف الصفوف) — نُفرِغها بأمان (لا queue:clear
        // الشامل الذي يمسح إشعارات المنصّة المشروعة). آمن حتّى لو كان جدول jobs غير موجود.
        $pendingJobs = 0;
        if (! $this->option('keep-jobs')) {
            try {
                $pendingJobs = (int) DB::table('jobs')->where('payload', 'like', '%ContactConfirmationMail%')->count();
            } catch (\Throwable $e) {
                $pendingJobs = 0;
            }
            $this->info("مهامّ بريد تأكيد معلّقة للحذف: {$pendingJobs}");
        }

        if ($dry) {
            $this->warn('وضع المعاينة (--dry): لم يُحذَف شيء.');

            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("✅ حُذِف {$deleted} صفّ سبام.");

        if (! $this->option('keep-jobs') && $pendingJobs > 0) {
            try {
                $jobsDeleted = DB::table('jobs')->where('payload', 'like', '%ContactConfirmationMail%')->delete();
                $this->info("✅ أُفرِغ {$jobsDeleted} مهمّة بريد تأكيد معلّقة من الطابور.");
            } catch (\Throwable $e) {
                $this->warn('تعذّر تفريغ مهامّ الطابور: ' . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
