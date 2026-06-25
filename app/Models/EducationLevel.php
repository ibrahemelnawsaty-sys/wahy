<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationLevel extends Model
{
    protected $fillable = ['name', 'sort_order', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * السنوات الدراسية التابعة لهذه المرحلة
     */
    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class)->orderBy('sort_order');
    }

    /**
     * المدارس المرتبطة بهذه المرحلة
     */
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_education_level')->withTimestamps();
    }

    /**
     * Scope للمراحل النشطة
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
