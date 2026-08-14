@extends('emails.layouts.base')
@section('title', '🎫 تذكرة دعم جديدة')
@section('content')
    <p>مرحبًا {{ $recipient->name }} 👋</p>
    <p>فُتِحت تذكرة دعم جديدة تحتاج مراجعتكم:</p>
    <div class="panel">
        <div><strong>رقم التذكرة:</strong> #{{ $ticketId }}</div>
        <div><strong>الموضوع:</strong> {{ $subjectLine }}</div>
        <div><strong>مقدّمها:</strong> {{ $requester }}</div>
    </div>
    <div class="btn-wrap"><a class="email-btn" href="{{ $url }}">فتح التذكرة</a></div>
@endsection
