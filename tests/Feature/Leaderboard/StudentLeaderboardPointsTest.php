<?php

namespace Tests\Feature\Leaderboard;

use App\Models\Point;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentLeaderboardPointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_leaderboard_reflects_points(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        Point::create(['user_id' => $student->id, 'points' => 42, 'reason' => 'test']);

        $resp = $this->actingAs($student)->getJson('/leaderboard/students');
        $resp->assertOk();

        $mine = collect($resp->json('leaderboard'))->firstWhere('id', $student->id);
        $this->assertNotNull($mine, 'الطالب يجب أن يظهر في الصدارة');
        $this->assertSame(42, $mine['points'], 'نقاط الطالب يجب أن تظهر لا 0 (عطل ترتيب withSum/select)');
    }
}
