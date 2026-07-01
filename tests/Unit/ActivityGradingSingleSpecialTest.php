<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Services\ActivityGradingService as Grader;
use PHPUnit\Framework\TestCase;

/**
 * يثبت إصلاح تصحيح الأنشطة الخاصة أحادية السؤال داخل نشاط من نوع quiz (الافتراضي)،
 * ومفتاح موافقة المعلم، واستخراج الإجابة الصحيحة للعرض التعليمي.
 */
class ActivityGradingSingleSpecialTest extends TestCase
{
    private function activity(array $attrs): Activity
    {
        return new Activity($attrs);
    }

    public function test_word_order_single_question_in_quiz_scores_full_when_correct(): void
    {
        $activity = $this->activity([
            'type' => 'quiz',
            'passing_score' => 50,
            'questions' => [[
                'type' => 'word_order',
                'question' => 'رتّب الكلمات',
                'options' => ['الصلاة', 'عماد', 'الدين'],
                'points' => 10,
            ]],
        ]);

        // إجابة صحيحة بنفس ترتيب الخيارات
        $this->assertSame(100, Grader::grade($activity, json_encode(['الصلاة', 'عماد', 'الدين'], JSON_UNESCAPED_UNICODE)));
        // إجابة مقلوبة → أقل من 100 (كانت تُعطى صفراً دائماً قبل الإصلاح)
        $this->assertLessThan(100, Grader::grade($activity, json_encode(['الدين', 'عماد', 'الصلاة'], JSON_UNESCAPED_UNICODE)));
    }

    public function test_sentence_order_single_question_in_quiz_scores(): void
    {
        $activity = $this->activity([
            'type' => 'quiz',
            'questions' => [[
                'type' => 'sentence_order',
                'question' => 'رتّب الجمل',
                'options' => ['الجملة الأولى', 'الجملة الثانية', 'الجملة الثالثة'],
                'points' => 10,
            ]],
        ]);

        $this->assertSame(100, Grader::grade($activity, json_encode(['الجملة الأولى', 'الجملة الثانية', 'الجملة الثالثة'], JSON_UNESCAPED_UNICODE)));
    }

    public function test_letter_choice_single_question_in_quiz_uses_target_word(): void
    {
        $activity = $this->activity([
            'type' => 'quiz',
            'questions' => [[
                'type' => 'letter_choice',
                'question' => 'كوّن الكلمة',
                'word' => 'صلاة',
                'options' => ['ص', 'ل', 'ا', 'ة'],
                'answer' => 'ص',
                'correct_index' => 0,
                'points' => 10,
            ]],
        ]);

        // الكلمة الصحيحة (كانت تُقارَن بحرف واحد "ص" قبل الإصلاح فتفشل)
        $this->assertSame(100, Grader::grade($activity, 'صلاة'));
        $this->assertSame(0, Grader::grade($activity, 'صلا'));
    }

    public function test_manual_review_forces_pending_null(): void
    {
        $activity = $this->activity([
            'type' => 'quiz',
            'manual_review' => true,
            'questions' => [[
                'type' => 'word_order',
                'options' => ['أ', 'ب', 'ج'],
            ]],
        ]);

        $this->assertNull(Grader::grade($activity, json_encode(['أ', 'ب', 'ج'])));
    }

    public function test_short_answer_ignores_invisible_and_hamza_differences(): void
    {
        $activity = $this->activity([
            'type' => 'quiz',
            'questions' => [[
                'type' => 'short_answer',
                'question' => 'أكمل',
                'answer' => 'الصلاة',
                'points' => 10,
            ]],
        ]);

        // مطابقة تامة
        $this->assertSame(100, Grader::grade($activity, 'الصلاة'));
        // محرف اتجاه غير مرئي (RLM U+200F) يحقنه لوحة RTL/النسخ — يجب أن يبقى صحيحاً
        $this->assertSame(100, Grader::grade($activity, "الصلاة\u{200F}"));
        // محرف ZWNJ غير مرئي
        $this->assertSame(100, Grader::grade($activity, "\u{200C}الصلاة"));

        // توحيد الكاف الفارسية (ک U+06A9) مع العربية (ك U+0643)
        $kaf = $this->activity([
            'type' => 'quiz',
            'questions' => [['type' => 'short_answer', 'answer' => 'كتاب']],
        ]);
        $this->assertSame(100, Grader::grade($kaf, "\u{06A9}تاب")); // کتاب بالكاف الفارسية

        // توحيد الهمزة على واو (ؤ→و): المخزَّن «لؤلؤ» وإجابة الطالب «لولو»
        $hamza = $this->activity([
            'type' => 'quiz',
            'questions' => [['type' => 'short_answer', 'answer' => 'لؤلؤ']],
        ]);
        $this->assertSame(100, Grader::grade($hamza, 'لولو'));
    }

    public function test_correct_answer_text_for_reveal(): void
    {
        $letter = $this->activity([
            'type' => 'quiz',
            'questions' => [['type' => 'letter_choice', 'word' => 'صلاة', 'options' => ['ص', 'ل', 'ا', 'ة']]],
        ]);
        $this->assertSame('صلاة', Grader::correctAnswerText($letter));

        $order = $this->activity([
            'type' => 'quiz',
            'questions' => [['type' => 'word_order', 'options' => ['الصلاة', 'عماد', 'الدين']]],
        ]);
        $this->assertStringContainsString('1) الصلاة', Grader::correctAnswerText($order));
    }
}
