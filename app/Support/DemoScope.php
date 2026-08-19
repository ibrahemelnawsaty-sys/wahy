<?php

namespace App\Support;

use App\Models\User;

/**
 * أدوات استثناء حسابات الديمو من التجميعات — للاستعلامات الخام (DB::table + join/subquery)
 * التي لا تمرّ بنطاقات Eloquent. المصدر الوحيد للحقيقة يبقى users.is_demo / schools.is_demo.
 *
 * لاستعلامات Eloquent استعمل النطاق: User::query()->notDemo() و School::query()->notDemo().
 *
 * (خطّة docs/DEMO_ACCOUNTS_PLAN.md — الدفعة 0.)
 */
class DemoScope
{
    /**
     * أضِف شرط استبعاد مستخدمي الديمو إلى مُنشئ استعلام (على جدول users أو join معه).
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     */
    public static function excludeUsers($query, string $alias = 'users')
    {
        return $query->where("{$alias}.is_demo", false);
    }

    /**
     * أضِف شرط استبعاد مدارس الديمو إلى مُنشئ استعلام (على جدول schools أو join معه).
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     */
    public static function excludeSchools($query, string $alias = 'schools')
    {
        return $query->where("{$alias}.is_demo", false);
    }

    /**
     * نقِّ قائمة معرّفات مستخدمين قادمة من جدول خام (classroom_student/streaks…) بإسقاط الديمو.
     *
     * @param  array<int, int|string>  $ids
     * @return array<int, int>
     */
    public static function notDemoIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return User::whereIn('id', $ids)->where('is_demo', false)->pluck('id')->all();
    }

    /**
     * قصاصة SQL موحّدة لإدراجها داخل whereRaw/selectRaw على subquery يحمل alias للمستخدم.
     * مثال: "... WHERE u.school_id = schools.id" . DemoScope::sqlExclude('u')
     */
    public static function sqlExclude(string $alias = 'u'): string
    {
        return " AND {$alias}.is_demo = 0 ";
    }
}
