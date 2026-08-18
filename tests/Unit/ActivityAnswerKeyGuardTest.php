<?php

namespace Tests\Unit;

use App\Services\ActivityGradingService;
use PHPUnit\Framework\TestCase;

/**
 * المصدر الوحيد hasAnswerKey — يشاركه حارس التأليف (منع حفظ اختيار/صح-خطأ بلا مفتاح)
 * والمصحّح (resolveKey). أيّ صيغة مفتاح صالحة يعرفها المصحّح يجب أن يقبلها الحارس.
 */
class ActivityAnswerKeyGuardTest extends TestCase
{
    public function test_correct_index_is_a_valid_key(): void
    {
        $this->assertTrue(ActivityGradingService::hasAnswerKey([
            'type' => 'multiple_choice', 'options' => ['أ', 'ب'], 'correct_index' => 1,
        ]));
    }

    public function test_option_is_correct_flag_is_a_valid_key(): void
    {
        $this->assertTrue(ActivityGradingService::hasAnswerKey([
            'type' => 'multiple_choice',
            'options' => [['text' => 'أ', 'is_correct' => false], ['text' => 'ب', 'is_correct' => true]],
        ]));
    }

    public function test_correct_answer_text_is_a_valid_key(): void
    {
        $this->assertTrue(ActivityGradingService::hasAnswerKey([
            'type' => 'true_false', 'correct_answer' => 'صح',
        ]));
    }

    public function test_missing_key_is_rejected(): void
    {
        // لا correct_index ولا is_correct ولا correct_answer/answer → غير صالح (كان يُحفَظ ثم يرتدّ null)
        $this->assertFalse(ActivityGradingService::hasAnswerKey([
            'type' => 'multiple_choice', 'options' => ['أ', 'ب', 'ج'],
        ]));
    }
}
