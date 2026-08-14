@extends('emails.layouts.base')
@section('title', '📊 ملخّص أسبوع أبنائك')
@section('content')
    <p>مرحبًا {{ $parent->name }} 👋</p>
    <p>هذا ملخّص نشاط أبنائك خلال الأسبوع الماضي في {{ setting('site_name', 'أثيل مكة') }}:</p>
    @foreach($children as $child)
        <div class="panel" style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:700;">{{ $child['name'] }}</span>
            <span style="font-weight:800;color:{{ setting('primary_color', '#3CCB8A') }};">{{ $child['points'] }} نقطة</span>
        </div>
    @endforeach
    <p>شكرًا لمتابعتك المستمرّة — دعمك أكبر حافز لهم 🌱</p>
    <div class="btn-wrap"><a class="email-btn" href="{{ url('/parent/dashboard') }}">متابعة أبنائك</a></div>
@endsection
