<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamActivity extends Model
{
    protected $fillable = [
        'team_id',
        'activity_id',
        'assigned_by',
        'due_date',
        'status',
        'total_score',
        'team_submission',
        'team_file',
        'submitted_at',
        'teacher_feedback'
    ];

    protected $casts = [
        'submitted_at' => 'datetime'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * هل تم تسليم النشاط؟
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'completed' && $this->submitted_at !== null;
    }

    /**
     * هل النشاط قيد التنفيذ؟
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }
}
