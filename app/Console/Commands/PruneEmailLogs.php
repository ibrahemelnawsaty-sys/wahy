<?php

namespace App\Console\Commands;

use App\Models\EmailLog;
use Illuminate\Console\Command;

/**
 * تقليم سجلّ البريد لمنع تضخّمه (خطّة البريد P7). يحذف السجلّات الأقدم من --days (افتراضيّ 180).
 */
class PruneEmailLogs extends Command
{
    protected $signature = 'emails:prune-logs {--days=180}';

    protected $description = 'حذف سجلّات البريد الأقدم من عدد أيّام محدَّد';

    public function handle(): int
    {
        $days = max(30, (int) $this->option('days'));
        $deleted = EmailLog::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("حُذف {$deleted} سجلّ بريد أقدم من {$days} يومًا.");

        return self::SUCCESS;
    }
}
