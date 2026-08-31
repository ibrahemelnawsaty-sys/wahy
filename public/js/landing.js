// قيمّ - Landing Page JavaScript
// Modern, smooth, professional interactions

'use strict';

// ==================== DOM Ready ====================
document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initSmoothScroll();
    initActiveNav();
    initHeaderScroll();
    initCurrentYear();
    initAccessibility();
    initContactForm();
});

// ==================== Mobile Menu ====================
function initMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    const links = navLinks?.querySelectorAll('.nav-link');

    if (!menuToggle || !navLinks) return;

    // Toggle menu
    menuToggle.addEventListener('click', () => {
        const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
        menuToggle.setAttribute('aria-expanded', !isExpanded);
        navLinks.classList.toggle('active');
        
        // Prevent body scroll when menu is open
        document.body.style.overflow = isExpanded ? '' : 'hidden';
    });

    // Close menu when clicking on a link
    links?.forEach(link => {
        link.addEventListener('click', () => {
            menuToggle.setAttribute('aria-expanded', 'false');
            navLinks.classList.remove('active');
            document.body.style.overflow = '';
        });
    });

    // Close menu on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && navLinks.classList.contains('active')) {
            menuToggle.setAttribute('aria-expanded', 'false');
            navLinks.classList.remove('active');
            document.body.style.overflow = '';
            menuToggle.focus();
        }
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!menuToggle.contains(e.target) && !navLinks.contains(e.target) && navLinks.classList.contains('active')) {
            menuToggle.setAttribute('aria-expanded', 'false');
            navLinks.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // شبكة أمان: لا تدع قفل التمرير يعلق — أعد الضبط عند تكبير/تدوير الشاشة للحاسوب
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768 && navLinks.classList.contains('active')) {
            menuToggle.setAttribute('aria-expanded', 'false');
            navLinks.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}

// ==================== Smooth Scroll ====================
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            
            // Skip empty anchors
            if (href === '#' || href === '#0') return;

            const target = document.querySelector(href);
            if (!target) return;

            e.preventDefault();

            // Calculate offset for fixed header
            const headerHeight = document.querySelector('.header')?.offsetHeight || 0;
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });

            // Update URL without scrolling
            history.pushState(null, '', href);

            // Focus target for accessibility
            target.setAttribute('tabindex', '-1');
            target.focus({ preventScroll: true });
            target.removeAttribute('tabindex');
        });
    });
}

