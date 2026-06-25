<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Value extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'image',
        'order',
        'created_by',
        'status',
    ];

    /**
     * المفاهيم تحت هذه القيمة
     */
    public function concepts(): HasMany
    {
        return $this->hasMany(Concept::class)->orderBy('order');
    }

    /**
     * السوبر أدمن اللي أنشأ القيمة
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * التيجان المرتبطة بهذه القيمة
     */
    public function crowns(): HasMany
    {
        return $this->hasMany(Crown::class);
    }

    /**
     * المدارس التي فُعِّلت لها هذه القيمة
     */
    public function activeSchools()
    {
        return $this->belongsToMany(School::class, 'school_active_values')
            ->withPivot(['activated_by', 'activated_at'])
            ->withTimestamps();
    }

    /**
     * Scope: قيم مفعّلة لمدرسة محددة (مع fallback لكل القيم النشطة لو لم تُخصَّص).
     */
    public function scopeVisibleForSchool($query, ?int $schoolId)
    {
        if (! $schoolId) {
            return $query->where('status', 'active');
        }

        $hasCustom = DB::table('school_active_values')
            ->where('school_id', $schoolId)
            ->exists();

        if (! $hasCustom) {
            return $query->where('status', 'active');
        }

        return $query->where('status', 'active')
            ->whereIn('id', function ($sub) use ($schoolId) {
                $sub->select('value_id')
                    ->from('school_active_values')
                    ->where('school_id', $schoolId);
            });
    }
}
