<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * أنماط المستخدم: حفظ قسم مصمَّم كنمط قابل لإعادة الاستخدام (سرد/جلب/حذف) + رفض غير الآمن.
 */
class PageBuilderV2UserPatternsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    public function test_save_list_show_and_delete_user_pattern(): void
    {
        $admin = $this->admin();
        $blocks = [['type' => 'heading', 'v' => 1, 'props' => ['text' => 'قسمي المخصّص', 'level' => 'h2']]];

        $id = $this->actingAs($admin)->postJson(route('admin.pb.user-patterns.store'), ['name' => 'قسم مخصّص', 'blocks' => $blocks])
            ->assertCreated()->json('pattern.id');

        $this->actingAs($admin)->getJson(route('admin.pb.user-patterns.index'))
            ->assertOk()->assertJsonFragment(['name' => 'قسم مخصّص']);

        $this->actingAs($admin)->getJson(route('admin.pb.user-patterns.show', $id))
            ->assertOk()->assertJsonPath('pattern.blocks.0.props.text', 'قسمي المخصّص');

        $this->actingAs($admin)->deleteJson(route('admin.pb.user-patterns.destroy', $id))->assertOk();
        $this->assertDatabaseMissing('pb_user_patterns', ['id' => $id]);
    }

    public function test_saving_unsafe_blocks_is_rejected(): void
    {
        $this->actingAs($this->admin())->postJson(route('admin.pb.user-patterns.store'), [
            'name' => 'خبيث',
            'blocks' => [['type' => 'nonexistent-type', 'props' => []]],
        ])->assertStatus(422);

        $this->assertDatabaseCount('pb_user_patterns', 0);
    }
}
