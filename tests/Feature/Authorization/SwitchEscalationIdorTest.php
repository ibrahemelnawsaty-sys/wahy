<?php

namespace Tests\Feature\Authorization;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Object-level / privilege-escalation guard for RoleSwitchController::switch.
 *
 * Route: POST /switch-role/{role}  (name: switch.role)
 *
 * The {role} param is the "target object". The controller MUST reject any
 * switch to a role the actor does not own (primary `role` + `secondary_roles`
 * via getAllRoles()). exists-style "the role string is valid" is NOT enough —
 * ownership of that role by the actor is what's enforced.
 */
class SwitchEscalationIdorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * CROSS-TENANT / ESCALATION: a student (school A) whose only role is
     * `student` tries to switch to `teacher` — a role owned by users in
     * other tenants but NOT by this actor. Must be rejected with 403.
     */
    public function test_actor_cannot_escalate_to_role_they_do_not_own(): void
    {
        $schoolA = School::factory()->create();
        $student = User::factory()->student($schoolA)->create([
            'secondary_roles' => null,
        ]);

        $response = $this->actingAs($student)
            ->post('/switch-role/teacher');

        $response->assertStatus(403);

        // The active role must remain unchanged (no escalation leaked through).
        $this->assertSame('student', $student->fresh()->getCurrentRole());
    }

    /**
     * A lacking-role actor cannot escalate even to school_admin / super_admin.
     */
    public function test_actor_cannot_escalate_to_admin_roles(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create([
            'secondary_roles' => null,
        ]);

        $this->actingAs($teacher)
            ->post('/switch-role/school_admin')
            ->assertStatus(403);

        $this->actingAs($teacher)
            ->post('/switch-role/super_admin')
            ->assertStatus(403);

        $this->assertSame('teacher', $teacher->fresh()->getCurrentRole());
    }

    /**
     * OWNER: a user who legitimately holds the target role as a SECONDARY role
     * switches successfully — redirect (302) to the new dashboard with success.
     */
    public function test_owner_can_switch_to_a_role_they_hold(): void
    {
        $school = School::factory()->create();
        // Primary teacher who also legitimately owns the `parent` role.
        $user = User::factory()->teacher($school)->create([
            'secondary_roles' => ['parent'],
        ]);

        $response = $this->actingAs($user)
            ->post('/switch-role/parent');

        $response->assertRedirect('/parent/dashboard');
        $response->assertSessionHas('success');

        $this->assertSame('parent', $user->fresh()->getCurrentRole());
    }

    /**
     * OWNER: switching to one's own PRIMARY role also succeeds.
     */
    public function test_owner_can_switch_to_their_primary_role(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->teacher($school)->create([
            'secondary_roles' => null,
        ]);

        $response = $this->actingAs($user)
            ->post('/switch-role/teacher');

        $response->assertRedirect('/teacher/dashboard');
        $response->assertSessionHas('success');

        $this->assertSame('teacher', $user->fresh()->getCurrentRole());
    }
}
