{{-- فوتر الموقع الموحّد للصفحات الثانوية — نفس مفاتيح lc/الإعدادات المُحرَّرة من «محتوى الصفحة الرئيسية». --}}
@php
    $__sn = setting('site_name', 'أثيل مكة');
    $__sd = setting('site_description', '');
@endphp
<footer class="pb-site-footer">
    <div class="pb-site-footer-inner">
        <div class="pb-site-footer-brand">
            <div class="pb-site-footer-name"><span class="pb-site-logo-icon">{{ lc('footer_logo_icon', '🌟') }}</span> {{ $__sn }}</div>
            @if($__sd)<p class="pb-site-footer-desc">{{ $__sd }}</p>@endif
        </div>
        <nav class="pb-site-footer-links">
            <a href="{{ url('/') }}">{{ lc('footer_link_home', 'الرئيسية') }}</a>
            <a href="{{ url('/#features') }}">{{ lc('footer_link_features', 'المميزات') }}</a>
            <a href="{{ url('/#values') }}">{{ lc('footer_link_values', 'القيم') }}</a>
            <a href="{{ url('/privacy') }}">سياسة الخصوصية</a>
            <a href="{{ url('/terms') }}">الشروط والأحكام</a>
        </nav>
    </div>
    <div class="pb-site-footer-bottom">{{ setting('footer_text') ?: '© ' . date('Y') . ' ' . $__sn . '. جميع الحقوق محفوظة' }}</div>
</footer>
