// Intersection Observer for Advanced Lazy Loading
document.addEventListener('DOMContentLoaded', () => {
    // Lazy load images
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                
                // Load WebP if supported, fallback to original
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
                
                if (img.dataset.srcset) {
                    img.srcset = img.dataset.srcset;
                }
                
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px 0px',
        threshold: 0.01
    });

    // Observe all lazy images
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });

    // Lazy load background images
    const bgObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const bgImage = element.dataset.bg;
                
                if (bgImage) {
                    element.style.backgroundImage = `url(${bgImage})`;
                    element.classList.add('bg-loaded');
                    observer.unobserve(element);
                }
            }
        });
    }, {
        rootMargin: '100px 0px'
    });

    document.querySelectorAll('[data-bg]').forEach(el => {
        bgObserver.observe(el);
    });

    // Preload critical resources on idle
    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => {
            preloadCriticalResources();
        });
    } else {
        setTimeout(preloadCriticalResources, 1);
    }
});

function preloadCriticalResources() {
    const links = [
        { href: '/css/landing.min.css', as: 'style' },
        { href: '/js/landing.min.js', as: 'script' },
        { href: '/icons.svg', as: 'image' }
    ];

    links.forEach(link => {
        if (!document.querySelector(`link[href="${link.href}"]`)) {
            const preloadLink = document.createElement('link');
            preloadLink.rel = 'preload';
            preloadLink.href = link.href;
            preloadLink.as = link.as;
            document.head.appendChild(preloadLink);
        }
    });
}

// Performance monitoring
if ('PerformanceObserver' in window) {
    // Monitor Largest Contentful Paint
    const lcpObserver = new PerformanceObserver((entryList) => {
        const entries = entryList.getEntries();
        const lastEntry = entries[entries.length - 1];
        console.log('LCP:', lastEntry.renderTime || lastEntry.loadTime);
    });
    lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });

    // Monitor First Input Delay
    const fidObserver = new PerformanceObserver((entryList) => {
        const entries = entryList.getEntries();
        entries.forEach(entry => {
            console.log('FID:', entry.processingStart - entry.startTime);
        });
    });
    fidObserver.observe({ entryTypes: ['first-input'] });

    // Monitor Cumulative Layout Shift
    let clsValue = 0;
    const clsObserver = new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
            if (!entry.hadRecentInput) {
                clsValue += entry.value;
                console.log('CLS:', clsValue);
            }
        }
    });
    clsObserver.observe({ entryTypes: ['layout-shift'] });
}
