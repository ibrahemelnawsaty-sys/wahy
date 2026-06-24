<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherRating extends Model
{
    protected $fillable = [
        'teacher_id',
        'student_id',
        'rating',
        'comment',
    ];

    /**
     * المعلم المقيّم
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * الطالب اللي قيّم
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
