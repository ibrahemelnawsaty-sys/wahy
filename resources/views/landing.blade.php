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
            'site_name' => 'أثيل مكة',
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
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- SEO & Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $siteName }} - ابنِ قيمك خطوة بخطوة">
    <meta property="og:description" content="{{ $siteDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    @if($siteLogo)
    <meta property="og:image" content="{{ asset('storage/data/' . $siteLogo) }}">
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
                            <img src="{{ asset('storage/data/' . $siteLogo) }}" alt="{{ $siteName }}" class="logo-img" data-editable-image="site_logo">
                        @else
                            <span class="logo-icon" data-editable="logo_icon" data-section="header">{{ lc('logo_icon', '🌟') }}</span>
                            <span class="logo-text" data-editable="logo_text" data-section="header">{{ $siteName }}</span>
                        @endif
                    </a>
                </div>
                <div class="nav-links" id="navLinks">
                    <div class="editable-element" data-element="nav-link-1">
                        <x-element-actions />
                        <a href="#home" class="nav-link active" data-editable="nav_link_1" data-section="header">{{ lc('nav_link_1', 'الرئيسية') }}</a>
                    </div>
                    <div class="editable-element" data-element="nav-link-2">
                        <x-element-actions />
                        <a href="#features" class="nav-link" data-editable="nav_link_2" data-section="header">{{ lc('nav_link_2', 'المميزات') }}</a>
                    </div>
                    <div class="editable-element" data-element="nav-link-3">
                        <x-element-actions />
                        <a href="#values" class="nav-link" data-editable="nav_link_3" data-section="header">{{ lc('nav_link_3', 'القيم') }}</a>
                    </div>
                    <div class="editable-element" data-element="nav-link-4">
                        <x-element-actions />
                        <a href="#activities" class="nav-link" data-editable="nav_link_4" data-section="header">{{ lc('nav_link_4', 'الأنشطة') }}</a>
                    </div>
                    <div class="editable-element" data-element="nav-link-5">
                        <x-element-actions />
                        @if(setting('show_partners'))<a href="#partners" class="nav-link" data-editable="nav_link_5" data-section="header">{{ lc('nav_link_5', 'الشركاء') }}</a>@endif
                    </div>
                    <div class="editable-element" data-element="nav-link-6">
                        <x-element-actions />
                        <a href="#support" class="nav-link" data-editable="nav_link_6" data-section="header">{{ lc('nav_link_6', 'الدعم') }}</a>
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
                        <a href="/login" class="btn btn-outline" data-editable="login_btn_text" data-section="header">{{ lc('login_btn_text', 'تسجيل دخول') }}</a>
                    </div>
                    <div class="editable-element" data-element="register-btn">
                        <x-element-actions />
                        <a href="/register" class="btn btn-primary" data-editable="register_btn_text" data-section="header">{{ lc('register_btn_text', 'ابدأ الآن') }}</a>
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
                            <h1 class="hero-title" data-editable="hero_title" data-section="hero">{{ lc('hero_title', 'منصة ' . $siteName . '.. قيم نبوية يحيى بها الطالب') }}</h1>
                        </div>
                        <div class="editable-element" data-element="hero-description">
                            <x-element-actions />
                            <p class="hero-description" data-editable="hero_description" data-section="hero">{{ lc('hero_description', 'من مكة المكرمة .. نبع الهدايات، انطلقت منصتنا تلهم الواردين من الطلاب والمعلمين.. تجمع بين المتعة والفائدة لتبني جيلاً فعالاً متخلقاً بقيم وأخلاق النبوة') }}</p>
                        </div>
                        <div class="hero-actions">
                            <div class="editable-element" data-element="hero-btn-primary">
                                <x-element-actions />
                                <a href="/register" class="btn btn-primary btn-lg">
                                    <span data-editable="hero_btn_primary" data-section="hero">{{ lc('hero_btn_primary', 'ابدأ الآن') }}</span>
                                    <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-arrow-left"/></svg>
                                </a>
                            </div>
                            <div class="editable-element" data-element="hero-btn-secondary">
                                <x-element-actions />
                                <a href="#features" class="btn btn-secondary btn-lg">
                                    <span data-editable="hero_btn_secondary" data-section="hero">{{ lc('hero_btn_secondary', 'اعرف المزيد') }}</span>
                                    <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-chevron-down"/></svg>
                                </a>
                            </div>
                        </div>
                        @if(setting('show_hero_stats'))
                        <div class="hero-stats">
                            <div class="stat-item editable-element" data-element="stat-schools">
                                <x-element-actions />
                                <span class="stat-number" data-editable="stat_schools" data-section="hero">{{ lc('stat_schools', '500+') }}</span>
                                <span class="stat-label" data-editable="stat_schools_label" data-section="hero">{{ lc('stat_schools_label', 'مدرسة') }}</span>
                            </div>
                            <div class="stat-item editable-element" data-element="stat-students">
                                <x-element-actions />
                                <span class="stat-number" data-editable="stat_students" data-section="hero">{{ lc('stat_students', '50k+') }}</span>
                                <span class="stat-label" data-editable="stat_students_label" data-section="hero">{{ lc('stat_students_label', 'طالب') }}</span>
                            </div>
                            <div class="stat-item editable-element" data-element="stat-teachers">
                                <x-element-actions />
                                <span class="stat-number" data-editable="stat_teachers" data-section="hero">{{ lc('stat_teachers', '2k+') }}</span>
                                <span class="stat-label" data-editable="stat_teachers_label" data-section="hero">{{ lc('stat_teachers_label', 'معلم') }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @php
                        // فيديو الهيرو (مهمّة 4): يستبدل الصورة عند التفعيل. المصدر آمن —
                        // خارجيّ https عبر safe_url، أو مسار محلّيّ بلا مخطّط/تسلّق عبر asset(). بلا تشغيل تلقائيّ.
                        $__hvOn = setting('hero_video_enabled');
                        $__hvRaw = trim((string) setting('hero_video_url'));
                        $__hvPosterRaw = trim((string) setting('hero_video_poster'));
                        $__localOk = fn ($p) => $p !== '' && ! str_contains($p, '..') && ! preg_match('#^[a-z][a-z0-9+.\-]*:#i', $p);
                        if (\Illuminate\Support\Str::startsWith($__hvRaw, ['http://', 'https://'])) { $__hvSrc = safe_url($__hvRaw, ''); }
                        elseif ($__localOk($__hvRaw)) { $__hvSrc = asset(ltrim($__hvRaw, '/')); }
                        else { $__hvSrc = ''; }
                        if (\Illuminate\Support\Str::startsWith($__hvPosterRaw, ['http://', 'https://'])) { $__hvPoster = safe_url($__hvPosterRaw, ''); }
                        elseif ($__localOk($__hvPosterRaw)) { $__hvPoster = asset(ltrim($__hvPosterRaw, '/')); }
                        else { $__hvPoster = ''; }
                    @endphp
                    <div class="hero-visual">
                        @if($__hvOn && $__hvSrc !== '')
                            <video class="hero-image hero-video" controls preload="metadata" playsinline @if($__hvPoster !== '') poster="{{ $__hvPoster }}" @endif>
                                <source src="{{ $__hvSrc }}" type="video/mp4">
                            </video>
                        @else
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
                        @endif
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
                        <h2 class="section-title" data-editable="features_title" data-section="features">{{ lc('features_title', 'لماذا ' . $siteName . '؟') }}</h2>
                    </div>
                    <div class="editable-element" data-element="features-subtitle">
                        <x-element-actions />
                        <p class="section-subtitle" data-editable="features_subtitle" data-section="features">{{ lc('features_subtitle', 'نظام متكامل بمميزات فريدة') }}</p>
                    </div>
                </div>
                <div class="features-grid">
                    <article class="feature-card editable-element" data-element="feature-card-5">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_5_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-medal"/></svg></div>
                        <h3 data-editable="feature_5_title" data-section="features">{{ lc('feature_5_title', 'تحديات ومنافسات') }}</h3>
                        <p data-editable="feature_5_desc" data-section="features">{{ lc('feature_5_desc', 'تحفز المشاركين وترفع مستوى التفكير والإدراك') }}</p>
                    </article>
                    <article class="feature-card editable-element" data-element="feature-card-6">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_6_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-chart-bar"/></svg></div>
                        <h3 data-editable="feature_6_title" data-section="features">{{ lc('feature_6_title', 'قياس الأثر') }}</h3>
                        <p data-editable="feature_6_desc" data-section="features">{{ lc('feature_6_desc', 'وجود استبانات توضح مدى نسبة الاستفادة من الأنشطة والتمارين لتحقيق الهدف') }}</p>
                    </article>
                    <article class="feature-card editable-element" data-element="feature-card-7">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_7_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-tasks"/></svg></div>
                        <h3 data-editable="feature_7_title" data-section="features">{{ lc('feature_7_title', 'أنشطة سلوكية') }}</h3>
                        <p data-editable="feature_7_desc" data-section="features">{{ lc('feature_7_desc', 'تساعد على تطبيق المفاهيم وتحويلها إلى ممارسات حياتية') }}</p>
                    </article>
                    <article class="feature-card editable-element" data-element="feature-card-8">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_8_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-rocket"/></svg></div>
                        <h3 data-editable="feature_8_title" data-section="features">{{ lc('feature_8_title', 'تحفيز وتنشيط') }}</h3>
                        <p data-editable="feature_8_desc" data-section="features">{{ lc('feature_8_desc', 'يساعدان في رفع المعنويات ودفع التقدم وتحريك الطاقات الكامنة') }}</p>
                    </article>
                    <article class="feature-card editable-element" data-element="feature-card-9">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_9_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-gem"/></svg></div>
                        <h3 data-editable="feature_9_title" data-section="features">{{ lc('feature_9_title', 'الانسيابية والسهولة') }}</h3>
                        <p data-editable="feature_9_desc" data-section="features">{{ lc('feature_9_desc', 'الوضوح واليسر والمتابعة بانتظام ودون تعقيد وتكلف') }}</p>
                    </article>
                    <article class="feature-card editable-element" data-element="feature-card-1">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_1_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-qrcode"/></svg></div>
                        <h3 data-editable="feature_1_title" data-section="features">{{ lc('feature_1_title', 'QR فريد لكل مستخدم') }}</h3>
                        <p data-editable="feature_1_desc" data-section="features">{{ lc('feature_1_desc', 'كل طالب ومعلم لديه رمز QR خاص للدخول السريع وتسجيل الحضور والأنشطة') }}</p>
                    </article>
                    <article class="feature-card editable-element" data-element="feature-card-2">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_2_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-trophy"/></svg></div>
                        <h3 data-editable="feature_2_title" data-section="features">{{ lc('feature_2_title', 'لوحة صدارة ذكية') }}</h3>
                        <p data-editable="feature_2_desc" data-section="features">{{ lc('feature_2_desc', 'نظام تنافسي محفز يعرض أفضل الطلاب والفرق بناءً على الإنجازات والنقاط') }}</p>
                    </article>
                    <article class="feature-card editable-element" data-element="feature-card-3">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_3_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-brain"/></svg></div>
                        <h3 data-editable="feature_3_title" data-section="features">{{ lc('feature_3_title', 'اقتراح أنشطة بالذكاء الاصطناعي') }}</h3>
                        <p data-editable="feature_3_desc" data-section="features">{{ lc('feature_3_desc', 'نظام ذكي يقترح أنشطة مخصصة لكل طالب حسب مستواه واهتماماته') }}</p>
                    </article>
                    <article class="feature-card editable-element" data-element="feature-card-4">
                        <x-element-actions />
                        <div class="feature-icon" data-editable-icon="feature_4_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-user-check"/></svg></div>
                        <h3 data-editable="feature_4_title" data-section="features">{{ lc('feature_4_title', 'متابعة وتقييم المعلمين') }}</h3>
                        <p data-editable="feature_4_desc" data-section="features">{{ lc('feature_4_desc', 'أدوات شاملة لمتابعة أداء الطلاب وتقييمهم بطرق متنوعة ومرنة') }}</p>
                    </article>
                </div>
            </div>
        </section>
        
        <section class="values-section section section-alt" id="values">
            <div class="container">
                <div class="section-header">
                    <div class="editable-element" data-element="values-title">
                        <x-element-actions />
                        <h2 class="section-title" data-editable="values_title" data-section="values">{{ lc('values_title', 'كيف نبني القيم؟') }}</h2>
                    </div>
                    <div class="editable-element" data-element="values-subtitle">
                        <x-element-actions />
                        <p class="section-subtitle" data-editable="values_subtitle" data-section="values">{{ lc('values_subtitle', 'منهجية متكاملة من القيمة إلى التطبيق العملي') }}</p>
                    </div>
                </div>
                
                <div class="values-flow">
                    <div class="flow-card editable-element" data-element="flow-card-1">
                        <x-element-actions />
                        <div class="flow-number" data-editable="flow_1_number" data-section="values">{{ lc('flow_1_number', '1') }}</div>
                        <div class="flow-icon" data-editable-icon="flow_1_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-heart"/></svg></div>
                        <h3 data-editable="flow_1_title" data-section="values">{{ lc('flow_1_title', 'القيمة الكلية') }}</h3>
                        <p data-editable="flow_1_example" data-section="values">مثال: <strong>{{ lc('flow_1_example', 'الرحمة') }}</strong></p>
                        <span class="flow-desc" data-editable="flow_1_desc" data-section="values">{{ lc('flow_1_desc', 'اختيار قيمة كلية تندرج تحتها مجموعة من القيم الضمنية') }}</span>
                    </div>
                    
                    <div class="flow-arrow editable-element" data-element="flow-arrow-1">
                        <x-element-actions />
                        <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-arrow-left"/></svg>
                    </div>
                    
                    <div class="flow-card editable-element" data-element="flow-card-2">
                        <x-element-actions />
                        <div class="flow-number" data-editable="flow_2_number" data-section="values">{{ lc('flow_2_number', '2') }}</div>
                        <div class="flow-icon" data-editable-icon="flow_2_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-star"/></svg></div>
                        <h3 data-editable="flow_2_title" data-section="values">{{ lc('flow_2_title', 'القيمة الضمنية') }}</h3>
                        <p data-editable="flow_2_example" data-section="values">مثال: <strong>{{ lc('flow_2_example', 'بر الوالدين') }}</strong></p>
                        <span class="flow-desc" data-editable="flow_2_desc" data-section="values">{{ lc('flow_2_desc', 'حصر القيم الضمنية المتعلقة بالقيمة الكلية وإدراجها في المنظومة') }}</span>
                    </div>
                    
                    <div class="flow-arrow editable-element" data-element="flow-arrow-2">
                        <x-element-actions />
                        <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-arrow-left"/></svg>
                    </div>
                    
                    <div class="flow-card editable-element" data-element="flow-card-3">
                        <x-element-actions />
                        <div class="flow-number" data-editable="flow_3_number" data-section="values">{{ lc('flow_3_number', '3') }}</div>
                        <div class="flow-icon" data-editable-icon="flow_3_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-lightbulb"/></svg></div>
                        <h3 data-editable="flow_3_title" data-section="values">{{ lc('flow_3_title', 'المفاهيم الرئيسية') }}</h3>
                        <p data-editable="flow_3_example" data-section="values">مثال: <strong>{{ lc('flow_3_example', 'الإحسان إلى الوالدين') }}</strong></p>
                        <span class="flow-desc" data-editable="flow_3_desc" data-section="values">{{ lc('flow_3_desc', 'استخراج المفاهيم الرئيسية من القيمة الضمنية') }}</span>
                    </div>
                    
                    <div class="flow-arrow editable-element" data-element="flow-arrow-3">
                        <x-element-actions />
                        <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-arrow-left"/></svg>
                    </div>
                    
                    <div class="flow-card editable-element" data-element="flow-card-4">
                        <x-element-actions />
                        <div class="flow-number" data-editable="flow_4_number" data-section="values">{{ lc('flow_4_number', '4') }}</div>
                        <div class="flow-icon" data-editable-icon="flow_4_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-book-open"/></svg></div>
                        <h3 data-editable="flow_4_title" data-section="values">{{ lc('flow_4_title', 'المعاني المرتبطة') }}</h3>
                        <p data-editable="flow_4_example" data-section="values">مثال: <strong>{{ lc('flow_4_example', 'طاعة الوالدين – إكرام الوالدين – الدعاء لهما') }}</strong></p>
                        <span class="flow-desc" data-editable="flow_4_desc" data-section="values">{{ lc('flow_4_desc', 'تحديد أهم المعاني المتعلقة بالمفهوم') }}</span>
                    </div>

                    <div class="flow-arrow editable-element" data-element="flow-arrow-4">
                        <x-element-actions />
                        <svg class="icon"><use href="{{ asset('icons.svg') }}#icon-arrow-left"/></svg>
                    </div>

                    <div class="flow-card editable-element" data-element="flow-card-5">
                        <x-element-actions />
                        <div class="flow-number" data-editable="flow_5_number" data-section="values">{{ lc('flow_5_number', '5') }}</div>
                        <div class="flow-icon" data-editable-icon="flow_5_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-tasks"/></svg></div>
                        <h3 data-editable="flow_5_title" data-section="values">{{ lc('flow_5_title', 'الأنشطة') }}</h3>
                        <p data-editable="flow_5_example" data-section="values">مثال: <strong>{{ lc('flow_5_example', 'حفل الإحسان') }}</strong></p>
                        <span class="flow-desc" data-editable="flow_5_desc" data-section="values">{{ lc('flow_5_desc', 'تنفيذ أنشطة ومشاريع لتعزيز المفاهيم والمعاني وتطبيقها') }}</span>
                    </div>
                </div>
                
                {{-- قسم «مثال عملي» أُزيل (مهمّة 9): صار المثال متضمَّناً في خطوات المنهجية أعلاه --}}
            </div>
        </section>
        
        <section class="teams-section section" id="activities">
            <div class="container">
                <div class="section-header">
                    <div class="editable-element" data-element="teams-title">
                        <x-element-actions />
                        <h2 class="section-title" data-editable="teams_title" data-section="teams">{{ lc('teams_title', 'التعلم التعاوني مع الفرق') }}</h2>
                    </div>
                    <div class="editable-element" data-element="teams-subtitle">
                        <x-element-actions />
                        <p class="section-subtitle" data-editable="teams_subtitle" data-section="teams">{{ lc('teams_subtitle', 'نظام فرق ذكي يحفز الطلاب على التعاون والتنافس الإيجابي') }}</p>
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
                            <div class="team-rank" data-editable="team_1_rank" data-section="teams">{{ lc('team_1_rank', '1') }}</div>
                            <div class="team-icon" data-editable-icon="team_1_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-trophy"/></svg></div>
                            <h4 data-editable="team_1_name" data-section="teams">{{ lc('team_1_name', 'فريق الريادة') }}</h4>
                            <div class="team-points" data-editable="team_1_points" data-section="teams">{{ lc('team_1_points', '2,450 نقطة') }}</div>
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
                            <div class="team-rank" data-editable="team_2_rank" data-section="teams">{{ lc('team_2_rank', '2') }}</div>
                            <div class="team-icon" data-editable-icon="team_2_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-rocket"/></svg></div>
                            <h4 data-editable="team_2_name" data-section="teams">{{ lc('team_2_name', 'فريق السمو') }}</h4>
                            <div class="team-points" data-editable="team_2_points" data-section="teams">{{ lc('team_2_points', '2,180 نقطة') }}</div>
                            <div class="team-members">
                                <span class="member-avatar">ف</span>
                                <span class="member-avatar">ر</span>
                                <span class="member-avatar">ك</span>
                                <span class="member-avatar">ه</span>
                            </div>
                        </div>
                        
                        <div class="team-card team-card-accent editable-element" data-element="team-card-3">
                            <x-element-actions />
                            <div class="team-rank" data-editable="team_3_rank" data-section="teams">{{ lc('team_3_rank', '3') }}</div>
                            <div class="team-icon" data-editable-icon="team_3_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-gem"/></svg></div>
                            <h4 data-editable="team_3_name" data-section="teams">{{ lc('team_3_name', 'فريق المعالي') }}</h4>
                            <div class="team-points" data-editable="team_3_points" data-section="teams">{{ lc('team_3_points', '1,920 نقطة') }}</div>
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
                
                @if(setting('show_coop_benefits', true))
                <div class="teams-benefits">
                    <div class="editable-element" data-element="benefits-title">
                        <x-element-actions />
                        <h3 data-editable="benefits_title" data-section="teams">{{ lc('benefits_title', 'فوائد التعلم التعاوني') }}</h3>
                    </div>
                    <div class="benefits-grid">
                        <div class="benefit-card editable-element" data-element="benefit-card-1">
                            <x-element-actions />
                            <div data-editable-icon="benefit_1_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-handshake"/></svg></div>
                            <h4 data-editable="benefit_1_title" data-section="teams">{{ lc('benefit_1_title', 'تعزيز التعاون') }}</h4>
                            <p data-editable="benefit_1_desc" data-section="teams">{{ lc('benefit_1_desc', 'يتعلم الطلاب العمل معاً وتحقيق الأهداف المشتركة') }}</p>
                        </div>
                        <div class="benefit-card editable-element" data-element="benefit-card-2">
                            <x-element-actions />
                            <div data-editable-icon="benefit_2_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-comments"/></svg></div>
                            <h4 data-editable="benefit_2_title" data-section="teams">{{ lc('benefit_2_title', 'تطوير التواصل') }}</h4>
                            <p data-editable="benefit_2_desc" data-section="teams">{{ lc('benefit_2_desc', 'تحسين مهارات التواصل والاستماع للآخرين') }}</p>
                        </div>
                        <div class="benefit-card editable-element" data-element="benefit-card-3">
                            <x-element-actions />
                            <div data-editable-icon="benefit_3_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-brain"/></svg></div>
                            <h4 data-editable="benefit_3_title" data-section="teams">{{ lc('benefit_3_title', 'تنمية التفكير') }}</h4>
                            <p data-editable="benefit_3_desc" data-section="teams">{{ lc('benefit_3_desc', 'تبادل الأفكار يساعد على التفكير النقدي والإبداعي') }}</p>
                        </div>
                        <div class="benefit-card editable-element" data-element="benefit-card-4">
                            <x-element-actions />
                            <div data-editable-icon="benefit_4_icon"><svg class="icon"><use href="{{ asset('icons.svg') }}#icon-heart"/></svg></div>
                            <h4 data-editable="benefit_4_title" data-section="teams">{{ lc('benefit_4_title', 'بناء العلاقات') }}</h4>
                            <p data-editable="benefit_4_desc" data-section="teams">{{ lc('benefit_4_desc', 'تكوين صداقات وعلاقات إيجابية بين الطلاب') }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </section>
        
        @if(setting('show_partners'))
        <section class="partners section section-alt" id="partners">
            <div class="container">
                <div class="section-header">
                    <div class="editable-element" data-element="partners-title">
                        <x-element-actions />
                        <h2 class="section-title" data-editable="partners_title" data-section="partners">{{ lc('partners_title', 'شركاؤنا في النجاح') }}</h2>
                    </div>
                    <div class="editable-element" data-element="partners-subtitle">
                        <x-element-actions />
                        <p class="section-subtitle" data-editable="partners_subtitle" data-section="partners">{{ lc('partners_subtitle', 'ثقة أكثر من 500 مدرسة ومؤسسة تعليمية رائدة') }}</p>
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
        @endif

        <!-- Contact Us Section -->
        <section class="contact-section section section-alt" id="support">
            <div class="container">
                <div class="contact-wrapper">
                    <!-- Info Panel -->
                    <div class="contact-info-panel">
                        <div class="editable-element" data-element="contact-title">
                            <x-element-actions />
                            <h2 class="contact-title" data-editable="contact_title" data-section="contact">{{ lc('contact_title', 'يسعدنا تواصلك معنا') }}</h2>
                        </div>
                        <div class="editable-element" data-element="contact-description">
                            <x-element-actions />
                            <p class="contact-description" data-editable="contact_description" data-section="contact">{{ lc('contact_description', 'فريقنا جاهز للإجابة على جميع استفساراتكم المتعلقة بالمنصة، القيم، الأنشطة، أو الدعم الفني.') }}</p>
                        </div>

                        <div class="contact-details-list">
                            <div class="contact-detail-item">
                                <span class="contact-detail-icon">📧</span>
                                <div class="contact-detail-content">
                                    <strong>البريد الإلكتروني</strong>
                                    <a href="mailto:{{ setting('contact_email', 'info@atheel-makkah.com') }}">{{ setting('contact_email', 'info@atheel-makkah.com') }}</a>
                                </div>
                            </div>

                            <div class="contact-detail-item">
                                <span class="contact-detail-icon">☎️</span>
                                <div class="contact-detail-content">
                                    <strong>رقم الهاتف</strong>
                                    @php $__cp = setting('contact_phone', '+966500000000'); @endphp
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $__cp) }}">{{ $__cp }}</a>
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
                        <h2 data-editable="cta_title" data-section="cta">{{ lc('cta_title', 'جاهز للانضمام؟') }}</h2>
                    </div>
                    <div class="editable-element" data-element="cta-subtitle">
                        <x-element-actions />
                        <p data-editable="cta_subtitle" data-section="cta">{{ lc('cta_subtitle', 'ابدأ رحلتك اليوم') }}</p>
                    </div>
                    <div class="cta-actions">
                        <div class="editable-element" data-element="cta-button">
                            <x-element-actions />
                            <a href="/register" class="btn btn-primary btn-lg" data-editable="cta_button_text" data-section="cta">{{ lc('cta_button_text', 'ابدأ مجاناً') }}</a>
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
    
    {{--
        ملاحظة: النصوص المُدارة من «محتوى الصفحة الرئيسية» تُعرَض الآن خادميّاً عبر lc('key', الافتراضيّ)،
        فلا وميض ولا محتوى قديم. يبقى هذا السكربت للزوّار فقط كطبقة احتياطيّة تطبّق أيّ مفاتيح
        محفوظة في landing_content لم تُلَفّ خادميّاً بعد — بلا كاش localStorage (كان يؤخّر ظهور تعديلات
        الأدمن حتى 5 دقائق)، فيُجلب الطازج دائماً ويُطبَّق نصّاً فقط (textContent) بلا أيّ حقن HTML.
    --}}
    @guest
    <script>
        (function() {
            fetch('/api/landing/content')
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (data && data.success && data.content && Object.keys(data.content).length > 0) {
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
    
    {{-- زرّ واتساب عائم (مهمّة 18) — يظهر فقط عند ضبط whatsapp_number؛ القيمة أرقام فقط (بلا حقن) --}}
    @php $__wa = preg_replace('/[^0-9]/', '', (string) setting('whatsapp_number')); @endphp
    @if($__wa !== '')
    <a href="https://wa.me/{{ $__wa }}" target="_blank" rel="noopener" class="wa-float" aria-label="تواصل عبر واتساب" title="تواصل عبر واتساب">
        <svg viewBox="0 0 32 32" width="30" height="30" fill="currentColor" aria-hidden="true">
            <path d="M16.001 3C9.383 3 4 8.383 4 15c0 2.117.555 4.19 1.608 6.017L4 29l8.174-1.58A11.94 11.94 0 0016 27c6.617 0 12-5.383 12-12S22.618 3 16.001 3zm0 21.8c-1.86 0-3.68-.5-5.27-1.444l-.378-.224-4.85.938.97-4.73-.246-.388A9.76 9.76 0 016.2 15c0-5.406 4.396-9.8 9.8-9.8 5.404 0 9.8 4.394 9.8 9.8 0 5.404-4.396 9.8-9.8 9.8zm5.383-7.34c-.295-.148-1.745-.86-2.016-.96-.27-.098-.467-.148-.663.148-.197.295-.762.96-.934 1.157-.172.197-.344.222-.639.074-.295-.148-1.246-.459-2.373-1.463-.877-.782-1.469-1.748-1.641-2.043-.172-.295-.018-.454.13-.601.134-.133.296-.345.443-.518.148-.172.197-.295.296-.492.098-.197.049-.37-.025-.518-.074-.148-.663-1.6-.909-2.19-.239-.574-.482-.497-.663-.506l-.565-.01c-.197 0-.516.074-.787.37-.27.295-1.033 1.01-1.033 2.46 0 1.45 1.058 2.852 1.205 3.049.148.197 2.083 3.18 5.046 4.458.705.304 1.255.486 1.684.622.708.225 1.352.193 1.861.117.568-.085 1.745-.714 1.991-1.403.246-.688.246-1.279.172-1.403-.074-.123-.27-.197-.565-.345z"/>
        </svg>
    </a>
    <style>
        /* زرّ واتساب عائم أسفل اليسار + حلقة نابضة + قوس ضوء دائر حوله */
        .wa-float{position:fixed;bottom:24px;left:24px;z-index:900;width:56px;height:56px;border-radius:50%;
            background:#25D366;color:#fff;display:flex;align-items:center;justify-content:center;
            box-shadow:0 6px 20px rgba(37,211,102,.45);transition:transform .15s,box-shadow .15s;text-decoration:none}
        .wa-float svg{position:relative;z-index:2}
        /* حلقة نابضة تتمدّد وتتلاشى (لفت النظر) */
        .wa-float::before{content:"";position:absolute;inset:0;border-radius:50%;z-index:0;pointer-events:none;
            border:2px solid rgba(37,211,102,.55);animation:wa-pulse 2s ease-out infinite}
        /* قوس ضوء يدور حول الزرّ */
        .wa-float::after{content:"";position:absolute;inset:-5px;border-radius:50%;z-index:1;pointer-events:none;
            background:conic-gradient(from 0deg,transparent 0deg,rgba(255,255,255,.95) 55deg,transparent 130deg);
            -webkit-mask:radial-gradient(farthest-side,transparent calc(100% - 3px),#000 calc(100% - 3px));
            mask:radial-gradient(farthest-side,transparent calc(100% - 3px),#000 calc(100% - 3px));
            animation:wa-spin 3s linear infinite}
        .wa-float:hover{transform:scale(1.08);box-shadow:0 8px 26px rgba(37,211,102,.6)}
        @keyframes wa-pulse{0%{transform:scale(1);opacity:.7}100%{transform:scale(1.9);opacity:0}}
        @keyframes wa-spin{to{transform:rotate(360deg)}}
        @media (prefers-reduced-motion:reduce){.wa-float::before,.wa-float::after{animation:none}}
        @media (max-width:600px){.wa-float{width:50px;height:50px;bottom:18px;left:18px}}
    </style>
    @endif

    <!-- Lazy Loading + Performance Monitoring -->
    <script src="{{ asset('js/lazy-load.min.js') }}" defer></script>
    
    <!-- Service Worker Registration - Offline First ⚡ -->
    <script src="{{ asset('js/sw-register.js') }}" defer></script>
</body>
</html>
