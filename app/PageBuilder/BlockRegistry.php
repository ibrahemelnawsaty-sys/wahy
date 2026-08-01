<?php

namespace App\PageBuilder;

/**
 * سجلّ الكتل الخادميّ — قائمة السماح الوحيدة لأنواع الكتل + إصداراتها الحاليّة + مكوّن Blade لكلّ نوع
 * + دوالّ الترقية (ت-٢). أيّ نوع خارج هذا السجلّ يُتجاهَل عند الرندرة (لا يُصبّ خاماً) — أساس منع XSS.
 *
 * لإضافة كتلة: أضِف مدخلاً هنا + مكوّن Blade في resources/views/pb/blocks/. لا لمس المُصيِّر.
 */
class BlockRegistry
{
    /**
     * type => [ 'v' => الإصدار الحاليّ, 'view' => مكوّن Blade, 'upgraders' => [n => fn(block):block] ]
     */
    public static function all(): array
    {
        return [
            'hero' => ['v' => 1, 'view' => 'pb.blocks.hero'],
            'richtext' => ['v' => 1, 'view' => 'pb.blocks.richtext'],
            'image' => ['v' => 1, 'view' => 'pb.blocks.image'],
            'button' => ['v' => 1, 'view' => 'pb.blocks.button'],
            'features' => ['v' => 1, 'view' => 'pb.blocks.features'],
            'columns' => ['v' => 1, 'view' => 'pb.blocks.columns'],
            'cta' => ['v' => 1, 'view' => 'pb.blocks.cta'],
            'spacer' => ['v' => 1, 'view' => 'pb.blocks.spacer'],
        ];
    }

    public static function has(string $type): bool
    {
        return isset(self::all()[$type]);
    }

    public static function view(string $type): ?string
    {
        return self::all()[$type]['view'] ?? null;
    }

    public static function currentVersion(string $type): int
    {
        return (int) (self::all()[$type]['v'] ?? 1);
    }

    /** @return array<int, callable> دوالّ الترقية من الإصدار n-1 إلى n. */
    public static function upgraders(string $type): array
    {
        return self::all()[$type]['upgraders'] ?? [];
    }
}
