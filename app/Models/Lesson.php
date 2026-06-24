<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Survey;

class Lesson extends Model
{
    use HasFactory;
    protected $fillable = [
        'concept_id',
        'title',
        'content',
        'type',
        'video_url',
        'audio_url',
        'video_file',
        'audio_file',
        'images',
        'duration',
        'points',
        'order',
        'status',
        // إعدادات الـ Streak
        'streak_min_days',
        'streak_max_days',
        'streak_bonus_points',
        'streak_enabled',
    ];

    protected $casts = [
        'images' => 'array',
        'streak_enabled' => 'boolean',
    ];

    /**
     * المفهوم الأساسي
     */
    public function concept(): BelongsTo
    {
        return $this->belongsTo(Concept::class);
    }

    /**
     * الأنشطة تحت هذا الدرس
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderBy('order');
    }

    /**
     * سجلات streak الطلاب لهذا الدرس
     */
    public function userStreaks(): HasMany
    {
        return $this->hasMany(LessonUserStreak::class);
    }

    /**
     * الحصول على streak طالب معين
     */
    public function getUserStreak($userId): ?LessonUserStreak
    {
        return $this->userStreaks()->where('user_id', $userId)->first();
    }

    /**
     * هل نظام الـ streak مفعل لهذا الدرس؟
     */
    public function hasStreakEnabled(): bool
    {
        return $this->streak_enabled && $this->streak_min_days > 0;
    }

    /**
     * استبيانات التقييم المرتبطة بهذا الدرس
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
