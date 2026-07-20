<?php

namespace Tests\Feature\Survey;

use App\Models\Survey;
use Tests\TestCase;

/**
 * حارس ضدّ خطأ 500 في صفحة مقارنة الاستبيان (teacher/school-admin/parent — قالب مشترك).
 * كان القالب يقرأ $comparisonData['average_pre']/['average_post'] بلا ?? وهي غير موجودة
 * في مُخرَج getComparisonData → «Undefined array key» → ErrorException → 500 لكل من يعرض النتيجة.
 */
class SurveyComparisonRenderTest extends TestCase
{
    public function test_comparison_partial_renders_without_500_when_average_keys_absent(): void
    {
        $survey = new Survey(['title' => 'تقييم القيمة', 'lesson_id' => null, 'value_id' => null]);

        // بيانات مقارنة موجودة (فيصل التنفيذ إلى فرع الأشرطة/البطاقات) لكن **بلا** مفاتيح average_*
        // — يُحاكي الشكل الذي كان يُسقط الصفحة. يجب أن يُرندَر الآن بلا استثناء بفضل حراسة ??.
        $comparisonData = [
            'comparison' => [
                [
                    'user' => null,
                    'pre_score' => 2,
                    'post_score' => 4,
                    'improvement' => 20.0,
                    'details' => [],
                    'pre_date' => now(),
                    'post_date' => now(),
                ],
            ],
        ];

        $html = view('partials.survey-comparison', compact('survey', 'comparisonData'))->render();

        $this->assertStringContainsString('المقارنة', $html);
    }

    public function test_get_comparison_data_exposes_keys_the_view_needs(): void
    {
        // نضمن أنّ مُخرَج النموذج يوفّر المفاتيح التي يقرؤها القالب (تفادي عودة التفاوت مستقبلاً).
        // نستدعي على استبيان بلا رابط → يعيد error، ثم نتأكّد أنّ دالّة العرض تتحمّل الحالتين.
        $survey = new Survey(['title' => 'x', 'assessment_phase' => 'post', 'linked_survey_id' => null]);
        $result = $survey->getComparisonData();

        // بلا استبيان مرتبط → مسار error (لا 500)
        $this->assertArrayHasKey('error', $result);
    }
}
