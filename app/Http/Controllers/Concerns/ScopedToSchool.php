<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\UserRole;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Trait للـ controllers التي تتعامل مع مدرسة المستخدم الحالي (school_admin/teacher).
 *
 * يلغي تكرار النمط:
 *   $school = Auth::user()->school;
 *   if (!$school) { abort(403); }
 *
 * في 20+ controller method.
 */
trait ScopedToSchool
{
    /**
     * الحصول على مدرسة المستخدم الحالي — يطلق abort(403) إن لم تكن مرتبطة.
     * super_admin يحصل على أول مدرسة نشطة (للاختبار) — أو يجب أن يستخدم scope مخصص.
     */
    protected function currentSchool(): School
    {
        $user = Auth::user();

        if (!$user) {
            abort(401);
        }

        // super_admin غير محصور بمدرسة
        if ($user->role === UserRole::SuperAdmin->value) {
            $school = School::where('status', 'active')->first();
            if (!$school) {
                abort(404, 'لا توجد مدارس نشطة');
            }
            return $school;
        }

        if (!$user->school_id) {
            abort(403, 'حسابك غير مرتبط بمدرسة. اتصل بالإدارة.');
        }

        $school = $user->school;
        if (!$school) {
            abort(404, 'المدرسة المرتبطة بحسابك غير موجودة');
        }

        return $school;
    }

    /**
     * Query builder للطلاب في مدرسة المستخدم — استخدم بدلاً من التكرار:
     *   User::where('role','student')->where('school_id', $school->id)
     */
    protected function studentsInMySchool(): Builder
    {
        return User::query()
            ->where('role', UserRole::Student->value)
            ->where('school_id', $this->currentSchool()->id);
    }

    /**
     * Query builder للمعلمين في مدرسة المستخدم.
     */
    protected function teachersInMySchool(): Builder
    {
        return User::query()
            ->where('role', UserRole::Teacher->value)
            ->where('school_id', $this->currentSchool()->id);
    }

    /**
     * Query builder لأولياء الأمور في مدرسة المستخدم.
     */
    protected function parentsInMySchool(): Builder
    {
        return User::query()
            ->where('role', UserRole::Parent->value)
            ->where('school_id', $this->currentSchool()->id);
    }
}
