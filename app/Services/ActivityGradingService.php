<?php

namespace App\Services;

use App\Models\Activity;

/**
 * منطق تصحيح أنشطة المنصة بصورة موحّدة وقابلة للاختبار.
 *
 * يستقبل النشاط وإجابة الطالب (raw)، ويُرجع:
 * - integer 0..100: درجة محسوبة آلياً
 * - null: النشاط يحتاج مراجعة يدوية من المعلم
 *
 * يدعم الأنواع:
 *   - multiple_choice  (اختيار من متعدد)
 *   - true_false       (صح / خطأ)
 *   - short_answer     (إجابة قصيرة — مقارنة نص بعد تطبيع)
 *   - letter_choice    (تكوين كلمة من حروف)
 *   - word_ordering    (ترتيب كلمات)
 *   - sentence_ordering (ترتيب جمل)
 *   - image_ordering   (ترتيب صور)
 *   - essay / upload / creative / project → null (مراجعة يدوية)
 */
class ActivityGradingService
{
    /**
     * تصحيح إجابة نشاط وإرجاع الدرجة بالنسبة المئوية أو null للمراجعة اليدوية.
     */
    public static function grade(Activity $activity, $rawAnswer): ?int
    {
        $answer = self::normalizeAnswer($rawAnswer);
        $questions = is_array($activity->questions) ? $activity->questions : [];

        // أنواع نشاط تتطلب مراجعة يدوية دائماً (بحسب نوع النشاط نفسه)
        if (in_array($activity->type, ['essay', 'upload', 'creative', 'project', 'practical', 'discussion'], true)) {
            return null;
        }

        // نشاط ترتيب صور مستقل: المخزن مصفوفة صور بترتيب أصلي
        // إجابة الطالب: [{image_url, selected_order}] — يقارن selected_order بـ order الأصلي.
        if (in_array($activity->type, ['image_order', 'image_ordering'], true)) {
            return self::gradeImageOrder($activity, $answer);
        }

        // النشاط من نوع quiz أو أي نشاط متعدد الأسئلة → تصحيح لكل سؤال حسب نوعه
        if (!empty($questions) && ($activity->type === 'quiz' || count($questions) > 1)) {
            return self::gradeQuiz($questions, $answer);
        }

        // النوع الفعلي لنشاط أحادي السؤال: نموذج الإنشاء يحفظه داخل questions[0].type
        // بينما question_type على مستوى النشاط يبقى غالباً الافتراضي 'multiple_choice'.
        $firstQType = self::normalizeType($questions[0]['type'] ?? $questions[0]['question_type'] ?? null);
        $type = $firstQType ?: ($activity->question_type ?: $activity->type);

        // أنواع سؤال تتطلب مراجعة يدوية
        if (in_array($type, ['essay', 'upload', 'creative', 'project', 'practical', 'discussion', 'file_upload'], true)) {
            return null;
        }

        // الأنشطة الفردية ذات نوع سؤال واحد
        return match ($type) {
            'multiple_choice'                   => self::gradeMultipleChoice($activity, $answer),
            'true_false'                        => self::gradeTrueFalse($activity, $answer),
            'short_answer'                      => self::gradeShortAnswer($activity, $answer),
            'letter_choice'                     => self::gradeLetterChoice($activity, $answer),
            'word_ordering', 'word_order'       => self::gradeOrdering($activity, $answer),
            'sentence_ordering', 'sentence_order' => self::gradeOrdering($activity, $answer),
            'image_ordering', 'image_order'     => self::gradeOrdering($activity, $answer),
            default                             => null,
        };
    }

    /**
     * النسبة المئوية للنجاح المعتبرة "مكتمل"
     */
    public static function passingScoreFor(Activity $activity): int
    {
        return (int) ($activity->passing_score ?? 50);
    }

