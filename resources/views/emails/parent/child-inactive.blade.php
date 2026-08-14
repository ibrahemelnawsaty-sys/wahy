@extends('emails.layouts.base')
@section('title', '👀 اطمئنان على ابنك')
@section('content')
    <p>مرحبًا {{ $parent->name }} 👋</p>
    <p>لاحظنا أنّ ابنك/ابنتك <strong>{{ $student->name }}</strong> لم يدخل منصّة {{ setting('site_name', 'أثيل مكة') }} منذ <strong>{{ $days }}</strong> أيّام.</p>
    <p>تشجيعٌ بسيط منك قد يعيده لرحلته في بناء القيم وإكمال أنشطته 🌱</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ url('/parent/dashboard') }}">متابعة ابنك</a></div>
@endsection
