<?php

namespace Database\Seeders;

use App\Models\PageBuilder;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. صفحة "من نحن"
        PageBuilder::create([
            'page_name' => 'من نحن',
            'slug' => 'about-us',
            'json_data' => [
                [
                    'type' => 'hero',
                    'content' => [
                        'title' => 'من نحن',
                        'subtitle' => 'تعرف على منصة قيمّ ورؤيتنا',
                        'buttonText' => 'انضم إلينا',
                        'buttonLink' => '/register',
                    ],
                ],
                [
                    'type' => 'spacer',
                    'content' => ['height' => '60px'],
                ],
                [
                    'type' => 'heading',
                    'content' => [
                        'level' => 'h2',
                        'text' => 'رؤيتنا',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        'text' => 'نؤمن بأن التعليم هو أساس بناء المجتمعات، ونسعى لتوفير تعليم قيمي تفاعلي يبني شخصية الطالب ويعزز القيم الإنسانية.',
                    ],
                ],
                [
                    'type' => 'spacer',
                    'content' => ['height' => '40px'],
                ],
                [
                    'type' => 'heading',
                    'content' => [
                        'level' => 'h2',
                        'text' => 'ما نقدمه',
                    ],
                ],
                [
                    'type' => 'cards',
                    'content' => [
                        'items' => [
                            [
                                'icon' => '🎓',
                                'title' => 'دروس تفاعلية',
                                'text' => 'محتوى تعليمي مصمم بعناية لبناء القيم',
                            ],
                            [
                                'icon' => '👥',
                                'title' => 'بيئة آمنة',
                                'text' => 'منصة آمنة تربط المدرسة والطالب والأسرة',
                            ],
                            [
                                'icon' => '🏆',
                                'title' => 'تحفيز مستمر',
                                'text' => 'نظام نقاط وشارات يحفز الطلاب على التميز',
                            ],
                            [
                                'icon' => '📊',
                                'title' => 'تقارير شاملة',
                                'text' => 'متابعة دقيقة لتقدم الطالب ومستواه',
                            ],
                        ],
                    ],
                ],
            ],
            'meta_title' => 'من نحن - منصة قيمّ',
            'meta_description' => 'تعرف على منصة قيمّ التعليمية ورؤيتنا لبناء القيم الإنسانية',
            'is_active' => true,
        ]);

        // 2. صفحة المميزات
        PageBuilder::create([
            'page_name' => 'المميزات',
            'slug' => 'features',
            'json_data' => [
                [
                    'type' => 'hero',
                    'content' => [
                        'title' => 'مميزات منصة قيمّ',
                        'subtitle' => 'اكتشف ما يجعلنا مختلفين',
                        'buttonText' => 'جرب الآن',
                        'buttonLink' => '/register',
                    ],
                ],
                [
                    'type' => 'spacer',
                    'content' => ['height' => '60px'],
                ],
                [
                    'type' => 'cards',
                    'content' => [
                        'items' => [
                            [
                                'icon' => '🎮',
                                'title' => 'تعلم تفاعلي',
                                'text' => 'ألعاب وأنشطة تفاعلية تجعل التعلم ممتعاً ومشوقاً',
                            ],
                            [
                                'icon' => '📱',
                                'title' => 'متاح في كل مكان',
                                'text' => 'تعلم من أي مكان وفي أي وقت عبر جميع الأجهزة',
                            ],
                            [
                                'icon' => '👨‍👩‍👧‍👦',
                                'title' => 'تواصل عائلي',
                                'text' => 'نربط ولي الأمر بتقدم أبنائه بشكل مستمر',
                            ],
                            [
                                'icon' => '🎯',
                                'title' => 'تقييم ذكي',
                                'text' => 'نظام تقييم متقدم يقيس التقدم الحقيقي للطالب',
                            ],
                            [
                                'icon' => '🔒',
                                'title' => 'بيئة آمنة',
                                'text' => 'حماية كاملة لبيانات الطلاب وخصوصيتهم',
                            ],
                            [
                                'icon' => '💡',
                                'title' => 'محتوى متطور',
                                'text' => 'محتوى تعليمي يُحدّث باستمرار ليواكب العصر',
                            ],
                        ],
                    ],
                ],
            ],
            'meta_title' => 'مميزات منصة قيمّ التعليمية',
            'meta_description' => 'اكتشف مميزات منصة قيمّ التعليمية التفاعلية',
            'is_active' => true,
        ]);

        // 3. صفحة الأسعار
        PageBuilder::create([
            'page_name' => 'الأسعار',
            'slug' => 'pricing',
            'json_data' => [
                [
                    'type' => 'hero',
                    'content' => [
                        'title' => 'الأسعار والباقات',
                        'subtitle' => 'اختر الباقة المناسبة لمدرستك',
                        'buttonText' => 'تواصل معنا',
                        'buttonLink' => '/contact',
                    ],
                ],
                [
                    'type' => 'spacer',
                    'content' => ['height' => '60px'],
                ],
                [
                    'type' => 'cards',
                    'content' => [
                        'items' => [
                            [
                                'icon' => '💫',
                                'title' => 'الباقة الأساسية',
                                'text' => '299 ريال شهرياً - حتى 100 طالب - مثالية للمدارس الصغيرة',
                            ],
                            [
                                'icon' => '⭐',
                                'title' => 'الباقة المتقدمة',
                                'text' => '599 ريال شهرياً - حتى 300 طالب - للمدارس المتوسطة',
                            ],
                            [
                                'icon' => '👑',
                                'title' => 'الباقة الشاملة',
                                'text' => '999 ريال شهرياً - طلاب غير محدودين - للمدارس الكبيرة',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'spacer',
                    'content' => ['height' => '40px'],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        'text' => 'جميع الباقات تشمل: دعم فني على مدار الساعة، تحديثات مجانية، تدريب للمعلمين، تقارير تفصيلية',
                    ],
                ],
            ],
            'meta_title' => 'أسعار منصة قيمّ - باقات تناسب الجميع',
            'meta_description' => 'تعرف على أسعار وباقات منصة قيمّ التعليمية',
            'is_active' => true,
        ]);

        // 4. صفحة اتصل بنا
        PageBuilder::create([
            'page_name' => 'اتصل بنا',
            'slug' => 'contact',
            'json_data' => [
                [
                    'type' => 'hero',
                    'content' => [
                        'title' => 'تواصل معنا',
                        'subtitle' => 'نحن هنا لمساعدتك',
                        'buttonText' => 'أرسل رسالة',
                        'buttonLink' => '#contact-form',
                    ],
                ],
                [
                    'type' => 'spacer',
                    'content' => ['height' => '60px'],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        'text' => 'يمكنك التواصل معنا عبر البريد الإلكتروني أو الهاتف، ونحن سنرد عليك في أقرب وقت ممكن.',
                    ],
                ],
                [
                    'type' => 'spacer',
                    'content' => ['height' => '40px'],
                ],
                [
                    'type' => 'cards',
                    'content' => [
                        'items' => [
                            [
                                'icon' => '📧',
                                'title' => 'البريد الإلكتروني',
                                'text' => 'info@qiyamm.sa',
                            ],
                            [
                                'icon' => '📱',
                                'title' => 'الهاتف',
                                'text' => '0112345678',
                            ],
                            [
                                'icon' => '📍',
                                'title' => 'العنوان',
                                'text' => 'الرياض، المملكة العربية السعودية',
                            ],
                        ],
                    ],
                ],
            ],
            'meta_title' => 'اتصل بنا - منصة قيمّ',
            'meta_description' => 'تواصل مع فريق منصة قيمّ',
            'is_active' => true,
        ]);
    }
}
