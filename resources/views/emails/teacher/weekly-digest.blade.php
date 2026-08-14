@extends('emails.layouts.base')
@section('title', '📊 ملخّص أسبوعك')
@section('content')
    <p>مرحبًا {{ $teacher->name }} 👋</p>
    <p>هذا ملخّص أسبوعك التعليميّ في {{ setting('site_name', 'أثيل مكة') }}:</p>
    <div class="panel" style="text-align:center;">
        <div style="font-size:32px;font-weight:800;color:{{ setting('primary_color', '#3CCB8A') }};">{{ $pendingCount }}</div>
        <div class="muted">تسليمًا بانتظار مراجعتك</div>
    </div>
    <p>لديك {{ $studentCount }} طالبًا في فصولك. مراجعتك السريعة تحفّزهم على الاستمرار 🌟</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ url('/teacher/review') }}">مراجعة التسليمات</a></div>
@endsection
