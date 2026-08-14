@extends('emails.layouts.base')
@section('title', '✅ تم تفعيل حساب ابنك')
@section('content')
    <p>مرحبًا {{ $parent->name }} 👋</p>
    <p>تم تفعيل حساب ابنك/ابنتك <strong>{{ $student->name }}</strong> بنجاح في {{ setting('site_name', 'أثيل مكة') }}.</p>
    <p>يمكنك الآن متابعة رحلته التعليميّة وإنجازاته أوّلًا بأوّل.</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ url('/parent/dashboard') }}">متابعة ابنك</a></div>
@endsection
