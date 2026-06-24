<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicYear extends Model
{
    protected $fillable = ['education_level_id', 'name', 'sort_order', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * المرحلة الدراسية التابع لها
     */
    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class);
    }

    /**
     * Scope للسنوات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * ترتيب حسب sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
