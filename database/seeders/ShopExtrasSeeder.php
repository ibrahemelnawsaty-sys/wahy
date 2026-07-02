<?php

namespace Database\Seeders;

use App\Models\ShopItem;
use Illuminate\Database\Seeder;

/**
 * عناصر متجر إضافية: براويز (frames) للأفاتار + قوى (power-ups) ذات أثر فعلي.
 * idempotent — firstOrCreate بالاسم فلا يُكرّر عند إعادة التشغيل.
 */
class ShopExtrasSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // ===== براويز (frames) — type=theme، metadata.kind=frame + حلقة متدرّجة حول الأفاتار =====
            [
                'name' => 'برواز ذهبي',
                'description' => 'إطار ذهبي فاخر حول صورتك الرمزية',
                'type' => 'theme', 'price' => 120, 'icon' => '🥇', 'rarity' => 'rare',
                'status' => 'active', 'order' => 30,
                'metadata' => ['kind' => 'frame', 'anim' => 'gold', 'ring' => 'linear-gradient(135deg,#FFD700,#FFA500)', 'glow' => '0 0 14px rgba(255,193,7,.75)'],
            ],
            [
                'name' => 'برواز نيون',
                'description' => 'إطار نيون متوهّج ودوّار بلون سماوي',
                'type' => 'theme', 'price' => 160, 'icon' => '💠', 'rarity' => 'epic',
                'status' => 'active', 'order' => 31,
                'metadata' => ['kind' => 'frame', 'anim' => 'neon', 'ring' => 'linear-gradient(135deg,#22d3ee,#3b82f6)', 'glow' => '0 0 16px rgba(34,211,238,.8)'],
            ],
            [
                'name' => 'برواز ملكي',
                'description' => 'إطار ملكي بنفسجي ذهبي دوّار للأبطال',
                'type' => 'theme', 'price' => 220, 'icon' => '👑', 'rarity' => 'epic',
                'status' => 'active', 'order' => 32,
                'metadata' => ['kind' => 'frame', 'anim' => 'royal', 'ring' => 'linear-gradient(135deg,#a855f7,#f59e0b)', 'glow' => '0 0 16px rgba(168,85,247,.8)'],
            ],
            [
                'name' => 'برواز ناري',
                'description' => 'إطار ناري متوهّج ودوّار للجريئين',
                'type' => 'theme', 'price' => 200, 'icon' => '🔥', 'rarity' => 'epic',
                'status' => 'active', 'order' => 33,
                'metadata' => ['kind' => 'frame', 'anim' => 'fire', 'ring' => 'linear-gradient(135deg,#ef4444,#f59e0b)', 'glow' => '0 0 16px rgba(239,68,68,.8)'],
            ],

            // ===== قوى (power-ups) ذات أثر فوري فعلي عند الاستخدام =====
            [
                'name' => 'جرعة النقاط',
                'description' => 'استخدمها لتحصل على 40 نقطة فورية ترفع مستواك وترتيبك',
                'type' => 'power_up', 'price' => 60, 'icon' => '⚡', 'rarity' => 'rare',
                'status' => 'active', 'order' => 34,
                'metadata' => ['effect' => 'points', 'amount' => 40],
            ],
            [
                'name' => 'شحنة النقاط الكبرى',
                'description' => 'قوة خرافية: 120 نقطة فورية!',
                'type' => 'power_up', 'price' => 150, 'icon' => '🚀', 'rarity' => 'epic',
                'status' => 'active', 'order' => 35,
                'metadata' => ['effect' => 'points', 'amount' => 120],
            ],
            [
                'name' => 'صندوق الحظ',
                'description' => 'استخدمه لتربح نقاطاً عشوائية (من 15 إلى 150)!',
                'type' => 'power_up', 'price' => 50, 'icon' => '🎁', 'rarity' => 'rare',
                'status' => 'active', 'order' => 36,
                'metadata' => ['effect' => 'mystery', 'min' => 15, 'max' => 150],
            ],
        ];

        foreach ($items as $item) {
            // updateOrCreate: يُحدّث الموجود (مثل إضافة anim للمعادن السابقة) دون تكرار
            ShopItem::updateOrCreate(
                ['name' => $item['name'], 'type' => $item['type']],
                $item,
            );
        }
    }
}
