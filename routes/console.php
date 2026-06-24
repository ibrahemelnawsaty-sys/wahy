<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Task Scheduling - التحقق من مواعيد الواجبات المنزلية
Schedule::command('homework:check-due-dates')
    ->everyFourHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/homework-checks.log'));

// Task Scheduling - النسخ الاحتياطي التلقائي
Schedule::command('backup:run')
    ->daily()
    ->at('02:00') // الساعة 2 صباحاً
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        info('✅ النسخ الاحتياطي التلقائي اكتمل بنجاح');
    })
    ->onFailure(function () {
        error('❌ فشل النسخ الاحتياطي التلقائي');
    })
    ->appendOutputTo(storage_path('logs/backups.log'));

// Task Scheduling - تنظيف النسخ القديمة (أسبوعياً)
Schedule::command('backup:clean')
    ->weekly()
    ->sundays()
    ->at('03:00') // الساعة 3 صباحاً يوم الأحد
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/backup-cleanup.log'));

// Task Scheduling - مراقبة صحة النسخ الاحتياطية (يومياً)
Schedule::command('backup:monitor')
    ->daily()
    ->at('09:00') // الساعة 9 صباحاً
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/backup-monitor.log'));

// Task Scheduling - تحديث جدول إحصائيات المدارس (كل ساعة)
Schedule::command('schools:refresh-stats')
    ->hourly()
    ->withoutOverlapping(10) // قفل 10 دقائق منع تداخل
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/schools-stats.log'));

// Task Scheduling - تنظيف الإشعارات الأقدم من 90 يوم (أسبوعياً)
// يمنع تضخّم جدول notifications مع مرور الوقت
Schedule::command('notifications:cleanup --days=90')
    ->weekly()
    ->sundays()
    ->at('04:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/notifications-cleanup.log'));
