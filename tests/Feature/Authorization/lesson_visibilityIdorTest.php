<?php

namespace Tests\Feature\Authorization;

use App\Models\Concept;
use App\Models\Lesson;
use App\Models\School;
use App\Models\User;
use App\Models\Value;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Object-level / tenant-isolation test for the "lesson_visibility" site in
 * App\Http\Controllers\StudentController::lesson($id)
 * (route: GET /student/lesson/{id}, name: student.lesson).
 *
 * The role:student + school.access middleware only gate the REQUESTER. The
 * {id} route parameter is a free-form lesson id; exists/findOrFail proves the
 * lesson exists, NOT that its content belongs to the actor's school. A lesson
 * hangs off concept->value, and a Value is only visible to schools it is
 * activated for (school_active_values pivot; falls back to all-active when a
 * school has NO custom activations).
 *
 * Without the object-level check a student in school A could open a lesson whose
 * value is activated only for school B — a cross-tenant content leak. The
 * controller closes this with:
 *
 *   $value = optional($lesson->concept)->value;
 *   if (! $value || ! Value::visibleForSchool($user->school_id)
 *           ->whereKey($value->id)->exists()) {
 *       abort(404);
 *   }
 *
 * These tests drive the REAL HTTP route via actingAs()+get() so the middleware
 * stack AND the in-controller authorization run end-to-end. The controller
 * returns 404 (not 403) for an out-of-tenant lesson.
 */
class lesson_visibilityIdorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a lesson whose value is activated EXCLUSIVELY for $school.
     *
     * Activating a custom value for the school flips visibleForSchool() out of
     * its "all active" fallback for THAT school, so a different school will not
     * see this value (and therefore not the lesson under it).
     */
    private function lessonActivatedOnlyFor(School $school): Lesson
    {
        $value = Value::factory()->create(['status' => 'active']);

        // Pivot row => the value is visible to this school, and (because the row
        // exists) only explicitly-activated values are visible to it.
        $school->activeValues()->attach($value->id, [
            'activated_at' => now(),
        ]);

        $concept = Concept::factory()->create(['value_id' => $value->id]);

        return Lesson::factory()->create([
            'concept_id' => $concept->id,
            'status'     => 'active',
        ]);
    }

    /**
     * CROSS-TENANT: a student in school A requests a lesson whose value is
     * activated only for school B. The value is invisible to school A, so the
     * controller must reject with 404 (its abort code for out-of-tenant content).
     */
    public function test_student_cannot_open_lesson_from_another_school(): void
    {
        $schoolB     = School::factory()->create();
        $lessonB     = $this->lessonActivatedOnlyFor($schoolB);

        // Attacker lives in school A. Give school A its OWN custom-activated
        // value so school A is NOT in the legacy "all values active" fallback,
        // making the isolation real rather than incidental.
        $schoolA = School::factory()->create();
        $schoolA->activeValues()->attach(
            Value::factory()->create(['status' => 'active'])->id,
            ['activated_at' => now()],
        );
        $attacker = User::factory()->student($schoolA)->create();

        $response = $this->actingAs($attacker)->get(route('student.lesson', $lessonB->id));

        $response->assertStatus(404);
    }

    /**
     * OWNER: the legitimate student in school B opens the same lesson, whose
     * value IS activated for school B. The object-level check passes and the
     * lesson page renders successfully.
     */
    public function test_owner_student_can_open_own_school_lesson(): void
    {
        $schoolB = School::factory()->create();
        $lessonB = $this->lessonActivatedOnlyFor($schoolB);

        $owner = User::factory()->student($schoolB)->create();

        $response = $this->actingAs($owner)->get(route('student.lesson', $lessonB->id));

        $response->assertStatus(200);
    }

    /**
     * Defense in depth: a non-student (teacher) is bounced by the role:student
     * middleware before the lesson is ever resolved. Confirms the requester gate
     * still stands alongside the new object-level check.
     */
    public function test_non_student_role_is_rejected_by_middleware(): void
    {
        $schoolB = School::factory()->create();
        $lessonB = $this->lessonActivatedOnlyFor($schoolB);

        $teacher = User::factory()->teacher($schoolB)->create();

        $response = $this->actingAs($teacher)->get(route('student.lesson', $lessonB->id));

        $response->assertStatus(403);
    }
}
