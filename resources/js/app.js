/**
 * @file app.js
 * @description نقطة الدخول الرئيسية للـ frontend.
 *              يُحمَّل في كل الصفحات عبر Vite في layouts/*.blade.php:
 *              {{ Vite::asset('resources/js/app.js') }}
 *
 *              يستورد:
 *              - bootstrap.js   → axios + CSRF + interceptors
 *              - (lazy) celebration → تُحمّل عند الحاجة فقط
 *
 * @author Wahy Platform
 */

import './bootstrap';

/**
 * Lazy-load celebration module — يقلل حجم الـ bundle الأساسي.
 *
 * @returns {Promise<typeof import('./celebration.js')>}
 *
 * @example
 *   // في Blade view:
 *   document.addEventListener('activity:completed', async () => {
 *     const { showCelebration } = await window.loadCelebration();
 *     showCelebration('activity_complete');
 *   });
 */
window.loadCelebration = () => import('./celebration.js');
