<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="{{ $branding['site_theme'] ?? 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.head-meta')
    @include('partials.theme-vars')
    <title>@yield('title', 'لوحة ولي الأمر - بناء القيم')</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
    <style>
        * {
            font-family: 'IBM Plex Sans Arabic', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, var(--color-primary, #667eea) 0%, var(--color-secondary, #764ba2) 50%, #f093fb 100%);
            background-attachment: fixed;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Premium Header */
        .parent-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(50px) saturate(200%);
            -webkit-backdrop-filter: blur(50px) saturate(200%);
            border-bottom: 1.5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 1.25rem 2rem;
            margin-bottom: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 2.5rem;
        }
        
        .header-logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--color-primary, #667eea) 0%, var(--color-secondary, #764ba2) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .header-nav {
            display: flex;
            gap: 0.75rem;
        }
        
        .nav-link {
            text-decoration: none;
            color: #475569;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.625rem 1.25rem;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--color-primary, #667eea) 0%, var(--color-secondary, #764ba2) 100%);
            border-radius: 2px;
            transition: width 0.3s;
        }
        
        .nav-link:hover::before,
        .nav-link.active::before {
            width: 80%;
        }
        
        .nav-link:hover {
            background: rgba(102, 126, 234, 0.1);
            color: var(--color-primary, #667eea);
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%);
            color: var(--color-primary, #667eea);
        }
        
        .badge-notification {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 700;
            margin-right: 0.5rem;
            min-width: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
            animation: pulseBadge 2s ease-in-out infinite;
        }
        
        @keyframes pulseBadge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .role-switcher-btn {
            background: linear-gradient(135deg, var(--color-primary, #667eea) 0%, var(--color-secondary, #764ba2) 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .role-switcher-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .role-switcher-menu {
            display: none;
            position: absolute;
            top: calc(100% + 0.75rem);
            left: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(50px) saturate(200%);
            -webkit-backdrop-filter: blur(50px) saturate(200%);
            border-radius: 16px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.15),
                0 8px 24px rgba(0, 0, 0, 0.1);
            padding: 0.75rem;
            min-width: 220px;
            z-index: 1000;
            border: 1.5px solid rgba(255, 255, 255, 0.4);
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .role-menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem;
            text-decoration: none;
            color: #1e293b;
            border-radius: 12px;
            transition: all 0.2s;
            margin-bottom: 0.25rem;
        }
        
        .role-menu-item:last-child {
            margin-bottom: 0;
        }
        
        .role-menu-item:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateX(-3px);
        }
        
        .role-menu-icon {
            font-size: 1.25rem;
        }
        
        .role-menu-text {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }
        
        .logout-btn:active {
            transform: translateY(0);
        }
        
        main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0;
            position: relative;
            z-index: 1;
        }
        
        /* زر قائمة الجوال — مخفي على سطح المكتب */
        .parent-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.6rem;
            line-height: 1;
            cursor: pointer;
            color: var(--color-text, #475569);
        }

        @media (max-width: 768px) {
            .header-container {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .header-left {
                width: 100%;
                justify-content: space-between;
            }

            /* إظهار زر الهامبرغر وتحويل القائمة لمنسدلة عمودية بدل إخفائها كلياً */
            .parent-menu-toggle {
                display: flex;
                align-items: center;
            }
            .header-nav {
                display: none;
                flex-direction: column;
                align-items: stretch;
                width: 100%;
                order: 3;
                gap: 0.5rem;
                margin-top: 0.75rem;
            }
            .header-nav.open {
                display: flex;
            }
            .header-nav .nav-link {
                width: 100%;
            }

            .header-right {
                width: 100%;
                justify-content: space-between;
            }
        }
        
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>

    @include('partials.theme-toggle')
</head>
<body>
    @include('partials.flash')
    <a href="#parent-main-content" class="skip-to-content"
       style="position:absolute;top:-40px;right:0;background:#1e293b;color:#fff;padding:8px 16px;z-index:9999;text-decoration:none;border-radius:0 0 0 8px;"
       onfocus="this.style.top='0'" onblur="this.style.top='-40px'">تخطي إلى المحتوى الرئيسي</a>
    <header class="parent-header">
        <div class="header-container">
            <div class="header-left">
                <div class="header-logo">
                    <span>👨‍👩‍👧‍👦</span>
                    <span>لوحة ولي الأمر</span>
                </div>
                <button type="button" class="parent-menu-toggle" aria-label="القائمة" aria-expanded="false" aria-controls="parentNav"
                        onclick="var n=document.getElementById('parentNav');var o=n.classList.toggle('open');this.setAttribute('aria-expanded',o?'true':'false');">☰</button>
                <nav class="header-nav" id="parentNav">
                    <a href="{{ route('parent.dashboard') }}" class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>الرئيسية</span>
                    </a>
                    {{-- N12: مقارنات الاستبيانات لأبنائي --}}
                    <a href="{{ route('parent.surveys.comparisons') }}" class="nav-link {{ request()->routeIs('parent.surveys.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>تقدّم أبنائي</span>
                    </a>
                    @php
                        $pendingFamily = \App\Models\FamilyActivitySubmission::where('parent_id', auth()->id())->where('status', 'pending')->count();
                    @endphp
                    <a href="{{ route('parent.family-activities.pending') }}" class="nav-link {{ request()->routeIs('parent.family-activities.*') ? 'active' : '' }}" style="position: relative;">
                        <i class="fas fa-hands-helping"></i>
                        <span>الأنشطة العائلية</span>
                        @if($pendingFamily > 0)<span class="nav-badge" style="position:absolute;top:2px;left:6px;background:#ef4444;color:#fff;border-radius:50%;min-width:18px;height:18px;font-size:11px;display:flex;align-items:center;justify-content:center;">{{ $pendingFamily }}</span>@endif
                    </a>
                    <a href="{{ route('messages.index') }}" class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" style="position: relative;">
                        <i class="fas fa-comments"></i>
                        <span>الرسائل</span>
                        @php
                            $unreadCount = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="badge-notification">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('parent.messages') }}" class="nav-link {{ request()->routeIs('parent.messages') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i>
                        <span>مراسلة المعلم</span>
                    </a>
                    <a href="{{ route('messages.bulk.inbox') }}" class="nav-link {{ request()->routeIs('messages.bulk.inbox') ? 'active' : '' }}" style="position: relative;">
                        <i class="fas fa-inbox"></i>
                        <span>رسائل جماعية</span>
                        @php
                            $bulkUnreadCount = \App\Models\BulkMessageRecipient::where('user_id', auth()->id())->whereNull('read_at')->count();
                        @endphp
                        @if($bulkUnreadCount > 0)
                            <span class="badge-notification">{{ $bulkUnreadCount > 9 ? '9+' : $bulkUnreadCount }}</span>
                        @endif
                    </a>
                </nav>
            </div>
            
            <div class="header-right">
                @if(auth()->user()->hasMultipleRoles())
                    <div style="position: relative;">
                        <button onclick="toggleRoleSwitcher()" class="role-switcher-btn">
                            <i class="fas fa-sync-alt"></i>
                            <span>تبديل الدور</span>
                        </button>
                        <div id="roleSwitcherMenu" class="role-switcher-menu">
                            @php
                                $currentRole = auth()->user()->getCurrentRole();
                                $allRoles = auth()->user()->getAllRoles();
                            @endphp
                            @foreach($allRoles as $role)
                                @if($role !== $currentRole)
                                    <form method="POST" action="{{ route('switch.role', $role) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="role-menu-item" style="background:none;border:none;width:100%;text-align:inherit;cursor:pointer;display:flex;align-items:center;gap:10px;">
                                            <span class="role-menu-icon">
                                                {{ App\Models\User::getRoleIcon($role) === 'fas fa-chalkboard-teacher' ? '👨‍🏫' : (App\Models\User::getRoleIcon($role) === 'fas fa-users' ? '👨‍👩‍👧' : '👤') }}
                                            </span>
                                            <span class="role-menu-text">{{ App\Models\User::getRoleNameAr($role) }}</span>
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <script>
                        function toggleRoleSwitcher() {
                            const menu = document.getElementById('roleSwitcherMenu');
                            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
                        }
                        document.addEventListener('click', function(e) {
                            const menu = document.getElementById('roleSwitcherMenu');
                            if (!e.target.closest('#roleSwitcherMenu') && !e.target.closest('button[onclick="toggleRoleSwitcher()"]')) {
                                menu.style.display = 'none';
                            }
                        });
                    </script>
                @endif
                
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </div>
    </header>
    
    <main id="parent-main-content">
        @yield('content')
    </main>
    
    <!-- Real-Time Messages System -->
    <script src="{{ asset('js/messages-realtime.js') }}"></script>
    @stack('scripts')
    
    <!-- Survey Popup Component -->
    @include('components.survey-popup')

    @stack('after-content')
</body>
</html>
