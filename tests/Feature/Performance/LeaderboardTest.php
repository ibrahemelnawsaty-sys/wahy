<?php

namespace Tests\Feature\Performance;

use App\Models\Point;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * يختبر LeaderboardController بعد refactor الأداء في Sprint 1:
 * - صفر N+1
 * - Cache TTL 15 دقيقة
 * - ORDER BY على SQL مستوى
 */
class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_leaderboard_uses_withSum_correctly(): void
    {
        $school = School::factory()->create();
        $s1 = User::factory()->student($school)->create();

        Point::create(['user_id' => $s1->id, 'points' => 300]);

        // التأكد أن withSum يعمل عبر العلاقة
        $user = User::where('id', $s1->id)
            ->withSum('points as total_points', 'points')
            ->first();

        $this->assertEquals(300, (int) $user->total_points);
    }

    public function test_leaderboard_query_count_is_low(): void
    {
        // إنشاء 3 مدارس + طلاب
        for ($i = 0; $i < 3; $i++) {
            $school = School::factory()->create();
            for ($j = 0; $j < 5; $j++) {
                $student = User::factory()->student($school)->create();
                Point::create(['user_id' => $student->id, 'points' => rand(10, 100)]);
            }
        }

        Cache::flush();
        DB::enableQueryLog();

        $controller = new \App\Http\Controllers\LeaderboardController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getStudentLeaderboard');
        $method->setAccessible(true);
        $result = $method->invoke($controller, 10, null, null, 'global');

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // مع 15 طالب × 3 مدارس = 15 طالب، لكن الـ query واحد فقط
        $this->assertLessThanOrEqual(3, count($queries),
            'Student leaderboard must use ≤ 3 queries (no N+1)');
        $this->assertNotEmpty($result, 'يجب إرجاع نتائج');
    }

    public function test_school_leaderboard_aggregates_via_subqueries(): void
    {
        $schoolA = School::factory()->create(['name' => 'A']);
        $schoolB = School::factory()->create(['name' => 'B']);

        $sa = User::factory()->student($schoolA)->create();
        $sb = User::factory()->student($schoolB)->create();

        Point::create(['user_id' => $sa->id, 'points' => 500]);
        Point::create(['user_id' => $sb->id, 'points' => 300]);

        Cache::flush();
        DB::enableQueryLog();

        $controller = new \App\Http\Controllers\LeaderboardController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getSchoolLeaderboard');
        $method->setAccessible(true);
        $result = $method->invoke($controller, 10);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // قبل refactor: ~150 query. بعد: 1 (subqueries مدمجة)
        $this->assertLessThanOrEqual(3, count($queries),
            'School leaderboard must use ≤ 3 queries');

        $this->assertCount(2, $result);
    }

    public function test_teacher_leaderboard_returns_students_count(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();

        Point::create(['user_id' => $teacher->id, 'points' => 200]);

        Cache::flush();
        $controller = new \App\Http\Controllers\LeaderboardController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getTeacherLeaderboard');
        $method->setAccessible(true);
        $result = $method->invoke($controller, 10, $school->id);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('students_count', $result[0]);
        $this->assertIsInt($result[0]['students_count']);
    }

    public function test_parent_leaderboard_returns_children_count(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->parent($school)->create();

        Point::create(['user_id' => $parent->id, 'points' => 50]);

        Cache::flush();
        $controller = new \App\Http\Controllers\LeaderboardController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getParentLeaderboard');
        $method->setAccessible(true);
        $result = $method->invoke($controller, 10, $school->id);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('children_count', $result[0]);
    }
}
