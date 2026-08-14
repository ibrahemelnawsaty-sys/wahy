<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\BlockValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * دفعة 2: مكتبة الكتل الغنيّة (كتل S). تتحقّق من: رندرة كلّ نوع جديد عبر المُصيِّر الموثوق،
 * قبول BlockValidator للأنواع الجديدة، وتهريب المحتوى + تقييد الوسوم (لا حقن).
 */
class PageBuilderV2BlocksTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    public function test_new_blocks_render_via_trusted_renderer(): void
    {
        $blocks = [
            ['type' => 'heading', 'props' => ['text' => 'عنوان الاختبار', 'level' => 'h2']],
            ['type' => 'list', 'props' => ['items' => [['text' => 'عنصر أوّل']]]],
            ['type' => 'quote', 'props' => ['text' => 'اقتباسٌ ملهم', 'cite' => 'فلان']],
            ['type' => 'iconlist', 'props' => ['items' => [['icon' => '✅', 'text' => 'بند مهمّ']]]],
            ['type' => 'buttons', 'props' => ['items' => [['text' => 'اشترك', 'link' => '/x', 'style' => 'primary']]]],
            ['type' => 'testimonial', 'props' => ['quote' => 'خدمة رائعة', 'name' => 'سعد']],
            ['type' => 'pricing', 'props' => ['items' => [['name' => 'الباقة', 'price' => '99', 'features' => "ميزة أولى\nميزة ثانية", 'button_text' => 'اشترِ']]]],
            ['type' => 'social', 'props' => ['items' => [['network' => 'facebook', 'url' => 'https://facebook.com/x']]]],
            ['type' => 'table', 'props' => ['headers' => 'الاسم|القيمة', 'rows' => [['cells' => 'أ|1']]]],
            ['type' => 'separator', 'props' => ['style' => 'line']],
        ];

        $html = $this->actingAs($this->admin())
            ->post(route('admin.pb.preview'), ['body' => $blocks])
            ->assertOk()->getContent();

        foreach (['عنوان الاختبار', 'عنصر أوّل', 'اقتباسٌ ملهم', 'بند مهمّ', 'اشترك',
            'خدمة رائعة', 'الباقة', 'ميزة أولى', 'الاسم', 'أ'] as $needle) {
            $this->assertStringContainsString($needle, $html);
        }
        $this->assertStringContainsString('pb-table', $html);
        $this->assertStringContainsString('pb-price-card', $html);
        $this->assertStringContainsString('pb-social-facebook', $html);
    }

    public function test_heading_level_whitelisted_and_text_escaped(): void
    {
        // مستوى خبيث + نصّ فيه وسم: المستوى يُقيَّد لـh2 والنصّ يُهرَّب (لا حقن).
        $html = $this->actingAs($this->admin())
            ->post(route('admin.pb.preview'), ['body' => [
                ['type' => 'heading', 'props' => ['text' => '<script>alert(1)</script>', 'level' => 'script']],
            ]])->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('<h2 class="pb-block pb-heading"', $html);
    }

    public function test_validator_accepts_all_new_block_types(): void
    {
        $blocks = [];
        foreach (['heading', 'list', 'quote', 'separator', 'buttons', 'iconlist', 'testimonial', 'pricing', 'social', 'table'] as $t) {
            $blocks[] = ['type' => $t, 'props' => []];
        }

        $this->assertSame([], BlockValidator::validate($blocks));
    }
}
