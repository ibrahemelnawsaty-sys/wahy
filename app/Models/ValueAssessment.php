<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValueAssessment extends Model
{
    protected $fillable = [
        'value_id',
        'student_id',
        'assessment_type',
        'score',
        'answers',
        'completed_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'completed_at' => 'datetime',
    ];

    public function value()
    {
        return $this->belongsTo(Value::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
