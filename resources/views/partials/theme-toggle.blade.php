{{-- Wahy Theme System — المصدر الموحّد الوحيد لتبديل الوضع الليلي/النهاري لكل الـ layouts.
     يفرض الدستور مفتاحاً واحداً: localStorage['wahy-theme'] (فاتح افتراضي، الليلي اختياري عبر الزر).
     يربط أي زر تبديل موجود: #wahyThemeFab (العائم) + #sidebarThemeToggle (الشريط الجانبي) + [data-theme-toggle].
     التغطية اللونية الشاملة مُستخرجة في partials/dark-coverage (تُضمَّن هنا وفي student-app).
     ملاحظة معمارية: هذا الـ partial هو الوحيد المسموح له بإدارة data-theme — أي سكربت آخر يقرأ/يكتب مفتاحاً مختلفاً
     (admin-theme/theme) يُعدّ مخالفة ويكسر استمرارية التبديل عبر الصفحات. --}}

<style>
    /* CSS Variables الموحدة للوضعَين */
    :root, html[data-theme="light"] {
        --w-bg: #f8fafc;
        --w-bg-elevated: #ffffff;
        --w-text: #0f172a;
        --w-text-muted: #475569;
        --w-border: rgba(15, 23, 42, 0.08);
        --w-card: #ffffff;
        --w-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        color-scheme: light;
    }
    html[data-theme="dark"] {
        --w-bg: #0b1220;
        --w-bg-elevated: #111827;
        --w-text: #f1f5f9;
        --w-text-muted: #94a3b8;
        --w-border: rgba(255, 255, 255, 0.10);
        --w-card: #1e293b;
        --w-shadow: 0 10px 28px rgba(0, 0, 0, 0.45);
        color-scheme: dark;
    }

    /* تطبيق الـ Dark Mode على عناصر الـ Admin/Teacher/Parent/SchoolAdmin layouts (حاويات بأسماء أصناف شائعة). */
    html[data-theme="dark"] body {
        background: var(--w-bg) !important;
        color: var(--w-text);
    }
    html[data-theme="dark"] .admin-card,
    html[data-theme="dark"] .form-card,
    html[data-theme="dark"] .stat-card,
    html[data-theme="dark"] .data-table,
    html[data-theme="dark"] .info-card,
    html[data-theme="dark"] .filters-bar,
    html[data-theme="dark"] .card,
    html[data-theme="dark"] .box,
    html[data-theme="dark"] .panel,
    html[data-theme="dark"] .widget,
    html[data-theme="dark"] .content-card,
    html[data-theme="dark"] .dashboard-card,
    html[data-theme="dark"] .section-card,
    html[data-theme="dark"] .modal-content,
    html[data-theme="dark"] .dropdown-menu,
    html[data-theme="dark"] .admin-topbar,
    html[data-theme="dark"] .admin-header {
        background: var(--w-card) !important;
        color: var(--w-text) !important;
        border-color: var(--w-border) !important;
        box-shadow: var(--w-shadow) !important;
    }
    html[data-theme="dark"] .admin-card h1,
    html[data-theme="dark"] .admin-card h2,
    html[data-theme="dark"] .admin-card h3,
    html[data-theme="dark"] .admin-card h4,
    html[data-theme="dark"] .card h1,
    html[data-theme="dark"] .card h2,
    html[data-theme="dark"] .card h3,
    html[data-theme="dark"] .card h4,
    html[data-theme="dark"] .card h5,
    html[data-theme="dark"] table th,
    html[data-theme="dark"] .page-title,
    html[data-theme="dark"] .section-title,
    html[data-theme="dark"] .card-title {
        color: var(--w-text) !important;
    }
    html[data-theme="dark"] .text-muted,
    html[data-theme="dark"] .text-secondary,
    html[data-theme="dark"] .subtitle,
    html[data-theme="dark"] small {
        color: var(--w-text-muted) !important;
    }
    html[data-theme="dark"] table td,
    html[data-theme="dark"] table tr {
        border-color: var(--w-border) !important;
        background: transparent !important;
        color: var(--w-text) !important;
    }
    html[data-theme="dark"] input:not([type="checkbox"]):not([type="radio"]):not([type="color"]),
    html[data-theme="dark"] select,
    html[data-theme="dark"] textarea {
        background: rgba(255,255,255,0.05) !important;
        color: var(--w-text) !important;
        border-color: var(--w-border) !important;
    }
    html[data-theme="dark"] input::placeholder,
    html[data-theme="dark"] textarea::placeholder {
        color: var(--w-text-muted) !important;
    }
    html[data-theme="dark"] a:not(.btn):not(.btn-primary):not(.btn-secondary):not(.admin-btn):not(.admin-nav-link) {
        color: #93c5fd;
    }
    html[data-theme="dark"] .btn-secondary {
        background: rgba(255,255,255,.08) !important;
        color: var(--w-text) !important;
        border-color: var(--w-border) !important;
    }

    /* زر التبديل العائم */
    .wahy-theme-fab {
        position: fixed;
        bottom: 24px;
        inset-inline-end: 24px;
        z-index: 9990;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: 1px solid var(--w-border);
        background: var(--w-card);
        color: var(--w-text);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        box-shadow: var(--w-shadow);
        transition: transform .15s, background .25s;
    }
    .wahy-theme-fab:hover { transform: scale(1.08); }
    @media (max-width: 640px) {
        .wahy-theme-fab { width: 42px; height: 42px; bottom: 16px; inset-inline-end: 16px; font-size: 18px; }
    }
