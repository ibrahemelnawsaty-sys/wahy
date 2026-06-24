<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Lesson $lesson): bool
    {
        // الدروس عادةً عامة لكل المسجّلين
        return true;
    }

    /**
     * إنشاء — مدير مدرسة، معلم، أو سوبر أدمن.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['teacher', 'school_admin', 'super_admin'], true);
    }

    /**
     * تعديل — السوبر أدمن (المحتوى التعليمي مركزي).
     * يُستثنى المعلم — المحتوى عام عبر المنصة.
     */
    public function update(User $user, Lesson $lesson): bool
    {
        return $user->role === 'super_admin';
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->role === 'super_admin';
    }
}
