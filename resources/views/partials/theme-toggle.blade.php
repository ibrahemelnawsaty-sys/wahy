{{-- Wahy Theme System — مكوّن مشترك لكل الـ layouts (admin/teacher/parent/school-admin)
     يضع زر تبديل عائم + إدارة Light/Dark Mode عبر localStorage. --}}

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

    /* تطبيق الـ Dark Mode على عناصر الـ Admin/Teacher/Parent layouts */
    html[data-theme="dark"] body {
        background: var(--w-bg) !important;
    }
    html[data-theme="dark"] .admin-card,
    html[data-theme="dark"] .form-card,
    html[data-theme="dark"] .stat-card,
    html[data-theme="dark"] .data-table,
    html[data-theme="dark"] .info-card,
    html[data-theme="dark"] .filters-bar {
        background: var(--w-card) !important;
        color: var(--w-text) !important;
        border-color: var(--w-border) !important;
        box-shadow: var(--w-shadow) !important;
    }
    html[data-theme="dark"] .admin-card h1,
    html[data-theme="dark"] .admin-card h2,
    html[data-theme="dark"] .admin-card h3,
    html[data-theme="dark"] .admin-card h4,
    html[data-theme="dark"] table th {
        color: var(--w-text) !important;
    }
    html[data-theme="dark"] table td,
    html[data-theme="dark"] table tr {
        border-color: var(--w-border) !important;
        background: transparent !important;
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
    html[data-theme="dark"] a:not(.btn):not(.btn-primary):not(.btn-secondary) {
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

<script>
    // تطبيق الثيم المحفوظ قبل العرض لمنع FOUC
    (function () {
        try {
            var saved = localStorage.getItem('wahy-theme');
            if (!saved) {
                saved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', saved);
        } catch (e) {}
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
    <script>
        (function () {
            var root = document.documentElement;
            var btn = document.getElementById('wahyThemeFab');
            var icon = document.getElementById('wahyThemeFabIcon');
            function refreshIcon() {
                var dark = root.getAttribute('data-theme') === 'dark';
                if (icon) icon.textContent = dark ? '☀️' : '🌙';
                if (btn) btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
            }
            refreshIcon();
            btn?.addEventListener('click', function () {
                var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                try { localStorage.setItem('wahy-theme', next); } catch (e) {}
                refreshIcon();
            });
        })();
    </script>
@endpush
