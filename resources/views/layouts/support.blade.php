@php
    // جلب إعدادات الثيم من قاعدة البيانات (نظير layouts/admin)
    $fontFamily = setting('font_family', 'IBM Plex Sans Arabic');
    $primaryColor = setting('primary_color', '#667eea');
    $secondaryColor = setting('secondary_color', '#764ba2');
    $textColor = setting('text_color', '#1e293b');
    $backgroundColor = setting('background_color', '#ffffff');
    $siteTheme = setting('site_theme', 'dark');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="{{ $siteTheme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.head-meta')
    <title>@yield('title', 'لوحة الدعم الفنيّ') - {{ $branding['site_name'] ?? 'قيمّ' }}</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- الخطوط من Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if($fontFamily === 'IBM Plex Sans Arabic')
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @elseif($fontFamily === 'Cairo')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    @elseif($fontFamily === 'Tajawal')
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    @elseif($fontFamily === 'Almarai')
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">
    @endif

    <!-- ملف CSS الأساسي (نفس أصناف الأدمن: admin-layout / admin-sidebar / admin-stat-card ...) -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    <!-- CSS ديناميكي من إعدادات الثيم -->
    <style>
        :root {
            --color-primary: {{ $primaryColor }};
            --color-primary-hover: {{ adjustBrightness($primaryColor, -20) }};
            --color-primary-light: {{ hexToRgba($primaryColor, 0.1) }};
            --color-secondary: {{ $secondaryColor }};
            --color-secondary-hover: {{ adjustBrightness($secondaryColor, -20) }};
            --color-text: {{ $textColor }};
            --color-bg: {{ $backgroundColor }};
            --font-family: '{{ $fontFamily }}', sans-serif;
        }

        body { font-family: var(--font-family) !important; }

        *:not(.fas):not(.far):not(.fab):not(.fa-solid):not(.fa-regular):not(.fa-brands):not(.fa):not([class^="fa-"]) {
            font-family: var(--font-family) !important;
        }

        .admin-btn-primary {
            background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }}) !important;
        }
        .admin-nav-item.active {
            background: {{ hexToRgba($primaryColor, 0.1) }} !important;
            color: {{ $primaryColor }} !important;
        }
        .admin-stat-icon.primary {
            background: linear-gradient(135deg, {{ $primaryColor }}, {{ adjustBrightness($primaryColor, 20) }}) !important;
        }
        .gradient-purple {
            background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }}) !important;
        }

        /* ===== أصناف مساعدة خاصّة بلوحة الدعم (support-*) ===== */
        .support-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 12px; border-radius: 999px;
            font-size: 12px; font-weight: 700; line-height: 1.6;
            white-space: nowrap;
        }
        .support-badge.warning { background: #fef3c7; color: #b45309; }
        .support-badge.info    { background: #dbeafe; color: #1d4ed8; }
        .support-badge.success { background: #dcfce7; color: #15803d; }
        .support-badge.secondary { background: #e2e8f0; color: #475569; }
        .support-badge.danger  { background: #fee2e2; color: #b91c1c; }
        .support-badge.escalate{ background: #fae8ff; color: #a21caf; }

        html[data-theme="dark"] .support-badge.warning { background: rgba(245,158,11,.18); color: #fcd34d; }
        html[data-theme="dark"] .support-badge.info    { background: rgba(59,130,246,.18); color: #93c5fd; }
        html[data-theme="dark"] .support-badge.success { background: rgba(34,197,94,.18); color: #86efac; }
        html[data-theme="dark"] .support-badge.secondary { background: rgba(148,163,184,.18); color: #cbd5e1; }
        html[data-theme="dark"] .support-badge.danger  { background: rgba(239,68,68,.18); color: #fca5a5; }
        html[data-theme="dark"] .support-badge.escalate{ background: rgba(168,85,247,.18); color: #e9d5ff; }

        .support-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(15,23,42,.05);
        }
        html[data-theme="dark"] .support-card {
            background: var(--w-card) !important;
            border-color: var(--w-border) !important;
            color: var(--w-text) !important;
        }

        .support-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 16px; border-radius: 10px;
            font-size: 13px; font-weight: 700; cursor: pointer;
            border: none; text-decoration: none; transition: all .2s;
            font-family: inherit;
        }
        .support-btn:hover { transform: translateY(-1px); }
        .support-btn-primary { background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); color: #fff; }
        .support-btn-success { background: #16a34a; color: #fff; }
        .support-btn-warning { background: #f59e0b; color: #fff; }
        .support-btn-secondary { background: #64748b; color: #fff; }
        .support-btn-escalate { background: linear-gradient(135deg, #a21caf, #7e22ce); color: #fff; }
        .support-btn-ghost { background: #f1f5f9; color: #334155; }
        html[data-theme="dark"] .support-btn-ghost { background: rgba(255,255,255,.08); color: var(--w-text); }

        .support-table { width: 100%; border-collapse: collapse; }
        .support-table th {
            background: #f8fafc; padding: 14px 16px; text-align: right;
            font-weight: 700; color: #475569; font-size: 13px;
            border-bottom: 2px solid #e2e8f0; white-space: nowrap;
        }
        .support-table td { padding: 14px 16px; border-bottom: 1px solid #eef2f7; vertical-align: middle; }
        .support-table tbody tr:hover { background: #f8fafc; }
        html[data-theme="dark"] .support-table th { background: rgba(255,255,255,.04); color: var(--w-text-muted); border-color: var(--w-border); }
        html[data-theme="dark"] .support-table td { border-color: var(--w-border); color: var(--w-text); }
        html[data-theme="dark"] .support-table tbody tr:hover { background: rgba(255,255,255,.03); }
    </style>

    @stack('styles')

    @include('partials.theme-toggle')
</head>
<body>
    @include('partials.flash')
    <a href="#support-main-content" class="skip-to-content"
       style="position:absolute;top:-40px;right:0;background:#1e293b;color:#fff;padding:8px 16px;z-index:9999;text-decoration:none;border-radius:0 0 0 8px;"
       onfocus="this.style.top='0'" onblur="this.style.top='-40px'">تخطي إلى المحتوى الرئيسي</a>

    <div class="admin-layout">
        <!-- Sidebar -->
        <div id="adminSidebarOverlay" class="admin-sidebar-overlay"
             onclick="(function(){var s=document.querySelector('.admin-sidebar');s.classList.remove('open');document.getElementById('adminSidebarOverlay').style.display='none';var b=document.querySelector('.admin-mobile-menu-btn');if(b)b.setAttribute('aria-expanded','false');})()"></div>
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <a href="{{ route('support.dashboard') }}" class="admin-logo">
                    @include('partials.brand')
                </a>
            </div>

            <nav class="admin-nav">
                <a href="{{ route('support.dashboard') }}" class="admin-nav-item {{ request()->routeIs('support.dashboard') ? 'active' : '' }}">
                    <span class="admin-nav-icon">🎧</span>
                    <span class="admin-nav-text">لوحة الدعم</span>
                </a>

                <div class="admin-nav-section">
                    <div class="admin-nav-section-title">الدعم الفنيّ</div>
                    <div class="admin-nav-items">
                        <a href="{{ route('support.tickets.index') }}" class="admin-nav-item {{ request()->routeIs('support.tickets.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🎫</span>
                            <span class="admin-nav-text">التذاكر</span>
                            @php
                                $openTicketsCount = \App\Models\SupportTicket::whereIn('status', ['open', 'answered'])->count();
                            @endphp
                            <span style="background: #f59e0b; color: white; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600; margin-right: auto; display: {{ $openTicketsCount > 0 ? 'inline-flex' : 'none' }};">{{ $openTicketsCount > 0 ? $openTicketsCount : 0 }}</span>
                        </a>

                        <a href="{{ route('support.users.index') }}" class="admin-nav-item {{ request()->routeIs('support.users.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">👥</span>
                            <span class="admin-nav-text">المستخدمون</span>
                        </a>
                    </div>
                </div>
            </nav>

            <div class="admin-sidebar-footer">
                <a href="{{ route('landing') }}" class="admin-nav-item">
                    <span class="admin-nav-icon">🏠</span>
                    <span class="admin-nav-text">العودة للموقع</span>
                </a>

                <button class="admin-nav-item" id="sidebarThemeToggle" style="margin-bottom: 8px;">
                    <span class="admin-nav-icon">
                        <span class="icon-sun">☀️</span>
                        <span class="icon-moon">🌙</span>
                    </span>
                    <span class="admin-nav-text">تبديل الوضع</span>
                </button>

                <form action="{{ route('logout') }}" method="POST" class="admin-logout-form">
                    @csrf
                    <button type="submit" class="admin-nav-item admin-logout-btn">
                        <span class="admin-nav-icon">🚪</span>
                        <span class="admin-nav-text">تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main" id="support-main-content">
            <!-- Header -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <button type="button" class="admin-mobile-menu-btn"
                            aria-label="فتح القائمة"
                            aria-controls="adminSidebar"
                            aria-expanded="false"
                            onclick="(function(btn){var s=document.querySelector('.admin-sidebar');var open=s.classList.toggle('open');btn.setAttribute('aria-expanded',open?'true':'false');var ov=document.getElementById('adminSidebarOverlay');if(ov){ov.style.display=open?'block':'none';}})(this)"
                            style="display:none;background:none;border:1px solid #e5e7eb;border-radius:8px;width:44px;height:44px;cursor:pointer;font-size:20px;align-items:center;justify-content:center;margin-left:8px;">
                        ☰
                    </button>
                    <h1 class="admin-page-title">@yield('page-title', 'لوحة الدعم الفنيّ')</h1>
                </div>
                <div class="admin-header-right" style="display: flex; align-items: center; gap: 16px;">
                    <div class="admin-user-info">
                        <span class="admin-user-name">{{ auth()->user()->name }}</span>
                        <span class="admin-user-role">الدعم الفنيّ</span>
                    </div>
                    <div style="position: relative;" id="avatarDropdownContainer">
                        <div class="admin-user-avatar" id="avatarToggleBtn" style="cursor: pointer; overflow: hidden; padding: 0;">
                            <img src="{{ auth()->user()->avatar_url }}" alt="صورة الملف الشخصي" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        </div>

                        <div id="avatarDropdownMenu" style="display: none; position: absolute; top: calc(100% + 10px); left: 50%; transform: translateX(-50%); width: 260px; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); z-index: 1000; overflow: hidden;">
                            <div style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); padding: 20px; text-align: center;">
                                <div style="width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 10px; overflow: hidden; border: 3px solid rgba(255,255,255,0.4);">
                                    <img src="{{ auth()->user()->avatar_url }}" alt="صورة" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div style="color: white; font-weight: 700; font-size: 15px;">{{ auth()->user()->name }}</div>
                                <div style="color: rgba(255,255,255,0.8); font-size: 12px; margin-top: 4px;">{{ auth()->user()->email }}</div>
                            </div>
                            <div style="padding: 8px;">
                                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; width: 100%; border: none; background: transparent; cursor: pointer; transition: background 0.2s; color: #ef4444; font-weight: 600; font-size: 14px; font-family: inherit;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                        <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(239,68,68,0.1); display: flex; align-items: center; justify-content: center;">🚪</span>
                                        تسجيل الخروج
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="admin-content">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')

    <script>
        // Avatar Dropdown Toggle
        (function() {
            const toggleBtn = document.getElementById('avatarToggleBtn');
            const dropdownMenu = document.getElementById('avatarDropdownMenu');
            const container = document.getElementById('avatarDropdownContainer');
            if (toggleBtn && dropdownMenu) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
                });
                document.addEventListener('click', function(e) {
                    if (container && !container.contains(e.target)) {
                        dropdownMenu.style.display = 'none';
                    }
                });
            }
        })();
    </script>

    {{-- تغطية داكنة إضافية لأصناف الدعم عبر متغيّرات --w-* المشتركة (لا تلمس الشارات الملوّنة) --}}
    <style>
        html[data-theme="dark"] #avatarDropdownMenu { background: var(--w-card) !important; }
        html[data-theme="dark"] #avatarDropdownMenu button { color: #fca5a5 !important; }
    </style>

    @stack('after-content')
</body>
</html>
