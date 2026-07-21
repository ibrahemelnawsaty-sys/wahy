<?php

namespace Tests\Feature\Auth;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * الدخول يبدأ دائماً بالدور الأساسيّ.
 * كان دخول أدمن (له دور معلّم ثانويّ بدّل إليه سابقاً) يوجّهه للوحة المعلّم ويُظهر خطأ،
 * لأن User::switchRole يُثبّت active_role في العمود فيبقى عالقاً بعد تسجيل الخروج.
 */
class LoginPrimaryRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_resets_stuck_switched_role_and_lands_on_primary_dashboard(): void
    {
        School::factory()->create();
        $user = User::factory()->create([
            'role' => 'super_admin',
            'secondary_roles' => ['teacher'],
            'active_role' => 'teacher', // مُبدَّل سابقاً وعالق في العمود
            'status' => 'active',
            'two_factor_enabled' => false,
            'password_change_required' => false,
            'password' => Hash::make('secret123'),
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret123'])
            ->assertRedirect(route('dashboard'));

        // العمود الخام صُفِّر عند الدخول (الـaccessor getActiveRoleAttribute يُرجع الدور
        // الأساسيّ عند null، لذا نفحص القيمة الخام لا الوصول عبر الخاصيّة).
        $this->assertNull($user->fresh()->getRawOriginal('active_role'));

        // لوحة الدخول توجّه للوحة السوبر أدمن لا المعلّم
        $this->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_dashboard_falls_back_to_primary_when_active_role_not_owned(): void
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
            'secondary_roles' => [],
            'active_role' => 'teacher', // قيمة عالقة/فاسدة لا يملكها فعلاً
            'status' => 'active',
            'password_change_required' => false,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_dashboard_respects_owned_active_role_within_session(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'role' => 'super_admin',
            'secondary_roles' => ['teacher'],
            'school_id' => $school->id, // دور المعلّم يتطلّب مدرسة كي لا يُحجَب
            'status' => 'active',
            'password_change_required' => false,
        ]);

        // محاكاة تبديل ضمن الجلسة لدور مملوك (وصالح للفتح) — يجب أن يُحترَم
        $this->actingAs($user)
            ->withSession(['active_role_' . $user->id => 'teacher'])
            ->get(route('dashboard'))
            ->assertRedirect(route('teacher.dashboard'));
    }
}
