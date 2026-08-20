<?php

namespace Tests\Feature\Security;

use App\Models\Activity;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * عزل الأدوار: مدير النظام (super_admin) لم يعد يخترق تجارب الأدوار بحسابه عبر تغيير الرابط
 * (docs/ROLE_ISOLATION_AUDIT.md). لوحته الخاصّة تبقى تعمل.
 */
class RoleIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_cannot_access_student_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->get(route('student.dashboard'))->assertForbidden();
    }

    public function test_super_admin_cannot_access_teacher_parent_or_school_admin(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->get(route('teacher.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('parent.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('school-admin.dashboard'))->assertForbidden();
    }

    public function test_super_admin_cannot_submit_activity_as_student(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $activity = Activity::factory()->create([
            'type' => 'quiz', 'status' => 'active', 'all_schools_mode' => 'direct', 'lesson_id' => null,
            'questions' => [['type' => 'short_answer', 'correct_answer' => 'x']],
        ]);

        $this->actingAs($admin)
            ->postJson(route('student.activity.submit', $activity->id), ['answer' => 'x'])
            ->assertForbidden();
    }

    public function test_student_still_accesses_own_dashboard(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $this->actingAs($student)->get(route('student.dashboard'))->assertOk();
    }

    public function test_super_admin_still_accesses_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }
}
