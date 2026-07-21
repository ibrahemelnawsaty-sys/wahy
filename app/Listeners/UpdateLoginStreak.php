<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\StreakService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class UpdateLoginStreak
{
    /**
     * سجّل «يوم حضور» للطالب عند تسجيل الدخول (يشمل الدخول التلقائي عبر «تذكّرني»).
     * هذا ما يجعل «أيام متتالية» تعكس دخول الطالب اليوميّ فعلاً.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // السلسلة ميزة طالب فقط — لا نُنشئ صفوفاً للأدوار الأخرى
        if (! $user instanceof User || ! $user->isStudent()) {
            return;
        }

        try {
            StreakService::touch($user);
        } catch (\Throwable $e) {
            // أثر جانبي غير حرج — يجب ألّا يمنع تسجيل الدخول أبداً
            Log::warning('Login streak touch failed for user ' . $user->id . ': ' . $e->getMessage());
        }
    }
}
