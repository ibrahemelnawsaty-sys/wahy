<?php

namespace Tests\Feature\Authorization;

use App\Enums\UserRole;
use App\Models\BulkMessage;
use App\Models\BulkMessageRecipient;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Object-level / tenant-isolation test for the "null_school_cross_leak" site in
 * App\Http\Controllers\BulkMessageController::send().
 *
 * A school_admin may only broadcast inside their OWN school. The controller
 * forces $validated['school_id'] = $sender->school_id. If the sender has a
 * null school_id, the school-scoped query (where('school_id', null) => IS NULL)
 * would otherwise target EVERY schoolless user across the whole platform — a
 * cross-tenant data leak. The controller guards this with abort(403).
 *
 * These tests drive the REAL POST /messages/bulk/send route through the HTTP
 * client so the auth middleware + in-controller authorization run end-to-end.
 */
class null_school_cross_leakIdorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * CROSS-TENANT: a school_admin with NO school (school_id = null) tries to
     * broadcast to school_* recipients. This is the null_school_cross_leak
     * vector — without the guard it would leak to schoolless users in OTHER
     * tenants. Expect 403 AND zero rows written (no message, no recipients).
     */
    public function test_school_admin_with_null_school_cannot_broadcast_cross_tenant(): void
    {
        // Attacker: school_admin whose account is not linked to any school.
        $attacker = User::factory()->create([
            'role' => UserRole::SchoolAdmin->value,
            'school_id' => null,
        ]);

        // Victim tenant (school B) the attacker explicitly tries to target by
        // passing school B's id. The controller must reject the schoolless
        // sender rather than honour the attacker-supplied school_id.
        $schoolB = School::factory()->create();
        $victimB = User::factory()->student($schoolB)->create();
        // Also a schoolless victim — exactly the population a null IS NULL query
        // would wrongly broadcast to if the override were missing.
        $victimNull = User::factory()->create([
            'role' => UserRole::Student->value,
            'school_id' => null,
        ]);

        $response = $this->actingAs($attacker)->post(route('messages.bulk.send'), [
            'recipient_type' => 'school_all',
            // Attacker-supplied target tenant — must NOT be honoured.
            'school_id' => $schoolB->id,
            'subject' => 'cross tenant leak attempt',
            'message' => 'should never be delivered',
        ]);

        // Controller aborts with 403 for a schoolless bulk sender.
        $response->assertStatus(403);

        // And critically: nothing was persisted, so the victim received nothing.
        $this->assertDatabaseCount('bulk_messages', 0);
        $this->assertDatabaseCount('bulk_message_recipients', 0);
        $this->assertDatabaseMissing('bulk_message_recipients', [
            'user_id' => $victimB->id,
        ]);
        $this->assertDatabaseMissing('bulk_message_recipients', [
            'user_id' => $victimNull->id,
        ]);
    }

    /**
     * OWNER: a school_admin WITH a valid school broadcasts to their own school.
     * Expect success (302 redirect with flash success), recipients created only
     * for their own school, and a member of another tenant is NOT reached.
     */
    public function test_school_admin_can_broadcast_to_own_school(): void
    {
        $schoolA = School::factory()->create();
        $admin = User::factory()->schoolAdmin($schoolA)->create();

        // Member of the admin's own school — should receive the message.
        $ownStudent = User::factory()->student($schoolA)->create();

        // Member of a different tenant (school B) — must NOT receive it.
        $schoolB = School::factory()->create();
        $otherStudent = User::factory()->student($schoolB)->create();

        $response = $this->actingAs($admin)->post(route('messages.bulk.send'), [
            'recipient_type' => 'school_all',
            // IDOR attempt: admin passes ANOTHER school's id. The controller
            // must force the sender's own school_id (schoolA) regardless.
            'school_id' => $schoolB->id,
            'subject' => 'own school broadcast',
            'message' => 'hello my school',
        ]);

        // Successful send redirects to the bulk index with a success flash.
        $response->assertRedirect(route('messages.bulk.index'));
        $response->assertSessionHas('success');

        // Exactly one bulk message, scoped to the admin's own school.
        $this->assertDatabaseHas('bulk_messages', [
            'sender_id' => $admin->id,
            'recipient_type' => 'school_all',
            'school_id' => $schoolA->id,
            'subject' => 'own school broadcast',
        ]);

        $message = BulkMessage::where('sender_id', $admin->id)->firstOrFail();

        // Own-school student is a recipient; cross-tenant student is not.
        $this->assertDatabaseHas('bulk_message_recipients', [
            'bulk_message_id' => $message->id,
            'user_id' => $ownStudent->id,
        ]);
        $this->assertDatabaseMissing('bulk_message_recipients', [
            'bulk_message_id' => $message->id,
            'user_id' => $otherStudent->id,
        ]);

        // No recipient outside the admin's school leaked in.
        $leaked = BulkMessageRecipient::where('bulk_message_id', $message->id)
            ->whereIn('user_id', [$otherStudent->id])
            ->exists();
        $this->assertFalse($leaked, 'A cross-tenant user was wrongly included as a recipient.');
    }

    /**
     * Defense in depth: a non-privileged user (student) lacking the bulk-sender
     * role is rejected by authorizeBulkSender() with 403 on the same route.
     */
    public function test_non_privileged_user_cannot_send_bulk_messages(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->post(route('messages.bulk.send'), [
            'recipient_type' => 'all',
            'subject' => 'nope',
            'message' => 'nope',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('bulk_messages', 0);
    }
}
