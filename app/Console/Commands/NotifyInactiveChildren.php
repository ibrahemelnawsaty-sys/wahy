<?php

namespace App\Console\Commands;

use App\Mail\ParentChildInactiveMail;
use App\Models\User;
use App\Services\Mail\MailGate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * تنبيه أولياء الأمور عند خمول أبنائهم (خطّة أدوار البريد P7).
 * يستهدف الطلاب الذين كان آخر نشاط لهم قبل --days يومًا **بالضبط** (لتفادي تكرار التنبيه يوميًّا).
 * يُجدوَل يوميًّا. يحترم أعلام النوع وإلغاء الاشتراك عبر MailGate.
 */
class NotifyInactiveChildren extends Command
{
    protected $signature = 'emails:parent-inactive-children {--days=2}';

    protected $description = 'تنبيه أولياء الأمور عند خمول أبنائهم عدّة أيّام';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $target = now()->subDays($days)->toDateString();

        if (! \Illuminate\Support\Facades\Schema::hasTable('streaks')) {
            $this->warn('لا يوجد جدول streaks — تُخطّى.');

            return self::SUCCESS;
        }

        $studentIds = DB::table('streaks')->whereDate('last_activity_date', $target)->pluck('user_id');
        if ($studentIds->isEmpty()) {
            $this->info('لا طلاب بلغوا الخمول اليوم.');

            return self::SUCCESS;
        }

        $students = User::whereIn('id', $studentIds)->where('role', 'student')->with('parents')->get();
        $sent = 0;

        foreach ($students as $student) {
            foreach ($student->parents as $parent) {
                if ($parent->email && MailGate::send($parent, 'parent_child_inactive', 'event', new ParentChildInactiveMail($parent, $student, $days))) {
                    $sent++;
                }
            }
        }

        $this->info("تنبيه الخمول: {$students->count()} طالب خامل، {$sent} بريد لأولياء الأمور.");

        return self::SUCCESS;
    }
}
