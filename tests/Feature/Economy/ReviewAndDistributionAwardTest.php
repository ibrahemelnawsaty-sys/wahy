<?php

namespace Tests\Feature\Economy;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Classroom;
use App\Models\Point;
use App\Models\School;
use App\Models\User;
use App\Services\Activity\PointsDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * M5: توزيع نقاط الطالب الديمو يُتخطّى (لا يُضخّم المعلّم/الوليّ/المدرسة).
 * M2: إعادة مراجعة المعلّم برفع الدرجة تمنح الفرق التصاعديّ (لا تبتلعه ولا تُضاعف).
 */
class ReviewAndDistributionAwardTest extends TestCase
{
    use RefreshDatabase;

    private function seedClassAndParent(User $student, User $teacher, School $school): void
    {
        $classroom = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacher->id]);
        $student->classrooms()->attach($classroom->id, ['status' => 'active', 'enrollment_date' => now()]);
        $parent = User::factory()->parent($school)->create();
        DB::table('parent_student')->insert(['parent_id' => $parent->id, 'student_id' => $student->id, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_demo_student_distribution_is_skipped(): void
    {
        $school = School::factory()->create(['total_points' => 0]);
        $teacher = User::factory()->teacher($school)->create();
        $demo = User::factory()->student($school)->create(['is_demo' => true]);
        $this->seedClassAndParent($demo, $teacher, $school);

        app(PointsDistributionService::class)->distribute($demo, 100, 'activity_submission', 'نشاط');

        $this->assertDatabaseCount('teacher_points', 0);
        $this->assertDatabaseCount('parent_points', 0);
        $this->assertDatabaseCount('school_points', 0);
        $this->assertSame(0, (int) $school->fresh()->total_points);
    }

    public function test_real_student_distribution_happens(): void
    {
        $school = School::factory()->create(['total_points' => 0]);
        $teacher = User::factory()->teacher($school)->create();
        $real = User::factory()->student($school)->create();
        $this->seedClassAndParent($real, $teacher, $school);

        app(PointsDistributionService::class)->distribute($real, 100, 'activity_submission', 'نشاط');

        $this->assertDatabaseHas('teacher_points', ['teacher_id' => $teacher->id]);
        $this->assertDatabaseCount('parent_points', 1);
        $this->assertGreaterThan(0, (int) $school->fresh()->total_points);
    }

    public function test_teacher_re_review_awards_only_incremental_delta(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $student = User::factory()->student($school)->create();
        $classroom = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacher->id]);
        $student->classrooms()->attach($classroom->id, ['status' => 'active', 'enrollment_date' => now()]);

        $activity = Activity::factory()->create(['created_by' => $teacher->id, 'points' => 100]);
        $sub = ActivitySubmission::create([
            'student_id' => $student->id, 'activity_id' => $activity->id,
            'answer' => 'x', 'status' => 'needs_review', 'attempts' => 1, 'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)->post(route('teacher.review.submit', $sub->id), ['score' => 50]);
        $this->assertSame(50, (int) Point::where('user_id', $student->id)->sum('points'), 'المراجعة الأولى: 50');

        // إعادة المراجعة برفع الدرجة إلى 90 → يُمنَح الفرق (40) فقط، المجموع 90 (لا 50 مبتلَعة ولا 140 مضاعفة)
        $this->actingAs($teacher)->post(route('teacher.review.submit', $sub->id), ['score' => 90]);
        $this->assertSame(90, (int) Point::where('user_id', $student->id)->sum('points'), 'الفرق التصاعديّ فقط');
        $this->assertSame(90, (int) $sub->fresh()->awarded_points);
    }
}
