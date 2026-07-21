@extends('layouts.student-app')

@section('title', $lesson->title ?? 'الدرس')

@push('styles')
<style>
    /* Lesson View — الثيم موحّد مع بقيّة صفحات الطالب عبر var(--app-bg).
       الوضع الفاتح يستخدم html[data-theme="light"] (متوافق مع الـ toggle العالمي). */
    /* الخلفية موحّدة مع بقيّة صفحات الطالب — ترث ثيم الـ layout
       (داكن حقيقي #1e1b4b→#0b1220 ليلاً، فاتح نهاراً) بدل البنفسجي الساطع الثابت. */
    body {
        background: var(--app-bg);
    }
    html[data-theme="light"] body {
        color: #1e293b;
    }
    html[data-theme="light"] .rich-content { color: #1e293b; }
    html[data-theme="light"] .rich-content a { color: #1d4ed8; }
    html[data-theme="light"] .content-text { color: #334155; }
    html[data-theme="light"] .lesson-header { background: rgba(255,255,255,0.7); }
    html[data-theme="light"] .lesson-back-btn { color: #334155; background: rgba(0,0,0,0.06); }
    /* شارة نوع القسم تدرّج علاميّ بنصّ أبيض مقروء في الوضعين — لا حاجة لتسطيحها رماديّاً نهاراً */

    /* الوضع النهاري: النصوص البيضاء المُصلَّبة تصبح غير مقروءة على الخلفية الفاتحة — نجعلها داكنة (Issue: تباين الوضع النهاري) */
    html[data-theme="light"] .lesson-content-card { color: #334155; }
    html[data-theme="light"] .lesson-title-main,
    html[data-theme="light"] .section-header,
    html[data-theme="light"] .activity-title { color: #1e293b; }
    html[data-theme="light"] .lesson-meta-info,
    html[data-theme="light"] .progress-text,
    html[data-theme="light"] .activity-meta { color: #475569; }
    html[data-theme="light"] .lesson-title-section { border-bottom-color: rgba(15,23,42,0.10); }
    html[data-theme="light"] .activity-card { background: rgba(255,255,255,0.7); border-color: rgba(15,23,42,0.08); }
    html[data-theme="light"] .activity-card:hover { background: rgba(255,255,255,0.9); }
    html[data-theme="light"] .activity-card.completed { background: rgba(34,197,94,0.12); border-color: rgba(34,197,94,0.4); }
    html[data-theme="light"] .activity-status.locked { background: rgba(15,23,42,0.06); color: #64748b; }
    /* حالة «لا أنشطة»: النص الأبيض inline → داكن */
    html[data-theme="light"] .lesson-content-card h3[style*="color: white"],
    html[data-theme="light"] .lesson-content-card p[style*="rgba(255,255,255"] { color: #334155 !important; }

    /* نهاراً: البطاقات الزجاجيّة تحتاج حدّاً/ظلّاً مرئيّاً وإلا ذابت في الخلفية الفاتحة */
    html[data-theme="light"] .lesson-content-card,
    html[data-theme="light"] .lesson-header {
        border-color: var(--color-border);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }
    html[data-theme="light"] .lesson-content-card { background: rgba(255, 255, 255, 0.82); }
    /* وعاء شريط التقدّم الفارغ يجب أن يُرى نهاراً فوق الرأس الفاتح */
    html[data-theme="light"] .progress-bar-track { background: rgba(15, 23, 42, 0.10); }

    .lesson-container {
        max-width: 900px;
        margin: 0 auto;
        /* الحشو الأفقيّ يتكفّل به .container-wrapper (20px) — نُصفّره هنا لمنع الازدواج على الجوّال */
        padding: var(--spacing-lg) 0 120px;
    }
    
    /* Minimal Header */
    .lesson-header {
        background: var(--glass-bg-light);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .lesson-back-btn {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 10px 20px;
        border-radius: var(--radius-full);
        cursor: pointer;
        transition: all var(--transition-base);
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 14px;
    }
    
    .lesson-back-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateX(5px);
    }
    
    .lesson-progress-bar {
        flex: 1;
        max-width: 400px;
        margin: 0 var(--spacing-lg);
    }
    
    .progress-bar-track {
        background: rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-full);
        height: 8px;
        overflow: hidden;
        position: relative;
    }
    
    .progress-bar-fill {
        background: linear-gradient(90deg, var(--color-primary), var(--color-success));
        height: 100%;
        border-radius: var(--radius-full);
        transition: width var(--transition-slow);
    }
    
    .progress-text {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.8);
        text-align: center;
        margin-top: 6px;
    }
    
    /* Main Content Card */
    .lesson-content-card {
        background: var(--glass-bg-heavy);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-2xl);
        padding: var(--spacing-2xl);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        margin-bottom: var(--spacing-xl);
    }
    
    .lesson-title-section {
        text-align: center;
        margin-bottom: var(--spacing-xl);
        padding-bottom: var(--spacing-xl);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .lesson-icon-large {
        font-size: 64px;
        margin-bottom: var(--spacing-md);
    }
    
    .lesson-title-main {
        font-size: 32px;
        font-weight: 700;
        color: white;
        margin-bottom: var(--spacing-sm);
    }
    
    .lesson-meta-info {
        display: flex;
        justify-content: center;
        gap: var(--spacing-lg);
        font-size: 14px;
        color: rgba(255, 255, 255, 0.7);
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    /* Content Section */
    .content-section {
        margin-bottom: var(--spacing-2xl);
    }
    
    .section-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
        padding: 6px 16px;
        border-radius: var(--radius-full);
        font-size: 13px;
        font-weight: 600;
        margin-bottom: var(--spacing-md);
    }
    
    .content-text {
        color: rgba(255, 255, 255, 0.9);
        font-size: 16px;
        line-height: 1.8;
        margin-bottom: var(--spacing-lg);
    }

    /* تنسيق محتوى المحرّر الغنيّ داخل الدرس — يُعرَض على «سطح أبيض» مطابق لخلفية المحرّر،
       فتظهر ألوان المؤلّف (نصّ وخلفيّة) كما اختارها بالضبط (WYSIWYG) وتبقى مقروءة. */
    .rich-content {
        color: #1f2937;
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    .rich-content p { margin-bottom: 12px; }
    .rich-content img { max-width: 100%; border-radius: 10px; margin: 12px 0; height: auto; }
    .rich-content a { color: #2563eb; text-decoration: underline; }
    .rich-content ul, .rich-content ol { padding-right: 24px; margin-bottom: 12px; }
    .rich-content li { margin-bottom: 6px; }
    .rich-content b, .rich-content strong { font-weight: 700; }
    .rich-content h1, .rich-content h2, .rich-content h3, .rich-content h4 { margin-bottom: 10px; }
    /* عرض الصور المُدرَجة بحجم مناسب */
    .rich-content figure, .rich-content figure img { max-width: 100%; }
    
    /* Video/Media Container */
    .media-container {
        background: rgba(0, 0, 0, 0.3);
        border-radius: var(--radius-xl);
        overflow: hidden;
        margin: var(--spacing-lg) 0;
        aspect-ratio: 16/9;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    
    .media-placeholder {
        text-align: center;
        color: rgba(255, 255, 255, 0.5);
    }
    
    .media-placeholder-icon {
        font-size: 64px;
        margin-bottom: var(--spacing-sm);
    }
    
    /* Activities List */
    .activities-section {
        margin-top: var(--spacing-2xl);
    }
    
    .section-header {
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin-bottom: var(--spacing-lg);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .activity-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-md);
        cursor: pointer;
        transition: all var(--transition-base);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .activity-card:hover {
        background: rgba(255, 255, 255, 0.1);
        /* لا إزاحة أفقيّة (كانت تُسبّب تموّجاً/تجاوزاً في RTL على الجوّال) — نكتفي بالحدّ والظلّ */
        border-color: var(--color-primary);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
    }
    
    .activity-card.completed {
        border-color: var(--color-success);
        background: rgba(34, 197, 94, 0.1);
    }
    
    .activity-info {
        flex: 1;
    }
    
    .activity-title {
        font-size: 18px;
        font-weight: 700;
        color: white;
        margin-bottom: 6px;
    }
    
    .activity-meta {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.6);
        display: flex;
        gap: var(--spacing-md);
    }
    
    .activity-status {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: var(--radius-full);
        font-weight: 600;
        font-size: 14px;
    }
    
    .activity-status.completed {
        background: linear-gradient(135deg, var(--color-success), #16A34A);
        color: white;
    }
    
    .activity-status.pending {
        background: linear-gradient(135deg, var(--color-warning), #D97706);
        color: white;
    }
    
    .activity-status.locked {
        background: rgba(203, 213, 224, 0.2);
        color: rgba(255, 255, 255, 0.5);
    }
    
    .activity-status.available {
        background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
        color: white;
    }
    
    /* Floating Action Button */
    .floating-cta {
        position: fixed;
        bottom: calc(80px + var(--spacing-lg));
        right: var(--spacing-lg);
        background: linear-gradient(135deg, var(--color-primary), var(--color-success));
        color: white;
        padding: 16px 32px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 16px;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
        cursor: pointer;
        transition: all var(--transition-base);
        z-index: 100;
        display: flex;
        align-items: center;
        gap: 10px;
        border: none;
    }
    
    .floating-cta:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 12px 32px rgba(16, 185, 129, 0.6);
    }
    
    .floating-cta:active {
        transform: translateY(-2px) scale(1.02);
    }
    
    /* XP Reward Badge */
    .xp-badge {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        color: #7C3AED;
        padding: 8px 16px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
    }
    
    /* غلاف موحّد للصوت (كان <audio> عارياً خارج نسق البطاقات) + مواءمة واجهة المشغّل مع الثيم */
    .media-audio {
        background: var(--color-card);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: var(--spacing-md);
        margin-top: var(--spacing-md);
    }
    .media-audio audio {
        width: 100%;
        display: block;
    }
    /* منع المشغّلات الأصليّة من الظهور بواجهة فاتحة قاسية داخل بطاقة داكنة */
    .media-audio audio,
    .media-container video {
        color-scheme: light dark;
    }

    /* نقاط أيام الالتزام: أصناف صريحة بتباين كافٍ في الوضعين بدل ألوان inline لا يلتقطها dark-coverage */
    .streak-day-dot {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 800;
    }
    .streak-day-dot.filled {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
        box-shadow: 0 2px 6px rgba(217, 119, 6, .45);
    }
    .streak-day-dot.empty {
        background: rgba(255, 255, 255, .65);
        color: #92400e;
        border: 2px dashed #f59e0b;
    }
    html[data-theme="dark"] .streak-day-dot.empty {
        background: rgba(255, 255, 255, .10);
        color: #fcd34d;
        border-color: #b45309;
    }

    @media (max-width: 767px) {
        .lesson-header {
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .lesson-progress-bar {
            width: 100%;
            max-width: 100%;
            margin: 0;
        }
        
        .floating-cta {
            right: 50%;
            transform: translateX(50%);
            bottom: calc(80px + var(--spacing-md));
        }
        
        .floating-cta:hover {
            transform: translateX(50%) translateY(-4px) scale(1.05);
        }
    }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">{{-- الحشو العلويّ من .student-main (شريط الحالة sticky داخل التدفّق) — لا نُكرّره لتفادي الفجوة العلوية المفرطة --}}
<div class="lesson-container fade-in">

    {{-- استبيان التقييم القبلي/البعدي المرتبط بالدرس --}}
    @php $__assessSurvey = ($preSurvey ?? null) ?: ($postSurvey ?? null); @endphp
    @if($__assessSurvey)
    <a href="{{ route('survey.show', $__assessSurvey->id) }}"
       style="display:flex;align-items:center;gap:16px;text-decoration:none;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:white;border-radius:16px;padding:18px 22px;margin-bottom:20px;box-shadow:0 8px 24px rgba(109,40,217,.35);">
        <span style="font-size:36px;">📊</span>
        <div style="flex:1;">
            <div style="font-weight:800;font-size:17px;margin-bottom:4px;">
                @if($preSurvey ?? null) التقييم القبلي — أجب عليه قبل بدء الدرس
                @else 🎉 أكملت الدرس! أجب على التقييم البعدي
                @endif
            </div>
            <div style="font-size:13px;opacity:.9;">{{ $__assessSurvey->title }}</div>
        </div>
        <span style="background:rgba(255,255,255,.2);padding:10px 18px;border-radius:10px;font-weight:700;white-space:nowrap;">ابدأ الآن ←</span>
    </a>
    @endif

    {{-- استبيان التقييم القبلي/البعدي على مستوى القيمة (تلقائي مع تقدّم القيمة) --}}
    @php $__valueAssessSurvey = ($valuePreSurvey ?? null) ?: ($valuePostSurvey ?? null); @endphp
    @if($__valueAssessSurvey)
    <a href="{{ route('survey.show', $__valueAssessSurvey->id) }}"
       style="display:flex;align-items:center;gap:16px;text-decoration:none;background:linear-gradient(135deg,#0ea5e9,#0369a1);color:white;border-radius:16px;padding:18px 22px;margin-bottom:20px;box-shadow:0 8px 24px rgba(3,105,161,.35);">
        <span style="font-size:36px;">🌟</span>
        <div style="flex:1;">
            <div style="font-weight:800;font-size:17px;margin-bottom:4px;">
                @if($valuePreSurvey ?? null) تقييم القيمة: قبلي — أجب عليه قبل بدء دروس القيمة
                @else 🏆 أتقنت القيمة! أجب على تقييم القيمة: بعدي
                @endif
            </div>
            <div style="font-size:13px;opacity:.9;">{{ $__valueAssessSurvey->title }}</div>
        </div>
        <span style="background:rgba(255,255,255,.2);padding:10px 18px;border-radius:10px;font-weight:700;white-space:nowrap;">ابدأ الآن ←</span>
    </a>
    @endif

    <!-- Streak Progress Card -->
    @if($lesson->hasStreakEnabled() && isset($lessonStreak))
    @php
        $__sMin       = (int) $lesson->streak_min_days;
        $__sDone      = (int) $lessonStreak->completed_days;
        $__sClaimed   = (bool) $lessonStreak->bonus_claimed;
        $__sReached   = $__sDone >= $__sMin;
        $__sRemaining = max(0, $__sMin - $__sDone);
        $__sPct       = $lessonStreak->getProgressPercentage();
    @endphp
    <div class="streak-progress-card" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 16px; padding: 20px; margin-bottom: 20px; border: 2px solid #f59e0b; box-shadow: 0 8px 24px rgba(217,119,6,0.20);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 36px;">🔥</span>
                <div>
                    @if($__sClaimed)
                        <h3 style="font-size: 18px; font-weight: 800; color: #92400e; margin: 0;">🏆 حصلت على مكافأة الالتزام!</h3>
                        <p style="font-size: 12.5px; color: #b45309; margin: 4px 0 0 0; line-height: 1.7;">
                            🎉 أُضيفت لك <strong>{{ $lesson->streak_bonus_points }}</strong> نقطة <strong>نهائية</strong> — تُمنح مرّة واحدة فقط. أحسنت الالتزام!
                        </p>
                    @elseif($__sDone >= 1)
                        <h3 style="font-size: 18px; font-weight: 800; color: #92400e; margin: 0;">🔥 بدأت رحلة الالتزام! يوم {{ $__sDone }} من {{ $__sMin }}</h3>
                        <p style="font-size: 12.5px; color: #b45309; margin: 4px 0 0 0; line-height: 1.7;">
                            باقٍ <strong>{{ $__sRemaining }}</strong> {{ $__sRemaining == 1 ? 'يوم' : 'أيام' }} لتنال <strong>{{ $lesson->streak_bonus_points }}</strong> نقطة <strong>نهائية</strong> (تُمنح مرّة واحدة). أنجز نشاطاً كل يوم واستمرّ!
                        </p>
                    @else
                        <h3 style="font-size: 18px; font-weight: 800; color: #92400e; margin: 0;">🌱 ابدأ رحلة الالتزام اليوم!</h3>
                        <p style="font-size: 12.5px; color: #b45309; margin: 4px 0 0 0; line-height: 1.7;">
                            أنجز نشاطاً في <strong>{{ $__sMin }}</strong> أيام مختلفة لتنال <strong>{{ $lesson->streak_bonus_points }}</strong> نقطة <strong>نهائية</strong> (تُمنح مرّة واحدة). أوّل نشاط اليوم يبدأ رحلتك!
                        </p>
                    @endif
                </div>
            </div>
            <div style="text-align: center; min-width: 128px;">
                <div style="font-size: 34px; font-weight: 800; color: #92400e; line-height: 1;">
                    {{ $__sDone }}<span style="font-size:18px; font-weight:700; color:#b45309;"> / {{ $__sMin }}</span>
                </div>
                <div style="font-size: 12px; color: #b45309; margin-top: 2px;">يوم مكتمل</div>
                @if(!$__sClaimed && $__sDone > 0)
                <div style="display:inline-block; margin-top:8px; background:#f59e0b; color:#fff7ed; font-size:11px; font-weight:800; padding:4px 12px; border-radius:999px;">🚀 لقد بدأت! استمرّ</div>
                @endif
            </div>
        </div>
        <!-- نقاط الأيام: ✓ لكل يوم مكتمل -->
        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:16px; direction:rtl;">
            @for($__d = 1; $__d <= $__sMin; $__d++)
                @php $__filled = $__d <= $__sDone; @endphp
                <div title="اليوم {{ $__d }}" class="streak-day-dot {{ $__filled ? 'filled' : 'empty' }}">{{ $__filled ? '✓' : $__d }}</div>
            @endfor
        </div>
        <!-- Progress Bar -->
        <div style="margin-top: 14px;">
            <div style="background: rgba(255,255,255,0.5); border-radius: 10px; height: 12px; overflow: hidden;">
                <div style="background: linear-gradient(90deg, #f59e0b, #d97706); height: 100%; border-radius: 10px; transition: width 0.5s; width: {{ $__sPct }}%;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 11px; color: #92400e; font-weight:700;">
                @if($__sClaimed)
                    <span>🏅 اكتملت مكافأة هذا الدرس</span>
                    <span>{{ $__sDone }} يوم التزام</span>
                @elseif($__sReached)
                    <span>✨ أتممت الأيام المطلوبة! مكافأتك النهائية في طريقها</span>
                @else
                    <span>{{ $__sDone > 0 ? '🔥 استمرّ — أنت على الطريق' : '🌱 ابدأ اليوم بأوّل نشاط' }}</span>
                    <span>باقٍ {{ $__sRemaining }} {{ $__sRemaining == 1 ? 'يوم' : 'أيام' }}</span>
                @endif
            </div>
        </div>
        <!-- تمييز صريح: نقاط يومية مقابل مكافأة نهائية -->
        <div style="margin-top:14px; padding-top:12px; border-top:1px dashed rgba(146,64,14,.35); font-size:11.5px; color:#92400e; line-height:1.7;">
            <div>⭐ <strong>نقاط الأنشطة</strong>: تُحتسب لك <strong>يوميًا</strong> مع كل نشاط تُنجزه.</div>
            <div>🏆 <strong>مكافأة الالتزام</strong>: <strong>{{ $lesson->streak_bonus_points }}</strong> نقطة <strong>نهائية تُمنح مرّة واحدة فقط</strong> عند بلوغ {{ $__sMin }} أيام.</div>
        </div>
    </div>
    @endif

    <!-- Minimal Header with Progress -->
    <div class="lesson-header">
        <button class="lesson-back-btn" onclick="window.location.href='{{ route('student.path') }}'">
            <span>←</span>
            <span>رجوع</span>
        </button>
        
        <div class="lesson-progress-bar">
            <div class="progress-bar-track">
                <div class="progress-bar-fill" style="width: {{ $completionPercent }}%"></div>
            </div>
            <div class="progress-text">{{ $completedActivities }} من {{ $totalActivities }} أنشطة</div>
        </div>
        
        <div class="xp-badge">
            <span>⭐</span>
            <span>{{ $lesson->points ?? 10 }} XP</span>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="lesson-content-card scale-in">
        <!-- Title Section -->
        <div class="lesson-title-section">
            <div class="lesson-icon-large">📖</div>
            <h1 class="lesson-title-main">{{ $lesson->title }}</h1>
            <div class="lesson-meta-info">
                <div class="meta-item">
                    <span>⏱️</span>
                    <span>{{ $lesson->duration ?? 10 }} دقيقة</span>
                </div>
                <div class="meta-item">
                    <span>📚</span>
                    <span>{{ $lesson->type ?? 'نظري' }}</span>
                </div>
                <div class="meta-item">
                    <span>🎯</span>
                    <span>{{ $totalActivities }} أنشطة</span>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        @if($lesson->content)
        <div class="content-section">
            <span class="section-type-badge">
                <span>📝</span>
                <span>محتوى الدرس</span>
            </span>
            <div class="content-text rich-content">
                {!! safe_html($lesson->content) !!}
            </div>
        </div>
        @endif

        <!-- Video Section -->
        @if(!empty($lesson->video_url))
        @php
            $videoUrl = $lesson->video_url;
            // Convert YouTube URLs to embed format
            if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
                $videoUrl = 'https://www.youtube-nocookie.com/embed/' . $matches[1];
            } elseif (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
                $videoUrl = 'https://www.youtube-nocookie.com/embed/' . $matches[1];
            } elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
                $videoUrl = 'https://www.youtube-nocookie.com/embed/' . $matches[1];
            }
        @endphp
        <div class="content-section">
            <span class="section-type-badge">
                <span>🎥</span>
                <span>فيديو تعليمي</span>
            </span>
            <div class="media-container">
                <iframe 
                    src="{{ $videoUrl }}" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen
                    style="width: 100%; height: 100%;">
                </iframe>
            </div>
        </div>
        @endif

        <!-- Video File (uploaded) Section -->
        @if(!empty($lesson->video_file))
        <div class="content-section">
            <span class="section-type-badge">
                <span>🎥</span>
                <span>فيديو تعليمي</span>
            </span>
            <div class="media-container">
                <video controls style="width: 100%; height: 100%;">
                    <source src="{{ asset('storage/' . ltrim($lesson->video_file, '/')) }}">
                    متصفحك لا يدعم تشغيل الفيديو.
                </video>
            </div>
        </div>
        @endif

        <!-- Audio Section -->
        @if(!empty($lesson->audio_url))
        <div class="content-section">
            <span class="section-type-badge">
                <span>🎧</span>
                <span>مقطع صوتي</span>
            </span>
            <div class="media-audio">
                <audio controls>
                    <source src="{{ $lesson->audio_url }}" type="audio/mpeg">
                    متصفحك لا يدعم تشغيل الملفات الصوتية.
                </audio>
            </div>
        </div>
        @endif

        <!-- Audio File (uploaded) Section -->
        @if(!empty($lesson->audio_file))
        <div class="content-section">
            <span class="section-type-badge">
                <span>🎧</span>
                <span>مقطع صوتي</span>
            </span>
            <div class="media-audio">
                <audio controls>
                    <source src="{{ asset('storage/' . ltrim($lesson->audio_file, '/')) }}">
                    متصفحك لا يدعم تشغيل الملفات الصوتية.
                </audio>
            </div>
        </div>
        @endif
    </div>

    <!-- Activities Section -->
    @if($activities->count() > 0)
    <div class="activities-section slide-up">
        <h2 class="section-header">
            <span style="font-size: 32px;">🎯</span>
            <span>الأنشطة والتمارين</span>
        </h2>

        @foreach($activities as $index => $activity)
        <div class="activity-card {{ $activity->status ?? 'available' }}" 
             onclick="{{ in_array($activity->status ?? '', ['completed', 'available', 'pending', 'approved', 'needs_review']) ? "window.location.href='" . route('student.activity', $activity->id) . "'" : '' }}">
            <div class="activity-info">
                <div class="activity-title">
                    {{ $index + 1 }}. {{ $activity->title }}
                </div>
                <div class="activity-meta">
                    <span>{{ $activity->type ?? 'تمرين' }}</span>
                    <span>•</span>
                    <span>{{ $activity->points ?? 5 }} XP</span>
                </div>
            </div>
            
            <div class="activity-status {{ $activity->status ?? 'available' }}">
                @if(in_array($activity->status ?? '', ['completed', 'approved'], true))
                    <span>✓</span>
                    <span>مكتمل</span>
                @elseif(in_array($activity->status ?? '', ['pending', 'needs_review'], true))
                    <span>⏳</span>
                    <span>قيد المراجعة</span>
                @elseif(($activity->status ?? '') == 'locked')
                    <span>🔒</span>
                    <span>مقفل</span>
                @else
                    <span>▶</span>
                    <span>ابدأ</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($activities->isEmpty())
    <div class="lesson-content-card" style="text-align: center; padding: 60px 40px;">
        <div style="font-size: 64px; margin-bottom: 20px;">🎯</div>
        <h3 style="font-size: 24px; font-weight: 700; color: white; margin-bottom: 12px;">لا توجد أنشطة حالياً</h3>
        <p style="font-size: 16px; color: rgba(255,255,255,0.7);">سيتم إضافة الأنشطة قريباً</p>
    </div>
    @endif
</div>

<!-- Floating CTA -->
@if($nextActivity)
<button class="floating-cta" onclick="window.location.href='{{ route('student.activity', $nextActivity->id) }}'">
    <span>{{ $nextActivity->status == 'completed' ? 'مراجعة النشاط' : 'ابدأ النشاط' }}</span>
    <span style="font-size: 20px;">→</span>
</button>
@endif
</div>
@endsection

@push('scripts')
<script>
    // Auto-scroll to first incomplete activity
    const firstIncomplete = document.querySelector('.activity-card.available');
    if (firstIncomplete) {
        setTimeout(() => {
            firstIncomplete.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 500);
    }
</script>
@endpush
