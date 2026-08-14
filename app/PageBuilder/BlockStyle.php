<?php

namespace App\PageBuilder;

/**
 * تعقيم تصميم الكتلة (دفعة 3) — يبني قيمة style آمنة من props._style عبر **قائمة سماح صارمة**
 * (لا قيم CSS حرّة أبداً): ألوان hex فقط، أرقام px مقيَّدة، محاذاة من مجموعة ثابتة.
 * يمنع حقن CSS/الانفلات من التصريح (مثل "red;}body{...}") — نفس صرامة PageDesign::sanitize.
 */
class BlockStyle
{
    public static function inline(mixed $style): string
    {
        if (! is_array($style)) {
            return '';
        }

        $hex = fn ($v) => (is_string($v) && preg_match('/^#[0-9a-fA-F]{3,8}$/', trim($v))) ? trim($v) : null;
        $px = function ($v, int $max): ?int {
            if (! is_numeric($v)) {
                return null;
            }
            $n = (int) $v;

            return ($n >= 0 && $n <= $max) ? $n : null;
        };

        $decl = [];
        if ($c = $hex($style['bg'] ?? null)) {
            $decl[] = 'background:' . $c;
        }
        if ($c = $hex($style['color'] ?? null)) {
            $decl[] = 'color:' . $c;
        }
        if (in_array($style['align'] ?? null, ['start', 'center', 'end'], true)) {
            $decl[] = 'text-align:' . $style['align'];
        }
        if (($pt = $px($style['pt'] ?? null, 200)) !== null) {
            $decl[] = 'padding-top:' . $pt . 'px';
        }
        if (($pb = $px($style['pb'] ?? null, 200)) !== null) {
            $decl[] = 'padding-bottom:' . $pb . 'px';
        }
        if (($mw = $px($style['maxw'] ?? null, 1600)) !== null && $mw > 0) {
            $decl[] = 'max-width:' . $mw . 'px';
            $decl[] = 'margin-inline:auto';
        }

        return implode(';', $decl);
    }

    /** أصناف الإظهار حسب الجهاز (تجاوب) — قائمة سماح ثابتة، لا قيم حرّة. */
    public static function classes(mixed $style): string
    {
        if (! is_array($style)) {
            return '';
        }
        $c = [];
        if (! empty($style['hide_mobile'])) {
            $c[] = 'pb-hide-mobile';
        }
        if (! empty($style['hide_tablet'])) {
            $c[] = 'pb-hide-tablet';
        }
        if (! empty($style['hide_desktop'])) {
            $c[] = 'pb-hide-desktop';
        }

        return implode(' ', $c);
    }

    public static function has(mixed $style): bool
    {
        return self::inline($style) !== '' || self::classes($style) !== '';
    }
}
