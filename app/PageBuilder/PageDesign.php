<?php

namespace App\PageBuilder;

use App\Models\Setting;

/**
 * رموز التصميم (ت-١٠) — ألوان/خطّ/استدارة على مستوى الموقع، تُحقَن كمتغيّرات CSS في مستند v2 والمعاينة.
 * أمنيّ: كلّ قيمة تُعقَّم بصرامة قبل دخول <style> (ألوان hex فقط، خطّ من قائمة سماح، أرقام مقيَّدة)
 * — يمنع حقن CSS/الانفلات من التصريح (قيمة مثل "red;}body{...}").
 */
class PageDesign
{
    public const SETTING_KEY = 'pb_design_tokens';

    public const DEFAULTS = [
        'primary' => '#667eea',
        'secondary' => '#764ba2',
        'text' => '#1f2937',
        'bg' => '#ffffff',
        'font' => 'Tajawal',
        'radius' => 12,
    ];

    /** لوحات ألوان جاهزة مسمّاة (تُطبَّق بنقرة على رموز التصميم). */
    public const PALETTES = [
        'بنفسجيّ' => ['primary' => '#667eea', 'secondary' => '#764ba2', 'text' => '#1f2937', 'bg' => '#ffffff'],
        'أزرق ملكيّ' => ['primary' => '#2563eb', 'secondary' => '#1e40af', 'text' => '#0f172a', 'bg' => '#ffffff'],
        'أخضر طبيعيّ' => ['primary' => '#10b981', 'secondary' => '#059669', 'text' => '#064e3b', 'bg' => '#ffffff'],
        'ذهبيّ دافئ' => ['primary' => '#d97706', 'secondary' => '#b45309', 'text' => '#1c1917', 'bg' => '#fffbeb'],
        'ورديّ أنيق' => ['primary' => '#ec4899', 'secondary' => '#be185d', 'text' => '#500724', 'bg' => '#ffffff'],
        'فيروزيّ' => ['primary' => '#0891b2', 'secondary' => '#0e7490', 'text' => '#083344', 'bg' => '#f0fdff'],
        'داكن ليليّ' => ['primary' => '#818cf8', 'secondary' => '#a78bfa', 'text' => '#e5e7eb', 'bg' => '#0f172a'],
        'رماديّ راقٍ' => ['primary' => '#475569', 'secondary' => '#334155', 'text' => '#0f172a', 'bg' => '#f8fafc'],
    ];

    /** خطوط مسموحة: الاسم => مَعلَم Google Fonts (null = خطّ نظام، بلا تحميل خارجيّ). */
    public const FONTS = [
        'Tajawal' => 'Tajawal',
        'Cairo' => 'Cairo',
        'Almarai' => 'Almarai',
        'Noto Kufi Arabic' => 'Noto+Kufi+Arabic',
        'System' => null,
    ];

    /** الرموز الفعّالة (الافتراضيّ مدموجاً بالمخزَّن، بعد تعقيم). */
    public static function tokens(): array
    {
        $stored = Setting::get(self::SETTING_KEY, []);
        $stored = is_array($stored) ? $stored : [];

        return self::sanitize(array_merge(self::DEFAULTS, $stored));
    }

    /** تعقيم صارم — قيمة غير صالحة تُستبدَل بالافتراضيّ (لا تمرّ خاماً إلى CSS). */
    public static function sanitize(array $in): array
    {
        $hex = function ($v, $fallback) {
            $v = is_string($v) ? trim($v) : '';

            return preg_match('/^#[0-9a-fA-F]{3,8}$/', $v) ? $v : $fallback;
        };

        $font = (is_string($in['font'] ?? null) && array_key_exists($in['font'], self::FONTS))
            ? $in['font'] : self::DEFAULTS['font'];

        $radius = (int) ($in['radius'] ?? self::DEFAULTS['radius']);
        $radius = max(0, min(40, $radius));

        return [
            'primary' => $hex($in['primary'] ?? '', self::DEFAULTS['primary']),
            'secondary' => $hex($in['secondary'] ?? '', self::DEFAULTS['secondary']),
            'text' => $hex($in['text'] ?? '', self::DEFAULTS['text']),
            'bg' => $hex($in['bg'] ?? '', self::DEFAULTS['bg']),
            'font' => $font,
            'radius' => $radius,
        ];
    }

    public static function save(array $in): array
    {
        $clean = self::sanitize(array_merge(self::DEFAULTS, $in));
        Setting::set(self::SETTING_KEY, $clean, 'json');

        return $clean;
    }

    /** عائلة الخطّ كقيمة CSS (مع بدائل). */
    public static function fontFamily(?string $font = null): string
    {
        $font = $font ?: self::tokens()['font'];
        if ($font === 'System') {
            return "system-ui,-apple-system,'Segoe UI',sans-serif";
        }

        return "'" . $font . "','Tajawal','Segoe UI',system-ui,sans-serif";
    }

    /** رابط Google Fonts للخطّ المختار (أو null لخطوط النظام). */
    public static function googleFontUrl(?string $font = null): ?string
    {
        $font = $font ?: self::tokens()['font'];
        $param = self::FONTS[$font] ?? null;

        return $param ? "https://fonts.googleapis.com/css2?family={$param}:wght@400;700;800&display=swap" : null;
    }

    /** سلسلة متغيّرات CSS جاهزة للحقن (كلّ القيم مُعقَّمة). */
    public static function cssVars(): string
    {
        $t = self::tokens();

        return implode('', [
            '--pb-primary:' . $t['primary'] . ';',
            '--pb-secondary:' . $t['secondary'] . ';',
            '--pb-text:' . $t['text'] . ';',
            '--pb-bg:' . $t['bg'] . ';',
            '--pb-radius:' . $t['radius'] . 'px;',
            '--pb-font:' . self::fontFamily($t['font']) . ';',
        ]);
    }
}
