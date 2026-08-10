<?php

namespace Tests\Feature;

use App\Models\PageBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * حارس انحراف: قائمتا الأنواع في PageBuilder يجب أن تطابقا ما يُصيّره
 * resources/views/pages/show.blade.php فعلاً. القالب بلا `@default` في الفرعين، فنوعٌ
 * خارج الـ`@case` يُنتِج **صفر** مخرجات — واحتسابه «محتوى» يُعيد إنتاج الشاشة البيضاء.
 *
 * إن أضفت `@case` جديدة للقالب (أو حذفت واحدة) فهذا الاختبار يسقط حتى تُحدِّث الثابت.
 */
class PageBuilderRenderableTypesTest extends TestCase
{
    use RefreshDatabase;

    private const TEMPLATE = 'resources/views/pages/show.blade.php';

    /** السطر الفاصل بين فرع sections وفرع الصيغة القديمة: `@else {{-- Fallback for old format --}}`. */
    private function templateHalves(): array
    {
        $source = file_get_contents(base_path(self::TEMPLATE));
        $this->assertNotFalse($source, 'تعذّر قراءة القالب');

        $split = strpos($source, 'Fallback for old format');
        $this->assertNotFalse($split, 'اختفى فاصل «Fallback for old format» من القالب — راجِع بنيته');

        return [substr($source, 0, $split), substr($source, $split)];
    }

    /** @return array<int, string> */
    private function casesIn(string $blade): array
    {
        preg_match_all("/@case\('([a-z0-9_-]+)'\)/i", $blade, $m);
        $types = array_values(array_unique($m[1]));
        sort($types);

        return $types;
    }

    public function test_old_format_block_types_match_the_template(): void
    {
        [, $oldFormat] = $this->templateHalves();

        $expected = PageBuilder::RENDERABLE_BLOCK_TYPES;
        sort($expected);

        $this->assertSame(
            $this->casesIn($oldFormat),
            $expected,
            'PageBuilder::RENDERABLE_BLOCK_TYPES انحرفت عن @case في فرع الصيغة القديمة',
        );
    }

    public function test_sections_component_types_match_the_template(): void
    {
        [$sections] = $this->templateHalves();

        $expected = PageBuilder::RENDERABLE_COMPONENT_TYPES;
        sort($expected);

        $this->assertSame(
            $this->casesIn($sections),
            $expected,
            'PageBuilder::RENDERABLE_COMPONENT_TYPES انحرفت عن @case في فرع sections',
        );
    }

    public function test_page_made_only_of_unrenderable_types_does_not_shadow_the_landing(): void
    {
        // الحالة الحقيقيّة: المحرّر يعرض stats/features/cta ولا `@case` لأيٍّ منها.
        PageBuilder::create([
            'page_name' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'json_data' => [
                ['id' => 'b1', 'type' => 'stats', 'content' => ['items' => []]],
                ['id' => 'b2', 'type' => 'features', 'content' => ['items' => []]],
                ['id' => 'b3', 'type' => 'cta', 'content' => ['title' => 'انضم']],
            ],
            'is_active' => true,
        ]);

        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('فوائد التعلم التعاوني', false);            // الصفحة الثابتة ظهرت
        $res->assertDontSee('min-height: 100vh; padding: 80px 0;', false); // لا غلاف فارغ
    }

    public function test_one_renderable_block_among_unrenderable_ones_is_enough(): void
    {
        PageBuilder::create([
            'page_name' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'json_data' => [
                ['id' => 'b1', 'type' => 'stats', 'content' => ['items' => []]],
                ['id' => 'b2', 'type' => 'heading', 'content' => ['level' => 'h2', 'text' => 'عنوان ظاهر']],
            ],
            'is_active' => true,
        ]);

        $this->get('/')->assertOk()->assertSee('عنوان ظاهر', false);
    }

    public function test_sections_page_with_only_unknown_components_falls_back(): void
    {
        PageBuilder::create([
            'page_name' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'json_data' => ['sections' => [
                ['columns' => 1, 'grid' => [[['type' => 'stats', 'content' => []]]]],
            ]],
            'is_active' => true,
        ]);

        $this->get('/')->assertOk()->assertSee('فوائد التعلم التعاوني', false);
    }

    public function test_sections_page_with_a_known_component_is_served(): void
    {
        PageBuilder::create([
            'page_name' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'json_data' => ['sections' => [
                ['columns' => 1, 'grid' => [[['type' => 'heading', 'content' => ['level' => 'h1', 'text' => 'مرحبا بالأقسام']]]]],
            ]],
            'is_active' => true,
        ]);

        $this->get('/')->assertOk()->assertSee('مرحبا بالأقسام', false);
    }
}