// ==================== Active Navigation ====================
function initActiveNav() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');

    if (sections.length === 0 || navLinks.length === 0) return;

    const observerOptions = {
        root: null,
        rootMargin: '-20% 0px -70% 0px',
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${id}`) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }, observerOptions);

    sections.forEach(section => observer.observe(section));
}

// ==================== Header Scroll Effect ====================
function initHeaderScroll() {
    const header = document.querySelector('.header');
    if (!header) return;

    let ticking = false;

    const updateHeader = () => {
        const scrollY = window.pageYOffset;
        header.classList.toggle('scrolled', scrollY > 100);
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(updateHeader);
            ticking = true;
        }
    });
}

// ==================== Current Year ====================
function initCurrentYear() {
    const yearElement = document.getElementById('current-year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }
}

// ==================== Accessibility Enhancements ====================
function initAccessibility() {
    // Add keyboard navigation for cards
    const interactiveCards = document.querySelectorAll('.feature-card, .step-card, .partner-logo');

    interactiveCards.forEach(card => {
        card.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                card.click();
            }
        });
    });

    // Announce dynamic content changes to screen readers
    const announcer = document.createElement('div');
    announcer.setAttribute('role', 'status');
    announcer.setAttribute('aria-live', 'polite');
    announcer.setAttribute('aria-atomic', 'true');
    announcer.className = 'sr-only';
    announcer.style.cssText = 'position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden;';
    document.body.appendChild(announcer);

    // Store announcer globally for potential use
    window.qiyammAnnouncer = (message) => {
        announcer.textContent = message;
        setTimeout(() => announcer.textContent = '', 1000);
    };

    // Enhanced focus visible
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-nav');
        }
    });

    document.addEventListener('mousedown', () => {
        document.body.classList.remove('keyboard-nav');
    });
}

// Add accessibility styles
const style = document.createElement('style');
style.textContent = `
    .sr-only {
        position: absolute;
        left: -10000px;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }

    body.keyboard-nav *:focus-visible {
        outline: 2px solid #10B981;
        outline-offset: 2px;
        border-radius: 8px;
    }
`;
document.head.appendChild(style);

// ==================== Contact Form Handling ====================
function initContactForm() {
    const form = document.getElementById('contactForm');
    if (!form) return;

    // تدفّق إثبات ملكيّة البريد: زرّ «إرسال الرمز» بجانب البريد (مستقلّ عن زرّ إرسال الرسالة)
    // يُرسل رمزاً، ثمّ يُدخِله المستخدم في الصندوق أسفل البريد، ثمّ يُرسل الرسالة بالزرّ الرئيسيّ.
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    const formMessage = document.getElementById('formMessage');

    const emailInput = document.getElementById('email');
    const sendCodeBtn = document.getElementById('sendCodeBtn');
    const scbText = sendCodeBtn ? sendCodeBtn.querySelector('.scb-text') : null;
    const scbLoader = sendCodeBtn ? sendCodeBtn.querySelector('.scb-loader') : null;
    const codeGroup = document.getElementById('contactCodeGroup');
    const codeInput = document.getElementById('contactCode');
    const codeStatus = document.getElementById('codeStatus');
    const codeHint = document.getElementById('codeHint');
    const resendBtn = document.getElementById('contactResend');

    let codeSent = false;     // هل أُرسل رمزٌ للبريد الحاليّ؟
    let sentToEmail = '';     // البريد الذي أُرسل إليه الرمز (لكشف تغييره)
    let resendTimer = null;   // مؤقّت العدّ التنازليّ لإعادة الإرسال
    let sending = false;      // قفل «طلبٌ جارٍ» يمنع إرسال رمزين متزامنين (أساسيّ + إعادة)

    // form.reset() يُعيد cc_token إلى قيمته الافتراضيّة الفارغة، والسكربت المضمَّن يملؤه مرّة واحدة
    // عند التحميل فقط — فنعيد ملأه من ccGate بعد أيّ reset كي تنجح رسالةٌ ثانيةٌ من الصفحة نفسها.
    function refillCcToken() {
        const g = document.getElementById('ccGate');
        const f = document.getElementById('cc_token');
        if (g && f) f.value = g.getAttribute('data-g') || '';
    }

    function showMsg(txt, ok) {
        formMessage.textContent = txt;
        formMessage.className = 'form-message ' + (ok ? 'success' : 'error');
        // إعلان لقارئ الشاشة: النجاح مهذّب (polite)، والخطأ يقاطع (assertive) — WCAG 4.1.3.
        formMessage.setAttribute('role', ok ? 'status' : 'alert');
        formMessage.setAttribute('aria-live', ok ? 'polite' : 'assertive');
        formMessage.style.display = 'block';
        // على الجوّال قد تكون الرسالة أسفل الطيّة — نجلبها للعرض إن لزم (block:'nearest' لا يُزعج لو ظاهرة).
        try { formMessage.scrollIntoView({ block: 'nearest' }); } catch (e) { /* noop */ }
    }
    function setStatus(txt, ok) {
        if (!codeStatus) return;
        codeStatus.textContent = txt || '';
        codeStatus.className = 'code-status ' + (txt ? (ok ? 'ok' : 'err') : '');
    }
    function setHint(txt) { if (codeHint) codeHint.textContent = txt || ''; }

    function clearResendTimer() {
        if (resendTimer) { clearInterval(resendTimer); resendTimer = null; }
    }
    function startResendCountdown(seconds) {
        clearResendTimer();
        let remaining = seconds;
        if (resendBtn) resendBtn.disabled = true;
        setHint('يمكنك إعادة الإرسال خلال ' + remaining + ' ثانية');
        resendTimer = setInterval(() => {
            remaining -= 1;
            if (remaining <= 0) {
                clearResendTimer();
                setHint('');
                if (resendBtn) resendBtn.disabled = false;
            } else {
                setHint('يمكنك إعادة الإرسال خلال ' + remaining + ' ثانية');
            }
        }, 1000);
    }

    // تغيير البريد بعد إرسال الرمز يُبطل التحقّق (الرمز مرتبط بالبريد تحديداً).
    function resetVerification() {
        codeSent = false;
        sentToEmail = '';
        clearResendTimer();
        if (codeGroup) codeGroup.style.display = 'none';
        if (codeInput) {
            codeInput.value = '';
            codeInput.removeAttribute('aria-invalid');
        }
        setStatus('', true);
        setHint('');
        // ملاحظة: لا نُخفي #formMessage هنا — فهذه الدالّة تُستدعى أيضاً بعد النجاح حيث نُريد بقاء
        // لافتة «تم إرسال رسالتك». إخفاء اللافتة البائتة يقع في معالج تغيير البريد فقط (أدناه).
        if (sendCodeBtn) { sendCodeBtn.classList.remove('is-sent'); sendCodeBtn.disabled = false; }
        if (scbText) scbText.textContent = 'إرسال الرمز';
    }

    // الخطوة 1: إرسال رمز التحقّق إلى البريد (يتحقّق من صحّة البريد فقط، لا كامل النموذج).
    async function requestCode() {
        if (sending) return; // قفل: يمنع طلبين متزامنين (نقر مزدوج على الإرسال أو الإعادة)
        if (!emailInput || !emailInput.value.trim() || !emailInput.checkValidity()) {
            if (emailInput) { emailInput.classList.add('error'); emailInput.reportValidity(); emailInput.focus(); }
            return;
        }
        // نلتقط البريد قبل الطلب: الرمز يُبنى على هذه اللقطة، فنربط sentToEmail بها لا بالحقل الحيّ
        // (كي لا يختلّ التطابق لو عُدِّل الحقل أثناء الطلب على شبكةٍ بطيئة).
        const emailAtSend = emailInput.value.trim().toLowerCase();

        sending = true;
        // حالة تحميل الزرّ المجاور للبريد + تعطيل الإعادة أثناء الطلب
        if (sendCodeBtn) sendCodeBtn.disabled = true;
        if (resendBtn) resendBtn.disabled = true;
        if (scbText) scbText.style.display = 'none';
        if (scbLoader) scbLoader.style.display = 'flex';
        formMessage.style.display = 'none';

        let revealed = false;
        try {
            const response = await fetch('/contact/send-code', {
                method: 'POST',
                body: new FormData(form), // يحمل email + cc_token + _token + honeypot تلقائيّاً
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const result = await response.json().catch(() => ({}));
            // نكشف الصندوق فقط عند إرسالٍ حقيقيّ (need_code=true). النجاح الكاذب من guardBots (رمز JS
            // فارغ/منتهٍ) يعيد success=true بلا need_code وبلا بريد — فلا نُوهم المستخدم بأنّ الرمز أُرسل.
            if (response.ok && result.success && result.need_code) {
                revealed = true;
                codeSent = true;
                sentToEmail = emailAtSend;
                if (codeGroup) codeGroup.style.display = 'flex';
                if (codeInput) { codeInput.removeAttribute('aria-invalid'); codeInput.focus(); }
                if (sendCodeBtn) sendCodeBtn.classList.add('is-sent');
                if (scbText) scbText.textContent = 'تم الإرسال ✓';
                setStatus('تمّ إرسال الرمز', true);
                showMsg(result.message || 'أرسلنا رمز تحقّق إلى بريدك. أدخِله لإتمام الإرسال.', true);
                startResendCountdown(60);
            } else if (response.ok && result.success) {
                // نجاحٌ كاذب (غالباً صفحة مفتوحة منذ مدّة طويلة) — أرشِد لتحديث الصفحة بدل انتظارٍ بلا طائل.
                showMsg('يبدو أنّ الصفحة مفتوحة منذ مدّة. يرجى تحديثها ثمّ إعادة إرسال الرمز.', false);
            } else {
                showMsg(result.message || result.error || 'تعذّر إرسال الرمز. حاول لاحقاً.', false);
            }
        } catch (error) {
            showMsg('حدث خطأ في الاتصال. يرجى المحاولة لاحقاً.', false);
        } finally {
            sending = false;
            // إعادة إظهار نصّ الزرّ؛ يبقى معطّلاً عند النجاح (الإعادة عبر الرابط المؤقَّت)، ويُفعَّل عند الفشل.
            if (scbText) scbText.style.display = 'inline';
            if (scbLoader) scbLoader.style.display = 'none';
            if (sendCodeBtn) sendCodeBtn.disabled = revealed;
            // عند الفشل نُعيد تفعيل الإعادة فوراً؛ عند النجاح يتكفّل العدّاد بها.
            if (resendBtn && !revealed && codeSent) resendBtn.disabled = false;
        }
    }

    // الخطوة 2: إرسال الرسالة مع الرمز (الزرّ الرئيسيّ).
    async function submitMessage() {
        if (!form.checkValidity()) { form.reportValidity(); return; }
        if (!codeSent) {
            showMsg('يرجى إرسال رمز التحقّق إلى بريدك أوّلاً (زرّ «إرسال الرمز» بجانب البريد) ثمّ إدخاله.', false);
            if (sendCodeBtn) sendCodeBtn.focus();
            return;
        }
        if (codeInput && codeInput.value.trim().length !== 6) {
            // فحص طولٍ محليّ: يمنع إحراق محاولةٍ من الخادم برسالةٍ عامّة على رمزٍ ناقص.
            showMsg('رمز التحقّق مكوّن من 6 أرقام.', false);
            codeInput.focus();
            return;
        }

        submitBtn.disabled = true;
        if (btnText) btnText.style.display = 'none';
        if (btnLoader) btnLoader.style.display = 'flex';
        formMessage.style.display = 'none';

        try {
            const response = await fetch('/contact', {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const result = await response.json().catch(() => ({}));
            if (response.ok && result.success) {
                showMsg(result.message || 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.', true);
                form.reset();
                refillCcToken(); // reset مسح cc_token — نُعيد ملأه لرسالةٍ ثانيةٍ محتملة
                resetVerification();
            } else {
                // رمز خاطئ/منتهٍ → أبقِ الصندوق ظاهراً ليعيد المحاولة أو يطلب رمزاً جديداً.
                // result.error يغطّي استجابة 419 (انتهاء صلاحية الصفحة) التي لا تحمل مفتاح message.
                showMsg(result.message || result.error || 'الرمز غير صحيح أو انتهت صلاحيته.', false);
                if (result.need_code) {
                    setStatus('رمز غير صحيح', false);
                    if (codeGroup) codeGroup.style.display = 'flex';
                    if (codeInput) {
                        codeInput.setAttribute('aria-invalid', 'true');
                        codeInput.focus();
                        codeInput.select();
                    }
                }
            }
        } catch (error) {
            showMsg('حدث خطأ في الاتصال. يرجى المحاولة لاحقاً.', false);
        } finally {
            submitBtn.disabled = false;
            if (btnText) btnText.style.display = 'inline';
            if (btnLoader) btnLoader.style.display = 'none';
        }
    }

    if (sendCodeBtn) sendCodeBtn.addEventListener('click', requestCode);
    if (resendBtn) resendBtn.addEventListener('click', () => { if (!resendBtn.disabled) requestCode(); });

    form.addEventListener('submit', (e) => { e.preventDefault(); submitMessage(); });

    // تغيير البريد بعد إرسال الرمز يُبطل الحالة.
    if (emailInput) {
        emailInput.addEventListener('input', () => {
            if (codeSent && emailInput.value.trim().toLowerCase() !== sentToEmail) {
                resetVerification();
                // امسح لافتة «أرسلنا رمزاً…» البائتة كي لا تتناقض مع اختفاء الصندوق.
                if (formMessage) formMessage.style.display = 'none';
            }
        });
    }

    // حصر إدخال الرمز في الأرقام (حتّى 6) + إزالة علامة الخطأ عند التعديل.
    if (codeInput) {
        codeInput.addEventListener('input', () => {
            const digits = codeInput.value.replace(/\D/g, '').slice(0, 6);
            if (digits !== codeInput.value) codeInput.value = digits;
            codeInput.removeAttribute('aria-invalid');
        });
    }

    // Real-time validation
    const inputs = form.querySelectorAll('.form-input, .form-select, .form-textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', () => {
            if (input.validity.valid) {
                input.classList.remove('error');
            } else {
                input.classList.add('error');
            }
        });

        input.addEventListener('input', () => {
            if (input.classList.contains('error') && input.validity.valid) {
                input.classList.remove('error');
            }
        });
    });
}

// ==================== Performance Optimizations ====================
// Lazy load images
if ('loading' in HTMLImageElement.prototype) {
    const images = document.querySelectorAll('img[loading="lazy"]');
    images.forEach(img => {
        img.src = img.dataset.src || img.src;
    });
} else {
    // Fallback for browsers that don't support native lazy loading
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
    document.body.appendChild(script);
}

// ==================== Error Handling ====================
window.addEventListener('error', (e) => {
    // Errors are caught silently - could send to logging service
});

// ==================== Export for testing ====================
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initMobileMenu,
        initSmoothScroll,
        initActiveNav,
        initHeaderScroll,
        initCurrentYear,
        initAccessibility,
        initContactForm
    };
}
