<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityUserStreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'completed_days',
        'activity_dates',
        'bonus_claimed',
        'last_activity_date',
        'total_bonus_earned',
    ];

    protected $casts = [
        'activity_dates' => 'array',
        'bonus_claimed' => 'boolean',
        'last_activity_date' => 'date',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * تسجيل يوم نشاط جديد — atomic مع lockForUpdate لمنع streak corruption
     * الأيام لا يشترط أن تكون متتالية
     */
    public function recordActivityDay(): bool
    {
        return DB::transaction(function () {
            $today = Carbon::today()->format('Y-m-d');

            // إعادة قراءة الصف تحت قفل لمنع race بين القراءة و الكتابة
            $fresh = static::lockForUpdate()->find($this->id);
            if (!$fresh) {
                return false;
            }

            $dates = $fresh->activity_dates ?? [];
            if (in_array($today, $dates, true)) {
                $this->setRawAttributes($fresh->getAttributes(), true);
                return false;
            }

            $dates[] = $today;
            $fresh->completed_days = count($dates);
            $fresh->activity_dates = $dates;
            $fresh->last_activity_date = $today;
            $fresh->save();

            $this->setRawAttributes($fresh->getAttributes(), true);
            return true;
        }, 3);
    }

    /**
     * فحص وصرف المكافأة (مكافأة ثابتة)
     */
    public function checkAndClaimBonus(int $minDays, int $maxDays, int $bonusPoints): array
    {
        // إذا تم صرف المكافأة مسبقاً في هذه الدورة
        if ($this->bonus_claimed) {
            return [
                'success' => false,
                'message' => 'تم صرف المكافأة مسبقاً',
                'bonus' => 0
            ];
        }
        
        // التحقق من تحقيق الحد الأدنى من الأيام
        if ($this->completed_days >= $minDays) {
            // المكافأة ثابتة
            $finalBonus = $bonusPoints;

            // ملاحظة (P0-1): لا نُنشئ Point هنا — المسؤولية الواحدة في Controller
            // (StudentController::submitActivity) لتجنّب مضاعفة نقاط المكافأة.
            // إنشاء Point هنا كان يُسبب +bonusPoints مرّتين عن كل streak claim.

            // تحديث السجل
            $this->bonus_claimed = true;
            $this->total_bonus_earned = ($this->total_bonus_earned ?? 0) + $finalBonus;
            $this->save();
            
            return [
                'success' => true,
                'message' => "🎉 تهانينا! حصلت على مكافأة الالتزام: {$finalBonus} نقطة",
                'bonus' => $finalBonus,
                'days' => $this->completed_days
            ];
        }
        
        return [
            'success' => false,
            'message' => 'لم تكمل العدد المطلوب من الأيام بعد',
            'bonus' => 0,
            'remaining' => $minDays - $this->completed_days
        ];
    }

    /**
     * إعادة تعيين الـ streak (دورة جديدة)
     */
    public function resetStreak(): void
    {
        $this->completed_days = 0;
        $this->activity_dates = [];
        $this->bonus_claimed = false;
        $this->last_activity_date = null;
        $this->save();
    }

    /**
     * نسبة التقدم
     */
    public function getProgressPercentage(int $targetDays): int
    {
        if ($targetDays <= 0) return 0;
        return min(100, round(($this->completed_days / $targetDays) * 100));
    }

    /**
     * جلب أو إنشاء سجل للمستخدم
     */
    public static function getOrCreate(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'completed_days' => 0,
                'activity_dates' => [],
                'bonus_claimed' => false,
                'total_bonus_earned' => 0,
            ]
        );
    }
}
