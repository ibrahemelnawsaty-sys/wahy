@php
    // جلب جميع الإعدادات دفعة واحدة - أسرع × 8 مرات
    $settings = \App\Models\Setting::getMany(
        ['font_family', 'primary_color', 'secondary_color', 'text_color', 'background_color', 'site_logo', 'site_name', 'site_description', 'contact_email', 'contact_phone', 'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url'],
        [
            'font_family' => 'IBM Plex Sans Arabic',
            'primary_color' => '#3CCB8A',
            'secondary_color' => '#3B82F6',
            'text_color' => '#1e293b',
            'background_color' => '#f8fafc',
            'site_name' => 'قيمّ',
            'site_description' => 'منصة تعليمية رائدة لبناء القيم الإنسانية',
            'contact_email' => null,
            'contact_phone' => null,
            'facebook_url' => null,
            'twitter_url' => null,
            'instagram_url' => null,
            'linkedin_url' => null
        ]
    );
    
    $fontFamily = $settings['font_family'];
    $primaryColor = $settings['primary_color'];
    $secondaryColor = $settings['secondary_color'];
    $textColor = $settings['text_color'];
    $backgroundColor = $settings['background_color'];
    $siteLogo = $settings['site_logo'] ?? null;
    $siteName = $settings['site_name'];
    $siteDescription = $settings['site_description'];
    $contactEmail = $settings['contact_email'];
    $contactPhone = $settings['contact_phone'];
    $facebookUrl = $settings['facebook_url'];
    $twitterUrl = $settings['twitter_url'];
    $instagramUrl = $settings['instagram_url'];
    $linkedinUrl = $settings['linkedin_url'];
    
    // Cache حساب الألوان - توفير 15-30ms
    $primaryHover = adjustBrightness($primaryColor, -20);
    $primaryLight = hexToRgba($primaryColor, 0.1);
    $secondaryHover = adjustBrightness($secondaryColor, -20);
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    {{-- Theme init - prevents flash of wrong theme (FOUC) --}}
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('wahy-theme') || 'dark');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $siteDescription }}">
    <meta name="theme-color" content="{{ $primaryColor }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- SEO & Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $siteName }} - ابنِ قيمك خطوة بخطوة">
    <meta property="og:description" content="{{ $siteDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    @if($siteLogo)
    <meta property="og:image" content="{{ asset('storage/app/public/data/' . $siteLogo) }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $siteName }}">
    <meta name="twitter:description" content="{{ $siteDescription }}">
    
    <title>{{ $siteName }} - ابنِ قيمك خطوة بخطوة</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192x192.png') }}">
    
    <!-- DNS Prefetch للموارد الخارجية -->
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    
    <!-- Preload Critical Assets للتحميل الأسرع -->
    <link rel="preload" href="{{ asset('css/landing.css') }}?v={{ @filemtime(public_path('css/landing.css')) ?: '1' }}" as="style">
    <link rel="preload" href="{{ asset('js/landing.js') }}?v={{ @filemtime(public_path('js/landing.js')) ?: '1' }}" as="script">
    <!-- إزالة preload لـ icons.svg لأنه يُستخدم بشكل lazy في SVG sprites -->
    
    <!-- Critical CSS Inline - تحميل فوري لـ Above the Fold -->
    @include('partials.critical-css')
    
    <!-- الخطوط محملة محلياً في landing.css ✓ (أسرع بـ 200ms من Google Fonts) -->
    
    <!-- تحميل landing.css -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ @filemtime(public_path('css/landing.css')) ?: '1' }}">
    
        
    <!-- Premium Glassmorphism Design -->
    <link rel="stylesheet" href="{{ asset('css/glass-luxury.css') }}?v={{ @filemtime(public_path('css/glass-luxury.css')) ?: '1' }}">
    
    <!-- CSS ديناميكي من قاعدة البيانات - Cached Colors ⚡ -->
    <style>
        :root {
            /* الألوان الرئيسية */
            --color-primary: {{ $primaryColor }};
            --color-primary-hover: {{ $primaryHover }};
            --color-primary-light: {{ $primaryLight }};
            
            /* الألوان الثانوية */
            --color-secondary: {{ $secondaryColor }};
            --color-secondary-hover: {{ $secondaryHover }};
            
            /* ألوان النصوص والخلفيات */
            --color-text: {{ $textColor }};
            --color-bg: {{ $backgroundColor }};
            
            /* الخط */
            --font-family-base: '{{ $fontFamily }}', sans-serif;
        }
        
        /* منع الانزياح الأفقي - Critical Fix ⚠️ */
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
            position: relative;
        }
        
        body {
            font-family: var(--font-family-base);
        }
        
        /* ضمان عدم تجاوز العناصر لعرض الشاشة */
        *, *::before, *::after {
            box-sizing: border-box;
        }
        
        img, video, iframe, embed, object {
            max-width: 100%;
            height: auto;
        }
        
        /* إصلاح العناصر المطلقة */
        .edit-toolbar,
        .edit-toggle-btn,
        .components-sidebar,
        .properties-panel {
            max-width: 100vw;
        }
    </style>
    
    <script src="{{ asset('js/landing.js') }}?v={{ @filemtime(public_path('js/landing.js')) ?: '1' }}" defer></script>
    <script src="{{ asset('js/theme.js') }}?v={{ @filemtime(public_path('js/theme.js')) ?: '1' }}" defer></script>
    
    @auth
    @if(auth()->user()->role === 'super_admin')
    <!-- Edit Mode Styles - ملف منفصل -->
    <link rel="stylesheet" href="{{ asset('css/landing-edit-mode.css') }}">
    <!-- Edit Mode JavaScript - يُحمّل أولاً قبل Alpine.js -->
    <script src="{{ asset('js/landing-editor.js') }}"></script>
    <!-- Alpine.js for Edit Mode - يُحمّل بعد landing-editor.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    @endif
    @endauth

    {{-- ⚡ تحسين الأداء على الجوال (Issue 28) — توسيع للنسخة المُحسَّنة --}}
    <style>
        @media (max-width: 768px) {
            /* تعطيل backdrop-filter بالكامل على الجوال — يبطئ التمرير بشدة */
            *,
            *::before,
            *::after {
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }

            /* إيقاف animations المُكلفة (تشغل GPU) — مع إبقاء transitions البسيطة */
            *, *::before, *::after {
                animation-duration: 0.001s !important;
                animation-delay: 0s !important;
            }
            /* تقليل shadow blur — أقل تكلفة على GPU */
            [style*="box-shadow"] { box-shadow: 0 2px 6px rgba(0,0,0,0.1) !important; }

            /* إلغاء transforms المعقدة على scroll */
            section { transform: none !important; will-change: auto !important; }

            /* تصغير العناوين الكبيرة */
            section h1[style*="font-size: 56px"],
            section h1[style*="font-size:56px"] { font-size: 32px !important; line-height: 1.25 !important; }
            section h1[style*="font-size: 48px"] { font-size: 28px !important; }
            section p[style*="font-size: 22px"] { font-size: 16px !important; }
            section [style*="padding: 100px 0"] { padding: 50px 0 !important; }
            section [style*="padding: 80px 0"] { padding: 40px 0 !important; }

            /* إخفاء decorative pseudo-elements الثقيلة */
            section[class*="hero"]::before,
            section[class*="hero"]::after,
            section::before,
            section::after { display: none !important; }

            /* إيقاف hover effects التي تسبب reflow */
            section *:hover { transform: none !important; }
        }

        /* تحميل ناعم للصور */
        img { content-visibility: auto; }
        img[loading="lazy"] { background: #f1f5f9; }
    </style>

    {{-- إضافة loading="lazy" تلقائياً لكل الصور بعد التحميل --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('img:not([loading])').forEach(img => img.loading = 'lazy');
        });
    </script>
</head>
<body @auth @if(auth()->user()->role === 'super_admin') x-data="landingEditor()" @endif @endauth>
    <a href="#main-content" class="skip-link">الانتقال إلى المحتوى الرئيسي</a>
    
    <header class="header">
        <div class="container">
            <nav class="navbar" role="navigation">
                <div class="editable-element" data-element="header-logo">
                    <x-element-actions />
                    <a href="/" class="logo">
                        @if($siteLogo)
                            <img src="{{ asset('storage/app/public/data/' . $siteLogo) }}" alt="{{ $siteName }}" class="logo-img" data-editable-image="site_logo">
                        @else
                            <span class="logo-icon" data-editable="logo_icon" data-section="header">🌟</span>
                            <span class="logo-text" data-editable="logo_text" data-section="header">{{ $siteName }}</span>
                        @endif
                    </a>
                </div>
                <div class="nav-links" id="navLinks">
                    <div class="editable-element" data-element="nav-link-1">
                        <x-element-actions />
                        <a href="#home" class="nav-link active" data-editable="nav_link_1" data-section="header">الرئيسية</a>
                    </div>
                    <div class="editable-element" data-element="nav-link-2">
                        <x-element-actions />
                        <a href="#features" class="nav-link" data-editable="nav_link_2" data-section="header">المميزات</a>
                    </div>
                    <div class="editable-element" data-element="nav-link-3">
                        <x-element-actions />
                        <a href="#values" class="nav-link" data-editable="nav_link_3" data-section="header">القيم</a>
                    </div>
                    <div class="editable-element" data-element="nav-link-4">
                        <x-element-actions />
                        <a href="#activities" class="nav-link" data-editable="nav_link_4" data-section="header">الأنشطة</a>
                    </div>
                    <div class="editable-element" data-element="nav-link-5">
                        <x-element-actions />
                        <a href="#partners" class="nav-link" data-editable="nav_link_5" data-section="header">الشركاء</a>
                    </div>
                    <div class="editable-element" data-element="nav-link-6">
                        <x-element-actions />
                        <a href="#support" class="nav-link" data-editable="nav_link_6" data-section="header">الدعم</a>
                    </div>
                    {{-- زر تسجيل الدخول داخل قائمة الجوال (مخفي على الديسكتوب حيث يظهر في nav-actions) --}}
                    <a href="{{ url('/login') }}" class="nav-link nav-mobile-login">تسجيل الدخول</a>
                </div>
                <div class="nav-actions">
                    <!-- Theme Toggle Button -->
                    <div class="editable-element" data-element="theme-toggle">
                        <x-element-actions />
                        <button class="theme-toggle" id="themeToggle" aria-label="تبديل الوضع">
                            <span class="icon-sun" data-editable-icon="theme_sun">☀️</span>
                            <span class="icon-moon" data-editable-icon="theme_moon">🌙</span>
                        </button>
                    </div>
                    <div class="editable-element" data-element="login-btn">
                        <x-element-actions />
                        <a href="/login" class="btn btn-outline" data-editable="login_btn_text" data-section="header">تسجيل دخول</a>
                    </div>
                    <div class="editable-element" data-element="register-btn">
                        <x-element-actions />
                        <a href="/register" class="btn btn-primary" data-editable="register_btn_text" data-section="header">ابدأ الآن</a>
                    </div>
                </div>
                <button class="menu-toggle" aria-label="فتح القائمة" aria-expanded="false"><span></span><span></span><span></span></button>
            </nav>
        </div>
    </header>
    <main id="main-content">
        <section class="hero" id="home">
            @auth @if(auth()->user()->role === 'super_admin')
            <div class="section-actions" style="display:none;">
                <button onclick="window.landingEditorInstance.duplicateSection(this.closest('section'))" title="نسخ القسم">📋</button>
                <button onclick="window.landingEditorInstance.editSectionProperties(this.closest('section'))" title="تعديل الخصائص">⚙️</button>
                <button onclick="if(confirm('حذف هذا القسم؟')) this.closest('section').remove()" title="حذف القسم">🗑️</button>
            </div>
            @endif @endauth
            <div class="container">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="editable-element" data-element="hero-title">
                            <x-element-actions />
                            <h1 class="hero-title" data-editable="hero_title" data-section="hero">منصة القيم المدرسية – تعليم يعيش مع الطلاب</h1>
                        </div>
                        <div class="editable-element" data-element="hero-description">
                            <x-element-actions />
                            <p class="hero-description" data-editable="hero_description" data-section="hero">نبني القيم الإنسانية بطريقة تفاعلية وممتعة. منصة شاملة تربط المدرسة والمعلم والطالب وولي الأمر في بيئة تعليمية آمنة ومحفزة.</p>
                        </div>
                        <div class="hero-actions">
                            <div class="editable-element" data-element="hero-btn-primary">
                                <x-element-actions />
                                <a href="/register" class="btn btn-primary btn-lg">
                                    <span data-editable="hero_btn_primary" data-section="hero">ابدأ الآن</span>
                                    <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-arrow-left"/></svg>
                                </a>
                            </div>
                            <div class="editable-element" data-element="hero-btn-secondary">
                                <x-element-actions />
                                <a href="#features" class="btn btn-secondary btn-lg">
                                    <span data-editable="hero_btn_secondary" data-section="hero">اعرف المزيد</span>
                                    <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-chevron-down"/></svg>
                                </a>
                            </div>
                        </div>
                        <div class="hero-stats">
                            <div class="stat-item editable-element" data-element="stat-schools">
                                <x-element-actions />
                                <span class="stat-number" data-editable="stat_schools" data-section="hero">500+</span>
                                <span class="stat-label" data-editable="stat_schools_label" data-section="hero">مدرسة</span>
                            </div>
                            <div class="stat-item editable-element" data-element="stat-students">
                                <x-element-actions />
                                <span class="stat-number" data-editable="stat_students" data-section="hero">50k+</span>
                                <span class="stat-label" data-editable="stat_students_label" data-section="hero">طالب</span>
                            </div>
                            <div class="stat-item editable-element" data-element="stat-teachers">
                                <x-element-actions />
                                <span class="stat-number" data-editable="stat_teachers" data-section="hero">2k+</span>
                                <span class="stat-label" data-editable="stat_teachers_label" data-section="hero">معلم</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-visual">
                        <div class="editable-element" data-element="hero-image">
                            <x-element-actions />
                            <picture>
                                <source type="image/webp" data-srcset="{{ asset('images/hero-illustration.webp') }}">
                                <img data-src="{{ asset('images/hero-illustration.svg') }}" 
                                     data-editable-image="hero_image" 
                                     alt="رسم توضيحي" 
                                     class="hero-image" 
                                     loading="lazy">
                            </picture>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="features section" id="features">
            @auth @if(auth()->user()->role === 'super_admin')
            <div class="section-actions" style="display:none;">
                <button onclick="window.landingEditorInstance.duplicateSection(this.closest('section'))" title="نسخ القسم">📋</button>
                <button onclick="window.landingEditorInstance.editSectionProperties(this.closest('section'))" title="تعديل الخصائص">⚙️</button>
                <button onclick="if(confirm('حذف هذا القسم؟')) this.closest('section').remove()" title="حذف القسم">🗑️</button>
            </div>
            @endif @endauth
            <div class="container">
                <div class="section-header">
                    <div class="editable-element" data-element="features-title">
                        <x-element-actions />
                        <h2 class="section-title" data-editable="features_title" data-section="features">لماذا قيمّ؟</h2>
                    </div>
                    <div class="editable-element" data-element="features-subtitle">
                        <x-element-actions />
                        <p class="section-subtitle" data-editable="features_subtitle" data-section="features">نظام متكامل بمميزات فريدة</p>
                    </div>
                </div>
                <div class="features-grid">
                    <article class="feature-card editable-element" data-element="feature-card-1">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_1_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-qrcode"/></svg></div>
                        <h3 data-editable="feature_1_title" data-section="features">QR فريد لكل مستخدم</h3>
                        <p data-editable="feature_1_desc" data-section="features">كل طالب ومعلم لديه رمز QR خاص للدخول السريع وتسجيل الحضور والأنشطة</p>
                    </article>
                    <article class="feature-card editable-element" data-element="feature-card-2">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_2_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-trophy"/></svg></div>
                        <h3 data-editable="feature_2_title" data-section="features">لوحة صدارة ذكية</h3>
                        <p data-editable="feature_2_desc" data-section="features">نظام تنافسي محفز يعرض أفضل الطلاب والفرق بناءً على الإنجازات والنقاط</p>
                    </article>
                    <article class="feature-card editable-element" data-element="feature-card-3">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_3_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-brain"/></svg></div>
                        <h3 data-editable="feature_3_title" data-section="features">اقتراح أنشطة بالذكاء الاصطناعي</h3>
                        <p data-editable="feature_3_desc" data-section="features">نظام ذكي يقترح أنشطة مخصصة لكل طالب حسب مستواه واهتماماته</p>
                    </article>
                    <article class="feature-card editable-element" data-element="feature-card-4">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_4_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-user-check"/></svg></div>
                        <h3 data-editable="feature_4_title" data-section="features">متابعة وتقييم المعلمين</h3>
                        <p data-editable="feature_4_desc" data-section="features">أدوات شاملة لمتابعة أداء الطلاب وتقييمهم بطرق متنوعة ومرنة</p>
                    </article>
                </div>
            </div>
        </section>
        
        <section class="values-section section section-alt" id="values">
            <div class="container">
                <div class="section-header">
                    <div class="editable-element" data-element="values-title">
                        <x-element-actions />
                        <h2 class="section-title" data-editable="values_title" data-section="values">كيف نبني القيم؟</h2>
                    </div>
                    <div class="editable-element" data-element="values-subtitle">
                        <x-element-actions />
                        <p class="section-subtitle" data-editable="values_subtitle" data-section="values">منهجية متكاملة من القيمة إلى التطبيق العملي</p>
                    </div>
                </div>
                
                <div class="values-flow">
                    <div class="flow-card editable-element" data-element="flow-card-1">
                        <x-element-actions />
                        <div class="flow-number" data-editable="flow_1_number" data-section="values">1</div>
                        <div class="flow-icon" data-editable-icon="flow_1_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-heart"/></svg></div>
                        <h3 data-editable="flow_1_title" data-section="values">القيمة</h3>
                        <p data-editable="flow_1_example" data-section="values">مثال: <strong>الصدق</strong></p>
                        <span class="flow-desc" data-editable="flow_1_desc" data-section="values">القيمة الأساسية التي نريد غرسها</span>
                    </div>
                    
                    <div class="flow-arrow editable-element" data-element="flow-arrow-1">
                        <x-element-actions />
                        <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-arrow-left"/></svg>
                    </div>
                    
                    <div class="flow-card editable-element" data-element="flow-card-2">
                        <x-element-actions />
                        <div class="flow-number" data-editable="flow_2_number" data-section="values">2</div>
                        <div class="flow-icon" data-editable-icon="flow_2_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-lightbulb"/></svg></div>
                        <h3 data-editable="flow_2_title" data-section="values">المفهوم</h3>
                        <p data-editable="flow_2_example" data-section="values">مثال: <strong>الأمانة</strong></p>
                        <span class="flow-desc" data-editable="flow_2_desc" data-section="values">الفكرة الرئيسية المرتبطة بالقيمة</span>
                    </div>
                    
                    <div class="flow-arrow editable-element" data-element="flow-arrow-2">
                        <x-element-actions />
                        <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-arrow-left"/></svg>
                    </div>
                    
                    <div class="flow-card editable-element" data-element="flow-card-3">
                        <x-element-actions />
                        <div class="flow-number" data-editable="flow_3_number" data-section="values">3</div>
                        <div class="flow-icon" data-editable-icon="flow_3_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-book-open"/></svg></div>
                        <h3 data-editable="flow_3_title" data-section="values">المعنى</h3>
                        <p data-editable="flow_3_example" data-section="values">مثال: <strong>قول الحقيقة دائماً</strong></p>
                        <span class="flow-desc" data-editable="flow_3_desc" data-section="values">الشرح التفصيلي والمبسط</span>
                    </div>
                    
                    <div class="flow-arrow editable-element" data-element="flow-arrow-3">
                        <x-element-actions />
                        <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-arrow-left"/></svg>
                    </div>
                    
                    <div class="flow-card editable-element" data-element="flow-card-4">
                        <x-element-actions />
                        <div class="flow-number" data-editable="flow_4_number" data-section="values">4</div>
                        <div class="flow-icon" data-editable-icon="flow_4_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-tasks"/></svg></div>
                        <h3 data-editable="flow_4_title" data-section="values">النشاط</h3>
                        <p data-editable="flow_4_example" data-section="values">مثال: <strong>قصة تفاعلية + تطبيق عملي</strong></p>
                        <span class="flow-desc" data-editable="flow_4_desc" data-section="values">التطبيق العملي للطالب</span>
                    </div>
                </div>
                
                <div class="values-example">
                    <h3>مثال عملي: تعليم قيمة الصدق</h3>
                    <div class="example-steps">
                        <div class="example-step">
                            <span class="step-badge">القيمة</span>
                            <p>الصدق</p>
                        </div>
                        <span class="step-separator">←</span>
                        <div class="example-step">
                            <span class="step-badge">المفهوم</span>
                            <p>الأمانة في القول والفعل</p>
                        </div>
                        <span class="step-separator">←</span>
                        <div class="example-step">
                            <span class="step-badge">المعنى</span>
                            <p>أن تقول الحقيقة دائماً حتى لو كان صعباً</p>
                        </div>
                        <span class="step-separator">←</span>
                        <div class="example-step">
                            <span class="step-badge">النشاط</span>
                            <p>قصة أحمد الصادق + مسابقة + نقاط</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="teams-section section" id="activities">
            <div class="container">
                <div class="section-header">
                    <div class="editable-element" data-element="teams-title">
                        <x-element-actions />
                        <h2 class="section-title" data-editable="teams_title" data-section="teams">التعلم التعاوني مع الفرق</h2>
                    </div>
                    <div class="editable-element" data-element="teams-subtitle">
                        <x-element-actions />
                        <p class="section-subtitle" data-editable="teams_subtitle" data-section="teams">نظام فرق ذكي يحفز الطلاب على التعاون والتنافس الإيجابي</p>
                    </div>
                </div>
                
                <div class="teams-content">
                    <div class="teams-info">
                        <h3>كيف يعمل نظام الفرق؟</h3>
                        <ul class="teams-features">
                            <li>
                                <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-users"/></svg>
                                <div>
                                    <strong>فرق صغيرة</strong>
                                    <p>كل فصل يُقسم إلى فرق من 4-6 طلاب لتحقيق التعاون الفعّال</p>
                                </div>
                            </li>
                            <li>
                                <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-star"/></svg>
                                <div>
                                    <strong>نظام نقاط متطور</strong>
                                    <p>كل فريق يكسب نقاط عند إتمام الأنشطة والمهام الجماعية</p>
                                </div>
                            </li>
                            <li>
                                <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-medal"/></svg>
                                <div>
                                    <strong>جوائز وتحفيز</strong>
                                    <p>الفريق الفائز يحصل على شارات وجوائز تشجيعية</p>
                                </div>
                            </li>
                            <li>
                                <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-chart-bar"/></svg>
                                <div>
                                    <strong>لوحة صدارة</strong>
                                    <p>متابعة أداء الفرق في الوقت الفعلي وتحديثات مستمرة</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="teams-visual">
                        <div class="team-card team-card-primary editable-element" data-element="team-card-1">
                            <x-element-actions />
                            <div class="team-rank" data-editable="team_1_rank" data-section="teams">1</div>
                            <div class="team-icon" data-editable-icon="team_1_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-trophy"/></svg></div>
                            <h4 data-editable="team_1_name" data-section="teams">فريق النجوم</h4>
                            <div class="team-points" data-editable="team_1_points" data-section="teams">2,450 نقطة</div>
                            <div class="team-members">
                                <span class="member-avatar">أ</span>
                                <span class="member-avatar">م</span>
                                <span class="member-avatar">س</span>
                                <span class="member-avatar">ل</span>
                                <span class="member-more">+2</span>
                            </div>
                        </div>
                        
                        <div class="team-card team-card-secondary editable-element" data-element="team-card-2">
                            <x-element-actions />
                            <div class="team-rank" data-editable="team_2_rank" data-section="teams">2</div>
                            <div class="team-icon" data-editable-icon="team_2_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-rocket"/></svg></div>
                            <h4 data-editable="team_2_name" data-section="teams">فريق الصواريخ</h4>
                            <div class="team-points" data-editable="team_2_points" data-section="teams">2,180 نقطة</div>
                            <div class="team-members">
                                <span class="member-avatar">ف</span>
                                <span class="member-avatar">ر</span>
                                <span class="member-avatar">ك</span>
                                <span class="member-avatar">ه</span>
                            </div>
                        </div>
                        
                        <div class="team-card team-card-accent editable-element" data-element="team-card-3">
                            <x-element-actions />
                            <div class="team-rank" data-editable="team_3_rank" data-section="teams">3</div>
                            <div class="team-icon" data-editable-icon="team_3_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-gem"/></svg></div>
                            <h4 data-editable="team_3_name" data-section="teams">فريق الماس</h4>
                            <div class="team-points" data-editable="team_3_points" data-section="teams">1,920 نقطة</div>
                            <div class="team-members">
                                <span class="member-avatar">ن</span>
                                <span class="member-avatar">ب</span>
                                <span class="member-avatar">ي</span>
                                <span class="member-avatar">د</span>
                                <span class="member-more">+1</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="teams-benefits">
                    <div class="editable-element" data-element="benefits-title">
                        <x-element-actions />
                        <h3 data-editable="benefits_title" data-section="teams">فوائد التعلم التعاوني</h3>
                    </div>
                    <div class="benefits-grid">
                        <div class="benefit-card editable-element" data-element="benefit-card-1">
                            <x-element-actions />
                            <div data-editable-icon="benefit_1_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-handshake"/></svg></div>
                            <h4 data-editable="benefit_1_title" data-section="teams">تعزيز التعاون</h4>
                            <p data-editable="benefit_1_desc" data-section="teams">يتعلم الطلاب العمل معاً وتحقيق الأهداف المشتركة</p>
                        </div>
                        <div class="benefit-card editable-element" data-element="benefit-card-2">
                            <x-element-actions />
                            <div data-editable-icon="benefit_2_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-comments"/></svg></div>
                            <h4 data-editable="benefit_2_title" data-section="teams">تطوير التواصل</h4>
                            <p data-editable="benefit_2_desc" data-section="teams">تحسين مهارات التواصل والاستماع للآخرين</p>
                        </div>
                        <div class="benefit-card editable-element" data-element="benefit-card-3">
                            <x-element-actions />
                            <div data-editable-icon="benefit_3_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-brain"/></svg></div>
                            <h4 data-editable="benefit_3_title" data-section="teams">تنمية التفكير</h4>
                            <p data-editable="benefit_3_desc" data-section="teams">تبادل الأفكار يساعد على التفكير النقدي والإبداعي</p>
                        </div>
                        <div class="benefit-card editable-element" data-element="benefit-card-4">
                            <x-element-actions />
                            <div data-editable-icon="benefit_4_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-heart"/></svg></div>
                            <h4 data-editable="benefit_4_title" data-section="teams">بناء العلاقات</h4>
                            <p data-editable="benefit_4_desc" data-section="teams">تكوين صداقات وعلاقات إيجابية بين الطلاب</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="partners section section-alt" id="partners">
            <div class="container">
                <div class="section-header">
                    <div class="editable-element" data-element="partners-title">
                        <x-element-actions />
                        <h2 class="section-title" data-editable="partners_title" data-section="partners">شركاؤنا في النجاح</h2>
                    </div>
                    <div class="editable-element" data-element="partners-subtitle">
                        <x-element-actions />
                        <p class="section-subtitle" data-editable="partners_subtitle" data-section="partners">ثقة أكثر من 500 مدرسة ومؤسسة تعليمية رائدة</p>
                    </div>
                </div>

                <div class="partners-grid">
                    <div class="partner-logo editable-element" data-element="partner-logo-1">
                        <x-element-actions />
                        <picture>
                            <source type="image/webp" data-srcset="{{ asset('images/partners/school-1.webp') }}">
                            <img data-src="{{ asset('images/partners/school-1.png') }}" alt="شعار مدرسة النور الأهلية" loading="lazy" data-editable-image="partner_logo_1">
                        </picture>
                    </div>
                    <div class="partner-logo editable-element" data-element="partner-logo-2">
                        <x-element-actions />
                        <picture>
                            <source type="image/webp" data-srcset="{{ asset('images/partners/school-2.webp') }}">
                            <img data-src="{{ asset('images/partners/school-2.png') }}" alt="شعار مدرسة الرؤية الحديثة" loading="lazy" data-editable-image="partner_logo_2">
                        </picture>
                    </div>
                    <div class="partner-logo editable-element" data-element="partner-logo-3">
                        <x-element-actions />
                        <picture>
                            <source type="image/webp" data-srcset="{{ asset('images/partners/school-3.webp') }}">
                            <img data-src="{{ asset('images/partners/school-3.png') }}" alt="شعار أكاديمية التميز الدولية" loading="lazy" data-editable-image="partner_logo_3">
                        </picture>
                    </div>
                    <div class="partner-logo editable-element" data-element="partner-logo-4">
                        <x-element-actions />
                        <picture>
                            <source type="image/webp" data-srcset="{{ asset('images/partners/school-4.webp') }}">
                            <img data-src="{{ asset('images/partners/school-4.png') }}" alt="شعار مدارس الإبداع" loading="lazy" data-editable-image="partner_logo_4">
                        </picture>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Contact Us Section -->
        <section class="contact-section section section-alt" id="support">
            <div class="container">
                <div class="contact-wrapper">
                    <!-- Info Panel -->
                    <div class="contact-info-panel">
                        <div class="editable-element" data-element="contact-title">
                            <x-element-actions />
                            <h2 class="contact-title" data-editable="contact_title" data-section="contact">يسعدنا تواصلك معنا</h2>
                        </div>
                        <div class="editable-element" data-element="contact-description">
                            <x-element-actions />
                            <p class="contact-description" data-editable="contact_description" data-section="contact">
                                فريقنا جاهز للإجابة على جميع استفساراتكم المتعلقة بالمنصة، القيم، الأنشطة، أو الدعم الفني.
                            </p>
                        </div>

                        <div class="contact-details-list">
                            <div class="contact-detail-item">
                                <span class="contact-detail-icon">📧</span>
                                <div class="contact-detail-content">
                                    <strong>البريد الإلكتروني</strong>
                                    <a href="mailto:support@qiyamm.sa">support@qiyamm.sa</a>
                                </div>
                            </div>

                            <div class="contact-detail-item">
                                <span class="contact-detail-icon">☎️</span>
                                <div class="contact-detail-content">
                                    <strong>رقم الهاتف</strong>
                                    <a href="tel:+966500000000">+966 5 000 0000</a>
                                </div>
                            </div>

                            <div class="contact-detail-item">
                                <span class="contact-detail-icon">🕒</span>
                                <div class="contact-detail-content">
                                    <strong>أوقات العمل</strong>
                                    <span>الأحد – الخميس | 8:00 صباحًا – 4:00 مساءً</span>
                                </div>
                            </div>
                        </div>

                        <div class="contact-social">
                            <h3>تابعنا على</h3>
                            <div class="contact-social-links">
                                @if(!empty($facebookUrl))
                                <a href="{{ $facebookUrl }}" class="contact-social-link" aria-label="فيسبوك" target="_blank" rel="noopener noreferrer">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                                @endif
                                @if(!empty($instagramUrl))
                                <a href="{{ $instagramUrl }}" class="contact-social-link" aria-label="إنستغرام" target="_blank" rel="noopener noreferrer">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                                    </svg>
                                </a>
                                @endif
                                @if(!empty($twitterUrl))
                                <a href="{{ $twitterUrl }}" class="contact-social-link" aria-label="تويتر" target="_blank" rel="noopener noreferrer">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </a>
                                @endif
                                @if(!empty($linkedinUrl))
                                <a href="{{ $linkedinUrl }}" class="contact-social-link" aria-label="لينكد إن" target="_blank" rel="noopener noreferrer">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="contact-form-panel">
                        <form action="/contact" method="POST" class="contact-form" id="contactForm">
                            @csrf
                            
                            <div class="form-group">
                                <label for="full_name" class="form-label">الاسم الكامل</label>
                                <input 
                                    type="text" 
                                    id="full_name" 
                                    name="full_name" 
                                    class="form-input" 
                                    required
                                    placeholder="أدخل اسمك الكامل"
                                >
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="form-input" 
                                    required
                                    placeholder="example@domain.com"
                                >
                            </div>

                            <div class="form-group">
                                <label for="user_type" class="form-label">نوع المستخدم</label>
                                <select id="user_type" name="user_type" class="form-select" required>
                                    <option value="">اختر نوع المستخدم</option>
                                    <option value="school">مدرسة</option>
                                    <option value="teacher">معلم</option>
                                    <option value="parent">ولي أمر</option>
                                    <option value="student">طالب</option>
                                    <option value="institution">جهة تعليمية</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="message" class="form-label">الرسالة</label>
                                <textarea 
                                    id="message" 
                                    name="message" 
                                    class="form-textarea" 
                                    rows="6" 
                                    required
                                    placeholder="اكتب رسالتك هنا..."
                                ></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-full">
                                <span class="btn-text">إرسال الرسالة</span>
                                <span class="btn-loader" style="display: none;">
                                    <span class="loading-dot"></span>
                                    <span class="loading-dot"></span>
                                    <span class="loading-dot"></span>
                                </span>
                            </button>

                            <div class="form-message" id="formMessage" style="display: none;"></div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="cta section">
            <div class="container">
                <div class="cta-content">
                    <div class="editable-element" data-element="cta-title">
                        <x-element-actions />
                        <h2 data-editable="cta_title" data-section="cta">جاهز للانضمام؟</h2>
                    </div>
                    <div class="editable-element" data-element="cta-subtitle">
                        <x-element-actions />
                        <p data-editable="cta_subtitle" data-section="cta">ابدأ رحلتك اليوم</p>
                    </div>
                    <div class="cta-actions">
                        <div class="editable-element" data-element="cta-button">
                            <x-element-actions />
                            <a href="/register" class="btn btn-primary btn-lg" data-editable="cta_button_text" data-section="cta">ابدأ مجاناً</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    @include('components.footer')
    
    @auth
    @if(auth()->user()->role === 'super_admin')
    <!-- ======================================
         نظام التحرير الاحترافي - Professional Edit FAB
         ====================================== -->
    <div class="edit-fab-container" 
         :class="[
            'position-' + fabPosition,
            { 'minimized': fabMinimized, 'dragging': fabDragging }
         ]"
         x-data="{
            fabPosition: localStorage.getItem('editFabPosition') || 'bottom-left',
            fabMinimized: localStorage.getItem('editFabMinimized') === 'true',
            fabDragging: false,
            menuOpen: false,
            
            get isEditMode() {
                return window.landingEditorInstance?.editMode || false;
            },
            
            setPosition(pos) {
                this.fabPosition = pos;
                localStorage.setItem('editFabPosition', pos);
                this.menuOpen = false;
            },
            
            toggleMinimize() {
                this.fabMinimized = !this.fabMinimized;
                localStorage.setItem('editFabMinimized', this.fabMinimized);
            },
            
            handleToggle() {
                if (this.fabMinimized) {
                    this.fabMinimized = false;
                    localStorage.setItem('editFabMinimized', 'false');
                } else {
                    if (window.landingEditorInstance) {
                        window.landingEditorInstance.toggleEditMode();
                    }
                }
            }
         }">
        
        <!-- قائمة الإعدادات المنبثقة -->
        <div class="edit-fab-menu" :class="{ 'show': menuOpen }" @click.away="menuOpen = false">
            <button class="edit-fab-menu-item" :class="{ 'active': isEditMode }" @click="handleToggle()">
                <span class="menu-icon" x-text="isEditMode ? '✅' : '✏️'"></span>
                <span x-text="isEditMode ? 'وضع التحرير نشط' : 'تفعيل التحرير'"></span>
            </button>
            
            <div class="edit-fab-menu-divider"></div>
            
            <button class="edit-fab-menu-item" :class="{ 'active': fabPosition === 'bottom-left' }" @click="setPosition('bottom-left')">
                <span class="menu-icon">↙️</span>
                <span>أسفل يسار</span>
            </button>
            <button class="edit-fab-menu-item" :class="{ 'active': fabPosition === 'bottom-right' }" @click="setPosition('bottom-right')">
                <span class="menu-icon">↘️</span>
                <span>أسفل يمين</span>
            </button>
            <button class="edit-fab-menu-item" :class="{ 'active': fabPosition === 'top-left' }" @click="setPosition('top-left')">
                <span class="menu-icon">↖️</span>
                <span>أعلى يسار</span>
            </button>
            <button class="edit-fab-menu-item" :class="{ 'active': fabPosition === 'top-right' }" @click="setPosition('top-right')">
                <span class="menu-icon">↗️</span>
                <span>أعلى يمين</span>
            </button>
            
            <div class="edit-fab-menu-divider"></div>
            
            <button class="edit-fab-menu-item" @click="toggleMinimize()">
                <span class="menu-icon" x-text="fabMinimized ? '👁️' : '👁️‍🗨️'"></span>
                <span x-text="fabMinimized ? 'إظهار دائم' : 'تصغير عند عدم الاستخدام'"></span>
            </button>
        </div>
        
        <!-- الزر الرئيسي -->
        <button @click="handleToggle()" 
                @contextmenu.prevent="menuOpen = !menuOpen"
                class="edit-toggle-btn" 
                :class="{ 'active': isEditMode }"
                :title="isEditMode ? 'إيقاف وضع التحرير (كليك يمين للإعدادات)' : 'تعديل الصفحة (كليك يمين للإعدادات)'">
            <span class="fab-icon" x-text="isEditMode ? '✕' : '✏️'"></span>
            <span class="edit-fab-badge" x-show="window.landingEditorInstance && Object.keys(window.landingEditorInstance.changes || {}).length > 0" x-text="Object.keys(window.landingEditorInstance?.changes || {}).length"></span>
        </button>
    </div>
    
    <!-- ======================================
         لوحة التحرير الجانبية الموحدة
         ====================================== -->
    <div class="editor-panel" 
         :class="{ 
            'open': isEditMode, 
            'collapsed': editorCollapsed,
            'position-left': editorPosition === 'left',
            'position-right': editorPosition === 'right'
         }"
         x-data="{
            editorCollapsed: localStorage.getItem('editor-collapsed') === 'true',
            editorPosition: localStorage.getItem('editor-position') || 'right',
            activeTab: 'tools',
            
            get isEditMode() {
                return window.landingEditorInstance?.editMode || false;
            },
            
            get changes() {
                return window.landingEditorInstance?.changes || {};
            },
            
            get saving() {
                return window.landingEditorInstance?.saving || false;
            },
            
            get lastSaved() {
                return window.landingEditorInstance?.lastSaved || null;
            },
            
            get selectedElement() {
                return window.landingEditorInstance?.selectedElement || null;
            },
            
            toggleCollapse() {
                this.editorCollapsed = !this.editorCollapsed;
                localStorage.setItem('editor-collapsed', this.editorCollapsed);
                // تحديث body class
                if (window.landingEditorInstance) {
                    window.landingEditorInstance.toggleEditorCollapse(this.editorCollapsed);
                }
            },
            
            switchPosition() {
                this.editorPosition = this.editorPosition === 'right' ? 'left' : 'right';
                localStorage.setItem('editor-position', this.editorPosition);
                // تحديث body class
                if (window.landingEditorInstance) {
                    window.landingEditorInstance.setEditorPosition(this.editorPosition);
                }
            }
         }">
        
        <!-- شريط العنوان -->
        <div class="editor-panel-header">
            <div class="editor-panel-title">
                <span class="title-icon">🎨</span>
                <span class="title-text">محرر الصفحة</span>
            </div>
            <div class="editor-panel-actions">
                <button @click="switchPosition()" class="panel-action-btn" title="تبديل الجهة">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3m8-18h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-3M12 8l4 4-4 4m-4-8l-4 4 4 4"/>
                    </svg>
                </button>
                <button @click="toggleCollapse()" class="panel-action-btn" title="تصغير/توسيع">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="{ 'rotate-180': editorCollapsed }">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- المحتوى -->
        <div class="editor-panel-content" x-show="!editorCollapsed">
            <!-- التبويبات -->
            <div class="editor-tabs">
                <button class="editor-tab" :class="{ 'active': activeTab === 'tools' }" @click="activeTab = 'tools'">
                    <span>🛠️</span> الأدوات
                </button>
                <button class="editor-tab" :class="{ 'active': activeTab === 'components' }" @click="activeTab = 'components'">
                    <span>📦</span> المكونات
                </button>
                <button class="editor-tab" :class="{ 'active': activeTab === 'properties' }" @click="activeTab = 'properties'">
                    <span>⚙️</span> الخصائص
                </button>
            </div>
            
            <!-- تبويب الأدوات -->
            <div class="editor-tab-content" x-show="activeTab === 'tools'">
                <div class="tools-section">
                    <!-- حالة الحفظ -->
                    <div class="save-status" :class="{ 'has-changes': Object.keys(changes).length > 0 }">
                        <span class="status-dot"></span>
                        <span x-text="Object.keys(changes).length > 0 ? Object.keys(changes).length + ' تغيير غير محفوظ' : 'لا توجد تغييرات'"></span>
                    </div>
                    
                    <!-- أزرار الإجراءات -->
                    <div class="tools-grid">
                        <button class="tool-btn primary" @click="window.landingEditorInstance?.saveChanges()" :disabled="saving || Object.keys(changes).length === 0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            <span x-text="saving ? 'جاري الحفظ...' : 'حفظ التغييرات'"></span>
                        </button>
                        
                        <button class="tool-btn" @click="window.landingEditorInstance?.createSnapshot()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                            <span>نسخة احتياطية</span>
                        </button>
                        
                        <button class="tool-btn warning" @click="window.landingEditorInstance?.cancelEdit()" x-show="Object.keys(changes).length > 0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                <path d="M3 3v5h5"/>
                            </svg>
                            <span>إلغاء التغييرات</span>
                        </button>
                    </div>
                    
                    <!-- آخر حفظ -->
                    <div class="last-saved" x-show="lastSaved">
                        <span>💾</span> آخر حفظ: <span x-text="lastSaved"></span>
                    </div>
                </div>
            </div>
            
            <!-- تبويب المكونات -->
            <div class="editor-tab-content" x-show="activeTab === 'components'">
                <div class="components-grid">
                    <div class="component-card" draggable="true" @dragstart="window.landingEditorInstance?.dragStart($event, 'hero')">
                        <span class="component-icon">🎯</span>
                        <span class="component-name">Hero Section</span>
                    </div>
                    
                    <div class="component-card" draggable="true" @dragstart="window.landingEditorInstance?.dragStart($event, 'feature-card')">
                        <span class="component-icon">⭐</span>
                        <span class="component-name">Feature Card</span>
                    </div>
                    
                    <div class="component-card" draggable="true" @dragstart="window.landingEditorInstance?.dragStart($event, 'cta')">
                        <span class="component-icon">🚀</span>
                        <span class="component-name">Call to Action</span>
                    </div>
                    
                    <div class="component-card" draggable="true" @dragstart="window.landingEditorInstance?.dragStart($event, 'stats')">
                        <span class="component-icon">📊</span>
                        <span class="component-name">إحصائيات</span>
                    </div>
                    
                    <div class="component-card" draggable="true" @dragstart="window.landingEditorInstance?.dragStart($event, 'testimonial')">
                        <span class="component-icon">💬</span>
                        <span class="component-name">شهادة عميل</span>
                    </div>
                    
                    <div class="component-card" draggable="true" @dragstart="window.landingEditorInstance?.dragStart($event, 'image-text')">
                        <span class="component-icon">🖼️</span>
                        <span class="component-name">صورة + نص</span>
                    </div>
                    
                    <div class="component-card" draggable="true" @dragstart="window.landingEditorInstance?.dragStart($event, 'pricing')">
                        <span class="component-icon">💰</span>
                        <span class="component-name">جدول أسعار</span>
                    </div>
                    
                    <div class="component-card" draggable="true" @dragstart="window.landingEditorInstance?.dragStart($event, 'faq')">
                        <span class="component-icon">❓</span>
                        <span class="component-name">أسئلة شائعة</span>
                    </div>
                </div>
                
                <p class="components-hint">💡 اسحب المكون وأفلته في الصفحة</p>
            </div>
            
            <!-- تبويب الخصائص -->
            <div class="editor-tab-content" x-show="activeTab === 'properties'">
                <div x-show="selectedElement" class="properties-form">
                    <div class="property-group">
                        <label>🎨 لون الخلفية</label>
                        <div class="color-input-wrapper">
                            <input type="color" @input="window.landingEditorInstance?.updateProperty('background', $event.target.value)">
                            <span class="color-value"></span>
                        </div>
                    </div>
                    
                    <div class="property-group">
                        <label>🖊️ لون النص</label>
                        <div class="color-input-wrapper">
                            <input type="color" @input="window.landingEditorInstance?.updateProperty('color', $event.target.value)">
                            <span class="color-value"></span>
                        </div>
                    </div>
                    
                    <div class="property-group">
                        <label>📐 المسافات الداخلية</label>
                        <select @change="window.landingEditorInstance?.updateProperty('padding', $event.target.value)" class="property-select">
                            <option value="">افتراضي</option>
                            <option value="20px">صغير</option>
                            <option value="40px">متوسط</option>
                            <option value="60px">كبير</option>
                            <option value="80px">كبير جداً</option>
                        </select>
                    </div>
                    
                    <div class="property-group">
                        <label>🔤 حجم الخط</label>
                        <select @change="window.landingEditorInstance?.updateProperty('fontSize', $event.target.value)" class="property-select">
                            <option value="">افتراضي</option>
                            <option value="14px">صغير</option>
                            <option value="16px">عادي</option>
                            <option value="18px">متوسط</option>
                            <option value="24px">كبير</option>
                            <option value="32px">كبير جداً</option>
                        </select>
                    </div>
                    
                    <div class="property-group">
                        <label>😀 الأيقونة</label>
                        <div class="icon-picker-grid">
                            <button @click="window.landingEditorInstance?.updateIcon('⭐')" class="icon-btn">⭐</button>
                            <button @click="window.landingEditorInstance?.updateIcon('🎯')" class="icon-btn">🎯</button>
                            <button @click="window.landingEditorInstance?.updateIcon('🚀')" class="icon-btn">🚀</button>
                            <button @click="window.landingEditorInstance?.updateIcon('💎')" class="icon-btn">💎</button>
                            <button @click="window.landingEditorInstance?.updateIcon('🏆')" class="icon-btn">🏆</button>
                            <button @click="window.landingEditorInstance?.updateIcon('📊')" class="icon-btn">📊</button>
                            <button @click="window.landingEditorInstance?.updateIcon('💡')" class="icon-btn">💡</button>
                            <button @click="window.landingEditorInstance?.updateIcon('🎨')" class="icon-btn">🎨</button>
                            <button @click="window.landingEditorInstance?.updateIcon('🔒')" class="icon-btn">🔒</button>
                            <button @click="window.landingEditorInstance?.updateIcon('✨')" class="icon-btn">✨</button>
                            <button @click="window.landingEditorInstance?.updateIcon('🌟')" class="icon-btn">🌟</button>
                            <button @click="window.landingEditorInstance?.updateIcon('💬')" class="icon-btn">💬</button>
                        </div>
                    </div>
                    
                    <button @click="window.landingEditorInstance?.closeProperties()" class="apply-btn">
                        ✅ تطبيق التغييرات
                    </button>
                </div>
                
                <div x-show="!selectedElement" class="no-selection">
                    <div class="no-selection-icon">👆</div>
                    <p>اضغط على أي عنصر في الصفحة لتعديل خصائصه</p>
                </div>
            </div>
        </div>
        
        <!-- زر التوسيع عند التصغير -->
        <div class="editor-collapsed-hint" x-show="editorCollapsed" @click="toggleCollapse()">
            <span>🎨</span>
        </div>
    </div>
    
    <!-- ✅ Edit Mode Logic تم نقله بالكامل إلى /public/js/landing-editor.js -->
    @endif
    @endauth
    
    <!-- تحميل المحتوى من قاعدة البيانات - محسّن -->
    @guest
    <script>
        // تحميل المحتوى المحفوظ للزوار فقط (السوبر أدمن يرى الصفحة الأصلية للتعديل)
        (function() {
            const cacheKey = 'landing_v1';
            const cached = localStorage.getItem(cacheKey);
            const cacheTime = localStorage.getItem(cacheKey + '_time');
            const now = Date.now();
            const maxAge = 5 * 60 * 1000; // 5 دقائق
            
            // استخدام الكاش إذا كان موجوداً وحديثاً
            if (cached && cacheTime && (now - parseInt(cacheTime)) < maxAge) {
                try {
                    const data = JSON.parse(cached);
                    applyContent(data);
                } catch(e) {}
                return;
            }
            
            // تحميل من API (مع فحص HTTP status)
            fetch('/api/landing/content')
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (data && data.success && data.content && Object.keys(data.content).length > 0) {
                        localStorage.setItem(cacheKey, JSON.stringify(data.content));
                        localStorage.setItem(cacheKey + '_time', now.toString());
                        applyContent(data.content);
                    }
                })
                .catch(err => console.warn('Landing content fetch failed:', err));
            
            function applyContent(content) {
                Object.entries(content).forEach(([key, value]) => {
                    const el = document.querySelector(`[data-editable="${key}"]`);
                    if (el) {
                        if (el.tagName === 'IMG') el.src = value;
                        else if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') el.value = value;
                        else el.textContent = value;
                    }
                });
            }
        })();
    </script>
    @endguest
    
    <!-- Lazy Loading + Performance Monitoring -->
    <script src="{{ asset('js/lazy-load.min.js') }}" defer></script>
    
    <!-- Service Worker Registration - Offline First ⚡ -->
    <script src="{{ asset('js/sw-register.js') }}" defer></script>
</body>
</html>
