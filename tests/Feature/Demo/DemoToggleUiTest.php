<?php

namespace Tests\Feature\Demo;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 7 — زرّ التفعيل: تبديل فرديّ + جماعيّ للمدرسة، والحارس الأمنيّ (السوبر أدمن حصراً).
 */
class DemoToggleUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_toggle_user_demo(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $target = User::factory()->create(['role' => 'student', 'is_demo' => false]);

        $this->actingAs($admin)->post(route('admin.users.toggle-demo', $target))->assertRedirect();
        $this->assertTrue($target->fresh()->is_demo, 'السوبر أدمن يُفعّل علم الديمو');

        $this->actingAs($admin)->post(route('admin.users.toggle-demo', $target));
        $this->assertFalse($target->fresh()->is_demo, 'التبديل يعمل في الاتجاهين');
    }

    public function test_non_super_admin_cannot_toggle_user_demo(): void
    {
        $schoolAdmin = User::factory()->create(['role' => 'school_admin']);
        $target = User::factory()->create(['role' => 'student', 'is_demo' => false]);

        $this->actingAs($schoolAdmin)->post(route('admin.users.toggle-demo', $target));

        $this->assertFalse($target->fresh()->is_demo, 'غير السوبر أدمن لا يغيّر علم الديمو');
    }

    public function test_school_toggle_demo_cascades_to_all_users(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $school = School::factory()->create(['is_demo' => false]);
        $u1 = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $u2 = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);

        $this->actingAs($admin)->post(route('admin.schools.toggle-demo', $school))->assertRedirect();

        $this->assertTrue($school->fresh()->is_demo);
        $this->assertTrue($u1->fresh()->is_demo, 'التبديل الجماعيّ يشمل معلّمي المدرسة');
        $this->assertTrue($u2->fresh()->is_demo, 'التبديل الجماعيّ يشمل طلاب المدرسة');
    }

    public function test_new_user_under_demo_school_inherits_flag(): void
    {
        $demoSchool = School::factory()->create(['is_demo' => true]);
        $u = User::factory()->create(['role' => 'student', 'school_id' => $demoSchool->id]);
        $this->assertTrue($u->fresh()->is_demo, 'المستخدم الجديد تحت مدرسة ديمو يرث العلم');
    }
}
