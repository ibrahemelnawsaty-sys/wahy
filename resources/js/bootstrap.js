/**
 * @file bootstrap.js
 * @description يُهيّئ axios + CSRF + interceptors عامة لكل الـ AJAX requests.
 *              يُحمَّل عبر app.js مرة واحدة في كل صفحة.
 */

import axios from 'axios';

/** @type {import('axios').AxiosInstance} */
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF token (Laravel يضع meta tag في layouts/*)
const tokenMeta = document.head.querySelector('meta[name="csrf-token"]');
if (tokenMeta) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenMeta.content;
}

// JSON Accept افتراضي
window.axios.defaults.headers.common['Accept'] = 'application/json';

/**
 * Response interceptor: يعالج 401 و 419 (CSRF expired) موحّداً.
 */
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (!error.response) {
            return Promise.reject(error);
        }

        const status = error.response.status;

        // CSRF token expired — أعد التحميل لجلب جديد
        if (status === 419) {
            console.warn('[axios] CSRF token expired — reloading page');
            window.location.reload();
            return Promise.reject(error);
        }

        // 401 — حوّل إلى login (إن لم يكن مستخدم في صفحته)
        if (status === 401 && !window.location.pathname.includes('/login')) {
            window.location.href = '/login';
        }

        return Promise.reject(error);
    }
);
