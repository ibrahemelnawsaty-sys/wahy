<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\BlockValidator;
use App\PageBuilder\Patterns;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الأنماط الجاهزة (كالووردبريس): كل نمط شجرة كتل من الأنواع المُدرَجة فقط — يجب أن تجتاز
 * BlockValidator (أنواع صالحة + آمنة) وتُصيَّر عبر المُصيِّر الموثوق.
 */
class PageBuilderV2PatternsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_patterns_are_structurally_valid_and_safe(): void
    {
        $patterns = Patterns::all();
        $this->assertNotEmpty($patterns);

        foreach ($patterns as $pat) {
            $this->assertArrayHasKey('key', $pat);
            $this->assertArrayHasKey('blocks', $pat);
            $this->assertIsArray($pat['blocks']);

            $errors = BlockValidator::validate($pat['blocks']);
            $this->assertSame([], $errors, "النمط «{$pat['key']}» فيه كتل غير صالحة/آمنة: " . implode(' | ', $errors));
        }
    }

    public function test_a_pattern_renders_through_trusted_preview(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $pricing = collect(Patterns::all())->firstWhere('key', 'pricing');
        $this->assertNotNull($pricing);

        $html = $this->actingAs($admin)->post(route('admin.pb.preview'), ['body' => $pricing['blocks']])
            ->assertOk()->getContent();

        $this->assertStringContainsString('pb-pricing', $html);
        $this->assertStringContainsString('خطط الأسعار', $html);
    }

    public function test_patterns_shipped_to_editor(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->get(route('admin.pb.ui.create'))->assertOk()
            ->assertSee('pbPatternsBtn', false)
            ->assertSee('أنماط جاهزة');
    }
}
