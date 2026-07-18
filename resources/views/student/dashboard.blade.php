@extends('layouts.student-app')

@section('title', 'رحلتي في بناء القيم')

@push('styles')
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-in { animation: slideIn 0.5s ease-out; }
    .status-completed { background: #48bb78; }
    .status-progress { background: #ecc94b; }
    .status-locked { background: #cbd5e0; }

    /* بطاقات الأقسام: سطح موحّد يتبع الثيم (فاتح/داكن) بدل الأبيض الثابت.
       يحلّ «التنسيق السيّئ + اختلال الوضع الليلي/النهاري» بلا اعتماد على ترقيعات التغطية. */
    .dash-panel {
        background: var(--color-card);
        color: var(--color-text);
        border: 1px solid var(--color-border);
        border-radius: 24px;
        padding: 32px;
        box-shadow: var(--color-shadow);
    }
    .dash-panel h2 { color: var(--color-text); }
    @media (max-width: 768px) { .dash-panel { padding: 20px 15px; border-radius: 20px; } }

    /* إصلاح عرض بطاقات القيم: .values-list كانت display:grid بلا أعمدة صريحة، فالعمود يأخذ عرض
       المحتوى ويُحاذى لليمين (فراغ أبيض يسار + بطاقة ضيّقة/مقصوصة). الحل الحاسم: تكديس block
       (العنصر block يملأ عرض أبيه دائماً) — بلا كسر للكلمات، ويسري على كل المقاسات. */
    .values-tree-section .values-list { display: block !important; }
    .values-tree-section .values-list > .value-card {
        width: 100% !important;
        box-sizing: border-box;
        margin-bottom: 30px;
    }
    .values-tree-section .values-list > .value-card:last-child { margin-bottom: 0; }
    /* حاوية المفاهيم داخل البطاقة أيضاً block لتملأ العرض */
    .values-tree-section .concepts-container { display: block !important; }
    .values-tree-section .concepts-container > div { margin-bottom: 20px; box-sizing: border-box; }
    .values-tree-section .concepts-container > div:last-child { margin-bottom: 0; }

    /* أزرار الأنشطة: شبكة بطاقات مُوحّدة الحجم بشكل فاخر احترافي (بدل pills بأحجام مختلفة) */
    .student-activities {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
        gap: 12px !important;
    }
    .student-activities .activity-chip {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center;
        gap: 10px !important;
        padding: 18px 12px !important;
        border-radius: 18px !important;
        min-height: 130px;
        width: 100%;
        box-sizing: border-box;
        line-height: 1.35;
    }
    .student-activities .activity-chip:hover { filter: brightness(1.05); }
    .student-activities .activity-chip > span:first-child { font-size: 30px !important; line-height: 1; }  /* الأيقونة */
    .student-activities .activity-chip > span:nth-child(2) { font-weight: 700; overflow-wrap: break-word; }  /* العنوان */
    @media (max-width: 480px) {
        .student-activities { grid-template-columns: repeat(2, 1fr); }
    }

    /* Current Value (قيد التقدم) - Full Width & Prominent */
    .value-card-current {
        grid-column: 1 / -1 !important;
        border-width: 4px !important;
        box-shadow: 0 20px 60px rgba(236, 201, 75, 0.4) !important;
        background: linear-gradient(135deg, rgba(236, 201, 75, 0.05) 0%, rgba(255, 255, 255, 1) 100%) !important;
        animation: currentPulse 3s ease-in-out infinite !important;
    }

    @keyframes currentPulse {
        0%, 100% {
            box-shadow: 0 20px 60px rgba(236, 201, 75, 0.4);
            border-color: #ecc94b;
        }
        50% {
            box-shadow: 0 25px 70px rgba(236, 201, 75, 0.6);
            border-color: #f6d365;
        }
    }

    /* P2-A: تعطيل الـ animation الثقيل على الجوال لتحسين الأداء */
    @media (max-width: 768px) {
        .value-card-current {
            animation: none !important;
            box-shadow: 0 8px 20px rgba(236, 201, 75, 0.3) !important;
        }
        @keyframes currentPulse { 0%,100% { box-shadow: 0 8px 20px rgba(236, 201, 75, 0.3); } }
    }

    .value-card-current .value-header > div[style*="width: 80px"] {
        width: 100px !important;
        height: 100px !important;
        font-size: 52px !important;
        box-shadow: 0 15px 40px rgba(236, 201, 75, 0.5) !important;
    }

    .value-card-current h3 {
        font-size: 32px !important;
    }

    .value-card-current p[style*="color: #718096"] {
        font-size: 17px !important;
    }

    /* Responsive Design for Mobile */
    @media (max-width: 768px) {
        /* Container padding for mobile */
        .container-wrapper {
            padding-left: 12px !important;
            padding-right: 12px !important;
            padding-top: 80px !important;
            padding-bottom: 100px !important;
        }

        /* Values Tree Section - Mobile */
        .values-tree-section {
            padding: 20px 15px !important;
            border-radius: 20px !important;
            margin-bottom: 20px !important;
        }

        /* Values Tree Header - Mobile */
        .values-tree-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 15px !important;
            margin-bottom: 20px !important;
        }

        .values-tree-title {
            font-size: 20px !important;
        }

        .values-tree-title span:first-child {
            font-size: 28px !important;
        }

        .values-tree-title > span:not(:first-child) {
            font-size: 18px !important;
        }

        /* Legend - Mobile */
        .values-tree-legend {
            flex-wrap: wrap !important;
            gap: 10px !important;
            font-size: 11px !important;
            width: 100%;
        }

        .values-tree-legend > div {
            gap: 4px !important;
        }

        /* Value Cards - Mobile */
        .values-list {
            gap: 20px !important;
        }

        .value-card {
            padding: 20px 15px !important;
            border-radius: 16px !important;
            border-width: 2px !important;
        }

        /* Current Value - Mobile (Full Width) */
        .value-card-current {
            grid-column: 1 / -1 !important;
            padding: 25px 18px !important;
            margin-bottom: 10px !important;
            border-width: 3px !important;
        }

        .value-card-current .value-header > div[style*="width: 100px"] {
            width: 70px !important;
            height: 70px !important;
            font-size: 38px !important;
        }

        .value-card-current h3 {
            font-size: 22px !important;
        }

        .value-card-current p[style*="color: #718096"] {
            font-size: 14px !important;
        }

        /* Lock Icon - Mobile */
        .value-lock-icon {
            top: 10px !important;
            left: 10px !important;
            font-size: 32px !important;
        }

        /* Value Header - Mobile */
        .value-header {
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
            gap: 15px !important;
            margin-bottom: 20px !important;
        }

        .value-header > div[style*="width: 80px"] {
            width: 60px !important;
            height: 60px !important;
            font-size: 32px !important;
        }

        .value-header > div[style*="flex: 1"] {
            width: 100%;
        }

        .value-header h3 {
            font-size: 20px !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .value-header h3 span[style*="background: linear-gradient"] {
            font-size: 11px !important;
            padding: 3px 10px !important;
        }

        .value-header p[style*="color: #718096"] {
            font-size: 13px !important;
            text-align: center !important;
        }

        /* Progress Bar - Mobile */
        .value-header div[style*="background: #e2e8f0"][style*="border-radius: 20px"] {
            margin-top: 10px !important;
            height: 10px !important;
        }

        /* Concepts Container - Mobile */
        .concepts-container {
            margin-right: 0 !important;
            gap: 15px !important;
        }

        /* Concept Cards - Mobile */
        .concepts-container > div {
            padding: 18px 15px !important;
            border-radius: 12px !important;
            border-width: 3px !important;
        }

        .concepts-container > div > div[style*="font-weight: 700"][style*="font-size: 20px"] {
            font-size: 16px !important;
            flex-wrap: wrap !important;
            gap: 6px !important;
        }

        .concepts-container > div > div[style*="font-weight: 700"] > span[style*="font-size: 28px"] {
            font-size: 24px !important;
        }

        .concepts-container > div > p[style*="color: #4a5568"] {
            font-size: 13px !important;
            margin-bottom: 15px !important;
        }

        /* Meanings Container - Mobile */
        .concepts-container > div > div[style*="display: grid"][style*="gap: 15px"] {
            gap: 12px !important;
        }

        /* Meaning Cards - Mobile */
        .concepts-container > div > div[style*="display: grid"] > div {
            padding: 15px 12px !important;
            border-radius: 10px !important;
        }

        .concepts-container > div > div[style*="display: grid"] > div > div > div[style*="font-weight: 700"][style*="font-size: 18px"] {
            font-size: 15px !important;
        }

        .concepts-container > div > div[style*="display: grid"] > div > div > div[style*="font-weight: 700"] > span[style*="font-size: 24px"] {
            font-size: 20px !important;
        }

        .concepts-container > div > div[style*="display: grid"] > div > div > p[style*="color: #718096"] {
            font-size: 12px !important;
        }

        /* Lessons Container - Mobile */
        .concepts-container > div > div[style*="display: grid"] > div > div[style*="display: grid"][style*="gap: 12px"] {
            gap: 10px !important;
            margin-top: 12px !important;
        }

        /* Lesson Cards - Mobile */
        .concepts-container > div > div[style*="display: grid"] > div > div[style*="display: grid"] > div {
            padding: 15px 12px !important;
            border-radius: 8px !important;
            border-width: 2px !important;
        }

        .concepts-container > div > div[style*="display: grid"] > div > div[style*="display: grid"] > div > div[style*="font-weight: 700"][style*="font-size: 16px"] {
            font-size: 14px !important;
            margin-bottom: 10px !important;
        }

        .concepts-container > div > div[style*="display: grid"] > div > div[style*="display: grid"] > div > div[style*="font-weight: 700"] > span[style*="font-size: 20px"] {
            font-size: 18px !important;
        }

        /* Activities - Mobile */
        .concepts-container > div > div[style*="display: grid"] > div > div[style*="display: grid"] > div > div[style*="display: flex"] {
            flex-direction: column !important;
            gap: 8px !important;
            margin-top: 10px !important;
        }

        .concepts-container > div > div[style*="display: grid"] > div > div[style*="display: grid"] > div > div[style*="display: flex"] > a {
            width: 100% !important;
            justify-content: center !important;
            padding: 12px 16px !important;
            font-size: 13px !important;
        }

        .concepts-container > div > div[style*="display: grid"] > div > div[style*="display: grid"] > div > div[style*="display: flex"] > a > span[style*="font-size: 18px"] {
            font-size: 16px !important;
        }

        /* Top Stats Bar - Mobile */
        .animate-in[style*="display: grid"][style*="grid-template-columns"] {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
            margin-bottom: 20px !important;
        }

        .animate-in[style*="display: grid"] > div {
            padding: 18px 15px !important;
            border-radius: 16px !important;
        }

        .animate-in[style*="display: grid"] > div > div[style*="font-size: 48px"] {
            font-size: 36px !important;
            margin-bottom: 8px !important;
        }

        .animate-in[style*="display: grid"] > div > div[style*="font-size: 36px"] {
            font-size: 24px !important;
            margin-bottom: 4px !important;
        }

        .animate-in[style*="display: grid"] > div > div[style*="font-size: 14px"] {
            font-size: 12px !important;
        }

        .animate-in[style*="display: grid"] > div > div[style*="font-size: 18px"] {
            font-size: 14px !important;
            margin-bottom: 4px !important;
        }

        .animate-in[style*="display: grid"] > div > div[style*="font-size: 12px"] {
            font-size: 11px !important;
        }

        /* Badges Collection - Mobile */
        .animate-in.dash-panel {
            padding: 20px 15px !important;
            border-radius: 20px !important;
            margin-bottom: 20px !important;
        }

        .animate-in.dash-panel h2 {
            font-size: 20px !important;
            margin-bottom: 15px !important;
        }

        .animate-in.dash-panel h2 > span[style*="font-size: 36px"] {
            font-size: 28px !important;
        }

        .animate-in.dash-panel > div[style*="display: grid"] {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
        }

        .animate-in.dash-panel > div[style*="display: grid"] > div {
            padding: 18px 12px !important;
            border-radius: 14px !important;
        }

        .animate-in.dash-panel > div[style*="display: grid"] > div > div[style*="font-size: 56px"] {
            font-size: 40px !important;
            margin-bottom: 8px !important;
        }

        .animate-in.dash-panel > div[style*="display: grid"] > div > div[style*="font-weight: 700"][style*="font-size: 16px"] {
            font-size: 13px !important;
            margin-bottom: 4px !important;
        }

        .animate-in.dash-panel > div[style*="display: grid"] > div > div[style*="font-size: 12px"] {
            font-size: 10px !important;
        }

        /* Upcoming Homework - Mobile */
        .animate-in.dash-panel > div[style*="display: grid"][style*="grid-template-columns: repeat(auto-fill"] {
            grid-template-columns: 1fr !important;
        }

        /* Recent Activities - Mobile */
        .animate-in.dash-panel {
            padding: 20px 15px !important;
        }

        .animate-in.dash-panel > div[style*="position: relative"][style*="padding-right: 40px"] {
            padding-right: 20px !important;
        }

        .animate-in.dash-panel > div[style*="position: relative"] > div[style*="position: absolute"][style*="right: 19px"] {
            right: 9px !important;
            width: 2px !important;
        }

        .animate-in.dash-panel > div[style*="position: relative"] > div > div[style*="position: relative"] {
            padding: 15px 12px !important;
            border-radius: 12px !important;
        }

        .animate-in.dash-panel > div[style*="position: relative"] > div > div > div[style*="position: absolute"][style*="right: -60px"] {
            right: -30px !important;
            width: 30px !important;
            height: 30px !important;
            font-size: 16px !important;
        }

        .animate-in.dash-panel > div[style*="position: relative"] > div > div > div > div[style*="flex: 1"] > div > div[style*="font-weight: 700"][style*="font-size: 18px"] {
            font-size: 15px !important;
        }

        .animate-in.dash-panel > div[style*="position: relative"] > div > div > div > div > div[style*="text-align: left"] {
            text-align: center !important;
        }
    }
</style>
@endpush

@section('content')

<!-- Container with padding for status bar -->
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">

<style>
    /* بطاقة الشارات ذهبية بنصّ داكن (تباين ممتاز على الذهبي). كان dark-coverage
       يُعتّم خلفيتها الباهتة (#ffeaa7) فيختفي النص الداكن. نُبقيها ذهبية بنصّها الداكن
       في الوضع الليلي أيضاً — كأشقّائها الزاهية (نقاط/أيام). النوعية أعلى تخصّصاً
       من dark-coverage ([style]) فتفوز بصرف النظر عن الترتيب. */
    html[data-theme="dark"] .badges-stat-card[style] {
        background-image: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%) !important;
        background-color: #fdcb6e !important;
    }
    html[data-theme="dark"] .badges-stat-card > div[style] {
        color: #2d3436 !important;
    }

    /* بطاقات «آخر إنجازاتي» + المفاهيم + الدروس كانت تعتمد على dark-coverage
       لتعتيم خلفياتها الفاتحة المكتوبة inline. لكن معالِجات onmouse*="this.style.*"
       تُعيد تسلسل سمة style عند أوّل لمس (‎#f0fff4 → rgb(240,255,244)‎، و white →
       rgb(255,255,255)‎)، فتنكسر مطابقة dark-coverage للـinline وترتدّ الخلفية
       فاتحة بينما يبقى النص فاتحاً = فاتح على فاتح (يختفي المحتوى عند الضغط).
       الحلّ الجذري: (1) نقل تأثير الحوم إلى CSS ‎:hover‎ لأجهزة المؤشّر فقط —
       يمنع أيضاً «تعليق الحوم» على اللمس، و(2) إزالة معالِجات JS نهائياً فلا
       إعادة تسلسل، و(3) خلفيات ليلية صريحة قائمة على الصنف محصّنة ضدّ أيّ إعادة
       تسلسل. النوعية (‎html[data-theme] .class[style]‎) تفوق dark-coverage. */
    @media (hover: hover) {
        .ach-timeline-card:hover { transform: translateX(-10px); box-shadow: 0 8px 25px var(--glow, rgba(0,0,0,0.15)); }
        .ach-concept-card:hover  { transform: translateX(-5px);  box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .ach-lesson-card:hover   { border-color: var(--lc-hover-border, #667eea) !important; }
        .ach-lesson-head:hover   { background: #f7fafc; }
    }
    html[data-theme="dark"] .ach-timeline-card[style] {
        background: rgba(148, 163, 184, 0.10) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    html[data-theme="dark"] .ach-concept-card[style] {
        background-image: none !important;
        background-color: rgba(148, 163, 184, 0.10) !important;
    }
    html[data-theme="dark"] .ach-lesson-card[style] {
        background: rgba(148, 163, 184, 0.07) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    html[data-theme="dark"] .ach-lesson-head:hover { background: rgba(255, 255, 255, 0.05); }
</style>
<!-- Top Stats Bar -->
<div class="animate-in" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <!-- Total Points -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3); position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        <div style="font-size: 48px; margin-bottom: 10px; animation: pulse 2s infinite;">⭐</div>
        <div style="font-size: 36px; font-weight: 700; color: white; margin-bottom: 5px;">{{ $totalPoints ?? 0 }}</div>
        <div style="color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 600;">إجمالي النقاط</div>
    </div>

    <!-- Streak Days -->
    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(245, 87, 108, 0.3);">
        <div style="font-size: 48px; margin-bottom: 10px; animation: float 3s infinite;">🔥</div>
        <div style="font-size: 36px; font-weight: 700; color: white; margin-bottom: 5px;">{{ $streak->current_streak ?? 0 }}</div>
        <div style="color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 600;">أيام متتالية</div>
    </div>

    <!-- Badges -->
    <div class="badges-stat-card" style="background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%); border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(253, 203, 110, 0.3);">
        <div style="font-size: 48px; margin-bottom: 10px;">🏅</div>
        <div style="font-size: 36px; font-weight: 700; color: #2d3436; margin-bottom: 5px;">{{ $badges->count() }}</div>
        <div style="color: #2d3436; font-size: 14px; font-weight: 600;">شارة مكتسبة</div>
    </div>

    <!-- Next Challenge Button -->
    <div style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(9, 132, 227, 0.3); cursor: pointer; transition: transform 0.3s;"
         onmouseover="this.style.transform='translateY(-5px) scale(1.02)'"
         onmouseout="this.style.transform='translateY(0) scale(1)'"
         onclick="document.querySelector('#values-tree').scrollIntoView({behavior: 'smooth'})">
        <div style="font-size: 48px; margin-bottom: 10px;">🚀</div>
        <div style="font-size: 18px; font-weight: 700; color: white; margin-bottom: 5px;">ابدأ التحدي التالي</div>
        <div style="color: rgba(255,255,255,0.9); font-size: 12px;">اكتشف قيمة جديدة</div>
    </div>
    
    <!-- Rate Teachers Button -->
    <div style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(251, 191, 36, 0.3); cursor: pointer; transition: transform 0.3s;"
         onmouseover="this.style.transform='translateY(-5px) scale(1.02)'"
         onmouseout="this.style.transform='translateY(0) scale(1)'"
         onclick="window.location.href='{{ route('student.rate.teachers') }}'">
        <div style="font-size: 48px; margin-bottom: 10px;">⭐</div>
        <div style="font-size: 18px; font-weight: 700; color: white; margin-bottom: 5px;">قيّم معلميك</div>
        <div style="color: rgba(255,255,255,0.9); font-size: 12px;">شارك رأيك وتقديرك</div>
    </div>
</div>

<!-- Badges Collection -->
@if($badges->count() > 0)
<div class="animate-in dash-panel" style="margin-bottom: 30px;">
    <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 36px;">🏅</span> 
        <span>مجموعة شاراتي</span>
        <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; margin-right: auto;">{{ $badges->count() }} شارة</span>
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 20px;">
        @foreach($badges as $badge)
        <div style="text-align: center; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px 20px; border-radius: 18px; box-shadow: 0 8px 25px rgba(245, 87, 108, 0.25); cursor: pointer; transition: all 0.3s; position: relative; overflow: hidden;"
             onmouseover="this.style.transform='translateY(-8px) rotate(2deg)'; this.style.boxShadow='0 15px 40px rgba(245, 87, 108, 0.4)'"
             onmouseout="this.style.transform='translateY(0) rotate(0)'; this.style.boxShadow='0 8px 25px rgba(245, 87, 108, 0.25)'">
            <div style="position: absolute; top: -10px; right: -10px; background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 50%;"></div>
            <div style="font-size: 56px; margin-bottom: 12px; animation: pulse 2s infinite;">{{ $badge->icon }}</div>
            <div style="font-weight: 700; color: white; font-size: 16px; margin-bottom: 6px;">{{ $badge->name }}</div>
            <div style="font-size: 12px; color: rgba(255,255,255,0.95); line-height: 1.4;">{{ $badge->description }}</div>
            <div style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); background: rgba(255,255,255,0.3); padding: 3px 10px; border-radius: 12px; font-size: 10px; color: white; font-weight: 600;">
                {{ $badge->pivot->earned_at ? \Carbon\Carbon::parse($badge->pivot->earned_at)->format('Y/m/d') : 'جديد' }}
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Upcoming Homework -->
@if(isset($upcomingHomework) && $upcomingHomework->count() > 0)
<div class="animate-in dash-panel" style="margin-bottom: 30px;">
    <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 36px;">📚</span> 
        <span>الواجبات المنزلية القادمة</span>
        <span style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; margin-left: auto;">{{ $upcomingHomework->count() }}</span>
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        @foreach($upcomingHomework as $homework)
        <div style="background: linear-gradient(135deg, 
                    @if($homework->urgency === 'urgent') #ff6b6b 0%, #ee5a6f 100% 
                    @elseif($homework->urgency === 'soon') #ffa502 0%, #ff7f00 100%
                    @else #74b9ff 0%, #0984e3 100% @endif); 
                    border-radius: 18px; padding: 25px; position: relative; overflow: hidden; cursor: pointer; transition: all 0.3s; box-shadow: 0 8px 25px rgba(0,0,0,0.15);"
             onclick="window.location.href='{{ route('student.activity', $homework->id) }}'"
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 40px rgba(0,0,0,0.2)'"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)'">
            
            <div style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.3); padding: 5px 12px; border-radius: 12px; font-size: 11px; color: white; font-weight: 700;">
                @if($homework->urgency === 'urgent') ⚠️ عاجل! @elseif($homework->urgency === 'soon') ⏰ قريباً @else ✨ جديد @endif
            </div>
            
            <div style="font-size: 48px; margin-bottom: 15px; text-align: center;">
                @if($homework->type === 'quiz') 📝 @elseif($homework->type === 'upload') 📤 @elseif($homework->type === 'practical') 🎯 @else 💬 @endif
            </div>
            
            <h3 style="font-size: 20px; font-weight: 700; color: white; margin-bottom: 10px; text-align: center;">
                {{ $homework->title }}
            </h3>
            
            <div style="text-align: center; margin-bottom: 15px;">
                <div style="font-size: 13px; color: rgba(255,255,255,0.9); font-weight: 600; margin-bottom: 5px;">
                    📅 الموعد النهائي
                </div>
                <div style="font-size: 16px; color: white; font-weight: 700;">
                    {{ $homework->due_date->format('Y/m/d') }}
                </div>
                <div style="font-size: 14px; color: rgba(255,255,255,0.95);">
                    {{ $homework->due_date->format('H:i') }}
                </div>
            </div>
            
            <div style="background: rgba(255,255,255,0.2); border-radius: 12px; padding: 10px; text-align: center;">
                <div style="font-size: 12px; color: rgba(255,255,255,0.9); margin-bottom: 3px;">المتبقي</div>
                <div style="font-size: 18px; color: white; font-weight: 700;">
                    {{ $homework->due_date->diffForHumans() }}
                </div>
            </div>
            
            <div style="position: absolute; bottom: 15px; left: 15px; background: rgba(255,255,255,0.3); padding: 5px 12px; border-radius: 12px; font-size: 12px; color: white; font-weight: 700;">
                ⭐ {{ $homework->points }} نقطة
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Values Tree -->
<div id="values-tree" class="animate-in values-tree-section dash-panel" style="margin-bottom: 30px;">
    <div class="values-tree-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 class="values-tree-title" style="font-size: 28px; font-weight: 700; color: #1a202c; display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 36px;">🌳</span> 
            <span>شجرة القيم - رحلتي التعليمية</span>
        </h2>
        <div class="values-tree-legend" style="display: flex; gap: 15px; align-items: center; font-size: 13px; font-weight: 600;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <div class="status-completed" style="width: 20px; height: 20px; border-radius: 50%;"></div>
                <span style="color: #2d3748;">مكتمل</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <div class="status-progress" style="width: 20px; height: 20px; border-radius: 50%;"></div>
                <span style="color: #2d3748;">قيد التقدم</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <div class="status-locked" style="width: 20px; height: 20px; border-radius: 50%;"></div>
                <span style="color: #2d3748;">مقفل</span>
            </div>
        </div>
    </div>
    
    <div class="values-list" style="display: grid; gap: 30px;">
        @foreach($values as $index => $value)
        @php
            // استخدام البيانات الحقيقية من Controller
            $isUnlocked = $value->is_unlocked ?? ($index === 0);
            $isCompleted = $value->is_completed ?? false;
            $isInProgress = $isUnlocked && !$isCompleted; // القيمة قيد التقدم
            $progressPercent = $value->progress_percent ?? 0;
            $statusColor = $isCompleted ? '#48bb78' : ($isUnlocked ? '#ecc94b' : '#cbd5e0');
            $statusGlow = $isCompleted ? 'rgba(72, 187, 120, 0.3)' : ($isUnlocked ? 'rgba(236, 201, 75, 0.3)' : 'rgba(203, 213, 224, 0.3)');
        @endphp
        
        <div class="value-card {{ $isInProgress ? 'value-card-current' : '' }} {{ $isCompleted ? 'value-card-completed' : '' }} {{ !$isUnlocked ? 'value-card-locked' : '' }}" style="border: 3px solid {{ $statusColor }}; border-radius: 20px; padding: 30px; transition: all 0.4s; cursor: pointer; position: relative; overflow: hidden; {{ !$isUnlocked ? 'opacity: 0.6; pointer-events: none;' : '' }}"
             onmouseover="if({{ $isUnlocked ? 'true' : 'false' }}) { this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 40px {{ $statusGlow }}'; }"
             onmouseout="if({{ $isUnlocked ? 'true' : 'false' }}) { this.style.transform='translateY(0)'; this.style.boxShadow='none'; }">
            
            <!-- Progress Bar Background -->
            <div style="position: absolute; top: 0; left: 0; width: {{ $progressPercent }}%; height: 100%; background: linear-gradient(90deg, {{ $statusColor }}10, {{ $statusColor }}05); transition: width 0.6s ease;"></div>
            
            <!-- Lock Icon for Locked Values -->
            @if(!$isUnlocked)
            <div class="value-lock-icon" style="position: absolute; top: 20px; left: 20px; font-size: 48px; opacity: 0.3;">🔒</div>
            @endif
            
            <!-- Value Header -->
            <div class="value-header" style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px; position: relative; z-index: 1;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, {{ $statusColor }} 0%, {{ $statusColor }}cc 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 42px; box-shadow: 0 10px 30px {{ $statusGlow }}; animation: {{ $isUnlocked && !$isCompleted ? 'pulse 2s infinite' : 'none' }};">
                    {{ $value->icon }}
                </div>
                <div style="flex: 1;">
                    <h3 style="font-size: 26px; font-weight: 700; color: #1a202c; margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                        {{ $value->name }}
                        @if($isCompleted)
                        <span style="font-size: 20px; animation: float 3s infinite;">✅</span>
                        @elseif($isUnlocked)
                        <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4px 12px; border-radius: 15px; font-size: 13px; font-weight: 600;">جاري التعلم</span>
                        @endif
                    </h3>
                    <p style="color: #718096; font-size: 15px; line-height: 1.6;">{{ $value->description }}</p>
                    
                    <!-- Progress Bar -->
                    @if($isUnlocked)
                    <div style="margin-top: 12px; background: #e2e8f0; border-radius: 20px; height: 12px; overflow: hidden;">
                        <div style="width: {{ $progressPercent }}%; height: 100%; background: linear-gradient(90deg, {{ $statusColor }}, {{ $statusColor }}dd); border-radius: 20px; transition: width 0.6s ease; position: relative;">
                            <div style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); color: white; font-size: 9px; font-weight: 700;">{{ $progressPercent }}%</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Concepts -->
            @if($value->concepts->count() > 0 && $isUnlocked)
            <div class="concepts-container" style="margin-right: 40px; display: grid; gap: 20px; position: relative; z-index: 1;">
                @foreach($value->concepts as $conceptIndex => $concept)
                @php
                    // استخدام البيانات الحقيقية من Controller
                    $conceptCompleted = $concept->is_completed ?? false;
                    $conceptColor = $conceptCompleted ? '#48bb78' : '#667eea';
                @endphp
                
                <div class="ach-concept-card" style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 15px; padding: 25px; border-right: 5px solid {{ $conceptColor }}; transition: all 0.3s;">
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                        <div style="font-weight: 700; color: #2d3748; font-size: 20px; display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 28px;">📚</span>
                            {{ $concept->name }}
                        </div>
                        @if($conceptCompleted)
                        <span style="background: #48bb78; color: white; padding: 6px 15px; border-radius: 20px; font-size: 12px; font-weight: 600;">✓ مكتمل</span>
                        @endif
                    </div>
                    
                    <p style="color: #4a5568; font-size: 15px; margin-bottom: 20px; line-height: 1.6;">{{ $concept->description }}</p>
                    
                    <!-- Lessons -->
                    @if($concept->lessons->count() > 0)
                    <div style="display: grid; gap: 15px;">
                        @foreach($concept->lessons as $lesson)
                        @php
                            // استخدام البيانات الحقيقية من Controller
                            $lessonCompleted = $lesson->is_completed ?? false;
                        @endphp
                        
                        <div class="ach-lesson-card" style="--lc-hover-border: {{ $lessonCompleted ? '#48bb78' : '#667eea' }}; background: white; border-radius: 12px; padding: 20px; border: 2px solid {{ $lessonCompleted ? '#48bb78' : '#e2e8f0' }}; transition: all 0.3s;">
                            <div class="ach-lesson-head" style="display: flex; align-items: start; justify-content: space-between; gap: 15px; margin-bottom: 15px; cursor: pointer; padding: 8px; border-radius: 10px; transition: background 0.2s;"
                                 onclick="window.location.href='{{ route('student.lesson', $lesson->id) }}'">
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; color: #2d3748; font-size: 18px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 24px;">{{ $lessonCompleted ? '✅' : '💡' }}</span>
                                        {{ $lesson->title }}
                                        <span style="font-size: 12px; color: #667eea; margin-right: auto;">📖 اضغط للتفاصيل</span>
                                    </div>
                                    <p style="color: #718096; font-size: 14px; line-height: 1.5;">{{ html_excerpt($lesson->description ?? $lesson->content, 100) }}</p>

                                    {{-- شارة/شريط مكافأة الالتزام اليومي (كهرماني ذاتي التباين يُقرأ في الوضعين) --}}
                                    @if($lesson->hasStreakEnabled())
                                    @php
                                        $__ls        = $lessonStreaks[$lesson->id] ?? null;
                                        $__lsDone    = (int) ($__ls->completed_days ?? 0);
                                        $__lsMin     = (int) $lesson->streak_min_days;
                                        $__lsClaimed = (bool) ($__ls->bonus_claimed ?? false);
                                        $__lsPct     = $__lsMin > 0 ? min(100, round($__lsDone / $__lsMin * 100)) : 0;
                                    @endphp
                                    <div style="margin-top:10px; background:linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border:1.5px solid #f59e0b; border-radius:12px; padding:9px 12px;">
                                        @if($__lsClaimed)
                                            <div style="display:flex; align-items:center; gap:8px; font-size:12.5px; font-weight:800; color:#92400e;">
                                                <span style="font-size:16px;">🏆</span>
                                                <span>مكافأة الالتزام محقّقة!</span>
                                            </div>
                                        @elseif($__lsDone > 0)
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; font-size:12.5px; font-weight:800; color:#92400e;">
                                                <span style="display:flex; align-items:center; gap:6px;"><span style="font-size:15px;">🔥</span> التزام: يوم {{ $__lsDone }} من {{ $__lsMin }}</span>
                                                <span style="font-size:11px; font-weight:700; color:#b45309;">🚀 استمرّ!</span>
                                            </div>
                                            <div style="margin-top:7px; background:rgba(255,255,255,0.55); border-radius:8px; height:7px; overflow:hidden;">
                                                <div style="height:100%; border-radius:8px; background:linear-gradient(90deg,#f59e0b,#d97706); width:{{ $__lsPct }}%;"></div>
                                            </div>
                                        @else
                                            <div style="display:flex; align-items:center; gap:8px; font-size:12.5px; font-weight:800; color:#92400e;">
                                                <span style="font-size:16px;">🔥</span>
                                                <span>مكافأة التزام — ابدأ اليوم بأوّل نشاط!</span>
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Activities -->
                            @if($lesson->activities->count() > 0)
                            <div class="student-activities" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 12px;">
                                @foreach($lesson->activities as $activity)
                                @php
                                    // استخدام البيانات الحقيقية من Controller
                                    $activityCompleted = $activity->is_completed ?? false;
                                    $activityIcon = match($activity->type ?? 'default') {
                                        'quiz' => '✏️',
                                        'file_upload' => '📤',
                                        'team_activity' => '👥',
                                        'practical' => '🎯',
                                        default => '📝'
                                    };
                                @endphp
                                
                                <a href="{{ route('student.activity', $activity->id) }}" class="activity-chip" style="display: inline-flex; align-items: center; gap: 8px; background: {{ $activityCompleted ? 'linear-gradient(135deg, #48bb78 0%, #38a169 100%)' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }}; color: white; padding: 10px 18px; border-radius: 25px; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.3s; box-shadow: 0 4px 15px {{ $activityCompleted ? 'rgba(72, 187, 120, 0.3)' : 'rgba(102, 126, 234, 0.3)' }}; position: relative;"
                                   onmouseover="this.style.transform='translateY(-3px) scale(1.05)'; this.style.boxShadow='0 8px 25px {{ $activityCompleted ? 'rgba(72, 187, 120, 0.5)' : 'rgba(102, 126, 234, 0.5)' }}'"
                                   onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 15px {{ $activityCompleted ? 'rgba(72, 187, 120, 0.3)' : 'rgba(102, 126, 234, 0.3)' }}'">
                                    
                                    <span style="font-size: 18px;">{{ $activityIcon }}</span>
                                    <span>{{ $activity->title }}</span>
                                    <span style="background: rgba(255,255,255,0.3); padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;">
                                        {{ $activityCompleted ? '✓' : '+' }}{{ $activity->points }} نقطة
                                    </span>
                                    
                                    @if($activityCompleted)
                                    <span style="position: absolute; top: -5px; right: -5px; background: #fff; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">✅</span>
                                    @endif
                                </a>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

<!-- Recent Activities Timeline -->
@if($recentActivities->count() > 0)
<div class="animate-in dash-panel">
    <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px; display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 36px;">📊</span> 
        <span>آخر إنجازاتي</span>
        <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; margin-right: auto;">{{ $recentActivities->count() }} نشاط</span>
    </h2>
    
    <div style="position: relative; padding-right: 40px;">
        <!-- Timeline Line -->
        <div style="position: absolute; right: 19px; top: 0; bottom: 0; width: 3px; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);"></div>
        
        <div style="display: grid; gap: 20px;">
            @foreach($recentActivities as $submission)
            {{-- تخطّي أي تسليم يتيم (نشاطه محذوف) حتى لا تُسقِط سلسلة null الصفحة كاملةً (خطأ 500) --}}
            @continue(! $submission->activity)
            @php
                $statusConfig = match($submission->status) {
                    'completed' => ['color' => '#48bb78', 'bg' => '#f0fff4', 'icon' => '✅', 'text' => 'مكتمل', 'glow' => 'rgba(72, 187, 120, 0.3)'],
                    'approved' => ['color' => '#48bb78', 'bg' => '#f0fff4', 'icon' => '✅', 'text' => 'موافق عليه', 'glow' => 'rgba(72, 187, 120, 0.3)'],
                    'pending' => ['color' => '#ed8936', 'bg' => '#fffaf0', 'icon' => '⏳', 'text' => 'قيد المراجعة', 'glow' => 'rgba(237, 137, 54, 0.3)'],
                    'rejected' => ['color' => '#f56565', 'bg' => '#fff5f5', 'icon' => '❌', 'text' => 'مرفوض', 'glow' => 'rgba(245, 101, 101, 0.3)'],
                    default => ['color' => '#718096', 'bg' => '#f7fafc', 'icon' => '📝', 'text' => $submission->status, 'glow' => 'rgba(113, 128, 150, 0.3)']
                };
            @endphp
            
            <div class="ach-timeline-card" style="--glow: {{ $statusConfig['glow'] }}; position: relative; background: {{ $statusConfig['bg'] }}; border-radius: 15px; padding: 20px; border: 2px solid {{ $statusConfig['color'] }}20; transition: all 0.3s; cursor: pointer;">
                
                <!-- Timeline Dot -->
                <div style="position: absolute; right: -60px; top: 25px; width: 40px; height: 40px; background: {{ $statusConfig['color'] }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 15px {{ $statusConfig['glow'] }}; border: 4px solid white;">
                    {{ $statusConfig['icon'] }}
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <span style="font-size: 24px;">
                                @php
                                    echo match($submission->activity->type) {
                                        'quiz' => '✏️',
                                        'file_upload' => '📤',
                                        'team_activity' => '👥',
                                        'practical' => '🎯',
                                        default => '📝'
                                    };
                                @endphp
                            </span>
                            <div>
                                <div style="font-weight: 700; color: #2d3748; font-size: 18px;">{{ $submission->activity->title }}</div>
                                @if($submission->activity->lesson)
                                <div style="font-size: 14px; color: #718096; margin-top: 3px;">📖 {{ $submission->activity->lesson->title }}</div>
                                @endif
                            </div>
                        </div>
                        
                        @php
                            // النقاط المكتسبة = (الدرجة٪ ÷ 100) × نقاط النشاط — مطابق لصيغة المنح في
                            // SubmitActivityAction. سابقاً كان يُعرض $submission->score (النسبة) كأنه نقاط،
                            // فيَظهر ثابتاً/خاطئاً ولا يراعي نقاط النشاط ولا حالة الرفض (المرفوض يكتسب 0).
                            $activityPoints = (int) ($submission->activity->points ?? 20);
                            $earnedPoints = in_array($submission->status, ['completed', 'approved'], true) && $submission->score !== null
                                ? (int) round(($submission->score / 100) * $activityPoints)
                                : 0;
                            $showPoints = in_array($submission->status, ['completed', 'approved', 'rejected'], true);
                        @endphp
                        @if($showPoints)
                        <div style="margin-top: 12px; display: inline-flex; align-items: center; gap: 8px; background: white; padding: 8px 15px; border-radius: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <span style="font-size: 18px;">⭐</span>
                            <span style="font-weight: 700; color: #2d3748; font-size: 16px;">{{ $earnedPoints }}</span>
                            <span style="color: #718096; font-size: 13px;">/ {{ $activityPoints }} نقطة</span>
                        </div>
                        @endif
                        
                        @if($submission->teacher_feedback)
                        <div style="margin-top: 12px; background: white; padding: 12px; border-radius: 10px; border-right: 3px solid {{ $statusConfig['color'] }};">
                            <div style="font-size: 12px; color: #718096; margin-bottom: 5px; font-weight: 600;">💬 ملاحظات المعلم:</div>
                            <div style="color: #4a5568; font-size: 14px; line-height: 1.5;">{{ $submission->teacher_feedback }}</div>
                        </div>
                        @endif
                    </div>
                    
                    <div style="text-align: left;">
                        <span style="display: inline-block; background: {{ $statusConfig['color'] }}; color: white; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 8px; box-shadow: 0 4px 12px {{ $statusConfig['glow'] }};">
                            {{ $statusConfig['icon'] }} {{ $statusConfig['text'] }}
                        </span>
                        <div style="font-size: 12px; color: #718096; text-align: center;">
                            {{ $submission->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    // Sound Effects (optional - يمكن إضافة مكتبة صوت لاحقاً)
    function playSuccessSound() {
        // TODO: Add sound library like Howler.js
        console.log('🎉 Success sound!');
    }
    
    // Smooth scroll for activities
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
</script>
@endpush

</div> <!-- End container -->
