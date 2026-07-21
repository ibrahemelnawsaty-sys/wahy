<?php

namespace Tests\Feature\Admin;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * واجهات إدارة المستخدم: عرض الأدوار الثانوية في القائمة، مربّعات اختيار الأدوار
 * في نموذجَي الإنشاء/التعديل، قفل QR في الإنشاء، وزرّ إظهار تغيير كلمة المرور في التعديل.
 * (منطق التخزين/التطبيع مُغطّى في MultiRoleMultiSchoolTest — هنا نغطّي طبقة العرض.)
 */
class UserFormRolesViewTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_index_shows_secondary_roles_and_multirole_tag(): void
    {
        $school = School::factory()->create();
        User::factory()->create([
            'role' => 'teacher',
            'school_id' => $school->id,
            'secondary_roles' => ['parent'],
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('متعدّد الأدوار')   // مؤشّر تعدّد الأدوار
            ->assertSee('role-secondary')    // شارة الدور الثانويّ
            ->assertSee('ولي أمر');          // اسم الدور الثانويّ
    }

    public function test_index_hides_multirole_tag_for_single_role_user(): void
    {
        $school = School::factory()->create();
        User::factory()->create(['role' => 'teacher', 'school_id' => $school->id, 'secondary_roles' => []]);

        $this->actingAs($this->admin())
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee('متعدّد الأدوار');
    }

    public function test_create_page_renders_role_chips_and_locked_qr(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('secondaryRolesGrid')       // شبكة مربّعات الأدوار
            ->assertSee('roleSchoolWarning')        // تحذير «بلا مدرسة»
            ->assertSee('يُولَّد تلقائياً عند الحفظ') // QR مقفل
            ->assertSee('readonly', false);
    }

    public function test_edit_page_renders_chips_and_password_toggle(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create([
            'role' => 'teacher',
            'school_id' => $school->id,
            'secondary_roles' => ['parent'],
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee('secondaryRolesGrid')
            ->assertSee('togglePwBtn')          // زرّ إظهار تغيير كلمة المرور
            ->assertSee('تغيير كلمة المرور')
            // مربّع دور parent محدَّد مسبقاً (الدور الثانويّ الحاليّ)
            ->assertSee('value="parent"', false);
    }
}
