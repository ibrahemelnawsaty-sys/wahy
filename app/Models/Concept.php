<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Concept extends Model
{
    use HasFactory;

    protected $fillable = [
        'value_id',
        'name',
        'description',
        'order',
    ];

    /**
     * القيمة الأساسية
     */
    public function value(): BelongsTo
    {
        return $this->belongsTo(Value::class);
    }

    /**
     * الدروس تحت هذا المفهوم
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}
