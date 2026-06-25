<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolStatisticsCache extends Model
{
    protected $table = 'school_statistics_cache';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'school_id',
        'total_points',
        'previous_points',
        'points_change',
        'monthly_points',
        'platform_rank',
        'country_rank',
        'city_rank',
        'grade_rank',
        'platform_total',
        'country_total',
        'city_total',
        'grade_total',
        'trend',
        'rank_change',
        'country',
        'city',
        'grade_level',
        'badges',
        'calculated_at',
    ];

    protected $casts = [
        'badges' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function entity()
    {
        if ($this->entity_type === 'school') {
            return $this->belongsTo(School::class, 'entity_id');
        }

        return $this->belongsTo(User::class, 'entity_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get trend icon
     */
    public function getTrendIconAttribute(): string
    {
        return match ($this->trend) {
            'up' => '📈',
            'down' => '📉',
            default => '➡️',
        };
    }

    /**
     * Get percentile
     */
    public function getPercentile(string $scope = 'platform'): float
    {
        $rank = $this->{$scope . '_rank'} ?? 0;
        $total = $this->{$scope . '_total'} ?? 0;

        if ($total <= 0 || $rank <= 0) {
            return 0;
        }

        return round((1 - ($rank / $total)) * 100, 1);
    }
}
