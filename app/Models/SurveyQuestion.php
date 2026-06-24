<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends Model
{
    protected $fillable = [
        'survey_id',
        'question_text',
        'question_type',
        'options',
        'option_scores',
        'is_required',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'option_scores' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * الاستبيان
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * الإجابات على هذا السؤال
     */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
