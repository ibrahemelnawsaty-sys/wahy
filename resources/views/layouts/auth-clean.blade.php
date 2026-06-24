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
        @font-face{font-family:'IBM Plex Sans Arabic';src:url('/FONT/IBMPlexSansArabic-Regular.ttf')format('truetype');font-weight:400;font-display:swap}@font-face{font-family:'IBM Plex Sans Arabic';src:url('/FONT/IBMPlexSansArabic-Bold.ttf')format('truetype');font-weight:700;font-display:swap}*{margin:0;padding:0;box-sizing:border-box}body{font-family:'IBM Plex Sans Arabic',sans-serif;background:#0F172A;color:#F1F5F9;min-height:100vh}.header{position:sticky;top:0;background:rgba(15,23,42,0.8);backdrop-filter:blur(15px);z-index:1000;border-bottom:1px solid rgba(255,255,255,0.1)}.container{max-width:1280px;margin:0 auto;padding:0 24px}.navbar{display:flex;align-items:center;justify-content:space-between;padding:16px 0}.logo{display:flex;align-items:center;gap:12px;font-weight:700;font-size:24px;color:#F1F5F9;text-decoration:none}.btn{display:inline-flex;align-items:center;gap:12px;padding:12px 24px;border-radius:8px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all .3s}.btn-primary{background:linear-gradient(135deg,#10B981,#3B82F6);color:#fff}.btn-outline{background:rgba(255,255,255,0.05);color:#CBD5E1;border:2px solid rgba(16,185,129,0.3)}
    </style>
    
    <!-- Async CSS للباقي - تحميل غير متزامن لتحسين الأداء -->
    <link rel="preload" href="{{ asset('css/auth-glass.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="{{ asset('css/auth-enhancements.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('css/auth-glass.css') }}">
        <link rel="stylesheet" href="{{ asset('css/auth-enhancements.css') }}">
    </noscript>
    
    <script src="{{ asset('js/theme.min.js') }}" defer></script>
    @yield('extra_css')
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar" role="navigation">
                <a href="/" class="logo">
                    <span class="logo-icon">🌟</span>
                    <span class="logo-text">قيمّ</span>
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

    @yield('content')
    
    @include('components.footer')

    @stack('scripts')
</body>
</html>
