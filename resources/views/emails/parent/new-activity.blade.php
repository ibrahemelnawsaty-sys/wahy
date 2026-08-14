@extends('emails.layouts.base')
@section('title', '✨ نشاط جديد لابنك')
@section('content')
    <p>مرحبًا {{ $parent->name }} 👋</p>
    <p>أُتيح لابنك/ابنتك <strong>{{ $student->name }}</strong> نشاط جديد: «{{ $activityTitle }}».</p>
    <p>تشجيعك له على إنجازه يصنع فرقًا 🌱</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ url('/parent/dashboard') }}">متابعة ابنك</a></div>
@endsection
