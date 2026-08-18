<?php

namespace Tests\Feature\Survey;

use App\Models\Activity;
use App\Models\ActivitySubmission;
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
 * إلزامٌ حقيقيّ: ما دام تقييمٌ **قبليّ** إجباريّ معلَّقاً، لا يُفتح نشاطُ الدرس ولا يُقبل تسليمه.
 *
 * لماذا خادميّاً؟ قفل المتصفّح في components/survey-popup تجربةُ استخدام لا حدّ أمان — يُلتفّ
 * عليه بتعطيل JavaScript أو نداء المسار مباشرةً. فبدونه تبقى «إجباريّ» كلمةً لا أثر لها.
 *
 * **صمّام الأمان (جوهريّ):** الحجب مشروط بأن يكون الاستبيان **قابلاً للإجابة فعلاً** — استبيانٌ
 * بلا أسئلة كان سيقفل الطالب خارج درسه إلى الأبد بلا أيّ مخرج. عند الشكّ: يُفتح لا يُقفل.
 *
 * ولا يحجب **البعديّ** إطلاقاً: استحقاقه مشروطٌ بإنهاء الأنشطة، فحجبُها به قفلٌ دائريّ.
 */
class PreAssessmentGatesActivitiesTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Value $value;

    private Lesson $lesson;

    private Activity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
        $this->value = Value::factory()->create(['status' => 'active']);
        $this->school->activeValues()->attach($this->value->id, ['activated_at' => now()]);
        $concept = Concept::factory()->create(['value_id' => $this->value->id]);
        $this->lesson = Lesson::factory()->create(['concept_id' => $concept->id, 'status' => 'active']);
        $this->activity = Activity::factory()->create([
            'lesson_id' => $this->lesson->id,
            'status' => 'active',
            'all_schools_mode' => 'direct',
            'approval_status' => 'approved',
        ]);
    }

    private function student(): User
    {
        return User::factory()->create(['role' => 'student', 'school_id' => $this->school->id]);
    }

    private function assessment(string $phase, array $attrs = [], int $questions = 1): Survey
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

    public function test_pending_lesson_pre_assessment_blocks_opening_the_activity(): void
    {
        $student = $this->student();
        $this->assessment('pre', ['lesson_id' => $this->lesson->id]);

        // يُعاد للدرس حيث تنتظره النافذة — لا 403 مسدود يتركه حائراً.
        $this->actingAs($student)
            ->get(route('student.activity', $this->activity->id))
            ->assertRedirect(route('student.lesson', $this->lesson->id));
    }

    public function test_pending_pre_assessment_blocks_submitting_the_activity(): void
    {
        $student = $this->student();
        $this->assessment('pre', ['lesson_id' => $this->lesson->id]);

        $this->actingAs($student)
            ->postJson(route('student.activity.submit', $this->activity->id), ['answer' => 'x'])
            ->assertStatus(403);

        $this->assertDatabaseCount('activity_submissions', 0);
    }

    public function test_value_level_pre_assessment_also_blocks(): void
    {
        $student = $this->student();
        $this->assessment('pre', ['value_id' => $this->value->id]);

        $this->actingAs($student)
            ->get(route('student.activity', $this->activity->id))
            ->assertRedirect(route('student.lesson', $this->lesson->id));
    }

    public function test_answering_the_assessment_unlocks_the_activity(): void
    {
        $student = $this->student();
        $pre = $this->assessment('pre', ['lesson_id' => $this->lesson->id]);

        SurveyResponse::create([
            'survey_id' => $pre->id,
            'user_id' => $student->id,
            'answers' => [],
            'completed_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('student.activity', $this->activity->id))
            ->assertOk();
    }

    public function test_no_pending_assessment_leaves_activities_open(): void
    {
        $this->actingAs($this->student())
            ->get(route('student.activity', $this->activity->id))
            ->assertOk();
    }

    // ---------------- صمّامات الأمان: عند الشكّ يُفتح لا يُقفل ----------------

    public function test_assessment_without_questions_never_locks_the_student_out(): void
    {
        // الخطر الأكبر: استبيانٌ بلا أسئلة لا يمكن إنهاؤه ⟶ قفلٌ أبديّ خارج الدرس.
        $student = $this->student();
        $this->assessment('pre', ['lesson_id' => $this->lesson->id], questions: 0);

        $this->actingAs($student)
            ->get(route('student.activity', $this->activity->id))
            ->assertOk();
    }

    public function test_non_mandatory_assessment_does_not_block(): void
    {
        // احترام خيار الأدمن: ما لا يُحجب في النافذة لا يُحجب خادميّاً.
        $student = $this->student();
        $this->assessment('pre', [
            'lesson_id' => $this->lesson->id,
            'is_mandatory' => false,
            'is_popup' => false,
        ]);

        $this->actingAs($student)
            ->get(route('student.activity', $this->activity->id))
            ->assertOk();
    }

    public function test_post_assessment_never_blocks_activities(): void
    {
        // قفلٌ دائريّ: استحقاق البعديّ مشروطٌ بإنهاء الأنشطة، فحجبُها به يمنع إنهاءها.
        $student = $this->student();
        ActivitySubmission::create([
            'activity_id' => $this->activity->id,
            'student_id' => $student->id,
            'answer' => 'إجابة',
            'status' => 'approved',
            'submitted_at' => now(),
        ]);
        $this->assessment('post', ['lesson_id' => $this->lesson->id]);

        $this->actingAs($student)
            ->get(route('student.activity', $this->activity->id))
            ->assertOk();
    }

    public function test_expired_assessment_does_not_block(): void
    {
        // نافذة زمنيّة منتهية ⟶ الاستبيان غير مُجاب أصلاً ⟶ لا يجوز أن يقفل شيئاً.
        $student = $this->student();
        $this->assessment('pre', [
            'lesson_id' => $this->lesson->id,
            'end_date' => now()->subDay(),
        ]);

        $this->actingAs($student)
            ->get(route('student.activity', $this->activity->id))
            ->assertOk();
    }

    public function test_teacher_is_not_gated_by_student_assessments(): void
    {
        // الحارس يخصّ الطالب — لا يعرقل أدواراً أخرى تفتح النشاط.
        $this->assessment('pre', ['lesson_id' => $this->lesson->id]);
        $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $this->school->id]);

        $this->actingAs($teacher)
            ->get(route('student.activity', $this->activity->id))
            ->assertForbidden(); // يُصدّه حارس الدور (403) لا حارس الاستبيان (تحويل للدرس)
    }
}
