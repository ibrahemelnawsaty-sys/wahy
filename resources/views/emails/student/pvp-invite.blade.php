@extends('emails.layouts.base')
@section('title', '⚔️ ' . g('تحدٍّ جديد بانتظارك!', 'تحدٍّ جديد بانتظاركِ!', $opponent))
@section('content')
    <p>مرحبًا {{ $opponent->name }} 👋</p>
    <p>{{ g('تحدّاك', 'تحدّاكِ', $opponent) }} <strong>{{ $challenger->name }}</strong> في مبارزة «{{ $challengeTitle }}».</p>
    <p>{{ g('هل أنت مستعدّ؟ اقبل التحدّي وأثبت جدارتك!', 'هل أنتِ مستعدّة؟ اقبلي التحدّي وأثبتي جدارتكِ!', $opponent) }} 🔥</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ $url }}">دخول ساحة التحدّي</a></div>
@endsection
