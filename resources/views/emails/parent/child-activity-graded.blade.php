@extends('emails.layouts.base')
@section('title', '📝 تقييم جديد لابنك')
@section('content')
    <p>مرحبًا {{ $parent->name }} 👋</p>
    <p>حصل ابنك/ابنتك <strong>{{ $student->name }}</strong> على تقييم في نشاط «{{ $activityTitle }}».</p>
    <div class="panel" style="text-align:center;">
        <div style="font-size:30px;font-weight:800;color:{{ setting('primary_color', '#3CCB8A') }};">{{ $grade }}</div>
        <div class="muted">الدرجة/النقاط</div>
    </div>
    <div class="btn-wrap"><a class="email-btn" href="{{ url('/parent/dashboard') }}">عرض التفاصيل</a></div>
@endsection
