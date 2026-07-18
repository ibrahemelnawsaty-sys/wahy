@php
    // جلب إعدادات الثيم من قاعدة البيانات
    $fontFamily = setting('font_family', 'IBM Plex Sans Arabic');
    $primaryColor = setting('primary_color', '#667eea');
    $secondaryColor = setting('secondary_color', '#764ba2');
    $textColor = setting('text_color', '#1e293b');
    $backgroundColor = setting('background_color', '#ffffff');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="{{ $branding['site_theme'] ?? 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.head-meta')
    <title>@yield('title', 'لوحة المعلم - بناء القيم')</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --color-primary: {{ $primaryColor }};
            --color-secondary: {{ $secondaryColor }};
            --color-text: {{ $textColor }};
            --color-bg: {{ $backgroundColor }};
            --font-family: '{{ $fontFamily }}', sans-serif;
        }
        
        * { 
            font-family: var(--font-family); 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
            min-height: 100vh;
            position: relative;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
            pointer-events: none;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Layout */
        .teacher-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }
        
        /* Sidebar */
        .teacher-sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(40px) saturate(180%);
            border-left: 1px solid rgba(255, 255, 255, 0.2);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-logo {
            text-align: center;
            padding: 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .logo-icon {
            font-size: 48px;
            margin-bottom: 8px;
        }
        
        .logo-text {
            font-size: 20px;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar-user {
            padding: 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-size: 16px;
            font-weight: 700;
            color: white;
            margin-bottom: 4px;
        }
        
        .user-role {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 14px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-4px);
        }
        
        .nav-item.active {
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        .nav-icon {
            font-size: 22px;
            width: 28px;
            text-align: center;
        }
        
        .sidebar-footer {
            margin-top: auto;
        }
        
        .logout-btn {
            width: 100%;
            padding: 14px;
            background: rgba(239, 68, 68, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.4);
            transform: translateY(-2px);
        }
        
        /* Main Content */
        .teacher-main {
            margin-right: 280px;
            flex: 1;
            padding: 32px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .teacher-sidebar {
                transform: translateX(100%);
                transition: transform 0.3s ease;
                z-index: 1000;
            }
            
            .teacher-sidebar.open {
                transform: translateX(0);
            }
            
            .teacher-main {
                margin-right: 0;
            }
            
            .mobile-menu-btn {
                display: block;
                position: fixed;
                bottom: 20px;
                left: 20px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                border: none;
                font-size: 24px;
                cursor: pointer;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
                z-index: 999;
            }
        }
        
        @media (min-width: 1025px) {
            .mobile-menu-btn {
                display: none;
            }
        }

        /* ============================================================
           Wahy dark-mode coverage — لوحة المعلّم
           بلوك مُجمَّع جراحي: يجعل "كل العناصر تتغيّر" في الوضع الليلي دون
           لمس أي partial مشترك ولا إضافة سكربت جديد. يعتمد متغيّرات النظام
           الموحّد (--w-*) القادمة من partials/theme-toggle.
           يعالج: خلفية الـ body المتدرّجة، والبطاقات ذات background:white/فاتح
           المضمَّنة inline، والنصوص الداكنة (slate/gray) التي تختفي على خلفية
           داكنة، وحقول الإدخال. الألوان العلامية (بنفسجي/أخضر/برتقالي) تبقى
           كما هي لأنها تحقّق تبايناً كافياً على الداكن.
           ============================================================ */

        /* خلفية الصفحة: نُلغي المتدرّج ونستبدله بلون داكن صلب */
        html[data-theme="dark"] body {
            background: var(--w-bg) !important;
            color: var(--w-text);
        }
        html[data-theme="dark"] body::before { opacity: .35; }

        /* الشريط الجانبي الزجاجي: تعتيمه ليقرأ نصّه الأبيض بوضوح */
        html[data-theme="dark"] .teacher-sidebar {
            background: rgba(17, 24, 39, 0.72) !important;
            border-left-color: var(--w-border) !important;
        }
        html[data-theme="dark"] .sidebar-logo,
        html[data-theme="dark"] .sidebar-user {
            background: rgba(255, 255, 255, 0.06) !important;
            border-color: var(--w-border) !important;
        }
        html[data-theme="dark"] .nav-item.active {
            background: rgba(255, 255, 255, 0.14) !important;
        }

        /* البطاقات ذات الخلفية البيضاء/الفاتحة المضمَّنة inline */
        html[data-theme="dark"] [style*="background: white"],
        html[data-theme="dark"] [style*="background:white"],
        html[data-theme="dark"] [style*="background: #fff"],
        html[data-theme="dark"] [style*="background:#fff"],
        html[data-theme="dark"] [style*="background: #ffffff"],
        html[data-theme="dark"] [style*="background:#ffffff"],
        html[data-theme="dark"] [style*="background: #f8fafc"],
        html[data-theme="dark"] [style*="background: #f7fafc"],
        html[data-theme="dark"] [style*="background: #fafbfc"],
        html[data-theme="dark"] [style*="background: #fafafa"],
        html[data-theme="dark"] [style*="background: #f1f5f9"],
        html[data-theme="dark"] [style*="background: #edf2f7"],
        html[data-theme="dark"] [style*="background: #f0f2f5"],
        html[data-theme="dark"] [style*="background: #f0f0f0"] {
            background: var(--w-card) !important;
            border-color: var(--w-border) !important;
        }
        /* حدود/فواصل رمادية فاتحة مضمَّنة inline */
        html[data-theme="dark"] [style*="#e2e8f0"] {
            border-color: var(--w-border) !important;
        }

        /* النصوص الداكنة (slate/gray) التي تختفي على البطاقات الداكنة */
        html[data-theme="dark"] [style*="color: #0f172a"],
        html[data-theme="dark"] [style*="color: #1a202c"],
        html[data-theme="dark"] [style*="color: #1e293b"],
        html[data-theme="dark"] [style*="color: #2d3748"],
        html[data-theme="dark"] [style*="color: #334155"] {
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] [style*="color: #475569"],
        html[data-theme="dark"] [style*="color: #4a5568"],
        html[data-theme="dark"] [style*="color: #64748b"],
        html[data-theme="dark"] [style*="color: #718096"],
        html[data-theme="dark"] [style*="color: #94a3b8"],
        html[data-theme="dark"] [style*="color: #a0aec0"] {
            color: var(--w-text-muted) !important;
        }

        /* حقول الإدخال المضمَّنة inline بحدود فاتحة */
        html[data-theme="dark"] input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="range"]),
        html[data-theme="dark"] select,
        html[data-theme="dark"] textarea {
            background: rgba(255, 255, 255, 0.05) !important;
            color: var(--w-text) !important;
            border-color: var(--w-border) !important;
        }
        html[data-theme="dark"] input::placeholder,
        html[data-theme="dark"] textarea::placeholder {
            color: var(--w-text-muted) !important;
        }

        /* قائمة الأفاتار المنسدلة (خلفية بيضاء صريحة) */
        html[data-theme="dark"] #tchAvatarDropdownMenu {
            background: var(--w-card) !important;
            box-shadow: var(--w-shadow) !important;
        }

        /* ---- فئات المكوّنات المشتركة عبر صفحات المعلّم (معرّفة في <style> كل صفحة
           بألوان مُصلَّبة) — تغطية مُجمَّعة بدل تكرار البلوك في كل ملف صفحة. ---- */
        html[data-theme="dark"] .form-card,
        html[data-theme="dark"] .type-card,
        html[data-theme="dark"] .stat-mini,
        html[data-theme="dark"] .team-card,
        html[data-theme="dark"] .modal-box,
        html[data-theme="dark"] .modal-card,
        html[data-theme="dark"] .ab-modal-card,
        html[data-theme="dark"] .question-item,
        html[data-theme="dark"] .section-card,
        html[data-theme="dark"] .report-card,
        html[data-theme="dark"] .preview-card,
        html[data-theme="dark"] .back-link,
        html[data-theme="dark"] .btn-back {
            background: var(--w-card) !important;
            border-color: var(--w-border) !important;
            box-shadow: var(--w-shadow) !important;
            color: var(--w-text) !important;
        }
        /* أسطح داخلية أفتح (حقول/بطاقات ثانوية) */
        html[data-theme="dark"] .q-card,
        html[data-theme="dark"] .qbuilder,
        html[data-theme="dark"] .media-upload,
        html[data-theme="dark"] .member-item,
        html[data-theme="dark"] .member-tile,
        html[data-theme="dark"] .q-type-select,
        html[data-theme="dark"] .q-correct,
        html[data-theme="dark"] .info-item {
            background: rgba(255, 255, 255, 0.05) !important;
            border-color: var(--w-border) !important;
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] .member-item:hover,
        html[data-theme="dark"] .member-item.selected,
        html[data-theme="dark"] .back-link:hover,
        html[data-theme="dark"] .btn-back:hover {
            background: rgba(255, 255, 255, 0.10) !important;
        }
        /* أزرار/شرائح ثانوية بخلفية رمادية فاتحة ونص داكن */
        html[data-theme="dark"] .btn-secondary,
        html[data-theme="dark"] .ab-btn-secondary,
        html[data-theme="dark"] .chip,
        html[data-theme="dark"] .bank-tab {
            background: rgba(255, 255, 255, 0.08) !important;
            color: var(--w-text) !important;
            border-color: var(--w-border) !important;
        }
        html[data-theme="dark"] .bank-tab.active {
            background: var(--w-card) !important;
            color: #a5b4fc !important;
        }
        /* عناوين/تسميات هذه المكوّنات */
        html[data-theme="dark"] .form-card h1,
        html[data-theme="dark"] .form-card h2,
        html[data-theme="dark"] .type-card .title,
        html[data-theme="dark"] .team-name,
        html[data-theme="dark"] .stat-mini .value,
        html[data-theme="dark"] .member-name,
        html[data-theme="dark"] .section-head-title,
        html[data-theme="dark"] .modal-title,
        html[data-theme="dark"] .field label,
        html[data-theme="dark"] .ab-field label,
        html[data-theme="dark"] .q-label {
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] .stat-mini .label,
        html[data-theme="dark"] .team-desc,
        html[data-theme="dark"] .modal-text,
        html[data-theme="dark"] .media-upload .text,
        html[data-theme="dark"] .image-caption,
        html[data-theme="dark"] .q-opt-num,
        html[data-theme="dark"] .member-role,
        html[data-theme="dark"] .stat-label {
            color: var(--w-text-muted) !important;
        }

        /* ============================================================
           Wahy dark-mode round2 — إصلاح ارتدادات مؤكّدة (جولة ثانية)
           كل عنصر هنا يُعالَج كزوج (خلفية داكنة + نص فاتح) معاً حتى لا
           يبقى نص فاتح على خلفية فاتحة ولا نص داكن على خلفية داكنة.
           ============================================================ */

        /* (1) عناوين/تسميات .form-card:
           البطاقة نفسها معتَّمة (var(--w-card)) أعلاه، لكن h3/h4 كانت #1a202c
           والتسميات #334155 = نص داكن على خلفية داكنة = مخفي. نُفتّحها. */
        html[data-theme="dark"] .form-card h3,
        html[data-theme="dark"] .form-card h4 {
            color: var(--w-text) !important;
            border-bottom-color: var(--w-border) !important;
        }
        html[data-theme="dark"] .form-card .form-label,
        html[data-theme="dark"] .form-card label {
            color: var(--w-text-muted) !important;
        }

        /* (2) create-activity: .type-card.active خلفيتها #eff6ff فاتحة تبقى
           فاتحة و.type-card .title مُفتَّح أعلاه = فاتح على فاتح. نُعتّم البطاقة
           النشطة فيصبح النص الفاتح على خلفية داكنة. */
        html[data-theme="dark"] .type-card.active {
            background: rgba(129, 140, 248, 0.14) !important;
            border-color: #818cf8 !important;
        }

        /* (3)+(4)+(5) الحاويات ذات خلفية inline بتدرّج فاتح
           linear-gradient(135deg,#f7fafc … #edf2f7) — بطاقات dashboard/بنك
           الأسئلة/الأنشطة/تفاصيل الفصل وصف رأس جداول الصدارة. نُعتّمها بمطابقة
           بداية سلسلة التدرّج بصيغتَي المسافة. نصوصها الداكنة تُفتَّح أصلاً عبر
           بلوكات color أعلاه، فيتكوّن الزوج (خلفية داكنة + نص فاتح). */
        html[data-theme="dark"] [style*="linear-gradient(135deg, #f7fafc"],
        html[data-theme="dark"] [style*="linear-gradient(135deg,#f7fafc"],
        html[data-theme="dark"] [style*="linear-gradient(135deg, #f8fafc"],
        html[data-theme="dark"] [style*="linear-gradient(135deg,#f8fafc"] {
            background: var(--w-card) !important;
            border-color: var(--w-border) !important;
        }

        /* (3) dashboard: بطاقة "تحتاج مراجعة" بتدرّج برتقالي فاتح
           #fff7ed → #fffaf0 ونصوصها #2d3748/#4a5568 (تُفتَّح أعلاه). نُعتّمها. */
        html[data-theme="dark"] [style*="linear-gradient(135deg, #fff7ed"],
        html[data-theme="dark"] [style*="linear-gradient(135deg,#fff7ed"] {
            background: var(--w-card) !important;
            border-color: var(--w-border) !important;
        }

        /* (4) leaderboard + student-leaderboard: صف الرأس <tr> بتدرّج فاتح
           (يُغطّى بمطابقة #f7fafc أعلاه) وصف المستخدم الحالي بتدرّج أزرق فاتح
           #eff6ff → #dbeafe. th مُفتَّح أعلاه، فنُعتّم صف المستخدم الحالي. */
        html[data-theme="dark"] [style*="linear-gradient(135deg, #eff6ff"],
        html[data-theme="dark"] [style*="linear-gradient(135deg,#eff6ff"] {
            background: rgba(129, 140, 248, 0.12) !important;
        }

        /* (6-a) teams: .team-desc خلفيته تدرّج فاتح (#f8fafc,#f1f5f9) ونصّه
           مُفتَّح خافت أعلاه = فاتح على فاتح. نُعتّم خلفيته (الكلاس معرّف في
           <style> الصفحة فيُطابق هنا مع !important). */
        html[data-theme="dark"] .team-desc {
            background: rgba(255, 255, 255, 0.05) !important;
            border-right-color: #818cf8 !important;
        }

        /* (6-b) show-team: .team-description داخل هيدر بنفسجي — خلفيته تدرّج
           فاتح ونصّه #64748b داكن. نعالجه كزوج: خلفية داكنة + نص فاتح خافت. */
        html[data-theme="dark"] .team-description {
            background: rgba(255, 255, 255, 0.06) !important;
            color: var(--w-text-muted) !important;
            border-right-color: #818cf8 !important;
        }
    </style>
    
    <!-- Glass Notifications CSS -->
    <link rel="stylesheet" href="{{ asset('css/glass-notifications.css') }}">
    
    @stack('styles')

    @include('partials.theme-toggle')
