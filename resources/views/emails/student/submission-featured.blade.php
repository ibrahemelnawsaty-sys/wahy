@extends('emails.layouts.base')
@section('title', '🌟 عملك متميّز!')
@section('content')
    <p>مرحبًا {{ $student->name }} 👋</p>
    <p>ميّز معلّمك تسليمك في «{{ $activityTitle }}» واختاره ضمن الأعمال المتميّزة. أحسنت! 🎉</p>
    @if($points > 0)
        <p>وكافأك بـ <strong>{{ $points }} نقطة إضافيّة</strong> أُضيفت إلى رصيدك.</p>
    @endif
    @if(filled($reason))
        <p style="border-inline-start:4px solid #10b981;padding-inline-start:12px;color:#475569;">
            «{{ $reason }}»
        </p>
    @endif
    <div class="btn-wrap"><a class="email-btn" href="{{ $url }}">عرض إنجازاتي</a></div>
@endsection
