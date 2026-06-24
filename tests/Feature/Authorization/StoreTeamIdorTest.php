<?php

namespace Tests\Feature\Authorization;

use App\Models\Classroom;
use App\Models\School;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Object-level authorization (BOLA / IDOR) coverage for
 * TeacherController::storeTeam (POST teacher/teams -> route teacher.teams.store).
 *
 * The route is gated by role:teacher + school.access for the REQUESTER, but the
 * vulnerability is at the OBJECT level: a teacher must only be able to create a
 * team for a classroom they own and with students who belong to their own school.
 * The controller enforces this via:
 *   - Classroom::where('teacher_id', $user->id)->firstOrFail()  (cross-tenant classroom -> 404)
 *   - abort_unless(leader is a student in $user->school_id, 422) (cross-tenant leader -> 422)
 */
class StoreTeamIdorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * CROSS-TENANT: a teacher in school A tries to create a team for a classroom
     * owned by a teacher in school B. The object (classroom) does not belong to
     * the actor, so the controller's teacher_id firstOrFail() must reject it.
     */
    public function test_cross_tenant_teacher_cannot_create_team_for_other_schools_classroom(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $teacherA = User::factory()->teacher($schoolA)->create();
        $teacherB = User::factory()->teacher($schoolB)->create();

        // The object owned by school B.
        $classroomB = Classroom::factory()->create([
            'school_id'  => $schoolB->id,
            'teacher_id' => $teacherB->id,
        ]);

        // A student in school B (the only "valid" members for classroomB).
        $studentB = User::factory()->student($schoolB)->create();

        $response = $this->actingAs($teacherA)->post(route('teacher.teams.store'), [
            'name'         => 'فريق متسلل',
            'classroom_id' => $classroomB->id,
            'leader_id'    => $studentB->id,
            'member_ids'   => [$studentB->id],
            'description'  => 'محاولة عبر المدارس',
        ]);

        // firstOrFail() on a classroom not owned by teacherA -> 404.
        $response->assertStatus(404);

        // Nothing was created.
        $this->assertDatabaseMissing('teams', ['name' => 'فريق متسلل']);
        $this->assertSame(0, DB::table('team_members')->count());
    }

    /**
     * CROSS-TENANT (member-level): a teacher creates a team for their OWN
     * classroom but tries to inject a student from another school as leader.
     * The school_id scope + abort_unless must reject the foreign leader (422).
     */
    public function test_teacher_cannot_use_foreign_student_as_team_leader(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $teacherA = User::factory()->teacher($schoolA)->create();

        $classroomA = Classroom::factory()->create([
            'school_id'  => $schoolA->id,
            'teacher_id' => $teacherA->id,
        ]);

        // Foreign student (school B) injected as the leader.
        $foreignStudent = User::factory()->student($schoolB)->create();

        $response = $this->actingAs($teacherA)->post(route('teacher.teams.store'), [
            'name'         => 'فريق بقائد دخيل',
            'classroom_id' => $classroomA->id,
            'leader_id'    => $foreignStudent->id,
            'member_ids'   => [$foreignStudent->id],
            'description'  => null,
        ]);

        // Leader is not a student in the teacher's school -> abort_unless 422.
        $response->assertStatus(422);

        $this->assertDatabaseMissing('teams', ['name' => 'فريق بقائد دخيل']);
        $this->assertSame(0, DB::table('team_members')->count());
    }

    /**
     * OWNER: the legitimate teacher creates a team for their own classroom with a
     * student from their own school -> success (redirect to teacher.teams).
     */
    public function test_owner_teacher_can_create_team_for_own_classroom(): void
    {
        $school = School::factory()->create();

        $teacher = User::factory()->teacher($school)->create();

        $classroom = Classroom::factory()->create([
            'school_id'  => $school->id,
            'teacher_id' => $teacher->id,
        ]);

        $leader = User::factory()->student($school)->create();
        $member = User::factory()->student($school)->create();

        $response = $this->actingAs($teacher)->post(route('teacher.teams.store'), [
            'name'         => 'فريق شرعي',
            'classroom_id' => $classroom->id,
            'leader_id'    => $leader->id,
            'member_ids'   => [$leader->id, $member->id],
            'description'  => 'فريق صحيح',
        ]);

        // Controller redirects to teacher.teams on success.
        $response->assertRedirect(route('teacher.teams'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teams', [
            'name'         => 'فريق شرعي',
            'classroom_id' => $classroom->id,
            'created_by'   => $teacher->id,
        ]);

        $team = Team::where('name', 'فريق شرعي')->firstOrFail();

        $this->assertDatabaseHas('team_members', [
            'team_id'    => $team->id,
            'student_id' => $leader->id,
            'role'       => 'leader',
        ]);
        $this->assertDatabaseHas('team_members', [
            'team_id'    => $team->id,
            'student_id' => $member->id,
            'role'       => 'member',
        ]);
    }
}
