<?php

namespace Tests\Feature\Authorization;

use App\Models\Concept;
use App\Models\Lesson;
use App\Models\School;
use App\Models\Survey;
use App\Models\User;
use App\Models\Value;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * §4 (عزل المدارس — «أخطر من الاقتصاد»): SurveyController::submit كان يفحص requires_login
 * و isActive و target_roles فقط — **ولا يفحص المدرسة إطلاقاً**، وربط المسار غير منطوق.
 * فطالبُ مدرسةٍ يستطيع تلويث نتائج تقييم مدرسةٍ أخرى بنداء المسار مباشرةً، ولا تحميه النافذة
 * لأنّ الحجب في المتصفّح تجربةُ استخدام لا حدّ أمان.
 *
 * ناقلان: school_id صريح على الاستبيان، وتقييمٌ بـschool_id=NULL مرتبط بقيمة مُفعَّلة لمدرسة أخرى.
 */
class survey_submit_school_scopeIdorTest extends TestCase
{
    use RefreshDatabase;

    private function studentOf(School $school): User
    {
        return User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
    }

    private function surveyWithQuestion(array $attrs): Survey
    {
        $survey = Survey::create(array_merge([
            'title' => 'استبيان',
            'target_roles' => ['students'],
            'status' => 'active',
            'requires_login' => true,
            'created_by' => User::factory()->create()->id,
        ], $attrs));

        $survey->questions()->create([
            'question_text' => 'سؤال',
            'question_type' => 'text',
            'is_required' => false,
            'order' => 1,
        ]);

        return $survey;
    }

    /** درس تحت قيمة مُفعَّلة **حصراً** لهذه المدرسة. */
    private function lessonOfValueExclusiveTo(School $school): array
    {
        $value = Value::factory()->create(['status' => 'active']);
        $school->activeValues()->attach($value->id, ['activated_at' => now()]);
        $concept = Concept::factory()->create(['value_id' => $value->id]);
        $lesson = Lesson::factory()->create(['concept_id' => $concept->id, 'status' => 'active']);

        return [$value, $lesson];
    }

    public function test_student_cannot_answer_another_schools_survey(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $survey = $this->surveyWithQuestion(['school_id' => $schoolB->id]);

        $this->actingAs($this->studentOf($schoolA))
            ->postJson(route('survey.ajax-submit', $survey), ['answers' => []])
            ->assertStatus(403);

        $this->assertDatabaseCount('survey_responses', 0);
    }

    public function test_student_cannot_answer_assessment_of_a_value_invisible_to_their_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        // القيمة مُفعَّلة لـB فقط، والتقييم عامّ (school_id = NULL) كما ينشئه الأدمن فعلاً.
        [$valueB] = $this->lessonOfValueExclusiveTo($schoolB);
        $schoolA->activeValues()->attach(Value::factory()->create(['status' => 'active'])->id, ['activated_at' => now()]);

        $survey = $this->surveyWithQuestion([
            'survey_type' => 'pre_post_assessment',
            'assessment_phase' => 'pre',
            'trigger_type' => 'on_value_start',
            'value_id' => $valueB->id,
        ]);

        $this->actingAs($this->studentOf($schoolA))
            ->postJson(route('survey.ajax-submit', $survey), ['answers' => []])
            ->assertStatus(403);

        $this->assertDatabaseCount('survey_responses', 0);
    }

    public function test_assessment_reached_through_a_lesson_of_an_invisible_value_is_blocked(): void
    {
        // نفس الناقل لكن عبر lesson_id (تقييم على مستوى الدرس لا القيمة).
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        [, $lessonB] = $this->lessonOfValueExclusiveTo($schoolB);
        $schoolA->activeValues()->attach(Value::factory()->create(['status' => 'active'])->id, ['activated_at' => now()]);

        $survey = $this->surveyWithQuestion([
            'survey_type' => 'pre_post_assessment',
            'assessment_phase' => 'pre',
            'trigger_type' => 'on_lesson_start',
            'lesson_id' => $lessonB->id,
        ]);

        $this->actingAs($this->studentOf($schoolA))
            ->postJson(route('survey.ajax-submit', $survey), ['answers' => []])
            ->assertStatus(403);

        $this->assertDatabaseCount('survey_responses', 0);
    }

    public function test_owner_student_can_answer_their_own_assessment(): void
    {
        // الوجه الإيجابيّ: البوّابة لا تحجب صاحب الحقّ.
        $schoolB = School::factory()->create();
        [$valueB] = $this->lessonOfValueExclusiveTo($schoolB);

        $survey = $this->surveyWithQuestion([
            'survey_type' => 'pre_post_assessment',
            'assessment_phase' => 'pre',
            'trigger_type' => 'on_value_start',
            'value_id' => $valueB->id,
        ]);

        $this->actingAs($this->studentOf($schoolB))
            ->postJson(route('survey.ajax-submit', $survey), ['answers' => []])
            ->assertOk();

        $this->assertDatabaseCount('survey_responses', 1);
    }

    public function test_platform_wide_survey_without_school_stays_open(): void
    {
        // حارس عدم-إفراط: استبيان عامّ بلا school_id ولا ارتباط بقيمة يبقى مفتوحاً للجميع.
        $survey = $this->surveyWithQuestion([]);

        $this->actingAs($this->studentOf(School::factory()->create()))
            ->postJson(route('survey.ajax-submit', $survey), ['answers' => []])
            ->assertOk();

        $this->assertDatabaseCount('survey_responses', 1);
    }
}
