{{-- متغيّرات الثيم الموحّدة من الإعدادات — تُضمَّن في <head> لكل لايوت لا يقرأ الثيم
     (parent/school-admin/super-admin/app) حتى ينعكس تغيير الأدمن للألوان/الخط فيها (الجذر 3) --}}
@php
    $tvPrimary   = $branding['primary_color'] ?? setting('primary_color', '#667eea');
    $tvSecondary = $branding['secondary_color'] ?? setting('secondary_color', '#764ba2');
    $tvText      = $branding['text_color'] ?? setting('text_color', '#1e293b');
    $tvBg        = $branding['background_color'] ?? setting('background_color', '#ffffff');
    $tvFont      = $branding['font_family'] ?? setting('font_family', 'IBM Plex Sans Arabic');
@endphp
<style>
    :root {
        --color-primary: {{ $tvPrimary }};
        --color-secondary: {{ $tvSecondary }};
        --color-text: {{ $tvText }};
        --color-bg: {{ $tvBg }};
        --color-primary-rgb: {{ hexToRgb($tvPrimary) }};
        --color-secondary-rgb: {{ hexToRgb($tvSecondary) }};
        --font-family: '{{ $tvFont }}', system-ui, -apple-system, sans-serif;
    }
</style>
