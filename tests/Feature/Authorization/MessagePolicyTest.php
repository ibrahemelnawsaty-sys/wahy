<?php

namespace Tests\Feature\Authorization;

use App\Models\Message;
use App\Models\User;
use App\Policies\MessagePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagePolicyTest extends TestCase
{
    use RefreshDatabase;

    private MessagePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new MessagePolicy();
    }

    public function test_sender_can_view_own_message(): void
    {
        $sender   = User::factory()->create();
        $receiver = User::factory()->create();
        $message  = Message::factory()->create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
        ]);

        $this->assertTrue($this->policy->view($sender, $message));
        $this->assertTrue($this->policy->view($receiver, $message));
    }

    public function test_third_party_cannot_view_message(): void
    {
        $sender    = User::factory()->create();
        $receiver  = User::factory()->create();
        $thirdUser = User::factory()->create();
        $message   = Message::factory()->create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
        ]);

        $this->assertFalse($this->policy->view($thirdUser, $message));
    }

    public function test_super_admin_can_view_any_message(): void
    {
        $sender     = User::factory()->create();
        $receiver   = User::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $message    = Message::factory()->create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
        ]);

        $this->assertTrue($this->policy->view($superAdmin, $message));
    }

    public function test_only_sender_can_update_message(): void
    {
        $sender   = User::factory()->create();
        $receiver = User::factory()->create();
        $message  = Message::factory()->create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
        ]);

        $this->assertTrue($this->policy->update($sender, $message));
        $this->assertFalse($this->policy->update($receiver, $message));
    }

    public function test_only_receiver_can_mark_as_read(): void
    {
        $sender   = User::factory()->create();
        $receiver = User::factory()->create();
        $message  = Message::factory()->create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
        ]);

        $this->assertTrue($this->policy->markAsRead($receiver, $message));
        $this->assertFalse($this->policy->markAsRead($sender, $message));
    }
}
