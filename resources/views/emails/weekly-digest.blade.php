@extends('emails.layouts.base')

@section('title', '📊 ملخّص أسبوعك')

@section('content')
    <p>مرحبًا {{ $student->name }} 👋</p>
    <p>هذا ملخّص نشاطك خلال الأسبوع الماضي في {{ setting('site_name', 'أثيل مكة') }}:</p>
    <div class="panel" style="text-align:center;">
        <div style="font-size:34px;font-weight:800;color:{{ setting('primary_color', '#3CCB8A') }};">{{ $points }}</div>
        <div class="muted">نقطة جمعتها هذا الأسبوع 🎯</div>
    </div>
    <p>{{ g('واصل تقدّمك الرائع — رحلتك في بناء القيم تزداد قوّةً كل أسبوع!', 'واصلي تقدّمكِ الرائع — رحلتكِ في بناء القيم تزداد قوّةً كل أسبوع!', $student) }}</p>
    <div class="btn-wrap">
        <a class="email-btn" href="{{ url('/student/dashboard') }}">{{ g('تابع رحلتك', 'تابعي رحلتكِ', $student) }}</a>
    </div>
@endsection
