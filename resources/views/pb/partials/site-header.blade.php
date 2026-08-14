{{-- هيدر الموقع الموحّد للصفحات الثانوية — نفس مفاتيح lc/الإعدادات المُحرَّرة من «محتوى الصفحة الرئيسية». --}}
@php
    $__sn = setting('site_name', 'أثيل مكة');
    $__sl = setting('site_logo');
@endphp
<header class="pb-site-header">
    <div class="pb-site-header-inner">
        <a href="{{ url('/') }}" class="pb-site-brand">
            @if($__sl)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($__sl) }}" alt="{{ $__sn }}" class="pb-site-logo">
            @else
                <span class="pb-site-logo-icon">{{ lc('logo_icon', '🌟') }}</span>
            @endif
            <span class="pb-site-name">{{ $__sn }}</span>
        </a>
        <nav class="pb-site-nav">
            <a href="{{ url('/') }}">{{ lc('nav_link_1', 'الرئيسية') }}</a>
            <a href="{{ url('/#features') }}">{{ lc('nav_link_2', 'المميزات') }}</a>
            <a href="{{ url('/#values') }}">{{ lc('nav_link_3', 'القيم') }}</a>
            <a href="{{ url('/#support') }}">{{ lc('nav_link_6', 'الدعم') }}</a>
        </nav>
        <div class="pb-site-actions">
            <a href="{{ url('/login') }}" class="pb-btn pb-btn-ghost">{{ lc('login_btn_text', 'تسجيل دخول') }}</a>
            <a href="{{ url('/register') }}" class="pb-btn pb-btn-primary">{{ lc('register_btn_text', 'ابدأ الآن') }}</a>
        </div>
    </div>
</header>
