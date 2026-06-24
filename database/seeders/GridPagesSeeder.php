<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PageBuilder;
use Illuminate\Support\Facades\DB;

class GridPagesSeeder extends Seeder
{
    public function run(): void
    {
        // حذف الصفحات القديمة
        DB::table('page_builder')->truncate();

        // صفحة تجريبية شاملة
        PageBuilder::create([
            'page_name' => 'صفحة تجريبية',
            'slug' => 'demo-page',
            'json_data' => [
                'sections' => [
                    // قسم 1: عمود واحد - عنوان رئيسي
                    [
                        'columns' => 1,
                        'grid' => [
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h1',
                                        'text' => 'مرحباً بكم في نظام القيم 🌟',
                                        'align' => 'center'
                                    ]
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'منصة تعليمية متطورة لبناء القيم والأخلاق لدى الطلاب من خلال أنشطة تفاعلية وممتعة',
                                        'align' => 'center'
                                    ]
                                ],
                                [
                                    'type' => 'button',
                                    'content' => [
                                        'text' => 'ابدأ الآن',
                                        'link' => '#',
                                        'style' => 'primary'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    
                    // قسم 2: ثلاثة أعمدة - مميزات
                    [
                        'columns' => 3,
                        'grid' => [
                            [
                                [
                                    'type' => 'card',
                                    'content' => [
                                        'icon' => '🎯',
                                        'title' => 'تعليم تفاعلي',
                                        'text' => 'أنشطة وألعاب تعليمية تجعل التعلم ممتعاً ومشوقاً'
                                    ]
                                ]
                            ],
                            [
                                [
                                    'type' => 'card',
                                    'content' => [
                                        'icon' => '📊',
                                        'title' => 'تتبع التقدم',
                                        'text' => 'نظام شامل لمتابعة تقدم الطلاب وإنجازاتهم'
                                    ]
                                ]
                            ],
                            [
                                [
                                    'type' => 'card',
                                    'content' => [
                                        'icon' => '🏆',
                                        'title' => 'نظام المكافآت',
                                        'text' => 'نقاط وشارات ومكافآت تحفز على التعلم المستمر'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    
                    // قسم 3: عمودين - صورة ونص
                    [
                        'columns' => 2,
                        'grid' => [
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h2',
                                        'text' => 'تعليم القيم بطريقة عصرية',
                                        'align' => 'right'
                                    ]
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'نستخدم أحدث التقنيات التعليمية لجعل تعلم القيم والأخلاق تجربة ممتعة ومثمرة. من خلال منصتنا، يمكن للطلاب المشاركة في أنشطة تفاعلية وتحديات يومية تعزز من قيمهم الأخلاقية.',
                                        'align' => 'right'
                                    ]
                                ],
                                [
                                    'type' => 'button',
                                    'content' => [
                                        'text' => 'اكتشف المزيد',
                                        'link' => '#',
                                        'style' => 'outline'
                                    ]
                                ]
                            ],
                            [
                                [
                                    'type' => 'image',
                                    'content' => [
                                        'url' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800&h=600&fit=crop',
                                        'alt' => 'تعليم القيم'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    
                    // قسم 4: أربعة أعمدة - إحصائيات
                    [
                        'columns' => 4,
                        'grid' => [
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h3',
                                        'text' => '1000+',
                                        'align' => 'center'
                                    ]
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'طالب نشط',
                                        'align' => 'center'
                                    ]
                                ]
                            ],
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h3',
                                        'text' => '50+',
                                        'align' => 'center'
                                    ]
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'مدرسة مشاركة',
                                        'align' => 'center'
                                    ]
                                ]
                            ],
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h3',
                                        'text' => '200+',
                                        'align' => 'center'
                                    ]
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'نشاط تعليمي',
                                        'align' => 'center'
                                    ]
                                ]
                            ],
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h3',
                                        'text' => '95%',
                                        'align' => 'center'
                                    ]
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'نسبة الرضا',
                                        'align' => 'center'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    
                    // قسم 5: عمود واحد - فيديو
                    [
                        'columns' => 1,
                        'grid' => [
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h2',
                                        'text' => 'شاهد كيف يعمل النظام',
                                        'align' => 'center'
                                    ]
                                ],
                                [
                                    'type' => 'video',
                                    'content' => [
                                        'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    
                    // قسم 6: ثلاثة أعمدة - خطط الأسعار
                    [
                        'columns' => 3,
                        'grid' => [
                            [
                                [
                                    'type' => 'card',
                                    'content' => [
                                        'icon' => '📦',
                                        'title' => 'الخطة الأساسية',
                                        'text' => 'مثالية للمدارس الصغيرة - 500 ريال شهرياً'
                                    ]
                                ],
                                [
                                    'type' => 'button',
                                    'content' => [
                                        'text' => 'اختر الخطة',
                                        'link' => '#',
                                        'style' => 'secondary'
                                    ]
                                ]
                            ],
                            [
                                [
                                    'type' => 'card',
                                    'content' => [
                                        'icon' => '🚀',
                                        'title' => 'الخطة المتقدمة',
                                        'text' => 'للمدارس المتوسطة - 1000 ريال شهرياً'
                                    ]
                                ],
                                [
                                    'type' => 'button',
                                    'content' => [
                                        'text' => 'اختر الخطة',
                                        'link' => '#',
                                        'style' => 'primary'
                                    ]
                                ]
                            ],
                            [
                                [
                                    'type' => 'card',
                                    'content' => [
                                        'icon' => '👑',
                                        'title' => 'الخطة المميزة',
                                        'text' => 'للمدارس الكبيرة - 2000 ريال شهرياً'
                                    ]
                                ],
                                [
                                    'type' => 'button',
                                    'content' => [
                                        'text' => 'اختر الخطة',
                                        'link' => '#',
                                        'style' => 'secondary'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    
                    // قسم 7: عمود واحد - دعوة للتواصل
                    [
                        'columns' => 1,
                        'grid' => [
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h2',
                                        'text' => 'هل أنت مستعد للبدء؟',
                                        'align' => 'center'
                                    ]
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'انضم إلى مئات المدارس التي تستخدم نظام القيم لتطوير مهارات طلابهم',
                                        'align' => 'center'
                                    ]
                                ],
                                [
                                    'type' => 'button',
                                    'content' => [
                                        'text' => 'تواصل معنا الآن',
                                        'link' => '#',
                                        'style' => 'primary'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'meta_title' => 'صفحة تجريبية - نظام القيم',
            'meta_description' => 'صفحة تجريبية لعرض إمكانيات نظام بناء الصفحات',
            'is_active' => true
        ]);

        $this->command->info('✅ تم إنشاء الصفحة التجريبية بنجاح!');
        $this->command->info('🔗 الرابط: http://127.0.0.2:8000/pages/demo-page');
    }
}
