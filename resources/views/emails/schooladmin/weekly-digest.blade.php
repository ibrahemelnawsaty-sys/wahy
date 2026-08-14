@extends('emails.layouts.base')
@section('title', '📊 ملخّص مدرستك الأسبوعيّ')
@section('content')
    <p>مرحبًا {{ $admin->name }} 👋</p>
    <p>هذا ملخّص نشاط مدرستك خلال الأسبوع الماضي:</p>
    <div class="panel">
        <div style="display:flex;justify-content:space-between;padding:4px 0;"><span>الطلاب النشِطون هذا الأسبوع</span><strong style="color:{{ setting('primary_color', '#3CCB8A') }};">{{ $activeStudents }} / {{ $totalStudents }}</strong></div>
        <div style="display:flex;justify-content:space-between;padding:4px 0;"><span>أنشطة بانتظار اعتمادك</span><strong>{{ $pendingActivities }}</strong></div>
    </div>
    <div class="btn-wrap"><a class="email-btn" href="{{ url('/school-admin/dashboard') }}">لوحة المدرسة</a></div>
@endsection
