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
        // نُبقي صفّ السجلّ دائماً (أثرٌ تدقيقيّ)، لكن **لا نضخّم العدّاد الحيّ** بنقاط حساب ديمو
        // (حارس الكتابة — قرار المالك). getTotalPoints أدناه يستثني صفوف الديمو أيضاً.
        $isDemo = $userId && User::whereKey($userId)->where('is_demo', true)->exists();

        $record = self::create([
            'school_id' => $schoolId,
            'points' => $points,
            'source' => $source,
            'description' => $description,
            'user_id' => $userId,
        ]);

        // تحديث إجمالي نقاط المدرسة (يُتخطّى لحسابات الديمو)
        $school = School::find($schoolId);
        if ($school && ! $isDemo) {
            $school->increment('total_points', $points);
        }

        return $record;
    }

    /**
     * إجمالي نقاط المدرسة — يستثني نقاط حسابات الديمو، ويُبقي نقاط النظام (user_id فارغ).
     */
    public static function getTotalPoints(int $schoolId): int
    {
        return self::where('school_id', $schoolId)
            ->where(function ($q) {
                $q->whereNull('user_id')
                    ->orWhereHas('user', fn ($u) => $u->where('is_demo', false));
            })
            ->sum('points');
    }
}
