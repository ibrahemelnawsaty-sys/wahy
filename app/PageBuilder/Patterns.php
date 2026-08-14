<?php

namespace App\PageBuilder;

/**
 * أنماط/أقسام جاهزة (كالووردبريس) — أشجار كتل مُصمَّمة مسبقاً بمحتوى نموذجيّ يستبدله المستخدم.
 * كلّها مبنيّة من أنواع BlockRegistry المُدرَجة فقط، فتمرّ عبر المُصيِّر الموثوق نفسه (لا HTML خام).
 * تُدرَج في المحرّر بنقرة ثمّ يُعدَّل المحتوى.
 */
class Patterns
{
    private static function b(string $type, array $props = [], array $children = []): array
    {
        $blk = ['type' => $type, 'v' => 1, 'props' => $props];
        if ($children) {
            $blk['children'] = $children;
        }

        return $blk;
    }

    /** @return array<int, array{key:string,label:string,icon:string,category:string,blocks:array}> */
    public static function all(): array
    {
        $b = fn (...$a) => self::b(...$a);

        return [
            [
                'key' => 'hero', 'label' => 'واجهة بطوليّة', 'icon' => '🎯', 'category' => 'واجهات',
                'blocks' => [
                    $b('hero', ['title' => 'عنوان رئيسيّ جذّاب لصفحتك', 'subtitle' => 'اكتب هنا وصفاً مختصراً يشرح قيمة ما تقدّمه بجملة أو جملتين.', 'button_text' => 'ابدأ الآن', 'button_link' => '/register']),
                ],
            ],
            [
                'key' => 'features', 'label' => 'ثلاث مزايا', 'icon' => '⭐', 'category' => 'محتوى',
                'blocks' => [
                    $b('heading', ['text' => 'لماذا تختارنا؟', 'level' => 'h2', 'align' => 'center']),
                    $b('features', ['heading' => '', 'items' => [
                        ['icon' => '⚡', 'title' => 'سريع وسهل', 'text' => 'واجهة بسيطة تنجز المهمّة بأقلّ خطوات.'],
                        ['icon' => '🔒', 'title' => 'آمن وموثوق', 'text' => 'حماية عالية لبياناتك على مدار الساعة.'],
                        ['icon' => '💬', 'title' => 'دعم متواصل', 'text' => 'فريق جاهز لمساعدتك في أيّ وقت.'],
                    ]]),
                ],
            ],
            [
                'key' => 'pricing', 'label' => 'باقات أسعار', 'icon' => '💳', 'category' => 'تسويق',
                'blocks' => [
                    $b('heading', ['text' => 'خطط الأسعار', 'level' => 'h2', 'align' => 'center']),
                    $b('pricing', ['items' => [
                        ['name' => 'الأساسيّة', 'price' => '0', 'period' => '/شهر', 'features' => "ميزة أولى\nميزة ثانية\nدعم بالبريد", 'button_text' => 'ابدأ مجاناً', 'button_link' => '/register', 'featured' => false],
                        ['name' => 'الاحترافيّة', 'price' => '99', 'period' => '/شهر', 'features' => "كل ما سبق\nمزايا متقدّمة\nدعم فوريّ", 'button_text' => 'اشترك', 'button_link' => '/register', 'featured' => true],
                        ['name' => 'المؤسّسات', 'price' => 'حسب الطلب', 'period' => '', 'features' => "حلول مخصّصة\nمدير حساب\nاتّفاقيّة خدمة", 'button_text' => 'تواصل معنا', 'button_link' => '/#support', 'featured' => false],
                    ]]),
                ],
            ],
            [
                'key' => 'faq', 'label' => 'أسئلة شائعة', 'icon' => '🪗', 'category' => 'محتوى',
                'blocks' => [
                    $b('heading', ['text' => 'الأسئلة الشائعة', 'level' => 'h2', 'align' => 'center']),
                    $b('accordion', ['items' => [
                        ['title' => 'كيف أبدأ الاستخدام؟', 'content' => 'اكتب هنا إجابة واضحة ومختصرة عن كيفيّة البدء.'],
                        ['title' => 'هل هناك فترة تجريبيّة؟', 'content' => 'اكتب هنا تفاصيل الفترة التجريبيّة إن وُجدت.'],
                        ['title' => 'كيف أتواصل مع الدعم؟', 'content' => 'اكتب هنا طرق التواصل مع فريق الدعم.'],
                    ]]),
                ],
            ],
            [
                'key' => 'testimonials', 'label' => 'آراء العملاء', 'icon' => '💬', 'category' => 'تسويق',
                'blocks' => [
                    $b('heading', ['text' => 'ماذا يقول عملاؤنا', 'level' => 'h2', 'align' => 'center']),
                    $b('columns', ['count' => 3], [
                        $b('testimonial', ['quote' => 'خدمة رائعة غيّرت طريقة عملنا للأفضل.', 'name' => 'اسم العميل', 'role' => 'مدير']),
                        $b('testimonial', ['quote' => 'سهولة وسرعة ودعم ممتاز. أنصح به بشدّة.', 'name' => 'اسم العميل', 'role' => 'صاحب عمل']),
                        $b('testimonial', ['quote' => 'أفضل قرار اتّخذناه هذا العام.', 'name' => 'اسم العميل', 'role' => 'رائدة أعمال']),
                    ]),
                ],
            ],
            [
                'key' => 'about', 'label' => 'من نحن (نصّ + صورة)', 'icon' => '🖼️', 'category' => 'محتوى',
                'blocks' => [
                    $b('columns', ['count' => 2], [
                        $b('richtext', ['html' => '<h2>من نحن</h2><p>اكتب هنا نبذة عن مؤسّستك ورسالتك وقيمك. يمكنك تنسيق النصّ بحريّة.</p>']),
                        $b('image', ['src' => '', 'alt' => 'صورة تعريفيّة', 'caption' => '']),
                    ]),
                ],
            ],
            [
                'key' => 'stats', 'label' => 'شريط إحصاءات', 'icon' => '📊', 'category' => 'تسويق',
                'blocks' => [
                    $b('columns', ['count' => 4], [
                        $b('heading', ['text' => '+500', 'level' => 'h2', 'align' => 'center']),
                        $b('heading', ['text' => '+50k', 'level' => 'h2', 'align' => 'center']),
                        $b('heading', ['text' => '99%', 'level' => 'h2', 'align' => 'center']),
                        $b('heading', ['text' => '24/7', 'level' => 'h2', 'align' => 'center']),
                    ]),
                ],
            ],
            [
                'key' => 'cta', 'label' => 'دعوة للانضمام', 'icon' => '📣', 'category' => 'واجهات',
                'blocks' => [
                    $b('cta', ['title' => 'جاهز للبدء؟', 'text' => 'انضمّ إلينا اليوم وابدأ رحلتك.', 'button_text' => 'أنشئ حساباً', 'button_link' => '/register']),
                ],
            ],
            [
                'key' => 'contact', 'label' => 'تواصل معنا', 'icon' => '✉️', 'category' => 'محتوى',
                'blocks' => [
                    $b('heading', ['text' => 'تواصل معنا', 'level' => 'h2', 'align' => 'center']),
                    $b('columns', ['count' => 2], [
                        $b('iconlist', ['items' => [
                            ['icon' => '📧', 'text' => 'البريد: info@example.com'],
                            ['icon' => '📞', 'text' => 'الهاتف: 000 000 0000'],
                            ['icon' => '📍', 'text' => 'العنوان: اكتب عنوانك هنا'],
                        ]]),
                        $b('richtext', ['html' => '<p>اكتب هنا رسالة ترحيبيّة أو أوقات العمل أو أيّ تفاصيل تواصل إضافيّة.</p>']),
                    ]),
                ],
            ],
        ];
    }
}
