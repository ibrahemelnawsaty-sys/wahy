// Theme Toggle System - Dark/Light Mode
// Handles theme persistence and toggle animation
'use strict';

document.addEventListener('DOMContentLoaded', function () {
    const html = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');

    // Get saved theme or default to dark (المفتاح الموحّد wahy-theme)
    const savedTheme = localStorage.getItem('wahy-theme') || 'dark';
    html.setAttribute('data-theme', savedTheme);

    // Theme toggle button click event
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('wahy-theme', newTheme);

            // Smooth rotation animation
            this.style.transform = `rotate(${currentTheme === 'dark' ? '180deg' : '0deg'})`;

            // Announce theme change for accessibility
            if (window.qiyammAnnouncer) {
                window.qiyammAnnouncer(newTheme === 'dark' ? 'الوضع الليلي' : 'الوضع النهاري');
            }
        });
    }
});
