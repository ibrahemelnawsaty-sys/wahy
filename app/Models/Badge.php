<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'criteria',
        'type',
        'status',
        'condition_type',
        'condition_value',
        'coins_reward',
        'order',
        'color',
    ];

    protected $casts = [
        'criteria' => 'array',
        'condition_value' => 'integer',
        'coins_reward' => 'integer',
        'order' => 'integer',
    ];

    /**
     * أنواع الشروط القياسية الستّة (المجموعة المعتمدة).
     * القيمة = تسمية عربية للنوع تُستعمل في الإدارة والعرض.
     */
    public const CONDITION_TYPES = [
        'activities_completed' => 'الأنشطة المكتملة',
        'level' => 'المستوى',
        'streak' => 'سلسلة الأيام',
        'points' => 'نقاط XP',
        'lessons_completed' => 'الدروس المكتملة',
        'values_mastered' => 'القيم المُتقَنة',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    /**
     * نصّ «متى تظهر» مقروء مشتقّ من condition_type + condition_value.
     */
    public function conditionLabel(): string
    {
        $n = (int) $this->condition_value;

        switch ($this->condition_type) {
            case 'activities_completed':
                return "أكمل {$n} نشاطاً";
            case 'level':
                return "بلوغ المستوى {$n}";
            case 'streak':
                return "سلسلة {$n} يوماً";
            case 'points':
                return "اجمع {$n} نقطة";
            case 'lessons_completed':
                return "أكمل {$n} درساً";
            case 'values_mastered':
                return "أتقِن {$n} قيمة";
            default:
                return $this->description ?? 'شرط غير محدَّد';
        }
    }
}
