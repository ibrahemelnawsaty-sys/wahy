<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolPoint extends Model
{
    protected $fillable = [
        'school_id',
        'points',
        'source',
        'description',
        'user_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * إضافة نقاط للمدرسة
     */
    public static function addPoints(int $schoolId, int $points, string $source, ?string $description = null, ?int $userId = null): self
    {
        $record = self::create([
            'school_id' => $schoolId,
            'points' => $points,
            'source' => $source,
            'description' => $description,
            'user_id' => $userId,
        ]);

        // تحديث إجمالي نقاط المدرسة
        $school = School::find($schoolId);
        if ($school) {
            $school->increment('total_points', $points);
        }

        return $record;
    }

    /**
     * إجمالي نقاط المدرسة
     */
    public static function getTotalPoints(int $schoolId): int
    {
        return self::where('school_id', $schoolId)->sum('points');
    }
}
