/*
 * خطّ أصول محرّر الصفحات v2 — منطق ثابت مُدقَّق للكتل التفاعليّة فقط (لا شيء من تأليف المستخدم).
 * يُحمَّل شرطيّاً في مستند الصفحة فقط حين تُستعمل كتلة تحتاجه (BlockRegistry::needsRuntime).
 * يُهيّئ ذاتيّاً عبر data-* بلا تبعيّات.
 */
(function () {
    'use strict';

    function initTabs(root) {
        root.querySelectorAll('[data-pb-tabs]').forEach(function (tabs) {
            if (tabs.__pbTabsBound) return;
            tabs.__pbTabsBound = true;
            tabs.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-pb-tab]');
                if (!btn || !tabs.contains(btn)) return;
                var idx = btn.getAttribute('data-pb-tab');
                tabs.querySelectorAll('[data-pb-tab]').forEach(function (b) {
                    b.classList.toggle('is-active', b === btn);
                });
                tabs.querySelectorAll('[data-pb-panel]').forEach(function (p) {
                    p.classList.toggle('is-active', p.getAttribute('data-pb-panel') === idx);
                });
            });
        });
    }

    function init(root) { initTabs(root || document); }

    if (document.readyState !== 'loading') init(document);
    else document.addEventListener('DOMContentLoaded', function () { init(document); });

    // تُتاح لإعادة التهيئة داخل معاينة المحرّر عند الحاجة.
    window.PBRuntime = { init: init };
})();
