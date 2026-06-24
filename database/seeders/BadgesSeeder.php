<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgesSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['name' => 'المبتدئ', 'description' => 'أكمل أول درس', 'icon' => '🌱', 'type' => 'achievement'],
            ['name' => 'المتعاون', 'description' => 'شارك في 5 أنشطة جماعية', 'icon' => '🤝', 'type' => 'achievement'],
            ['name' => 'النجم', 'description' => 'حصل على 500 نقطة', 'icon' => '⭐', 'type' => 'achievement'],
            ['name' => 'المثابر', 'description' => 'حافظ على سلسلة 7 أيام', 'icon' => '🔥', 'type' => 'streak'],
            ['name' => 'البطل', 'description' => 'حصل على 1000 نقطة', 'icon' => '🏆', 'type' => 'achievement'],
            ['name' => 'الأمين', 'description' => 'أتم قيمة الأمانة بنسبة 100%', 'icon' => '🤲', 'type' => 'special'],
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }
    }
}
