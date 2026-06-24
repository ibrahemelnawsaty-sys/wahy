<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PvpChallenge extends Model
{
    protected $fillable = [
        'title', 'value_id', 'questions', 'time_limit', 'difficulty', 'is_active', 'created_by',
    ];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function matches() { return $this->hasMany(PvpMatch::class, 'challenge_id'); }
    public function value()   { return $this->belongsTo(Value::class, 'value_id'); }

    /**
     * Scope: تحديات متاحة لطالب مدرسة معينة.
     * - إذا value_id = null → تحدي عام لكل المدارس
     * - وإلا → فقط إن كانت القيمة مفعّلة لمدرسة الطالب
     */
    public function scopeAvailableForSchool($query, ?int $schoolId)
    {
        if (!$schoolId) {
            return $query->where('is_active', true)->whereNull('value_id');
        }

        return $query->where('is_active', true)
            ->where(function ($q) use ($schoolId) {
                $q->whereNull('value_id')
                  ->orWhereHas('value', function ($vq) use ($schoolId) {
                      $vq->visibleForSchool($schoolId);
                  });
            });
    }

    public function getFullQuestionsAttribute()
    {
        $ids = $this->questions ?? [];
        return QuestionBank::whereIn('id', $ids)->get();
    }

    public function getQuestionCountAttribute()
    {
        return count($this->questions ?? []);
    }
}
