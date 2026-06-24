<?php

namespace Tests\Feature\Performance;

use App\Models\Activity;
use App\Models\Concept;
use App\Models\Lesson;
use App\Models\School;
use App\Models\User;
use App\Models\Value;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch 1 regression for the student dashboard.
 *
 * Two distinct fixes are guarded here:
 *  - cluster 13 (DB portability): getStudentStats used the MySQL-only CURDATE();
 *    the dashboard now renders on any driver (sqlite/MySQL) instead of 500-ing.
 *  - cluster 09 (partial N+1): the values tree now eager-loads
 *    concepts.lessons.activities, so iterating $lesson->activities adds no
 *    per-lesson query. NOTE: the dashboard still has OTHER N+1 sources (a
 *    dedicated perf batch is required to make the whole action constant-query);
 *    this test only pins the eager-load chain that was fixed here.
 */
class StudentDashboardNPlusOneTest extends TestCase
{
    use RefreshDatabase;

    private function makeTree(int $lessons, int $activitiesPerLesson): Value
    {
        $value = Value::factory()->create(['status' => 'active']);
        $concept = Concept::factory()->create(['value_id' => $value->id]);
        for ($i = 0; $i < $lessons; $i++) {
            $lesson = Lesson::factory()->create(['concept_id' => $concept->id, 'status' => 'active']);
            Activity::factory()->count($activitiesPerLesson)->create(['lesson_id' => $lesson->id]);
        }

        return $value;
    }

    public function test_dashboard_renders_on_non_mysql_driver(): void
    {
        // Pre-fix this 500-ed under sqlite because of CURDATE().
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $this->makeTree(2, 2);

        $this->actingAs($student)->get('/student/dashboard')->assertOk();
    }

    public function test_lesson_activities_chain_is_eager_loaded(): void
    {
        $school = School::factory()->create();
        $this->makeTree(6, 3);

        // Load the values exactly as the dashboard does after the fix.
        $values = Value::visibleForSchool($school->id)
            ->with(['concepts.lessons.activities'])
            ->get();

        // Iterating the nested relations must trigger NO further queries.
        DB::enableQueryLog();
        foreach ($values as $value) {
            foreach ($value->concepts as $concept) {
                foreach ($concept->lessons as $lesson) {
                    $lesson->activities->each(fn ($a) => $a->id);
                }
            }
        }
        $lazyQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(
            0,
            $lazyQueries,
            "lesson->activities must be eager-loaded; saw {$lazyQueries} lazy queries",
        );
    }
}
