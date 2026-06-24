@php
    // جلب إعدادات الثيم من قاعدة البيانات - batch loading لتحسين الأداء
    $settings = \App\Models\Setting::getMany(
        ['font_family', 'primary_color', 'secondary_color', 'text_color', 'background_color', 'site_logo', 'site_name'],
        [
            'font_family' => 'IBM Plex Sans Arabic',
            'primary_color' => '#3CCB8A',
            'secondary_color' => '#3B82F6',
            'text_color' => '#1e293b',
            'background_color' => '#f8fafc',
            'site_logo' => null,
            'site_name' => 'قيمّ'
        ]
    );
    $fontFamily = $settings['font_family'] ?? 'IBM Plex Sans Arabic';
    $primaryColor = $settings['primary_color'] ?? '#3CCB8A';
    $secondaryColor = $settings['secondary_color'] ?? '#3B82F6';
    $textColor = $settings['text_color'] ?? '#1e293b';
    $backgroundColor = $settings['background_color'] ?? '#f8fafc';
    $siteLogo = $settings['site_logo'] ?? null;
    $siteName = $settings['site_name'] ?? 'قيمّ';
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'قيمّ - منصة تعليمية رائدة')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'قيمّ')</title>
    
    <!-- Preload Critical Fonts -->
    <link rel="preload" href="{{ asset('FONT/IBMPlexSansArabic-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="{{ asset('FONT/IBMPlexSansArabic-Bold.ttf') }}" as="font" type="font/ttf" crossorigin>
    
    <!-- Critical CSS Inline للتحميل الفوري ⚡ -->
    <style>
        @font-face{font-family:'IBM Plex Sans Arabic';src:url('/FONT/IBMPlexSansArabic-Regular.ttf')format('truetype');font-weight:400;font-display:swap}@font-face{font-family:'IBM Plex Sans Arabic';src:url('/FONT/IBMPlexSansArabic-Bold.ttf')format('truetype');font-weight:700;font-display:swap}*{margin:0;padding:0;box-sizing:border-box}body{font-family:'IBM Plex Sans Arabic',sans-serif}
        /* إخفاء skip-link - يظهر فقط عند التنقل بالكيبورد (Accessibility) */
        .skip-link{position:absolute;top:-100px;right:0;z-index:9999;background:#3CCB8A;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;transition:top 0.3s}.skip-link:focus{top:10px;outline:2px solid #fff;outline-offset:2px}
    </style>
    
    <!-- Async CSS للباقي - تحميل غير متزامن لتحسين الأداء -->
    <link rel="preload" href="{{ asset('css/auth-glass.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="{{ asset('css/auth.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('css/auth-glass.css') }}">
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    </noscript>
    
    <!-- تم استبدال Font Awesome بأيقونات SVG مباشرة في الصفحات -->
    @yield('extra_css')
    
    <!-- CSS ديناميكي من قاعدة البيانات -->
    <style>
        :root {
            /* الألوان الرئيسية */
            --color-primary: {{ $primaryColor }};
            --color-primary-hover: {{ adjustBrightness($primaryColor, -20) }};
            --color-primary-light: {{ hexToRgba($primaryColor, 0.1) }};
            
            /* الألوان الثانوية */
            --color-secondary: {{ $secondaryColor }};
            --color-secondary-hover: {{ adjustBrightness($secondaryColor, -20) }};
            
            /* ألوان النصوص والخلفيات */
            --color-text: {{ $textColor }};
            --color-bg: {{ $backgroundColor }};
            
            /* الخط */
            --font-family-base: '{{ $fontFamily }}', sans-serif;
        }
        
        body {
            font-family: var(--font-family-base);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Skip Link - مخفي، يظهر فقط عند التنقل بالكيبورد -->
    <a href="#main-content" class="skip-link">الانتقال إلى المحتوى الرئيسي</a>
    
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar" role="navigation">
                <a href="/" class="logo">
                    @if($siteLogo)
                        <img src="{{ asset('storage/app/public/data/' . $siteLogo) }}" alt="{{ $siteName }}" style="max-height: 40px;">
                    @else
                        <span class="logo-icon">🌟</span>
                        <span class="logo-text">{{ $siteName }}</span>
                    @endif
                </a>
                <div class="nav-links" id="navLinks">
                    <a href="/" class="nav-link">الرئيسية</a>
                    <a href="/#features" class="nav-link">المميزات</a>
                    <a href="/#values" class="nav-link">القيم</a>
                    <a href="/#activities" class="nav-link">الأنشطة</a>
                    <a href="/#partners" class="nav-link">الشركاء</a>
                    <a href="/#support" class="nav-link">الدعم</a>
                </div>
                <div class="nav-actions">
                    <!-- Theme Toggle Button -->
                    <button class="theme-toggle" id="themeToggle" aria-label="تبديل الوضع">
                        <span class="icon-sun">☀️</span>
                        <span class="icon-moon">🌙</span>
                    </button>
                    <a href="/login" class="btn btn-outline">تسجيل دخول</a>
                    <a href="/register" class="btn btn-primary">ابدأ الآن</a>
                </div>
                <button class="menu-toggle" aria-label="فتح القائمة" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main id="main-content" class="auth-main">
        @yield('content')
    </main>

    @include('components.footer')

    <script src="{{ asset('js/theme.min.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
