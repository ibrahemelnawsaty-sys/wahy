<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class LessonUserStreak extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'completed_days',
        'activity_dates',
        'last_activity_date',
        'bonus_claimed',
        'bonus_claimed_at',
    ];

    protected $casts = [
        'activity_dates' => 'array',
        'last_activity_date' => 'date',
        'bonus_claimed' => 'boolean',
        'bonus_claimed_at' => 'datetime',
    ];

    /**
     * الطالب
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * الدرس
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * تسجيل يوم جديد من إكمال النشاط — atomic لمنع double-counting race
     */
    public function recordActivityDay(): bool
    {
        return DB::transaction(function () {
            $today = now()->toDateString();

            // قراءة تحت قفل لمنع race condition
            $fresh = static::lockForUpdate()->find($this->id);
            if (!$fresh) {
                return false;
            }

            $dates = $fresh->activity_dates ?? [];
            if (in_array($today, $dates, true)) {
                $this->setRawAttributes($fresh->getAttributes(), true);
                return false;
            }

            $dates[] = $today;
            $fresh->activity_dates = $dates;
            $fresh->completed_days = count($dates);
            $fresh->last_activity_date = $today;
            $fresh->save();

            $this->setRawAttributes($fresh->getAttributes(), true);
            return true;
        }, 3);
    }

    /**
     * التحقق من استحقاق المكافأة
     */
    public function checkAndClaimBonus(): array
    {
        // لو حصل على المكافأة من قبل
        if ($this->bonus_claimed) {
            return ['eligible' => false, 'reason' => 'already_claimed'];
        }

        $lesson = $this->lesson;

        // لو الـ streak غير مفعل للدرس
        if (!$lesson->streak_enabled) {
            return ['eligible' => false, 'reason' => 'streak_disabled'];
        }

        // التحقق من الحد الأدنى
        if ($this->completed_days < $lesson->streak_min_days) {
            return [
                'eligible' => false, 
                'reason' => 'min_not_reached',
                'current' => $this->completed_days,
                'required' => $lesson->streak_min_days
            ];
        }

        // مستحق للمكافأة!
        $this->bonus_claimed = true;
        $this->bonus_claimed_at = now();
        $this->save();

        return [
            'eligible' => true,
            'bonus_points' => $lesson->streak_bonus_points,
            'days_completed' => $this->completed_days
        ];
    }

    /**
     * الحصول على نسبة التقدم
     */
    public function getProgressPercentage(): int
    {
        $lesson = $this->lesson;
        
        if (!$lesson->streak_enabled || !$lesson->streak_min_days) {
            return 0;
        }

        return min(100, round(($this->completed_days / $lesson->streak_min_days) * 100));
    }

    /**
     * هل وصل للحد الأقصى؟
     */
    public function hasReachedMax(): bool
    {
        $lesson = $this->lesson;
        
        if (!$lesson->streak_max_days) {
            return false;
        }

        return $this->completed_days >= $lesson->streak_max_days;
    }
}
