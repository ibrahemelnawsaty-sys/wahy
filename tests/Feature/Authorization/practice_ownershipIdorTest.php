<?php

namespace Tests\Feature\Authorization;

use App\Models\Classroom;
use App\Models\PracticeExercise;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Object-level authorization (BOLA / IDOR) coverage for the "practice_ownership"
 * site in StudentController::startExercise (GET student/practice/{id}/start ->
 * route student.practice.start).
 *
 * The route is gated by role:student + school.access for the REQUESTER, but the
 * vulnerability is at the OBJECT level: a PracticeExercise bound to a classroom
 * (classroom_id != null) must only be reachable by a student who is enrolled in
 * THAT classroom. Existence of the exercise id is NOT ownership.
 *
 * The controller enforces this via exerciseBelongsToStudent():
 *   - classroom_id === null  -> public, allowed
 *   - else student must be a member of $exercise->classroom_id (classroom_student)
 *   - otherwise abort(403)
 */
class practice_ownershipIdorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * CROSS-TENANT: a student in school A requests an exercise bound to a
     * classroom owned by school B, in which they are NOT enrolled. The
     * object-level ownership check must reject it with 403.
     */
    public function test_cross_tenant_student_cannot_start_other_classrooms_exercise(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $studentA = User::factory()->student($schoolA)->create();
        $teacherB = User::factory()->teacher($schoolB)->create();

        // The object owned by school B: a classroom + a classroom-bound exercise.
        $classroomB = Classroom::factory()->create([
            'school_id'  => $schoolB->id,
            'teacher_id' => $teacherB->id,
        ]);

        $exerciseB = PracticeExercise::create([
            'teacher_id'   => $teacherB->id,
            'classroom_id' => $classroomB->id,
            'title'        => 'تمرين مدرسة ب',
            'type'         => 'quiz',
            'difficulty'   => 'easy',
            'max_attempts' => 3,
            'is_active'    => true,
            'questions'    => [],
        ]);

        // studentA is NOT enrolled in classroomB -> exerciseBelongsToStudent() false.
        $response = $this->actingAs($studentA)
            ->get(route('student.practice.start', $exerciseB->id));

        $response->assertStatus(403);
    }

    /**
     * OWNER: a student enrolled in the classroom that owns the exercise can start
     * it -> success (200).
     */
    public function test_owner_student_can_start_their_own_classrooms_exercise(): void
    {
        $school = School::factory()->create();

        $student = User::factory()->student($school)->create();
        $teacher = User::factory()->teacher($school)->create();

        $classroom = Classroom::factory()->create([
            'school_id'  => $school->id,
            'teacher_id' => $teacher->id,
        ]);

        // Enroll the student into the classroom that owns the exercise.
        $student->classrooms()->attach($classroom->id, [
            'enrollment_date' => now(),
            'status'          => 'active',
        ]);

        $exercise = PracticeExercise::create([
            'teacher_id'   => $teacher->id,
            'classroom_id' => $classroom->id,
            'title'        => 'تمرين فصلي شرعي',
            'type'         => 'quiz',
            'difficulty'   => 'easy',
            'max_attempts' => 3,
            'is_active'    => true,
            'questions'    => [],
        ]);

        $response = $this->actingAs($student)
            ->get(route('student.practice.start', $exercise->id));

        $response->assertStatus(200);
    }

    /**
     * CROSS-TENANT (public exercise): a school-B teacher's PUBLIC exercise
     * (classroom_id = null) must NOT be reachable by a school-A student. A public
     * exercise has no classroom anchor, so it is bound to its CREATOR's school —
     * it is not globally open across tenants. (Regression for the adversarial bypass.)
     */
    public function test_cross_tenant_student_cannot_start_other_schools_public_exercise(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $studentA = User::factory()->student($schoolA)->create();
        $teacherB = User::factory()->teacher($schoolB)->create();

        $publicExerciseB = PracticeExercise::create([
            'teacher_id'   => $teacherB->id,
            'classroom_id' => null, // public — no classroom anchor
            'title'        => 'تمرين عام مدرسة ب',
            'type'         => 'quiz',
            'difficulty'   => 'easy',
            'max_attempts' => 3,
            'is_active'    => true,
            'questions'    => [],
        ]);

        $this->actingAs($studentA)
            ->get(route('student.practice.start', $publicExerciseB->id))
            ->assertStatus(403);
    }

    /**
     * OWNER (public exercise): a student CAN start a public exercise created by a
     * teacher in their OWN school.
     */
    public function test_student_can_start_public_exercise_of_their_own_school(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $teacher = User::factory()->teacher($school)->create();

        $publicExercise = PracticeExercise::create([
            'teacher_id'   => $teacher->id,
            'classroom_id' => null,
            'title'        => 'تمرين عام مدرستي',
            'type'         => 'quiz',
            'difficulty'   => 'easy',
            'max_attempts' => 3,
            'is_active'    => true,
            'questions'    => [],
        ]);

        $this->actingAs($student)
            ->get(route('student.practice.start', $publicExercise->id))
            ->assertStatus(200);
    }
}
