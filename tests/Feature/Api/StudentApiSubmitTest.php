<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Point;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * تسليم النشاط عبر الجوّال (API): توحيد مع الويب + سدّ سباق التسليم المزدوج (H2 من التدقيق العميق).
 * التسليم صار داخل معاملة بقفل صفّ النشاط — صفٌّ واحد ومنحٌ مرّة واحدة.
 */
class StudentApiSubmitTest extends TestCase
{
    use RefreshDatabase;

    private function activity(array $overrides = []): Activity
    {
        return Activity::factory()->create(array_merge([
            'type' => 'quiz', 'status' => 'active', 'all_schools_mode' => 'direct', 'lesson_id' => null,
            'points' => 100, 'passing_score' => 50, 'max_attempts' => 3,
            'questions' => [['type' => 'short_answer', 'correct_answer' => 'الصدق']],
        ], $overrides));
    }

    public function test_submit_creates_single_submission_and_awards_once(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->activity();
        Sanctum::actingAs($student);

        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['الصدق']])->assertOk();

        $rows = ActivitySubmission::where('activity_id', $activity->id)->where('student_id', $student->id)->get();
        $this->assertCount(1, $rows, 'صفّ تسليم واحد فقط');
        $this->assertSame('completed', $rows[0]->status);
        $this->assertSame(100, (int) $rows[0]->score);
        $this->assertSame(100, (int) Point::where('user_id', $student->id)->sum('points'), 'مُنِح مرّة واحدة');
    }

    public function test_resubmit_increments_attempts_without_new_row_or_double_award(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->activity();
        Sanctum::actingAs($student);

        // محاولة خاطئة أولاً → needs_review
        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['خطأ']])->assertOk();
        // ثمّ محاولة صحيحة
        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['الصدق']])->assertOk();

        $rows = ActivitySubmission::where('activity_id', $activity->id)->where('student_id', $student->id)->get();
        $this->assertCount(1, $rows, 'ما زال صفّاً واحداً بعد الإعادة');
        $this->assertSame(2, (int) $rows[0]->attempts);
        $this->assertSame('completed', $rows[0]->status);
        $this->assertSame(100, (int) Point::where('user_id', $student->id)->sum('points'), 'الفرق التصاعديّ فقط، لا مضاعفة');
    }

    public function test_attempts_exhausted_returns_400(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->activity(['max_attempts' => 1]);
        Sanctum::actingAs($student);

        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['خطأ']])->assertOk();
        // needs_review لكن المحاولات (1) استُنفدت
        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['الصدق']])->assertStatus(400);

        $this->assertSame(1, ActivitySubmission::where('activity_id', $activity->id)->where('student_id', $student->id)->count());
    }

    // ===== L9: الحدّ الزمنيّ للاختبار الموقوت مفروضٌ خادميّاً في الجوّال =====

    public function test_timed_quiz_rejects_submit_without_opening_first(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->activity(['quiz_duration' => 10]);
        Sanctum::actingAs($student);

        // بلا فتحٍ (GET) لا يُسجَّل ختمُ البدء → رفض
        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['الصدق']])->assertStatus(422);
    }

    public function test_timed_quiz_accepts_submit_within_time_after_opening(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->activity(['quiz_duration' => 10]);
        Sanctum::actingAs($student);

        $this->getJson("/api/v1/student/activities/{$activity->id}")->assertOk(); // «فتح» يسجّل البدء
        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['الصدق']])->assertOk();
    }

    public function test_timed_quiz_rejects_submit_after_time_expired(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->activity(['quiz_duration' => 10]);
        Sanctum::actingAs($student);

        $this->getJson("/api/v1/student/activities/{$activity->id}")->assertOk();
        // تلاعبٌ بختم البدء ليصير قبل 20 دقيقة (تجاوز المدّة 10) → رفض
        Cache::put("quiz_start:{$student->id}:{$activity->id}", now()->subMinutes(20)->timestamp, now()->addHour());

        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['الصدق']])->assertStatus(422);
    }
}
