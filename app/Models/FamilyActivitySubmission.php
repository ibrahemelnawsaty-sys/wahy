<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyActivitySubmission extends Model
{
    protected $fillable = [
        'activity_id',
        'student_id',
        'parent_id',
        'submission_data',
        'photos',
        'parent_approved',
        'parent_approved_at',
        'parent_praise',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'submission_data' => 'array',
        'photos' => 'array',
        'parent_approved' => 'boolean',
        'parent_approved_at' => 'datetime',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
