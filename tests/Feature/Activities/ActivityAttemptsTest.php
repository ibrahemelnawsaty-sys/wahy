<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Coin;
use App\Models\Concept;
use App\Models\Lesson;
use App\Models\Point;
use App\Models\School;
use App\Models\User;
use App\Models\Value;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * #13: عدد المحاولات — الطالب لا يستطيع التقديم على محاولة ثانية.
 * إعادة إنتاج + تثبيت السلوك المطلوب: يُسمح بإعادة التسليم ما دامت المحاولات متبقية
 * والنشاط لم يُعتمَد نهائيًّا من المعلّم.
 */
class ActivityAttemptsTest extends TestCase
{
    use RefreshDatabase;

    /** يُنشئ طالباً + نشاطاً + تسليماً موجوداً بحالة/محاولات محدّدة، ويعيد [student, activity]. */
    private function scenario(string $status, int $attempts, int $maxAttempts = 3): array
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = Activity::factory()->create([
            'manual_review' => true, // re-grade → null → pending (يعزل قرار الإعادة عن التصحيح)
            'max_attempts' => $maxAttempts,
            'all_schools_mode' => 'direct',
            'status' => 'active',
            'lesson_id' => null,
            'points' => 10,
        ]);
        ActivitySubmission::create([
            'student_id' => $student->id,
            'activity_id' => $activity->id,
            'answer' => 'قديم',
            'status' => $status,
            'attempts' => $attempts,
            'submitted_at' => now(),
        ]);

