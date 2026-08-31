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

    // تدفّق من خطوتين لإثبات ملكيّة البريد: (1) إرسال رمز إلى البريد، ثمّ (2) إدخال الرمز والإرسال.
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    const formMessage = document.getElementById('formMessage');
    const codeGroup = document.getElementById('contactCodeGroup');
    const codeInput = document.getElementById('contactCode');
    const resendBtn = document.getElementById('contactResend');

    function setLoading(loading) {
        submitBtn.disabled = loading;
        if (btnText) btnText.style.display = loading ? 'none' : 'inline';
        if (btnLoader) btnLoader.style.display = loading ? 'flex' : 'none';
    }
    function setLabel(txt) { if (btnText) btnText.textContent = txt; }
    function showMsg(txt, ok) {
        formMessage.textContent = txt;
        formMessage.className = 'form-message ' + (ok ? 'success' : 'error');
        formMessage.style.display = 'block';
    }

    // الخطوة 1: طلب رمز التحقّق للبريد المُدخَل.
    async function requestCode() {
        if (!form.checkValidity()) { form.reportValidity(); return; }
        setLoading(true);
        formMessage.style.display = 'none';
        try {
            const response = await fetch('/contact/send-code', {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (response.ok && result.success) {
                if (codeGroup) codeGroup.style.display = 'block';
                if (codeInput) codeInput.focus();
                showMsg(result.message || 'أرسلنا رمز تحقّق إلى بريدك. أدخِله لإتمام الإرسال.', true);
                setLabel('تأكيد وإرسال');
                submitBtn.dataset.step = 'verify';
            } else {
                showMsg(result.message || 'تعذّر إرسال الرمز. حاول لاحقاً.', false);
            }
        } catch (error) {
            showMsg('حدث خطأ في الاتصال. يرجى المحاولة لاحقاً.', false);
        } finally {
            setLoading(false);
        }
    }

    // الخطوة 2: إرسال الرسالة مع الرمز.
    async function submitMessage() {
        if (codeInput && !codeInput.value.trim()) {
            showMsg('أدخِل رمز التحقّق الذي وصلك على بريدك.', false);
            return;
        }
        setLoading(true);
        formMessage.style.display = 'none';
        try {
            const response = await fetch('/contact', {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (response.ok && result.success) {
                showMsg(result.message || 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.', true);
                form.reset();
                if (codeGroup) codeGroup.style.display = 'none';
                setLabel('إرسال رمز التحقّق');
                submitBtn.dataset.step = 'send';
            } else {
                // رمز خاطئ/منتهٍ → أبقِ حقل الرمز ظاهراً ليعيد المحاولة أو يطلب رمزاً جديداً.
                showMsg(result.message || 'الرمز غير صحيح أو انتهت صلاحيته.', false);
                if (result.need_code && codeGroup) codeGroup.style.display = 'block';
            }
        } catch (error) {
            showMsg('حدث خطأ في الاتصال. يرجى المحاولة لاحقاً.', false);
        } finally {
            setLoading(false);
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (submitBtn.dataset.step === 'verify') {
            await submitMessage();
        } else {
            await requestCode();
        }
    });

    if (resendBtn) {
        resendBtn.addEventListener('click', () => { requestCode(); });
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
