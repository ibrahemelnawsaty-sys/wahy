<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * إصلاحات أمن تجربة مدير المدرسة (المراجعة الخصميّة الشاملة):
 *  - حذف الفصل الهدّام (cascade يمحو أنشطة/تسليمات) مُحاصَر.
 */
class SchoolAdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function schoolAdmin(School $school): User
    {
        return User::factory()->create(['role' => 'school_admin', 'school_id' => $school->id]);
    }

    public function test_delete_classroom_blocked_when_it_has_activities(): void
    {
        $school = School::factory()->create();
        $admin = $this->schoolAdmin($school);
        $classroom = Classroom::factory()->create(['school_id' => $school->id]);
        Activity::factory()->create(['classroom_id' => $classroom->id]);

        $this->actingAs($admin)
            ->delete(route('school-admin.classrooms.delete', $classroom->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        // الفصل (وأنشطته/تسليماته) لم يُحذف
        $this->assertDatabaseHas('classrooms', ['id' => $classroom->id]);
    }

    public function test_delete_classroom_allowed_when_empty(): void
    {
        $school = School::factory()->create();
        $admin = $this->schoolAdmin($school);
        $classroom = Classroom::factory()->create(['school_id' => $school->id]);

        $this->actingAs($admin)
            ->delete(route('school-admin.classrooms.delete', $classroom->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('classrooms', ['id' => $classroom->id]);
    }

    public function test_delete_classroom_isolated_to_active_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $admin = $this->schoolAdmin($schoolA);
        $classroomB = Classroom::factory()->create(['school_id' => $schoolB->id]);

        // مدير مدرسة A لا يحذف فصل مدرسة B (عزل)
        $this->actingAs($admin)
            ->delete(route('school-admin.classrooms.delete', $classroomB->id))
            ->assertNotFound();
        $this->assertDatabaseHas('classrooms', ['id' => $classroomB->id]);
    }
}
