<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticeExercise extends Model
{
    protected $fillable = [
        'teacher_id', 'classroom_id', 'title', 'description',
        'type', 'difficulty', 'time_limit', 'max_attempts',
        'is_active', 'questions', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function classroom() { return $this->belongsTo(Classroom::class); }
    public function attempts() { return $this->hasMany(PracticeAttempt::class, 'exercise_id'); }

    // الأسئلة الكاملة من بنك الأسئلة
    public function getFullQuestionsAttribute()
    {
        $ids = $this->questions ?? [];
        return QuestionBank::whereIn('id', $ids)->get();
    }

    // هل التمرين متاح حالياً
    public function getIsAvailableAttribute()
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->ends_at && now()->gt($this->ends_at)) return false;
        return true;
    }

    // عدد الطلاب الذين أكملوا
    public function completedCount()
    {
        return $this->attempts()->whereNotNull('completed_at')->count();
    }

    // متوسط الدرجة
    public function averageScore()
    {
        return round($this->attempts()->whereNotNull('completed_at')->avg('score') ?? 0, 1);
    }
}