        return [$student, $activity];
    }

    private function submit(User $student, Activity $activity)
    {
        return $this->actingAs($student)
            ->postJson(route('student.activity.submit', $activity->id), ['answer' => 'جديد']);
    }

    public function test_resubmit_allowed_when_pending_with_attempts_remaining(): void
    {
        [$s, $a] = $this->scenario('pending', 1, 3);
        $this->assertTrue((bool) $this->submit($s, $a)->json('success'), 'نشاط قيد المراجعة والمحاولات متبقية يجب أن يُعاد تسليمه');
    }

    public function test_resubmit_allowed_when_needs_review(): void
    {
        [$s, $a] = $this->scenario('needs_review', 1, 3);
        $this->assertTrue((bool) $this->submit($s, $a)->json('success'));
    }

    public function test_resubmit_allowed_when_rejected(): void
    {
        [$s, $a] = $this->scenario('rejected', 1, 3);
        $this->assertTrue((bool) $this->submit($s, $a)->json('success'));
    }

    public function test_resubmit_blocked_when_teacher_approved(): void
    {
        [$s, $a] = $this->scenario('approved', 1, 3);
        $this->assertFalse((bool) $this->submit($s, $a)->json('success'), 'المعتمَد نهائيًّا من المعلّم لا يُعاد');
    }

    // ملاحظة: حالة 'completed' مُستثناة من الإعادة عمدًا (منح XP على كل تسليم = استغلال مضاعفة
    // النقاط)؛ لا تُختبَر هنا لأنّ enum('completed') يُضاف بهجرة MySQL-only فلا يصلح في SQLite.

    public function test_resubmit_blocked_when_attempts_exhausted(): void
    {
        [$s, $a] = $this->scenario('needs_review', 3, 3);
        $this->assertFalse((bool) $this->submit($s, $a)->json('success'), 'استُنفدت المحاولات');
    }

    /** حصانة استغلال: إعادة تسليم pending (score=null) يجب ألّا تمنح عملات (كان max(1,⌊0/2⌋)=1). */
    public function test_pending_resubmit_grants_no_coins(): void
    {
        [$s, $a] = $this->scenario('pending', 1, 3);
        $this->assertTrue((bool) $this->submit($s, $a)->json('success'));
        $this->assertSame(0, (int) Coin::where('user_id', $s->id)->sum('coins'), 'لا عملات على إعادة تسليم بلا درجة');
    }

    // ===================== مكافأة «أفضل محاولة» (لا تراكم) =====================

    /** كويز مُصحَّح آليًّا، passing_score=100 كي تبقى الدرجات الجزئيّة needs_review (لا completed المحجوب في SQLite). */
    private function gradedQuiz(): Activity
    {
        $value = Value::factory()->create();
        $concept = Concept::factory()->create(['value_id' => $value->id]);
        $lesson = Lesson::factory()->create(['concept_id' => $concept->id]);

        return Activity::factory()->quiz()->create([
            'lesson_id' => $lesson->id,
            'points' => 10,
            'passing_score' => 100,
            'max_attempts' => 5,
            'manual_review' => false,
            'all_schools_mode' => 'direct',
            'status' => 'active',
            'questions' => [
                ['question' => 'Q1', 'options' => ['أ', 'ب', 'ج'], 'correct_answer' => 0],
                ['question' => 'Q2', 'options' => ['أ', 'ب', 'ج'], 'correct_answer' => 1],
                ['question' => 'Q3', 'options' => ['أ', 'ب', 'ج'], 'correct_answer' => 2],
            ],
        ]);
    }

    private function rewardPoints(User $student, Activity $activity): int
    {
        return (int) Point::where('user_id', $student->id)->where('activity_id', $activity->id)->sum('points');
    }

    public function test_best_attempt_reward_is_not_cumulative(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->gradedQuiz();

        // محاولة 1: سؤال واحد صحيح = 33% → needs_review، xp=round(3.3)=3
        $this->actingAs($student)->postJson(route('student.activity.submit', $activity->id), ['answer' => [0, 9, 9]])->assertOk();
        $first = $this->rewardPoints($student, $activity);
        $this->assertGreaterThan(0, $first);

        // محاولة 2: سؤالان صحيحان = 67% → needs_review، xp=7 → يُمنح الفرق فقط (الإجمالي = 7)
        $this->actingAs($student)->postJson(route('student.activity.submit', $activity->id), ['answer' => [0, 1, 9]])->assertOk();
        $best = $this->rewardPoints($student, $activity);
        $this->assertGreaterThan($first, $best, 'التحسّن يمنح الفرق');
        // الإجمالي = xp أفضل محاولة (67% من 10 = 7) لا التراكم (3+7=10)
        $this->assertSame(7, $best, 'الإجمالي = xp أفضل محاولة لا التراكم');

        // محاولة 3: أسوأ (33%) → لا تُضاف ولا تُخصم (يحتفظ بأفضل نتيجة)
        $this->actingAs($student)->postJson(route('student.activity.submit', $activity->id), ['answer' => [0, 9, 9]])->assertOk();
        $this->assertSame($best, $this->rewardPoints($student, $activity), 'المحاولة الأسوأ لا تُغيّر المكافأة');

        // «تحتفظ بأفضل درجة»: الدرجة/الحالة المحفوظة لا تهبط بالمحاولة الأسوأ (نفس مسار حماية completed)
        $sub = ActivitySubmission::where('student_id', $student->id)->where('activity_id', $activity->id)->first();
        $this->assertSame(67, (int) $sub->score, 'الدرجة المحفوظة تبقى الأفضل لا تهبط');
        $this->assertSame(3, (int) $sub->attempts, 'المحاولات تُزاد رغم عدم تحديث الدرجة');
    }

    // ===================== توحيد API (محاولات/حالة) =====================

    private function apiActivity(int $maxAttempts = 3): Activity
    {
        return Activity::factory()->create([
            'manual_review' => true,
            'max_attempts' => $maxAttempts,
            'all_schools_mode' => 'direct',
            'status' => 'active',
            'lesson_id' => null,
        ]);
    }

    private function apiExisting(User $student, Activity $activity, string $status, int $attempts): void
    {
        ActivitySubmission::create([
            'student_id' => $student->id, 'activity_id' => $activity->id,
            'answer' => 'قديم', 'status' => $status, 'attempts' => $attempts, 'submitted_at' => now(),
        ]);
    }

    public function test_api_enforces_max_attempts(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->apiActivity(2);
        $this->apiExisting($student, $activity, 'pending', 2); // استنفد المحاولتين

        Sanctum::actingAs($student);
        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['x']])
            ->assertStatus(400);
    }

    public function test_api_blocks_resubmit_of_approved(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->apiActivity(5);
        $this->apiExisting($student, $activity, 'approved', 1);

        Sanctum::actingAs($student);
        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['x']])
            ->assertStatus(400);
    }

    public function test_api_allows_pending_resubmit_and_increments_attempts(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $activity = $this->apiActivity(3);
        $this->apiExisting($student, $activity, 'pending', 1);

        Sanctum::actingAs($student);
        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['جديد']])
            ->assertOk();

        $sub = ActivitySubmission::where('student_id', $student->id)->where('activity_id', $activity->id)->first();
        $this->assertSame(2, (int) $sub->attempts, 'المحاولات تُزاد');
    }
}
