@extends('emails.layouts.base')
@section('title', '↩️ نشاطك يحتاج تعديلًا')
@section('content')
    <p>مرحبًا {{ $teacher->name }} 👋</p>
    <p>لم يُعتمَد نشاطك «{{ $activityTitle }}» بصيغته الحاليّة.</p>
    <div class="panel"><strong>السبب:</strong> {{ $reason }}</div>
    <p>يمكنك تعديله وإعادة إرساله للاعتماد.</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ $url }}">تعديل النشاط</a></div>
@endsection
