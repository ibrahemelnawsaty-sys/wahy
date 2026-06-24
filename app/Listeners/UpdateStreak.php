<?php

namespace App\Listeners;

use App\Events\ActivityCompleted;
use App\Events\StreakUpdated;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class UpdateStreak
{
    /**
     * تحديث الاستمرارية عند إكمال نشاط
     */
    public function handle(ActivityCompleted $event)
    {
        $student = $event->student;
        $streak = $student->streak;

        if (!$streak) {
            // إنشاء streak جديد
            DB::table('streaks')->insert([
                'user_id' => $student->id,
                'current_days' => 1,
                'longest_days' => 1,
                'last_activity_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return;
        }

        $lastActivityDate = \Carbon\Carbon::parse($streak->last_activity_date);
        $today = now()->toDateString();

        // إذا كان آخر نشاط اليوم، لا نعمل شيء
        if ($lastActivityDate->toDateString() === $today) {
            return;
        }

        // إذا كان آخر نشاط بالأمس، نزيد العداد
        if ($lastActivityDate->toDateString() === now()->subDay()->toDateString()) {
            $newCurrentDays = $streak->current_days + 1;
            $newLongestDays = max($streak->longest_days, $newCurrentDays);

            DB::table('streaks')
                ->where('user_id', $student->id)
                ->update([
                    'current_days' => $newCurrentDays,
                    'longest_days' => $newLongestDays,
                    'last_activity_date' => $today,
                    'updated_at' => now(),
                ]);

            // إعطاء مكافأة عند معالم الاستمرارية
            if (in_array($newCurrentDays, [7, 14, 30, 50, 100])) {
                DB::table('coins')->insert([
                    'user_id' => $student->id,
                    'coins' => $newCurrentDays, // مكافأة حسب الأيام
                    'source' => 'streak_milestone',
                    'description' => "إنجاز رائع! {$newCurrentDays} يوم متواصل 🔥",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                event(new \App\Events\StreakUpdated($student, $newCurrentDays, true));
            }
        } else {
            // إعادة تعيين الاستمرارية
            DB::table('streaks')
                ->where('user_id', $student->id)
                ->update([
                    'current_days' => 1,
                    'last_activity_date' => $today,
                    'updated_at' => now(),
                ]);
        }
    }
}
