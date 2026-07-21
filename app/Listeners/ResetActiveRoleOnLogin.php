<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class ResetActiveRoleOnLogin
{
    /**
     * كل جلسة دخول جديدة تبدأ بالدور الأساسيّ للمستخدم — لأيّ طريقة دخول (عادي/2FA/تذكّرني).
     *
     * تبديل الأدوار فعل ضمن الجلسة، لكنّ User::switchRole يُثبّت active_role في العمود أيضاً؛
     * فبقاؤه بعد تسجيل الخروج يجعل الدخول التالي (بجلسة فارغة) يسقط للعمود ويوجّه المستخدم
     * للوحة دور مُبدَّل قد تنكسر — وهذا سبب «دخول الأدمن يفتح لوحة المعلّم ويُظهر خطأ».
     * نُصفّره عند كل دخول لنعود دائماً للدور الأساسيّ؛ ويبدّل المستخدم يدوياً إن رغب.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        // امسح أيّ دور مُبدَّل من الجلسة
        session()->forget('active_role_' . $user->id);

        // صفّر العمود المُثبَّت إن كان يخالف الدور الأساسيّ (نفحص القيمة الخام لا الـaccessor
        // الذي يُرجع الدور الأساسيّ عند null). active_role ليس ضمن الحقول الحسّاسة، لكن
        // نستعمل saveQuietly لتفادي ضجيج الأحداث/السجلّ في مسار الدخول المتكرّر.
        $raw = $user->getRawOriginal('active_role');
        if (! empty($raw) && $raw !== $user->role) {
            $user->forceFill(['active_role' => null])->saveQuietly();
        }
    }
}
