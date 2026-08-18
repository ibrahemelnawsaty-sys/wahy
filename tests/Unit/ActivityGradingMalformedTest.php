<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Services\ActivityGradingService as Grader;
use PHPUnit\Framework\TestCase;

/**
 * المرحلة 2: إدخالٌ مشوَّه (ليس مصفوفة) ⇒ مراجعة يدويّة (null) لا صفرٌ زائف يُحتفَل به كإجابة خاطئة؛
 * واختيار الحروف بعد توحيد النموذج (الكلمة = الحروف بترتيبها) يُصحَّح 100 عند التكوين الصحيح.
 */
class ActivityGradingMalformedTest extends TestCase
{
    private function activity(array $attrs): Activity
    {
        return new Activity($attrs);
    }

    public function test_malformed_ordering_answer_is_manual_review_not_zero(): void
    {
        $activity = $this->activity([
            'type' => 'exercise',
            'questions' => [['type' => 'word_order', 'options' => ['أ', 'ب', 'ج']]],
        ]);

        // نصٌّ غير قابل للتحليل (لا مصفوفة) → null (مراجعة) بدل 0 (كان يُخلَط مع «ترتيب خاطئ»)
        $this->assertNull(Grader::grade($activity, 'ليست مصفوفة'));
    }

    public function test_malformed_quiz_answer_is_manual_review_not_zero(): void
    {
        $activity = $this->activity([
            'type' => 'quiz',
            'questions' => [
                ['type' => 'multiple_choice', 'options' => ['أ', 'ب'], 'correct_index' => 0],
                ['type' => 'multiple_choice', 'options' => ['أ', 'ب'], 'correct_index' => 1],
            ],
        ]);

        $this->assertNull(Grader::grade($activity, 'ليست مصفوفة'));
    }

    public function test_letter_choice_word_derived_from_letters_grades_full(): void
    {
        // يحاكي شكل النموذج بعد الإصلاح: الكلمة الهدف = الحروف بترتيبها (word = join(options))
        $activity = $this->activity([
            'type' => 'quiz',
            'questions' => [['type' => 'letter_choice', 'options' => ['ص', 'ل', 'ا', 'ة'], 'word' => 'صلاة']],
        ]);

        $this->assertSame(100, Grader::grade($activity, json_encode(['ص', 'ل', 'ا', 'ة'], JSON_UNESCAPED_UNICODE)));
    }
}
