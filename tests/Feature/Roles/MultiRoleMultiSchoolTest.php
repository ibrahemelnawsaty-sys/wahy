<?php

namespace Tests\Feature\Roles;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * تعدّد الأدوار (إسناد ثانويّ + تبديل) + تعدّد المدارس لمدير المدرسة («مدرسة نشطة» عبر الجلسة).
 * يركّز على الأمن: لا تبديل لمدرسة غير مملوكة، فصل الـpivot عند التنزيل، حفظ الأساسيّة،
 * وتوافق whereJsonContains على SQLite.
 */
class MultiRoleMultiSchoolTest extends TestCase
{
    use RefreshDatabase;

    // ---------------- تعدّد المدارس ----------------

    public function test_single_school_admin_is_backward_compatible(): void
    {
        $s1 = School::factory()->create();
        $sa = User::factory()->create(['role' => 'school_admin', 'school_id' => $s1->id]);

        $this->assertSame([$s1->id], $sa->managedSchoolIds()); // pivot ∪ school_id
        $this->assertFalse($sa->hasMultipleSchools());
        $this->assertSame($s1->id, $sa->activeSchoolId());
    }

    public function test_admin_assigns_multiple_schools_preserving_primary(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $s1 = School::factory()->create();
        $s2 = School::factory()->create();
        $sa = User::factory()->create(['role' => 'school_admin', 'school_id' => $s1->id]);

        $this->actingAs($admin)->put(route('admin.users.update', $sa), [
            'name' => $sa->name,
            'email' => $sa->email,
            'role' => 'school_admin',
            'school_ids' => [$s2->id, $s1->id], // ترتيب DOM يضع s2 أولاً؛ يجب إبقاء s1 الأساسيّة
            'status' => 'active',
        ])->assertRedirect();

        $sa->refresh();
        $this->assertEqualsCanonicalizing([$s1->id, $s2->id], $sa->managedSchoolIds());
        $this->assertTrue($sa->hasMultipleSchools());
        $this->assertSame($s1->id, $sa->school_id, 'المدرسة الأساسيّة القائمة يجب أن تُحفظ');
    }

    public function test_admin_switches_active_school_and_scoping_follows(): void
    {
        $s1 = School::factory()->create();
        $s2 = School::factory()->create();
        $sa = User::factory()->create(['role' => 'school_admin', 'school_id' => $s1->id]);
        $sa->managedSchools()->sync([$s1->id, $s2->id]);

        $this->assertSame($s1->id, $sa->activeSchoolId()); // الافتراضيّة قبل التبديل

        $this->actingAs($sa)->post(route('switch.school', $s2->id))->assertRedirect();

        // الجلسة عُيّنت للمدرسة الثانية — مصدر توسيع SchoolAdminController
        $this->assertSame($s2->id, session('active_school_' . $sa->id));
    }

    public function test_cannot_switch_to_unmanaged_school(): void
    {
        $s1 = School::factory()->create();
        $foreign = School::factory()->create();
        $sa = User::factory()->create(['role' => 'school_admin', 'school_id' => $s1->id]);

        $this->actingAs($sa)->post(route('switch.school', $foreign->id))->assertForbidden();
        $this->assertNull(session('active_school_' . $sa->id));
    }

    public function test_active_school_id_ignores_stale_session_not_in_managed_set(): void
    {
        $s1 = School::factory()->create();
        $foreign = School::factory()->create();
        $sa = User::factory()->create(['role' => 'school_admin', 'school_id' => $s1->id]);

        // جلسة ملوّثة بمدرسة غير مملوكة → يجب تجاهلها والسقوط للأساسيّة
        session(['active_school_' . $sa->id => $foreign->id]);
        $this->assertSame($s1->id, $sa->activeSchoolId());
    }

    public function test_demoting_school_admin_detaches_managed_schools(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $s1 = School::factory()->create();
        $s2 = School::factory()->create();
        $sa = User::factory()->create(['role' => 'school_admin', 'school_id' => $s1->id]);
        $sa->managedSchools()->sync([$s1->id, $s2->id]);
        $this->assertTrue($sa->fresh()->hasMultipleSchools());

        // تنزيل إلى معلّم — يجب تفريغ pivot admin_schools (وإلا بقيت صلاحية تعدّد المدارس عالقة)
        $this->actingAs($admin)->put(route('admin.users.update', $sa), [
            'name' => $sa->name,
            'email' => $sa->email,
            'role' => 'teacher',
            'school_id' => $s1->id,
            'status' => 'active',
        ])->assertRedirect();

        $sa->refresh();
        $this->assertSame(0, $sa->managedSchools()->count());
        $this->assertSame([$s1->id], $sa->managedSchoolIds()); // فقط school_id
        $this->assertFalse($sa->hasMultipleSchools());
    }

    // ---------------- تعدّد الأدوار ----------------

    public function test_admin_assigns_secondary_parent_role_and_user_becomes_multirole(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($admin)->put(route('admin.users.update', $teacher), [
            'name' => $teacher->name,
            'email' => $teacher->email,
            'role' => 'teacher',
            'school_id' => $teacher->school_id,
            'secondary_roles' => ['parent'],
            'status' => 'active',
        ])->assertRedirect();

        $teacher->refresh();
        $this->assertContains('parent', $teacher->secondary_roles);
        $this->assertTrue($teacher->hasMultipleRoles());
        $this->assertEqualsCanonicalizing(['teacher', 'parent'], $teacher->getAllRoles());
    }

    public function test_secondary_role_excludes_primary_and_rejects_switch_to_unowned(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $teacher = User::factory()->teacher()->create();

        // إسناد parent (+ الدور الأساسيّ teacher يجب أن يُستبعد من الثانوية)
        $this->actingAs($admin)->put(route('admin.users.update', $teacher), [
            'name' => $teacher->name, 'email' => $teacher->email, 'role' => 'teacher',
            'school_id' => $teacher->school_id, 'secondary_roles' => ['teacher', 'parent'], 'status' => 'active',
        ])->assertRedirect();
        $this->assertNotContains('teacher', $teacher->fresh()->secondary_roles);

        // التبديل لدور مملوك يعمل، ولدور غير مملوك يُرفض
        $this->actingAs($teacher->fresh())->post(route('switch.role', 'parent'))->assertRedirect();
        $this->actingAs($teacher->fresh())->post(route('switch.role', 'super_admin'))->assertForbidden();
    }

    public function test_parents_query_includes_secondary_parent_users_on_sqlite(): void
    {
        $s1 = School::factory()->create();
        $primaryParent = User::factory()->create(['role' => 'parent', 'school_id' => $s1->id]);
        $teacherParent = User::factory()->create([
            'role' => 'teacher', 'school_id' => $s1->id, 'secondary_roles' => ['parent'],
        ]);
        $plainTeacher = User::factory()->create(['role' => 'teacher', 'school_id' => $s1->id]);

        // نفس منطق SchoolAdminController::parents (whereJsonContains — تحقّق توافق SQLite)
        $ids = User::where('school_id', $s1->id)
            ->where(fn ($q) => $q->where('role', 'parent')->orWhereJsonContains('secondary_roles', 'parent'))
            ->pluck('id')->all();

        $this->assertContains($primaryParent->id, $ids);
        $this->assertContains($teacherParent->id, $ids);
        $this->assertNotContains($plainTeacher->id, $ids);
    }
}
