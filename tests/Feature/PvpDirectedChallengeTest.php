<?php

namespace Tests\Feature;

use App\Models\PvpChallenge;
use App\Models\PvpMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * التحدي الموجّه في PvP: اختيار منافس محدّد + دعوة (قبول/رفض) + الصلاحيات (IDOR).
 */
class PvpDirectedChallengeTest extends TestCase
{
    use RefreshDatabase;

    private function challenge(): PvpChallenge
    {
        return PvpChallenge::create([
            'title' => 'تحدي القيم',
            'value_id' => null, // عام لكل المدارس
            'questions' => [
                ['text' => '١+١؟', 'type' => 'multiple_choice', 'options' => ['١', '٢', '٣'], 'correct' => 1, 'points' => 100],
            ],
            'time_limit' => 30,
            'is_active' => true,
            'created_by' => User::factory()->superAdmin()->create()->id,
        ]);
    }

    private function student(string $name = 'طالب'): User
    {
        return User::factory()->student()->create(['name' => $name, 'status' => 'active']);
    }

    public function test_student_can_challenge_a_specific_opponent_creating_an_invite(): void
    {
        $challenge = $this->challenge();
        $p1 = $this->student('زيد');
        $p2 = $this->student('عمرو');

        $this->actingAs($p1)
            ->postJson("/student/pvp/{$challenge->id}/challenge", ['opponent_id' => $p2->id])
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'invited']);

        $this->assertDatabaseHas('pvp_matches', [
            'challenge_id' => $challenge->id,
            'player1_id' => $p1->id,
            'player2_id' => $p2->id,
            'status' => 'invited',
        ]);
    }

    public function test_invited_opponent_can_accept_and_match_starts(): void
    {
        $challenge = $this->challenge();
        $p1 = $this->student();
        $p2 = $this->student();
        $match = PvpMatch::create(['challenge_id' => $challenge->id, 'player1_id' => $p1->id, 'player2_id' => $p2->id, 'status' => 'invited']);

        $this->actingAs($p2)
            ->postJson("/student/pvp-invite/{$match->id}/accept")
            ->assertOk()
            ->assertJson(['success' => true]);

        $match->refresh();
        $this->assertSame('playing', $match->status);
        $this->assertNotNull($match->started_at);
    }

    public function test_invited_opponent_can_decline(): void
    {
        $challenge = $this->challenge();
        $match = PvpMatch::create(['challenge_id' => $challenge->id, 'player1_id' => $this->student()->id, 'player2_id' => ($p2 = $this->student())->id, 'status' => 'invited']);

        $this->actingAs($p2)->postJson("/student/pvp-invite/{$match->id}/decline")->assertOk();
        $this->assertSame('declined', $match->refresh()->status);
    }

    public function test_cannot_challenge_self(): void
    {
        $challenge = $this->challenge();
        $p1 = $this->student();

        $this->actingAs($p1)
            ->postJson("/student/pvp/{$challenge->id}/challenge", ['opponent_id' => $p1->id])
            ->assertStatus(422);

        $this->assertDatabaseCount('pvp_matches', 0);
    }

    public function test_cannot_challenge_a_non_student(): void
    {
        $challenge = $this->challenge();
        $p1 = $this->student();
        $teacher = User::factory()->teacher()->create(['status' => 'active']);

        $this->actingAs($p1)
            ->postJson("/student/pvp/{$challenge->id}/challenge", ['opponent_id' => $teacher->id])
            ->assertStatus(422);

        $this->assertDatabaseCount('pvp_matches', 0);
    }

    public function test_only_invited_player_can_accept(): void
    {
        $challenge = $this->challenge();
        $p1 = $this->student();
        $p2 = $this->student();
        $stranger = $this->student();
        $match = PvpMatch::create(['challenge_id' => $challenge->id, 'player1_id' => $p1->id, 'player2_id' => $p2->id, 'status' => 'invited']);

        // المتحدّي نفسه لا يقبل دعوته
        $this->actingAs($p1)->postJson("/student/pvp-invite/{$match->id}/accept")->assertForbidden();
        // طرف ثالث لا يقبل دعوة غيره
        $this->actingAs($stranger)->postJson("/student/pvp-invite/{$match->id}/accept")->assertForbidden();

        $this->assertSame('invited', $match->refresh()->status);
    }

    public function test_duplicate_challenge_reuses_pending_invite(): void
    {
        $challenge = $this->challenge();
        $p1 = $this->student();
        $p2 = $this->student();

        $this->actingAs($p1)->postJson("/student/pvp/{$challenge->id}/challenge", ['opponent_id' => $p2->id])->assertOk();
        $this->actingAs($p1)->postJson("/student/pvp/{$challenge->id}/challenge", ['opponent_id' => $p2->id])->assertOk();

        $this->assertDatabaseCount('pvp_matches', 1);
    }

    public function test_opponent_search_excludes_self_and_returns_only_students(): void
    {
        $p1 = $this->student('زيد');
        $p2 = $this->student('عمرو');
        $teacher = User::factory()->teacher()->create(['name' => 'أستاذ', 'status' => 'active']);

        $opponents = $this->actingAs($p1)->getJson('/student/pvp-opponents/search?q=')
            ->assertOk()->json('opponents');

        $ids = collect($opponents)->pluck('id');
        $this->assertTrue($ids->contains($p2->id), 'يجب أن يظهر طالب آخر');
        $this->assertFalse($ids->contains($p1->id), 'يجب ألا يظهر الطالب نفسه');
        $this->assertFalse($ids->contains($teacher->id), 'يجب ألا يظهر معلم');
    }

    public function test_reinvite_is_blocked_shortly_after_decline(): void
    {
        $challenge = $this->challenge();
        $p1 = $this->student();
        $p2 = $this->student();
        $match = PvpMatch::create(['challenge_id' => $challenge->id, 'player1_id' => $p1->id, 'player2_id' => $p2->id, 'status' => 'invited']);

        // المنافس يرفض
        $this->actingAs($p2)->postJson("/student/pvp-invite/{$match->id}/decline")->assertOk();

        // إعادة الدعوة فوراً تُرفض (تهدئة تمنع المضايقة)
        $this->actingAs($p1)
            ->postJson("/student/pvp/{$challenge->id}/challenge", ['opponent_id' => $p2->id])
            ->assertStatus(429);

        // لم تُنشأ دعوة معلّقة جديدة
        $this->assertDatabaseMissing('pvp_matches', [
            'challenge_id' => $challenge->id, 'player1_id' => $p1->id, 'player2_id' => $p2->id, 'status' => 'invited',
        ]);
    }
}
