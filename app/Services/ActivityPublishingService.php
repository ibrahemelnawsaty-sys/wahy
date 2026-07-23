<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\DB;

/**
 * منطق النشر الموحّد للأنشطة (المرحلة 3).
 *
 * مصدر وحيد لكتابة حالة الاعتماد + النشر (activity_school / all_schools_mode)، تستدعيه
 * المسارات الثلاثة (مدير المدرسة، ActivityApprovalController، ActivityBankController القديم)
 * كي لا يتسرّب اعتمادٌ يتجاوز النطاق أو الوضع (§8). جدول activity_school لا يُكتب إلا من هنا.
 *
 * فصلُ «هل النشاط معتمَد؟» عن «أين/لمن يُنشر؟»:
 *  - المرحلة 1 (مدير المدرسة): اعتماد + نشر لمدرسته فقط (صفّ activity_school).
 *  - المرحلة 2 (الأدمن): اعتماد + نشر لكل المدارس (all_schools_mode) أو لمدارس محدّدة + نقل للبنك.
 */
class ActivityPublishingService
{
    /**
     * المرحلة 1 — اعتماد مدير المدرسة: يُصبح النشاط متاحًا لمدرسته فورًا بالوضع المختار.
     */
    public function schoolApprove(Activity $activity, int $schoolId, string $publishMode, int $approverId): void
    {
        $publishMode = $this->normalizeMode($publishMode);

        // ذرّيّة: الاعتماد + صفّ النشر يُثبَّتان معًا أو يُلغَيان معًا (لا حالة «معتمَد بلا نشر»).
        DB::transaction(function () use ($activity, $schoolId, $publishMode, $approverId) {
            $activity->update([
                'school_approval_status' => 'approved',
                'school_approved_by' => $approverId,
                'school_approved_at' => now(),
                'school_rejection_reason' => null,
            ]);

            $this->publishToSchool($activity, $schoolId, $publishMode, $approverId);
        });
    }

    /**
     * المرحلة 2 — اعتماد الأدمن: نشر لكل المدارس (افتراضيًّا) أو لمدارس محدّدة + نقل تلقائيّ للبنك.
     *
     * @param  string  $scope  'all' | 'specific'
     * @param  int[]  $schoolIds  المدارس المستهدفة عند scope='specific' فقط
     */
    public function adminApprove(Activity $activity, string $scope, string $publishMode, array $schoolIds, int $approverId): void
    {
        $publishMode = $this->normalizeMode($publishMode);
        $scope = $scope === 'specific' ? 'specific' : 'all';

        // ذرّيّة: الاعتماد + نقل البنك + كل صفوف نشر المدارس تُثبَّت معًا أو تُلغى معًا — فلا يبقى
        // نشاطٌ «معتمَد ومنقول للبنك» بنشرٍ جزئيّ لبعض المدارس إن أخفق صفٌّ في منتصف الحلقة.
        DB::transaction(function () use ($activity, $scope, $publishMode, $schoolIds, $approverId) {
            $activity->update([
                'approval_status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
                'rejection_reason' => null,
                // نقل تلقائيّ للبنك المشترك عند اعتماد الأدمن (§متطلب 3)
                'is_activity_bank' => true,
                // كل المدارس ⇒ العمود الصريح؛ مدارس محدّدة ⇒ لا نشر عالميّ (none) والنشر عبر الـpivot
                'all_schools_mode' => $scope === 'all' ? $publishMode : 'none',
            ]);

            if ($scope === 'specific') {
                // sync (لا syncWithoutDetaching) كي تعكس صفوفُ activity_school النطاقَ المطلوب
                // **حصراً**: تُفصَل المدارس المُزالة من الاختيار (تضييق النطاق) وصفُّ المرحلة 1 إن
                // لم يعُد ضمن المستهدَفة — فلا تبقى مدرسةٌ مرئيّةً خلافاً للقرار الأخير (عزل §12).
                $activity->schools()->sync($this->schoolPivotMap($schoolIds, $publishMode, $approverId));
            } else {
                // scope='all': النشر عبر all_schools_mode وحده — نُفرّغ الصفوف الموجَّهة كي لا يبقى
                // صفُّ direct عالقٌ من المرحلة 1 يُبقي رؤيةً مباشرةً تخالف وضع الأدمن (مثلاً 'bank').
                $activity->schools()->detach();
            }
        });
    }

    /**
     * إلغاء كل النشر: يُستدعى عند الرفض (أو أيّ تصفير) كي لا يبقى نشاطٌ مرفوض/مسحوب مرئيًّا للطلاب.
     * يُزيل all_schools_mode + صفوف المدارس + الإسناد المرجعيّ للفصول. forceFill+saveQuietly
     * لتجاوز الحارس (يعمل من أيّ مسار رفض مخوَّل بصرف النظر عن العمود الخام للدور).
     */
    public function revokePublishing(Activity $activity): void
    {
        $activity->schools()->detach();
        $activity->classrooms()->detach();
        $activity->forceFill(['all_schools_mode' => 'none'])->saveQuietly();
    }

    /**
     * سحبُ نشرٍ من مدرسة واحدة فقط (رفض مدير المدرسة للمرحلة 1) — لا يمسّ نشرَ الأدمن العالميّ
     * (all_schools_mode) ولا صفوفَ/فصولَ المدارس الأخرى (§12.1 عزل: فاعلٌ مدرسيّ لا يعكس قرارًا
     * عالميّاً اتخذه الأدمن، ولا يؤثّر على مستأجرين آخرين). عكسُ revokePublishing الشامل المخصَّص
     * لمسار رفض الأدمن حيث للفاعل صلاحيّة عالميّة.
     */
    public function unpublishFromSchool(Activity $activity, int $schoolId): void
    {
        $activity->schools()->detach([$schoolId]);
    }

    /**
     * إنشاء/تحديث صفّ نشر لمدرسة (idempotent عبر unique(activity_id, school_id)).
     * يُستعمَل في مسار المرحلة 1 (schoolApprove) حيث الإضافة تراكميّة مقصودة.
     */
    private function publishToSchool(Activity $activity, int $schoolId, string $publishMode, int $approverId): void
    {
        $activity->schools()->syncWithoutDetaching([
            $schoolId => [
                'publish_mode' => $publishMode,
                'published_by' => $approverId,
                'published_at' => now(),
            ],
        ]);
    }

    /**
     * خريطة pivot لمجموعة مدارس (لـsync في مسار الأدمن — نطاق حصريّ).
     *
     * @param  int[]  $schoolIds
     * @return array<int, array<string, mixed>>
     */
    private function schoolPivotMap(array $schoolIds, string $publishMode, int $approverId): array
    {
        $now = now();
        $map = [];
        foreach ($schoolIds as $sid) {
            $map[(int) $sid] = [
                'publish_mode' => $publishMode,
                'published_by' => $approverId,
                'published_at' => $now,
            ];
        }

        return $map;
    }

    private function normalizeMode(string $mode): string
    {
        return in_array($mode, ['bank', 'direct'], true) ? $mode : 'bank';
    }
}
