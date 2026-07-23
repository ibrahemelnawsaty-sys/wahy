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
    {{-- Bootstrap RTL: صفحات وليّ الأمر (الأنشطة العائلية…) مبنيّة بأصناف Bootstrap وكانت بلا
         تنسيق لأنّ الطبقة لم تُحمّله. قبل الـ<style> المخصّص فيبقى التصميم المخصّص هو المُهيمِن. --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
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

        /* =========================================================
           Wahy dark-mode coverage — بلوك موحّد لدور ولي الأمر.
           يغطّي الفجوات التي لا يلمسها الـ partial المشترك: الهيدر
           الزجاجي، البطاقات ذات الأصناف الخاصة (premium-*/cd-*/msg-*)،
           والحاويات المرسومة بألوان مُصلَّبة inline (background:white
           / #fff / rgba(255,255,255,...)). الهدف: كل خلفية فاتحة
           تصير داكنة وكل نص داكن يصير فاتحاً ليحقق تباين WCAG AA.
           جراحي/مضاف فقط — لا يغيّر تخطيطاً ولا مسافات.
           ========================================================= */

        /* الهيدر الزجاجي وروابط التنقل */
        html[data-theme="dark"] .parent-header {
            background: rgba(17, 24, 39, 0.92) !important;
            border-bottom-color: var(--w-border) !important;
            box-shadow: var(--w-shadow) !important;
        }
        html[data-theme="dark"] .nav-link {
            color: var(--w-text-muted) !important;
        }
        html[data-theme="dark"] .nav-link:hover,
        html[data-theme="dark"] .nav-link.active {
            color: #a5b4fc !important;
            background: rgba(165, 180, 252, 0.12) !important;
        }
        html[data-theme="dark"] .parent-menu-toggle {
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] .role-switcher-menu {
            background: var(--w-card) !important;
            border-color: var(--w-border) !important;
            box-shadow: var(--w-shadow) !important;
        }
        html[data-theme="dark"] .role-menu-item {
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] .role-menu-item:hover {
            background: rgba(165, 180, 252, 0.12) !important;
        }

        /* البطاقات الزجاجية البيضاء العامة للدور */
        html[data-theme="dark"] .premium-stat-card,
        html[data-theme="dark"] .premium-empty-state,
        html[data-theme="dark"] .premium-child-card,
        html[data-theme="dark"] .cd-hero,
        html[data-theme="dark"] .cd-stat,
        html[data-theme="dark"] .cd-card,
        html[data-theme="dark"] .cd-modal,
        html[data-theme="dark"] .cd-success-popup,
        html[data-theme="dark"] .msg-wrap,
        html[data-theme="dark"] .modal-box,
        html[data-theme="dark"] .form-modal-box {
            background: var(--w-card) !important;
            border-color: var(--w-border) !important;
            box-shadow: var(--w-shadow) !important;
        }

        /* النصوص الداكنة داخل البطاقات → فاتحة */
        html[data-theme="dark"] .stat-value,
        html[data-theme="dark"] .empty-title,
        html[data-theme="dark"] .info-value,
        html[data-theme="dark"] .stat-mini-value,
        html[data-theme="dark"] .cd-stat-value,
        html[data-theme="dark"] .cd-card-title,
        html[data-theme="dark"] .cd-value-name,
        html[data-theme="dark"] .cd-activity-title,
        html[data-theme="dark"] .cd-badge-name,
        html[data-theme="dark"] .cd-teacher-name,
        html[data-theme="dark"] .cd-week-count,
        html[data-theme="dark"] .cd-success-title,
        html[data-theme="dark"] .cd-praise-cat-label,
        html[data-theme="dark"] .msg-header h2,
        html[data-theme="dark"] .modal-head h3 {
            color: var(--w-text) !important;
            -webkit-text-fill-color: var(--w-text) !important;
        }
        /* عناوين ذات gradient-clip: نلغي القص لإظهار اللون الفاتح */
        html[data-theme="dark"] .stat-value,
        html[data-theme="dark"] .stat-mini-value {
            background: none !important;
            -webkit-background-clip: initial !important;
            background-clip: initial !important;
        }
        html[data-theme="dark"] .stat-label,
        html[data-theme="dark"] .empty-text,
        html[data-theme="dark"] .info-label,
        html[data-theme="dark"] .stat-mini-label,
        html[data-theme="dark"] .cd-stat-label,
        html[data-theme="dark"] .cd-value-count,
        html[data-theme="dark"] .cd-activity-lesson,
        html[data-theme="dark"] .cd-activity-time,
        html[data-theme="dark"] .cd-badge-date,
        html[data-theme="dark"] .cd-teacher-email,
        html[data-theme="dark"] .cd-week-name,
        html[data-theme="dark"] .cd-week-date,
        html[data-theme="dark"] .cd-rank-label,
        html[data-theme="dark"] .cd-rank-total,
        html[data-theme="dark"] .cd-success-text,
        html[data-theme="dark"] .cd-suggest-title {
            color: var(--w-text-muted) !important;
        }

        /* حاويات فرعية بخلفيات فاتحة مُصلَّبة */
        html[data-theme="dark"] .school-info-premium,
        html[data-theme="dark"] .info-item-premium,
        html[data-theme="dark"] .child-stat-mini,
        html[data-theme="dark"] .activity-item-premium,
        html[data-theme="dark"] .cd-rank-item,
        html[data-theme="dark"] .cd-week-day,
        html[data-theme="dark"] .cd-activity,
        html[data-theme="dark"] .cd-badge,
        html[data-theme="dark"] .cd-teacher,
        html[data-theme="dark"] .cd-praise-cat,
        html[data-theme="dark"] .cd-suggest-item,
        html[data-theme="dark"] .cd-hero-bottom,
        html[data-theme="dark"] .conv-item,
        html[data-theme="dark"] .msg-bubble.other {
            background: rgba(255, 255, 255, 0.04) !important;
            border-color: var(--w-border) !important;
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] .cd-rank-item:hover,
        html[data-theme="dark"] .cd-teacher:hover,
        html[data-theme="dark"] .conv-item:hover {
            background: rgba(255, 255, 255, 0.08) !important;
        }
        html[data-theme="dark"] .activity-title,
        html[data-theme="dark"] .conv-item h3,
        html[data-theme="dark"] .msg-bubble.other {
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] .activity-date {
            color: var(--w-text-muted) !important;
        }

        /* محرّرات النص الغني ومدخلات النموذج في صفحة الرسائل */
        html[data-theme="dark"] .rte-toolbar {
            background: rgba(255, 255, 255, 0.05) !important;
            border-color: var(--w-border) !important;
        }
        html[data-theme="dark"] .rte-btn-mini {
            background: var(--w-card) !important;
            color: var(--w-text) !important;
            border-color: var(--w-border) !important;
        }
        html[data-theme="dark"] .rte-editor-msg,
        html[data-theme="dark"] .new-msg-editor,
        html[data-theme="dark"] .form-select-field,
        html[data-theme="dark"] .cd-praise-textarea {
            background: rgba(255, 255, 255, 0.05) !important;
            color: var(--w-text) !important;
            border-color: var(--w-border) !important;
        }
        html[data-theme="dark"] .btn-cancel {
            background: rgba(255, 255, 255, 0.08) !important;
            color: var(--w-text) !important;
        }

        /* حاويات مرسومة بألوان بيضاء/فاتحة عبر style inline (تقارير/استبيانات/تفاصيل) */
        html[data-theme="dark"] [style*="background: white"],
        html[data-theme="dark"] [style*="background:#fff"],
        html[data-theme="dark"] [style*="background: #fff"],
        html[data-theme="dark"] [style*="rgba(255,255,255,0.9"],
        html[data-theme="dark"] [style*="rgba(255, 255, 255, 0.9"],
        html[data-theme="dark"] [style*="rgba(255,255,255,0.95"],
        html[data-theme="dark"] [style*="rgba(255, 255, 255, 0.95"] {
            background: var(--w-card) !important;
            border-color: var(--w-border) !important;
            color: var(--w-text) !important;
        }
        html[data-theme="dark"] [style*="background:#f7fafc"],
        html[data-theme="dark"] [style*="background: #f7fafc"],
        html[data-theme="dark"] [style*="background:#f8fafc"],
        html[data-theme="dark"] [style*="background: #f8fafc"] {
            background: rgba(255, 255, 255, 0.05) !important;
            color: var(--w-text) !important;
        }
        /* بطاقات المقارنة الباستيل (لوحة الرئيسية): خضراء/زرقاء/صفراء فاتحة →
           خلفية شفافة داكنة مع إبقاء نصوصها الملوّنة مقروءة */
        html[data-theme="dark"] [style*="linear-gradient(135deg, #f0fdf4"],
        html[data-theme="dark"] [style*="linear-gradient(135deg, #eff6ff"],
        html[data-theme="dark"] [style*="linear-gradient(135deg, #fefce8"] {
            background: rgba(255, 255, 255, 0.05) !important;
            border-color: var(--w-border) !important;
        }
        /* نصوص داكنة مضبوطة inline (#1a202c/#2d3748/#1e293b) → فاتحة */
        html[data-theme="dark"] [style*="color: #1a202c"],
        html[data-theme="dark"] [style*="color:#1a202c"],
        html[data-theme="dark"] [style*="color: #2d3748"],
        html[data-theme="dark"] [style*="color:#2d3748"],
        html[data-theme="dark"] [style*="color: #1e293b"],
        html[data-theme="dark"] [style*="color:#1e293b"] {
            color: var(--w-text) !important;
        }
        /* نصوص رمادية inline (#718096/#64748b/#94a3b8/#4a5568/#6b7280) → رمادي فاتح */
        html[data-theme="dark"] [style*="color: #718096"],
        html[data-theme="dark"] [style*="color:#718096"],
        html[data-theme="dark"] [style*="color: #64748b"],
        html[data-theme="dark"] [style*="color:#64748b"],
        html[data-theme="dark"] [style*="color: #4a5568"],
        html[data-theme="dark"] [style*="color:#4a5568"],
        html[data-theme="dark"] [style*="color: #6b7280"],
        html[data-theme="dark"] [style*="color:#6b7280"] {
            color: var(--w-text-muted) !important;
        }
        /* عناوين ملوّنة داكنة في بطاقات المقارنة الباستيل → تفتيحها للتباين */
        html[data-theme="dark"] [style*="color: #065f46"],
        html[data-theme="dark"] [style*="color:#065f46"] { color: #6ee7b7 !important; }
        html[data-theme="dark"] [style*="color: #1e40af"],
        html[data-theme="dark"] [style*="color:#1e40af"] { color: #93c5fd !important; }
        html[data-theme="dark"] [style*="color: #854d0e"],
        html[data-theme="dark"] [style*="color:#854d0e"] { color: #fcd34d !important; }

        /* =========================================================
           Wahy dark-mode round2 — إصلاح ارتدادات مؤكّدة كشفها مُدقّق خصمي.
           كل زوج (خلفية+نص) عولج معاً وفق القاعدة الذهبية:
           لا نص فاتح على خلفية فاتحة، ولا نص داكن على خلفية داكنة.
           ========================================================= */

        /* dashboard: .activities-title (#374151) داخل .premium-child-card
           المُعتَّمة (خلفية داكنة) → كانت داكن-على-داكن، نفتّحها. */
        html[data-theme="dark"] .activities-title {
            color: var(--w-text) !important;
        }

        /* dashboard: جزيرة "الترتيب والإنجازات" — .rankings-premium تدرّج
           فاتح (#fff7ed→#ffedd5) و.rank-item-premium خلفية white؛ نعتّمهما
           بمحدّد الكلاس (يتغلّب على التدرّج وعلى white). نصوصهما الملوّنة
           (rank-value) تبقى كما هي لأنها مقروءة على خلفية داكنة. */
        html[data-theme="dark"] .rankings-premium {
            background: var(--w-card) !important;
            border-color: var(--w-border) !important;
        }
        html[data-theme="dark"] .rank-item-premium {
            background: rgba(255, 255, 255, 0.05) !important;
            border-color: var(--w-border) !important;
        }
        /* عنوان الجزيرة (#9a3412 داكن على خلفية داكنة الآن) + تسمية الرتبة
           (#6b7280) → نفتّحهما للتباين على الخلفية الداكنة الجديدة. */
        html[data-theme="dark"] .rankings-title {
            color: #fdba74 !important;
        }
        html[data-theme="dark"] .rank-label {
            color: var(--w-text-muted) !important;
        }

        /* messages: .conv-snippet (#4a5568) داخل .conv-item المُعتَّمة،
           و.form-label (#4a5568) داخل .form-modal-box المُعتَّمة →
           كلاهما داكن-على-داكن، نفتّحهما إلى رمادي فاتح. */
        html[data-theme="dark"] .conv-snippet,
        html[data-theme="dark"] .form-label {
            color: var(--w-text-muted) !important;
        }

        /* child-detail: .cd-level-text (var(--gray-600)=#718096) على
           .cd-hero-bottom المُعتَّمة — تباين حدّي؛ نفتّحه قليلاً. */
        html[data-theme="dark"] .cd-level-text {
            color: var(--w-text-muted) !important;
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
                        <span class="nav-badge" style="position:absolute;top:2px;left:6px;background:#ef4444;color:#fff;border-radius:50%;min-width:18px;height:18px;font-size:11px;align-items:center;justify-content:center;display:{{ $pendingFamily > 0 ? 'flex' : 'none' }};" data-live="family_activities_pending" data-live-badge>{{ $pendingFamily > 0 ? $pendingFamily : 0 }}</span>
                    </a>
                    <a href="{{ route('messages.index') }}" class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" style="position: relative;">
                        <i class="fas fa-comments"></i>
                        <span>الرسائل</span>
                        @php
                            $unreadCount = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count();
                        @endphp
                        <span class="badge-notification" style="display: {{ $unreadCount > 0 ? 'inline-flex' : 'none' }};" data-live="messages_unread" data-live-badge data-live-cap="9">{{ $unreadCount > 0 ? ($unreadCount > 9 ? '9+' : $unreadCount) : 0 }}</span>
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
                        <span class="badge-notification" style="display: {{ $bulkUnreadCount > 0 ? 'inline-flex' : 'none' }};" data-live="bulk_messages_unread" data-live-badge data-live-cap="9">{{ $bulkUnreadCount > 0 ? ($bulkUnreadCount > 9 ? '9+' : $bulkUnreadCount) : 0 }}</span>
                    </a>
                    <a href="{{ route('tickets.index') }}" class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                        <i class="fas fa-life-ring"></i>
                        <span>الدعم الفنيّ</span>
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

    <!-- Bootstrap JS Bundle (للمودالات/القوائم المنسدلة في صفحات وليّ الأمر المبنيّة بـBootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Real-Time Messages System -->
    <script src="{{ asset('js/messages-realtime.js') }}"></script>
    @stack('scripts')
    
    <!-- Survey Popup Component -->
    @include('components.survey-popup')

    @include('partials.live-updates')

    @stack('after-content')
</body>
</html>
