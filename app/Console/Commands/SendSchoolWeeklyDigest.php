<?php

namespace App\Console\Commands;

use App\Mail\SchoolWeeklyDigestMail;
use App\Models\User;
use App\Services\Mail\MailGate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * الملخّص الأسبوعيّ لمديري المدارس (خطّة أدوار البريد SA6): طلاب نشِطون + أنشطة بانتظار الاعتماد.
 * يُرسَل لمن لمدرسته طلاب. عبر MailGate (فئة digest).
 */
class SendSchoolWeeklyDigest extends Command
{
    protected $signature = 'emails:digest-school-weekly';

    protected $description = 'إرسال الملخّص الأسبوعيّ لمديري المدارس';

    public function handle(): int
    {
        $sent = 0;
        $weekAgo = now()->subDays(7)->toDateString();

        User::where('role', 'school_admin')->whereNotNull('email')->where('email', '!=', '')
            ->whereNotNull('school_id')->chunkById(200, function ($admins) use (&$sent, $weekAgo) {
                foreach ($admins as $admin) {
                    try {
                        // أرقام الملخّص لا تحتسب طلاب الديمو (المدير قد يتلقّى الملخّص عاديّاً)
                        $studentIds = User::where('role', 'student')->where('school_id', $admin->school_id)
                            ->where('is_demo', false)->pluck('id');
                        if ($studentIds->isEmpty()) {
                            continue;
                        }
                        $active = Schema::hasTable('streaks')
                            ? DB::table('streaks')->whereIn('user_id', $studentIds)->whereDate('last_activity_date', '>=', $weekAgo)->count()
                            : 0;
                        $pending = DB::table('activities')
                            ->where('school_approval_status', 'pending')
                            ->where(function ($q) use ($admin) {
                                $q->where('school_id', $admin->school_id)->orWhereNull('school_id');
                            })->count();
                    } catch (\Throwable $e) {
                        continue;
                    }

                    if (MailGate::send($admin, 'schooladmin_weekly_digest', 'digest', new SchoolWeeklyDigestMail($admin, (int) $active, $studentIds->count(), (int) $pending))) {
                        $sent++;
                    }
                }
            });

        $this->info("الملخّص الأسبوعيّ للمدارس: أُرسِل لـ{$sent} مدير.");

        return self::SUCCESS;
    }
}
