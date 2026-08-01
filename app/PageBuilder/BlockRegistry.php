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

    /**
     * مخطّط المحرّر لكلّ نوع: تسمية + أيقونة + حقول لوحة الخصائص (تُولَّد منها الواجهة تلقائيّاً).
     * مصدرٌ واحد يشاركه المحرّر (بناء اللوحات) — مقيَّد بقائمة السماح نفسها (all()).
     */
    public static function schema(): array
    {
        return [
            'hero' => ['label' => 'واجهة بطوليّة', 'icon' => '🎯', 'fields' => [
                ['key' => 'title', 'label' => 'العنوان', 'type' => 'text'],
                ['key' => 'subtitle', 'label' => 'العنوان الفرعيّ', 'type' => 'textarea'],
                ['key' => 'button_text', 'label' => 'نصّ الزرّ', 'type' => 'text'],
                ['key' => 'button_link', 'label' => 'رابط الزرّ', 'type' => 'url'],
            ]],
            'richtext' => ['label' => 'نصّ غنيّ', 'icon' => '📝', 'fields' => [
                ['key' => 'html', 'label' => 'المحتوى', 'type' => 'richtext'],
            ]],
            'image' => ['label' => 'صورة', 'icon' => '🖼️', 'fields' => [
                ['key' => 'src', 'label' => 'الصورة', 'type' => 'media'],
                ['key' => 'alt', 'label' => 'نصّ بديل', 'type' => 'text'],
                ['key' => 'caption', 'label' => 'تعليق', 'type' => 'text'],
            ]],
            'button' => ['label' => 'زرّ', 'icon' => '🔘', 'fields' => [
                ['key' => 'text', 'label' => 'النصّ', 'type' => 'text'],
                ['key' => 'link', 'label' => 'الرابط', 'type' => 'url'],
                ['key' => 'style', 'label' => 'النمط', 'type' => 'select',
                    'options' => ['primary' => 'أساسيّ', 'secondary' => 'ثانويّ', 'ghost' => 'شفّاف']],
                ['key' => 'align', 'label' => 'المحاذاة', 'type' => 'select',
                    'options' => ['start' => 'بداية', 'center' => 'وسط', 'end' => 'نهاية']],
            ]],
            'features' => ['label' => 'مزايا', 'icon' => '⭐', 'fields' => [
                ['key' => 'heading', 'label' => 'العنوان', 'type' => 'text'],
                ['key' => 'items', 'label' => 'العناصر', 'type' => 'repeater', 'fields' => [
                    ['key' => 'icon', 'label' => 'أيقونة', 'type' => 'text'],
                    ['key' => 'title', 'label' => 'العنوان', 'type' => 'text'],
                    ['key' => 'text', 'label' => 'الوصف', 'type' => 'textarea'],
                ]],
            ]],
            'columns' => ['label' => 'أعمدة', 'icon' => '▦', 'children' => true, 'fields' => [
                ['key' => 'count', 'label' => 'عدد الأعمدة', 'type' => 'number', 'min' => 1, 'max' => 6],
            ]],
            'cta' => ['label' => 'دعوة لإجراء', 'icon' => '📣', 'fields' => [
                ['key' => 'title', 'label' => 'العنوان', 'type' => 'text'],
                ['key' => 'text', 'label' => 'الوصف', 'type' => 'textarea'],
                ['key' => 'button_text', 'label' => 'نصّ الزرّ', 'type' => 'text'],
                ['key' => 'button_link', 'label' => 'رابط الزرّ', 'type' => 'url'],
            ]],
            'spacer' => ['label' => 'فاصل', 'icon' => '↕️', 'fields' => [
                ['key' => 'height', 'label' => 'الارتفاع (بكسل)', 'type' => 'number', 'min' => 0, 'max' => 400],
            ]],
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
