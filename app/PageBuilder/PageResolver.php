<?php

namespace App\PageBuilder;

use App\Models\Setting;
use App\PageBuilder\Models\Page;

/**
 * جسر الترحيل التدريجيّ (ت-١٢): علم ميزة لكلّ صفحة يقرّر متى يخدم محرّر v2 مساراً عامّاً
 * بدل النظام القديم (landing / PageBuilder). آمن بالتصميم: إن كان العلم مرفوعاً بلا صفحة
 * منشورة مطابقة → يُرجِع null فيرتدّ المتحكّم للقديم (لا شاشة بيضاء).
 */
class PageResolver
{
    /** مفتاح الإعداد: قائمة الـslugs المخدومة عبر v2 (JSON). */
    public const FLAG = 'pb_v2_enabled_slugs';

    /** @return array<int, string> */
    public static function enabledSlugs(): array
    {
        $raw = Setting::get(self::FLAG, '[]');
        $list = is_array($raw) ? $raw : json_decode((string) $raw, true);

        return is_array($list) ? array_values(array_filter(array_map('strval', $list))) : [];
    }

    public static function isEnabled(string $slug): bool
    {
        return in_array($slug, self::enabledSlugs(), true);
    }

    public static function enable(string $slug): void
    {
        $list = self::enabledSlugs();
        if (! in_array($slug, $list, true)) {
            $list[] = $slug;
            self::persist($list);
        }
    }

    public static function disable(string $slug): void
    {
        self::persist(array_values(array_diff(self::enabledSlugs(), [$slug])));
    }

    /**
     * الصفحة المنشورة v2 لهذا المسار إن كان مُفعَّلاً — وإلّا null (ارتداد للقديم).
     */
    public static function resolve(string $slug, string $locale = 'ar'): ?Page
    {
        if (! self::isEnabled($slug)) {
            return null;
        }

        return Page::query()
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->where('status', 'published')
            // جدولة: صفحة منشورة بتاريخ نشرٍ مستقبليّ لا تُخدَم حتى يحين وقتها (null = فوريّ).
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->first();
    }

    private static function persist(array $list): void
    {
        Setting::set(self::FLAG, array_values(array_unique($list)), 'json');
    }
}
