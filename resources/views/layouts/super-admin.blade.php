<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.head-meta')
    @include('partials.theme-vars')
    <title>@yield('title', 'لوحة المدير العام - بناء القيم')</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/super-admin-glass.css') }}">
    <style>
        /* ===================================================================
           Wahy dark-mode coverage (super-admin)
           بلوك مُجمَّع يعالج فجوات الوضع الليلي للصفحات التي تستخدم هذا اللايوت
           (schools / content-management) والتي تكتب ألواناً مُصلَّبة inline.
           جراحي: مقصور على #sa-main-content فقط ولا يمسّ الشريط/الهيدر (يستعملان var).
           =================================================================== */
        html[data-theme="dark"] #sa-main-content {
            /* عناوين ونصوص الصفحة المكتوبة inline بألوان داكنة تصبح غير مرئية على الخلفية الداكنة */
            --sa-dark-heading: #F1F5F9;
            --sa-dark-body: #CBD5E1;
            --sa-dark-muted: #94A3B8;
        }

        /* العناوين الرئيسية والفرعية للصفحة (h1/h2/h3/h4) الموضوعة مباشرة فوق خلفية اللايوت الداكنة */
        html[data-theme="dark"] #sa-main-content > div > .d-flex h1,
        html[data-theme="dark"] #sa-main-content > div > div > div > h1,
        html[data-theme="dark"] #sa-main-content h1[style*="#1a202c"],
        html[data-theme="dark"] #sa-main-content h2[style*="#1a202c"],
        html[data-theme="dark"] #sa-main-content h3[style*="#2d3748"],
        html[data-theme="dark"] #sa-main-content h3[style*="#1a202c"],
        html[data-theme="dark"] #sa-main-content h4[style*="#2d3748"] {
            color: var(--sa-dark-heading) !important;
        }

        /* الفقرات الوصفية المكتوبة بلون رمادي فاتح (#718096) تحت العنوان مباشرة على الخلفية الداكنة */
        html[data-theme="dark"] #sa-main-content > div > div > div > p[style*="#718096"] {
            color: var(--sa-dark-muted) !important;
        }

        /* البطاقات البيضاء / الرمادية الفاتحة (background: white | #f7fafc) تبقى فاتحة في الوضع الليلي
           فتكسر اتساق الثيم؛ نحوّلها إلى أسطح داكنة زجاجية مع إبقاء تباين النص سليماً. */
        html[data-theme="dark"] #sa-main-content div[style*="background: white"],
        html[data-theme="dark"] #sa-main-content div[style*="background:#fff"],
        html[data-theme="dark"] #sa-main-content div[style*="background: #f7fafc"],
        html[data-theme="dark"] #sa-main-content div[style*="background:#f7fafc"] {
            background: rgba(30, 41, 59, 0.75) !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35) !important;
        }

        /* النصوص الداكنة داخل تلك البطاقات (عناوين/نصوص/رمادي) تصبح فاتحة لتحقيق تباين AA */
        html[data-theme="dark"] #sa-main-content [style*="color: #1a202c"],
        html[data-theme="dark"] #sa-main-content [style*="color:#1a202c"],
        html[data-theme="dark"] #sa-main-content [style*="color: #2d3748"],
        html[data-theme="dark"] #sa-main-content [style*="color:#2d3748"] {
            color: var(--sa-dark-heading) !important;
        }
        html[data-theme="dark"] #sa-main-content [style*="color: #718096"],
        html[data-theme="dark"] #sa-main-content [style*="color:#718096"],
        html[data-theme="dark"] #sa-main-content [style*="color: #4a5568"],
        html[data-theme="dark"] #sa-main-content [style*="color:#4a5568"],
        html[data-theme="dark"] #sa-main-content [style*="color: #475569"],
        html[data-theme="dark"] #sa-main-content [style*="color:#475569"] {
            color: var(--sa-dark-muted) !important;
        }

        /* حقول الإدخال/القوائم البيضاء ذات الحدود الفاتحة تُوحَّد مع السطح الداكن */
        html[data-theme="dark"] #sa-main-content input[style*="border"],
        html[data-theme="dark"] #sa-main-content select[style*="border"],
        html[data-theme="dark"] #sa-main-content textarea[style*="border"] {
            background: rgba(15, 23, 42, 0.6) !important;
            color: var(--sa-dark-heading) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }
        html[data-theme="dark"] #sa-main-content input::placeholder,
        html[data-theme="dark"] #sa-main-content textarea::placeholder {
            color: var(--sa-dark-muted) !important;
        }

        /* الحدود الفاصلة الفاتحة (#f7fafc / #e2e8f0) داخل البطاقات تُخفَّف لتناسب الوضع الليلي */
        html[data-theme="dark"] #sa-main-content [style*="border-bottom: 3px solid #f7fafc"],
        html[data-theme="dark"] #sa-main-content [style*="border-top: 2px solid #e2e8f0"] {
            border-color: rgba(255, 255, 255, 0.12) !important;
        }
    </style>
    @include('partials.theme-toggle')