    /**
     * تطبيع الإجابة الواردة من JSON أو نص.
     */
    private static function normalizeAnswer($rawAnswer)
    {
        if (is_array($rawAnswer)) {
            return $rawAnswer;
        }

        if (is_string($rawAnswer)) {
            $decoded = json_decode($rawAnswer, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $rawAnswer;
    }

    /**
     * توحيد صيغ نوع السؤال (مع/بدون لاحقة -ing) لتطابق فروع match.
     */
    private static function normalizeType(?string $t): ?string
    {
        if ($t === null) {
            return null;
        }
        $map = [
            'word_order'     => 'word_ordering',
            'sentence_order' => 'sentence_ordering',
            'image_order'    => 'image_ordering',
        ];
        return $map[$t] ?? $t;
    }

    /**
     * الحصول على الإجابة الصحيحة من بنية الـ activity (متغيرة الأسماء).
     */
    private static function correctAnswerOf(Activity $activity)
    {
        $questions = $activity->questions;

        if (is_array($questions) && !empty($questions)) {
            $first = $questions[0] ?? null;
            if (is_array($first)) {
                return $first['correct_answer'] ?? $first['answer'] ?? null;
            }
        }

        return $activity->correct_answer ?? null;
    }

    /**
     * استرجاع أول سؤال بصيغة مصفوفة (أو مصفوفة فارغة).
     */
    private static function firstQuestion(Activity $activity): array
    {
        $questions = $activity->questions;
        if (is_array($questions) && !empty($questions)) {
            $first = $questions[0] ?? null;
            if (is_array($first)) {
                return $first;
            }
        }
        return [];
    }

    /**
     * استخراج مفتاح الإجابة الصحيحة من سؤال بصِيَغه المتعددة (توحيد الاصطلاحات).
     * يدعم: correct_index، correct (كفهرس رقمي)، options[].is_correct (اشتقاق الفهرس)،
     *        correct_answer، answer، correct (كنص).
     *
     * @return array{index: int|null, text: mixed, has: bool}
     *   - index: دليل الخيار الصحيح إن وُجد
     *   - text : نص/قيمة الإجابة الصحيحة إن وُجدت
     *   - has  : هل يوجد مفتاح إجابة صالح أصلاً (وإلا → مراجعة يدوية)
     */
    private static function resolveKey(array $q): array
    {
        $index = null;

        // 1) فهرس صريح
        if (isset($q['correct_index']) && is_numeric($q['correct_index'])) {
            $index = (int) $q['correct_index'];
        } elseif (isset($q['correct']) && is_numeric($q['correct'])) {
            // مفتاح البذرة/المحتوى القديم: "correct" => رقم الخيار
            $index = (int) $q['correct'];
        } else {
            // 2) اشتقاق الفهرس من options[].is_correct
            $opts = is_array($q['options'] ?? null) ? $q['options'] : [];
            foreach ($opts as $i => $opt) {
                if (is_array($opt) && !empty($opt['is_correct'])) {
                    $index = (int) $i;
                    break;
                }
            }
        }

        // 3) النص الصحيح
        $text = $q['correct_answer'] ?? $q['answer'] ?? null;
        if (($text === null || $text === '') && isset($q['correct']) && is_string($q['correct'])) {
            $text = $q['correct'];
        }

        $has = ($index !== null) || ($text !== null && $text !== '');

        return ['index' => $index, 'text' => $text, 'has' => $has];
    }

    /**
     * مقارنة موحّدة بين إجابة الطالب (قد تكون: index رقمي / نص الخيار / bool)
     * والإجابة الصحيحة (قد تكون: index رقمي / نص الخيار / bool).
     *
     * يستخدم مصفوفة options لتحويل الـ index إلى نص الخيار قبل المقارنة.
     *
     * @param mixed $studentAnswer إجابة الطالب
     * @param mixed $correctAnswer الإجابة الصحيحة المحفوظة
     * @param array $options قائمة الخيارات للسؤال (لتحويل الأرقام إلى نصوص)
     * @param int|null $correctIndex دليل الخيار الصحيح إن وُجد
     */
    private static function optionMatches($studentAnswer, $correctAnswer, array $options, $correctIndex = null): bool
    {
        if ($studentAnswer === null) {
            return false;
        }

        // 1) لو لدينا correct_index صريح، نقارن دليل الطالب به مباشرة
        if ($correctIndex !== null && is_numeric($correctIndex)) {
            return (int) $studentAnswer === (int) $correctIndex;
        }

        // 2) تحويل دليل الطالب إلى نص الخيار إن أمكن
        $studentText = $studentAnswer;
        if (is_numeric($studentAnswer) && isset($options[(int) $studentAnswer])) {
            $opt = $options[(int) $studentAnswer];
            $studentText = is_array($opt) ? ($opt['text'] ?? $opt['label'] ?? '') : (string) $opt;
        }

        // 3) تحويل الإجابة الصحيحة كذلك (إن كانت دليلاً رقمياً)
        $correctText = $correctAnswer;
        if (is_numeric($correctAnswer) && isset($options[(int) $correctAnswer])) {
            $opt = $options[(int) $correctAnswer];
            $correctText = is_array($opt) ? ($opt['text'] ?? $opt['label'] ?? '') : (string) $opt;
        }

        // 4) لو كلاهما نص → نقارن نصياً مع التطبيع
        if (is_string($studentText) && is_string($correctText)) {
            return self::textEquals($studentText, $correctText);
        }

        return self::scalarEquals($studentText, $correctText);
    }

    private static function gradeMultipleChoice(Activity $activity, $answer): ?int
    {
        $firstQ = self::firstQuestion($activity);
        $key = self::resolveKey($firstQ);

        // لا مفتاح إجابة صالح → مراجعة يدوية بدل منح صفر زائف أو تطابق كاذب
        if (!$key['has']) {
            return null;
        }

        $student = is_array($answer) ? ($answer[0] ?? null) : $answer;
        $options = is_array($firstQ['options'] ?? null) ? $firstQ['options'] : [];

        return self::optionMatches($student, $key['text'], $options, $key['index']) ? 100 : 0;
    }

    private static function gradeTrueFalse(Activity $activity, $answer): ?int
    {
        $firstQ = self::firstQuestion($activity);
        $key = self::resolveKey($firstQ);

        $student = is_array($answer) ? ($answer[0] ?? null) : $answer;
        $options = is_array($firstQ['options'] ?? null) ? $firstQ['options'] : [];

        // الطريقة المعتمدة: إن وُجد options، حوّل دليل الطالب إلى النص ثم قارن نصياً
        if (!empty($options)) {
            if (!$key['has']) {
                return null;
            }
            return self::optionMatches($student, $key['text'], $options, $key['index']) ? 100 : 0;
        }

        // fallback: مقارنة بـ bool عند عدم وجود options (سؤال حر)
        $rawCorrect = $key['text'];
        if ($rawCorrect === null && $key['index'] !== null) {
            $rawCorrect = $key['index'];
        }
        $correctBool = self::toBool($rawCorrect);

        // لا مفتاح إجابة صالح → مراجعة يدوية
        if ($correctBool === null) {
            return null;
        }

        $studentBool = self::toBool($student);
        if ($studentBool === null) {
            return 0;
        }

        return $studentBool === $correctBool ? 100 : 0;
    }

    private static function gradeShortAnswer(Activity $activity, $answer): ?int
    {
        $firstQ = self::firstQuestion($activity);
        $correct = $firstQ['correct_answer'] ?? $firstQ['answer'] ?? null;
        if (($correct === null || $correct === '') && isset($firstQ['correct']) && is_string($firstQ['correct'])) {
            $correct = $firstQ['correct'];
        }
        if ($correct === null || $correct === '') {
            $correct = self::correctAnswerOf($activity);
        }
        if ($correct === null || $correct === '') {
            // لا توجد إجابة مخزّنة → مراجعة يدوية
            return null;
        }

        $student = is_array($answer) ? ($answer[0] ?? '') : (string) $answer;

        return self::textEquals($student, (string) $correct) ? 100 : 0;
    }

    /**
     * نشاط اختيار حروف: الكلمة المستهدفة محفوظة في عدة مفاتيح ممكنة.
     * نُفضّل (word/target_word) إن وُجِدت، وإلا نقع على correct_answer.
     * إجابة الطالب: مصفوفة حروف أو نص متصل.
     */
    private static function gradeLetterChoice(Activity $activity, $answer): ?int
    {
        $firstQ = self::firstQuestion($activity);

        // الأولوية: word/target_word ثم correct_answer
        $target = $firstQ['word']
            ?? $firstQ['target_word']
            ?? $firstQ['correct_answer']
            ?? $firstQ['answer']
            ?? null;

        if ($target === null || $target === '') {
            // لا كلمة هدف → مراجعة يدوية
            return null;
        }

        $studentText = is_array($answer) ? implode('', $answer) : (string) $answer;

        // حرس: إذا كان "الصحيح" حرفاً واحداً والإجابة كلمة، فالأرجح أنها بيانات قديمة فاسدة
        // (الكلمة الهدف مخزّنة كفهرس خيار لا ككلمة) → مراجعة يدوية بدل تقييم خاطئ.
        if (mb_strlen((string) $target) <= 1 && mb_strlen($studentText) >= 2) {
            return null;
        }

        return self::textEquals($studentText, (string) $target) ? 100 : 0;
    }

    /**
     * منطق ترتيب موحد لـ word_ordering / sentence_ordering / image_ordering.
     * الترتيب الصحيح = ترتيب الـ options كما حفظها المعلم. إجابة الطالب = مصفوفة العناصر بالترتيب المُعاد.
     * fallback لـ correct_answer إن كانت محفوظة كنص مفصول.
     */
    private static function gradeOrdering(Activity $activity, $answer): ?int
    {
        $firstQ = self::firstQuestion($activity);
        $correct = self::correctAnswerOf($activity);

        // الترتيب الصحيح هو ترتيب الـ options كما حفظها الأدمن (الافتراضي)
        if ($correct === null && !empty($firstQ['options']) && is_array($firstQ['options'])) {
            $correct = array_map(
                fn($opt) => is_array($opt) ? ($opt['text'] ?? $opt['label'] ?? '') : (string) $opt,
                $firstQ['options']
            );
        }

        if ($correct === null) {
            // لا ترتيب مرجعي → مراجعة يدوية
            return null;
        }

        if (is_string($correct)) {
            $correct = preg_split('/[,،|]\s*/u', $correct);
        }

        if (!is_array($answer) || !is_array($correct) || empty($correct)) {
            return 0;
        }

        $total = count($correct);
        $matched = 0;

        foreach ($correct as $idx => $expected) {
            $student = $answer[$idx] ?? null;
            if (self::scalarEquals($student, $expected)) {
                $matched++;
            }
        }

        return (int) round(($matched / $total) * 100);
    }

    /**
     * تصحيح اختبار quiz بأسئلة متعددة، يدعم نسب جزئية.
     */
    private static function gradeQuiz(array $questions, $answers): ?int
    {
        if (!is_array($answers)) {
            return 0;
        }

        $totalQuestions = 0;
        $earned = 0;
        $needsManualReview = false;

        foreach ($questions as $i => $question) {
            if (!is_array($question)) {
                continue;
            }
            $totalQuestions++;

            $type = $question['type'] ?? $question['question_type'] ?? null;
            $options = is_array($question['options'] ?? null) ? $question['options'] : [];
            $student = $answers[$i] ?? null;

            // أنواع تتطلب مراجعة المعلم → الكويز كله يذهب للمراجعة اليدوية
            if (in_array($type, ['essay', 'upload', 'creative', 'project', 'practical', 'discussion'], true)) {
                $needsManualReview = true;
                continue;
            }

            // أسئلة الترتيب: الترتيب الصحيح من النص المرجعي أو من ترتيب الـ options
            $isOrdering = in_array($type, [
                'word_ordering', 'word_order',
                'sentence_ordering', 'sentence_order',
                'image_ordering',
            ], true);

            if ($isOrdering) {
                $correctSeq = $question['correct_answer'] ?? $question['answer'] ?? null;
                if ($correctSeq === null && !empty($options)) {
                    $correctSeq = array_map(
                        fn($o) => is_array($o) ? ($o['text'] ?? $o['label'] ?? '') : (string) $o,
                        $options
                    );
                }
                if ($correctSeq === null) {
                    $needsManualReview = true;
                    continue;
                }
                if (self::orderingMatches($student, $correctSeq)) {
                    $earned++;
                }
                continue;
            }

            // ترتيب الصور داخل الكويز: بنيته تتطلب معالجة خاصة → مراجعة يدوية (فشل آمن)
            if ($type === 'image_order') {
                $needsManualReview = true;
                continue;
            }

            // باقي الأنواع: لا بد من مفتاح إجابة صالح، وإلا → مراجعة يدوية
            $key = self::resolveKey($question);
            if (!$key['has']) {
                $needsManualReview = true;
                continue;
            }

            $isCorrect = match ($type) {
                'short_answer' => is_string($student) && $key['text'] !== null
                    && self::textEquals($student, (string) $key['text']),
                'letter_choice' => self::textEquals(
                    is_array($student) ? implode('', $student) : (string) $student,
                    (string) ($key['text'] ?? '')
                ),
                default => !empty($options)
                    ? self::optionMatches($student, $key['text'], $options, $key['index'])
                    : self::scalarEquals($student, $key['text'] ?? $key['index']),
            };

            if ($isCorrect) {
                $earned++;
            }
        }

        if ($totalQuestions === 0) {
            return null;
        }

        // إن نقص مفتاح أي سؤال آلي أو وُجد سؤال يدوي → مراجعة يدوية كاملة
        // (يمنع تضخيم النسبة بالقسمة على الأسئلة المُصحَّحة فقط)
        if ($needsManualReview) {
            return null;
        }

        return (int) round(($earned / $totalQuestions) * 100);
    }

    /**
     * تصحيح نشاط ترتيب صور: لكل صورة، نقارن selected_order الذي اختاره الطالب
     * بـ order الأصلي للصورة (المُحدد من قبل المعلم/الأدمن).
     * النسبة المئوية = (عدد الصور المرتّبة صحيحاً / إجمالي الصور) × 100.
     */
    private static function gradeImageOrder(Activity $activity, $answer): ?int
    {
        // بناء خريطة الترتيب الصحيح: url → order
        $correctOrders = [];
        $questions = is_array($activity->questions) ? $activity->questions : [];

        // صيغة المعلم: كل عنصر صورة في الـ array الأعلى
        foreach ($questions as $q) {
            if (isset($q['image_url']) && isset($q['order'])) {
                $correctOrders[(string) $q['image_url']] = (int) $q['order'];
            }
            // صيغة الأدمن: نشاط واحد بمصفوفة images داخله
            if (isset($q['type']) && $q['type'] === 'image_order' && !empty($q['images'])) {
                foreach ($q['images'] as $i => $img) {
                    $url = $img['url'] ?? $img['image_url'] ?? '';
                    if ($url !== '') {
                        $correctOrders[(string) $url] = (int) ($img['order'] ?? $i + 1);
                    }
                }
            }
        }

        // لا بنية ترتيب صالحة → مراجعة يدوية (لا منح صفر زائف)
        if (empty($correctOrders) || !is_array($answer)) {
            return null;
        }

        $expectedCount = count($correctOrders);
        $total = 0;
        $matched = 0;
        $selectedValues = [];
        foreach ($answer as $item) {
            if (!is_array($item)) continue;
            $url = (string) ($item['image_url'] ?? '');
            $selected = (int) ($item['selected_order'] ?? 0);
            if (!isset($correctOrders[$url])) continue;

            $total++;
            $selectedValues[] = $selected;
            if ($selected === $correctOrders[$url]) {
                $matched++;
            }
        }

        if ($total === 0) {
            return null;
        }

        // التحقق من صحّة التبديلة: يجب أن تغطّي كل الصور وتكون قيمها {1..n} فريدة بلا تكرار.
        // يمنع تضخيم الدرجة عند تكرار قيمة (مثل ضبط الكل على 1) أو ترتيب جزئي.
        sort($selectedValues);
        if ($total !== $expectedCount || $selectedValues !== range(1, $expectedCount)) {
            return 0;
        }

        return (int) round(($matched / $expectedCount) * 100);
    }

    private static function orderingMatches($student, $correct): bool
    {
        if (is_string($correct)) {
            $correct = preg_split('/[,،|]\s*/u', $correct);
        }
        if (!is_array($student) || !is_array($correct)) {
            return false;
        }
        if (count($student) !== count($correct)) {
            return false;
        }

        foreach ($correct as $idx => $expected) {
            if (!self::scalarEquals($student[$idx] ?? null, $expected)) {
                return false;
            }
        }
        return true;
    }

    private static function scalarEquals($a, $b): bool
    {
        if ($a === null || $b === null) {
            return false;
        }
        if (is_numeric($a) && is_numeric($b)) {
            return (string) $a === (string) $b;
        }
        return self::textEquals((string) $a, (string) $b);
    }

    /**
     * مقارنة نص بعد تطبيع: trim، تحويل إلى lowercase، إزالة التشكيل العربي.
     */
    private static function textEquals(string $a, string $b): bool
    {
        return self::normalizeText($a) === self::normalizeText($b);
    }

    private static function normalizeText(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        // إزالة التشكيل العربي
        $value = preg_replace('/[\x{064B}-\x{0652}\x{0670}\x{0640}]/u', '', $value);
        // توحيد الألف والياء والتاء المربوطة
        $value = strtr($value, [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ى' => 'ي',
            'ة' => 'ه',
        ]);
        // ضغط المسافات المتعددة
        $value = preg_replace('/\s+/u', ' ', $value);
        return $value;
    }

    /**
     * تحويل قيمة إلى bool بحذر — يُرجع null للقيم الغامضة (مثل: 2, 3, "")
     * بدلاً من إجبارها على false (الذي كان يسبب التطابق الكاذب
     * بين دليل خيار رقمي وكلمة "خطأ").
     */
    private static function toBool($value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            $v = strtolower(trim($value));
            if (in_array($v, ['1', 'true', 'yes', 'صح', 'صحيح', 'نعم'], true)) {
                return true;
            }
            if (in_array($v, ['0', 'false', 'no', 'خطأ', 'خاطئ', 'لا'], true)) {
                return false;
            }
            return null;
        }
        if (is_int($value)) {
            // فقط 0 و 1 صريحَين يُترجمان كمنطقي؛ غيرهما غامض → null
            if ($value === 0) return false;
            if ($value === 1) return true;
            return null;
        }
        return null;
    }
}
