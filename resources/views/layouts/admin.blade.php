@php
    // جلب إعدادات الثيم من قاعدة البيانات
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
    <title>@yield('title', 'لوحة التحكم') - {{ $branding['site_name'] ?? 'قيمّ' }}</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
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
    
    <!-- ملف CSS الأساسي -->
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
        
        body {
            font-family: var(--font-family) !important;
        }
        
        *:not(.fas):not(.far):not(.fab):not(.fa-solid):not(.fa-regular):not(.fa-brands):not(.fa):not([class^="fa-"]) {
            font-family: var(--font-family) !important;
        }
        
        /* تطبيق الألوان على العناصر الأساسية */
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
    </style>
    
    @stack('styles')

    @include('partials.theme-toggle')
</head>
<body>
    @include('partials.flash')
    <a href="#admin-main-content" class="skip-to-content"
       style="position:absolute;top:-40px;right:0;background:#1e293b;color:#fff;padding:8px 16px;z-index:9999;text-decoration:none;border-radius:0 0 0 8px;"
       onfocus="this.style.top='0'" onblur="this.style.top='-40px'">تخطي إلى المحتوى الرئيسي</a>
    <div class="admin-layout">
        <!-- Sidebar -->
        <div id="adminSidebarOverlay" class="admin-sidebar-overlay"
             onclick="(function(){var s=document.querySelector('.admin-sidebar');s.classList.remove('open');document.getElementById('adminSidebarOverlay').style.display='none';var b=document.querySelector('.admin-mobile-menu-btn');if(b)b.setAttribute('aria-expanded','false');})()"></div>
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="admin-logo">
                    @include('partials.brand')
                </a>
            </div>

            <nav class="admin-nav">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="admin-nav-icon">📊</span>
                    <span class="admin-nav-text">لوحة البيانات</span>
                </a>

                <a href="{{ route('admin.pending-submissions') }}" class="admin-nav-item {{ request()->routeIs('admin.pending-submissions') || request()->routeIs('admin.review-submission') ? 'active' : '' }}">
                    <span class="admin-nav-icon">📝</span>
                    <span class="admin-nav-text">التقديمات المعلقة</span>
                    @php
                        $pendingSubmissionsCount = \App\Models\ActivitySubmission::where('status', 'pending')->count();
                    @endphp
                    @if($pendingSubmissionsCount > 0)
                        <span style="background: #f59e0b; color: white; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600; margin-right: auto;">{{ $pendingSubmissionsCount }}</span>
                    @endif
                </a>

                <a href="{{ route('messages.index') }}" class="admin-nav-item {{ request()->routeIs('messages.*') ? 'active' : '' }}">
                    <span class="admin-nav-icon">💬</span>
                    <span class="admin-nav-text">الرسائل</span>
                    @php
                        $unreadCount = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span style="background: #ef4444; color: white; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600; margin-right: auto;">{{ $unreadCount }}</span>
                    @endif
                </a>

                <div class="admin-nav-section">
                    <div class="admin-nav-section-title">إدارة النظام</div>
                    
                    <div class="admin-nav-items">
                        <a href="{{ route('admin.theme') }}" class="admin-nav-item {{ request()->routeIs('admin.theme*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🎨</span>
                            <span class="admin-nav-text">تخصيص الثيم</span>
                        </a>

                        <a href="{{ route('admin.pages.index') }}" class="admin-nav-item {{ request()->routeIs('admin.pages*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">📄</span>
                            <span class="admin-nav-text">بناء الصفحات</span>
                        </a>

                        <a href="{{ route('admin.settings') }}" class="admin-nav-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">⚙️</span>
                            <span class="admin-nav-text">الإعدادات العامة</span>
                        </a>

                        <a href="{{ route('admin.education-levels') }}" class="admin-nav-item {{ request()->routeIs('admin.education-levels*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🎓</span>
                            <span class="admin-nav-text">المراحل الدراسية</span>
                        </a>
                    </div>
                </div>

                <div class="admin-nav-section">
                    <div class="admin-nav-section-title">محرر الصفحة الرئيسية</div>
                    
                    <div class="admin-nav-items">
                        <a href="/" class="admin-nav-item" target="_blank">
                            <span class="admin-nav-icon">✏️</span>
                            <span class="admin-nav-text">افتح المحرر</span>
                        </a>
                    </div>
                    
                    <div style="padding: 15px; background: rgba(102, 126, 234, 0.1); border-radius: 8px; margin: 10px 15px; border-right: 3px solid #667eea;">
                        <p style="margin: 0 0 8px; font-size: 13px; color: #667eea; font-weight: 600;">💡 كيف تستخدم المحرر؟</p>
                        <ol style="margin: 0; padding-right: 20px; font-size: 12px; color: #666; line-height: 1.6;">
                            <li>اضغط على "افتح المحرر" أعلاه</li>
                            <li>اضغط على الزر العائم ✏️ أسفل اليسار</li>
                            <li>ابدأ التعديل مباشرة!</li>
                        </ol>
                    </div>
                </div>

                <div class="admin-nav-section">
                    <div class="admin-nav-section-title">إدارة المستخدمين</div>
                    
                    <div class="admin-nav-items">
                        <a href="{{ route('admin.users.index') }}" class="admin-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">👥</span>
                            <span class="admin-nav-text">المستخدمين</span>
                        </a>

                        <a href="{{ route('admin.schools.index') }}" class="admin-nav-item {{ request()->routeIs('admin.schools.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🏫</span>
                            <span class="admin-nav-text">المدارس</span>
                        </a>

                        <a href="{{ route('admin.teachers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">👨‍🏫</span>
                            <span class="admin-nav-text">المعلمين</span>
                        </a>

                        <a href="{{ route('admin.students.index') }}" class="admin-nav-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🎓</span>
                            <span class="admin-nav-text">الطلاب</span>
                        </a>

                        <a href="{{ route('admin.parents.index') }}" class="admin-nav-item {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">👪</span>
                            <span class="admin-nav-text">أولياء الأمور</span>
                        </a>

                        <a href="{{ route('admin.online-users') }}" class="admin-nav-item {{ request()->routeIs('admin.online-users*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🟢</span>
                            <span class="admin-nav-text">المتصلين الآن</span>
                        </a>
                    </div>
                </div>

                <div class="admin-nav-section">
                    <div class="admin-nav-section-title">المحتوى التعليمي</div>
                    
                    <div class="admin-nav-items">
                        <a href="{{ route('admin.values.index') }}" class="admin-nav-item {{ request()->routeIs('admin.values.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">💎</span>
                            <span class="admin-nav-text">القيم</span>
                        </a>

                        <a href="{{ route('admin.concepts.index') }}" class="admin-nav-item {{ request()->routeIs('admin.concepts.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">💡</span>
                            <span class="admin-nav-text">المفاهيم</span>
                        </a>

                        <a href="{{ route('admin.lessons.index') }}" class="admin-nav-item {{ request()->routeIs('admin.lessons.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">📚</span>
                            <span class="admin-nav-text">الدروس</span>
                        </a>

                        <a href="{{ route('admin.activities.index') }}" class="admin-nav-item {{ request()->routeIs('admin.activities.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🎯</span>
                            <span class="admin-nav-text">الأنشطة</span>
                        </a>

                        @if(auth()->user()->role === 'super_admin')
                        <a href="{{ route('admin.pvp-challenges.index') }}" class="admin-nav-item {{ request()->routeIs('admin.pvp-challenges.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">⚔️</span>
                            <span class="admin-nav-text">تحديات PvP</span>
                        </a>
                        @endif

                        <a href="{{ route('admin.surveys.index') }}" class="admin-nav-item {{ request()->routeIs('admin.surveys.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">📋</span>
                            <span class="admin-nav-text">الاستبيانات</span>
                        </a>
                    </div>
                </div>

                <div class="admin-nav-section">
                    <div class="admin-nav-section-title">التقارير والإحصاءات</div>
                    
                    <div class="admin-nav-items">
                        <a href="{{ route('admin.reports.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.reports.dashboard') ? 'active' : '' }}">
                            <span class="admin-nav-icon">📊</span>
                            <span class="admin-nav-text">لوحة الإحصائيات</span>
                        </a>

                        <a href="{{ route('admin.reports.students') }}" class="admin-nav-item {{ request()->routeIs('admin.reports.students*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">👨‍🎓</span>
                            <span class="admin-nav-text">تقارير الطلاب</span>
                        </a>

                        <a href="{{ route('admin.reports.schools') }}" class="admin-nav-item {{ request()->routeIs('admin.reports.schools*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🏫</span>
                            <span class="admin-nav-text">تقارير المدارس</span>
                        </a>

                        <a href="{{ route('admin.reports.activities') }}" class="admin-nav-item {{ request()->routeIs('admin.reports.activities') ? 'active' : '' }}">
                            <span class="admin-nav-icon">📝</span>
                            <span class="admin-nav-text">تقارير الأنشطة</span>
                        </a>

                        <a href="{{ route('admin.reports.values') }}" class="admin-nav-item {{ request()->routeIs('admin.reports.values') ? 'active' : '' }}">
                            <span class="admin-nav-icon">💎</span>
                            <span class="admin-nav-text">تقارير القيم</span>
                        </a>
                    </div>
                </div>

                <div class="admin-nav-section">
                    <div class="admin-nav-section-title">أدوات النظام</div>
                    
                    <div class="admin-nav-items">
                        <a href="{{ route('admin.excel-management') }}" class="admin-nav-item {{ request()->routeIs('admin.excel-management') ? 'active' : '' }}">
                            <span class="admin-nav-icon">📊</span>
                            <span class="admin-nav-text">إدارة Excel</span>
                        </a>

                        <a href="{{ route('admin.activity-bank.index') }}" class="admin-nav-item {{ request()->routeIs('admin.activity-bank.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">📚</span>
                            <span class="admin-nav-text">بنك الأنشطة</span>
                            @php
                                $pendingBankActivities = \App\Models\Activity::where('is_activity_bank', true)->where('approval_status', 'pending')->count();
                                $pendingBankQuestions = \App\Models\QuestionBank::where('status', 'pending')->count();
                                $totalBankPending = $pendingBankActivities + $pendingBankQuestions;
                            @endphp
                            @if($totalBankPending > 0)
                                <span style="background: #f59e0b; color: white; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600; margin-right: auto;">{{ $totalBankPending }}</span>
                            @endif
                        </a>

                        <a href="{{ route('admin.activity-approval.index') }}" class="admin-nav-item {{ request()->routeIs('admin.activity-approval.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">✅</span>
                            <span class="admin-nav-text">الموافقة على الأنشطة</span>
                            @php
                                $pendingActivities = \App\Models\Activity::where('is_activity_bank', true)->where('approval_status', 'pending')->count();
                            @endphp
                            @if($pendingActivities > 0)
                                <span style="background: #f59e0b; color: white; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600; margin-right: auto;">{{ $pendingActivities }}</span>
                            @endif
                        </a>

                        <a href="{{ route('admin.backups') }}" class="admin-nav-item {{ request()->routeIs('admin.backups') ? 'active' : '' }}">
                            <span class="admin-nav-icon">💾</span>
                            <span class="admin-nav-text">النسخ الاحتياطي</span>
                        </a>

                        <a href="{{ route('admin.activity-logs') }}" class="admin-nav-item {{ request()->routeIs('admin.activity-logs') ? 'active' : '' }}">
                            <span class="admin-nav-icon">📋</span>
                            <span class="admin-nav-text">سجل الأنشطة</span>
                        </a>

                        <a href="{{ route('admin.messages-log.index') }}" class="admin-nav-item {{ request()->routeIs('admin.messages-log.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">📨</span>
                            <span class="admin-nav-text">سجل الرسائل</span>
                        </a>

                        <a href="{{ route('admin.featured-activities') }}" class="admin-nav-item {{ request()->routeIs('admin.featured-activities*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">⭐</span>
                            <span class="admin-nav-text">الأنشطة المميزة</span>
                        </a>

                        <a href="{{ route('admin.shop.index') }}" class="admin-nav-item {{ request()->routeIs('admin.shop.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🛒</span>
                            <span class="admin-nav-text">إدارة المتجر</span>
                        </a>
                    </div>
                </div>

                <div class="admin-nav-section">
                    <div class="admin-nav-section-title">التواصل والتفاعل</div>
                    
                    <div class="admin-nav-items">
                        <a href="{{ route('messages.bulk.index') }}" class="admin-nav-item {{ request()->routeIs('messages.bulk.*') ? 'active' : '' }}">
                            <span class="admin-nav-icon">📨</span>
                            <span class="admin-nav-text">الرسائل الجماعية</span>
                        </a>

                        <a href="{{ route('messages.bulk.inbox') }}" class="admin-nav-item {{ request()->routeIs('messages.bulk.inbox') ? 'active' : '' }}">
                            <span class="admin-nav-icon">📬</span>
                            <span class="admin-nav-text">صندوق الوارد</span>
                        </a>
                    </div>
                </div>

                <div class="admin-nav-section">
                    <div class="admin-nav-section-title">🏆 لوحات الصدارة</div>
                    
                    <div class="admin-nav-items">
                        <a href="{{ route('leaderboard.index') }}" class="admin-nav-item {{ request()->routeIs('leaderboard.index') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🏆</span>
                            <span class="admin-nav-text">نظرة عامة</span>
                        </a>

                        <a href="{{ route('leaderboard.students') }}" class="admin-nav-item {{ request()->routeIs('leaderboard.students') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🎓</span>
                            <span class="admin-nav-text">صدارة الطلاب</span>
                        </a>

                        <a href="{{ route('leaderboard.teachers') }}" class="admin-nav-item {{ request()->routeIs('leaderboard.teachers') ? 'active' : '' }}">
                            <span class="admin-nav-icon">👨‍🏫</span>
                            <span class="admin-nav-text">صدارة المعلمين</span>
                        </a>

                        <a href="{{ route('leaderboard.parents') }}" class="admin-nav-item {{ request()->routeIs('leaderboard.parents') ? 'active' : '' }}">
                            <span class="admin-nav-icon">👨‍👩‍👧</span>
                            <span class="admin-nav-text">صدارة أولياء الأمور</span>
                        </a>

                        <a href="{{ route('leaderboard.schools') }}" class="admin-nav-item {{ request()->routeIs('leaderboard.schools') ? 'active' : '' }}">
                            <span class="admin-nav-icon">🏫</span>
                            <span class="admin-nav-text">صدارة المدارس</span>
                        </a>
                    </div>
                </div>

            </nav>

            <!-- تبديل الأدوار -->
            @include('components.role-switcher')

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
        <main class="admin-main" id="admin-main-content">
            <!-- Header -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <!-- زر القائمة للموبايل — يظهر فقط على شاشات أصغر من 768px -->
                    <button type="button" class="admin-mobile-menu-btn"
                            aria-label="فتح القائمة"
                            aria-controls="adminSidebar"
                            aria-expanded="false"
                            onclick="(function(btn){var s=document.querySelector('.admin-sidebar');var open=s.classList.toggle('open');btn.setAttribute('aria-expanded',open?'true':'false');var ov=document.getElementById('adminSidebarOverlay');if(ov){ov.style.display=open?'block':'none';}})(this)"
                            style="display:none;background:none;border:1px solid #e5e7eb;border-radius:8px;width:44px;height:44px;cursor:pointer;font-size:20px;align-items:center;justify-content:center;margin-left:8px;">
                        ☰
                    </button>
                    <h1 class="admin-page-title">@yield('page-title', 'لوحة التحكم')</h1>
                </div>
                <div class="admin-header-right" style="display: flex; align-items: center; gap: 16px;">
                    <!-- Notification Counters -->
                    <a href="{{ route('admin.users.index') }}" style="text-decoration: none; display: flex; align-items: center; gap: 6px; background: rgba(102, 126, 234, 0.1); padding: 8px 14px; border-radius: 10px; color: #667eea; font-weight: 600; font-size: 13px; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(102,126,234,0.2)'" onmouseout="this.style.background='rgba(102,126,234,0.1)'">
                        <span>👥</span>
                        <span>{{ $newUsersCount ?? 0 }} مستخدم جديد</span>
                    </a>
                    <a href="{{ route('admin.pending-submissions') }}" style="text-decoration: none; display: flex; align-items: center; gap: 6px; background: rgba(245, 158, 11, 0.1); padding: 8px 14px; border-radius: 10px; color: #f59e0b; font-weight: 600; font-size: 13px; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(245,158,11,0.2)'" onmouseout="this.style.background='rgba(245,158,11,0.1)'">
                        <span>📝</span>
                        <span>{{ $newSubmissionsCount ?? 0 }} تقديم جديد</span>
                    </a>

                    <!-- User Info + Avatar Dropdown -->
                    <div class="admin-user-info">
                        <span class="admin-user-name">{{ auth()->user()->name }}</span>
                        <span class="admin-user-role">{{ auth()->user()->role === 'super_admin' ? 'سوبر أدمن' : auth()->user()->role }}</span>
                    </div>
                    <div style="position: relative;" id="avatarDropdownContainer">
                        <div class="admin-user-avatar" id="avatarToggleBtn" style="cursor: pointer; overflow: hidden; padding: 0;">
                            <img src="{{ auth()->user()->avatar_url }}" alt="صورة الملف الشخصي" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        </div>

                        <!-- Avatar Dropdown Menu -->
                        <div id="avatarDropdownMenu" style="display: none; position: absolute; top: calc(100% + 10px); left: 50%; transform: translateX(-50%); width: 260px; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); z-index: 1000; overflow: hidden; animation: popupSlideUp 0.3s ease;">
                            <!-- User Info Header -->
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; text-align: center;">
                                <div style="width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 10px; overflow: hidden; border: 3px solid rgba(255,255,255,0.4);">
                                    <img src="{{ auth()->user()->avatar_url }}" alt="صورة" style="width: 100%; height: 100%; object-fit: cover;" id="dropdownAvatarImg">
                                </div>
                                <div style="color: white; font-weight: 700; font-size: 15px;">{{ auth()->user()->name }}</div>
                                <div style="color: rgba(255,255,255,0.8); font-size: 12px; margin-top: 4px;">{{ auth()->user()->email }}</div>
                            </div>
                            <!-- Menu Items -->
                            <div style="padding: 8px;">
                                <label for="avatarUploadInput" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; cursor: pointer; transition: background 0.2s; color: #334155; font-weight: 600; font-size: 14px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(102,126,234,0.1); display: flex; align-items: center; justify-content: center;">📷</span>
                                    تغيير الصورة
                                </label>
                                <input type="file" id="avatarUploadInput" accept="image/jpeg,image/png,image/jpg,image/webp" style="display: none;">

                                <a href="{{ route('admin.settings') }}" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; text-decoration: none; transition: background 0.2s; color: #334155; font-weight: 600; font-size: 14px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(102,126,234,0.1); display: flex; align-items: center; justify-content: center;">⚙️</span>
                                    الإعدادات
                                </a>

                                <div style="height: 1px; background: #e2e8f0; margin: 4px 16px;"></div>

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

    <!-- Glassmorphism Popup Styles -->
    <style>
        /* Glassmorphism Popup Overlay */
        .glassmorphism-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }

        .glassmorphism-popup-overlay.active {
            display: flex;
        }

        /* Glassmorphism Popup */
        .glassmorphism-popup {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            padding: 0;
            animation: popupSlideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
        }

        /* Popup Header */
        .glassmorphism-popup-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9));
            padding: 24px;
            text-align: center;
        }

        .glassmorphism-popup-icon {
            font-size: 56px;
            margin-bottom: 12px;
            animation: iconBounce 0.6s ease;
        }

        .glassmorphism-popup-title {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Popup Body */
        .glassmorphism-popup-body {
            padding: 32px;
            text-align: center;
        }

        .glassmorphism-popup-message {
            color: #334155;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .glassmorphism-popup-list {
            background: rgba(102, 126, 234, 0.05);
            border-radius: 12px;
            padding: 16px;
            margin: 20px 0;
            text-align: right;
        }

        .glassmorphism-popup-list-item {
            color: #475569;
            font-size: 14px;
            padding: 6px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .glassmorphism-popup-list-item::before {
            content: '✓';
            color: #667eea;
            font-weight: bold;
            font-size: 16px;
        }

        /* Popup Actions */
        .glassmorphism-popup-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .glassmorphism-popup-btn {
            padding: 14px 32px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .glassmorphism-popup-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .glassmorphism-popup-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .glassmorphism-popup-btn-secondary {
            background: rgba(148, 163, 184, 0.1);
            color: #64748b;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .glassmorphism-popup-btn-secondary:hover {
            background: rgba(148, 163, 184, 0.2);
        }

        /* Success Popup */
        .glassmorphism-popup.success .glassmorphism-popup-header {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9));
        }

        /* Error Popup */
        .glassmorphism-popup.error .glassmorphism-popup-header {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.9), rgba(220, 38, 38, 0.9));
        }

        /* Warning Popup */
        .glassmorphism-popup.warning .glassmorphism-popup-header {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.9), rgba(217, 119, 6, 0.9));
        }

        /* Info Popup */
        .glassmorphism-popup.info .glassmorphism-popup-header {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(37, 99, 235, 0.9));
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes popupSlideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes iconBounce {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
    </style>

    @stack('scripts')
    
    <script>
        // ملاحظة: تبديل الثيم يُدار مركزياً عبر partials/theme-toggle (مفتاح wahy-theme).
        // زر الشريط الجانبي #sidebarThemeToggle يُربط تلقائياً هناك — أي سكربت admin-theme هنا كان يكسر
        // استمرارية التبديل عبر الصفحات (كان يعود للثيم القديم عند كل تنقّل).

        // Avatar Dropdown Toggle
        (function() {
            const toggleBtn = document.getElementById('avatarToggleBtn');
            const dropdownMenu = document.getElementById('avatarDropdownMenu');
            const container = document.getElementById('avatarDropdownContainer');
            const avatarInput = document.getElementById('avatarUploadInput');

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

            // Avatar Upload
            if (avatarInput) {
                avatarInput.addEventListener('change', function() {
                    if (!this.files || !this.files[0]) return;

                    const formData = new FormData();
                    formData.append('avatar', this.files[0]);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    fetch('{{ route("profile.update-avatar") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            // تحديث جميع صور الأفاتار في الصفحة
                            document.querySelectorAll('#avatarToggleBtn img, #dropdownAvatarImg').forEach(img => {
                                img.src = data.avatar_url;
                            });
                            if (typeof showSuccess === 'function') showSuccess(data.message);
                        } else {
                            if (typeof showError === 'function') showError(data.message);
                        }
                    })
                    .catch(() => {
                        if (typeof showError === 'function') showError('حدث خطأ أثناء رفع الصورة');
                    });

                    this.value = '';
                });
            }
        })();

        // ========================================
        // Glassmorphism Popup Global Functions
        // ========================================

        /**
         * عرض بوب اب زجاجي احترافي
         * @param {string} type - نوع البوب اب: success, error, warning, info, confirm
         * @param {string} icon - الأيقونة (emoji)
         * @param {string} title - العنوان
         * @param {string} message - الرسالة (يمكن أن تحتوي على HTML)
         * @param {array} actions - مصفوفة الأزرار (اختياري)
         */
        function showGlassPopup(type, icon, title, message, actions = null) {
            // إزالة البوب اب الموجود
            const existingPopup = document.querySelector('.glassmorphism-popup-overlay');
            if (existingPopup) {
                existingPopup.remove();
            }

            // إنشاء الخلفية
            const overlay = document.createElement('div');
            overlay.className = 'glassmorphism-popup-overlay';

            // إنشاء البوب اب
            const popup = document.createElement('div');
            popup.className = `glassmorphism-popup ${type}`;

            // بناء محتوى الأزرار
            let actionsHTML = '';
            if (actions) {
                actionsHTML = '<div class="glassmorphism-popup-actions">';
                actions.forEach(action => {
                    actionsHTML += `<button class="glassmorphism-popup-btn glassmorphism-popup-btn-${action.type}" onclick="${action.onclick}">${action.label}</button>`;
                });
                actionsHTML += '</div>';
            } else {
                actionsHTML = '<div class="glassmorphism-popup-actions"><button class="glassmorphism-popup-btn glassmorphism-popup-btn-primary" onclick="closeGlassPopup()">حسناً</button></div>';
            }

            popup.innerHTML = `
                <div class="glassmorphism-popup-header">
                    <div class="glassmorphism-popup-icon">${icon}</div>
                    <h3 class="glassmorphism-popup-title">${title}</h3>
                </div>
                <div class="glassmorphism-popup-body">
                    <p class="glassmorphism-popup-message">${message}</p>
                    ${actionsHTML}
                </div>
            `;

            overlay.appendChild(popup);
            document.body.appendChild(overlay);

            // عرض مع الحركة
            setTimeout(() => {
                overlay.classList.add('active');
            }, 10);

            // الإغلاق عند الضغط على الخلفية
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    closeGlassPopup();
                }
            });
        }

        /**
         * إغلاق البوب اب الزجاجي
         */
        function closeGlassPopup() {
            const overlay = document.querySelector('.glassmorphism-popup-overlay');
            if (overlay) {
                overlay.classList.remove('active');
                setTimeout(() => {
                    overlay.remove();
                }, 300);
            }
        }

        /**
         * عرض رسالة نجاح
         */
        function showSuccess(message, title = 'نجح!') {
            showGlassPopup('success', '✅', title, message);
        }

        /**
         * عرض رسالة خطأ
         */
        function showError(message, title = 'خطأ!') {
            showGlassPopup('error', '❌', title, message);
        }

        /**
         * عرض رسالة تحذير
         */
        function showWarning(message, title = 'تحذير!') {
            showGlassPopup('warning', '⚠️', title, message);
        }

        /**
         * عرض رسالة معلومات
         */
        function showInfo(message, title = 'معلومة') {
            showGlassPopup('info', 'ℹ️', title, message);
        }

        /**
         * عرض رسالة تأكيد
         */
        function showConfirm(message, onConfirm, title = 'تأكيد', confirmText = 'نعم', cancelText = 'إلغاء') {
            const confirmFunctionName = 'confirmAction_' + Date.now();
            window[confirmFunctionName] = function() {
                closeGlassPopup();
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
                // ملاحظة أمنية: لا نستخدم eval(string) — أُزيلت لمنع XSS / حقن أكواد.
                // المتصلون يجب أن يمرروا دالة (function) بدلًا من نص.
                delete window[confirmFunctionName];
            };

            showGlassPopup('warning', '❓', title, message, [
                {
                    label: confirmText,
                    type: 'primary',
                    onclick: `${confirmFunctionName}()`
                },
                {
                    label: cancelText,
                    type: 'secondary',
                    onclick: 'closeGlassPopup()'
                }
            ]);
        }

        // استبدال دالة alert الافتراضية
        window.originalAlert = window.alert;
        window.alert = function(message) {
            showInfo(message, 'إشعار');
        };

        // استبدال دالة confirm - اعتراض النماذج التي تستخدم confirm
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form[onsubmit*="confirm"]').forEach(function(form) {
                var originalOnsubmit = form.getAttribute('onsubmit');
                var match = originalOnsubmit.match(/confirm\(['"](.+?)['"]\)/);
                var message = match ? match[1] : 'هل أنت متأكد؟';
                
                form.removeAttribute('onsubmit');
                
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var currentForm = this;
                    
                    showConfirm(message, function() {
                        currentForm.submit();
                    }, 'تأكيد', 'نعم، تأكيد', 'لا، إلغاء');
                });
            });
        });

        // ========================================
        // تعطيل التحقق الأصلي للمتصفح واستبداله برسائل عربية مخصصة
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            // إضافة novalidate لجميع النماذج لمنع رسائل المتصفح
            document.querySelectorAll('form').forEach(function(form) {
                form.setAttribute('novalidate', '');
            });

            // رسائل التحقق العربية
            function getArabicMessage(input) {
                var type = input.type;
                var name = input.getAttribute('name') || '';
                
                // الحصول على اسم الحقل من label
                var label = '';
                var labelEl = input.closest('.form-group, .mb-3, div')?.querySelector('label, .form-label');
                if (labelEl) {
                    label = labelEl.textContent.replace('*', '').trim();
                }
                if (!label) label = name;

                if (input.validity.valueMissing) {
                    return 'حقل "' + label + '" مطلوب ولا يمكن تركه فارغاً';
                }
                if (input.validity.typeMismatch) {
                    if (type === 'email') return 'يرجى إدخال بريد إلكتروني صحيح';
                    if (type === 'url') return 'يرجى إدخال رابط صحيح يبدأ بـ https://';
                    return 'صيغة "' + label + '" غير صحيحة';
                }
                if (input.validity.tooShort) {
                    return 'حقل "' + label + '" قصير جداً (الحد الأدنى ' + input.minLength + ' حروف)';
                }
                if (input.validity.tooLong) {
                    return 'حقل "' + label + '" طويل جداً (الحد الأقصى ' + input.maxLength + ' حروف)';
                }
                if (input.validity.rangeUnderflow) {
                    return 'قيمة "' + label + '" يجب أن تكون ' + input.min + ' أو أكثر';
                }
                if (input.validity.rangeOverflow) {
                    return 'قيمة "' + label + '" يجب أن تكون ' + input.max + ' أو أقل';
                }
                if (input.validity.patternMismatch) {
                    return 'حقل "' + label + '" لا يتوافق مع الصيغة المطلوبة';
                }
                if (input.validity.stepMismatch) {
                    return 'قيمة "' + label + '" غير مقبولة';
                }
                return 'حقل "' + label + '" يحتوي على خطأ';
            }

            // إزالة رسائل الخطأ السابقة
            function clearValidationErrors(form) {
                form.querySelectorAll('.custom-validation-error').forEach(function(el) {
                    el.remove();
                });
                form.querySelectorAll('.validation-error-border').forEach(function(el) {
                    el.classList.remove('validation-error-border');
                });
            }

            // عرض رسالة خطأ تحت الحقل
            function showFieldError(input, message) {
                input.classList.add('validation-error-border');
                var errorDiv = document.createElement('div');
                errorDiv.className = 'custom-validation-error';
                errorDiv.innerHTML = '<span style="color:#dc2626;font-size:13px;display:flex;align-items:center;gap:6px;margin-top:4px;">' +
                    '<span style="font-size:14px;">⚠️</span> ' + message + '</span>';
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }

            // التحقق عند إرسال النموذج
            document.addEventListener('submit', function(e) {
                var form = e.target;
                if (form.tagName !== 'FORM') return;
                
                clearValidationErrors(form);

                // جمع الحقول غير الصحيحة (فقط المرئية)
                var invalidFields = [];
                form.querySelectorAll('input, select, textarea').forEach(function(input) {
                    if (input.type === 'hidden') return;
                    if (input.offsetParent === null && input.type !== 'radio') return; // غير مرئي
                    
                    if (!input.checkValidity()) {
                        invalidFields.push(input);
                    }
                });

                if (invalidFields.length > 0) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    invalidFields.forEach(function(input) {
                        var message = getArabicMessage(input);
                        showFieldError(input, message);
                    });

                    // التمرير إلى أول حقل خاطئ
                    invalidFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    invalidFields[0].focus();
                }
            }, true); // capture phase to run before other handlers

            // إزالة الخطأ عند الكتابة
            document.addEventListener('input', function(e) {
                var input = e.target;
                if (input.classList.contains('validation-error-border')) {
                    input.classList.remove('validation-error-border');
                    var next = input.nextElementSibling;
                    if (next && next.classList.contains('custom-validation-error')) {
                        next.remove();
                    }
                }
            });
        });
    </script>

    <style>
    .validation-error-border {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
    }
    </style>

    {{-- =========================================================================
         Wahy dark-mode coverage (Admin) — بلوك مركزي مُجمَّع
         يعالج الألوان المُصلَّبة (background: white / #fff / نصوص داكنة) في صفحات
         الأدمن التي تستخدم <style> inline بكلاسات متكرّرة (form-card / filters-card /
         stat-card / *-table / page-header / empty-state / modal ...) وكذلك أدوات
         Tailwind (bg-white / bg-gray-50 / text-gray-* ...). لا يمسّ الشارات الملوّنة
         (gradient banners) لأن خلفياتها تدرّجات وليست أبيض. WCAG AA مضمون بألوان --w-*.
         مصدر متغيّرات --w-* هو partial المشترك theme-toggle (لا يُلمَس هنا).
         ========================================================================= --}}
    <style>
        /* --- حاويات البطاقات/الجداول/اللوحات ذات الخلفية البيضاء المُصلَّبة --- */
        html[data-theme="dark"] .form-card,
        html[data-theme="dark"] .filters-card,
        html[data-theme="dark"] .filter-bar,
        html[data-theme="dark"] .filters-form,
        html[data-theme="dark"] .stat-card,
        html[data-theme="dark"] .stats-grid > *,
        html[data-theme="dark"] .item-card,
        html[data-theme="dark"] .card-item,
        html[data-theme="dark"] .chart-card,
        html[data-theme="dark"] .info-box,
        html[data-theme="dark"] .info-card,
        html[data-theme="dark"] .page-header,
        html[data-theme="dark"] .section-card,
        html[data-theme="dark"] .concept-header,
        html[data-theme="dark"] .concept-item,
        html[data-theme="dark"] .value-header,
        html[data-theme="dark"] .activity-header,
        html[data-theme="dark"] .activity-item,
        html[data-theme="dark"] .questions-section,
        html[data-theme="dark"] .meanings-list,
        html[data-theme="dark"] .concepts-section,
        html[data-theme="dark"] .concepts-list,
        html[data-theme="dark"] .modal-box,
        html[data-theme="dark"] .modal-content,
        html[data-theme="dark"] .av-card,
        html[data-theme="dark"] .av-tile,
        html[data-theme="dark"] .comp-container,
        html[data-theme="dark"] .builder-wrapper,
        html[data-theme="dark"] [class*="-card"],
        html[data-theme="dark"] [class*="-table"],
        html[data-theme="dark"] [class*="-panel"] {
            background: var(--w-card) !important;
            color: var(--w-text) !important;
            border-color: var(--w-border) !important;
        }

        /* رؤوس الجداول ذات الخلفية الفاتحة (#f8fafc / #f1f5f9 / bg-gray-50) */
        html[data-theme="dark"] [class*="-table"] th,
        html[data-theme="dark"] .users-table th,
        html[data-theme="dark"] .concepts-table th,
        html[data-theme="dark"] .activities-table th,
        html[data-theme="dark"] .data-table th {
            background: rgba(255, 255, 255, 0.04) !important;
            color: var(--w-text) !important;
            border-color: var(--w-border) !important;
        }
        html[data-theme="dark"] [class*="-table"] td,
        html[data-theme="dark"] [class*="-table"] tr {
            border-color: var(--w-border) !important;
            color: var(--w-text) !important;
        }

        /* النصوص الداكنة المُصلَّبة (#1e293b / #334155 / #0f172a / #475569 ...) */
        html[data-theme="dark"] .form-label,
        html[data-theme="dark"] .filter-label,
        html[data-theme="dark"] .section-title,
        html[data-theme="dark"] .page-title,
        html[data-theme="dark"] .card-title,
        html[data-theme="dark"] .stat-value,
        html[data-theme="dark"] .value-title,
        html[data-theme="dark"] .meta-value,
        html[data-theme="dark"] .user-name,
        html[data-theme="dark"] .empty-title,
        html[data-theme="dark"] .empty-state h3,
        html[data-theme="dark"] .concept-header h1,
        html[data-theme="dark"] .concept-header h2 {
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] .stat-label,
        html[data-theme="dark"] .meta-label,
        html[data-theme="dark"] .user-email,
        html[data-theme="dark"] .empty-desc,
        html[data-theme="dark"] .value-description,
        html[data-theme="dark"] .form-help,
        html[data-theme="dark"] .filter-help {
            color: var(--w-text-muted) !important;
        }

        /* حالات فارغة/صناديق ذات خلفية بيضاء */
        html[data-theme="dark"] .empty-state,
        html[data-theme="dark"] .empty-canvas {
            background: var(--w-card) !important;
            color: var(--w-text-muted) !important;
            border-color: var(--w-border) !important;
        }

        /* التبويبات (bank-tab.active خلفيتها بيضاء) */
        html[data-theme="dark"] .bank-tab.active,
        html[data-theme="dark"] .type-option.active {
            background: var(--w-card) !important;
            color: var(--color-primary) !important;
        }

        /* --- أدوات Tailwind الشائعة (CDN) في صفحات الأدمن --- */
        html[data-theme="dark"] .bg-white {
            background-color: var(--w-card) !important;
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] .bg-gray-50,
        html[data-theme="dark"] .bg-gray-100,
        html[data-theme="dark"] .bg-slate-50,
        html[data-theme="dark"] .bg-slate-100 {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }
        html[data-theme="dark"] .text-gray-900,
        html[data-theme="dark"] .text-gray-800,
        html[data-theme="dark"] .text-gray-700,
        html[data-theme="dark"] .text-slate-900,
        html[data-theme="dark"] .text-slate-800,
        html[data-theme="dark"] .text-slate-700 {
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] .text-gray-600,
        html[data-theme="dark"] .text-gray-500,
        html[data-theme="dark"] .text-gray-400,
        html[data-theme="dark"] .text-slate-600,
        html[data-theme="dark"] .text-slate-500,
        html[data-theme="dark"] .text-slate-400 {
            color: var(--w-text-muted) !important;
        }
        html[data-theme="dark"] .border,
        html[data-theme="dark"] .border-gray-100,
        html[data-theme="dark"] .border-gray-200,
        html[data-theme="dark"] .border-slate-100,
        html[data-theme="dark"] .border-slate-200,
        html[data-theme="dark"] .divide-gray-100 > * + *,
        html[data-theme="dark"] .divide-gray-200 > * + * {
            border-color: var(--w-border) !important;
        }
        /* صفوف الجداول hover في Tailwind */
        html[data-theme="dark"] .hover\:bg-gray-50:hover {
            background-color: rgba(255, 255, 255, 0.06) !important;
        }

        /* الحفاظ على النص الأبيض فوق البانرات المتدرّجة (لا نلمسها) */
        html[data-theme="dark"] [class*="bg-gradient"] .text-white,
        html[data-theme="dark"] [class*="bg-gradient"] {
            color: #ffffff;
        }

        /* --- قائمة الأفاتار المنسدلة في الهيدر (خلفية بيضاء inline) --- */
        html[data-theme="dark"] #avatarDropdownMenu {
            background: var(--w-card) !important;
        }
        html[data-theme="dark"] #avatarDropdownMenu a,
        html[data-theme="dark"] #avatarDropdownMenu label,
        html[data-theme="dark"] #avatarDropdownMenu button {
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] #avatarDropdownMenu a:hover,
        html[data-theme="dark"] #avatarDropdownMenu label:hover {
            background: rgba(255, 255, 255, 0.06) !important;
        }
        /* الفاصل داخل القائمة (كان #e2e8f0) */
        html[data-theme="dark"] #avatarDropdownMenu > div > div[style*="height: 1px"] {
            background: var(--w-border) !important;
        }

        /* --- (school-admin round2) مقارنة الاستبيانات عبر layouts.admin ---
           صفحة school-admin/surveys/comparison تُضمّن partials.survey-comparison المشترك.
           .cmp-card تُعتَّم أصلاً عبر قاعدة [class*="-card"] أعلاه (خلفيتها من كلاس white)
           => عناوين h2/h3 المُصلَّبة inline (#1e293b) تصبح داكنة-على-داكنة وتختفي.
           نفتّحها من هنا (لا نلمس الـpartial المشترك). نطابق كلتا صيغتي المسافة. */
        html[data-theme="dark"] .cmp-card [style*="color: #1e293b"],
        html[data-theme="dark"] .cmp-card [style*="color:#1e293b"] { color: var(--w-text) !important; }
    </style>

    {{-- =========================================================================
         Wahy dark-mode round2 — إصلاح ارتدادات مؤكّدة (مُدقّق خصمي)
         المبدأ: لا نص فاتح على خلفية فاتحة، ولا نص داكن على خلفية داكنة.
         نعالج كل زوج (خلفية+نص) معاً. الأولوية للجزر الفاتحة التي أخفت عناصرها.
         ========================================================================= --}}
    <style>
        /* --- (0) الحل الأمثل: متغيّرات الاحتياطي التي تستعملها صفحات الأدمن ---
           عشرات المواضع تكتب var(--text-primary, #1e293b) / var(--card-bg, white)
           بدون تعريف المتغيّر، فيسقط على الاحتياطي الداكن/الأبيض. نعرّفها هنا داكنةً
           فيُحلّ education-levels + online-users + المودالات دفعةً واحدة، مع بقاء
           كل زوج متّسقاً: خلفية --card-bg داكنة + نص --text-* فاتح. */
        html[data-theme="dark"] {
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --card-bg: #1e293b;
        }

        /* --- (1) صناديق بخلفية بيضاء/فاتحة مُصلَّبة inline (values/index 244+248، landing 283/295/308) ---
           نعتّم الحاوية ونفتّح نصّها معاً. ونفتّح النصوص الداكنة inline *داخل هذه الصناديق فقط*
           (لا نلمس النص الداكن على خلفيات تبقى فاتحة في مكان آخر = منع ارتداد فاتح-على-فاتح). */
        html[data-theme="dark"] [style*="background: white"],
        html[data-theme="dark"] [style*="background:white"],
        html[data-theme="dark"] [style*="background: #fff"],
        html[data-theme="dark"] [style*="background:#fff"],
        html[data-theme="dark"] [style*="background: #f8fafc"],
        html[data-theme="dark"] [style*="background:#f8fafc"] {
            background: var(--w-card) !important;
            color: var(--w-text) !important;
            border-color: var(--w-border) !important;
        }
        /* نصوص العناوين/الفقرات داخل هذه الصناديق المعتَّمة (كانت داكنة inline أو ترث) */
        html[data-theme="dark"] [style*="background: white"] h3,
        html[data-theme="dark"] [style*="background:white"] h3,
        html[data-theme="dark"] [style*="background: white"] h4,
        html[data-theme="dark"] [style*="background: #f8fafc"] h4,
        html[data-theme="dark"] [style*="background: white"] [style*="color: #1e293b"],
        html[data-theme="dark"] [style*="background: white"] [style*="color:#1e293b"],
        html[data-theme="dark"] [style*="background: #f8fafc"] [style*="color: #475569"],
        html[data-theme="dark"] [style*="background: #f8fafc"] [style*="color:#475569"] {
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] [style*="background: white"] p,
        html[data-theme="dark"] [style*="background: white"] [style*="color: #64748b"],
        html[data-theme="dark"] [style*="background: white"] [style*="color:#64748b"],
        html[data-theme="dark"] [style*="background: #f8fafc"] ul,
        html[data-theme="dark"] [style*="background: #f8fafc"] [style*="color: #64748b"],
        html[data-theme="dark"] [style*="background: #f8fafc"] [style*="color:#64748b"] {
            color: var(--w-text-muted) !important;
        }

        /* --- (2) landing-page: كلاسات داخلية بخلفية فاتحة في <style> الصفحة --- */
        html[data-theme="dark"] .tabs-container,
        html[data-theme="dark"] .tabs-header,
        html[data-theme="dark"] .tab-content {
            background: var(--w-card) !important;
            color: var(--w-text) !important;
            border-color: var(--w-border) !important;
        }

        /* --- (4) صناديق التنبيه الصفراء الشاحبة (#fffbeb=theme:22، #fff3cd=badges) ---
           هذه هويّة تنبيه تبقى صفراء فاتحة، لكن قاعدة (1) قد تلتقطها عبر النمط الجزئي
           "#fff" فتُعتّمها وتترك نصّها الداكن inline (مثل #856404) داكناً على داكن.
           لذا نُعيد تثبيت خلفيتها الصفراء ونُثبّت نصّها داكناً (زوج: أصفر فاتح + نص داكن).
           هذه القاعدة *بعد* (1) في ترتيب المصدر فتتغلّب عند تساوي التخصيص. */
        html[data-theme="dark"] [style*="background: #fffbeb"],
        html[data-theme="dark"] [style*="background:#fffbeb"] {
            background: #fffbeb !important;
            color: #1e293b !important;
        }
        html[data-theme="dark"] [style*="background: #fff3cd"],
        html[data-theme="dark"] [style*="background:#fff3cd"] {
            background: #fff3cd !important;
            color: #856404 !important;
        }
        html[data-theme="dark"] [style*="background: #fffbeb"] *,
        html[data-theme="dark"] [style*="background:#fffbeb"] *,
        html[data-theme="dark"] [style*="background: #fff3cd"] *,
        html[data-theme="dark"] [style*="background:#fff3cd"] * {
            color: #1e293b !important;
        }
    </style>

    <!-- Real-Time Messages System -->
    <script src="{{ asset('js/messages-realtime.js') }}"></script>

    <!-- Wahy Rich Editor (auto-attaches to [data-rich-editor]) -->
    <script>
        window.WAHY_EDITOR_UPLOAD_URL = "{{ route('editor.upload-image') }}";
    </script>
    <script src="{{ asset('js/rich-editor.js') }}" defer></script>

    @stack('after-content')
</body>
</html>
