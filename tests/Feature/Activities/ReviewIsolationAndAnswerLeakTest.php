<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * M3: questionsForStudent يخلط ترتيب options لأنواع الترتيب/الحروف (لا يُسرِّب الترتيب الصحيح للجوّال).
 * M4: طابور مراجعة المعلّم (فرع created_by) مُقيَّد بطلاب مدرسة المعلّم (لا تسرّب عابر للمدارس).
 */
class ReviewIsolationAndAnswerLeakTest extends TestCase
{
    use RefreshDatabase;

    public function test_ordering_options_are_shuffled_and_answer_keys_stripped(): void
    {
        $activity = Activity::factory()->create([
            'type' => 'quiz',
            'questions' => [[
                'type' => 'sentence_order',
                'question' => 'رتّب',
                'options' => ['الأولى', 'الثانية', 'الثالثة', 'الرابعة', 'الخامسة'],
                'correct_answer' => 'الأولى، الثانية، الثالثة، الرابعة، الخامسة',
            ]],
        ]);

        // مفاتيح الإجابة مُزالة
        $q = $activity->questionsForStudent()[0];
        $this->assertArrayNotHasKey('correct_answer', $q);
        $this->assertArrayNotHasKey('answer', $q);
        // نفس العناصر (مجموعةً) لكن ليست بالترتيب المخزَّن دائماً
        $this->assertEqualsCanonicalizing(['الأولى', 'الثانية', 'الثالثة', 'الرابعة', 'الخامسة'], $q['options']);

        $orders = [];
        for ($i = 0; $i < 8; $i++) {
            $orders[] = implode('|', $activity->questionsForStudent()[0]['options']);
        }
        $this->assertGreaterThan(1, count(array_unique($orders)), 'الترتيب يُخلَط (لا يُسرَّب الترتيب الصحيح)');
    }

    public function test_teacher_cannot_review_cross_school_submission_on_own_activity(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $teacher = User::factory()->teacher($schoolA)->create();
        $activity = Activity::factory()->create(['created_by' => $teacher->id]);

        // طالب مدرسة أخرى (B) يُسلّم على نشاط معلّم مدرسة A (عبر نشر بنك عبر المدارس)
        $foreign = User::factory()->student($schoolB)->create();
        $foreignSub = ActivitySubmission::create([
            'student_id' => $foreign->id, 'activity_id' => $activity->id,
            'answer' => 'x', 'status' => 'needs_review', 'attempts' => 1, 'submitted_at' => now(),
        ]);

        // طالب مدرسة المعلّم نفسها (A) — ضابط
        $ownSchoolStudent = User::factory()->student($schoolA)->create();
        $ownSub = ActivitySubmission::create([
            'student_id' => $ownSchoolStudent->id, 'activity_id' => $activity->id,
            'answer' => 'y', 'status' => 'needs_review', 'attempts' => 1, 'submitted_at' => now(),
        ]);

        $this->assertFalse($foreignSub->isReviewableByTeacher($teacher), 'تسليم مدرسة أخرى ليس قابلاً للمراجعة');
        $this->assertTrue($ownSub->isReviewableByTeacher($teacher), 'تسليم مدرسة المعلّم قابلٌ للمراجعة');
    }
}
