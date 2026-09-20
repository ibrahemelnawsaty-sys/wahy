@extends('emails.layouts.base')
@section('title', '🌟 ' . g('عملك متميّز!', 'عملكِ متميّز!', $student))
@section('content')
    <p>مرحبًا {{ $student->name }} 👋</p>
    <p>{{ g('ميّز معلّمك تسليمك', 'ميّز معلّمكِ تسليمكِ', $student) }} في «{{ $activityTitle }}» واختاره ضمن الأعمال المتميّزة. {{ g('أحسنت!', 'أحسنتِ!', $student) }} 🎉</p>
    @if($points > 0)
        <p>{{ g('وكافأك بـ', 'وكافأكِ بـ', $student) }} <strong>{{ $points }} نقطة إضافيّة</strong> {{ g('أُضيفت إلى رصيدك.', 'أُضيفت إلى رصيدكِ.', $student) }}</p>
    @endif
    @if(filled($reason))
        <p style="border-inline-start:4px solid #10b981;padding-inline-start:12px;color:#475569;">
            «{{ $reason }}»
        </p>
    @endif
    <div class="btn-wrap"><a class="email-btn" href="{{ $url }}">عرض إنجازاتي</a></div>
@endsection
