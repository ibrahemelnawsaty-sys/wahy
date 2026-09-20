@extends('emails.layouts.base')
@section('title', '✨ نشاط جديد بانتظارك')
@section('content')
    <p>مرحبًا {{ $student->name }} 👋</p>
    <p>{{ g('أصبح لديك نشاط جديد:', 'أصبح لديكِ نشاط جديد:', $student) }} <strong>{{ $activityTitle }}</strong> في {{ setting('site_name', 'أثيل مكة') }}.</p>
    <p>ابدأ الآن واكسب نقاطًا جديدة! 🎯</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ url('/student/dashboard') }}">ابدأ النشاط</a></div>
@endsection
