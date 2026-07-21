<?php

namespace App\Listeners;

use App\Events\ActivityCompleted;
use App\Services\StreakService;

class UpdateStreak
{
    /**
     * تحديث سلسلة الأيام المتتالية عند إكمال نشاط.
     * (المنطق موحّد في StreakService — كان الكود القديم يكتب أعمدة
     * current_days/longest_days غير الموجودة فيفشل صامتاً ويبقى العدّاد 0.)
     */
    public function handle(ActivityCompleted $event): void
    {
        StreakService::touch($event->student);
    }
}
