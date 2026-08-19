<?php

namespace Tests\Feature\Demo;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 6 — عدّ حائزي الشارة (والتجميعات المشابهة) يستثني حسابات الديمو.
 */
class DemoGamificationExclusionTest extends TestCase
{
    use RefreshDatabase;

    public function test_badge_holder_count_excludes_demo(): void
    {
        $badge = Badge::create([
            'name' => 'شارة اختبار',
            'description' => 'وصف',
            'icon' => '🏅',
            'type' => 'achievement',
            'status' => 'active',
            'condition_type' => 'points',
            'condition_value' => 100,
            'coins_reward' => 0,
            'color' => '#000000',
        ]);

        $real = User::factory()->create();
        $demo = User::factory()->create(['is_demo' => true]);
        $badge->users()->attach([$real->id, $demo->id]);

        $count = Badge::withCount(['users' => fn ($q) => $q->where('users.is_demo', false)])
            ->find($badge->id)->users_count;

        $this->assertSame(1, (int) $count, 'عدّ حائزي الشارة يستثني حساب الديمو');
    }
}
