@extends('layouts.admin')

@section('title', 'تفاصيل الحملة')
@section('page-title', 'تفاصيل حملة البريد')

@section('content')
<div class="ecs-wrap">
    @if (session('success'))<div class="ecs-alert ok">{{ session('success') }}</div>@endif
    <a href="{{ route('admin.email-campaigns.index') }}" class="ecs-back">→ رجوع للحملات</a>

    <div class="ecs-card">
        <h3>{{ $campaign->subject }}</h3>
        <table class="ecs-meta">
            <tr><th>الجمهور</th><td>{{ $campaign->audienceLabel() }}</td></tr>
            <tr><th>الحالة</th><td>{{ $campaign->statusLabel() }}</td></tr>
            <tr><th>إجماليّ المستلمين</th><td>{{ $campaign->total_recipients }}</td></tr>
            <tr><th>أُنشئت</th><td>{{ $campaign->created_at?->format('Y-m-d H:i') }}</td></tr>
            <tr><th>المُرسِل</th><td>{{ $campaign->creator?->name ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="ecs-stats">
        <div class="ecs-stat ok"><span class="n">{{ $sent }}</span><span class="l">أُرسِل</span></div>
        <div class="ecs-stat err"><span class="n">{{ $failed }}</span><span class="l">فشل</span></div>
        <div class="ecs-stat"><span class="n">{{ $campaign->total_recipients }}</span><span class="l">الهدف</span></div>
    </div>

    <div class="ecs-links">
        <a href="{{ route('admin.email-logs.index', ['search' => '']) }}?status=&category=campaign">📧 عرض رسائل الحملات في سجلّ البريد</a>
        <span class="ecs-note">القيَم تتحدّث تدريجيًّا مع معالجة الطابور — حدِّث الصفحة للمتابعة.</span>
    </div>
</div>

<style>
.ecs-wrap{max-width:720px;margin:0 auto;}
.ecs-alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:600;font-size:14px;background:#dcfce7;color:#166534;}
.ecs-back{display:inline-block;color:#0ea5e9;text-decoration:none;margin-bottom:14px;font-weight:600;}
.ecs-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:22px;margin-bottom:16px;}
.ecs-card h3{margin:0 0 14px;color:#1e293b;}
.ecs-meta{width:100%;border-collapse:collapse;font-size:14px;}
.ecs-meta th{text-align:right;color:#64748b;font-weight:700;padding:7px 12px 7px 0;white-space:nowrap;width:150px;}
.ecs-meta td{padding:7px 0;color:#1e293b;}
.ecs-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;}
.ecs-stat{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px;text-align:center;}
.ecs-stat .n{display:block;font-size:26px;font-weight:800;color:#1e293b;} .ecs-stat .l{font-size:12.5px;color:#64748b;}
.ecs-stat.ok .n{color:#16a34a;} .ecs-stat.err .n{color:#dc2626;}
.ecs-links a{color:#0ea5e9;text-decoration:none;font-weight:600;display:inline-block;margin-bottom:8px;}
.ecs-note{display:block;color:#94a3b8;font-size:12.5px;}
[data-theme="dark"] .ecs-card,[data-theme="dark"] .ecs-stat{background:#1e293b;border-color:#334155;}
[data-theme="dark"] .ecs-card h3,[data-theme="dark"] .ecs-meta td,[data-theme="dark"] .ecs-stat .n{color:#f1f5f9;}
</style>
@endsection
