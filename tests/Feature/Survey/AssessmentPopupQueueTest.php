<?php

namespace Tests\Feature\Survey;

use App\Models\Concept;
use App\Models\Lesson;
use App\Models\School;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Models\Value;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * تقييمات القيمة القبليّة/البعديّة تُعرَض للطالب كنافذة **حاجبة تسلسليّة** على صفحة الدرس.
 *
 * البنية القائمة: المكوّن الحاجب (components/survey-popup) مبنيّ ومُضمَّن في layouts/student-app،
 * لكنّه يقرأ session('pending_surveys') التي يغذّيها CheckPendingSurveys عبر
 * Survey::getPendingSurveysForUser() — وهذه **تستبعد صراحةً** مُشغِّلات التقييم
 * (Survey.php:335-339، «Issue 19») وهي بالضبط ما يسكّه إنشاء التقييم. فالتقييم لا يصل النافذة أبداً.
 *
 * الحلّ **تغذية لا بناء**: StudentController::lesson يكتب المفتاحين نفسيهما من سياق الدرس.
 * وبما أنّ الوسيط ينساهما في كلّ طلب آخر، يبقى الحجب **محصوراً بصفحة الدرس** — وهو المطلوب،
 * ودون لمس استبعاد Issue 19 (إلغاؤه يحوّل كل تقييم إلى نافذة عامّة على كل صفحة لكل طالب).
 */
class AssessmentPopupQueueTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Value $value;

    private Lesson $lesson;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
        $this->value = Value::factory()->create(['status' => 'active']);
        $this->school->activeValues()->attach($this->value->id, ['activated_at' => now()]);

        $concept = Concept::factory()->create(['value_id' => $this->value->id]);
        $this->lesson = Lesson::factory()->create(['concept_id' => $concept->id, 'status' => 'active']);
    }

    private function student(): User
    {
        return User::factory()->create(['role' => 'student', 'school_id' => $this->school->id]);
    }

    /** تقييم (قبلي/بعدي) بالمُشغِّلات التي يسكّها الأدمن فعلاً. */
    private function assessment(string $phase, array $attrs = [], int $questions = 2): Survey
    {
        $isValue = array_key_exists('value_id', $attrs);
        $survey = Survey::create(array_merge([
            'title' => 'تقييم ' . $phase,
            'target_roles' => ['students'],
            'status' => 'active',
            'survey_type' => 'pre_post_assessment',
            'assessment_phase' => $phase,
            'trigger_type' => $isValue
                ? ($phase === 'pre' ? 'on_value_start' : 'on_value_complete')
                : ($phase === 'pre' ? 'on_lesson_start' : 'on_lesson_complete'),
            'requires_login' => true,
            'is_mandatory' => true,
            'is_popup' => true,
            'created_by' => User::factory()->create()->id,
        ], $attrs));

        for ($i = 1; $i <= $questions; $i++) {
            $survey->questions()->create([
                'question_text' => "سؤال {$i}",
                'question_type' => 'text',
                'is_required' => false,
                'order' => $i,
            ]);
        }

        return $survey;
    }

    private function openLesson(User $student)
    {
        return $this->actingAs($student)->get(route('student.lesson', $this->lesson->id));
    }

    public function test_lesson_page_queues_mandatory_assessments_into_session(): void
    {
        $student = $this->student();
        $valuePre = $this->assessment('pre', ['value_id' => $this->value->id]);
        $lessonPre = $this->assessment('pre', ['lesson_id' => $this->lesson->id]);

        $this->openLesson($student)->assertOk();

        $this->assertTrue(session('show_survey_popup'), 'يجب رفع علم النافذة على صفحة الدرس');
        $this->assertSame(
            [$valuePre->id, $lessonPre->id],
            collect(session('pending_surveys'))->pluck('id')->all(),
            'الترتيب حتميّ: قيمة-قبليّ ثمّ درس-قبليّ',
        );
    }

    public function test_queued_surveys_carry_eager_loaded_questions(): void
    {
        // المكوّن يتنقّل $survey->questions على نموذج مُستعاد من الجلسة — بلا تحميل مسبق تظهر فارغة.
        $student = $this->student();
        $this->assessment('pre', ['lesson_id' => $this->lesson->id], questions: 2);

        $this->openLesson($student)->assertOk();

        $first = collect(session('pending_surveys'))->first();
        $this->assertTrue($first->relationLoaded('questions'), 'يجب تحميل الأسئلة مسبقاً');
        $this->assertCount(2, $first->questions);
    }

    public function test_non_mandatory_assessment_stays_a_banner_and_does_not_block(): void
    {
        // احترام خيار الأدمن: pendingLessonSurveyFor لا تفحص is_mandatory/is_popup إطلاقاً،
        // فبدون مرشّح صريح يصير كلّ تقييم نافذةً حاجبة رغم إلغاء الأدمن للعلمين.
        $student = $this->student();
        $this->assessment('pre', [
            'lesson_id' => $this->lesson->id,
            'is_mandatory' => false,
            'is_popup' => false,
        ]);

        $res = $this->openLesson($student)->assertOk();

        $this->assertNull(session('show_survey_popup'), 'غير الإجباريّ لا يحجب');
        $res->assertSee('ابدأ الآن', false); // البانر باقٍ كسقوط
    }

    public function test_answered_assessment_is_not_queued_again(): void
    {
        $student = $this->student();
        $pre = $this->assessment('pre', ['lesson_id' => $this->lesson->id]);
        SurveyResponse::create([
            'survey_id' => $pre->id,
            'user_id' => $student->id,
            'answers' => [],
            'completed_at' => now(),
        ]);

        $this->openLesson($student)->assertOk();

        $this->assertNull(session('show_survey_popup'));
    }

    public function test_lesson_post_is_not_queued_before_activities_are_done(): void
    {
        $student = $this->student();
        $this->assessment('post', ['lesson_id' => $this->lesson->id]);

        $this->openLesson($student)->assertOk();

        // لا أنشطة في هذا الدرس ⟶ $totalActivities = 0 ⟶ البعديّ غير مستحقّ.
        $this->assertNull(session('show_survey_popup'), 'البعديّ لا يُحجب قبل إنهاء الأنشطة');
    }

    /**
     * توحيد تعريف «الإتمام»: بوّابة التقييم البعديّ كانت تعدّ SUBMITTED_STATUSES (تشمل pending
     * و needs_review) إتماماً، بينما إتقان القيمة يعدّ DONE_STATUSES فقط — ووثائق الثابت نفسه
     * تقول صراحةً إنّه «يُستخدم في عدّ ما أرسله الطالب **وليس** في الإنجاز النهائي».
     * فطالبٌ تسليمُه قيد المراجعة كان يُحجب بتقييمٍ بعديّ «كأنّه أنهى» — والمعلّم قد يردّه.
     */
    public function test_post_assessment_waits_for_accepted_work_not_merely_submitted(): void
    {
        $student = $this->student();
        // all_schools_mode='direct' يجعله مرئيّاً لكلّ المدارس (Activity::scopeVisibleToStudent).
        $activity = \App\Models\Activity::factory()->create([
            'lesson_id' => $this->lesson->id,
            'status' => 'active',
            'all_schools_mode' => 'direct',
            'approval_status' => 'approved',
        ]);
        \App\Models\ActivitySubmission::create([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'answer' => 'إجابة',
            'status' => 'needs_review', // سُلِّم لكن لم يُعتمد
            'submitted_at' => now(),
        ]);
        $this->assessment('post', ['lesson_id' => $this->lesson->id]);

        $res = $this->openLesson($student)->assertOk();

        $this->assertNull(session('show_survey_popup'), 'تسليمٌ قيد المراجعة ليس إتماماً');
        // ولا يتغيّر شريط التقدّم: «أرسلتُ» تقدّمٌ فعليّ يراه الطالب.
        $this->assertEquals(100, $res->viewData('completionPercent'));
    }

    public function test_post_assessment_is_queued_once_work_is_accepted(): void
    {
        $student = $this->student();
        // all_schools_mode='direct' يجعله مرئيّاً لكلّ المدارس (Activity::scopeVisibleToStudent).
        $activity = \App\Models\Activity::factory()->create([
            'lesson_id' => $this->lesson->id,
            'status' => 'active',
            'all_schools_mode' => 'direct',
            'approval_status' => 'approved',
        ]);
        \App\Models\ActivitySubmission::create([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'answer' => 'إجابة',
            'status' => 'approved',
            'submitted_at' => now(),
        ]);
        $post = $this->assessment('post', ['lesson_id' => $this->lesson->id]);

        $this->openLesson($student)->assertOk();

        $this->assertTrue(session('show_survey_popup'));
        $this->assertContains($post->id, collect(session('pending_surveys'))->pluck('id')->all());
    }

    public function test_queue_is_scoped_to_the_lesson_page_only(): void
    {
        // حارس انحدارة «Issue 19»: الحجب سياقيّ لا عالميّ.
        $student = $this->student();
        $this->assessment('pre', ['lesson_id' => $this->lesson->id]);

        $this->openLesson($student)->assertOk();
        $this->assertTrue(session('show_survey_popup'));

        $this->actingAs($student)->get(route('student.dashboard'))->assertOk();
        $this->assertNull(session('show_survey_popup'), 'النافذة يجب ألّا تتبع الطالب خارج الدرس');
    }

    public function test_assessment_of_another_schools_value_is_not_queued(): void
    {
        // §4 عزل: قيمة مُفعَّلة حصراً لمدرسة أخرى ⟶ لا الدرس ولا تقييمه يصل الطالب.
        $otherSchool = School::factory()->create();
        $otherValue = Value::factory()->create(['status' => 'active']);
        $otherSchool->activeValues()->attach($otherValue->id, ['activated_at' => now()]);
        $otherConcept = Concept::factory()->create(['value_id' => $otherValue->id]);
        $otherLesson = Lesson::factory()->create(['concept_id' => $otherConcept->id, 'status' => 'active']);
        $this->assessment('pre', ['lesson_id' => $otherLesson->id]);

        $this->actingAs($this->student())
            ->get(route('student.lesson', $otherLesson->id))
            ->assertStatus(404);

        $this->assertNull(session('show_survey_popup'));
    }
}
