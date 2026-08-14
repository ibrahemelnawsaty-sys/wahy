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
            // دفعة 4: كتل تفاعليّة + تضمينات. accordion=details أصليّ (بلا JS)، tabs=خطّ أصول
            // خفيف مُدقَّق (runtime)، video=iframe مبنيّ خادميّاً من مضيف مسموح.
            'accordion' => ['v' => 1, 'view' => 'pb.blocks.accordion'],
            'tabs' => ['v' => 1, 'view' => 'pb.blocks.tabs', 'runtime' => true],
            'video' => ['v' => 1, 'view' => 'pb.blocks.video'],
            // جولة كتل إضافيّة (تغذية راجعة)
            'icon' => ['v' => 1, 'view' => 'pb.blocks.icon'],
            'gallery' => ['v' => 1, 'view' => 'pb.blocks.gallery'],
            'cover' => ['v' => 1, 'view' => 'pb.blocks.cover'],
            'map' => ['v' => 1, 'view' => 'pb.blocks.map'],
            // حزمة كتل تفاعليّة (تغذية راجعة)
            'countdown' => ['v' => 1, 'view' => 'pb.blocks.countdown', 'runtime' => true],
            'progress' => ['v' => 1, 'view' => 'pb.blocks.progress'],
            'alert' => ['v' => 1, 'view' => 'pb.blocks.alert'],
            'rating' => ['v' => 1, 'view' => 'pb.blocks.rating'],
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
            'accordion' => ['label' => 'أكورديون / أسئلة', 'icon' => '🪗', 'category' => 'تفاعل', 'fields' => [
                ['key' => 'items', 'label' => 'العناصر', 'type' => 'repeater', 'fields' => [
                    ['key' => 'title', 'label' => 'العنوان', 'type' => 'text'],
                    ['key' => 'content', 'label' => 'المحتوى', 'type' => 'textarea'],
                ]],
            ]],
            'tabs' => ['label' => 'تبويبات', 'icon' => '🗂️', 'category' => 'تفاعل', 'fields' => [
                ['key' => 'items', 'label' => 'التبويبات', 'type' => 'repeater', 'fields' => [
                    ['key' => 'title', 'label' => 'عنوان التبويب', 'type' => 'text'],
                    ['key' => 'content', 'label' => 'المحتوى', 'type' => 'textarea'],
                ]],
            ]],
            'video' => ['label' => 'فيديو (يوتيوب/ﭬيميو)', 'icon' => '▶️', 'category' => 'تضمين', 'fields' => [
                ['key' => 'url', 'label' => 'رابط الفيديو', 'type' => 'url'],
                ['key' => 'caption', 'label' => 'تعليق', 'type' => 'text'],
            ]],
            'icon' => ['label' => 'أيقونة', 'icon' => '⭐', 'category' => 'محتوى', 'fields' => [
                ['key' => 'icon', 'label' => 'الأيقونة (إيموجي)', 'type' => 'text'],
                ['key' => 'size', 'label' => 'الحجم', 'type' => 'select', 'options' => ['sm' => 'صغير', 'md' => 'متوسّط', 'lg' => 'كبير']],
                ['key' => 'align', 'label' => 'المحاذاة', 'type' => 'select', 'options' => ['start' => 'بداية', 'center' => 'وسط', 'end' => 'نهاية']],
            ]],
            'gallery' => ['label' => 'معرض صور', 'icon' => '🖼️', 'category' => 'وسائط', 'fields' => [
                ['key' => 'items', 'label' => 'الصور', 'type' => 'repeater', 'fields' => [
                    ['key' => 'src', 'label' => 'الصورة', 'type' => 'media'],
                    ['key' => 'alt', 'label' => 'نصّ بديل', 'type' => 'text'],
                ]],
            ]],
            'cover' => ['label' => 'غلاف بخلفيّة', 'icon' => '🌄', 'category' => 'تسويق', 'fields' => [
                ['key' => 'bg', 'label' => 'صورة الخلفيّة', 'type' => 'media'],
                ['key' => 'overlay', 'label' => 'التعتيم', 'type' => 'select', 'options' => ['none' => 'بلا', 'dark' => 'داكن', 'light' => 'فاتح']],
                ['key' => 'title', 'label' => 'العنوان', 'type' => 'text'],
                ['key' => 'subtitle', 'label' => 'العنوان الفرعيّ', 'type' => 'textarea'],
                ['key' => 'button_text', 'label' => 'نصّ الزرّ', 'type' => 'text'],
                ['key' => 'button_link', 'label' => 'رابط الزرّ', 'type' => 'url'],
            ]],
            'map' => ['label' => 'خريطة', 'icon' => '🗺️', 'category' => 'تضمين', 'fields' => [
                ['key' => 'address', 'label' => 'العنوان/المكان', 'type' => 'text'],
                ['key' => 'height', 'label' => 'الارتفاع (px)', 'type' => 'number', 'min' => 150, 'max' => 800],
            ]],
            'countdown' => ['label' => 'عدّاد تنازليّ', 'icon' => '⏳', 'category' => 'تفاعل', 'fields' => [
                ['key' => 'date', 'label' => 'التاريخ المستهدف (YYYY-MM-DD HH:MM)', 'type' => 'text'],
                ['key' => 'label', 'label' => 'نصّ أعلى العدّاد', 'type' => 'text'],
            ]],
            'progress' => ['label' => 'شريط تقدّم', 'icon' => '📊', 'category' => 'محتوى', 'fields' => [
                ['key' => 'label', 'label' => 'العنوان', 'type' => 'text'],
                ['key' => 'value', 'label' => 'النسبة (0-100)', 'type' => 'number', 'min' => 0, 'max' => 100],
            ]],
            'alert' => ['label' => 'تنبيه', 'icon' => '🔔', 'category' => 'محتوى', 'fields' => [
                ['key' => 'type', 'label' => 'النوع', 'type' => 'select', 'options' => ['info' => 'معلومة', 'success' => 'نجاح', 'warning' => 'تحذير', 'error' => 'خطأ']],
                ['key' => 'text', 'label' => 'النصّ', 'type' => 'textarea'],
            ]],
            'rating' => ['label' => 'تقييم نجوم', 'icon' => '⭐', 'category' => 'تسويق', 'fields' => [
                ['key' => 'value', 'label' => 'عدد النجوم (0-5)', 'type' => 'number', 'min' => 0, 'max' => 5],
                ['key' => 'label', 'label' => 'نصّ بجانبه', 'type' => 'text'],
            ]],
        ];
    }

    /**
     * محتوى مبدئيّ (placeholder) لكلّ كتلة عند إضافتها — كي تظهر فوراً في المعاينة قبل التحرير
     * (سلوك الووردبريس). يستبدله المستخدم. الأنواع البصريّة (صورة/فيديو) تبقى فارغة حتى الاختيار.
     */
    public static function defaults(): array
    {
        return [
            'hero' => ['title' => 'عنوان رئيسيّ جذّاب', 'subtitle' => 'اكتب هنا وصفاً مختصراً يشرح فكرتك.', 'button_text' => 'ابدأ الآن', 'button_link' => '#'],
            'heading' => ['text' => 'عنوان جديد', 'level' => 'h2', 'align' => 'start'],
            'richtext' => ['html' => '<p>اكتب نصّك هنا…</p>'],
            'list' => ['items' => [['text' => 'عنصر أوّل'], ['text' => 'عنصر ثانٍ'], ['text' => 'عنصر ثالث']]],
            'quote' => ['text' => 'اقتباسٌ ملهم يعبّر عن رسالتك.', 'cite' => 'المصدر'],
            'button' => ['text' => 'اضغط هنا', 'link' => '#', 'style' => 'primary', 'align' => 'start'],
            'buttons' => ['align' => 'start', 'items' => [['text' => 'زرّ أساسيّ', 'link' => '#', 'style' => 'primary'], ['text' => 'زرّ ثانويّ', 'link' => '#', 'style' => 'ghost']]],
            'iconlist' => ['items' => [['icon' => '✅', 'text' => 'ميزة أولى'], ['icon' => '✅', 'text' => 'ميزة ثانية'], ['icon' => '✅', 'text' => 'ميزة ثالثة']]],
            'features' => ['heading' => 'مزايانا', 'items' => [['icon' => '⭐', 'title' => 'ميزة', 'text' => 'وصف مختصر.'], ['icon' => '⚡', 'title' => 'ميزة', 'text' => 'وصف مختصر.'], ['icon' => '🔒', 'title' => 'ميزة', 'text' => 'وصف مختصر.']]],
            'cta' => ['title' => 'جاهز للبدء؟', 'text' => 'انضمّ إلينا اليوم وابدأ رحلتك.', 'button_text' => 'ابدأ الآن', 'button_link' => '#'],
            'testimonial' => ['quote' => 'خدمة رائعة أنصح بها بشدّة.', 'name' => 'اسم العميل', 'role' => 'الصفة'],
            'pricing' => ['items' => [['name' => 'الباقة', 'price' => '99', 'period' => '/شهر', 'features' => "ميزة أولى\nميزة ثانية", 'button_text' => 'اشترك', 'button_link' => '#', 'featured' => false]]],
            'social' => ['items' => [['network' => 'facebook', 'url' => 'https://facebook.com'], ['network' => 'twitter', 'url' => 'https://x.com'], ['network' => 'instagram', 'url' => 'https://instagram.com']]],
            'table' => ['headers' => 'العمود الأوّل|العمود الثاني', 'rows' => [['cells' => 'خليّة|خليّة'], ['cells' => 'خليّة|خليّة']]],
            'accordion' => ['items' => [['title' => 'سؤال شائع؟', 'content' => 'اكتب الإجابة هنا.'], ['title' => 'سؤال آخر؟', 'content' => 'اكتب الإجابة هنا.']]],
            'tabs' => ['items' => [['title' => 'التبويب الأوّل', 'content' => 'محتوى التبويب الأوّل.'], ['title' => 'التبويب الثاني', 'content' => 'محتوى التبويب الثاني.']]],
            'separator' => ['style' => 'line'],
            'spacer' => ['height' => 40],
            'columns' => ['count' => 2],
            'icon' => ['icon' => '⭐', 'size' => 'lg', 'align' => 'center'],
            'cover' => ['title' => 'عنوان الغلاف', 'subtitle' => 'نصّ يظهر فوق الخلفيّة.', 'overlay' => 'dark', 'button_text' => 'ابدأ الآن', 'button_link' => '#'],
            'map' => ['address' => 'مكة المكرمة', 'height' => 320],
            'countdown' => ['date' => '2026-12-31 23:59', 'label' => 'العرض ينتهي خلال'],
            'progress' => ['label' => 'نسبة الإنجاز', 'value' => 75],
            'alert' => ['type' => 'info', 'text' => 'اكتب رسالة التنبيه هنا.'],
            'rating' => ['value' => 5, 'label' => 'تقييم ممتاز'],
        ];
    }

    public static function has(string $type): bool
    {
        return isset(self::all()[$type]);
    }

    /** هل تحتاج شجرة الكتل خطّ أصول pb-runtime.js؟ (يُحقَن شرطيّاً فقط عند الحاجة). */
    public static function needsRuntime(mixed $blocks): bool
    {
        if (! is_array($blocks)) {
            return false;
        }
        $all = self::all();
        foreach ($blocks as $b) {
            if (! is_array($b)) {
                continue;
            }
            $t = $b['type'] ?? null;
            if (is_string($t) && ! empty($all[$t]['runtime'])) {
                return true;
            }
            if (isset($b['children']) && self::needsRuntime($b['children'])) {
                return true;
            }
        }

        return false;
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
