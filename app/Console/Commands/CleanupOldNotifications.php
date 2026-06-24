<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupOldNotifications extends Command
{
    protected $signature = 'notifications:cleanup {--days=90 : عدد الأيام للاحتفاظ بالإشعارات}';

    protected $description = 'حذف الإشعارات الأقدم من N يومًا لمنع تضخّم جدول notifications.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        if ($days < 7) {
            $this->error('لا يجوز الحذف بأقل من 7 أيام لأسباب أمان.');
            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $this->info("🧹 حذف الإشعارات الأقدم من {$cutoff->toDateString()}...");

        $deleted = Notification::where('created_at', '<', $cutoff)->delete();

        $this->info("✅ تم حذف {$deleted} إشعارًا.");

        return self::SUCCESS;
    }
}
