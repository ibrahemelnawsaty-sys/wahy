@extends('emails.layouts.base')
@section('title', '📥 تسليم بانتظار مراجعتك')
@section('content')
    <p>مرحبًا {{ $teacher->name }} 👋</p>
    <p>سلّم الطالب <strong>{{ $student->name }}</strong> النشاط «{{ $activityTitle }}» وهو بانتظار مراجعتك وتقييمك.</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ $url }}">مراجعة التسليم</a></div>
@endsection
