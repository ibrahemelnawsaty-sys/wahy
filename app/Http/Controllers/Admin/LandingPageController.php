<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageBuilder;
use App\Models\Setting;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        // جلب إعدادات الثيم
        $themeSettings = [
            'site_name' => setting('site_name', 'نظام القيم'),
            'site_tagline' => setting('site_tagline', 'منصة تعليمية لبناء القيم'),
            'primary_color' => setting('primary_color', '#3CCB8A'),
            'secondary_color' => setting('secondary_color', '#3B82F6'),
            'font_family' => setting('font_family', 'IBM Plex Sans Arabic'),
            'site_logo' => setting('site_logo'),
            'site_favicon' => setting('site_favicon'),
        ];

        // جلب صفحة الـ Landing أو إنشاء واحدة افتراضية
        $landingPage = PageBuilder::where('slug', 'home')->first();

        if (! $landingPage) {
            $landingPage = $this->createDefaultLandingPage();
        }

        return view('admin.landing-page', compact('themeSettings', 'landingPage'));
    }

    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'font_family' => 'nullable|string|max:100',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        Setting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ إعدادات الثيم بنجاح!',
        ]);
    }

    public function updateContent(Request $request)
    {
        $validated = $request->validate([
            'json_data' => 'required|json',
        ]);

        $landingPage = PageBuilder::where('slug', 'home')->first();

        if (! $landingPage) {
            $landingPage = new PageBuilder;
            $landingPage->page_name = 'الصفحة الرئيسية';
            $landingPage->slug = 'home';
            $landingPage->is_active = true;
        }

        $landingPage->json_data = json_decode($validated['json_data'], true);
        $landingPage->save();

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ محتوى الصفحة بنجاح!',
        ]);
    }

    private function createDefaultLandingPage()
    {
        return PageBuilder::create([
            'page_name' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'json_data' => [
                'sections' => [
                    // Section 1: Hero Section
                    [
                        'columns' => 1,
                        'grid' => [
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h1',
                                        'text' => '🌟 ابنِ قيمك خطوة بخطوة',
                                        'align' => 'center',
                                    ],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'منصة تعليمية رائدة لبناء القيم الإنسانية وتعزيز الأخلاق الحميدة',
                                        'align' => 'center',
                                    ],
                                ],
                                [
                                    'type' => 'button',
                                    'content' => [
                                        'text' => 'ابدأ رحلتك الآن 🚀',
                                        'link' => '/login',
                                        'style' => 'primary',
                                    ],
                                ],
                            ],
                        ],
                    ],

                    // Section 2: Features (3 Columns)
                    [
                        'columns' => 3,
                        'grid' => [
                            [
                                [
                                    'type' => 'card',
                                    'content' => [
                                        'icon' => '📚',
                                        'title' => 'محتوى تعليمي متميز',
                                        'description' => 'دروس تفاعلية وأنشطة مبتكرة لبناء القيم',
                                        'link' => '#',
                                    ],
                                ],
                            ],
                            [
                                [
                                    'type' => 'card',
                                    'content' => [
                                        'icon' => '🏆',
                                        'title' => 'نظام مكافآت محفز',
                                        'description' => 'نقاط وشارات وجوائز لتحفيز التعلم',
                                        'link' => '#',
                                    ],
                                ],
                            ],
                            [
                                [
                                    'type' => 'card',
                                    'content' => [
                                        'icon' => '📊',
                                        'title' => 'تقارير وتحليلات',
                                        'description' => 'متابعة تقدم الطلاب بشكل دقيق',
                                        'link' => '#',
                                    ],
                                ],
                            ],
                        ],
                    ],

                    // Section 3: About (2 Columns)
                    [
                        'columns' => 2,
                        'grid' => [
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h2',
                                        'text' => 'لماذا نظام قيمّ؟',
                                        'align' => 'right',
                                    ],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'نظام قيمّ هو منصة تعليمية متكاملة تهدف إلى بناء شخصية الطالب من خلال القيم الإنسانية والأخلاق الحميدة. نوفر محتوى تفاعلي وأنشطة مبتكرة تساعد على ترسيخ القيم بطريقة ممتعة وفعالة.',
                                        'align' => 'right',
                                    ],
                                ],
                                [
                                    'type' => 'button',
                                    'content' => [
                                        'text' => 'اعرف المزيد',
                                        'link' => '#about',
                                        'style' => 'secondary',
                                    ],
                                ],
                            ],
                            [
                                [
                                    'type' => 'image',
                                    'content' => [
                                        'url' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b',
                                        'alt' => 'تعليم القيم',
                                        'caption' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],

                    // Section 4: Stats (4 Columns)
                    [
                        'columns' => 4,
                        'grid' => [
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h2',
                                        'text' => '1000+',
                                        'align' => 'center',
                                    ],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'طالب مستفيد',
                                        'align' => 'center',
                                    ],
                                ],
                            ],
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h2',
                                        'text' => '50+',
                                        'align' => 'center',
                                    ],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'مدرسة شريكة',
                                        'align' => 'center',
                                    ],
                                ],
                            ],
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h2',
                                        'text' => '100+',
                                        'align' => 'center',
                                    ],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'درس تفاعلي',
                                        'align' => 'center',
                                    ],
                                ],
                            ],
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h2',
                                        'text' => '20+',
                                        'align' => 'center',
                                    ],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'قيمة أساسية',
                                        'align' => 'center',
                                    ],
                                ],
                            ],
                        ],
                    ],

                    // Section 5: CTA
                    [
                        'columns' => 1,
                        'grid' => [
                            [
                                [
                                    'type' => 'heading',
                                    'content' => [
                                        'level' => 'h2',
                                        'text' => 'هل أنت مستعد لبناء قيمك؟',
                                        'align' => 'center',
                                    ],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        'text' => 'انضم إلينا الآن وابدأ رحلتك في بناء شخصية متميزة',
                                        'align' => 'center',
                                    ],
                                ],
                                [
                                    'type' => 'button',
                                    'content' => [
                                        'text' => 'سجل الآن مجاناً ✨',
                                        'link' => '/register',
                                        'style' => 'primary',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'meta_title' => 'قيمّ - ابنِ قيمك خطوة بخطوة',
            'meta_description' => 'منصة تعليمية رائدة لبناء القيم الإنسانية وتعزيز الأخلاق الحميدة. محتوى تفاعلي وأنشطة مبتكرة للطلاب والمعلمين.',
            'is_active' => true,
        ]);
    }
}
