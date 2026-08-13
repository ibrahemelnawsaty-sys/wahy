<?php

namespace Tests\Feature;

use App\Models\PageBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الصفحة الرئيسية «/» يجب أن تُخدَم دائمًا من landing.blade، ولا يحجبها أيّ صفّ Page Builder
 * بـslug=home (كان فتحُ محرّر الصفحات يُنشئ صفحةً تحجب الصفحة الحقيقيّة — تكرّر مرّتين).
 */
class HomePageSourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_is_served_even_when_a_page_builder_home_exists(): void
    {
        PageBuilder::create([
            'page_name' => 'الرئيسية',
            'slug' => 'home',
            'is_active' => true,
            'json_data' => [['type' => 'hero', 'title' => 'صفحة مبنيّة تجريبيّة يجب ألّا تظهر']],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('كيف نبني القيم؟')                          // محتوى landing.blade الحقيقيّ
            ->assertDontSee('صفحة مبنيّة تجريبيّة يجب ألّا تظهر');    // stub المحرّر لا يُخدَم أبدًا
    }
}
