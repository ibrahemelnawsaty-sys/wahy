<?php

namespace Tests\Feature\Authorization;

use App\Models\PvpChallenge;
use App\Models\School;
use App\Models\User;
use App\Models\Value;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Object-level authorization (BOLA / IDOR) coverage for
 * StudentController::joinPvpMatch (POST student/pvp/{challengeId}/join ->
 * route student.pvp.join).
 *
 * The route is gated by role:student + school.access for the REQUESTER, but the
 * vulnerability is at the OBJECT level: validation that the challenge exists and
 * is_active does NOT prove it is available to the actor's school. A challenge
 * may be tied to a Value that is only activated for another school. The
 * controller enforces tenant ownership via:
 *
 *   if (! PvpChallenge::availableForSchool($student->school_id)
 *           ->whereKey($challenge->id)->exists()) {
 *       abort(403);
 *   }
 *
 * PvpChallenge::availableForSchool() defers to Value::scopeVisibleForSchool(),
 * which only restricts a school once that school has at least one row in
 * school_active_values. So the cross-tenant scenario requires:
 *   - school A has its own custom active-values set (its filter engages), and
 *   - the challenge's value is school B's active value, NOT in school A's set.
 */
class joinPvpMatch_scopeIdorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * CROSS-TENANT: a student in school A tries to join a PvP challenge whose
     * value is only activated for school B (not visible to school A). The
     * controller's availableForSchool scope must reject it with 403.
     */
    public function test_cross_tenant_student_cannot_join_challenge_scoped_to_other_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $studentA = User::factory()->student($schoolA)->create();

        // School A activates its OWN value -> its visibility filter now engages
        // (a school with custom active values only sees those values).
        $valueForA = Value::factory()->create(['status' => 'active']);
        DB::table('school_active_values')->insert([
            'school_id' => $schoolA->id,
            'value_id' => $valueForA->id,
            'activated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // The OBJECT owned by school B: a value activated only for school B,
        // and a challenge bound to that value.
        $valueForB = Value::factory()->create(['status' => 'active']);
        DB::table('school_active_values')->insert([
            'school_id' => $schoolB->id,
            'value_id' => $valueForB->id,
            'activated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $challengeB = PvpChallenge::create([
            'title' => 'تحدي مدرسة ب',
            'value_id' => $valueForB->id,
            'questions' => [],
            'time_limit' => 30,
            'is_active' => true,
            'created_by' => User::factory()->superAdmin()->create()->id,
        ]);

        $response = $this->actingAs($studentA)
            ->post(route('student.pvp.join', ['challengeId' => $challengeB->id]));

        // Challenge exists and is_active, but its value is not visible to
        // school A -> object-level guard aborts 403.
        $response->assertStatus(403);

        // No match was spawned for the cross-tenant attempt.
        $this->assertSame(0, DB::table('pvp_matches')->count());
    }

    /**
     * OWNER: a student in school B joins the challenge bound to school B's own
     * activated value -> success (a waiting match is created, JSON 200).
     */
    public function test_owner_student_can_join_challenge_scoped_to_own_school(): void
    {
        $schoolB = School::factory()->create();

        $studentB = User::factory()->student($schoolB)->create();

        $valueForB = Value::factory()->create(['status' => 'active']);
        DB::table('school_active_values')->insert([
            'school_id' => $schoolB->id,
            'value_id' => $valueForB->id,
            'activated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $challengeB = PvpChallenge::create([
            'title' => 'تحدي مدرسة ب',
            'value_id' => $valueForB->id,
            'questions' => [],
            'time_limit' => 30,
            'is_active' => true,
            'created_by' => User::factory()->superAdmin()->create()->id,
        ]);

        $response = $this->actingAs($studentB)
            ->post(route('student.pvp.join', ['challengeId' => $challengeB->id]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => 'waiting',
        ]);

        // The legitimate owner's join created exactly one match.
        $this->assertDatabaseHas('pvp_matches', [
            'challenge_id' => $challengeB->id,
            'player1_id' => $studentB->id,
            'status' => 'waiting',
        ]);
    }
}
