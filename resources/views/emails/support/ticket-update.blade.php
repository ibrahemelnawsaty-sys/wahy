@extends('emails.layouts.base')
@section('title', '💬 تحديث على تذكرتك')
@section('content')
    <p>مرحبًا {{ $recipient->name }} 👋</p>
    <p>{{ $headline }} (تذكرة #{{ $ticketId }} — «{{ $subjectLine }}»).</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ $url }}">عرض التذكرة</a></div>
@endsection