</head>
<body>
    @include('partials.flash')
    <a href="#teacher-main-content" class="skip-to-content"
       style="position:absolute;top:-40px;right:0;background:#1e293b;color:#fff;padding:8px 16px;z-index:9999;text-decoration:none;border-radius:0 0 0 8px;"
       onfocus="this.style.top='0'" onblur="this.style.top='-40px'">تخطي إلى المحتوى الرئيسي</a>
    <div class="teacher-layout">
        
        <!-- Sidebar -->
        <aside class="teacher-sidebar" id="sidebar">
            <div class="sidebar-logo">
                <div class="logo-icon">👨‍🏫</div>
                <div class="logo-text">مركز التدريس</div>
            </div>
            
            <div class="sidebar-user" style="position: relative;" id="tchAvatarDropdownContainer">
                <div class="user-avatar" id="tchAvatarToggleBtn" style="cursor: pointer; overflow: hidden; padding: 0;">
                    <img src="{{ auth()->user()->avatar_url }}" alt="صورة" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">معلم</div>
                </div>

                <!-- Dropdown Menu -->
                <div id="tchAvatarDropdownMenu" style="display: none; position: absolute; top: calc(100% + 10px); right: 0; width: 260px; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); z-index: 1000; overflow: hidden;">
                    <div style="padding: 8px;">
                        <label for="tchAvatarUploadInput" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; cursor: pointer; transition: background 0.2s; color: #334155; font-weight: 600; font-size: 14px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                            <span>📷</span> تغيير الصورة
                        </label>
                        <input type="file" id="tchAvatarUploadInput" accept="image/jpeg,image/png,image/jpg,image/webp" style="display: none;">

                        <a href="{{ route('teacher.settings') }}" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; text-decoration: none; transition: background 0.2s; color: #334155; font-weight: 600; font-size: 14px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
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
            
            <nav class="sidebar-nav">
                <a href="{{ route('teacher.dashboard') }}" class="nav-item {{ request()->is('teacher/dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">🏠</span>
                    لوحة التحكم
                </a>
                <a href="{{ route('messages.index') }}" class="nav-item {{ request()->routeIs('messages.*') ? 'active' : '' }}">
                    <span class="nav-icon">💬</span>
                    الرسائل
                    @php
                        $unreadCount = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count();
                    @endphp
                    <span style="background: #ef4444; color: white; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600; margin-right: auto; display: {{ $unreadCount > 0 ? 'inline-flex' : 'none' }};" data-live="messages_unread" data-live-badge>{{ $unreadCount > 0 ? $unreadCount : 0 }}</span>
                </a>
                <a href="{{ route('messages.bulk.inbox') }}" class="nav-item {{ request()->routeIs('messages.bulk.inbox') ? 'active' : '' }}">
                    <span class="nav-icon">📬</span>
                    الرسائل الجماعية
                    @php
                        $bulkUnreadCount = \App\Models\BulkMessageRecipient::where('user_id', auth()->id())->whereNull('read_at')->count();
                    @endphp
                    <span style="background: #f59e0b; color: white; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 600; margin-right: auto; display: {{ $bulkUnreadCount > 0 ? 'inline-flex' : 'none' }};" data-live="bulk_messages_unread" data-live-badge>{{ $bulkUnreadCount > 0 ? $bulkUnreadCount : 0 }}</span>
                </a>
                <a href="{{ route('teacher.review') }}" class="nav-item {{ request()->is('teacher/review*') ? 'active' : '' }}">
                    <span class="nav-icon">📋</span>
                    مراجعة الأنشطة
                </a>
                <a href="{{ route('teacher.students') }}" class="nav-item {{ request()->is('teacher/students*') ? 'active' : '' }}">
                    <span class="nav-icon">👥</span>
                    تقارير الطلاب
                </a>
                <a href="{{ route('teacher.classrooms') }}" class="nav-item {{ request()->is('teacher/classrooms*') ? 'active' : '' }}">
                    <span class="nav-icon">📚</span>
                    فصولي
                </a>
                <a href="{{ route('teacher.streak.settings') }}" class="nav-item {{ request()->is('teacher/streak*') ? 'active' : '' }}">
                    <span class="nav-icon">🔥</span>
                    مكافأة الالتزام
                </a>
                <a href="{{ route('teacher.teams') }}" class="nav-item {{ request()->is('teacher/teams*') ? 'active' : '' }}">
                    <span class="nav-icon">🤝</span>
                    الفرق
                </a>
                <a href="{{ route('teacher.messages') }}" class="nav-item {{ request()->is('teacher/messages*') ? 'active' : '' }}">
                    <span class="nav-icon">💬</span>
                    المراسلات
                </a>
                {{-- Issue #109: رابط لشاشة تفاعل أولياء الأمور --}}
                <a href="{{ route('teacher.parent-engagement') }}" class="nav-item {{ request()->is('teacher/parent-engagement*') ? 'active' : '' }}">
                    <span class="nav-icon">❤️</span>
                    تفاعل أولياء الأمور
                </a>
                {{-- N12: مقارنات الاستبيانات --}}
                <a href="{{ route('teacher.surveys.comparisons') }}" class="nav-item {{ request()->is('teacher/surveys*') ? 'active' : '' }}">
                    <span class="nav-icon">📊</span>
                    مقارنات الاستبيانات
                </a>
                <a href="{{ route('teacher.ratings') }}" class="nav-item {{ request()->is('teacher/ratings*') ? 'active' : '' }}">
                    <span class="nav-icon">⭐</span>
                    التقييمات
                </a>
                <a href="{{ route('teacher.analytics') }}" class="nav-item {{ request()->is('teacher/analytics*') ? 'active' : '' }}">
                    <span class="nav-icon">📊</span>
                    التحليلات
                </a>
                <a href="{{ route('teacher.activity-bank.index') }}" class="nav-item {{ request()->is('teacher/activity-bank*') || request()->is('teacher/question-bank*') ? 'active' : '' }}">
                    <span class="nav-icon">📚</span>
                    بنك الأنشطة
                </a>
                <a href="{{ route('tickets.index') }}" class="nav-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                    <span class="nav-icon">🛟</span>
                    الدعم الفنيّ
                </a>
                <a href="{{ route('teacher.settings') }}" class="nav-item {{ request()->is('teacher/settings*') ? 'active' : '' }}">
                    <span class="nav-icon">⚙️</span>
                    الإعدادات
                </a>
            </nav>
            
            <!-- تبديل الأدوار -->
            @include('components.role-switcher')
            
            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <span>🚪</span>
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="teacher-main" id="teacher-main-content">
            @yield('content')
        </main>
        
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
    </div>
    
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }
        
        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024) {
                const sidebar = document.getElementById('sidebar');
                const menuBtn = document.querySelector('.mobile-menu-btn');
                
                if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });

        // Avatar Dropdown Toggle
        (function() {
            const toggleBtn = document.getElementById('tchAvatarToggleBtn');
            const dropdownMenu = document.getElementById('tchAvatarDropdownMenu');
            const container = document.getElementById('tchAvatarDropdownContainer');
            const avatarInput = document.getElementById('tchAvatarUploadInput');

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
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    fetch('{{ route("profile.update-avatar") }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelectorAll('#tchAvatarToggleBtn img').forEach(img => img.src = data.avatar_url);
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
    
    <!-- Glass Notifications JS -->
    <script src="{{ asset('js/glass-notifications.js') }}"></script>
    
    @stack('scripts')
    
    <!-- Real-Time Messages System -->
    <script src="{{ asset('js/messages-realtime.js') }}"></script>
    
    <!-- Survey Popup Component -->
    @include('components.survey-popup')

    @include('partials.live-updates')

    @stack('after-content')
</body>
</html>

