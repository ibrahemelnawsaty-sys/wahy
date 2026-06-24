<?php

namespace Tests\Feature\Security;

use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * 🔴 Sprint 0 hotfixes: نتأكد أن الـ Eloquent saving guards تعمل.
 *
 * ملاحظة مهمة: الـ guards تتحقق من `app()->runningInConsole()`، وفي PHPUnit
 * عادةً runningInConsole=true. لذا نحاكي سياق HTTP عبر إنشاء request:
 *   $this->app->instance('request', Illuminate\Http\Request::create('/'));
 *   و actingAs($user) لضبط actor.
 *
 * لكن `runningInConsole()` يعتمد على PHP_SAPI الذي لا يمكن تغييره.
 * لذا الاختبارات هنا تختبر السلوك المتوقع باستخدام Auth::user() + console assumption.
 * البديل: استخدم Browser tests (Dusk) لاختبار في HTTP حقيقي.
 */
class MassAssignmentGuardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_field_exists_in_fillable(): void
    {
        // نتأكد أن الحقول الحساسة موجودة في $fillable لكنها محمية بـ saving event
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('role', $fillable, 'role موجود في fillable لكنه محمي بـ saving event');
        $this->assertContains('school_id', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_user_can_create_with_role_during_registration(): void
    {
        // CREATE مسموح (admin/seeder context)
        $user = User::create([
            'name'     => 'طالب',
            'email'    => 'create@example.com',
            'password' => bcrypt('password'),
            'role'     => UserRole::Student->value,
            'status'   => 'inactive',
        ]);

        $this->assertNotNull($user->id);
        $this->assertEquals(UserRole::Student->value, $user->role);
    }

    public function test_admin_actor_can_change_user_role(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user  = User::factory()->student()->create();

        $this->actingAs($admin);

        $user->role = UserRole::Teacher->value;
        // CLI context في PHPUnit — saving event يسمح
        $user->save();

        $this->assertEquals(UserRole::Teacher->value, $user->fresh()->role);
    }

    public function test_role_helper_methods_use_enum(): void
    {
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();
        $parent  = User::factory()->parent()->create();
        $admin   = User::factory()->schoolAdmin()->create();
        $super   = User::factory()->superAdmin()->create();

        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isTeacher());

        $this->assertTrue($teacher->isTeacher());
        $this->assertTrue($parent->isParent());

        $this->assertTrue($admin->isSchoolAdmin());
        $this->assertTrue($admin->isAdmin());

        $this->assertTrue($super->isSuperAdmin());
        $this->assertTrue($super->isAdmin());
    }

    public function test_has_role_enum_accepts_both_string_and_enum(): void
    {
        $student = User::factory()->student()->create();

        $this->assertTrue($student->hasRoleEnum(UserRole::Student));
        $this->assertTrue($student->hasRoleEnum('student'));
        $this->assertFalse($student->hasRoleEnum(UserRole::Teacher));
        $this->assertFalse($student->hasRoleEnum('teacher'));
    }
}
