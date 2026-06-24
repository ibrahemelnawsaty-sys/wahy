{{-- رأس موحّد: CSRF + favicon + meta SEO + theme-color + og — يُضمَّن في <head> كل اللايوتات
     يحلّ: غياب favicon/CSRF/og من اللايوتات، وتجاهُل site_favicon/meta_* (Issues تغطية الإعدادات) --}}
@php
    $b = $branding ?? [];
    $hmName    = $b['site_name'] ?? setting('site_name', 'قيمّ');
    $hmTitle   = $b['meta_title'] ?? ($b['site_name'] ?? setting('meta_title', $hmName));
    $hmDesc    = $b['meta_description'] ?? ($b['site_description'] ?? setting('meta_description', $b['site_tagline'] ?? ''));
    $hmFavicon = $b['site_favicon'] ?? setting('site_favicon');
    $hmPrimary = $b['primary_color'] ?? setting('primary_color', '#667eea');
@endphp
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="{{ $hmPrimary }}">
@if(!empty($hmDesc))
<meta name="description" content="{{ $hmDesc }}">
<meta property="og:description" content="{{ $hmDesc }}">
@endif
<meta property="og:title" content="{{ $hmTitle }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $hmName }}">
@if(!empty($hmFavicon))
<link rel="icon" href="{{ asset('storage/app/public/data/' . $hmFavicon) }}">
@else
<link rel="icon" href="{{ asset('favicon.ico') }}">
@endif
