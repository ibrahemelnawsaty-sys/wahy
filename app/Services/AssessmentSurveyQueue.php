<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\User;
use Closure;
use Illuminate\Support\Collection;

/**
 * طابور التقييمات الحاجبة (قبلي/بعدي) في سياق درس — المصدر الوحيد لما تعرضه النافذة.
 *
 * لماذا لا نستعمل Survey::getPendingSurveysForUser()؟ لأنّها تستبعد عمداً مُشغِّلات التقييم
 * (Survey.php:335-339 — «Issue 19») وهي بالضبط ما يسكّه إنشاء التقييم
 * (Admin\SurveyController.php:147-148). وإلغاء ذلك الاستبعاد يحوّل كلّ تقييم — وهي تُنشأ بـ
 * school_id = NULL و is_mandatory/is_popup = true — إلى نافذة حاجبة على **كلّ صفحة لكلّ طالب**
 * في المنصّة حتى لو لم يفتح الدرس قطّ. فنبني الطابور من الدالّتين السياقيّتين بدل ذلك.
 */
final class AssessmentSurveyQueue
{
    /**
     * الطابور مرتَّباً ترتيباً حتميّاً: قيمة-قبليّ ← درس-قبليّ ← درس-بعديّ ← قيمة-بعديّ.
     *
     * الترتيب يُبنى في PHP صراحةً لا من ترتيب قاعدة البيانات: الطالب يجب أن يُقيَّم قبل التعلّم
     * ثمّ بعده، ومستوى القيمة يحيط بمستوى الدرس.
     *
     * @param  bool  $lessonActivitiesDone  هل أنهى الطالب أنشطة هذا الدرس (بوّابة البعديّ الدرسيّ)
     * @param  Closure():bool  $valueMastered  فحصٌ **كسول** لإتقان القيمة — لا يُستدعى إلّا عند وجود
     *                                         مرشّح بعديّ فعليّ، حفاظاً على أداء أكثر صفحة زيارةً
     * @return Collection<int, Survey>
     */
    public static function build(
        User $user,
        int $lessonId,
        ?int $valueId,
        bool $lessonActivitiesDone,
        Closure $valueMastered,
    ): Collection {
        /** @var array<int, Survey|null> $ordered */
        $ordered = [];

        $ordered[] = $valueId ? Survey::pendingValueSurveyFor($user, $valueId, 'pre') : null;
        $ordered[] = Survey::pendingLessonSurveyFor($user, $lessonId, 'pre');
        $ordered[] = $lessonActivitiesDone ? Survey::pendingLessonSurveyFor($user, $lessonId, 'post') : null;

        if ($valueId) {
            $valuePost = Survey::pendingValueSurveyFor($user, $valueId, 'post');
            // الاستعلام الرخيص أوّلاً ثمّ الفحص الثقيل — لا نمسح الإتقان بلا مرشّح.
            $ordered[] = ($valuePost && $valueMastered()) ? $valuePost : null;
        }

        return collect($ordered)
            ->filter()
            // الدالّتان السياقيّتان لا تفحصان هذين العمودين (بخلاف getPendingSurveysForUser)،
            // فبدون هذا المرشّح نحجب الطالب باستبيان أزال عنه الأدمن «إجباري» و«نافذة» عمداً.
            ->filter(fn (Survey $s) => $s->is_mandatory || $s->is_popup)
            ->unique('id')
            ->values()
            // المكوّن يتنقّل $survey->questions على نموذج مُستعاد من الجلسة — بلا تحميل مسبق يظهر فارغاً.
            ->each(fn (Survey $s) => $s->loadMissing('questions'));
    }
}
