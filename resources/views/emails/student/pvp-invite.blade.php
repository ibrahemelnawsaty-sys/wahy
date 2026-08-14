@extends('emails.layouts.base')
@section('title', '⚔️ تحدٍّ جديد بانتظارك!')
@section('content')
    <p>مرحبًا {{ $opponent->name }} 👋</p>
    <p>تحدّاك <strong>{{ $challenger->name }}</strong> في مبارزة «{{ $challengeTitle }}».</p>
    <p>هل أنت مستعدّ؟ اقبل التحدّي وأثبت جدارتك! 🔥</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ $url }}">دخول ساحة التحدّي</a></div>
@endsection
