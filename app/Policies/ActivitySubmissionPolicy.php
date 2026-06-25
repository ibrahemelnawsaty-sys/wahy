<?php

namespace App\Policies;

use App\Models\ActivitySubmission;
use App\Models\User;

class ActivitySubmissionPolicy
{
    /**
     * عرض القائمة — مسجّل دخول.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * عرض تسليم واحد — صاحبه، أو معلم/مدير في نفس المدرسة، أو ولي أمر للطالب، أو سوبر أدمن.
     */
    public function view(User $user, ActivitySubmission $submission): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        // الطالب صاحب التسليم
        if ($user->id === $submission->student_id) {
            return true;
        }

        $student = $submission->student;
        if (! $student) {
            return false;
        }

        // معلم/مدير من نفس مدرسة الطالب
        if (in_array($user->role, ['teacher', 'school_admin'], true)
            && $user->school_id !== null
            && $user->school_id === $student->school_id) {
            return true;
        }

        // ولي أمر للطالب
        if ($user->role === 'parent') {
            return $user->children()->where('users.id', $student->id)->exists();
        }

        return false;
    }

    /**
     * إنشاء — الطالب فقط (يقدّم بنفسه).
     */
    public function create(User $user): bool
    {
        return $user->role === 'student';
    }

    /**
     * تصحيح/تعديل — معلم نفس المدرسة فقط، أو مدير مدرسة، أو سوبر أدمن.
     */
    public function review(User $user, ActivitySubmission $submission): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        $student = $submission->student;
        if (! $student) {
            return false;
        }

        if (in_array($user->role, ['teacher', 'school_admin'], true)) {
            return $user->school_id !== null && $user->school_id === $student->school_id;
        }

        return false;
    }

    /**
     * حذف — مدير مدرسة أو سوبر أدمن فقط.
     */
    public function delete(User $user, ActivitySubmission $submission): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        $student = $submission->student;
        if (! $student) {
            return false;
        }

        return $user->role === 'school_admin'
            && $user->school_id !== null
            && $user->school_id === $student->school_id;
    }
}
