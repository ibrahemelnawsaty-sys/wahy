<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\ShopItem;
use Database\Seeders\BadgesSeeder;
use Database\Seeders\ShopExtrasSeeder;
use Database\Seeders\ShopItemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * كتالوج الشارات والمتجر: يجب أن يُبذر بالكامل ويكون idempotent (إعادة التشغيل لا تُكرّر)
 * — لأنّه يُشغَّل على الإنتاج لاستعادة ما اختفى بعد نقل الخادم.
 */
class CatalogSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_catalog_seeds_fully_and_is_idempotent(): void
    {
        (new ShopItemsSeeder())->run();
        (new ShopExtrasSeeder())->run();

        $count = ShopItem::count();
        $this->assertSame(23, $count, '16 عنصر أساسيّ + 7 إضافيّ');
        $this->assertTrue(ShopItem::where('name', 'الأسد الشجاع')->exists());
        $this->assertTrue(ShopItem::where('name', 'برواز ذهبي')->exists());

        // إعادة التشغيل ⟶ لا تكرار
        (new ShopItemsSeeder())->run();
        (new ShopExtrasSeeder())->run();
        $this->assertSame($count, ShopItem::count(), 'إعادة البذر يجب ألّا تُكرّر');
    }

    public function test_badges_seed_and_are_idempotent(): void
    {
        (new BadgesSeeder())->run();
        $count = Badge::where('status', 'active')->count();
        $this->assertGreaterThanOrEqual(12, $count);

        (new BadgesSeeder())->run();
        $this->assertSame($count, Badge::where('status', 'active')->count());
    }
}
