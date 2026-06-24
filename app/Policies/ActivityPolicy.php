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
     * عرض نشاط واحد — يجب أن يكون من نفس المدرسة (أو super_admin).
     */
    public function view(User $user, Activity $activity): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        // النشاط مرتبط بدرس ضمن مدرسة المستخدم
        if ($activity->classroom_id) {
            return $user->school_id !== null
                && $activity->classroom?->school_id === $user->school_id;
        }

        return true; // أنشطة عامة (لم تُربط بفصل)
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
