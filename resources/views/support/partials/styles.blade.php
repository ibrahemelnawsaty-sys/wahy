{{-- أصناف مساعدة خاصّة بواجهات الدعم (support-*) — مشتركة بين لايوت الدعم ولايوت الأدمن
     حتى تُعرَض صفحات التذاكر بنفس التنسيق سواء فتحها موظّف الدعم أو السوبر أدمن.
     تعتمد على --color-* و --w-* المتوفّرين في كِلا اللايوتين (theme-toggle). --}}
<style>
    .support-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 700; line-height: 1.6;
        white-space: nowrap;
    }
    .support-badge.warning { background: #fef3c7; color: #b45309; }
    .support-badge.info    { background: #dbeafe; color: #1d4ed8; }
    .support-badge.success { background: #dcfce7; color: #15803d; }
    .support-badge.secondary { background: #e2e8f0; color: #475569; }
    .support-badge.danger  { background: #fee2e2; color: #b91c1c; }
    .support-badge.escalate{ background: #fae8ff; color: #a21caf; }

    html[data-theme="dark"] .support-badge.warning { background: rgba(245,158,11,.18); color: #fcd34d; }
    html[data-theme="dark"] .support-badge.info    { background: rgba(59,130,246,.18); color: #93c5fd; }
    html[data-theme="dark"] .support-badge.success { background: rgba(34,197,94,.18); color: #86efac; }
    html[data-theme="dark"] .support-badge.secondary { background: rgba(148,163,184,.18); color: #cbd5e1; }
    html[data-theme="dark"] .support-badge.danger  { background: rgba(239,68,68,.18); color: #fca5a5; }
    html[data-theme="dark"] .support-badge.escalate{ background: rgba(168,85,247,.18); color: #e9d5ff; }

    .support-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(15,23,42,.05);
    }
    html[data-theme="dark"] .support-card {
        background: var(--w-card) !important;
        border-color: var(--w-border) !important;
        color: var(--w-text) !important;
    }

    .support-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 16px; border-radius: 10px;
        font-size: 13px; font-weight: 700; cursor: pointer;
        border: none; text-decoration: none; transition: all .2s;
        font-family: inherit;
    }
    .support-btn:hover { transform: translateY(-1px); }
    .support-btn-primary { background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); color: #fff; }
    .support-btn-success { background: #16a34a; color: #fff; }
    .support-btn-warning { background: #f59e0b; color: #fff; }
    .support-btn-secondary { background: #64748b; color: #fff; }
    .support-btn-escalate { background: linear-gradient(135deg, #a21caf, #7e22ce); color: #fff; }
    .support-btn-ghost { background: #f1f5f9; color: #334155; }
    html[data-theme="dark"] .support-btn-ghost { background: rgba(255,255,255,.08); color: var(--w-text); }

    .support-table { width: 100%; border-collapse: collapse; }
    .support-table th {
        background: #f8fafc; padding: 14px 16px; text-align: right;
        font-weight: 700; color: #475569; font-size: 13px;
        border-bottom: 2px solid #e2e8f0; white-space: nowrap;
    }
    .support-table td { padding: 14px 16px; border-bottom: 1px solid #eef2f7; vertical-align: middle; }
    .support-table tbody tr:hover { background: #f8fafc; }
    html[data-theme="dark"] .support-table th { background: rgba(255,255,255,.04); color: var(--w-text-muted); border-color: var(--w-border); }
    html[data-theme="dark"] .support-table td { border-color: var(--w-border); color: var(--w-text); }
    html[data-theme="dark"] .support-table tbody tr:hover { background: rgba(255,255,255,.03); }
</style>
