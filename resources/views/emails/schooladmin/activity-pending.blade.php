@extends('emails.layouts.base')
@section('title', '📝 نشاط بانتظار اعتمادك')
@section('content')
    <p>مرحبًا {{ $recipient->name }} 👋</p>
    <p>أرسل المعلّم <strong>{{ $teacher->name }}</strong> النشاط «{{ $activityTitle }}» بانتظار اعتمادك.</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ $approvalUrl }}">مراجعة واعتماد</a></div>
@endsection
