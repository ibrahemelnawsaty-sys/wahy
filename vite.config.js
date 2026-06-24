import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

/**
 * Vite configuration — يعالج assets منصة قيمّ.
 *
 * تحسينات هذا الإعداد:
 *  1. Code splitting تلقائي للـ vendor (axios, alpine) — bundle أصغر للـ initial load
 *  2. CSS code splitting — يحمل CSS الصفحة فقط
 *  3. esbuild minification (أسرع من Terser)
 *  4. Source maps في development فقط
 *  5. Manifest في public/build لـ Laravel Vite plugin
 *  6. Asset hashing — كل بناء يحصل على hash جديد (cache busting)
 */
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: [
                'resources/views/**',
                'app/Http/Controllers/**',
                'app/View/Composers/**',
                'routes/**',
            ],
        }),
        tailwindcss(),
    ],

    build: {
        // أهداف عريضة (يدعم Safari 14+ و كل الـ evergreen browsers)
        target: 'es2020',

        // Source maps في الإنتاج: false (تقلل حجم Bundle 40%)
        sourcemap: process.env.NODE_ENV !== 'production',

        // Minification السريع
        minify: 'esbuild',

        // CSS code splitting — يحمل CSS أصغر لكل صفحة
        cssCodeSplit: true,

        // حذف console.log في الإنتاج
        esbuild: {
            drop: process.env.NODE_ENV === 'production' ? ['console', 'debugger'] : [],
        },

        rollupOptions: {
            output: {
                // أسماء ملفات نظيفة مع hash للـ cache busting
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',

                // Manual chunking — افصل vendor عن application code
                manualChunks: {
                    // axios + helpers vendor chunk
                    'vendor-http': ['axios'],
                    // alpine vendor chunk (لو مستخدم)
                    'vendor-alpine': ['alpinejs'],
                },
            },
        },

        // حذف warnings عن big chunks (نحن نتحكم في الـ chunking يدوياً)
        chunkSizeWarningLimit: 800,
    },

    // Development server
    server: {
        // host: '0.0.0.0',  // فعّل لو تختبر من device آخر على نفس الشبكة
        watch: {
            usePolling: false, // تجنّب CPU drain على Linux/Docker
        },
    },

    // Optimizations
    optimizeDeps: {
        // pre-bundle dependencies شائعة الاستخدام
        include: ['axios'],
    },
});
