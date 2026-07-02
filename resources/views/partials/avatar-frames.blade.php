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

    /* الأنماط الأربعة (لون + سرعة + رمز الجُسيمات المنبعثة) */
    .wahy-frame-gold  { --wf-c1:#FFD700; --wf-c2:#FF8C00; --wf-c3:#FFF3B0; --wf-glow:rgba(255,193,7,.85);  --wf-speed:3s;   --wf-emoji:'✨'; }
    .wahy-frame-neon  { --wf-c1:#22d3ee; --wf-c2:#3b82f6; --wf-c3:#a5f3fc; --wf-glow:rgba(34,211,238,.9);  --wf-speed:2s;   --wf-emoji:'⚡'; }
    .wahy-frame-royal { --wf-c1:#a855f7; --wf-c2:#f59e0b; --wf-c3:#e9d5ff; --wf-glow:rgba(168,85,247,.9);  --wf-speed:4s;   --wf-emoji:'⭐'; }
    .wahy-frame-fire  { --wf-c1:#ef4444; --wf-c2:#f59e0b; --wf-c3:#fde68a; --wf-glow:rgba(239,68,68,.9);   --wf-speed:2.4s; --wf-emoji:'🔥'; }

    .wahy-frame-royal::before,
    .wahy-frame-gold::before { filter: drop-shadow(0 0 3px var(--wf-glow)); }

    /* ===== الجُسيمات المنبعثة للخارج (نار/شرر/طاقة) — إحساس الفخامة ===== */
    .wf-particles { position: absolute; inset: 0; z-index: 3; pointer-events: none; }
    .wf-particles i {
        position: absolute; top: 50%; left: 50%; margin: -7px 0 0 -7px;
        font-size: 13px; line-height: 1; opacity: 0;
        animation: wfEmit var(--wf-emit, 1.7s) ease-out infinite;
        will-change: transform, opacity;
    }
    .wf-particles i::before { content: var(--wf-emoji, '✨'); }
    /* توزيع الاتجاهات + تأخير متدرّج لانبعاث مستمر */
    .wf-particles i:nth-child(1) { --a: 0deg;   animation-delay: 0s;    }
    .wf-particles i:nth-child(2) { --a: 60deg;  animation-delay: .28s;  }
    .wf-particles i:nth-child(3) { --a: 120deg; animation-delay: .56s;  }
    .wf-particles i:nth-child(4) { --a: 180deg; animation-delay: .85s;  }
    .wf-particles i:nth-child(5) { --a: 240deg; animation-delay: 1.13s; }
    .wf-particles i:nth-child(6) { --a: 300deg; animation-delay: 1.4s;  }

    @keyframes wfEmit {
        0%   { opacity: 0; transform: rotate(var(--a)) translateY(-6px)  scale(.4); }
        18%  { opacity: 1; }
        70%  { opacity: 1; }
        100% { opacity: 0; transform: rotate(var(--a)) translateY(-42px) scale(1.15); }
    }
    /* النار تميل للأعلى أكثر (تصعد) وتومض */
    .wahy-frame-fire .wf-particles i { filter: drop-shadow(0 0 4px rgba(239,68,68,.9)); }

    /* احترام تفضيل تقليل الحركة على مستوى النظام: إطار ثابت بلا جُسيمات */
    @media (prefers-reduced-motion: reduce) {
        .wahy-frame::before { animation: none; }
        .wahy-frame::after { animation: none; opacity: .85; }
        .wf-particles { display: none; }
    }
</style>
@endonce
