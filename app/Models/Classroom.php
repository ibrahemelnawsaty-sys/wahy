<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classroom extends Model
{
    use HasFactory;
    protected $fillable = [
        'school_id',
        'teacher_id',
        'name',
        'grade_level',
        'academic_year',
        'capacity',
        'description',
        'status',
    ];

    /**
     * العلاقة مع المدرسة
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * المعلم المسؤول عن الفصل
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * الطلاب في الفصل
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_student', 'classroom_id', 'student_id')
            ->withPivot('enrollment_date', 'status')
            ->withTimestamps();
    }
}
