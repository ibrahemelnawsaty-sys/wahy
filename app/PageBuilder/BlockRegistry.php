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
            // دفعة 2: مكتبة كتل غنيّة (كتل S — بلا JS، عبر مكوّنات Blade موثوقة)
            'heading' => ['v' => 1, 'view' => 'pb.blocks.heading'],
            'list' => ['v' => 1, 'view' => 'pb.blocks.list'],
            'quote' => ['v' => 1, 'view' => 'pb.blocks.quote'],
            'separator' => ['v' => 1, 'view' => 'pb.blocks.separator'],
            'buttons' => ['v' => 1, 'view' => 'pb.blocks.buttons'],
            'iconlist' => ['v' => 1, 'view' => 'pb.blocks.iconlist'],
            'testimonial' => ['v' => 1, 'view' => 'pb.blocks.testimonial'],
            'pricing' => ['v' => 1, 'view' => 'pb.blocks.pricing'],
            'social' => ['v' => 1, 'view' => 'pb.blocks.social'],
            'table' => ['v' => 1, 'view' => 'pb.blocks.table'],
        ];
    }

    /**
     * مخطّط المحرّر لكلّ نوع: تسمية + أيقونة + حقول لوحة الخصائص (تُولَّد منها الواجهة تلقائيّاً).
     * مصدرٌ واحد يشاركه المحرّر (بناء اللوحات) — مقيَّد بقائمة السماح نفسها (all()).
     */
    public static function schema(): array
    {
        return [
            'hero' => ['label' => 'واجهة بطوليّة', 'icon' => '🎯', 'category' => 'تسويق', 'fields' => [
                ['key' => 'title', 'label' => 'العنوان', 'type' => 'text'],
                ['key' => 'subtitle', 'label' => 'العنوان الفرعيّ', 'type' => 'textarea'],
                ['key' => 'button_text', 'label' => 'نصّ الزرّ', 'type' => 'text'],
                ['key' => 'button_link', 'label' => 'رابط الزرّ', 'type' => 'url'],
            ]],
            'heading' => ['label' => 'عنوان', 'icon' => '🔤', 'category' => 'نصّ', 'fields' => [
                ['key' => 'text', 'label' => 'النصّ', 'type' => 'text'],
                ['key' => 'level', 'label' => 'المستوى', 'type' => 'select',
                    'options' => ['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6']],
                ['key' => 'align', 'label' => 'المحاذاة', 'type' => 'select',
                    'options' => ['start' => 'بداية', 'center' => 'وسط', 'end' => 'نهاية']],
            ]],
            'richtext' => ['label' => 'نصّ غنيّ', 'icon' => '📝', 'category' => 'نصّ', 'fields' => [
                ['key' => 'html', 'label' => 'المحتوى', 'type' => 'richtext'],
            ]],
            'list' => ['label' => 'قائمة', 'icon' => '📋', 'category' => 'نصّ', 'fields' => [
                ['key' => 'ordered', 'label' => 'قائمة مرقّمة', 'type' => 'toggle'],
                ['key' => 'items', 'label' => 'العناصر', 'type' => 'repeater', 'fields' => [
                    ['key' => 'text', 'label' => 'النصّ', 'type' => 'text'],
                ]],
            ]],
            'quote' => ['label' => 'اقتباس', 'icon' => '❝', 'category' => 'نصّ', 'fields' => [
                ['key' => 'text', 'label' => 'الاقتباس', 'type' => 'textarea'],
                ['key' => 'cite', 'label' => 'المصدر/العزو', 'type' => 'text'],
            ]],
            'iconlist' => ['label' => 'قائمة أيقونات', 'icon' => '✅', 'category' => 'نصّ', 'fields' => [
                ['key' => 'items', 'label' => 'العناصر', 'type' => 'repeater', 'fields' => [
                    ['key' => 'icon', 'label' => 'أيقونة (إيموجي)', 'type' => 'text'],
                    ['key' => 'text', 'label' => 'النصّ', 'type' => 'text'],
                ]],
            ]],
            'table' => ['label' => 'جدول', 'icon' => '▤', 'category' => 'نصّ', 'fields' => [
                ['key' => 'headers', 'label' => 'العناوين (افصل بـ |)', 'type' => 'text'],
                ['key' => 'rows', 'label' => 'الصفوف', 'type' => 'repeater', 'fields' => [
                    ['key' => 'cells', 'label' => 'خلايا الصفّ (افصل بـ |)', 'type' => 'text'],
                ]],
            ]],
            'image' => ['label' => 'صورة', 'icon' => '🖼️', 'category' => 'وسائط', 'fields' => [
                ['key' => 'src', 'label' => 'الصورة', 'type' => 'media'],
                ['key' => 'alt', 'label' => 'نصّ بديل', 'type' => 'text'],
                ['key' => 'caption', 'label' => 'تعليق', 'type' => 'text'],
            ]],
            'button' => ['label' => 'زرّ', 'icon' => '🔘', 'category' => 'أزرار', 'fields' => [
                ['key' => 'text', 'label' => 'النصّ', 'type' => 'text'],
                ['key' => 'link', 'label' => 'الرابط', 'type' => 'url'],
                ['key' => 'style', 'label' => 'النمط', 'type' => 'select',
                    'options' => ['primary' => 'أساسيّ', 'secondary' => 'ثانويّ', 'ghost' => 'شفّاف']],
                ['key' => 'align', 'label' => 'المحاذاة', 'type' => 'select',
                    'options' => ['start' => 'بداية', 'center' => 'وسط', 'end' => 'نهاية']],
            ]],
            'buttons' => ['label' => 'مجموعة أزرار', 'icon' => '🎚️', 'category' => 'أزرار', 'fields' => [
                ['key' => 'align', 'label' => 'المحاذاة', 'type' => 'select',
                    'options' => ['start' => 'بداية', 'center' => 'وسط', 'end' => 'نهاية']],
                ['key' => 'items', 'label' => 'الأزرار', 'type' => 'repeater', 'fields' => [
                    ['key' => 'text', 'label' => 'النصّ', 'type' => 'text'],
                    ['key' => 'link', 'label' => 'الرابط', 'type' => 'url'],
                    ['key' => 'style', 'label' => 'النمط', 'type' => 'select',
                        'options' => ['primary' => 'أساسيّ', 'secondary' => 'ثانويّ', 'ghost' => 'شفّاف']],
                ]],
            ]],
            'cta' => ['label' => 'دعوة لإجراء', 'icon' => '📣', 'category' => 'تسويق', 'fields' => [
                ['key' => 'title', 'label' => 'العنوان', 'type' => 'text'],
                ['key' => 'text', 'label' => 'الوصف', 'type' => 'textarea'],
                ['key' => 'button_text', 'label' => 'نصّ الزرّ', 'type' => 'text'],
                ['key' => 'button_link', 'label' => 'رابط الزرّ', 'type' => 'url'],
            ]],
            'features' => ['label' => 'مزايا', 'icon' => '⭐', 'category' => 'تسويق', 'fields' => [
                ['key' => 'heading', 'label' => 'العنوان', 'type' => 'text'],
                ['key' => 'items', 'label' => 'العناصر', 'type' => 'repeater', 'fields' => [
                    ['key' => 'icon', 'label' => 'أيقونة', 'type' => 'text'],
                    ['key' => 'title', 'label' => 'العنوان', 'type' => 'text'],
                    ['key' => 'text', 'label' => 'الوصف', 'type' => 'textarea'],
                ]],
            ]],
            'testimonial' => ['label' => 'شهادة عميل', 'icon' => '💬', 'category' => 'تسويق', 'fields' => [
                ['key' => 'quote', 'label' => 'الشهادة', 'type' => 'textarea'],
                ['key' => 'name', 'label' => 'الاسم', 'type' => 'text'],
                ['key' => 'role', 'label' => 'الصفة', 'type' => 'text'],
                ['key' => 'avatar', 'label' => 'الصورة', 'type' => 'media'],
            ]],
            'pricing' => ['label' => 'باقات أسعار', 'icon' => '💳', 'category' => 'تسويق', 'fields' => [
                ['key' => 'items', 'label' => 'الباقات', 'type' => 'repeater', 'fields' => [
                    ['key' => 'name', 'label' => 'اسم الباقة', 'type' => 'text'],
                    ['key' => 'price', 'label' => 'السعر', 'type' => 'text'],
                    ['key' => 'period', 'label' => 'المدّة', 'type' => 'text'],
                    ['key' => 'features', 'label' => 'المزايا (سطر لكلّ ميزة)', 'type' => 'textarea'],
                    ['key' => 'button_text', 'label' => 'نصّ الزرّ', 'type' => 'text'],
                    ['key' => 'button_link', 'label' => 'رابط الزرّ', 'type' => 'url'],
                    ['key' => 'featured', 'label' => 'مميّزة', 'type' => 'toggle'],
                ]],
            ]],
            'social' => ['label' => 'روابط اجتماعيّة', 'icon' => '🔗', 'category' => 'اجتماعيّ', 'fields' => [
                ['key' => 'items', 'label' => 'الروابط', 'type' => 'repeater', 'fields' => [
                    ['key' => 'network', 'label' => 'الشبكة', 'type' => 'select',
                        'options' => ['facebook' => 'فيسبوك', 'twitter' => 'إكس/تويتر', 'instagram' => 'إنستغرام',
                            'linkedin' => 'لينكدإن', 'youtube' => 'يوتيوب', 'whatsapp' => 'واتساب', 'telegram' => 'تيليغرام']],
                    ['key' => 'url', 'label' => 'الرابط', 'type' => 'url'],
                ]],
            ]],
            'columns' => ['label' => 'أعمدة', 'icon' => '▦', 'category' => 'حاويات', 'children' => true, 'fields' => [
                ['key' => 'count', 'label' => 'عدد الأعمدة', 'type' => 'number', 'min' => 1, 'max' => 6],
            ]],
            'separator' => ['label' => 'فاصل خطّ', 'icon' => '➖', 'category' => 'تخطيط', 'fields' => [
                ['key' => 'style', 'label' => 'النمط', 'type' => 'select',
                    'options' => ['line' => 'خطّ', 'dots' => 'نقاط', 'space' => 'مسافة فقط']],
            ]],
            'spacer' => ['label' => 'فراغ', 'icon' => '↕️', 'category' => 'تخطيط', 'fields' => [
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
