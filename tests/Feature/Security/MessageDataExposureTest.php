<?php

namespace Tests\Feature\Security;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pass-4 Batch 1 / cluster 05 regression: the message JSON endpoints used to
 * serialize the FULL sender/otherUser User model, leaking email/phone/birth_date
 * to the recipient. The fix narrows the eager-load + returned user to
 * id,name,avatar,role at the call site. These tests pin that no sensitive
 * column leaks through the conversation/new-message endpoints.
 */
class MessageDataExposureTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_conversation_does_not_leak_other_users_email(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create([
            'email' => 'leak-canary@example.test',
            'phone' => '0590000001',
        ]);
        $admin = User::factory()->superAdmin()->create();

        $conversation = Conversation::findOrCreate($student->id, $admin->id);
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $student->id,
            'receiver_id' => $admin->id,
            'message' => 'مرحبا',
        ]);

        $this->actingAs($admin);

        $response = $this->getJson('/messages/conversation/' . $student->id)->assertOk();
        $response->assertDontSee('leak-canary@example.test');
        $response->assertDontSee('0590000001');
        // the partner's display name is still available; sensitive keys are gone
        $response->assertJsonPath('otherUser.name', $student->name);
        $response->assertJsonMissingPath('otherUser.email');
        $response->assertJsonMissingPath('messages.0.sender.email');
    }

    public function test_check_new_messages_does_not_leak_sender_email(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create([
            'email' => 'leak-canary2@example.test',
            'phone' => '0590000002',
        ]);
        $admin = User::factory()->superAdmin()->create();

        $conversation = Conversation::findOrCreate($student->id, $admin->id);
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $student->id,
            'receiver_id' => $admin->id,
            'message' => 'رسالة جديدة',
        ]);

        $this->actingAs($admin);

        $this->getJson('/messages/check-new/' . $student->id)
            ->assertOk()
            ->assertDontSee('leak-canary2@example.test')
            ->assertDontSee('0590000002');
    }
}
