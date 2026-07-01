{{-- Wahy Dark-Mode Coverage — التغطية الشاملة المتّسقة للوضع الليلي (مصدر واحد، يُضمَّن في theme-toggle
     وفي student-app). المبدأ المانِع للارتداد: نُعتّم كل خلفية فاتحة مُصلَّبة ونُفتّح كل نص داكن معاً.
     يستخدم متغيّرات احتياطية (var(--w-*, fallback)) ليعمل حتى حيث لا تُعرَّف --w-* (صفحات الطالب). --}}
<style>
    /* (0) تجاوز متغيّرات الثيم المحايدة في الوضع الليلي — يُصلح دفعةً واحدة كل عنصر يستهلك
       var(--color-text)/var(--text-primary)/var(--card-bg) (مثل .admin-stat-value). لا نلمس الألوان
       التجارية (--color-primary/secondary) فتبقى الهوية سليمة. */
    html[data-theme="dark"] {
        --color-text: #f1f5f9;
        --color-text-primary: #f1f5f9;
        --color-text-secondary: #cbd5e1;
        --color-text-muted: #94a3b8;
        --color-bg: #0b1220;
        --color-card: #1e293b;
        --card-bg: #1e293b;
        --text-primary: #f1f5f9;
        --text-secondary: #cbd5e1;
        --text-muted: #94a3b8;
        --gray-600: #cbd5e1;
        --gray-700: #cbd5e1;
        --gray-800: #e2e8f0;
        --gray-900: #f1f5f9;
    }

    /* (1) الخلفيات المحايدة الفاتحة (أبيض/رمادي) ← سطح داكن */
    html[data-theme="dark"] [style*="background: #fff"],
    html[data-theme="dark"] [style*="background:#fff"],
    html[data-theme="dark"] [style*="background: white"],
    html[data-theme="dark"] [style*="background:white"],
    html[data-theme="dark"] [style*="background-color: #fff"],
    html[data-theme="dark"] [style*="background-color:#fff"],
    html[data-theme="dark"] [style*="background-color: white"],
    html[data-theme="dark"] [style*="background-color:white"],
    html[data-theme="dark"] [style*="background: #f8fafc"],
    html[data-theme="dark"] [style*="background:#f8fafc"],
    html[data-theme="dark"] [style*="background: #f9fafb"],
    html[data-theme="dark"] [style*="background:#f9fafb"],
    html[data-theme="dark"] [style*="background: #fafafa"],
    html[data-theme="dark"] [style*="background:#fafafa"],
    html[data-theme="dark"] [style*="background: #fafbfc"],
    html[data-theme="dark"] [style*="background:#fafbfc"],
    html[data-theme="dark"] [style*="background: #f8f9fa"],
    html[data-theme="dark"] [style*="background:#f8f9fa"],
    html[data-theme="dark"] [style*="background: #f7fafc"],
    html[data-theme="dark"] [style*="background:#f7fafc"],
    html[data-theme="dark"] [style*="background: #f1f5f9"],
    html[data-theme="dark"] [style*="background:#f1f5f9"],
    html[data-theme="dark"] [style*="background: #f3f4f6"],
    html[data-theme="dark"] [style*="background:#f3f4f6"],
    html[data-theme="dark"] [style*="background: #edf2f7"],
    html[data-theme="dark"] [style*="background:#edf2f7"],
    html[data-theme="dark"] [style*="background: #e2e8f0"],
    html[data-theme="dark"] [style*="background:#e2e8f0"],
    html[data-theme="dark"] [style*="background: #e5e7eb"],
    html[data-theme="dark"] [style*="background:#e5e7eb"] {
        background-color: var(--w-card, #1e293b) !important;
        background-image: none !important;
        border-color: var(--w-border, rgba(255,255,255,0.1)) !important;
    }

    /* (2) الخلفيات الباستيل الملوّنة (شارات/تنبيهات) ← سطح داكن مرتفع خفيف */
    html[data-theme="dark"] [style*="background: #fee2e2"],
    html[data-theme="dark"] [style*="background:#fee2e2"],
    html[data-theme="dark"] [style*="background: #fef2f2"],
    html[data-theme="dark"] [style*="background:#fef2f2"],
    html[data-theme="dark"] [style*="background: #fecaca"],
    html[data-theme="dark"] [style*="background:#fecaca"],
    html[data-theme="dark"] [style*="background: #fee"],
    html[data-theme="dark"] [style*="background:#fee"],
    html[data-theme="dark"] [style*="background: #fff5f5"],
    html[data-theme="dark"] [style*="background:#fff5f5"],
    html[data-theme="dark"] [style*="background: #dcfce7"],
    html[data-theme="dark"] [style*="background:#dcfce7"],
    html[data-theme="dark"] [style*="background: #d1fae5"],
    html[data-theme="dark"] [style*="background:#d1fae5"],
    html[data-theme="dark"] [style*="background: #ecfdf5"],
    html[data-theme="dark"] [style*="background:#ecfdf5"],
    html[data-theme="dark"] [style*="background: #e8f9f2"],
    html[data-theme="dark"] [style*="background:#e8f9f2"],
    html[data-theme="dark"] [style*="background: #d4edda"],
    html[data-theme="dark"] [style*="background:#d4edda"],
    html[data-theme="dark"] [style*="background: #f0fdf4"],
    html[data-theme="dark"] [style*="background:#f0fdf4"],
    html[data-theme="dark"] [style*="background: #f0fff4"],
    html[data-theme="dark"] [style*="background:#f0fff4"],
    html[data-theme="dark"] [style*="background: #fef3c7"],
    html[data-theme="dark"] [style*="background:#fef3c7"],
    html[data-theme="dark"] [style*="background: #fffbeb"],
    html[data-theme="dark"] [style*="background:#fffbeb"],
    html[data-theme="dark"] [style*="background: #fff3cd"],
    html[data-theme="dark"] [style*="background:#fff3cd"],
    html[data-theme="dark"] [style*="background: #fffaf0"],
    html[data-theme="dark"] [style*="background:#fffaf0"],
    html[data-theme="dark"] [style*="background: #fff7ed"],
    html[data-theme="dark"] [style*="background:#fff7ed"],
    html[data-theme="dark"] [style*="background: #dbeafe"],
    html[data-theme="dark"] [style*="background:#dbeafe"],
    html[data-theme="dark"] [style*="background: #eff6ff"],
    html[data-theme="dark"] [style*="background:#eff6ff"],
    html[data-theme="dark"] [style*="background: #f0f9ff"],
    html[data-theme="dark"] [style*="background:#f0f9ff"],
    html[data-theme="dark"] [style*="background: #ebf4ff"],
    html[data-theme="dark"] [style*="background:#ebf4ff"],
    html[data-theme="dark"] [style*="background: #eef2ff"],
    html[data-theme="dark"] [style*="background:#eef2ff"],
    html[data-theme="dark"] [style*="background: #e0e7ff"],
    html[data-theme="dark"] [style*="background:#e0e7ff"],
    html[data-theme="dark"] [style*="background: #f5f3ff"],
    html[data-theme="dark"] [style*="background:#f5f3ff"],
    html[data-theme="dark"] [style*="background: #faf5ff"],
    html[data-theme="dark"] [style*="background:#faf5ff"],
    html[data-theme="dark"] [style*="background: #ede9fe"],
    html[data-theme="dark"] [style*="background:#ede9fe"],
    html[data-theme="dark"] [style*="background: #fce7f3"],
    html[data-theme="dark"] [style*="background:#fce7f3"],
    html[data-theme="dark"] [style*="background: #fdf4ff"],
    html[data-theme="dark"] [style*="background:#fdf4ff"] {
        background-color: rgba(148, 163, 184, 0.12) !important;
        background-image: none !important;
        border-color: var(--w-border, rgba(255,255,255,0.1)) !important;
    }

    /* (2b) التدرّجات الباهتة (أسطح) ← سطح داكن (نتجاوز التدرّجات الزاهية كـ #f59e0b/#ef4444) */
    html[data-theme="dark"] [style*="linear-gradient(135deg, #f7fafc"],
    html[data-theme="dark"] [style*="linear-gradient(135deg,#f7fafc"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #f8fafc"],
    html[data-theme="dark"] [style*="linear-gradient(135deg,#f8fafc"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #f9fafb"],
    html[data-theme="dark"] [style*="linear-gradient(135deg,#f9fafb"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #ffffff"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #e2e8f0"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #edf2f7"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #fef3c7"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #fff7ed"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #fefce8"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #ffeaa7"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #ffecd2"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #f0f9ff"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #eff6ff"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #e3f2fd"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #ebf8ff"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #e0e7ff"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #eef2ff"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #ede9fe"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #f5f3ff"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #faf5ff"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #fdf4ff"],
    html[data-theme="dark"] [style*="linear-gradient(135deg,#fdf4ff"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #f0fdf4"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #ecfdf5"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #f0fdfa"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #e8f5e9"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #fce4ec"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #fff5f5"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #fecaca"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #fef5e7"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #ffe4e1"],
    html[data-theme="dark"] [style*="linear-gradient(135deg, #dbeafe"] {
        background-image: none !important;
        background-color: var(--w-card, #1e293b) !important;
        border-color: var(--w-border, rgba(255,255,255,0.1)) !important;
    }

    /* (3) النصوص المحايدة الداكنة ← فاتح */
    html[data-theme="dark"] [style*="color: #0f172a"],
    html[data-theme="dark"] [style*="color:#0f172a"],
    html[data-theme="dark"] [style*="color: #111827"],
    html[data-theme="dark"] [style*="color:#111827"],
    html[data-theme="dark"] [style*="color: #1e293b"],
    html[data-theme="dark"] [style*="color:#1e293b"],
    html[data-theme="dark"] [style*="color: #1f2937"],
    html[data-theme="dark"] [style*="color:#1f2937"],
    html[data-theme="dark"] [style*="color: #1a202c"],
    html[data-theme="dark"] [style*="color:#1a202c"],
    html[data-theme="dark"] [style*="color: #2d3748"],
    html[data-theme="dark"] [style*="color:#2d3748"],
    html[data-theme="dark"] [style*="color: #2d3436"],
    html[data-theme="dark"] [style*="color:#2d3436"],
    html[data-theme="dark"] [style*="color: #334155"],
    html[data-theme="dark"] [style*="color:#334155"],
    html[data-theme="dark"] [style*="color: #374151"],
    html[data-theme="dark"] [style*="color:#374151"] {
        color: var(--w-text, #f1f5f9) !important;
    }
    html[data-theme="dark"] [style*="color: #475569"],
    html[data-theme="dark"] [style*="color:#475569"],
    html[data-theme="dark"] [style*="color: #4a5568"],
    html[data-theme="dark"] [style*="color:#4a5568"],
    html[data-theme="dark"] [style*="color: #4b5563"],
    html[data-theme="dark"] [style*="color:#4b5563"],
    html[data-theme="dark"] [style*="color: #64748b"],
    html[data-theme="dark"] [style*="color:#64748b"],
    html[data-theme="dark"] [style*="color: #6b7280"],
    html[data-theme="dark"] [style*="color:#6b7280"],
    html[data-theme="dark"] [style*="color: #718096"],
    html[data-theme="dark"] [style*="color:#718096"] {
        color: var(--w-text-muted, #94a3b8) !important;
    }

    /* (4) النصوص الملوّنة الداكنة ← نسخة فاتحة من الدرجة نفسها */
    html[data-theme="dark"] [style*="color: #dc2626"],
    html[data-theme="dark"] [style*="color:#dc2626"],
    html[data-theme="dark"] [style*="color: #991b1b"],
    html[data-theme="dark"] [style*="color:#991b1b"],
    html[data-theme="dark"] [style*="color: #b91c1c"],
    html[data-theme="dark"] [style*="color:#b91c1c"],
    html[data-theme="dark"] [style*="color: #7f1d1d"],
    html[data-theme="dark"] [style*="color:#7f1d1d"],
    html[data-theme="dark"] [style*="color: #c53030"],
    html[data-theme="dark"] [style*="color:#c53030"] {
        color: #fca5a5 !important;
    }
    html[data-theme="dark"] [style*="color: #92400e"],
    html[data-theme="dark"] [style*="color:#92400e"],
    html[data-theme="dark"] [style*="color: #b45309"],
    html[data-theme="dark"] [style*="color:#b45309"],
    html[data-theme="dark"] [style*="color: #d97706"],
    html[data-theme="dark"] [style*="color:#d97706"],
    html[data-theme="dark"] [style*="color: #854d0e"],
    html[data-theme="dark"] [style*="color:#854d0e"],
    html[data-theme="dark"] [style*="color: #9a3412"],
    html[data-theme="dark"] [style*="color:#9a3412"],
    html[data-theme="dark"] [style*="color: #78350f"],
    html[data-theme="dark"] [style*="color:#78350f"] {
        color: #fcd34d !important;
    }
    html[data-theme="dark"] [style*="color: #166534"],
    html[data-theme="dark"] [style*="color:#166534"],
    html[data-theme="dark"] [style*="color: #14532d"],
    html[data-theme="dark"] [style*="color:#14532d"],
    html[data-theme="dark"] [style*="color: #065f46"],
    html[data-theme="dark"] [style*="color:#065f46"],
    html[data-theme="dark"] [style*="color: #15803d"],
    html[data-theme="dark"] [style*="color:#15803d"],
    html[data-theme="dark"] [style*="color: #16a34a"],
    html[data-theme="dark"] [style*="color:#16a34a"],
    html[data-theme="dark"] [style*="color: #047857"],
    html[data-theme="dark"] [style*="color:#047857"],
    html[data-theme="dark"] [style*="color: #059669"],
    html[data-theme="dark"] [style*="color:#059669"] {
        color: #6ee7b7 !important;
    }
    html[data-theme="dark"] [style*="color: #9d174d"],
    html[data-theme="dark"] [style*="color:#9d174d"] {
        color: #f9a8d4 !important;
    }
    html[data-theme="dark"] [style*="color: #1e40af"],
    html[data-theme="dark"] [style*="color:#1e40af"],
    html[data-theme="dark"] [style*="color: #2563eb"],
    html[data-theme="dark"] [style*="color:#2563eb"] {
        color: #93c5fd !important;
    }
    html[data-theme="dark"] [style*="color: #4338ca"],
    html[data-theme="dark"] [style*="color:#4338ca"],
    html[data-theme="dark"] [style*="color: #4f46e5"],
    html[data-theme="dark"] [style*="color:#4f46e5"],
    html[data-theme="dark"] [style*="color: #5b21b6"],
    html[data-theme="dark"] [style*="color:#5b21b6"],
    html[data-theme="dark"] [style*="color: #6b21a8"],
    html[data-theme="dark"] [style*="color:#6b21a8"],
    html[data-theme="dark"] [style*="color: #7c3aed"],
    html[data-theme="dark"] [style*="color:#7c3aed"],
    html[data-theme="dark"] [style*="color: #5a67d8"],
    html[data-theme="dark"] [style*="color:#5a67d8"],
    html[data-theme="dark"] [style*="color: #667eea"],
    html[data-theme="dark"] [style*="color:#667eea"] {
        color: #c4b5fd !important;
    }

    /* (5) أدوات Tailwind الباهتة: نُعتّم الخلفية ونُفتّح النص لتجنّب فاتح-على-فاتح */
    html[data-theme="dark"] .bg-gray-50,
    html[data-theme="dark"] .bg-gray-100,
    html[data-theme="dark"] .bg-gray-200,
    html[data-theme="dark"] .bg-slate-50,
    html[data-theme="dark"] .bg-slate-100,
    html[data-theme="dark"] .bg-blue-50,
    html[data-theme="dark"] .bg-sky-50,
    html[data-theme="dark"] .bg-indigo-50,
    html[data-theme="dark"] .bg-green-50,
    html[data-theme="dark"] .bg-emerald-50,
    html[data-theme="dark"] .bg-teal-50,
    html[data-theme="dark"] .bg-purple-50,
    html[data-theme="dark"] .bg-violet-50,
    html[data-theme="dark"] .bg-pink-50,
    html[data-theme="dark"] .bg-rose-50,
    html[data-theme="dark"] .bg-orange-50,
    html[data-theme="dark"] .bg-amber-50,
    html[data-theme="dark"] .bg-yellow-50,
    html[data-theme="dark"] .bg-red-50,
    html[data-theme="dark"] [class*="from-gray-50"],
    html[data-theme="dark"] [class*="from-slate-50"],
    html[data-theme="dark"] [class*="from-blue-50"],
    html[data-theme="dark"] [class*="from-purple-50"],
    html[data-theme="dark"] [class*="from-indigo-50"],
    html[data-theme="dark"] [class*="from-green-50"],
    html[data-theme="dark"] [class*="to-blue-50"],
    html[data-theme="dark"] [class*="to-purple-50"],
    html[data-theme="dark"] [class*="to-indigo-50"] {
        background-color: rgba(148, 163, 184, 0.12) !important;
        background-image: none !important;
        border-color: var(--w-border, rgba(255,255,255,0.1)) !important;
    }
    html[data-theme="dark"] .text-gray-900,
    html[data-theme="dark"] .text-gray-800,
    html[data-theme="dark"] .text-slate-900,
    html[data-theme="dark"] .text-slate-800 {
        color: var(--w-text, #f1f5f9) !important;
    }
    html[data-theme="dark"] .text-gray-700,
    html[data-theme="dark"] .text-gray-600,
    html[data-theme="dark"] .text-gray-500,
    html[data-theme="dark"] .text-slate-700,
    html[data-theme="dark"] .text-slate-600 {
        color: var(--w-text-muted, #94a3b8) !important;
    }

    /* (6) تفتيح عام لعناوين وتسميات البطاقات المُعرّفة بكلاس (لا يلتقطها مطابق inline) —
       العناوين/التسميات نصوص محايدة دائماً فتفتيحها آمن. */
    html[data-theme="dark"] [class*="-card"] :where(h1,h2,h3,h4,h5,h6,label),
    html[data-theme="dark"] [class*="-tile"] :where(h1,h2,h3,h4,h5,h6,label),
    html[data-theme="dark"] [class*="-item"] :where(h1,h2,h3,h4,h5,h6,label),
    html[data-theme="dark"] [class*="-box"] :where(h1,h2,h3,h4,h5,h6,label),
    html[data-theme="dark"] [class*="-section"] :where(h1,h2,h3,h4,h5,h6,label),
    html[data-theme="dark"] [class*="-table"] :where(h1,h2,h3,h4,h5,h6,label),
    html[data-theme="dark"] [class*="-list"] :where(h1,h2,h3,h4,h5,h6,label),
    html[data-theme="dark"] .modal-box :where(h1,h2,h3,h4,h5,h6,label),
    html[data-theme="dark"] .modal-content :where(h1,h2,h3,h4,h5,h6,label) {
        color: var(--w-text, #f1f5f9) !important;
    }

    /* (6b) حالات مؤكّدة مُعرّفة بكلاس (أسماء/قيَم كنصوص div لا عناوين) على بطاقات مُعتَّمة */
    html[data-theme="dark"] .meaning-name,
    html[data-theme="dark"] .ml-user-name,
    html[data-theme="dark"] .av-title,
    html[data-theme="dark"] .value-name,
    html[data-theme="dark"] .section-head-title,
    html[data-theme="dark"] .stat-value,
    html[data-theme="dark"] .stat-number,
    html[data-theme="dark"] .admin-stat-value {
        color: var(--w-text, #f1f5f9) !important;
    }
    html[data-theme="dark"] .cd-rank-value,
    html[data-theme="dark"] .cd-value-score,
    html[data-theme="dark"] .cd-card-title i {
        color: #a5b4fc !important;
    }

    /* (6c) أسطح فاتحة مُعرّفة بكلاس (جزر نهارية عالية الأثر) ← تُعتَّم */
    html[data-theme="dark"] .question-card,
    html[data-theme="dark"] .value-card-current,
    html[data-theme="dark"] .empty-state,
    html[data-theme="dark"] .ml-stat,
    html[data-theme="dark"] .av-hint,
    html[data-theme="dark"] .values-section,
    html[data-theme="dark"] .quick-links,
    html[data-theme="dark"] .info-chip,
    html[data-theme="dark"] .text-answer-preview {
        background-color: var(--w-card, #1e293b) !important;
        background-image: none !important;
        border-color: var(--w-border, rgba(255,255,255,0.1)) !important;
    }
    html[data-theme="dark"] .empty-state :where(h1,h2,h3,h4,h5,h6,p,span),
    html[data-theme="dark"] .question-card :where(strong,p,span,li),
    html[data-theme="dark"] .info-chip {
        color: var(--w-text, #f1f5f9) !important;
    }

    /* (7) استثناءات إلزامية */
    html[data-theme="dark"] .qr-code-wrapper,
    html[data-theme="dark"] [class*="qr-code"],
    html[data-theme="dark"] [class*="qr-code"][style] {
        background-color: #ffffff !important;
        background-image: none !important;
    }
    html[data-theme="dark"] img[style*="background"],
    html[data-theme="dark"] svg[style*="background"] {
        background-color: transparent !important;
    }
</style>

<script>
    // Chart.js dark-mode: تسميات المحاور/الأسطورة وخطوط الشبكة الافتراضية (#666 / أسود شفّاف) تختفي على السطح الداكن.
    // نعترض إسناد window.Chart لنضبط الافتراضيات *قبل* إنشاء أي مخطّط (المخططات تُنشأ في سكربتات نهاية الصفحة)،
    // ونعيد ضبطها + نُحدّث المخططات القائمة عند تبديل الثيم.
    (function () {
        function palette() {
            var dark = document.documentElement.getAttribute('data-theme') === 'dark';
            return {
                text: dark ? '#cbd5e1' : '#666666',
                grid: dark ? 'rgba(255,255,255,0.10)' : 'rgba(0,0,0,0.1)',
            };
        }
        function applyDefaults(C) {
            if (!C || !C.defaults) return;
            var p = palette();
            C.defaults.color = p.text;
            C.defaults.borderColor = p.grid;
            try {
                if (C.defaults.scale && C.defaults.scale.grid) C.defaults.scale.grid.color = p.grid;
                if (C.defaults.scales) {
                    ['x', 'y', 'r', 'linear', 'category', 'radialLinear'].forEach(function (k) {
                        if (C.defaults.scales[k]) {
                            if (C.defaults.scales[k].grid) C.defaults.scales[k].grid.color = p.grid;
                            if (C.defaults.scales[k].ticks) C.defaults.scales[k].ticks.color = p.text;
                        }
                    });
                }
            } catch (e) {}
        }
        var _c = window.Chart;
        if (_c) applyDefaults(_c);
        try {
            Object.defineProperty(window, 'Chart', {
                configurable: true,
                get: function () { return _c; },
                set: function (v) { _c = v; applyDefaults(v); },
            });
        } catch (e) {
            var n = 0, id = setInterval(function () {
                if (window.Chart) { applyDefaults(window.Chart); clearInterval(id); }
                if (++n > 100) clearInterval(id);
            }, 50);
        }
        document.addEventListener('wahy:themechange', function () {
            if (!window.Chart) return;
            applyDefaults(window.Chart);
            var inst = window.Chart.instances || {};
            Object.keys(inst).forEach(function (k) { try { inst[k].update(); } catch (e) {} });
        });
    })();
</script>
