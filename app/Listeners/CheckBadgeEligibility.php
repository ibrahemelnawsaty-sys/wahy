<?php

namespace App\Listeners;

use App\Events\ActivityCompleted;
use App\Events\LevelUp;
use App\Events\StreakUpdated;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class CheckBadgeEligibility
{
    /**
     * فحص واسناد الأوسمة بناءً على الإنجازات
     */
    public function handle($event)
    {
        if ($event instanceof ActivityCompleted) {
            $this->checkActivityBadges($event->student);
        }
        
        if ($event instanceof LevelUp) {
            $this->checkLevelBadges($event->student, $event->newLevel);
        }
        
        if ($event instanceof StreakUpdated) {
            $this->checkStreakBadges($event->student, $event->streakDays);
        }
    }

    /**
     * أوسمة الأنشطة
     */
    private function checkActivityBadges($student)
    {
        $completedActivities = DB::table('activity_submissions')
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->count();

        $badges = [
            ['activities' => 5, 'badge_id' => 1],   // أول 5 أنشطة
            ['activities' => 10, 'badge_id' => 2],  // 10 أنشطة
            ['activities' => 25, 'badge_id' => 3],  // 25 نشاط
            ['activities' => 50, 'badge_id' => 4],  // 50 نشاط
            ['activities' => 100, 'badge_id' => 5], // 100 نشاط
        ];

        foreach ($badges as $criteria) {
            if ($completedActivities >= $criteria['activities']) {
                $this->awardBadge($student->id, $criteria['badge_id']);
            }
        }
    }

    /**
     * أوسمة المستويات
     */
    private function checkLevelBadges($student, $level)
    {
        $levelBadges = [
            ['level' => 5, 'badge_id' => 6],   // المستوى 5
            ['level' => 10, 'badge_id' => 7],  // المستوى 10
            ['level' => 25, 'badge_id' => 8],  // المستوى 25
            ['level' => 50, 'badge_id' => 9],  // المستوى 50
        ];

        foreach ($levelBadges as $criteria) {
            if ($level >= $criteria['level']) {
                $this->awardBadge($student->id, $criteria['badge_id']);
            }
        }
    }

    /**
     * أوسمة الاستمرارية
     */
    private function checkStreakBadges($student, $streakDays)
    {
        $streakBadges = [
            ['days' => 7, 'badge_id' => 10],   // أسبوع متواصل
            ['days' => 14, 'badge_id' => 11],  // أسبوعين
            ['days' => 30, 'badge_id' => 12],  // شهر كامل
            ['days' => 100, 'badge_id' => 13], // 100 يوم
        ];

        foreach ($streakBadges as $criteria) {
            if ($streakDays >= $criteria['days']) {
                $this->awardBadge($student->id, $criteria['badge_id']);
            }
        }
    }

    /**
     * إسناد الوسام للطالب — atomic لمنع double-award race
     */
    private function awardBadge($studentId, $badgeId)
    {
        $justInserted = DB::transaction(function () use ($studentId, $badgeId) {
            // قفل صفّي على الوسام لمنع race بين الـ events المتزامنة
            $exists = DB::table('user_badges')
                ->where('user_id', $studentId)
                ->where('badge_id', $badgeId)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                return false;
            }

            DB::table('user_badges')->insert([
                'user_id' => $studentId,
                'badge_id' => $badgeId,
                'earned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('coins')->insert([
                'user_id' => $studentId,
                'coins' => 50,
                'source' => 'badge_earned',
                'description' => 'حصلت على وسام جديد!',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        }, 3);

        // إطلاق Event مرة واحدة فقط — خارج المعاملة لتجنب تكرار الإشعار
        if ($justInserted) {
            $user = \App\Models\User::find($studentId);
            $badge = \App\Models\Badge::find($badgeId);
            if ($user && $badge) {
                event(new \App\Events\BadgeEarned($user, $badge));
            }
        }
    }
}
