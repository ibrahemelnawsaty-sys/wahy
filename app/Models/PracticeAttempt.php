<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticeAttempt extends Model
{
    protected $fillable = [
        'student_id', 'exercise_id', 'answers', 'score',
        'total_questions', 'correct_answers', 'time_taken', 'completed_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function exercise()
    {
        return $this->belongsTo(PracticeExercise::class, 'exercise_id');
    }

    public function getPercentageAttribute()
    {
        if ($this->total_questions == 0) {
            return 0;
        }

        return round(($this->correct_answers / $this->total_questions) * 100);
    }
}
