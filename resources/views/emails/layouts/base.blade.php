{{--
    قالب البريد الموحّد الآمن (خطّة البريد P2). مبنيّ بجداول + ألوان صلبة + خطوط نظام،
    بلا خطوط خارجيّة/backdrop-filter/background-clip (تنهار في عملاء البريد).
    الاستخدام: @extends('emails.layouts.base') ثمّ @section('title') و@section('content').
    متغيّرات اختياريّة تُمرَّر من الـMailable: $previewText، $unsubscribeUrl.
--}}
@php
    $siteName = setting('site_name', 'أثيل مكة');
    $primary = setting('primary_color', '#3CCB8A');
    $siteLogo = setting('site_logo');
    $contactEmail = setting('contact_email', 'info@atheel-makkah.com');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <title>@yield('title', $siteName)</title>
    <style>
        body { margin:0; padding:0; background:#f1f5f9; }
        table { border-collapse:collapse; }
        img { border:0; outline:none; text-decoration:none; max-width:100%; height:auto; }
        a { color: {{ $primary }}; }
        .wrap { width:100%; background:#f1f5f9; padding:24px 12px; }
        .card { width:100%; max-width:600px; margin:0 auto; background:#ffffff; border-radius:16px;
            overflow:hidden; font-family:'Segoe UI',Tahoma,Arial,'Helvetica Neue',sans-serif;
            color:#1f2937; box-shadow:0 6px 24px rgba(0,0,0,0.08); }
        .hd { background: {{ $primary }}; padding:24px 28px; text-align:center; }
        .hd .brand { color:#ffffff; font-size:22px; font-weight:800; text-decoration:none; }
        .hd .logo { max-height:44px; }
        .bd { padding:30px 28px; line-height:1.9; font-size:15.5px; }
        .bd h1, .title { font-size:24px; font-weight:800; color:#111827; margin:0 0 18px; text-align:center; }
        .bd p { margin:0 0 14px; }
        .email-btn { display:inline-block; background: {{ $primary }}; color:#ffffff !important; text-decoration:none;
            font-weight:700; font-size:16px; padding:13px 34px; border-radius:10px; }
        .btn-wrap { text-align:center; margin:26px 0; }
        .panel { background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px; padding:16px 18px; margin:18px 0; }
        .muted { color:#6b7280; font-size:13px; }
        .ft { padding:22px 28px; text-align:center; background:#f8fafc; border-top:1px solid #e5e7eb; }
        .ft a { color:#6b7280; text-decoration:none; margin:0 8px; font-size:13px; }
        .ft .cr { color:#9ca3af; font-size:12px; margin-top:10px; display:block; }
    </style>
</head>
<body>
    @isset($previewText)
        <div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $previewText }}</div>
    @endisset
    <div class="wrap">
        <table class="card" role="presentation" width="600" align="center">
            <tr><td class="hd">
                @if($siteLogo)
                    <a href="{{ url('/') }}"><img class="logo" src="{{ asset('storage/data/' . $siteLogo) }}" alt="{{ $siteName }}"></a>
                @else
                    <a href="{{ url('/') }}" class="brand">🌟 {{ $siteName }}</a>
                @endif
            </td></tr>
            <tr><td class="bd">
                @hasSection('title')
                    <div class="title">@yield('title')</div>
                @endif
                @yield('content')
            </td></tr>
            <tr><td class="ft">
                <div>
                    <a href="{{ url('/') }}">الرئيسية</a>
                    <a href="mailto:{{ $contactEmail }}">الدعم الفنّي</a>
                    @isset($unsubscribeUrl)
                        <a href="{{ $unsubscribeUrl }}">إلغاء الاشتراك</a>
                    @endisset
                </div>
                <span class="cr">© {{ date('Y') }} {{ $siteName }}. جميع الحقوق محفوظة.</span>
            </td></tr>
        </table>
    </div>
</body>
</html>
