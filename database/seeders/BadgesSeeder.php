<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgesSeeder extends Seeder
{
    /**
     * مجموعة شارات نظيفة مبنيّة على الشروط الستّة القياسية.
     * updateOrCreate by name — لا حذف (حتى لا نُيتّم user_badges المكتسبة).
     */
    public function run(): void
    {
        $badges = [
            // lessons_completed
            [
                'name' => 'المبتدئ',
                'description' => 'أكملت أول درس في رحلتك',
                'icon' => '🌱',
                'type' => 'achievement',
                'condition_type' => 'lessons_completed',
                'condition_value' => 1,
                'coins_reward' => 50,
                'color' => '#22C55E',
                'order' => 1,
            ],
            [
                'name' => 'طالب العلم',
                'description' => 'أكملت 10 دروس',
                'icon' => '📚',
                'type' => 'achievement',
                'condition_type' => 'lessons_completed',
                'condition_value' => 10,
                'coins_reward' => 100,
                'color' => '#0EA5E9',
                'order' => 2,
            ],

            // activities_completed
            [
                'name' => 'النشيط',
                'description' => 'أكملت 10 أنشطة',
                'icon' => '⚡',
                'type' => 'achievement',
                'condition_type' => 'activities_completed',
                'condition_value' => 10,
                'coins_reward' => 75,
                'color' => '#F59E0B',
                'order' => 3,
            ],
            [
                'name' => 'المحترف',
                'description' => 'أكملت 50 نشاطاً',
                'icon' => '🎯',
                'type' => 'achievement',
                'condition_type' => 'activities_completed',
                'condition_value' => 50,
                'coins_reward' => 200,
                'color' => '#EF4444',
                'order' => 4,
            ],

            // level
            [
                'name' => 'الصاعد',
                'description' => 'بلغت المستوى 5',
                'icon' => '🚀',
                'type' => 'achievement',
                'condition_type' => 'level',
                'condition_value' => 5,
                'coins_reward' => 100,
                'color' => '#8B5CF6',
                'order' => 5,
            ],
            [
                'name' => 'القمّة',
                'description' => 'بلغت المستوى 10',
                'icon' => '⛰️',
                'type' => 'achievement',
                'condition_type' => 'level',
                'condition_value' => 10,
                'coins_reward' => 250,
                'color' => '#6366F1',
                'order' => 6,
            ],

            // points
            [
                'name' => 'نجم النقاط',
                'description' => 'جمعت 500 نقطة XP',
                'icon' => '⭐',
                'type' => 'achievement',
                'condition_type' => 'points',
                'condition_value' => 500,
                'coins_reward' => 100,
                'color' => '#FACC15',
                'order' => 7,
            ],
            [
                'name' => 'البطل',
                'description' => 'جمعت 1000 نقطة XP',
                'icon' => '🏆',
                'type' => 'achievement',
                'condition_type' => 'points',
                'condition_value' => 1000,
                'coins_reward' => 300,
                'color' => '#F97316',
                'order' => 8,
            ],

            // streak
            [
                'name' => 'اللهب',
                'description' => 'حافظت على سلسلة 7 أيام',
                'icon' => '🔥',
                'type' => 'streak',
                'condition_type' => 'streak',
                'condition_value' => 7,
                'coins_reward' => 100,
                'color' => '#FB7185',
                'order' => 9,
            ],
            [
                'name' => 'المثابر',
                'description' => 'حافظت على سلسلة 30 يوماً',
                'icon' => '💎',
                'type' => 'streak',
                'condition_type' => 'streak',
                'condition_value' => 30,
                'coins_reward' => 300,
                'color' => '#06B6D4',
                'order' => 10,
            ],

            // values_mastered
            [
                'name' => 'المتوّج',
                'description' => 'أتقنت أول قيمة (تاج)',
                'icon' => '👑',
                'type' => 'special',
                'condition_type' => 'values_mastered',
                'condition_value' => 1,
                'coins_reward' => 150,
                'color' => '#EAB308',
                'order' => 11,
            ],
            [
                'name' => 'صاحب التيجان',
                'description' => 'أتقنت 5 قيم',
                'icon' => '🌟',
                'type' => 'special',
                'condition_type' => 'values_mastered',
                'condition_value' => 5,
                'coins_reward' => 400,
                'color' => '#D946EF',
                'order' => 12,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['name' => $badge['name']],
                array_merge($badge, ['status' => 'active']),
            );
        }

        // شارات قديمة (بذرة سابقة) بلا condition_type لا يمنحها المحرّك الجديد أبداً؛
        // نُعطّلها كي لا تظهر للطالب كبطاقات مقفلة دائمة بلا طريقة كسب. لا حذف (نُبقي user_badges المكتسبة).
        Badge::whereNull('condition_type')->update(['status' => 'inactive']);
    }
}
