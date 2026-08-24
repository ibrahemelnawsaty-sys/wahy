<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Services\ActivityGradingService;
use PHPUnit\Framework\TestCase;

/**
 * حالات حديّة في المصحّح (تدقيق عميق):
 *  - L1: correct_index=0 لا يمنح 100 لإجابةٍ نصّيّة خاطئة تنهار (int)=0.
 *  - L2: إجابة قصيرة تشبه JSON (true/007/null) تبقى نصّاً ولا تُفسَّر.
 */
class GradingEdgeCasesTest extends TestCase
{
    private function activity(array $q, string $type = 'multiple_choice'): Activity
    {
        $a = new Activity();
        $a->type = $type;
        $a->question_type = 'multiple_choice';
        $a->manual_review = false;
        $a->questions = [$q];

        return $a;
    }

    public function test_correct_index_zero_does_not_reward_wrong_text(): void
    {
        $q = ['type' => 'multiple_choice', 'options' => ['نعم', 'لا'], 'correct_index' => 0];
        $this->assertSame(0, ActivityGradingService::grade($this->activity($q), 'لا'), 'نصّ خاطئ لا يُمنح 100');
        $this->assertSame(0, ActivityGradingService::grade($this->activity($q), 1), 'فهرس خاطئ = 0');
        $this->assertSame(100, ActivityGradingService::grade($this->activity($q), 0), 'الفهرس الصحيح = 100');
    }

    public function test_short_answer_json_like_values_stay_text(): void
    {
        foreach (['true', 'false', 'null', '007', '1.0', 'نعم'] as $val) {
            $q = ['type' => 'short_answer', 'correct_answer' => $val];
            $this->assertSame(100, ActivityGradingService::grade($this->activity($q), $val), "«{$val}» يبقى نصّاً");
        }
    }

    public function test_ordering_json_array_string_still_decodes(): void
    {
        $o = ['أوّلاً', 'ثانياً', 'ثالثاً'];
        $q = ['type' => 'sentence_order', 'options' => $o];
        $this->assertSame(100, ActivityGradingService::grade($this->activity($q, 'quiz'), json_encode($o, JSON_UNESCAPED_UNICODE)));
    }
}
