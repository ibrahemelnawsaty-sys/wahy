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
 * IDOR / BOLA guard for TeacherController::updateTeam.
 *
 * Route: POST /teacher/teams/{id} (name: teacher.teams.update)
 * Middleware: role:teacher + school.access (gates the REQUESTER only).
 *
 * Object-level check under test: the controller resolves the {id} team via
 *   Team::where('id', $id)
 *       ->whereIn('classroom_id', $user->teachingClassrooms()->pluck('classrooms.id'))
 *       ->firstOrFail();
 * so a team whose classroom is taught by ANOTHER teacher (another school)
 * is never resolved for the acting teacher -> firstOrFail() => 404.
 */
class updateTeamIdorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a school, a teacher in it, a classroom that teacher teaches,
     * a student in that school, and a team in that classroom whose only
     * member (leader) is the student. Returns [teacher, team, student].
     *
     * @return array{0: User, 1: Team, 2: User}
     */
    private function makeTenant(): array
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();

        $classroom = Classroom::factory()->create([
            'school_id'  => $school->id,
            'teacher_id' => $teacher->id,
        ]);

        $student = User::factory()->student($school)->create();
        $classroom->students()->attach($student->id);

        $team = Team::create([
            'name'         => 'فريق ' . $school->id,
            'classroom_id' => $classroom->id,
            'created_by'   => $teacher->id,
            'status'       => 'active',
        ]);

        DB::table('team_members')->insert([
            'team_id'    => $team->id,
            'student_id' => $student->id,
            'role'       => 'leader',
            'joined_at'  => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$teacher, $team, $student];
    }

    /**
     * CROSS-TENANT: teacher in school A tries to update a team owned by
     * school B's classroom. The team is never resolved for actor A, so the
     * controller's firstOrFail() returns 404.
     */
    public function test_cross_tenant_teacher_cannot_update_other_schools_team(): void
    {
        [$teacherA] = $this->makeTenant();
        [, $teamB, $studentB] = $this->makeTenant();

        $response = $this->actingAs($teacherA)->post('/teacher/teams/' . $teamB->id, [
            'name'         => 'اسم مخترق',
            'description'  => 'محاولة عبر المدارس',
            'leader_id'    => $studentB->id,
            'member_ids'   => [$studentB->id],
            'status'       => 'active',
        ]);

        $response->assertStatus(404);

        // The target team was NOT mutated by the cross-tenant actor.
        $this->assertDatabaseHas('teams', [
            'id'   => $teamB->id,
            'name' => $teamB->name,
        ]);
        $this->assertDatabaseMissing('teams', [
            'id'   => $teamB->id,
            'name' => 'اسم مخترق',
        ]);
    }

    /**
     * OWNER: the teacher who teaches the team's classroom updates their own
     * team with leader/members from their own school -> redirect (success).
     */
    public function test_owner_teacher_can_update_own_team(): void
    {
        [$teacherA, $teamA, $studentA] = $this->makeTenant();

        $response = $this->actingAs($teacherA)->post('/teacher/teams/' . $teamA->id, [
            'name'         => 'اسم محدّث',
            'description'  => 'تحديث مشروع',
            'leader_id'    => $studentA->id,
            'member_ids'   => [$studentA->id],
            'status'       => 'active',
        ]);

        $response->assertRedirect(route('teacher.teams.show', $teamA->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teams', [
            'id'   => $teamA->id,
            'name' => 'اسم محدّث',
        ]);
    }
}