</style>

{{-- التغطية الشاملة المتّسقة (خلفيات فاتحة ← داكنة + نصوص داكنة ← فاتحة + استثناءات) --}}
@include('partials.dark-coverage')

<script>
    // FOUC guard: يُطبّق الثيم قبل الرسم.
    // الأولوية: تفضيل المستخدم المحفوظ (wahy-theme) ← ثم الافتراضي الخادمي المرسوم على <html> ← ثم فاتح.
    (function () {
        try {
            var root = document.documentElement;
            var saved = localStorage.getItem('wahy-theme');
            var fallback = root.getAttribute('data-theme') || 'light';
            root.setAttribute('data-theme', saved || fallback);
        } catch (e) {}
    })();
</script>

<script>
    // موحّد التبديل: يربط كل أزرار التبديل الموجودة في الصفحة إلى نفس المفتاح والحالة.
    (function () {
        function initWahyTheme() {
            var root = document.documentElement;
            var toggles = document.querySelectorAll('#wahyThemeFab, #sidebarThemeToggle, [data-theme-toggle]');

            function isDark() { return root.getAttribute('data-theme') === 'dark'; }
            function refreshIcons() {
                var dark = isDark();
                var fabIcon = document.getElementById('wahyThemeFabIcon');
                if (fabIcon) fabIcon.textContent = dark ? '☀️' : '🌙';
                toggles.forEach(function (t) {
                    t.setAttribute('aria-pressed', dark ? 'true' : 'false');
                    var lbl = t.querySelector('[data-theme-label]');
                    if (lbl) lbl.textContent = dark ? 'الوضع النهاري' : 'الوضع الليلي';
                });
            }
            function toggle() {
                var next = isDark() ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                try { localStorage.setItem('wahy-theme', next); } catch (e) {}
                refreshIcons();
                document.dispatchEvent(new CustomEvent('wahy:themechange', { detail: { theme: next } }));
            }
            refreshIcons();
            toggles.forEach(function (t) { t.addEventListener('click', toggle); });
            window.addEventListener('storage', function (e) {
                if (e.key === 'wahy-theme' && e.newValue) {
                    root.setAttribute('data-theme', e.newValue);
                    refreshIcons();
                }
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initWahyTheme);
        } else {
            initWahyTheme();
        }
    })();
</script>

@push('after-content')
    <button type="button"
            id="wahyThemeFab"
            class="wahy-theme-fab"
            aria-label="تبديل الوضع الليلي/النهاري"
            title="تبديل الوضع الليلي/النهاري">
        <span id="wahyThemeFabIcon">🌙</span>
    </button>
@endpush
