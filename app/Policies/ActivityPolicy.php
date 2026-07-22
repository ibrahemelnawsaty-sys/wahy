<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    /**
     * عرض القائمة — أي مستخدم مسجّل دخول.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * عرض تفاصيل نشاط واحد (صفحة التفاصيل الموحّدة — المرحلة 4).
     *
     * العزل عبر «مدرسة المُنشئ» لا الفصل (النشاط لا يملك school_id، ومعظمه بلا classroom_id):
     *  - أدمن/سوبر أدمن: الكل.
     *  - المعلّم: نشاطه هو، أو نشاط بنك معتمَد (متاح للاختيار من البنك المشترك).
     *  - مدير المدرسة: نشاط أنشأه أحد معلّمي مدرسته (ضمن مدارسه المُدارة).
     */
    public function view(User $user, Activity $activity): bool
    {
        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return true;
        }

        if ($user->role === 'teacher') {
            if ($user->id === $activity->created_by) {
                return true;
            }

            if (! ($activity->is_activity_bank && $activity->approval_status === 'approved')) {
                return false;
            }

            // نشاط عامّ (بلا منشئ) = منهج مشترك بلا مالك ولا يمرّ باعتماد «مدارس محدّدة»
            // (طابور الأدمن يقصر على أنشطة المعلّمين) — يبقى متاحًا للجميع. أمّا نشاط معلّمٍ آخر
            // فيُشترط أن يكون متاحًا في بنك مدرسة هذا المعلّم (منشور لكل المدارس أو لمدرسته
            // صراحةً) — يمنع رؤية/اختيار نشاطٍ قصره الأدمن على مدارس أخرى (§12.1 عزل صارم).
            return $activity->created_by === null
                || ($user->school_id && $activity->isAvailableInBankToSchool((int) $user->school_id));
        }

        if ($user->role === 'school_admin') {
            $creatorSchoolId = $activity->creator?->school_id;
            if ($creatorSchoolId === null) {
                return false;
            }

            $managed = method_exists($user, 'managedSchoolIds')
                ? $user->managedSchoolIds()
                : array_filter([$user->school_id]);

            return in_array($creatorSchoolId, $managed, true);
        }

        return false;
    }

    /**
     * إنشاء — معلم/مدير مدرسة/سوبر أدمن فقط.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['teacher', 'school_admin', 'super_admin'], true);
    }

    /**
     * تعديل — المنشئ نفسه أو إدارة المدرسة.
     */
    public function update(User $user, Activity $activity): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        if ($user->id === $activity->created_by) {
            return true;
        }

        if ($user->role === 'school_admin' && $user->school_id !== null) {
            return $activity->classroom?->school_id === $user->school_id;
        }

        return false;
    }

    /**
     * حذف — المنشئ نفسه أو إدارة المدرسة.
     */
    public function delete(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }

    /**
     * اعتماد/رفض — مدير المدرسة أو السوبر أدمن فقط.
     */
    public function approve(User $user, Activity $activity): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        if ($user->role === 'school_admin' && $user->school_id !== null) {
            return $activity->classroom?->school_id === $user->school_id;
        }

        return false;
    }

    /**
     * تمييز — السوبر أدمن فقط.
     */
    public function feature(User $user): bool
    {
        return $user->role === 'super_admin';
    }
}
