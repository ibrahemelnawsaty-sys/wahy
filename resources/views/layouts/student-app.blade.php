@php
    // جلب إعدادات الثيم من قاعدة البيانات
    $fontFamily = setting('font_family', 'IBM Plex Sans Arabic');
    $primaryColor = setting('primary_color', '#667eea');
    $secondaryColor = setting('secondary_color', '#764ba2');
    $textColor = setting('text_color', '#1e293b');
    $backgroundColor = setting('background_color', '#ffffff');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="{{ $primaryColor }}">
    @include('partials.head-meta')
    <title>@yield('title', 'رحلتي التعليمية')</title>
    
    <!-- Fonts -->
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
    @else
    {{-- احتياطي: خط عربي مضمون دائماً حتى لا تظهر الحروف كمربعات عند إعداد خط غير معروف (Issue 71) --}}
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Student Glass CSS -->
    <link rel="stylesheet" href="{{ asset('css/student-glass.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/profile-modal.css') }}?v={{ time() }}">
    
    <!-- CSS ديناميكي من إعدادات الثيم -->
    <style>
        :root {
            --color-primary: {{ $primaryColor }};
            --color-primary-rgb: {{ hexToRgb($primaryColor) }};
            --color-secondary: {{ $secondaryColor }};
            --color-secondary-rgb: {{ hexToRgb($secondaryColor) }};
            --font-family: '{{ $fontFamily }}', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji", "Twemoji Mozilla", sans-serif;
        }

        /* الوضع الافتراضي: فاتح (Light Mode) — خلفية متدرّجة ناعمة تُقرأ عليها البطاقات الزجاجية بوضوح */
        :root, html[data-theme="light"] {
            --color-bg: #f8fafc;
            /* خلفية صفحة الطالب: مصدر موحّد يتجاوب مع الثيم (يحلّ تعارض glass.css) */
            --app-bg: linear-gradient(135deg, #7c83f0 0%, #8e7bd4 100%);
            --color-bg-elevated: #ffffff;
            --color-text: #0f172a;
            --color-text-muted: #475569;
            --color-border: rgba(15, 23, 42, 0.08);
            --color-card: #ffffff;
            --color-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            --color-overlay: rgba(255, 255, 255, 0.85);
            --status-bar-bg: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            --status-bar-text: #ffffff;
            /* تجاوز متغيرات glass.css لتلائم الخلفية المتدرّجة الفاتحة (نص داكن فوق زجاج أبيض) */
            --glass-bg-light: rgba(255, 255, 255, 0.16);
            --glass-bg-medium: rgba(255, 255, 255, 0.24);
            --glass-bg-heavy: rgba(255, 255, 255, 0.34);
            --glass-border: rgba(255, 255, 255, 0.35);
            --color-text-primary: #15213b;
            --color-text-secondary: #2c3a55;
            --color-text-light: #475569;
            --status-bar-surface: rgba(255, 255, 255, 0.95);
            color-scheme: light;
        }

        /* الوضع الليلي — تم تعديل التباين ليتوافق مع WCAG AA */
        html[data-theme="dark"] {
            --color-bg: #0b1220;
            --app-bg: linear-gradient(135deg, #1e1b4b 0%, #0b1220 100%);
            --color-bg-elevated: #111827;
            --color-text: #f1f5f9;
            --color-text-muted: #94a3b8;
            --color-border: rgba(255, 255, 255, 0.10);
            --color-card: #1e293b;
            --color-shadow: 0 10px 28px rgba(0, 0, 0, 0.45);
            --color-overlay: rgba(15, 23, 42, 0.85);
            --status-bar-bg: linear-gradient(135deg, #312e81, #1e293b);
            --status-bar-text: #f8fafc;
            /* تجاوز متغيرات glass.css للوضع الليلي: زجاج أخفّ + نص فاتح (يحلّ اختفاء النص) */
            --glass-bg-light: rgba(255, 255, 255, 0.06);
            --glass-bg-medium: rgba(255, 255, 255, 0.10);
            --glass-bg-heavy: rgba(255, 255, 255, 0.16);
            --glass-border: rgba(255, 255, 255, 0.14);
            --color-text-primary: #f1f5f9;
            --color-text-secondary: #cbd5e1;
            --color-text-light: #94a3b8;
            --status-bar-surface: rgba(17, 24, 39, 0.92);
            color-scheme: dark;
        }

        html, body {
            /* مصدر خلفية موحّد — يتجاوز خلفية glass.css الثابتة لأن هذا البلوك يأتي لاحقاً */
            background: var(--app-bg, var(--color-bg));
            background-attachment: fixed;
            color: var(--color-text);
        }

        /* شريط الحالة يتجاوب مع الثيم (كان أبيض ثابتاً في الوضع الليلي) */
        .student-status-bar {
            background: var(--status-bar-surface) !important;
        }

        body {
            font-family: var(--font-family) !important;
            transition: background .3s ease, color .3s ease;
        }

        /* Theme toggle button (floating) */
        .theme-toggle-btn {
            position: fixed;
            bottom: 88px;
            inset-inline-end: 16px;
            z-index: 9998;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 1px solid var(--color-border);
            background: var(--color-card);
            color: var(--color-text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: var(--color-shadow);
            transition: transform .15s, background .25s;
        }
        .theme-toggle-btn:hover { transform: scale(1.05); }

        /* تحسين تباين الزر الأخضر فوق الخلفية البنفسجية في activity-view (Issue 58) */
        .feedback-card .feedback-icon {
            filter: drop-shadow(0 2px 6px rgba(0,0,0,0.4));
        }

        /* الأخضر فوق البنفسجي — رفع التباين ليصبح مقروءاً بوضوح (Issue 58) */
        .student-app [class*="text-green"],
        .student-app [style*="color: #10B981"],
        .student-app [style*="color: #16a34a"],
        .student-app [style*="color: #22c55e"] {
            color: #34d399 !important;  /* أخضر فاتح أكثر للخلفيات الداكنة */
            text-shadow: 0 1px 2px rgba(0,0,0,.3);
        }

        /* مسار الطالب: الأرقام الخضراء — استبدال بلون واضح (Issue 54) */
        .stat-value-green,
        [data-theme-text="success"],
        .progress-stat-number {
            color: #fbbf24 !important; /* أصفر ذهبي = تباين 9:1 على البنفسجي */
            font-weight: 800;
            text-shadow: 0 1px 3px rgba(0,0,0,.2);
        }

        /* كلا الوضعين (فاتح=بنفسجي / ليلي=داكن) خلفيتهما ملوّنة، فالأصفر الذهبي يوفّر تبايناً عالياً في الاثنين */

        /* ============================================================
           Wahy dark-mode coverage — بلوك مُجمَّع يغطّي كل صفحات الطالب
           يعالج الألوان المُصلَّبة inline (بطاقات بيضاء + نصوص داكنة)
           التي لا تستجيب للوضع الليلي. جراحي/مضاف فقط — لا يمسّ التخطيط.
           ============================================================ */

        /* 1) البطاقات ذات الخلفية البيضاء الثابتة → سطح داكن في الوضع الليلي */
        html[data-theme="dark"] .student-app [style*="background: white"],
        html[data-theme="dark"] .student-app [style*="background:#fff"],
        html[data-theme="dark"] .student-app [style*="background: #fff"],
        html[data-theme="dark"] .student-app [style*="background: #ffffff"],
        html[data-theme="dark"] .student-app [style*="background-color: white"],
        html[data-theme="dark"] .student-app [style*="background-color: #fff"] {
            background: var(--color-card) !important;
            border-color: var(--color-border) !important;
        }

        /* بطاقات/كبسولات بيضاء نصف-شفافة شبه معتمة (0.78+) → سطح داكن
           (تغطّي .crown-card وغيرها؛ نمرّر كل تنويعات المسافات المستخدمة) */
        html[data-theme="dark"] .student-app [style*="background: rgba(255,255,255,0.7"],
        html[data-theme="dark"] .student-app [style*="background: rgba(255,255,255,0.8"],
        html[data-theme="dark"] .student-app [style*="background: rgba(255,255,255,0.9"],
        html[data-theme="dark"] .student-app [style*="background: rgba(255, 255, 255, 0.7"],
        html[data-theme="dark"] .student-app [style*="background: rgba(255, 255, 255, 0.8"],
        html[data-theme="dark"] .student-app [style*="background: rgba(255, 255, 255, 0.9"],
        html[data-theme="dark"] .student-app [style*="background:rgba(255,255,255,0.9"] {
            background: var(--color-card) !important;
            border-color: var(--color-border) !important;
        }

        /* بطاقات بخلفية متدرّجة "فاتحة/بيضاء/وردية باهتة" (نوافذ نتائج، بطاقات مدح)
           → سطح داكن. هذه ليست بطاقات ملوّنة زاهية بل بيضاء تزيينية، فيجب أن تستجيب
           للوضع الليلي؛ نصوصها الداكنة تُفتَّح لاحقاً فتبقى مقروءة. */
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg, #ffffff"],
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg,#fff "],
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg, #fff "],
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg, #fff5f5"],
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg, #f8fafc"],
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg, #f9fafb"] {
            background: var(--color-card) !important;
            border-color: var(--color-border) !important;
        }

        /* خلفيات رمادية فاتحة صلبة (لوحات داخلية/أزرار ثانوية/مسارات تقدّم/عناصر نائبة)
           → سطح داكن مُرتفع قليلاً، حتى يبقى النص الداكن (المُفتَّح لاحقاً) مقروءاً */
        html[data-theme="dark"] .student-app [style*="background: #f7fafc"],
        html[data-theme="dark"] .student-app [style*="background:#f7fafc"],
        html[data-theme="dark"] .student-app [style*="background: #edf2f7"],
        html[data-theme="dark"] .student-app [style*="background: #e2e8f0"],
        html[data-theme="dark"] .student-app [style*="background:#e2e8f0"],
        html[data-theme="dark"] .student-app [style*="background: #cbd5e0"],
        html[data-theme="dark"] .student-app [style*="background: #f1f5f9"],
        html[data-theme="dark"] .student-app [style*="background: #e5e7eb"] {
            background: #334155 !important;
            border-color: var(--color-border) !important;
        }

        /* Wahy dark-mode round2 — كبسولات إحصاء teams بخلفيات باستيل صلبة فاتحة
           (#f0fff4 أخضر / #ebf4ff أزرق / #faf5ff بنفسجي). أرقامها color:#2d3748 والنص
           الثانوي #718096 يُفتَّحان في البلوك أعلاه، فبدون تعتيم الخلفية = فاتح-على-فاتح
           مخفي. نعتّمها لسطح داكن حتى يبقى النص المُفتَّح مقروءاً. نغطّي صيغتَي المسافة. */
        html[data-theme="dark"] .student-app [style*="background: #f0fff4"],
        html[data-theme="dark"] .student-app [style*="background:#f0fff4"],
        html[data-theme="dark"] .student-app [style*="background: #ebf4ff"],
        html[data-theme="dark"] .student-app [style*="background:#ebf4ff"],
        html[data-theme="dark"] .student-app [style*="background: #faf5ff"],
        html[data-theme="dark"] .student-app [style*="background:#faf5ff"] {
            background: #334155 !important;
            border-color: var(--color-border) !important;
        }

        /* 2) النصوص الداكنة المُصلَّبة → نص فاتح مقروء (WCAG AA) */
        html[data-theme="dark"] .student-app [style*="color: #1a202c"],
        html[data-theme="dark"] .student-app [style*="color:#1a202c"],
        html[data-theme="dark"] .student-app [style*="color: #2d3748"],
        html[data-theme="dark"] .student-app [style*="color:#2d3748"],
        html[data-theme="dark"] .student-app [style*="color: #2d3436"],
        html[data-theme="dark"] .student-app [style*="color: #1e293b"],
        html[data-theme="dark"] .student-app [style*="color:#1e293b"],
        html[data-theme="dark"] .student-app [style*="color: #0f172a"],
        html[data-theme="dark"] .student-app [style*="color: #000"],
        html[data-theme="dark"] .student-app [style*="color:#000"] {
            color: #f1f5f9 !important;  /* نص أساسي فاتح */
        }

        /* نصوص ثانوية داكنة (رمادية متوسطة) → رمادي فاتح */
        html[data-theme="dark"] .student-app [style*="color: #4a5568"],
        html[data-theme="dark"] .student-app [style*="color:#4a5568"],
        html[data-theme="dark"] .student-app [style*="color: #718096"],
        html[data-theme="dark"] .student-app [style*="color:#718096"],
        html[data-theme="dark"] .student-app [style*="color: #64748b"],
        html[data-theme="dark"] .student-app [style*="color: #64748B"],
        html[data-theme="dark"] .student-app [style*="color: #475569"],
        html[data-theme="dark"] .student-app [style*="color: #334155"],
        html[data-theme="dark"] .student-app [style*="color: #a0aec0"] {
            color: #cbd5e1 !important;  /* نص ثانوي فاتح */
        }

        /* حارس تباين: النص الداكن الجالس مباشرةً على بطاقة بخلفية متدرّجة "دافئة/زاهية"
           (ذهبي/أصفر/برتقالي — تبقى ملوّنة في الوضع الليلي) يجب أن يبقى داكناً؛
           تفتيحه يكسر التباين (نص فاتح على أصفر). نستهدف ألوان التدرّج الدافئة تحديداً
           حتى لا نلمس التدرّجات البيضاء/الوردية التي عتّمناها أعلاه. */
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg, #ffd700"] > [style*="color: #2d3436"],
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg, #ffd700"] > [style*="color: #718096"],
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg, #ffeaa7"] > [style*="color: #2d3436"],
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg, #ffe"] > [style*="color: #2d3436"],
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg, #fbbf24"] > [style*="color: #2d3436"],
        html[data-theme="dark"] .student-app [style*="linear-gradient(135deg, #fef"] > [style*="color: #2d3436"] {
            color: #1e293b !important;  /* يبقى داكناً فوق الخلفية الدافئة الثابتة */
        }

        /* 3) عناوين البطاقات البيضاء (h2/h3 داخلها) — احتياط لو لم تحمل style مباشراً */
        html[data-theme="dark"] .student-app [style*="background: white"] h2,
        html[data-theme="dark"] .student-app [style*="background: white"] h3 {
            color: #f1f5f9 !important;
        }

        /* 4) شريط التنقّل السفلي — زجاج داكن بدل الأبيض الثابت في glass.css */
        html[data-theme="dark"] .student-app .bottom-nav {
            background: rgba(17, 24, 39, 0.92) !important;
            border-color: var(--color-border) !important;
        }
        html[data-theme="dark"] .student-app .nav-item {
            color: #94a3b8;
        }

        /* 5) الحدود الفاتحة الثابتة (#e2e8f0 وشبيهاتها) → حدّ داكن خفيف */
        html[data-theme="dark"] .student-app [style*="border: 2px solid #e2e8f0"],
        html[data-theme="dark"] .student-app [style*="border: 1px solid #e2e8f0"],
        html[data-theme="dark"] .student-app [style*="border: 2px solid #edf2f7"],
        html[data-theme="dark"] .student-app [style*="border-color: #e2e8f0"] {
            border-color: rgba(255, 255, 255, 0.12) !important;
        }
    </style>

    <script>
        // تطبيق الثيم المحفوظ قبل عرض الصفحة لتجنّب الوميض (FOUC)
        (function () {
            try {
                // الافتراضي فاتح (الطالب اشتكى من الخلفية الداكنة) — والوضع الليلي اختياري عبر الزر
                var saved = localStorage.getItem('wahy-theme') || 'light';
                document.documentElement.setAttribute('data-theme', saved);
            } catch (e) {}
        })();
    </script>
    
    @stack('styles')

    {{-- التغطية الشاملة المتّسقة للوضع الليلي (نفس مصدر باقي الأدوار) — يعمل هنا لأول مرة على صفحات الطالب --}}
    @include('partials.dark-coverage')

    {{-- براويز الأفاتار المتحركة (Wow) --}}
    @include('partials.avatar-frames')
</head>
<body class="student-app">
    @include('partials.flash')
    <a href="#student-main-content" class="skip-to-content"
       style="position:absolute;top:-40px;right:0;background:#1e293b;color:#fff;padding:8px 16px;z-index:9999;text-decoration:none;border-radius:0 0 0 8px;"
       onfocus="this.style.top='0'" onblur="this.style.top='-40px'">تخطي إلى المحتوى الرئيسي</a>
    <!-- Status Bar (Always Visible) -->
    <div class="student-status-bar">
        <div class="status-bar-container">
            <!-- Left: Avatar & User Info -->
            <div class="status-bar-left">
                @php
                    $__su = auth()->user();
                    $__frameP = method_exists($__su, 'equippedFrame') ? $__su->equippedFrame() : null;
                    $__fm = ($__frameP && is_array($__frameP->metadata)) ? $__frameP->metadata : [];
                    $__anim = $__fm['anim'] ?? null;   // برواز متحرك (gold/neon/royal/fire)
                    $__ring = $__fm['ring'] ?? null;   // احتياطي ثابت للثيمات القديمة
                    $__glow = $__fm['glow'] ?? null;
                    $__badgeP = method_exists($__su, 'equippedBadge') ? $__su->equippedBadge() : null;
                @endphp
                <div class="status-avatar {{ $__anim ? 'wahy-frame wahy-frame-' . $__anim : '' }}" onclick="openProfileModal()" title="اضغط لتعديل الملف الشخصي"
                     style="cursor: pointer;{{ (! $__anim && $__ring) ? ' background:' . $__ring . '; padding:3px;' : '' }}{{ (! $__anim && $__glow) ? ' box-shadow:' . $__glow . ';' : '' }}">
                    <img src="{{ $__su->avatar_url }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <span style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-weight:700;">{{ mb_substr($__su->name, 0, 1) }}</span>
                    @if($__anim)@include('partials.wf-particles')@endif
                </div>
                <div class="status-info">
                    <div class="status-name">
                        {{ $__su->name }}@if($__badgeP)<span title="{{ $__badgeP->name }}" style="margin-inline-start:4px;">{{ $__badgeP->icon }}</span>@endif
                    </div>
                    <div class="status-level">
                        <span>⭐</span>
                        <span>المستوى {{ $stats['total_points'] ?? 0 > 100 ? floor(($stats['total_points'] ?? 0) / 100) : 1 }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Center: XP Progress Bar -->
            <div class="status-bar-center">
                @php
                    $currentXP = ($stats['total_points'] ?? 0) % 100;
                    $nextLevelXP = 100;
                    $xpPercent = ($currentXP / $nextLevelXP) * 100;
                @endphp
                <div class="xp-bar-container">
                    <div class="xp-bar-fill" style="width: {{ $xpPercent }}%"></div>
                </div>
                <div class="xp-bar-text">{{ $currentXP }} / {{ $nextLevelXP }} XP</div>
            </div>
            
            <!-- Right: Streak & Coins & Notifications -->
            <div class="status-bar-right">
                <!-- Streak Badge -->
                @if(isset($streak) && $streak && $streak->current_streak > 0)
                <div class="status-badge status-badge-streak">
                    <span class="status-badge-icon">🔥</span>
                    <span data-live="streak_current">{{ $streak->current_streak }}</span>
                </div>
                @endif
                
                <!-- Coins Badge -->
                <div class="status-badge status-badge-coins" onclick="openCoinsModal()" style="cursor: pointer;" title="اضغط لعرض سجل النقاط">
                    <span class="status-badge-icon">💰</span>
                    <span data-live="coins_total">{{ $stats['total_coins'] ?? 0 }}</span>
                </div>
                
                <!-- Notifications Badge -->
                <a href="{{ route('notifications.index') }}" class="status-badge status-badge-notifications" style="cursor: pointer; text-decoration: none; position: relative;" title="الإشعارات">
                    <span class="status-badge-icon">🔔</span>
                    @php
                        try {
                            $unreadCount = \App\Services\NotificationService::getUnreadCount(auth()->id());
                        } catch (\Throwable $e) {
                            $unreadCount = 0;
                        }
                    @endphp
                    <span style="position: absolute; top: -6px; left: -6px; background: #ef4444; color: white; border-radius: 10px; padding: 2px 6px; font-size: 10px; font-weight: 700; min-width: 18px; text-align: center; border: 2px solid white;display: {{ $unreadCount > 0 ? 'inline-flex' : 'none' }};" data-live="notifications_unread" data-live-badge data-live-cap="9">
                        {{ $unreadCount > 0 ? ($unreadCount > 9 ? '9+' : $unreadCount) : 0 }}
                    </span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="student-main" id="student-main-content">
        @yield('content')
    </main>

    <!-- Bottom Navigation (App-First) -->
    <nav class="bottom-nav">
        <div class="bottom-nav-container">
            <!-- Learn -->
            <a href="{{ route('student.dashboard') }}" class="nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <div class="nav-item-icon-wrapper">
                    <svg class="nav-item-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    </svg>
                    <div class="nav-item-indicator"></div>
                </div>
                <div class="nav-item-label">التعلم</div>
            </a>
            
            <!-- Path -->
            <a href="{{ route('student.path') }}" class="nav-item {{ request()->routeIs('student.path') ? 'active' : '' }}">
                <div class="nav-item-icon-wrapper">
                    <svg class="nav-item-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="3 11 12 2 21 11 21 22 3 22 3 11"></polygon>
                        <path d="M7 13h10"></path>
                        <path d="M7 17h10"></path>
                    </svg>
                    <div class="nav-item-indicator"></div>
                </div>
                <div class="nav-item-label">الخريطة</div>
            </a>
            
            <!-- Practice -->
            <a href="{{ route('student.practice') }}" class="nav-item {{ request()->routeIs('student.practice') ? 'active' : '' }}">
                <div class="nav-item-icon-wrapper">
                    <svg class="nav-item-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <div class="nav-item-indicator"></div>
                </div>
                <div class="nav-item-label">تحديات</div>
            </a>
            
            <!-- Messages -->
            <a href="{{ route('messages.index') }}" class="nav-item {{ request()->routeIs('messages.*') ? 'active' : '' }}" style="position: relative;">
                <div class="nav-item-icon-wrapper">
                    <svg class="nav-item-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        <path d="M8 10h.01"></path>
                        <path d="M12 10h.01"></path>
                        <path d="M16 10h.01"></path>
                    </svg>
                    <div class="nav-item-indicator"></div>
                </div>
                <div class="nav-item-label">الرسائل</div>
                @php
                    try {
                        $unreadMessages = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count();
                    } catch (\Throwable $e) {
                        $unreadMessages = 0;
                    }
                @endphp
                <span class="nav-item-badge" style="display: {{ $unreadMessages > 0 ? 'inline-flex' : 'none' }};" data-live="messages_unread" data-live-badge data-live-cap="9">{{ $unreadMessages > 0 ? ($unreadMessages > 9 ? '9+' : $unreadMessages) : 0 }}</span>
            </a>
            
            <!-- Profile -->
            <a href="{{ route('student.profile') }}" class="nav-item {{ request()->routeIs('student.profile') ? 'active' : '' }}">
                <div class="nav-item-icon-wrapper">
                    <svg class="nav-item-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <div class="nav-item-indicator"></div>
                </div>
                <div class="nav-item-label">حسابي</div>
            </a>
        </div>
    </nav>

    <!-- Coins History Modal -->
    <div id="coinsModal" class="profile-modal" style="display: none;">
        <div class="profile-modal-overlay" onclick="closeCoinsModal()"></div>
        <div class="profile-modal-content" style="max-width: 700px;">
            <div class="profile-modal-header">
                <h2>⭐ سجل النقاط والعملات</h2>
                <button onclick="closeCoinsModal()" class="profile-modal-close">✕</button>
            </div>
            
            <!-- Stats Summary -->
            <div class="coins-summary">
                <div class="coins-stat-card">
                    <div class="coins-stat-icon">💰</div>
                    <div class="coins-stat-info">
                        <div class="coins-stat-value">{{ $stats['total_coins'] ?? 0 }}</div>
                        <div class="coins-stat-label">إجمالي العملات</div>
                    </div>
                </div>
                <div class="coins-stat-card">
                    <div class="coins-stat-icon">🎯</div>
                    <div class="coins-stat-info">
                        <div class="coins-stat-value"><span data-live="points_total">{{ $stats['total_points'] ?? 0 }}</span> XP</div>
                        <div class="coins-stat-label">نقاط الخبرة</div>
                    </div>
                </div>
                <div class="coins-stat-card">
                    <div class="coins-stat-icon">📊</div>
                    <div class="coins-stat-info">
                        <div class="coins-stat-value">المستوى {{ floor(($stats['total_points'] ?? 0) / 100) + 1 }}</div>
                        <div class="coins-stat-label">مستواك الحالي</div>
                    </div>
                </div>
            </div>

            <!-- How Points Work -->
            <div class="profile-section">
                <h3>📖 كيف تُحسب النقاط؟</h3>
                <div class="points-rules">
                    <div class="points-rule">
                        <span class="points-rule-icon">✓</span>
                        <span class="points-rule-text">إكمال نشاط: <strong>+10 عملات</strong> و <strong>+20 XP</strong></span>
                    </div>
                    <div class="points-rule">
                        <span class="points-rule-icon">⚡</span>
                        <span class="points-rule-text">التمرين السريع: <strong>+5 عملات</strong> و <strong>+10 XP</strong></span>
                    </div>
                    <div class="points-rule">
                        <span class="points-rule-icon">🎯</span>
                        <span class="points-rule-text">درجة كاملة: <strong>مكافأة إضافية +5 عملات</strong></span>
                    </div>
                    <div class="points-rule">
                        <span class="points-rule-icon">🔥</span>
                        <span class="points-rule-text">سلسلة يومية: <strong>+2 عملات لكل يوم</strong></span>
                    </div>
                    <div class="points-rule">
                        <span class="points-rule-icon">🛒</span>
                        <span class="points-rule-text">شراء مكافأة: <strong>-10 إلى -50 عملة</strong></span>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="profile-section">
                <h3>📜 سجل النجوم</h3>
                <div id="coinsHistory" class="coins-history-list">
                    <div class="loading-spinner">
                        <div class="spinner"></div>
                        <p>جاري التحميل...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    
    <script>
        function openCoinsModal() {
            document.getElementById('coinsModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            loadCoinsHistory();
        }

        function closeCoinsModal() {
            document.getElementById('coinsModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        async function loadCoinsHistory() {
            const historyDiv = document.getElementById('coinsHistory');
            
            try {
                const response = await fetch('{{ route("student.coins.history") }}');
                const data = await response.json();
                
                if (data.success && data.history.length > 0) {
                    historyDiv.innerHTML = data.history.map(item => {
                        const isPositive = item.amount > 0;
                        const icon = getTransactionIcon(item.source);
                        const color = isPositive ? '#10B981' : '#EF4444';
                        
                        return `
                            <div class="coins-history-item">
                                <div class="coins-history-icon" style="background: ${isPositive ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'}">
                                    ${icon}
                                </div>
                                <div class="coins-history-details">
                                    <div class="coins-history-desc">${item.description}</div>
                                    <div class="coins-history-date">${item.date}</div>
                                </div>
                                <div class="coins-history-amount" style="color: ${color}">
                                    ${isPositive ? '+' : ''}${item.amount}
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    historyDiv.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">📝</div>
                            <div class="empty-state-text">لا يوجد سجل بعد</div>
                            <div class="empty-state-hint">ابدأ بإكمال الأنشطة لكسب العملات!</div>
                        </div>
                    `;
                }
            } catch (error) {
                historyDiv.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">⚠️</div>
                        <div class="empty-state-text">حدث خطأ في تحميل السجل</div>
                    </div>
                `;
            }
        }

        function getTransactionIcon(source) {
            const icons = {
                'activity_completion': '✓',
                'practice_completion': '⚡',
                'perfect_score': '🎯',
                'daily_streak': '🔥',
                'shop_purchase': '🛒',
                'bonus': '🎁'
            };
            return icons[source] || '⭐';
        }

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (document.getElementById('coinsModal').style.display === 'flex') {
                    closeCoinsModal();
                }
                if (document.getElementById('profileModal') && document.getElementById('profileModal').style.display === 'flex') {
                    closeProfileModal();
                }
            }
        });
    </script>

    <!-- Profile Edit Modal -->
    <div id="profileModal" class="profile-modal" style="display: none;">
        <div class="profile-modal-overlay" onclick="closeProfileModal()"></div>
        <div class="profile-modal-content">
            <div class="profile-modal-header">
                <h2>⚙️ إعدادات الحساب</h2>
                <button onclick="closeProfileModal()" class="profile-modal-close">✕</button>
            </div>
            
            <form id="profileForm" enctype="multipart/form-data" onsubmit="updateProfile(event)">
                @csrf
                
                <!-- Avatar Section -->
                <div class="profile-section">
                    <h3>📷 الصورة الشخصية</h3>
                    <div class="avatar-upload-section">
                        <div class="avatar-preview">
                            <div id="avatarPreview" class="avatar-preview-circle">
                                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" onerror="this.style.display='none'; this.parentElement.textContent='{{ mb_substr(auth()->user()->name, 0, 1) }}'">
                            </div>
                        </div>
                        <div class="avatar-upload-controls">
                            <label for="avatarInput" class="btn-upload">
                                📤 رفع صورة جديدة
                            </label>
                            <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(event)">
                            <p class="upload-hint">JPG, PNG - حد أقصى 2MB</p>
                        </div>
                    </div>
                </div>

                <!-- Personal Info -->
                <div class="profile-section">
                    <h3>👤 المعلومات الشخصية</h3>
                    <div class="form-group">
                        <label>الاسم الكامل</label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ auth()->user()->email }}" required class="form-input">
                    </div>
                </div>

                <!-- Password Section — مطويّة خلف زرّ، تظهر عند الضغط -->
                <div class="profile-section">
                    <button type="button" class="pw-toggle-btn" onclick="togglePwFields(this)" aria-expanded="false" aria-controls="pwFields">
                        <span>🔐 تغيير كلمة المرور</span>
                        <span class="pw-chevron" aria-hidden="true">▾</span>
                    </button>
                    <div class="pw-fields" id="pwFields" hidden>
                        <div class="form-group">
                            <label>كلمة المرور الحالية</label>
                            <input type="password" name="current_password" class="form-input" placeholder="اتركه فارغاً إذا لم ترد التغيير">
                        </div>
                        <div class="form-group">
                            <label>كلمة المرور الجديدة</label>
                            <input type="password" name="new_password" class="form-input" placeholder="8 أحرف على الأقل">
                        </div>
                        <div class="form-group">
                            <label>تأكيد كلمة المرور الجديدة</label>
                            <input type="password" name="new_password_confirmation" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="profile-modal-footer">
                    <button type="submit" class="btn-save">
                        ✓ حفظ التغييرات
                    </button>
                    <button type="button" onclick="closeProfileModal()" class="btn-cancel">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // إظهار/إخفاء حقول تغيير كلمة المرور (مطويّة افتراضياً)
        function togglePwFields(btn) {
            var f = btn.parentElement.querySelector('.pw-fields');
            if (!f) return;
            var willShow = f.hasAttribute('hidden');
            if (willShow) { f.removeAttribute('hidden'); } else { f.setAttribute('hidden', ''); }
            btn.setAttribute('aria-expanded', willShow ? 'true' : 'false');
        }

        function openProfileModal() {
            document.getElementById('profileModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeProfileModal() {
            document.getElementById('profileModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        function previewAvatar(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatarPreview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                };
                reader.readAsDataURL(file);
            }
        }

        async function updateProfile(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const btn = event.target.querySelector('.btn-save');
            btn.disabled = true;
            btn.textContent = '⏳ جاري الحفظ...';
            
            try {
                const response = await fetch('{{ route("student.profile.update") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✓ تم تحديث البيانات بنجاح!');
                    location.reload();
                } else {
                    alert('✗ حدث خطأ: ' + (data.message || 'حاول مرة أخرى'));
                }
            } catch (error) {
                alert('✗ حدث خطأ في الاتصال');
            } finally {
                btn.disabled = false;
                btn.textContent = '✓ حفظ التغييرات';
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('profileModal').style.display === 'flex') {
                closeProfileModal();
            }
        });
    </script>

    <!-- Survey Popup Component -->
    @include('components.survey-popup')

    <!-- Theme toggle (Light/Dark) -->
    <button type="button"
            id="wahyThemeToggle"
            class="theme-toggle-btn"
            aria-label="تبديل الوضع الليلي/النهاري"
            title="تبديل الوضع الليلي/النهاري">
        <span id="wahyThemeIcon">🌙</span>
    </button>
    <script>
        (function () {
            const root = document.documentElement;
            const btn = document.getElementById('wahyThemeToggle');
            const icon = document.getElementById('wahyThemeIcon');

            function refreshIcon() {
                const isDark = root.getAttribute('data-theme') === 'dark';
                if (icon) icon.textContent = isDark ? '☀️' : '🌙';
                if (btn) btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            }

            refreshIcon();

            btn?.addEventListener('click', () => {
                const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                try { localStorage.setItem('wahy-theme', next); } catch (e) {}
                refreshIcon();
            });
        })();
    </script>

    @include('partials.live-updates')
</body>
</html>
