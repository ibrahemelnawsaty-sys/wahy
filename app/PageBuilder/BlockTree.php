<?php

namespace App\PageBuilder;

/**
 * تحضير شجرة الكتل قبل الرندرة: إسقاط الأنواع المجهولة (قائمة سماح BlockRegistry)، تطبيع البنية،
 * وترقية الكتل الأقدم إصداراً عند القراءة (ت-٢) — غير مدمِّر (لا يُكتب فوق المخزَّن؛ يُثبَّت عند أوّل حفظ).
 */
class BlockTree
{
    /** يُحضِّر شجرة كتل للرندرة الآمنة. */
    public static function prepare(?array $blocks): array
    {
        if (! is_array($blocks)) {
            return [];
        }

        $out = [];
        foreach ($blocks as $block) {
            if (! is_array($block) || empty($block['type']) || ! is_string($block['type'])) {
                continue;
            }
            if (! BlockRegistry::has($block['type'])) {
                continue; // نوع مجهول → يُتجاهَل (لا يُصبّ خاماً)
            }

            $block = self::upgrade($block);
            $block['props'] = is_array($block['props'] ?? null) ? $block['props'] : [];
            $block['children'] = self::prepare($block['children'] ?? []);
            $out[] = $block;
        }

        return $out;
    }

    /** ترقية كتلة عبر دوالّ الترقية المتتالية حتى الإصدار الحاليّ (ت-٢). */
    private static function upgrade(array $block): array
    {
        $type = $block['type'];
        $v = (int) ($block['v'] ?? 1);
        $current = BlockRegistry::currentVersion($type);
        $upgraders = BlockRegistry::upgraders($type);

        while ($v < $current) {
            $next = $v + 1;
            if (isset($upgraders[$next]) && is_callable($upgraders[$next])) {
                $block = ($upgraders[$next])($block);
            }
            $v = $next;
            $block['v'] = $v;
        }

        return $block;
    }
}
