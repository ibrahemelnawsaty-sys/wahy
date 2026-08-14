<?php

namespace App\Console\Commands;

use App\Mail\WeeklyDigestMail;
use App\Models\User;
use App\Services\Mail\MailGate;
use Illuminate\Console\Command;

/**
 * الملخّص الأسبوعيّ للطلاب النشِطين (خطّة البريد P7). يُرسَل عبر MailGate فيحترم العلم وإلغاء الاشتراك.
 * الطلاب بلا نشاط هذا الأسبوع لا يُراسَلون (تفادي الإزعاج).
 */
class SendWeeklyDigest extends Command
{
    protected $signature = 'emails:digest-weekly';

    protected $description = 'إرسال الملخّص الأسبوعيّ للطلاب النشِطين';

    public function handle(): int
    {
        $sent = 0;
        $skipped = 0;

        User::where('role', 'student')->whereNotNull('email')->where('email', '!=', '')
            ->chunkById(200, function ($students) use (&$sent, &$skipped) {
                foreach ($students as $student) {
                    $points = 0;
                    try {
                        $points = (int) $student->points()->where('created_at', '>=', now()->subDays(7))->sum('points');
                    } catch (\Throwable $e) {
                        // علاقة غير متاحة — نتخطّى بأمان
                    }

                    if ($points <= 0) {
                        $skipped++;
                        continue; // لا نشاط هذا الأسبوع
                    }

                    if (MailGate::send($student, 'weekly_digest', 'digest', new WeeklyDigestMail($student, $points))) {
                        $sent++;
                    } else {
                        $skipped++;
                    }
                }
            });

        $this->info("الملخّص الأسبوعيّ: أُرسِل لـ{$sent} طالب، تُخطّي {$skipped}.");

        return self::SUCCESS;
    }
}
