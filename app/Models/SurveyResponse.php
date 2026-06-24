<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    protected $fillable = [
        'survey_id',
        'user_id',
        'answers',
        'phase', // pre = قبلي, post = بعدي
        'completed_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * الاستبيان
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * المستخدم الذي أجاب
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
