{{-- تفاعل احتفاليّ/حزين عند نتيجة الإجابة — ذاتيّ الاحتواء بالكامل (canvas + Web Audio API، بلا CDN ولا ملفّات صوت).
     الاستعمال: @include('partials.answer-celebration') مرّة واحدة في الصفحة، ثم نادِ:
       celebrateCorrect()  ← ألعاب نارية + وجه فرِح 😄 + صوت سرور
       celebrateWrong()    ← وجه حزين 😢 + صوت خسارة
     ملاحظة: تشغيل الصوت يتطلّب تفاعل المستخدم (سياسة المتصفّح)؛ الصوت أفضل-جهد والمرئيّات مضمونة. --}}
<style>
    /* ===== تفاعل احتفالي/حزين للإجابة ===== */
    @keyframes wahyJoyPop {
        0%   { transform: translate(-50%,-50%) scale(.2) rotate(-12deg); opacity: 0; }
        35%  { transform: translate(-50%,-50%) scale(1.25) rotate(8deg);  opacity: 1; }
        70%  { transform: translate(-50%,-50%) scale(1) rotate(-4deg);    opacity: 1; }
        100% { transform: translate(-50%,-58%) scale(1) rotate(0);        opacity: 0; }
    }
    .wahy-joy-pop { animation: wahyJoyPop 1.5s cubic-bezier(.34,1.56,.64,1) forwards; }
    @keyframes wahySadShake {
        0%   { transform: translate(-50%,-50%) scale(.6); opacity: 0; }
        15%  { transform: translate(-50%,-50%) scale(1.1); opacity: 1; }
        25%,45%,65% { transform: translate(-58%,-50%) rotate(-8deg); opacity: 1; }
        35%,55%,75% { transform: translate(-42%,-50%) rotate(8deg);  opacity: 1; }
        85%  { transform: translate(-50%,-50%) rotate(0); opacity: 1; }
        100% { transform: translate(-50%,-50%) scale(.9); opacity: 0; }
    }
    .wahy-sad-shake { animation: wahySadShake 1.5s ease-in-out forwards; }
    @media (prefers-reduced-motion: reduce) { .wahy-joy-pop, .wahy-sad-shake { animation-duration: .01ms; } }
</style>
<script>
// ================= احتفال/حزن مولّد ذاتياً (بلا ملفات خارجية) — يُعرّف مرّة واحدة =================
(function () {
    if (window.__wahyCelebReady) return;
    window.__wahyCelebReady = true;

    var _wahyAC = null;
    function _wahyAudioCtx() {
        try {
            if (!_wahyAC) _wahyAC = new (window.AudioContext || window.webkitAudioContext)();
            if (_wahyAC.state === 'suspended') _wahyAC.resume();
            return _wahyAC;
        } catch (e) { return null; }
    }
    function wahyHappyChime() {
        const ac = _wahyAudioCtx(); if (!ac) return; const t0 = ac.currentTime;
        [523.25, 659.25, 783.99, 1046.50].forEach((f, i) => { // C5 E5 G5 C6 صاعد
            const o = ac.createOscillator(), g = ac.createGain(); o.type = 'triangle'; o.frequency.value = f;
            const t = t0 + i * 0.12;
            g.gain.setValueAtTime(0.0001, t); g.gain.exponentialRampToValueAtTime(0.22, t + 0.03);
            g.gain.exponentialRampToValueAtTime(0.0001, t + 0.28);
            o.connect(g).connect(ac.destination); o.start(t); o.stop(t + 0.3);
        });
    }
    function wahySadTone() {
        const ac = _wahyAudioCtx(); if (!ac) return; const t0 = ac.currentTime;
        const o = ac.createOscillator(), g = ac.createGain(), lp = ac.createBiquadFilter();
        o.type = 'sawtooth'; lp.type = 'lowpass'; lp.frequency.value = 900;
        o.frequency.setValueAtTime(311.13, t0);                     // Eb4
        o.frequency.exponentialRampToValueAtTime(233.08, t0 + 0.28); // Bb3
        o.frequency.exponentialRampToValueAtTime(155.56, t0 + 0.72); // Eb3 (انزلاق هابط حزين)
        g.gain.setValueAtTime(0.0001, t0); g.gain.exponentialRampToValueAtTime(0.18, t0 + 0.05);
        g.gain.exponentialRampToValueAtTime(0.0001, t0 + 0.85);
        o.connect(lp).connect(g).connect(ac.destination); o.start(t0); o.stop(t0 + 0.9);
    }
    function wahyLaunchConfetti() {
        if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const cv = document.createElement('canvas');
        cv.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:100000;';
        document.body.appendChild(cv);
        const ctx = cv.getContext('2d'), dpr = window.devicePixelRatio || 1;
        function resize() { cv.width = innerWidth * dpr; cv.height = innerHeight * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0); }
        resize(); addEventListener('resize', resize, { once: true });
        const colors = ['#FFD700', '#10B981', '#6366F1', '#EC4899', '#F59E0B', '#FCD34D', '#34D399', '#818CF8'];
        const P = [];
        for (let i = 0; i < 140; i++) P.push({ x: Math.random() * innerWidth, y: -20 - Math.random() * innerHeight * 0.4,
            vx: (Math.random() - 0.5) * 3, vy: 2 + Math.random() * 4, size: 6 + Math.random() * 8,
            color: colors[(Math.random() * colors.length) | 0], rot: Math.random() * Math.PI, vr: (Math.random() - 0.5) * 0.3 });
        [[innerWidth * 0.3, innerHeight * 0.4], [innerWidth * 0.7, innerHeight * 0.35]].forEach(([ox, oy]) => {
            for (let i = 0; i < 60; i++) { const a = (Math.PI * 2 * i) / 60, sp = 3 + Math.random() * 4;
                P.push({ x: ox, y: oy, vx: Math.cos(a) * sp, vy: Math.sin(a) * sp, size: 4 + Math.random() * 4,
                    color: colors[(Math.random() * colors.length) | 0], rot: 0, vr: 0, spark: true }); }
        });
        const start = performance.now();
        (function frame(now) { const t = now - start; ctx.clearRect(0, 0, innerWidth, innerHeight);
            P.forEach(p => { p.vy += p.spark ? 0.06 : 0.12; p.x += p.vx; p.y += p.vy; p.rot += p.vr;
                ctx.save(); ctx.globalAlpha = Math.max(0, 1 - t / 2600); ctx.translate(p.x, p.y); ctx.rotate(p.rot); ctx.fillStyle = p.color;
                if (p.spark) { ctx.beginPath(); ctx.arc(0, 0, p.size / 2, 0, Math.PI * 2); ctx.fill(); }
                else ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.6); ctx.restore(); });
            if (t < 2600) requestAnimationFrame(frame); else cv.remove();
        })(start);
    }
    function wahyEmojiBurst(emoji, cls) {
        const el = document.createElement('div'); el.textContent = emoji; el.className = cls;
        el.style.cssText = 'position:fixed;top:38%;left:50%;font-size:96px;z-index:100001;pointer-events:none;';
        document.body.appendChild(el); setTimeout(() => el.remove(), 1600);
    }

    window.celebrateCorrect = function () { wahyLaunchConfetti(); wahyEmojiBurst('😄', 'wahy-joy-pop'); wahyHappyChime(); };
    window.celebrateWrong = function () { wahyEmojiBurst('😢', 'wahy-sad-shake'); wahySadTone(); };
})();
</script>
