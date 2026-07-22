@extends('layouts.student-app')

@section('title', $activity->title ?? 'نشاط')

@push('styles')
<style>
    /* Activity View - الافتراضي تدرج بنفسجي/أزرق (Issues #87, #99)
       وضع الفاتح يستبدل بـ background فاتح. */
    body {
        background: linear-gradient(135deg, #4c51bf 0%, #5b21b6 50%, #6d28d9 100%);
    }
    html[data-theme="light"] body {
        background: linear-gradient(135deg, #eef2ff 0%, #f3e8ff 50%, #fce7f3 100%);
    }

    .activity-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0;
        padding-bottom: 100px;
        min-height: 100vh;
    }

    .activity-header {
        position: sticky;
        top: 0;
        background: rgba(13, 17, 23, 0.95);
        backdrop-filter: blur(20px);
        z-index: 100;
        padding: var(--spacing-md) var(--spacing-lg);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    html[data-theme="light"] .activity-header { background: rgba(255,255,255,0.92); border-bottom-color: rgba(0,0,0,0.06); }
    html[data-theme="light"] .activity-header * { color: #1e293b; }
    html[data-theme="light"] .back-btn { background: rgba(0,0,0,0.05); color: #334155; }
    html[data-theme="light"] .question-section { background: rgba(255,255,255,0.85); color: #1e293b; }
    html[data-theme="light"] .question-text { color: #1e293b; }
    html[data-theme="light"] .quiz-option { background: rgba(0,0,0,0.04); color: #1e293b; }
    html[data-theme="light"] .quiz-option.selected { background: rgba(99,102,241,0.15); }
    
    .header-top {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-sm);
    }
    
    .back-btn {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all var(--transition-base);
        font-size: 20px;
        color: white;
    }
    
    .back-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.05);
    }
    
    .progress-bar-container {
        flex: 1;
        height: 10px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-full);
        overflow: hidden;
    }
    
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
        border-radius: var(--radius-full);
        transition: width 0.3s ease;
    }
    
    .xp-badge-header {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        color: #7C3AED;
        padding: 8px 16px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .activity-title-header {
        font-size: 18px;
        font-weight: 700;
        color: white;
        text-align: center;
    }
    
    .activity-content-card {
        background: var(--glass-bg-heavy);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-2xl);
        padding: var(--spacing-2xl);
        margin: var(--spacing-xl);
        margin-top: var(--spacing-2xl);
    }
    
    .activity-icon-large {
        font-size: 80px;
        text-align: center;
        margin-bottom: var(--spacing-lg);
    }
    
    .activity-title-main {
        font-size: 28px;
        font-weight: 800;
        color: white;
        text-align: center;
        margin-bottom: var(--spacing-md);
    }
    
    .activity-description {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.7);
        text-align: center;
        margin-bottom: var(--spacing-xl);
        line-height: 1.6;
    }
    
    /* Quiz Styles */
    .quiz-question {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
    }
    .quiz-question-text {
        font-size: 18px;
        font-weight: 700;
        color: white;
        margin-bottom: 16px;
        text-align: right;
        line-height: 1.6;
    }
    .quiz-question-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px; height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
        font-weight: 700;
        font-size: 14px;
        margin-left: 10px;
    }
    .quiz-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .quiz-option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.15);
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.3s;
        color: white;
        font-size: 15px;
        text-align: right;
    }
    .quiz-option:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--color-primary);
    }
    /* Issue #58: تباين أعلى — استخدام الأصفر الذهبي على الخلفية البنفسجية بدل الأخضر */
    .quiz-option.selected {
        background: rgba(252, 211, 77, 0.22);
        border-color: #FCD34D;
        box-shadow: 0 4px 18px rgba(252, 211, 77, 0.35);
    }
    .quiz-option input[type="radio"] {
        display: none;
    }
    .quiz-option-circle {
        width: 24px; height: 24px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.3s;
    }
    .quiz-option.selected .quiz-option-circle {
        border-color: #FCD34D;
        background: #FCD34D;
        color: #1e1b4b;
    }
    .quiz-option.selected .quiz-option-circle::after {
        content: '✓';
        color: white;
        font-size: 14px;
        font-weight: 700;
    }

    /* Text Answer */
    .question-section {
        margin-bottom: var(--spacing-xl);
    }
    .question-text {
        font-size: 20px;
        font-weight: 700;
        color: white;
        margin-bottom: var(--spacing-lg);
        text-align: right;
    }
    .text-input-field {
        width: 100%;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        color: white;
        font-size: 16px;
        font-family: inherit;
        transition: all var(--transition-base);
    }
    .text-input-field:focus {
        outline: none;
        border-color: var(--color-primary);
        background: rgba(255, 255, 255, 0.1);
    }
    
    /* Activity Type Badge */
    .activity-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
        padding: 6px 16px;
        border-radius: var(--radius-full);
        font-size: 13px;
        font-weight: 600;
        margin: 0 auto 20px;
    }

    .submit-btn {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
        border: none;
        padding: 18px 48px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 18px;
        cursor: pointer;
        width: 100%;
        transition: all var(--transition-base);
        margin-top: var(--spacing-xl);
    }
    .submit-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
    }
    .submit-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .feedback-modal {
        display: none;
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        max-width: 500px;
        width: calc(100% - 40px);
    }
    .feedback-modal.active {
        display: block;
        animation: slideUp 0.3s ease-out;
    }
    @keyframes slideUp {
        from { transform: translateX(-50%) translateY(100px); opacity: 0; }
        to { transform: translateX(-50%) translateY(0); opacity: 1; }
    }
    .feedback-card {
        background: var(--glass-bg-heavy);
        backdrop-filter: blur(40px) saturate(180%);
        border: 2px solid #10B981;
        border-radius: var(--radius-2xl);
        padding: var(--spacing-xl);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }
    .feedback-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-md);
    }
    .feedback-icon { font-size: 48px; }
    .feedback-title {
        font-size: 24px;
        font-weight: 700;
        color: white;
    }
    .feedback-message {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: var(--spacing-lg);
    }
    .feedback-xp {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 20px;
        font-weight: 700;
        color: #FFD700;
        margin-bottom: var(--spacing-lg);
    }
    .continue-btn {
        background: white;
        color: #0D1117;
        border: none;
        padding: 14px 32px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        transition: all var(--transition-base);
    }
    .continue-btn:hover { transform: scale(1.02); }

    /* Toast Notification */
    .toast-notification {
        position: fixed;
        top: 30px;
        left: 50%;
        transform: translateX(-50%) translateY(-120px);
        z-index: 99999;
        max-width: 420px;
        width: calc(100% - 40px);
        padding: 18px 24px;
        border-radius: 16px;
        backdrop-filter: blur(20px) saturate(180%);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        gap: 14px;
        font-weight: 600;
        font-size: 15px;
        color: white;
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s;
        opacity: 0;
    }
    .toast-notification.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
    .toast-notification.warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.9), rgba(217, 119, 6, 0.9));
        border: 1px solid rgba(251, 191, 36, 0.5);
    }
    .toast-notification.error {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.9), rgba(220, 38, 38, 0.9));
        border: 1px solid rgba(248, 113, 113, 0.5);
    }
    .toast-notification.info {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.9), rgba(79, 70, 229, 0.9));
        border: 1px solid rgba(129, 140, 248, 0.5);
    }
    .toast-icon { font-size: 28px; flex-shrink: 0; }
    .toast-close {
        margin-right: auto;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 28px; height: 28px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background 0.2s;
    }
    .toast-close:hover { background: rgba(255,255,255,0.35); }

    /* ============================================================
       LIGHT-MODE COVERAGE — واجهة نشاط الطالب مؤلَّفة أصلاً بنص أبيض على زجاج داكن.
       في الوضع النهاري يصبح الزجاج فاتحاً (--glass-bg-heavy) فوق تدرّج فاتح، فيختفي
       كل نص/حدّ أبيض (أبيض-على-أبيض — انظر شكوى مُجمّع الحروف). نُعتّم كل نص/حدّ أبيض
       يجلس على سطح محايد/فاتح؛ والعناصر ذات الخلفية الملوّنة الصلبة (شارة النوع، زر
       الإرسال، أرقام الأسئلة، دوائر الترتيب، زر المتابعة المتدرّج، الـtoast) تُترك
       بيضاء عمداً لأنها تستخدم الكلمة الصريحة color:white (لا rgba/hex فلا تُطابَق).
       كل قاعدة مقيّدة بـ html[data-theme="light"] فالوضع الليلي سليم تماماً.
       بُني عبر Workflow (5 مسوحات + تركيب + تحقّق خصمي) — مطابق للدستور بالاتجاه المعاكس.
       ============================================================ */
    html[data-theme="light"] .progress-bar-container { background: rgba(0,0,0,0.08); }

    html[data-theme="light"] .activity-title-main { color: #1e293b; }
    html[data-theme="light"] .activity-description { color: rgba(30,41,59,0.75); }

    html[data-theme="light"] .quiz-question { background: rgba(0,0,0,0.03); border-color: rgba(0,0,0,0.08); }
    html[data-theme="light"] .quiz-question-text { color: #1e293b; }
    /* الحدّ فقط، ونستثني المحدَّد كي يبقى إطاره الذهبي (السطر 186) ظاهراً */
    html[data-theme="light"] .quiz-option:not(.selected) { border-color: rgba(0,0,0,0.12); }
    html[data-theme="light"] .quiz-option-circle { border-color: rgba(0,0,0,0.25); }

    /* كل الحقول النصية (inline يفرض !important) */
    html[data-theme="light"] .text-input-field {
        color: #1e293b !important;
        background: rgba(0,0,0,0.03) !important;
        border-color: rgba(0,0,0,0.2) !important;
    }
    html[data-theme="light"] .text-input-field::placeholder { color: rgba(30,41,59,0.5) !important; }
    html[data-theme="light"] .text-input-field:focus { background: rgba(0,0,0,0.05) !important; }

    /* ترتيب الصور: الحاويات والقوائم المنسدلة */
    html[data-theme="light"] .quiz-image-order-item,
    html[data-theme="light"] .image-order-item {
        background: rgba(0,0,0,0.03) !important;
        border-color: rgba(0,0,0,0.12) !important;
    }
    html[data-theme="light"] .quiz-img-select,
    html[data-theme="light"] .image-order-select {
        color: #1e293b !important;
        background: rgba(0,0,0,0.05) !important;
        border-color: rgba(0,0,0,0.25) !important;
    }

    /* ترتيب الكلمات/الجمل (قواعد مُعرّفة في كتلة نمط متداخلة بلا inline) */
    html[data-theme="light"] #orderingList .order-item {
        background: rgba(0,0,0,0.03);
        border-color: rgba(0,0,0,0.15);
        color: #1e293b;
    }
    html[data-theme="light"] #orderingList .order-item:hover {
        background: rgba(0,0,0,0.06);
        border-color: rgba(0,0,0,0.3);
    }
    html[data-theme="light"] .order-handle { background: rgba(0,0,0,0.08); color: #334155; }

    /* اختيار الحروف (العطل في الصورة) */
    html[data-theme="light"] #letterAnswerBox {
        color: #1e293b !important;            /* تُورَّث لأحرف JS المُدرجة (span بلا لون) */
        border-color: rgba(0,0,0,0.3) !important;
        background: rgba(0,0,0,0.03) !important;
    }
    /* اللون والحدّ فقط؛ نترك الخلفية كي يظهر feedback النقر الأخضر (JS inline) */
    html[data-theme="light"] .letter-btn {
        color: #1e293b !important;
        border-color: rgba(0,0,0,0.2) !important;
    }

    /* رفع الملفات: منطقة الإفلات ونصّها (color:white كلمة صريحة) */
    html[data-theme="light"] label[for="activityFile"] {
        background: rgba(0,0,0,0.03) !important;
        border-color: rgba(0,0,0,0.25) !important;
    }
    html[data-theme="light"] label[for="activityFile"] span { color: #334155 !important; }

    /* ---- محتوى inline داخل البطاقة (حالة التسليم المكتمل + التلميحات) ----
       مطابِقات مقيّدة بـ.activity-content-card؛ لا تلتقط عناصر keep-white لأنها
       تستخدم color:white الصريحة لا rgba/hex. نغطّي متغيّرات المسافات كلّها. */
    html[data-theme="light"] .activity-content-card [style*="color: rgba(255,255,255"],
    html[data-theme="light"] .activity-content-card [style*="color:rgba(255,255,255"],
    html[data-theme="light"] .activity-content-card [style*="color: rgba(255, 255, 255"],
    html[data-theme="light"] .activity-content-card [style*="color:rgba(255, 255, 255"] {
        color: #334155 !important;
    }
    html[data-theme="light"] .activity-content-card [style*="color: #FCA5A5"],
    html[data-theme="light"] .activity-content-card [style*="color:#fca5a5"] {   /* رفض/فشل + onerror + مسح الحروف */
        color: #B91C1C !important;
    }
    html[data-theme="light"] .activity-content-card [style*="color: #F59E0B"],   /* قيد المراجعة */
    html[data-theme="light"] .activity-content-card [style*="color: #FCD34D"] {  /* درجة 50–79٪ */
        color: #B45309 !important;
    }
    html[data-theme="light"] .activity-content-card [style*="color: #86EFAC"],   /* درجة ≥80٪ */
    html[data-theme="light"] .activity-content-card [style*="color: #10B981"] {
        color: #047857 !important;
    }
    html[data-theme="light"] .activity-content-card [style*="color: #94a3b8"],   /* تم التسليم */
    html[data-theme="light"] .activity-content-card [style*="color: #CBD5E1"] {  /* درجة null */
        color: #475569 !important;
    }
    html[data-theme="light"] .activity-content-card [style*="color: #c7d2fe"] {  /* رابط الملف المرفوع */
        color: #4338CA !important;
    }
    /* عنوان الاكتمال h2 'تم تسليم هذا النشاط' (#10B981 منخفض التباين) — h2 وحيد داخل البطاقة */
    html[data-theme="light"] .activity-content-card h2 { color: #065F46 !important; }

    /* ---- نافذة النتيجة (feedback modal) — خارج .activity-content-card ---- */
    html[data-theme="light"] .feedback-title   { color: #1e293b; }            /* JS يفرض titleColor للحالات المصحّحة */
    html[data-theme="light"] .feedback-message  { color: rgba(30,41,59,0.9); } /* يُورَّث للسطر الفرعي 1426 */
    html[data-theme="light"] .feedback-xp       { color: #B45309; }
    /* زر 'متابعة' في النافذة: أبيض صلب يذوب في البطاقة الفاتحة → تدرّج العلامة.
       غير-important كي تبقى نسخة الاكتمال (السطر 647) بتدرّجها inline كما هي. */
    html[data-theme="light"] .continue-btn {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: #fff;
    }
    /* الإجابة الصحيحة + سطر الدرجة المُدرَجان بـJS: أخضر/أبيض على صندوق أخضر شاحب */
    html[data-theme="light"] #feedbackMessage [style*="color:#fff"],
    html[data-theme="light"] #feedbackMessage [style*="color: #fff"],
    html[data-theme="light"] #feedbackMessage [style*="color:#10B981"],
    html[data-theme="light"] #feedbackMessage [style*="color: #10B981"] {
        color: #065F46 !important;
    }

    /* الجوال: تقليص حشو/هامش البطاقة كي يأخذ المحتوى (وخصوصاً مراجعة الإجابات)
       العرض الكامل. كانت padding:48px + margin:32px تخنق النص إلى عمود رفيع
       (~130px) على الشاشات الصغيرة. */
    @media (max-width: 640px) {
        .activity-content-card {
            padding: 16px;
            margin: 12px;
            margin-top: 20px;
        }
    }

    /* ===== تنسيق وصف النشاط الغنيّ (من الأدمن/المعلم) — يُعرَض على «سطح أبيض» مطابق لخلفية
       المحرّر، فتظهر ألوان المؤلّف كما اختارها بالضبط (WYSIWYG) وتبقى مقروءة في الوضعين. ===== */
    .activity-description.rich-content {
        text-align: right;
        color: #1f2937;                 /* الافتراضي داكن على السطح الأبيض — نفس افتراض المحرّر */
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        line-height: 1.9;
    }
    .activity-description.rich-content p { margin-bottom: 12px; }
    .activity-description.rich-content img { max-width: 100%; height: auto; border-radius: 10px; margin: 12px 0; }
    .activity-description.rich-content a { color: #2563eb; text-decoration: underline; }
    .activity-description.rich-content ul, .activity-description.rich-content ol { padding-right: 24px; margin-bottom: 12px; }
    .activity-description.rich-content li { margin-bottom: 6px; }
    .activity-description.rich-content b, .activity-description.rich-content strong { font-weight: 700; }
    .activity-description.rich-content h1, .activity-description.rich-content h2, .activity-description.rich-content h3, .activity-description.rich-content h4 { margin-bottom: 10px; font-weight: 800; }

    /* تفاعل الإجابة (كونفيتي/حزن + الحركات) مُستخرَج إلى partials/answer-celebration */
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
<div class="activity-container">
    <div class="activity-header">
        <div class="header-top">
            <button class="back-btn" onclick="history.back()">←</button>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" id="progressBar" style="width: 0%"></div>
            </div>
            <div class="xp-badge-header">
                <span>⭐</span>
                <span id="activityXP">{{ $activity->points ?? 10 }}</span>
            </div>
        </div>
        <div class="activity-title-header">{{ $activity->title ?? 'نشاط تعليمي' }}</div>
        @php
            // #13 عدد المحاولات: يُسمح بإعادة الإرسال ما دامت المحاولات متبقية والحالة قابلة للإعادة
            // (needs_review/rejected/pending/completed — لا approved النهائيّ). يُحسب مرّة ويُعاد استخدامه.
            $__attemptsRemaining = isset($submission) && $submission
                && (int) ($submission->attempts ?? 1) < max(1, (int) ($activity->max_attempts ?? 1));
            $canRetry = isset($submission) && $submission && $__attemptsRemaining
                && in_array($submission->status ?? '', ['needs_review', 'rejected', 'pending', 'completed'], true);
            // إظهار النموذج: للحالات غير الناجحة مباشرةً، وللناجح (completed) فقط في وضع الإعادة
            // (?retry=1) — كي يبقى الطالب الناجح على شاشة الإنجاز مع زرّ «أعد لتحسين درجتك».
            $allowResubmit = $canRetry
                && (($submission->status ?? '') !== 'completed' || request()->boolean('retry'));
            $__timedQuiz = ($activity->quiz_duration ?? null) && $activity->type === 'quiz'
                && ! (isset($submission) && $submission && ! $allowResubmit);
        @endphp
        @if($__timedQuiz)
        <div id="quizTimer" data-duration="{{ (int) $activity->quiz_duration }}"
             style="text-align:center;margin-top:10px;font-weight:800;font-size:18px;color:#FCD34D;">
            ⏱ <span id="quizTimerText">{{ (int) $activity->quiz_duration }}:00</span>
        </div>
        @endif
    </div>

    <div class="activity-content-card fade-in">
        @php
            $typeIcon = match($activity->type ?? 'quiz') {
                'quiz'        => '📝',
                'exercise'    => '📋',
                'project'     => '🏗️',
                'image_order' => '🖼️',
                'creative'    => '✨',
                'upload'      => '📤',
                'practical'   => '🎯',
                'discussion'  => '💬',
                default       => '📝',
            };
            $typeName = match($activity->type ?? 'quiz') {
                'quiz'        => 'اختبار',
                'exercise'    => 'تمرين',
                'project'     => 'مشروع',
                'image_order' => 'ترتيب صور',
                'creative'    => 'نشاط إبداعي',
                'upload'      => 'رفع ملف',
                'practical'   => 'نشاط عملي',
                'discussion'  => 'مناقشة',
                default       => 'نشاط',
            };
        @endphp
        
        <div class="activity-icon-large">{{ $typeIcon }}</div>
        <h1 class="activity-title-main">{{ $activity->title ?? 'نشاط تعليمي' }}</h1>
        
        @if($activity->description)
        <div class="activity-description rich-content">{!! safe_html($activity->description) !!}</div>
        @endif

        @include('activities.partials.media')

        <div style="text-align: center;">
            <span class="activity-type-badge">{{ $typeIcon }} {{ $typeName }}</span>
        </div>

        {{-- $allowResubmit مُحتسَب أعلاه (يشمل pending + فحص المحاولات المتبقية) --}}
        @if(isset($submission) && $submission && !$allowResubmit)
        {{-- حالة النشاط المكتمل --}}
        <div style="text-align: center; padding: 30px 20px;">
            <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #10B981, #059669); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);">
                <span style="font-size: 50px;">✅</span>
            </div>
            <h2 style="color: #10B981; font-size: 24px; font-weight: 800; margin-bottom: 10px;">تم تسليم هذا النشاط</h2>
            <p style="color: rgba(255,255,255,0.7); font-size: 15px; margin-bottom: 20px;">
                {{ $submission->created_at->diffForHumans() }}
            </p>
            
            @php
                // Issue #57: قد تكون score = 0 بدل null؛ نُظهر الدرجة دائماً عند الاكتمال
                $hasScore = $submission->score !== null;
                $isPending = in_array($submission->status, ['pending', 'needs_review'], true);
                $isRejected = in_array($submission->status, ['rejected'], true);
                $earnedXp = $hasScore ? (int)round(($submission->score / 100) * ($activity->points ?? 10)) : 0;
                $scoreColor = $hasScore ? ($submission->score >= 80 ? '#86EFAC' : ($submission->score >= 50 ? '#FCD34D' : '#FCA5A5')) : '#CBD5E1';
                $scoreBg    = $hasScore ? ($submission->score >= 80 ? 'rgba(134,239,172,0.18)' : ($submission->score >= 50 ? 'rgba(252,211,77,0.18)' : 'rgba(252,165,165,0.18)')) : 'rgba(203,213,225,0.18)';
            @endphp
            @if($isRejected)
            <div role="alert" style="display: flex; flex-direction: column; align-items: center; gap: 8px; background: rgba(239, 68, 68, 0.18); border: 2px solid #FCA5A5; padding: 16px 26px; border-radius: 16px; margin-bottom: 20px; box-shadow: 0 4px 14px rgba(0,0,0,0.25);">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size: 24px;" aria-hidden="true">⚠️</span>
                    <span style="color: #FCA5A5; font-weight: 800; font-size: 18px;">يحتاج التسليم إلى تعديل</span>
                </div>
                <span style="color: rgba(255,255,255,0.85); font-size: 14px; font-weight: 600;">يمكنك إعادة الإرسال بإجابة محسّنة من خلال إعادة فتح النشاط</span>
            </div>
            @elseif($hasScore)
            <div style="display: inline-flex; align-items: center; gap: 10px; background: {{ $scoreBg }}; border: 2px solid {{ $scoreColor }}; padding: 12px 26px; border-radius: 50px; margin-bottom: 20px; box-shadow: 0 4px 14px rgba(0,0,0,0.25);">
                <span style="font-size: 22px;" aria-hidden="true">⭐</span>
                <span style="color: {{ $scoreColor }}; font-weight: 800; font-size: 19px; text-shadow: 0 1px 3px rgba(0,0,0,0.4);">الدرجة: {{ $submission->score }}%</span>
                <span style="color: rgba(255,255,255,0.85); font-size: 14px; margin-right: 8px; font-weight: 600;">
                    ({{ $earnedXp }}/{{ $activity->points ?? 10 }} نقطة)
                </span>
            </div>
            @elseif($isPending)
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); padding: 12px 24px; border-radius: 50px; margin-bottom: 20px;">
                <span style="font-size: 20px;" aria-hidden="true">⏳</span>
                <span style="color: #F59E0B; font-weight: 700; font-size: 16px;">بانتظار تقييم المعلم</span>
            </div>
            @else
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(148, 163, 184, 0.15); border: 1px solid rgba(148, 163, 184, 0.3); padding: 12px 24px; border-radius: 50px; margin-bottom: 20px;">
                <span style="font-size: 20px;" aria-hidden="true">📋</span>
                <span style="color: #94a3b8; font-weight: 700; font-size: 16px;">تم التسليم</span>
            </div>
            @endif

            {{-- #13: إعادة نشاطٍ اجتازه الطالب لتحسين درجته — المكافأة على أفضل محاولة (لا تُضاعَف ولا تُخصَم) --}}
            @if($canRetry && ($submission->status ?? '') === 'completed')
            <div style="margin-bottom: 20px;">
                <a href="{{ url()->current() }}?retry=1" style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; padding: 12px 26px; border-radius: 50px; font-weight: 700; font-size: 15px; text-decoration: none; box-shadow: 0 6px 18px rgba(102,126,234,0.4);">
                    🔄 أعد المحاولة لتحسين درجتك (المحاولة {{ ((int) ($submission->attempts ?? 1)) + 1 }} من {{ (int) ($activity->max_attempts ?? 3) }})
                </a>
                <div style="color: rgba(255,255,255,0.6); font-size: 12.5px; margin-top: 8px;">تحتفظ بأفضل درجة — لا تُخصم نقاطك إن كانت المحاولة الجديدة أقلّ.</div>
            </div>
            @endif

            @if($submission->answer)
            <div style="text-align: right; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 20px; margin-top: 20px;">
                <div style="color: rgba(255,255,255,0.5); font-size: 13px; margin-bottom: 8px; font-weight: 600;">إجابتك:</div>
                @php
                    $answerData = json_decode($submission->answer, true);
                    $imageOrderItems = [];

                    if (is_array($answerData)) {
                        // Format 1: Quiz with image_order — {"0":"[{image_url, selected_order}]"}
                        foreach ($answerData as $key => $val) {
                            if (is_string($val)) {
                                $parsed = json_decode($val, true);
                                if (is_array($parsed) && !empty($parsed) && isset($parsed[0]['image_url'])) {
                                    $imageOrderItems = array_merge($imageOrderItems, $parsed);
                                }
                            }
                        }
                        // Format 2: Standalone image_order — [{image_url, selected_order}]
                        if (empty($imageOrderItems) && isset($answerData[0]['image_url'])) {
                            $imageOrderItems = $answerData;
                        }
                    }

                    // Sort by selected_order
                    if (!empty($imageOrderItems)) {
                        usort($imageOrderItems, fn($a, $b) => ($a['selected_order'] ?? 0) - ($b['selected_order'] ?? 0));
                    }
                @endphp

                @if(!empty($imageOrderItems))
                    <div style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; margin-top: 10px;">
                        @foreach($imageOrderItems as $item)
                        <div style="text-align: center; background: rgba(255,255,255,0.08); border: 2px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 10px; position: relative;">
                            <div style="position: absolute; top: -8px; right: -8px; width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary, #10B981), var(--color-secondary, #059669)); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                                {{ $item['selected_order'] ?? '?' }}
                            </div>
                            <img src="{{ $item['image_url'] ?? '' }}" alt=""
                                 style="width: 110px; height: 110px; object-fit: contain; background: rgba(255,255,255,0.05); border-radius: 8px; display: block;"
                                 onerror="this.outerHTML='<div style=\'width:110px;height:110px;background:rgba(220,38,38,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fca5a5;font-size:12px;\'>❌ غير متاحة</div>';">
                        </div>
                        @endforeach
                    </div>
                @elseif(is_array($answerData) && (isset($answerData['file']) || isset($answerData['file_url'])))
                    {{-- إجابة برفع ملف (Issues 55, 56): note + الملف/الصورة المرفوعة --}}
                    @php
                        $noteText = $answerData['note'] ?? null;
                        if (is_array($noteText)) { $noteText = implode(' ', array_filter($noteText, 'is_scalar')); }
                        // نبني الرابط من مسار الملف بالاصطلاح العامل (نتجاهل file_url القديم/القصير)
                        $filePath = $answerData['file'] ?? null;
                        $fileUrl = $filePath
                            ? (\Illuminate\Support\Str::startsWith((string) $filePath, 'http') ? $filePath : asset('storage/app/public/data/' . ltrim((string) $filePath, '/')))
                            : ($answerData['file_url'] ?? null);
                        $ext = $fileUrl ? strtolower(pathinfo(parse_url($fileUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) : '';
                        $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
                    @endphp
                    @if(!empty($noteText))
                        <div style="color: rgba(255,255,255,0.9); font-size: 15px; line-height: 1.8; white-space: pre-wrap; margin-bottom: 12px;">{{ $noteText }}</div>
                    @endif
                    @if($fileUrl)
                        @if($isImg)
                            <img src="{{ $fileUrl }}" alt="الملف المرفوع"
                                 style="max-width: 100%; max-height: 320px; border-radius: 12px; display: block; margin: 6px auto 0;"
                                 onerror="this.outerHTML='<div style=\'padding:14px;background:rgba(220,38,38,0.15);border-radius:10px;color:#fca5a5;font-size:13px;text-align:center;\'>❌ تعذّر عرض الملف</div>';">
                        @else
                            <a href="{{ $fileUrl }}" target="_blank" rel="noopener"
                               style="display: inline-flex; align-items: center; gap: 8px; background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.4); color: #c7d2fe; padding: 12px 20px; border-radius: 12px; text-decoration: none; font-weight: 700;">
                                <span style="font-size: 20px;">📎</span> فتح الملف المرفوع
                            </a>
                        @endif
                    @endif
                @elseif(is_array($answerData))
                    {{-- عرض إجابة الاختبار/التمرين بشكل مقروء (سؤال → إجابة الطالب) --}}
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach($answerData as $qIdx => $studentAns)
                            @php
                                $q = $activity->questions[$qIdx] ?? null;
                                $qText = $q['question'] ?? $q['text'] ?? 'السؤال '.($qIdx+1);
                                $options = $q['options'] ?? $q['choices'] ?? null;
                                $display = $studentAns;
                                if (is_numeric($studentAns) && is_array($options) && isset($options[(int)$studentAns])) {
                                    $opt = $options[(int)$studentAns];
                                    $display = is_array($opt) ? ($opt['text'] ?? $opt['label'] ?? json_encode($opt, JSON_UNESCAPED_UNICODE)) : $opt;
                                } elseif (is_array($studentAns)) {
                                    $display = implode('، ', $studentAns);
                                }
                                // توحيد قراءة المفتاح مع محرّك التصحيح (correct_index / correct / is_correct / correct_answer / answer)
                                $correctIdx = $q['correct_index'] ?? (isset($q['correct']) && is_numeric($q['correct']) ? (int) $q['correct'] : null);
                                if ($correctIdx === null && is_array($options)) {
                                    foreach ($options as $oi => $op) {
                                        if (is_array($op) && !empty($op['is_correct'])) { $correctIdx = $oi; break; }
                                    }
                                }
                                $correct = $q['correct_answer'] ?? $q['answer'] ?? (isset($q['correct']) && is_string($q['correct']) ? $q['correct'] : null);
                                // مقارنة مرنة تأخذ في الاعتبار: index صريح ← ثم نص الخيار عند المطابقة
                                $isCorrect = false;
                                if ($correctIdx !== null && is_numeric($studentAns)) {
                                    $isCorrect = (int)$studentAns === (int)$correctIdx;
                                } elseif ($correct !== null) {
                                    $studentText = $display;
                                    $correctText = $correct;
                                    if (is_numeric($correct) && is_array($options) && isset($options[(int)$correct])) {
                                        $opt = $options[(int)$correct];
                                        $correctText = is_array($opt) ? ($opt['text'] ?? $opt['label'] ?? '') : $opt;
                                    }
                                    $isCorrect = mb_strtolower(trim((string)$studentText)) === mb_strtolower(trim((string)$correctText));
                                }
                            @endphp
                            <div style="background: rgba(255,255,255,0.04); border-right: 3px solid {{ $isCorrect ? '#10B981' : ($correct !== null ? '#EF4444' : 'rgba(255,255,255,0.2)') }}; padding: 10px 14px; border-radius: 8px;">
                                <div style="color: rgba(255,255,255,0.55); font-size: 13px; margin-bottom: 4px;">{{ $qText }}</div>
                                <div style="color: rgba(255,255,255,0.92); font-size: 14px; font-weight: 600;">إجابتك: {{ $display }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="color: rgba(255,255,255,0.9); font-size: 15px; line-height: 1.8; white-space: pre-wrap;">{{ html_excerpt($submission->answer, 2000) }}</div>
                @endif
            </div>
            @endif

            {{-- Issue #34: نقرأ من الحقلَين لتوافق العرض مع كلا مسارَي التقييم (admin + teacher) --}}
            @php $teacherNote = $submission->feedback ?: ($submission->teacher_feedback ?? null); @endphp
            @if($teacherNote)
            <div style="text-align: right; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 16px; padding: 20px; margin-top: 15px;">
                <div style="color: rgba(255,255,255,0.5); font-size: 13px; margin-bottom: 8px; font-weight: 600;">💬 ملاحظات المعلم:</div>
                <div style="color: rgba(255,255,255,0.9); font-size: 15px; line-height: 1.8;">{{ $teacherNote }}</div>
            </div>
            @endif

            <button class="continue-btn" onclick="continueToNext()" style="margin-top: 25px; background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); color: white; border: none; padding: 16px 40px; border-radius: 50px; font-weight: 700; font-size: 16px; cursor: pointer;">
                @if(isset($nextActivity))
                    النشاط التالي ←
                @else
                    العودة للدرس
                @endif
            </button>
        </div>
        @else
        {{-- نموذج الإجابة --}}
        <form id="activityForm">
            @csrf

            @php
                // النوع الفعلي للنشاط: نموذج الإنشاء يحفظ النوع داخل questions[0].type
                // بينما question_type على مستوى النشاط يبقى غالباً 'multiple_choice' الافتراضي.
                // لذا نشتقّ النوع الفعلي لتوجيه العرض بدقة (Issues 63, 64, 65, 67).
                $qz = is_array($activity->questions) ? $activity->questions : [];
                $firstQType = $qz[0]['type'] ?? $qz[0]['question_type'] ?? null;
                $normMap = ['word_order'=>'word_ordering','sentence_order'=>'sentence_ordering','image_order'=>'image_ordering'];
                $firstQTypeNorm = $normMap[$firstQType] ?? $firstQType;
                $effType = $firstQTypeNorm ?: ($activity->question_type ?? null);
                // الأنواع الخاصة أحادية السؤال تُوجَّه لفرعها المخصّص بدل فرع quiz العام
                $specialTypes = ['word_ordering','sentence_ordering','letter_choice','short_answer'];
                $isSingleSpecial = (count($qz) === 1) && in_array($firstQTypeNorm, $specialTypes, true);
            @endphp

            @if($activity->type === 'quiz' && !empty($activity->questions) && !$isSingleSpecial)
                {{-- أسئلة الاختبار --}}
                @foreach($activity->questions as $qIndex => $question)
                <div class="quiz-question" data-question="{{ $qIndex }}">
                    <div class="quiz-question-text">
                        <span class="quiz-question-number">{{ $qIndex + 1 }}</span>
                        {{ $question['question'] ?? $question['text'] ?? 'سؤال' }}
                    </div>

                    @include('partials.question-media', ['q' => $question])

                    @if(isset($question['type']) && $question['type'] === 'image_order' && !empty($question['images']))
                        {{-- سؤال ترتيب صور داخل الاختبار --}}
                        <p style="color:rgba(255,255,255,0.5);font-size:13px;margin-bottom:12px;">اختر الرقم المناسب لكل صورة</p>
                        <div class="quiz-image-order-container" data-question-index="{{ $qIndex }}" style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;">
                            @php $shuffledImgs = collect($question['images'])->shuffle()->values(); @endphp
                            @foreach($shuffledImgs as $imgIdx => $img)
                            <div class="quiz-image-order-item" draggable="true"
                                 data-url="{{ $img['url'] ?? '' }}"
                                 style="text-align:center;background:rgba(255,255,255,0.08);border:2px solid rgba(255,255,255,0.15);border-radius:12px;padding:10px;cursor:grab;">
                                <select class="quiz-img-select" data-q="{{ $qIndex }}"
                                        style="width:45px;height:28px;border-radius:6px;border:1px solid rgba(255,255,255,0.3);background:rgba(255,255,255,0.1);color:white;font-weight:700;text-align:center;margin-bottom:6px;font-size:13px;cursor:pointer;"
                                        onchange="updateQuizImageOrder({{ $qIndex }})">
                                    <option value="" selected style="color:#000;">#</option>
                                    @for($n = 1; $n <= count($question['images']); $n++)
                                    <option value="{{ $n }}" style="color:#000;">{{ $n }}</option>
                                    @endfor
                                </select>
                                @if(!empty($img['url']))
                                <img src="{{ $img['url'] }}" alt="{{ $img['description'] ?? '' }}"
                                     style="width:110px;height:110px;object-fit:contain;background:rgba(255,255,255,0.05);border-radius:8px;display:block;"
                                     onerror="this.outerHTML='<div style=\'width:110px;height:110px;background:rgba(220,38,38,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fca5a5;font-size:12px;\'>❌ غير متاحة</div>';">
                                @else
                                <div style="width:110px;height:110px;background:rgba(255,255,255,0.05);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:32px;">🖼️</div>
                                @endif
                                @if(!empty($img['description']))
                                <div style="color:rgba(255,255,255,0.6);font-size:11px;margin-top:4px;">{{ $img['description'] }}</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="question_{{ $qIndex }}" class="quiz-image-order-answer" value="">
                    @elseif(($question['type'] ?? null) === 'short_answer')
                        {{-- إجابة قصيرة داخل الاختبار --}}
                        <textarea class="text-input-field exercise-answer" data-index="{{ $qIndex }}"
                                  rows="2" placeholder="اكتب إجابتك هنا..."
                                  style="width:100%;padding:12px 14px;border-radius:10px;border:2px solid rgba(255,255,255,0.2);background:rgba(255,255,255,0.06);color:white;"></textarea>
                    @else
                        <div class="quiz-options">
                            @foreach($question['options'] ?? $question['choices'] ?? [] as $oIndex => $option)
                            <label class="quiz-option" onclick="selectOption(this, {{ $qIndex }})">
                                <input type="radio" name="question_{{ $qIndex }}" value="{{ $oIndex }}">
                                <span class="quiz-option-circle"></span>
                                <span>{{ is_string($option) ? $option : '' }}</span>
                            </label>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endforeach

            @elseif($activity->type === 'exercise' && !empty($activity->questions) && !$isSingleSpecial)
                {{-- تمرين: أسئلة بإجابات نصية --}}
                @foreach($activity->questions as $qIndex => $question)
                <div class="quiz-question" data-question="{{ $qIndex }}">
                    <div class="quiz-question-text">
                        <span class="quiz-question-number">{{ $qIndex + 1 }}</span>
                        {{ $question['question'] ?? $question['text'] ?? 'سؤال ' . ($qIndex+1) }}
                    </div>
                    @if(!empty($question['options']))
                    <div class="quiz-options">
                        @foreach($question['options'] as $oIndex => $option)
                        <label class="quiz-option" onclick="selectOption(this, {{ $qIndex }})">
                            <input type="radio" name="question_{{ $qIndex }}" value="{{ $oIndex }}">
                            <span class="quiz-option-circle"></span>
                            <span>{{ $option }}</span>
                        </label>
                        @endforeach
                    </div>
                    @else
                    <textarea class="text-input-field exercise-answer" data-index="{{ $qIndex }}"
                              rows="2" placeholder="اكتب إجابتك هنا..."></textarea>
                    @endif
                </div>
                @endforeach

            @elseif($activity->type === 'image_order' && !empty($activity->questions))
                {{-- ترتيب صور --}}
                @php
                    // Normalize: merge both formats into [{url, caption}]
                    $normalizedImages = [];
                    $firstQ = $activity->questions[0] ?? [];
                    if (isset($firstQ['image_url'])) {
                        // Teacher format: [{image_url, caption, order}]
                        foreach ($activity->questions as $q) {
                            $normalizedImages[] = ['url' => $q['image_url'] ?? '', 'caption' => $q['caption'] ?? '', 'order' => $q['order'] ?? count($normalizedImages) + 1];
                        }
                    } else {
                        // Admin format: [{type:'image_order', question:'...', images:[{url, description}]}]
                        foreach ($activity->questions as $q) {
                            if (isset($q['type']) && $q['type'] === 'image_order' && !empty($q['images'])) {
                                foreach ($q['images'] as $img) {
                                    $normalizedImages[] = ['url' => $img['url'] ?? '', 'caption' => $img['description'] ?? '', 'order' => count($normalizedImages) + 1];
                                }
                            }
                        }
                    }
                    $shuffledImages = collect($normalizedImages)->shuffle()->values();
                    $totalImages = count($normalizedImages);
                @endphp
                
                @if($totalImages > 0)
                <div class="question-section">
                    <div class="question-text">🖼️ رتّب هذه الصور بالترتيب الصحيح</div>
                    <p style="color:rgba(255,255,255,0.5);font-size:14px;text-align:center;margin-bottom:20px;">اختر الرقم المناسب لكل صورة أو اسحبها للترتيب</p>
                    <div id="imageOrderContainer" style="display:flex;flex-wrap:wrap;gap:15px;justify-content:center;">
                        @foreach($shuffledImages as $idx => $img)
                        <div class="image-order-item" draggable="true"
                             data-url="{{ $img['url'] }}"
                             data-original-order="{{ $img['order'] }}"
                             style="cursor:grab;text-align:center;background:rgba(255,255,255,0.08);border:2px solid rgba(255,255,255,0.15);border-radius:14px;padding:12px;transition:border-color 0.3s,transform 0.2s;position:relative;">
                            <select class="image-order-select" style="width:50px;height:30px;border-radius:8px;border:1px solid rgba(255,255,255,0.3);background:rgba(255,255,255,0.1);color:white;font-weight:700;text-align:center;margin-bottom:8px;font-size:14px;cursor:pointer;" onchange="updateImageOrderAnswer()">
                                <option value="" selected style="color:#000;">#</option>
                                @for($n = 1; $n <= $totalImages; $n++)
                                <option value="{{ $n }}" style="color:#000;">{{ $n }}</option>
                                @endfor
                            </select>
                            @if(!empty($img['url']))
                            <img src="{{ $img['url'] }}"
                                 alt="{{ $img['caption'] ?? '' }}"
                                 style="width:130px;height:130px;object-fit:contain;background:rgba(255,255,255,0.05);border-radius:10px;display:block;"
                                 onerror="this.outerHTML='<div style=\'width:130px;height:130px;background:rgba(220,38,38,0.15);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fca5a5;font-size:13px;\'>❌ صورة غير متاحة</div>';">
                            @else
                            <div style="width:130px;height:130px;background:rgba(255,255,255,0.05);border-radius:10px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.3);font-size:36px;">🖼️</div>
                            @endif
                            @if(!empty($img['caption']))
                            <div style="color:rgba(255,255,255,0.7);font-size:12px;margin-top:6px;">{{ $img['caption'] }}</div>
                            @endif
                            <div style="color:rgba(255,255,255,0.4);font-size:11px;margin-top:4px;">↕ اسحب للترتيب</div>
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="image_order_answer" id="imageOrderAnswer">
                </div>
                @endif

            @elseif(in_array($effType, ['word_ordering', 'sentence_ordering', 'word_order', 'sentence_order']))
                {{-- ترتيب كلمات/جمل عبر السحب والإفلات — UI متطور (Issues 65, 67) --}}
                @php
                    $firstQ = is_array($activity->questions) ? ($activity->questions[0] ?? []) : [];
                    $items = $firstQ['items'] ?? $firstQ['options'] ?? [];
                    if (!is_array($items)) $items = [];
                    $shuffled = collect($items)->shuffle()->values();
                @endphp
                <style>
                    #orderingList .order-item {
                        background: rgba(255,255,255,0.08);
                        border: 2px solid rgba(255,255,255,0.18);
                        border-radius: 14px;
                        padding: 14px 18px;
                        color: white;
                        cursor: grab;
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        font-size: 15px;
                        font-weight: 600;
                        transition: transform .15s, border-color .15s, background .15s;
                        user-select: none;
                        touch-action: none;
                    }
                    #orderingList .order-item:hover {
                        border-color: rgba(255,255,255,0.4);
                        background: rgba(255,255,255,0.12);
                    }
                    #orderingList .order-item.dragging {
                        opacity: .35;
                        transform: scale(.98);
                        cursor: grabbing;
                    }
                    #orderingList .order-item.drag-over {
                        border-color: #10b981;
                        background: rgba(16, 185, 129, .15);
                        transform: translateY(-2px);
                    }
                    .order-handle {
                        width: 28px; height: 28px;
                        border-radius: 8px;
                        background: rgba(255,255,255,.12);
                        display: flex; align-items: center; justify-content: center;
                        font-size: 16px; opacity: .6;
                    }
                    .order-number {
                        width: 26px; height: 26px;
                        border-radius: 50%;
                        background: linear-gradient(135deg, var(--color-primary, #10B981), var(--color-secondary, #059669));
                        display: flex; align-items: center; justify-content: center;
                        font-weight: 800; font-size: 13px;
                        color: white;
                    }
                </style>
                @php $qPrompt = $firstQ['question'] ?? $firstQ['text'] ?? null; @endphp
                <div class="question-section">
                    @include('partials.question-media', ['q' => $firstQ])
                    @if(!empty($qPrompt))
                        {{-- نصّ السؤال الفعلي (كان لا يظهر للطالب) --}}
                        <div class="question-text">{{ $qPrompt }}</div>
                        <p style="color:rgba(255,255,255,0.7);font-size:14px;text-align:center;margin-bottom:6px;">
                            @if(in_array($effType, ['word_ordering', 'word_order'])) 🔤 رتّب الكلمات بالترتيب الصحيح
                            @else 📝 رتّب الجمل بالترتيب الصحيح @endif
                        </p>
                    @else
                        <div class="question-text">
                            @if(in_array($effType, ['word_ordering', 'word_order'])) 🔤 رتّب الكلمات بالترتيب الصحيح
                            @else 📝 رتّب الجمل بالترتيب الصحيح
                            @endif
                        </div>
                    @endif
                    <p style="color:rgba(255,255,255,0.7);font-size:14px;text-align:center;margin-bottom:18px;">
                        ↕ اسحب العناصر لإعادة ترتيبها — الأعلى يُحتسب أولاً
                    </p>
                    <ul id="orderingList" style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px;">
                        @foreach($shuffled as $idx => $item)
                            <li class="order-item" draggable="true"
                                data-value="{{ is_array($item) ? ($item['text'] ?? '') : $item }}">
                                <span class="order-number">{{ $idx + 1 }}</span>
                                <span class="order-handle">≡</span>
                                <span style="flex:1;">{{ is_array($item) ? ($item['text'] ?? '') : $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <input type="hidden" name="ordering_answer" id="orderingAnswer">
                </div>

            @elseif($effType === 'letter_choice')
                {{-- اختيار حروف لتكوين كلمة (Issue 36, 64) --}}
                @php
                    $firstQ = is_array($activity->questions) ? ($activity->questions[0] ?? []) : [];
                    $targetWord = $firstQ['word'] ?? $firstQ['target_word'] ?? $firstQ['correct_answer'] ?? $firstQ['answer'] ?? '';

                    // الحروف الواجب توفّرها لتكوين الإجابة (مع مراعاة التكرار): من الكلمة الهدف،
                    // وإن غابت فمن الحروف المخزّنة (توافق خلفي مع أسئلة قديمة بلا word).
                    $mustHave = preg_split('//u', (string) $targetWord, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    if (empty($mustHave)) {
                        foreach ((array) ($firstQ['letters'] ?? $firstQ['options'] ?? []) as $l) {
                            $l = is_array($l) ? ($l['text'] ?? $l['label'] ?? '') : (string) $l;
                            $mustHave = array_merge($mustHave, preg_split('//u', $l, -1, PREG_SPLIT_NO_EMPTY) ?: []);
                        }
                    }

                    // المجمع = كل الحروف الهجائية العربية (نُظهر كل الحروف لا حروف الإجابة فقط)
                    // + نسخ إضافية لأي حرف يتكرر في الإجابة أو بأشكال خاصة (ة/أ/إ/آ/ؤ/ئ/ى/ء)
                    // كي تبقى الكلمة قابلة للتكوين. ثم خلط عشوائي في كل عرض.
                    $alphabet = ['ا','ب','ت','ث','ج','ح','خ','د','ذ','ر','ز','س','ش','ص','ض','ط','ظ','ع','غ','ف','ق','ك','ل','م','ن','ه','و','ي'];
                    $pool = $alphabet;
                    foreach (array_count_values($mustHave) as $ch => $need) {
                        $have = count(array_keys($pool, (string) $ch, true));
                        for ($i = $have; $i < $need; $i++) {
                            $pool[] = (string) $ch;
                        }
                    }
                    $availableLetters = collect($pool)->shuffle()->values()->all();
                @endphp
                @php $qPrompt = $firstQ['question'] ?? $firstQ['text'] ?? null; @endphp
                <div class="question-section">
                    @include('partials.question-media', ['q' => $firstQ])
                    @if(!empty($qPrompt))
                        {{-- نصّ السؤال/اللغز الفعلي (كان لا يظهر للطالب) --}}
                        <div class="question-text">{{ $qPrompt }}</div>
                        <p style="color:rgba(255,255,255,0.7);font-size:14px;text-align:center;margin-bottom:12px;">🔤 كوّن الكلمة الصحيحة من الحروف</p>
                    @else
                        <div class="question-text">🔤 كوّن الكلمة الصحيحة من الحروف</div>
                    @endif
                    @if(!empty($firstQ['hint']))
                        <p style="color:rgba(255,255,255,.7);font-size:14px;text-align:center;margin-bottom:12px;">💡 {{ $firstQ['hint'] }}</p>
                    @endif
                    <div id="letterAnswerBox"
                         style="min-height:60px;border:2px dashed rgba(255,255,255,0.3);border-radius:12px;padding:14px;margin-bottom:14px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center;align-items:center;font-size:22px;font-weight:800;letter-spacing:6px;color:white;background:rgba(255,255,255,0.05);">
                        <span id="letterPlaceholder" style="opacity:.4;font-size:14px;font-weight:400;letter-spacing:0;">اضغط على الحروف لتكوين الكلمة</span>
                    </div>
                    <div id="letterPool" style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;">
                        @foreach($availableLetters as $letter)
                            <button type="button" class="letter-btn"
                                    data-letter="{{ $letter }}"
                                    style="width:48px;height:48px;border-radius:10px;border:2px solid rgba(255,255,255,0.25);background:rgba(255,255,255,0.1);color:white;font-size:20px;font-weight:800;cursor:pointer;transition:.2s;">
                                {{ $letter }}
                            </button>
                        @endforeach
                    </div>
                    <button type="button" id="letterReset"
                            style="margin-top:14px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);color:#fca5a5;padding:8px 16px;border-radius:10px;font-size:13px;cursor:pointer;">↩ مسح والبدء من جديد</button>
                    <input type="hidden" name="letter_answer" id="letterAnswer">
                </div>

            @elseif($effType === 'short_answer')
                {{-- إجابة قصيرة (Issue 63) --}}
                @php
                    $firstQ = is_array($activity->questions) ? ($activity->questions[0] ?? []) : [];
                    $promptText = $firstQ['question'] ?? $firstQ['text'] ?? strip_tags((string) $activity->description);
                @endphp
                <div class="question-section">
                    @include('partials.question-media', ['q' => $firstQ])
                    <div class="question-text">✍️ {{ $promptText ?: 'أكمل الفراغ بالإجابة الصحيحة' }}</div>
                    <input type="text" name="answer" id="shortAnswerInput"
                           class="text-input-field"
                           placeholder="اكتب إجابتك هنا..."
                           autocomplete="off"
                           required
                           style="width:100%;padding:14px 18px;border-radius:12px;border:2px solid rgba(255,255,255,0.2);background:rgba(255,255,255,0.08);color:white;font-size:18px;text-align:center;font-weight:700;">
                </div>

            @elseif(in_array($activity->type, ['upload', 'creative', 'project', 'practical']) || ($activity->question_type ?? null) === 'file_upload')
                {{-- رفع ملف + وصف اختياري (Issue 55) — يشمل creative/project/practical لأنها كلها تتطلب تسليم ملف فعلي --}}
                @php
                    $allowed = $activity->allowed_file_types ?? ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'mp4', 'mp3'];
                    $maxMb = $activity->max_file_size ?? 10;
                    $isFileRequired = $activity->type === 'upload' || ($activity->question_type ?? null) === 'file_upload';
                @endphp
                <div class="question-section">
                    <div class="question-text">
                        @if($activity->type === 'practical') 🎯 ارفع مقطعاً أو صورة تُوثّق نشاطك العملي
                        @elseif($activity->type === 'creative') ✨ ارفع عملك الإبداعي
                        @elseif($activity->type === 'project') 🏗️ ارفع ملفات مشروعك
                        @else 📤 ارفع ملف إجابتك
                        @endif
                    </div>
                    <p style="color:rgba(255,255,255,0.55);font-size:13px;text-align:center;margin-bottom:14px;">
                        الأنواع المسموحة: {{ implode('، ', is_array($allowed) ? $allowed : ['pdf']) }} — الحد الأقصى: {{ $maxMb }}MB
                    </p>
                    <label for="activityFile"
                           style="display:flex;flex-direction:column;align-items:center;justify-content:center;border:2px dashed rgba(255,255,255,0.3);border-radius:14px;padding:30px;cursor:pointer;background:rgba(255,255,255,0.04);transition:.2s;">
                        <span style="font-size:42px;margin-bottom:10px;">📎</span>
                        <span style="color:white;font-weight:700;font-size:16px;">اضغط لاختيار ملف</span>
                        <span id="activityFileName" style="color:rgba(255,255,255,.6);font-size:13px;margin-top:6px;">لم يتم اختيار ملف</span>
                    </label>
                    <input type="file" id="activityFile" name="answer_file"
                           accept="{{ '.' . implode(',.', is_array($allowed) ? $allowed : ['pdf']) }}"
                           style="display:none;" {{ $isFileRequired ? 'required' : '' }}>
                    <textarea name="answer" id="uploadDescription" rows="3"
                              class="text-input-field"
                              placeholder="@if($isFileRequired)ملاحظة اختيارية مع الملف...@else اشرح ما قمت به (نص أو ملف يكفي أحدهما)...@endif"
                              style="margin-top:14px;width:100%;padding:12px 14px;border-radius:10px;border:2px solid rgba(255,255,255,0.2);background:rgba(255,255,255,0.06);color:white;"></textarea>
                </div>

            @else
                {{-- إجابة نصية افتراضية (discussion, quiz بدون أسئلة) --}}
                <div class="question-section">
                    <div class="question-text">
                        @if($activity->type === 'discussion') 💬 شارك رأيك في النقاش
                        @else أجب على السؤال التالي
                        @endif
                    </div>
                    <textarea
                        class="text-input-field"
                        name="answer"
                        rows="4"
                        placeholder="اكتب إجابتك هنا..."
                        required
                    ></textarea>
                </div>
            @endif

            <button type="submit" class="submit-btn" id="submitBtn">
                إرسال الإجابة ✓
            </button>
        </form>
        @endif
    </div>
</div>

<div class="feedback-modal" id="feedbackModal">
    <div class="feedback-card" id="feedbackCard">
        <div class="feedback-header">
            <div class="feedback-icon" id="feedbackIcon">🎉</div>
            <div class="feedback-title" id="feedbackTitle">ممتاز!</div>
        </div>
        <div class="feedback-message" id="feedbackMessage">تم إرسال إجابتك بنجاح!</div>
        <div class="feedback-xp" id="feedbackXP">
            <span>⭐</span>
            <span>+10 XP</span>
        </div>
        <button class="continue-btn" onclick="continueToNext()">متابعة</button>
    </div>
</div>

@push('scripts')
@include('partials.answer-celebration')
<script>
    const activityId = {{ $activity->id ?? 0 }};
    const lessonId = {{ $lesson->id ?? 0 }};
    const activityType = '{{ $activity->type ?? "quiz" }}';

    // تهريب HTML لعرض محتوى من إنشاء المعلم بأمان (منع XSS في الإجابة الصحيحة)
    function escapeHtml(str) {
        return str.replace(/[&<>"']/g, s => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[s]);
    }

    function selectOption(element, questionIndex) {
        // Remove selected from siblings
        const parent = element.closest('.quiz-question');
        parent.querySelectorAll('.quiz-option').forEach(opt => opt.classList.remove('selected'));
        // Select this one
        element.classList.add('selected');
        element.querySelector('input[type="radio"]').checked = true;
    }

    // Handle image ordering within a quiz question
    function updateQuizImageOrder(qIndex) {
        const container = document.querySelector(`.quiz-image-order-container[data-question-index="${qIndex}"]`);
        if (!container) return;
        const items = container.querySelectorAll('.quiz-image-order-item');
        const result = [];
        items.forEach(item => {
            const select = item.querySelector('.quiz-img-select');
            const url = item.dataset.url || '';
            const order = select ? parseInt(select.value) : 0;
            result.push({ image_url: url, selected_order: order });
        });
        const hiddenInput = container.closest('.quiz-question').querySelector('.quiz-image-order-answer');
        if (hiddenInput) hiddenInput.value = JSON.stringify(result);
    }
    // Initialize all quiz image order answers on load
    document.querySelectorAll('.quiz-image-order-container').forEach(c => {
        const qIdx = c.dataset.questionIndex;
        if (qIdx !== undefined) updateQuizImageOrder(parseInt(qIdx));
    });
    
    @if(isset($submission) && $submission && !$allowResubmit)
        // Activity already submitted (no retry allowed) - show full progress
        setTimeout(() => {
            document.getElementById('progressBar').style.width = '100%';
        }, 300);
    @else
    // ===== Drag & Drop ordering (word/sentence) — مع feedback بصري قوي =====
    (function () {
        const list = document.getElementById('orderingList');
        if (!list) return;
        let dragged = null;

        function refreshNumbers() {
            list.querySelectorAll('.order-item').forEach((item, idx) => {
                const num = item.querySelector('.order-number');
                if (num) num.textContent = idx + 1;
            });
        }

        function syncOrderingAnswer() {
            const values = Array.from(list.querySelectorAll('.order-item')).map(i => i.dataset.value);
            const inp = document.getElementById('orderingAnswer');
            if (inp) inp.value = JSON.stringify(values);
            refreshNumbers();
        }

        function clearDragOver() {
            list.querySelectorAll('.order-item.drag-over').forEach(el => el.classList.remove('drag-over'));
        }

        list.querySelectorAll('.order-item').forEach(item => {
            // Desktop: HTML5 drag
            item.addEventListener('dragstart', (e) => {
                dragged = item;
                item.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', '');
            });
            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
                clearDragOver();
                dragged = null;
                syncOrderingAnswer();
            });
            item.addEventListener('dragenter', (e) => {
                if (dragged && dragged !== item) item.classList.add('drag-over');
            });
            item.addEventListener('dragleave', () => item.classList.remove('drag-over'));
            item.addEventListener('dragover', (e) => { e.preventDefault(); });
            item.addEventListener('drop', (e) => {
                e.preventDefault();
                clearDragOver();
                if (!dragged || dragged === item) return;
                const rect = item.getBoundingClientRect();
                const before = (e.clientY - rect.top) < rect.height / 2;
                list.insertBefore(dragged, before ? item : item.nextSibling);
                syncOrderingAnswer();
            });

            // Touch (mobile)
            let lastTouch = null;
            item.addEventListener('touchstart', (e) => {
                dragged = item;
                item.classList.add('dragging');
            }, { passive: true });
            item.addEventListener('touchmove', (e) => {
                if (!dragged) return;
                const t = e.touches[0];
                lastTouch = t;
                const elem = document.elementFromPoint(t.clientX, t.clientY)?.closest('.order-item');
                clearDragOver();
                if (elem && elem !== dragged) {
                    elem.classList.add('drag-over');
                    const rect = elem.getBoundingClientRect();
                    const before = (t.clientY - rect.top) < rect.height / 2;
                    list.insertBefore(dragged, before ? elem : elem.nextSibling);
                }
            }, { passive: true });
            item.addEventListener('touchend', () => {
                if (dragged) dragged.classList.remove('dragging');
                clearDragOver();
                dragged = null;
                syncOrderingAnswer();
            });
        });

        syncOrderingAnswer();
    })();

    // ===== Letter choice (build word) =====
    (function () {
        const pool = document.getElementById('letterPool');
        const box = document.getElementById('letterAnswerBox');
        if (!pool || !box) return;

        const placeholder = document.getElementById('letterPlaceholder');
        const hidden = document.getElementById('letterAnswer');
        const built = [];

        function render() {
            const text = built.join('');
            if (text.length === 0) {
                box.innerHTML = '';
                if (placeholder) box.appendChild(placeholder);
                placeholder.style.display = '';
            } else {
                box.innerHTML = text.split('').map(ch => `<span style="background:rgba(16,185,129,.25);padding:4px 10px;border-radius:8px;">${ch}</span>`).join('');
            }
            if (hidden) hidden.value = text;
        }

        pool.querySelectorAll('.letter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                built.push(btn.dataset.letter);
                btn.style.background = 'rgba(16,185,129,.3)';
                btn.style.borderColor = 'rgba(16,185,129,.6)';
                render();
            });
        });

        document.getElementById('letterReset')?.addEventListener('click', () => {
            built.length = 0;
            pool.querySelectorAll('.letter-btn').forEach(b => {
                b.style.background = 'rgba(255,255,255,0.1)';
                b.style.borderColor = 'rgba(255,255,255,0.25)';
            });
            render();
        });
    })();

    // ===== File upload preview =====
    (function () {
        const file = document.getElementById('activityFile');
        const label = document.getElementById('activityFileName');
        if (!file || !label) return;
        file.addEventListener('change', () => {
            const f = file.files?.[0];
            label.textContent = f ? `${f.name} (${(f.size/1024).toFixed(1)} KB)` : 'لم يتم اختيار ملف';
        });
    })();

    // ===== مؤقّت الاختبار الموقوت =====
    (function () {
        const el = document.getElementById('quizTimer');
        if (!el) return;
        let remaining = parseInt(el.dataset.duration, 10) * 60;
        const txt = document.getElementById('quizTimerText');
        const render = () => {
            const m = String(Math.floor(remaining / 60)).padStart(2, '0');
            const s = String(remaining % 60).padStart(2, '0');
            if (txt) txt.textContent = `${m}:${s}`;
            if (remaining <= 60) el.style.color = '#FCA5A5';
        };
        render();
        const iv = setInterval(() => {
            remaining--;
            render();
            if (remaining <= 0) {
                clearInterval(iv);
                showToast('⏱ انتهى وقت الاختبار — يتم إرسال إجابتك', 'warning');
                const f = document.getElementById('activityForm');
                if (f) f.requestSubmit(document.getElementById('submitBtn'));
            }
        }, 1000);
    })();

    document.getElementById('activityForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'جاري الإرسال...';

        let answer = '';
        let answerFile = null;

        const orderingInput = document.getElementById('orderingAnswer');
        const letterInput = document.getElementById('letterAnswer');
        const shortInput = document.getElementById('shortAnswerInput');
        const fileInput = document.getElementById('activityFile');

        if (orderingInput && orderingInput.value) {
            answer = orderingInput.value;
        } else if (letterInput && letterInput.value) {
            answer = letterInput.value;
        } else if (shortInput && shortInput.value.trim()) {
            answer = shortInput.value.trim();
        } else if (fileInput && fileInput.files && fileInput.files.length > 0) {
            answerFile = fileInput.files[0];
            const desc = document.getElementById('uploadDescription');
            answer = desc ? desc.value.trim() : '(ملف مرفوع)';
            if (!answer) answer = fileInput.files[0].name;
        } else if (activityType === 'quiz' || activityType === 'exercise') {
            // Collect quiz/exercise answers
            const questions = document.querySelectorAll('.quiz-question');
            if (questions.length > 0) {
                const answers = {};
                let allAnswered = true;
                questions.forEach((q, index) => {
                    // Check for image_order question (hidden input)
                    const imgOrderInput = q.querySelector('.quiz-image-order-answer');
                    if (imgOrderInput) {
                        // Image order question — update and use hidden input value
                        updateQuizImageOrder(index);
                        answers[index] = imgOrderInput.value || '[]';
                    } else {
                        // Check for radio buttons first
                        const selected = q.querySelector('input[type="radio"]:checked');
                        if (selected) {
                            answers[index] = parseInt(selected.value);
                        } else {
                            // Check for textarea (exercise text answers)
                            const textarea = q.querySelector('textarea.exercise-answer');
                            if (textarea) {
                                answers[index] = textarea.value.trim();
                                if (!answers[index]) allAnswered = false;
                            } else {
                                allAnswered = false;
                            }
                        }
                    }
                });
                
                if (!allAnswered) {
                    showToast('⚠️ الرجاء الإجابة على جميع الأسئلة', 'warning');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'إرسال الإجابة ✓';
                    return;
                }
                answer = JSON.stringify(answers);
            } else {
                // Text answer fallback
                const textarea = document.querySelector('textarea[name="answer"]');
                answer = textarea ? textarea.value : '';
            }
        } else if (activityType === 'image_order') {
            // Image order answer
            updateImageOrderAnswer();
            const orderInput = document.getElementById('imageOrderAnswer');
            answer = orderInput ? orderInput.value : '';
            if (!answer) {
                showToast('⚠️ الرجاء ترتيب الصور أولاً', 'warning');
                submitBtn.disabled = false;
                submitBtn.textContent = 'إرسال الإجابة ✓';
                return;
            }
        } else {
            // Text answer
            const textarea = document.querySelector('textarea[name="answer"]');
            answer = textarea ? textarea.value : '';
        }
        
        if (!answer || !answer.trim()) {
            showToast('⚠️ الرجاء كتابة إجابة', 'warning');
            submitBtn.disabled = false;
            submitBtn.textContent = 'إرسال الإجابة ✓';
            return;
        }
        
        try {
            let response;
            if (answerFile) {
                // رفع ملف عبر FormData (Issue 55)
                const fd = new FormData();
                fd.append('answer', answer);
                fd.append('answer_file', answerFile);
                fd.append('xp', '{{ $activity->points ?? 10 }}');
                response = await fetch('{{ route("student.activity.submit", ["id" => $activity->id ?? 0]) }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: fd,
                });
            } else {
                response = await fetch('{{ route("student.activity.submit", ["id" => $activity->id ?? 0]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        answer: answer,
                        xp: {{ $activity->points ?? 10 }}
                    })
                });
            }
            
            if (!response.ok) {
                const text = await response.text();
                console.error('Server error:', response.status, text.substring(0, 500));
                throw new Error('خطأ في الخادم: ' + response.status);
            }
            
            const data = await response.json();

            if (data.success) {
                // تحديد الرسالة والأيقونة بناءً على الدرجة الفعلية
                const score = (data.score === null || data.score === undefined) ? null : Number(data.score);
                let title = 'تم استلام إجابتك ✓';
                let icon = '📨';
                let titleColor = '#3b82f6';

                if (score !== null) {
                    // العتبة الفاصلة هي درجة النجاح التي حدّدها المعلم (لا عتبات ثابتة)
                    const passing = (data.passing_score === null || data.passing_score === undefined) ? 50 : Number(data.passing_score);
                    const passed = (data.passed === true) || (score >= passing);
                    if (passed && score >= 90) {
                        title = 'ممتاز! 🎉';
                        icon = '🎉';
                        titleColor = '#10B981';
                    } else if (passed) {
                        title = 'أحسنت — اجتزت النشاط';
                        icon = '✅';
                        titleColor = '#10B981';
                    } else {
                        title = 'لم تبلغ درجة النجاح (' + passing + '%)';
                        icon = '❌';
                        titleColor = '#EF4444';
                    }
                } else {
                    title = 'تم تسليم إجابتك للمراجعة';
                    icon = '⏳';
                    titleColor = '#6366f1';
                }

                const titleEl = document.getElementById('feedbackTitle');
                const iconEl = document.getElementById('feedbackIcon');
                if (titleEl) { titleEl.textContent = title; titleEl.style.color = titleColor; }
                if (iconEl) iconEl.textContent = icon;

                // عرض النقاط المكتسبة (0 لو الدرجة منخفضة)
                const xpEarned = Number(data.xp_earned ?? 0);
                document.getElementById('feedbackXP').innerHTML =
                    `<span>⭐</span><span>+${xpEarned} XP</span>`;

                let msgHtml = '';
                if (score !== null) {
                    msgHtml += `<span style="font-size: 22px; font-weight: 800; color: ${titleColor};">الدرجة: ${score}%</span>`;
                    msgHtml += `<br><span style="font-size: 14px; opacity: .8;">${xpEarned} من ${data.activity_points} نقطة</span>`;
                } else {
                    msgHtml = 'سيتم احتساب نقاطك بعد مراجعة المعلم.';
                }

                // عرض الإجابة الصحيحة تعليمياً بعد محاولة خاطئة/جزئية (يرسلها الخادم فقط عند score < 100)
                if (data.correct_answer) {
                    msgHtml += `<div style="margin-top:16px;padding:14px 16px;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.45);border-radius:12px;text-align:center;">
                        <div style="font-size:13px;color:#10B981;font-weight:800;margin-bottom:6px;">✅ الإجابة الصحيحة</div>
                        <div style="font-size:17px;color:#fff;font-weight:700;line-height:1.9;">${escapeHtml(String(data.correct_answer))}</div>
                    </div>`;
                }

                if (data.streak_bonus > 0) {
                    msgHtml += `<br><span style="color: #f59e0b;">🔥 ${data.streak_message}</span>`;
                }

                document.getElementById('feedbackMessage').innerHTML = msgHtml;
                document.getElementById('feedbackModal').classList.add('active');
                document.getElementById('progressBar').style.width = '100%';

                // === تفاعل احتفالي/حزين حسب النتيجة ===
                const _fbScore = (data.score === null || data.score === undefined) ? null : Number(data.score);
                if (_fbScore !== null) { // score===null ⇒ بانتظار مراجعة المعلم: لا احتفال ولا حزن
                    const _pass = (data.passed === true) ||
                        (_fbScore >= ((data.passing_score === null || data.passing_score === undefined) ? 50 : Number(data.passing_score)));
                    if (_pass) celebrateCorrect(); else celebrateWrong();
                }
            } else {
                showToast('❌ ' + (data.message || 'حدث خطأ'), 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'إرسال الإجابة ✓';
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('❌ حدث خطأ. حاول مرة أخرى.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'إرسال الإجابة ✓';
        }
    });
    
    setTimeout(() => {
        document.getElementById('progressBar').style.width = '50%';
    }, 300);
    @endif
    
    function continueToNext() {
        @if(isset($nextActivity))
            window.location.href = '{{ route("student.activity", ["id" => $nextActivity->id ?? 0]) }}';
        @elseif(isset($lesson))
            window.location.href = '{{ route("student.lesson", ["id" => $lesson->id ?? 0]) }}';
        @else
            window.location.href = '{{ route("student.dashboard") }}';
        @endif
    }
    
    function showToast(message, type = 'warning') {
        // Remove existing toast
        const existing = document.querySelector('.toast-notification');
        if (existing) existing.remove();
        
        const icons = { warning: '⚠️', error: '❌', info: 'ℹ️' };
        
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || '⚠️'}</span>
            <span>${message.replace(/^[⚠️❌ℹ️]+\s*/, '')}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;
        document.body.appendChild(toast);
        
        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });
        
        // Auto dismiss after 4 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }

    // تفاعل الإجابة (كونفيتي/ألعاب نارية + صوت سرور/خسارة) مُستخرَج إلى partials/answer-celebration
    // — celebrateCorrect() و celebrateWrong() معرّفتان على window هناك (مضمّن أعلى هذا الـpush).

    // ================================
    // Image Order drag-and-drop logic
    // ================================
    function updateImageOrderAnswer() {
        const items = document.querySelectorAll('#imageOrderContainer .image-order-item');
        if (!items.length) return;
        const result = [];
        const chosen = [];
        let complete = true;
        items.forEach(item => {
            const select = item.querySelector('.image-order-select');
            const url = item.dataset.url || '';
            const raw = select ? select.value : '';
            if (raw === '' || raw === null) { complete = false; }
            const order = parseInt(raw);
            if (!isNaN(order)) chosen.push(order);
            result.push({ image_url: url, selected_order: isNaN(order) ? 0 : order });
        });
        const input = document.getElementById('imageOrderAnswer');
        if (!input) return;
        // لا نعتبر الإجابة جاهزة إلا إذا اختار الطالب ترتيباً لكل صورة بتبديلة صحيحة (بلا تكرار)
        const uniqueValid = complete && new Set(chosen).size === items.length;
        input.value = uniqueValid ? JSON.stringify(result) : '';
    }

    // Drag-and-drop for image reordering
    (function() {
        const container = document.getElementById('imageOrderContainer');
        if (!container) return;

        let draggedItem = null;

        container.addEventListener('dragstart', function(e) {
            draggedItem = e.target.closest('.image-order-item');
            if (!draggedItem) return;
            draggedItem.style.opacity = '0.4';
            e.dataTransfer.effectAllowed = 'move';
        });

        container.addEventListener('dragend', function(e) {
            if (draggedItem) {
                draggedItem.style.opacity = '1';
                draggedItem = null;
            }
            container.querySelectorAll('.image-order-item').forEach(item => {
                item.style.borderColor = 'rgba(255,255,255,0.15)';
            });
        });

        container.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const target = e.target.closest('.image-order-item');
            if (target && target !== draggedItem) {
                target.style.borderColor = '#10B981';
            }
        });

        container.addEventListener('dragleave', function(e) {
            const target = e.target.closest('.image-order-item');
            if (target) {
                target.style.borderColor = 'rgba(255,255,255,0.15)';
            }
        });

        container.addEventListener('drop', function(e) {
            e.preventDefault();
            const target = e.target.closest('.image-order-item');
            if (!target || !draggedItem || target === draggedItem) return;

            // Swap position in DOM
            const items = [...container.querySelectorAll('.image-order-item')];
            const dragIdx = items.indexOf(draggedItem);
            const dropIdx = items.indexOf(target);

            if (dragIdx < dropIdx) {
                container.insertBefore(draggedItem, target.nextSibling);
            } else {
                container.insertBefore(draggedItem, target);
            }

            // Update select values to new positions
            const allItems = container.querySelectorAll('.image-order-item');
            allItems.forEach((item, i) => {
                const sel = item.querySelector('.image-order-select');
                if (sel) sel.value = i + 1;
            });

            updateImageOrderAnswer();
        });

        // ===== Touch support للموبايل (HTML5 DnD لا يعمل على iOS/Android) =====
        let touchDragged = null;
        let touchStartY = 0;
        let touchOffsetY = 0;

        container.querySelectorAll('.image-order-item').forEach(item => {
            item.style.touchAction = 'none';
            item.style.userSelect = 'none';

            item.addEventListener('touchstart', function(e) {
                touchDragged = this;
                touchStartY = e.touches[0].clientY;
                const rect = this.getBoundingClientRect();
                touchOffsetY = touchStartY - rect.top;
                this.style.opacity = '0.5';
                this.style.transform = 'scale(1.05)';
                this.style.zIndex = '1000';
            }, { passive: true });

            item.addEventListener('touchmove', function(e) {
                if (!touchDragged) return;
                e.preventDefault();
                const touchY = e.touches[0].clientY;
                const touchX = e.touches[0].clientX;

                // البحث عن العنصر تحت الإصبع
                touchDragged.style.pointerEvents = 'none';
                const el = document.elementFromPoint(touchX, touchY);
                touchDragged.style.pointerEvents = 'auto';

                if (!el) return;
                const target = el.closest('.image-order-item');
                if (target && target !== touchDragged) {
                    const items = [...container.querySelectorAll('.image-order-item')];
                    const dragIdx = items.indexOf(touchDragged);
                    const tgtIdx = items.indexOf(target);
                    if (dragIdx < tgtIdx) {
                        container.insertBefore(touchDragged, target.nextSibling);
                    } else {
                        container.insertBefore(touchDragged, target);
                    }
                }
            }, { passive: false });

            item.addEventListener('touchend', function() {
                if (!touchDragged) return;
                touchDragged.style.opacity = '1';
                touchDragged.style.transform = '';
                touchDragged.style.zIndex = '';

                // تحديث select values بعد التحريك
                const allItems = container.querySelectorAll('.image-order-item');
                allItems.forEach((item, i) => {
                    const sel = item.querySelector('.image-order-select');
                    if (sel) sel.value = i + 1;
                });
                updateImageOrderAnswer();
                touchDragged = null;
            });

            item.addEventListener('touchcancel', function() {
                if (touchDragged) {
                    touchDragged.style.opacity = '1';
                    touchDragged.style.transform = '';
                    touchDragged.style.zIndex = '';
                    touchDragged = null;
                }
            });
        });

        // Initialize answer on load
        updateImageOrderAnswer();
    })();
</script>
@endpush
</div>
@endsection
