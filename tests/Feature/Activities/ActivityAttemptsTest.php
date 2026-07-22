<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Coin;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
