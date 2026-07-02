{{-- براويز الأفاتار المتحركة (Wow!) — حلقة متدرّجة دوّارة + توهّج نابض. تُطبَّق بإضافة الصنفين:
     class="... wahy-frame wahy-frame-{anim}" على حاوية الأفاتار. تُضمَّن مرّة واحدة عبر @once. --}}
@once
<style>
    .wahy-frame {
        position: relative;
        overflow: visible !important;
        box-shadow: none !important;
        isolation: isolate;
    }
    .wahy-frame > img,
    .wahy-frame > span { position: relative; z-index: 2; }

    /* الحلقة المتدرّجة الدوّارة (تُقصّ لتظهر كإطار فقط عبر mask) */
    .wahy-frame::before {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        z-index: 1;
        background: conic-gradient(from 0deg, var(--wf-c1), var(--wf-c2), var(--wf-c3), var(--wf-c2), var(--wf-c1));
        -webkit-mask: radial-gradient(farthest-side, #0000 calc(100% - 6px), #000 calc(100% - 6px));
                mask: radial-gradient(farthest-side, #0000 calc(100% - 6px), #000 calc(100% - 6px));
        animation: wahyFrameSpin var(--wf-speed, 3s) linear infinite;
        will-change: transform;
    }
    /* توهّج نابض حول الأفاتار */
    .wahy-frame::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        z-index: 0;
        box-shadow: 0 0 16px 3px var(--wf-glow), 0 0 6px 1px var(--wf-glow);
        animation: wahyFramePulse 1.8s ease-in-out infinite;
    }

    @keyframes wahyFrameSpin { to { transform: rotate(1turn); } }
    @keyframes wahyFramePulse { 0%, 100% { opacity: .5; } 50% { opacity: 1; } }

    /* الأنماط الأربعة */
    .wahy-frame-gold  { --wf-c1:#FFD700; --wf-c2:#FF8C00; --wf-c3:#FFF3B0; --wf-glow:rgba(255,193,7,.85);  --wf-speed:3s; }
    .wahy-frame-neon  { --wf-c1:#22d3ee; --wf-c2:#3b82f6; --wf-c3:#a5f3fc; --wf-glow:rgba(34,211,238,.9);  --wf-speed:2s; }
    .wahy-frame-royal { --wf-c1:#a855f7; --wf-c2:#f59e0b; --wf-c3:#e9d5ff; --wf-glow:rgba(168,85,247,.9);  --wf-speed:4s; }
    .wahy-frame-fire  { --wf-c1:#ef4444; --wf-c2:#f59e0b; --wf-c3:#fde68a; --wf-glow:rgba(239,68,68,.9);   --wf-speed:2.4s; }

    /* نجمة صغيرة تدور مع الإطار لجذب أكبر (زخرفية) */
    .wahy-frame-royal::before,
    .wahy-frame-gold::before { filter: drop-shadow(0 0 3px var(--wf-glow)); }

    /* احترام تفضيل تقليل الحركة على مستوى النظام: نُبقي الإطار ثابتاً بدل إيقافه كلياً */
    @media (prefers-reduced-motion: reduce) {
        .wahy-frame::before { animation: none; }
        .wahy-frame::after { animation: none; opacity: .85; }
    }
</style>
@endonce
