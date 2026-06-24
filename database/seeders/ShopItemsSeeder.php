<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShopItem;

class ShopItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Avatars - Common
            [
                'name' => 'الأسد الشجاع',
                'description' => 'صورة رمزية تمثل القوة والشجاعة',
                'type' => 'avatar',
                'price' => 50,
                'icon' => '🦁',
                'rarity' => 'common',
                'status' => 'active',
                'order' => 1,
            ],
            [
                'name' => 'النحلة النشيطة',
                'description' => 'صورة رمزية تمثل النشاط والاجتهاد',
                'type' => 'avatar',
                'price' => 50,
                'icon' => '🐝',
                'rarity' => 'common',
                'status' => 'active',
                'order' => 2,
            ],
            [
                'name' => 'الفراشة الجميلة',
                'description' => 'صورة رمزية تمثل الجمال والتحول',
                'type' => 'avatar',
                'price' => 50,
                'icon' => '🦋',
                'rarity' => 'common',
                'status' => 'active',
                'order' => 3,
            ],
            
            // Avatars - Rare
            [
                'name' => 'النسر الحكيم',
                'description' => 'صورة رمزية نادرة تمثل الحكمة والرؤية',
                'type' => 'avatar',
                'price' => 100,
                'icon' => '🦅',
                'rarity' => 'rare',
                'status' => 'active',
                'order' => 4,
            ],
            [
                'name' => 'الدولفين الذكي',
                'description' => 'صورة رمزية نادرة تمثل الذكاء والمرح',
                'type' => 'avatar',
                'price' => 100,
                'icon' => '🐬',
                'rarity' => 'rare',
                'status' => 'active',
                'order' => 5,
            ],
            
            // Badges - Common
            [
                'name' => 'شارة النجمة',
                'description' => 'شارة تمثل التميز والإنجاز',
                'type' => 'badge',
                'price' => 75,
                'icon' => '⭐',
                'rarity' => 'common',
                'status' => 'active',
                'order' => 6,
            ],
            [
                'name' => 'شارة القلب',
                'description' => 'شارة تمثل الحب والعطاء',
                'type' => 'badge',
                'price' => 75,
                'icon' => '❤️',
                'rarity' => 'common',
                'status' => 'active',
                'order' => 7,
            ],
            
            // Badges - Epic
            [
                'name' => 'شارة التاج الذهبي',
                'description' => 'شارة أسطورية للمتميزين',
                'type' => 'badge',
                'price' => 200,
                'icon' => '👑',
                'rarity' => 'epic',
                'status' => 'active',
                'order' => 8,
            ],
            [
                'name' => 'شارة الكأس',
                'description' => 'شارة أسطورية للفائزين',
                'type' => 'badge',
                'price' => 200,
                'icon' => '🏆',
                'rarity' => 'epic',
                'status' => 'active',
                'order' => 9,
            ],
            
            // Themes - Rare
            [
                'name' => 'ثيم الليل الساحر',
                'description' => 'ثيم داكن أنيق وجميل',
                'type' => 'theme',
                'price' => 150,
                'icon' => '🌙',
                'rarity' => 'rare',
                'status' => 'active',
                'order' => 10,
                'metadata' => ['theme_id' => 'dark_magic'],
            ],
            [
                'name' => 'ثيم الغابة الخضراء',
                'description' => 'ثيم طبيعي منعش',
                'type' => 'theme',
                'price' => 150,
                'icon' => '🌳',
                'rarity' => 'rare',
                'status' => 'active',
                'order' => 11,
                'metadata' => ['theme_id' => 'green_forest'],
            ],
            
            // Power-ups - Epic
            [
                'name' => 'مضاعفة النقاط',
                'description' => 'ضاعف نقاطك لمدة ساعة واحدة',
                'type' => 'power_up',
                'price' => 250,
                'icon' => '⚡',
                'rarity' => 'epic',
                'status' => 'active',
                'order' => 12,
                'metadata' => ['duration_hours' => 1, 'multiplier' => 2],
            ],
            [
                'name' => 'تعزيز العملات',
                'description' => 'احصل على عملات إضافية لمدة ساعة',
                'type' => 'power_up',
                'price' => 250,
                'icon' => '💰',
                'rarity' => 'epic',
                'status' => 'active',
                'order' => 13,
                'metadata' => ['duration_hours' => 1, 'bonus_percentage' => 50],
            ],
            
            // Special - Legendary
            [
                'name' => 'التاج الماسي',
                'description' => 'تاج خرافي للأبطال الحقيقيين',
                'type' => 'special',
                'price' => 500,
                'icon' => '💎',
                'rarity' => 'legendary',
                'status' => 'active',
                'order' => 14,
            ],
            [
                'name' => 'النجمة الذهبية',
                'description' => 'نجمة خرافية تضيء طريقك',
                'type' => 'special',
                'price' => 500,
                'icon' => '🌟',
                'rarity' => 'legendary',
                'status' => 'active',
                'order' => 15,
            ],
            
            // Limited Time Items
            [
                'name' => 'هدية العيد الخاصة',
                'description' => 'هدية محدودة الوقت بمناسبة العيد',
                'type' => 'special',
                'price' => 100,
                'icon' => '🎁',
                'rarity' => 'rare',
                'status' => 'active',
                'is_limited' => true,
                'available_until' => now()->addDays(30),
                'stock' => 50,
                'order' => 16,
            ],
        ];

        foreach ($items as $item) {
            ShopItem::create($item);
        }
    }
}
