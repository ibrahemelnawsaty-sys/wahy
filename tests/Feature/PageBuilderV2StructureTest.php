<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\BlockTree;
use App\PageBuilder\BlockValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * حاوية القسم (section) + حارس عمق التداخل: تُصيَّر أبناؤها بالمُصيِّر الآمن،
 * والتعشيش المُصطنَع العميق يُقصّ (رندرة) ويُرفَض (تحقّق).
 */
class PageBuilderV2StructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_section_renders_nested_children(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $html = $this->actingAs($admin)->post(route('admin.pb.preview'), ['body' => [
            ['type' => 'section', 'props' => ['width' => 'boxed'], 'children' => [
                ['type' => 'heading', 'props' => ['text' => 'عنوان داخل القسم', 'level' => 'h2']],
            ]],
        ]])->assertOk()->getContent();

        $this->assertStringContainsString('pb-section', $html);
        $this->assertStringContainsString('عنوان داخل القسم', $html);
    }

    public function test_prepare_and_validate_cap_deep_nesting(): void
    {
        $node = ['type' => 'heading', 'props' => ['text' => 'DEEP_LEAF']];
        for ($i = 0; $i < 12; $i++) {
            $node = ['type' => 'section', 'props' => [], 'children' => [$node]];
        }

        // الرندرة تُسقط ما يتجاوز الحدّ (لا تصبّه خاماً ولا تنفجر تعاوداً)
        $prepared = BlockTree::prepare([$node]);
        $this->assertStringNotContainsString('DEEP_LEAF', json_encode($prepared));

        // التحقّق يُرجِع خطأ عمق صريحاً للمحرّر
        $errors = BlockValidator::validate([$node]);
        $this->assertNotEmpty(array_filter($errors, fn ($e) => str_contains($e, 'تداخل عميق')));
    }
}
