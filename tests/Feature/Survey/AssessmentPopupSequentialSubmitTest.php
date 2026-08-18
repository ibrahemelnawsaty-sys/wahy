<?php

namespace Tests\Feature\Survey;

use App\Models\Coin;
use App\Models\Concept;
use App\Models\Lesson;
use App\Models\Point;
use App\Models\School;
use App\Models\Survey;
use App\Models\User;
use App\Models\Value;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * التسلسل: يُرسِل الطالب الاستبيان الأوّل ⟶ يبدأ الذي بعده ⟶ وبعد الأخير يُفكّ القفل ويعود للدرس.
 *
 * §3 (سلامة الاقتصاد): مسار الاستبيان **لا يسكّ** نقاطاً ولا كوينز اليوم، وهذا حارسٌ يُثبّت ذلك —
 * فالنافذة مسارُ كتابةٍ جديد نحو نفس النقطة، ولو أضاف أحدٌ منحاً هنا لتضاعف مع المسار القديم.
 */
class AssessmentPopupSequentialSubmitTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Lesson $lesson;

    private Value $value;

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

    private function assessment(string $phase, array $attrs = [], bool $required = false): Survey
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

        $survey->questions()->create([
            'question_text' => 'سؤال',
            'question_type' => 'text',
            'is_required' => $required,
            'order' => 1,
        ]);

        return $survey;
    }

    public function test_queue_advances_then_clears_after_the_last_survey(): void
    {
        $student = $this->student();
        $valuePre = $this->assessment('pre', ['value_id' => $this->value->id]);
        $lessonPre = $this->assessment('pre', ['lesson_id' => $this->lesson->id]);

        $this->actingAs($student)->get(route('student.lesson', $this->lesson->id))->assertOk();

        // الأوّل ⟶ ما زال هناك تالٍ
        $this->actingAs($student)
            ->postJson(route('survey.ajax-submit', $valuePre), ['answers' => []])
            ->assertOk()
            ->assertJson(['success' => true, 'has_more_surveys' => true]);

        // الأخير ⟶ ينتهي الطابور ويُفكّ الحجب
        $this->actingAs($student)
            ->postJson(route('survey.ajax-submit', $lessonPre), ['answers' => []])
            ->assertOk()
            ->assertJson(['success' => true, 'has_more_surveys' => false]);

        $this->assertNull(session('show_survey_popup'), 'يجب رفع الحجب بعد آخر استبيان');
        $this->assertNull(session('pending_surveys'));
        $this->assertDatabaseCount('survey_responses', 2);
    }

    public function test_duplicate_submit_is_a_true_no_op(): void
    {
        $student = $this->student();
        $pre = $this->assessment('pre', ['lesson_id' => $this->lesson->id]);

        $this->actingAs($student)->postJson(route('survey.ajax-submit', $pre), ['answers' => []])->assertOk();
        $this->actingAs($student)->postJson(route('survey.ajax-submit', $pre), ['answers' => []])->assertStatus(400);

        $this->assertDatabaseCount('survey_responses', 1);
    }

    public function test_survey_submission_mints_no_points_or_coins(): void
    {
        $student = $this->student();
        $pre = $this->assessment('pre', ['lesson_id' => $this->lesson->id]);

        $this->actingAs($student)->postJson(route('survey.ajax-submit', $pre), ['answers' => []])->assertOk();

        $this->assertSame(0, Point::where('user_id', $student->id)->count(), 'الاستبيان لا يمنح نقاطاً');
        $this->assertSame(0, Coin::where('user_id', $student->id)->count(), 'الاستبيان لا يمنح كوينز');
        $this->assertSame(0, DB::table('award_ledger')->count(), 'لا قيد في دفتر المنح');
    }

    public function test_required_question_blocks_submit_and_writes_nothing(): void
    {
        $student = $this->student();
        $pre = $this->assessment('pre', ['lesson_id' => $this->lesson->id], required: true);

        $this->actingAs($student)
            ->postJson(route('survey.ajax-submit', $pre), ['answers' => []])
            ->assertStatus(422);

        $this->assertDatabaseCount('survey_responses', 0);
    }

    public function test_popup_advances_by_explicit_order_not_by_hidden_style(): void
    {
        // العلّة: انتقاء التالي كان `.survey-form[style*="display: none"]:not([data-survey-id=X])`.
        // بعد إخفاء الحاليّ يصير هو نفسه مطابقاً، فمع 3 استبيانات فأكثر يُعيد querySelector
        // **الأوّل المُجاب** (أسبق في ترتيب المستند) فيعلق الطالب على استبيان أجاب عليه.
        $student = $this->student();
        $this->assessment('pre', ['value_id' => $this->value->id]);
        $this->assessment('pre', ['lesson_id' => $this->lesson->id]);

        $html = (string) $this->actingAs($student)
            ->get(route('student.lesson', $this->lesson->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-order="0"', $html, 'كل نموذج يحمل ترتيبه الصريح');
        $this->assertStringNotContainsString('[style*="display: none"]', $html, 'الانتقاء الهشّ أُزيل');
    }
}