</head>
<body>
    @include('partials.flash')
    <a href="#sa-main-content" class="skip-to-content" style="position:absolute;top:-40px;right:0;background:#1e293b;color:#fff;padding:8px 16px;z-index:9999;text-decoration:none;border-radius:0 0 0 8px;" onfocus="this.style.top='0'" onblur="this.style.top='-40px'">تخطي إلى المحتوى الرئيسي</a>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="{{ route('super-admin.dashboard') }}" class="admin-logo">
                    @include('partials.brand')
                </a>
            </div>
            
            <nav class="admin-nav">
                <div class="admin-nav-section">
                    <div class="admin-nav-title">لوحات التحكم</div>
                    <ul class="admin-nav-list">
                        <li class="admin-nav-item">
                            <a href="{{ route('admin.dashboard') }}" 
                               class="admin-nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                                <span class="admin-nav-icon">⚙️</span>
                                <span class="admin-nav-text">لوحة الأدمن الرئيسية</span>
                            </a>
                        </li>
                        <li class="admin-nav-item">
                            <a href="{{ route('super-admin.dashboard') }}" 
                               class="admin-nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                                <span class="admin-nav-icon">⚡</span>
                                <span class="admin-nav-text">لوحة السوبر أدمن</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="admin-nav-section">
                    <div class="admin-nav-title">محرر الصفحة الرئيسية</div>
                    <ul class="admin-nav-list">
                        <li class="admin-nav-item">
                            <a href="/" 
                               class="admin-nav-link"
                               target="_blank">
                                <span class="admin-nav-icon">✏️</span>
                                <span class="admin-nav-text">افتح المحرر</span>
                            </a>
                        </li>
                    </ul>
                    <div style="padding: 15px; background: rgba(102, 126, 234, 0.1); border-radius: 8px; margin: 10px 15px; border-right: 3px solid var(--color-primary, #667eea);">
                        <p style="margin: 0 0 8px; font-size: 13px; color: var(--color-primary, #667eea); font-weight: 600;">💡 كيف تستخدم المحرر؟</p>
                        <ol style="margin: 0; padding-right: 20px; font-size: 12px; color: var(--text-secondary, #cbd5e1); line-height: 1.6;">
                            <li>اضغط على "افتح المحرر" أعلاه</li>
                            <li>اضغط على الزر العائم ✏️ أسفل اليسار</li>
                            <li>ابدأ التعديل مباشرة!</li>
                        </ol>
                    </div>
                </div>
                
                <div class="admin-nav-section">
                    <div class="admin-nav-title">الإدارة</div>
                    <ul class="admin-nav-list">
                        {{-- <li class="admin-nav-item">
                            <a href="{{ route('super-admin.schools') }}" 
                               class="admin-nav-link {{ request()->routeIs('super-admin.schools') ? 'active' : '' }}">
                                <span class="admin-nav-icon">🏫</span>
                                <span class="admin-nav-text">المدارس</span>
                            </a>
                        </li>
                        <li class="admin-nav-item">
                            <a href="{{ route('super-admin.content-management') }}" 
                               class="admin-nav-link {{ request()->routeIs('super-admin.content-management') ? 'active' : '' }}">
                                <span class="admin-nav-icon">📚</span>
                                <span class="admin-nav-text">إدارة المحتوى</span>
                            </a>
                        </li> --}}
                        <li class="admin-nav-item">
                            <a href="{{ route('super-admin.excel-management') }}" 
                               class="admin-nav-link {{ request()->routeIs('super-admin.excel-management') ? 'active' : '' }}">
                                <span class="admin-nav-icon">📊</span>
                                <span class="admin-nav-text">إدارة Excel</span>
                            </a>
                        </li>
                        <li class="admin-nav-item">
                            <a href="{{ route('admin.activity-bank.index') }}" 
                               class="admin-nav-link {{ request()->routeIs('admin.activity-bank.*') ? 'active' : '' }}">
                                <span class="admin-nav-icon">📚</span>
                                <span class="admin-nav-text">بنك الأنشطة</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="admin-nav-section">
                    <div class="admin-nav-title">الأمان</div>
                    <ul class="admin-nav-list">
                        <li class="admin-nav-item">
                            <a href="{{ route('super-admin.backups') }}" 
                               class="admin-nav-link {{ request()->routeIs('super-admin.backups') ? 'active' : '' }}">
                                <span class="admin-nav-icon">💾</span>
                                <span class="admin-nav-text">النسخ الاحتياطي</span>
                            </a>
                        </li>
                        <li class="admin-nav-item">
                            <a href="{{ route('super-admin.activity-logs') }}" 
                               class="admin-nav-link {{ request()->routeIs('super-admin.activity-logs') ? 'active' : '' }}">
                                <span class="admin-nav-icon">📋</span>
                                <span class="admin-nav-text">سجل الأنشطة</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="admin-nav-section">
                    <div class="admin-nav-title">التطوير</div>
                    <ul class="admin-nav-list">
                        <li class="admin-nav-item">
                            <a href="{{ route('super-admin.api-documentation') }}" 
                               class="admin-nav-link {{ request()->routeIs('super-admin.api-documentation') ? 'active' : '' }}">
                                <span class="admin-nav-icon">📖</span>
                                <span class="admin-nav-text">توثيق API</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="admin-nav-section">
                    <div class="admin-nav-title">الاختبارات</div>
                    <ul class="admin-nav-list">
                        <li class="admin-nav-item">
                            <a href="{{ route('school-admin.test-notifications') }}" 
                               class="admin-nav-link {{ request()->routeIs('school-admin.test-notifications') ? 'active' : '' }}">
                                <span class="admin-nav-icon">🔔</span>
                                <span class="admin-nav-text">اختبار الإشعارات</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="admin-nav-section">
                    <div class="admin-nav-title">الإشعارات</div>
                    <ul class="admin-nav-list">
                        <li class="admin-nav-item">
                            <a href="{{ route('notifications.index') }}" 
                               class="admin-nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                                <span class="admin-nav-icon">🔔</span>
                                <span class="admin-nav-text">إدارة الإشعارات</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                {{-- <div class="admin-nav-section">
                    <div class="admin-nav-title">الإعدادات</div>
                    <ul class="admin-nav-list">
                        <li class="admin-nav-item">
                            <a href="{{ route('super-admin.settings') }}" 
                               class="admin-nav-link {{ request()->routeIs('super-admin.settings') ? 'active' : '' }}">
                                <span class="admin-nav-icon">⚙️</span>
                                <span class="admin-nav-text">الإعدادات العامة</span>
                            </a>
                        </li>
                    </ul>
                </div> --}}
            </nav>

            <!-- تبديل الأدوار -->
            @include('components.role-switcher')

            <div class="admin-sidebar-footer">
                <div class="admin-user-info" style="position: relative;" id="saAvatarDropdownContainer">
                    <div class="admin-user-avatar" id="saAvatarToggleBtn" style="cursor: pointer; overflow: hidden; padding: 0;">
                        <img src="{{ auth()->user()->avatar_url }}" alt="صورة" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    </div>
                    <div class="admin-user-details">
                        <div class="admin-user-name">{{ auth()->user()->name }}</div>
                        <div class="admin-user-role">مدير عام</div>
                    </div>

                    <!-- Dropdown Menu -->
                    <div id="saAvatarDropdownMenu" style="display: none; position: absolute; bottom: calc(100% + 10px); right: 0; width: 260px; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); z-index: 1000; overflow: hidden;">
                        <div style="padding: 8px;">
                            <label for="saAvatarUploadInput" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; cursor: pointer; transition: background 0.2s; color: #334155; font-weight: 600; font-size: 14px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <span>📷</span> تغيير الصورة
                            </label>
                            <input type="file" id="saAvatarUploadInput" accept="image/jpeg,image/png,image/jpg,image/webp" style="display: none;">

                            <a href="{{ route('admin.settings') }}" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; text-decoration: none; transition: background 0.2s; color: #334155; font-weight: 600; font-size: 14px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <span>⚙️</span> الإعدادات
                            </a>

                            <div style="height: 1px; background: #e2e8f0; margin: 4px 16px;"></div>

                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; width: 100%; border: none; background: transparent; cursor: pointer; transition: background 0.2s; color: #ef4444; font-weight: 600; font-size: 14px; font-family: inherit;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                    <span>🚪</span> تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <button class="sidebar-theme-toggle" id="sidebarThemeToggle">
                    <span class="icon-sun">☀️</span>
                    <span class="icon-moon">🌙</span>
                    <span>تبديل الوضع</span>
                </button>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="admin-content">
            <header class="admin-header">
                <div class="admin-header-content">
                    <div class="admin-header-title">
                        <h1>@yield('page-icon', '⚡') @yield('title', 'لوحة المدير العام')</h1>
                        <p class="admin-header-subtitle">@yield('subtitle', 'إدارة كاملة لمنصة بناء القيم')</p>
                    </div>
                    <div class="admin-header-actions" style="display: flex; align-items: center; gap: 12px;">
                        <!-- Notification Counters -->
                        <a href="{{ route('admin.users.index') }}" style="text-decoration: none; display: flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.15); padding: 8px 14px; border-radius: 10px; color: white; font-weight: 600; font-size: 13px; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                            <span>👥</span>
                            <span><span data-live="registration_requests_pending">{{ $newUsersCount ?? 0 }}</span> مستخدم جديد</span>
                        </a>
                        <a href="{{ route('admin.pending-submissions') }}" style="text-decoration: none; display: flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.15); padding: 8px 14px; border-radius: 10px; color: white; font-weight: 600; font-size: 13px; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                            <span>📝</span>
                            <span><span data-live="activity_submissions_pending">{{ $newSubmissionsCount ?? 0 }}</span> تقديم جديد</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="admin-logout-btn">
                                <span>تسجيل الخروج</span>
                                <span>🚪</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>
            
            <main class="dashboard-container" id="sa-main-content">
                @yield('content')
            </main>
        </div>
    </div>
    
    <script>
        // تبديل الثيم يُدار مركزياً عبر partials/theme-toggle (مفتاح wahy-theme).
        // زر #sidebarThemeToggle يُربط هناك تلقائياً. الافتراضي هنا داكن (data-theme="dark" على <html>)
        // ويحترمه الـ FOUC guard عند غياب تفضيل محفوظ.

        // Mobile menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.admin-sidebar');
        
        menuToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Avatar Dropdown Toggle
        (function() {
            const toggleBtn = document.getElementById('saAvatarToggleBtn');
            const dropdownMenu = document.getElementById('saAvatarDropdownMenu');
            const container = document.getElementById('saAvatarDropdownContainer');
            const avatarInput = document.getElementById('saAvatarUploadInput');

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

            if (avatarInput) {
                avatarInput.addEventListener('change', function() {
                    if (!this.files || !this.files[0]) return;
                    const formData = new FormData();
                    formData.append('avatar', this.files[0]);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}');

                    fetch('{{ route("profile.update-avatar") }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelectorAll('#saAvatarToggleBtn img').forEach(img => img.src = data.avatar_url);
                            alert(data.message);
                        } else {
                            alert(data.message || 'حدث خطأ');
                        }
                    })
                    .catch(() => alert('حدث خطأ أثناء رفع الصورة'));
                    this.value = '';
                });
            }
        })();
    </script>

    @include('partials.live-updates')
</body>
</html>

