<?php

namespace Tests\Feature\Activities;

use App\Http\Controllers\Admin\ActivityManagementController;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * حرّاس تأليف الأسئلة (المرحلتان 1 و2):
 *  - اختيار متعدّد/صح-خطأ بلا مفتاح إجابة → مرفوض (كان يرتدّ null→pending للمعلّم بلا سبب).
 *  - أسئلة الترتيب/اختيار الحروف داخل اختبار متعدّد الأسئلة → مرفوضة (واجهته راديو تمنح صفراً؛
 *    تُؤلَّف كنشاطٍ مستقلّ بسؤال واحد).
 */
class ActivityAuthoringGuardTest extends TestCase
{
    private function validate(array $questions): void
    {
        $ctrl = app(ActivityManagementController::class);
        $m = new \ReflectionMethod($ctrl, 'validateActivityQuestions');
        $m->setAccessible(true);
        $m->invoke($ctrl, $questions);
    }

    public function test_multiple_choice_without_answer_key_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->validate([['type' => 'multiple_choice', 'options' => ['أ', 'ب', 'ج']]]);
    }

    public function test_sequence_type_inside_multi_question_quiz_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->validate([
            ['type' => 'multiple_choice', 'options' => ['أ', 'ب'], 'correct_index' => 0],
            ['type' => 'letter_choice', 'options' => ['ص', 'ل']],
        ]);
    }

    public function test_single_sequence_question_is_allowed(): void
    {
        $this->validate([['type' => 'letter_choice', 'options' => ['ص', 'ل', 'ا', 'ة']]]);
        $this->addToAssertionCount(1); // لم يُرمَ استثناء
    }

    public function test_valid_multi_choice_quiz_is_allowed(): void
    {
        $this->validate([
            ['type' => 'multiple_choice', 'options' => ['أ', 'ب'], 'correct_index' => 0],
            ['type' => 'true_false', 'options' => ['صح', 'خطأ'], 'correct_index' => 1],
        ]);
        $this->addToAssertionCount(1);
    }
}
