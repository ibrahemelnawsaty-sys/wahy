<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionBank extends Model
{
    protected $table = 'question_bank';

    protected $fillable = [
        'created_by',
        'lesson_id',
        'title',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'difficulty',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'usage_count',
    ];

    protected $casts = [
        'options' => 'array',
        'approved_at' => 'datetime',
        'points' => 'integer',
        'usage_count' => 'integer',
    ];

    /**
     * المعلم الذي أنشأ السؤال
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * الدرس المرتبط بالسؤال
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * السوبر أدمن الذي وافق على السؤال
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * الموافقة على السؤال
     */
    public function approve($userId)
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * رفض السؤال
     */
    public function reject($userId, $reason = null)
    {
        return $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
