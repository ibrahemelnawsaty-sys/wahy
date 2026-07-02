/**
 * Wahy Live Updates — محرّك تحديث لحظي موحّد (Polling) لكل الأدوار.
 * يستطلع endpoint واحداً (افتراضياً /live/summary) يعيد عدّادات { counts: { key: number } }،
 * ويحدّث كل عنصر موسوم data-live="KEY" فوراً دون تحديث الصفحة (نص العدّاد + إظهار/إخفاء عند 0).
 * يتوقف عند إخفاء التبويب (توفير مورد) ويعيد التشغيل عند العودة. يطلق حدث wahy:live لكل استجابة
 * كي تعيد الصفحات تحميل قوائمها إن رغبت. لا يعتمد على WebSockets (يناسب الاستضافة المشتركة).
 */
(function () {
    'use strict';

    var ENDPOINT = (window.WAHY_LIVE && window.WAHY_LIVE.endpoint) || '/live/summary';
    var DELAY = (window.WAHY_LIVE && window.WAHY_LIVE.interval) || 10000; // 10s افتراضياً
    var timer = null;
    var firstRun = true;

    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function applyCount(key, value) {
        var n = parseInt(value, 10);
        if (isNaN(n)) return;
        var els = document.querySelectorAll('[data-live="' + key + '"]');
        els.forEach(function (el) {
            var cap = el.getAttribute('data-live-cap');
            var text = (cap && n > parseInt(cap, 10)) ? (cap + '+') : String(n);
            // إظهار/إخفاء الشارة عند 0 (الشارات تختفي عند لا وجود جديد)
            var isBadge = el.hasAttribute('data-live-badge')
                || el.classList.contains('nav-badge')
                || el.classList.contains('badge-notification')
                || el.classList.contains('notification-badge');
            if (isBadge) {
                el.style.display = n > 0 ? '' : 'none';
            }
            if (el.textContent !== text) el.textContent = text;
            // نبضة لطيفة قصيرة عند زيادة العدد (وصول جديد) — ليست حركة دائمة
            var prev = el.dataset.liveVal;
            if (!firstRun && prev !== undefined && n > (parseInt(prev, 10) || 0)) {
                el.classList.remove('live-bump');
                void el.offsetWidth; // إعادة تشغيل الأنيميشن
                el.classList.add('live-bump');
            }
            el.dataset.liveVal = String(n);
        });
    }

    async function poll() {
        if (document.hidden) return;
        try {
            var res = await fetch(ENDPOINT, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf()
                },
                credentials: 'same-origin'
            });
            if (!res.ok) return;
            var data = await res.json();
            var counts = data.counts || {};
            Object.keys(counts).forEach(function (k) { applyCount(k, counts[k]); });
            // حدث عام: الصفحات تستمع إليه لإعادة تحميل قوائمها عند تغيّر التوقيع (data.signatures)
            document.dispatchEvent(new CustomEvent('wahy:live', { detail: data }));
            firstRun = false;
        } catch (e) { /* صامت — لا نُزعج المستخدم بأخطاء الشبكة العابرة */ }
    }

    function start() {
        if (timer) return;
        poll();
        timer = setInterval(poll, DELAY);
    }
    function stop() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    // إيقاف/استئناف حسب رؤية التبويب
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) stop(); else { start(); poll(); }
    });
    window.addEventListener('pagehide', stop);

    // تحديث فوري عند عودة التركيز للنافذة
    window.addEventListener('focus', function () { if (!document.hidden) poll(); });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }

    // كشف واجهة صغيرة للاستخدام اليدوي عند الحاجة
    window.WahyLive = { poll: poll, start: start, stop: stop };
})();
