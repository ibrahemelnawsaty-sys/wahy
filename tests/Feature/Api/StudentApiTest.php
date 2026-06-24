<?php

namespace Tests\Feature\Api;

use App\Models\Coin;
use App\Models\Concept;
use App\Models\Point;
use App\Models\School;
use App\Models\User;
use App\Models\Value;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Pass-4 Batch 1 / cluster 04 regression: the mobile student API endpoints
 * previously threw HTTP 500 on every call because they queried a non-existent
 * `amount` column (points/coins use `points`/`coins`), called the non-existent
 * `streaks()` relation (it is `streak()`), and eager-loaded the removed
 * `concepts.meanings` relation. These tests pin the corrected identifiers.
 */
class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        $school = School::factory()->create();

        return User::factory()->student($school)->create();
    }

    public function test_dashboard_returns_point_and_coin_totals_without_500(): void
    {
        $student = $this->student();
        Point::create(['user_id' => $student->id, 'points' => 50, 'reason' => 'test']);
        Point::create(['user_id' => $student->id, 'points' => 30, 'reason' => 'test']);
        Coin::create(['user_id' => $student->id, 'coins' => 10, 'transaction_type' => 'earn', 'reason' => 'test']);

        Sanctum::actingAs($student);

        $this->getJson('/api/v1/student/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.stats.total_points', 80)
            ->assertJsonPath('data.stats.total_coins', 10)
            ->assertJsonPath('data.stats.current_streak', 0);
    }

    public function test_values_tree_eager_loads_concepts_lessons_without_500(): void
    {
        $student = $this->student();
        $value = Value::create(['name' => 'الصدق', 'description' => 'd', 'order' => 1, 'status' => 'active', 'created_by' => null]);
        Concept::create(['value_id' => $value->id, 'name' => 'الأمانة', 'order' => 1]);

        Sanctum::actingAs($student);

        $this->getJson('/api/v1/student/values-tree')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.title', 'الصدق')
            ->assertJsonPath('data.0.concepts.0.title', 'الأمانة')
            ->assertJsonPath('data.0.concepts.0.lessons_count', 0);
    }

    public function test_leaderboard_orders_by_points_sum_without_500(): void
    {
        $school = School::factory()->create();
        $top = User::factory()->student($school)->create();
        $low = User::factory()->student($school)->create();
        Point::create(['user_id' => $top->id, 'points' => 100, 'reason' => 'test']);
        Point::create(['user_id' => $low->id, 'points' => 10, 'reason' => 'test']);

        Sanctum::actingAs($top);

        $this->getJson('/api/v1/student/leaderboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.leaderboard.0.id', $top->id)
            ->assertJsonPath('data.leaderboard.0.points', 100)
            ->assertJsonPath('data.user_points', 100);
    }
}
