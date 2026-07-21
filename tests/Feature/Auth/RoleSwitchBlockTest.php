<?php

namespace Tests\Feature\Auth;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * عند تبديل المستخدم لدور ثانويّ معطوب (مثلاً مرتبط بمدرسة والمستخدم بلا مدرسة):
 * تظهر صفحة توضّح سبب عدم الفتح مع خيار العودة للحساب الأساسيّ — بدل لوحة تنكسر.
 */
class RoleSwitchBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_to_school_scoped_role_without_school_shows_reason(): void
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
            'secondary_roles' => ['teacher'],
            'school_id' => null,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('switch.role', 'teacher'))
            ->assertOk()
            ->assertSee('تعذّر فتح هذا الدور')
            ->assertSee('غير مرتبط بأيّ مدرسة')
            ->assertSee('العودة إلى حسابي الأساسيّ');

        // لم يُبدَّل الدور فعلاً
        $this->assertNull($user->fresh()->getRawOriginal('active_role'));
    }

    public function test_switching_to_role_with_school_succeeds(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'role' => 'super_admin',
            'secondary_roles' => ['teacher'],
            'school_id' => $school->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('switch.role', 'teacher'))
            ->assertRedirect('/teacher/dashboard');

        $this->assertSame('teacher', $user->fresh()->getRawOriginal('active_role'));
    }

    public function test_reset_to_primary_clears_active_role(): void
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
            'secondary_roles' => ['teacher'],
            'active_role' => 'teacher',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->withSession(['active_role_' . $user->id => 'teacher'])
            ->post(route('switch.role.reset'))
            ->assertRedirect(route('dashboard'));

        $this->assertNull($user->fresh()->getRawOriginal('active_role'));
    }

    public function test_dashboard_shows_reason_for_blocked_owned_secondary_role(): void
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
            'secondary_roles' => ['teacher'],
            'school_id' => null,
            'status' => 'active',
            'password_change_required' => false,
        ]);

        // دور معلّم مملوك ومُبدَّل إليه ضمن الجلسة، لكن بلا مدرسة → صفحة السبب
        $this->actingAs($user)
            ->withSession(['active_role_' . $user->id => 'teacher'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('تعذّر فتح هذا الدور')
            ->assertSee('العودة إلى حسابي الأساسيّ');
    }

    public function test_primary_role_is_never_blocked_on_dashboard(): void
    {
        // مدير مدرسة بلا مدرسة (دوره الأساسيّ) — لا يُحجَب بل يذهب للوحته (لا يُقفَل خارجاً)
        $user = User::factory()->create([
            'role' => 'school_admin',
            'school_id' => null,
            'status' => 'active',
            'password_change_required' => false,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('school-admin.dashboard'));
    }
}
