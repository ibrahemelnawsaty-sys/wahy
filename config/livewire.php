<?php

/**
 * Livewire Configuration.
 *
 * تثبيت:
 *   composer require livewire/livewire:^3.5
 *   php artisan livewire:publish --config
 *
 * كيف يدمج مع منصة قيمّ:
 *   - يستخدم Blade templates الحالية (لا تغيير في الـ views الأخرى)
 *   - يعتمد على نفس Tailwind و Alpine.js
 *   - يتم تركيب مكون واحد كمثال:
 *       `app/Livewire/Student/QuickStats.php` + `resources/views/livewire/student/quick-stats.blade.php`
 *
 *   ثم في أي Blade:
 *       <livewire:student.quick-stats :user-id="$user->id" />
 */
return [

    /*
     * Component namespace — حيث Livewire يبحث عن الـ classes
     */
    'class_namespace' => 'App\\Livewire',

    /*
     * Views path
     */
    'view_path' => resource_path('views/livewire'),

    /*
     * Layout الافتراضي للـ full-page components
     */
    'layout' => 'layouts.app',

    /*
     * Temporary file uploads — استخدم نفس الـ storage Laravel
     */
    'temporary_file_upload' => [
        'disk' => 'local',
        'rules' => null,
        'directory' => 'livewire-tmp',
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
        'cleanup' => true,
    ],

    /*
     * Render hooks — يُعطّل ميزات معينة في الإنتاج
     */
    'render_on_redirect' => false,

    /*
     * Smart route registration — Laravel-vite ميزة
     */
    'legacy_model_binding' => false,
    'inject_assets' => true,
    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#2299dd',
    ],

    /*
     * Lazy loading — مهم للأداء
     */
    'pagination_theme' => 'tailwind',
];
