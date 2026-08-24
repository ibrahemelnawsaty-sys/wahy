<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Services\ActivityGradingService;
use PHPUnit\Framework\TestCase;

/**
 * ترتيب الجمل: الجُمل تحوي فواصل، فكان المصحّح يقسّم المرجع النصّيّ على الفواصل ويختلّ التطابق
 * (كلّه خطأ = صفر) — خاصّةً كلّما زاد عدد الجمل. الآن الترتيب الصحيح = ترتيب الـ options،
 * ولا يُعتَدّ بمرجعٍ نصّيّ فارغ (answer='' المتبقّي من نموذج التأليف).
 */
class SentenceOrderingGradingTest extends TestCase
{
    private function activity(array $q): Activity
    {
        $a = new Activity();
        $a->type = 'quiz';
        $a->question_type = 'multiple_choice';
        $a->manual_review = false;
        $a->questions = [$q];

        return $a;
    }

    public function test_correct_order_scores_full_even_with_commas_in_sentences(): void
    {
        $sentences = ['ذهبت إلى السوق، واشتريت خبزاً', 'ثمّ رجعت، وأكلت', 'وبعدها نمتُ مبكراً'];
        // نموذج مؤلِّف قديم قد يخزّن correct_answer نصّاً مفصولاً بفواصل — يجب ألّا يُفسِد التصحيح
        $a = $this->activity([
            'type' => 'sentence_order', 'options' => $sentences,
            'correct_answer' => implode('، ', $sentences),
        ]);

        $this->assertSame(100, ActivityGradingService::grade($a, $sentences));
    }

    public function test_options_only_scores_full_at_any_count(): void
    {
        foreach ([2, 3, 6] as $n) {
            $sentences = array_map(fn ($i) => "الجملة رقم {$i}، وتكملتها", range(1, $n));
            $a = $this->activity(['type' => 'sentence_order', 'options' => $sentences]);
            $this->assertSame(100, ActivityGradingService::grade($a, $sentences), "n=$n");
        }
    }

    public function test_lingering_empty_answer_is_ignored(): void
    {
        $sentences = ['أوّلاً، نبدأ', 'ثانياً، نكمل', 'أخيراً، ننتهي'];
        $a = $this->activity(['type' => 'sentence_order', 'options' => $sentences, 'answer' => '']);
        $this->assertSame(100, ActivityGradingService::grade($a, $sentences));
    }

    public function test_wrong_order_scores_partial_not_zero(): void
    {
        $sentences = ['جملة واحدة، أ', 'جملة اثنتان، ب', 'جملة ثلاث، ج', 'جملة أربع، د', 'جملة خمس، هـ'];
        $answer = $sentences;
        [$answer[0], $answer[1]] = [$answer[1], $answer[0]]; // بدّل الأوّلين فقط
        $a = $this->activity(['type' => 'sentence_order', 'options' => $sentences]);
        $this->assertSame(60, ActivityGradingService::grade($a, $answer)); // 3 من 5 صحيحة
    }

    public function test_reveal_text_not_mangled_by_commas(): void
    {
        $sentences = ['ذهبت، ثمّ رجعت', 'وأكلت، ثمّ نمت'];
        $a = $this->activity([
            'type' => 'sentence_order', 'options' => $sentences,
            'correct_answer' => implode('، ', $sentences),
        ]);
        $text = ActivityGradingService::correctAnswerText($a);
        // يعرض جملتين مرقّمتين (لا يُقسِّم الجملة على فاصلتها الداخليّة)
        $this->assertStringContainsString('1) ذهبت، ثمّ رجعت', $text);
        $this->assertStringContainsString('2) وأكلت، ثمّ نمت', $text);
    }
}
