<?php

namespace App\PageBuilder;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * حماية الـslug (ت-٤): يمنع صفحةً يُنشئها المحرّر من تظليل مسارات النظام (admin/login/api/…)
 * فتكسر التطبيق أو تُستخدَم في تصيّد داخليّ. قائمة منع ثابتة + فحص ديناميكيّ مقابل المسارات المسجَّلة.
 */
class SlugGuard
{
    /** المقاطع الأولى المحجوزة (جذور مسارات النظام). */
    public const RESERVED = [
        'admin', 'super-admin', 'school-admin', 'support', 'login', 'logout', 'register',
        'password', 'forgot-password', 'reset-password', 'api', 'storage', 'sanctum', 'livewire',
        'dashboard', 'student', 'teacher', 'parent', 'messages', 'leaderboard', 'tickets',
        'survey', 'surveys', 'home', 'pages', 'page', 'profile', 'settings', 'notifications',
        'activities', 'lessons', 'values', 'badges', 'concepts', 'shop', 'pvp', 'reports',
        'users', 'schools', 'media', 'editor', 'up', 'assets', 'css', 'js', 'img', 'fonts',
    ];

    /** هل الـslug محجوز (لا يصلح لصفحة عامّة)؟ */
    public static function isReserved(string $slug): bool
    {
        $slug = strtolower(trim($slug));
        $slug = trim($slug, '/');

        if ($slug === '') {
            return true;
        }

        $first = explode('/', $slug)[0];

        if (in_array($first, self::RESERVED, true)) {
            return true;
        }

        // فحص ديناميكيّ: تصادم المقطع الأوّل مع أيّ مسار GET مسجَّل ثابت (غير متغيّر).
        try {
            foreach (Route::getRoutes()->get('GET') as $route) {
                $uri = trim($route->uri(), '/');
                if ($uri === '' || $uri === '/') {
                    continue;
                }
                $seg = explode('/', $uri)[0];
                if ($seg !== '' && ! Str::startsWith($seg, '{') && strtolower($seg) === $first) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // في سياقٍ بلا مسارات محمَّلة نكتفي بالقائمة الثابتة.
        }

        return false;
    }

    /** تطبيع نصّ إلى slug صالح (أحرف/أرقام/شرطات). */
    public static function normalize(string $value): string
    {
        return Str::slug($value) ?: '';
    }
}
