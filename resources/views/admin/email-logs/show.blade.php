@extends('layouts.admin')

@section('title', 'تفاصيل رسالة')
@section('page-title', 'تفاصيل رسالة بريد')

@section('content')
<div class="eld-wrap">
    @if (session('success'))<div class="elog-alert ok">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="elog-alert err">{{ session('error') }}</div>@endif

    <a href="{{ route('admin.email-logs.index') }}" class="eld-back">→ رجوع للسجلّ</a>

    <div class="eld-card">
        <div class="eld-head">
            <h3>{{ $log->subject ?: '(بلا عنوان)' }}</h3>
            <span class="badge" style="background:{{ $log->statusColor() }}1a;color:{{ $log->statusColor() }};">{{ $log->statusLabel() }}</span>
        </div>
        <table class="eld-meta">
            <tr><th>المستلِم</th><td>{{ $log->to_email }} {{ $log->to_name ? '(' . $log->to_name . ')' : '' }}</td></tr>
            <tr><th>الفئة</th><td>{{ $log->category }}</td></tr>
            <tr><th>النوع (Mailable)</th><td>{{ $log->mailable_class ?: '—' }}</td></tr>
            <tr><th>أُنشئ</th><td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td></tr>
            <tr><th>أُرسِل</th><td>{{ $log->sent_at?->format('Y-m-d H:i:s') ?: '—' }}</td></tr>
            <tr><th>Message-ID</th><td class="mono">{{ $log->message_id ?: '—' }}</td></tr>
            @if($log->error_message)<tr><th>الخطأ</th><td class="err-txt">{{ $log->error_message }}</td></tr>@endif
            @if($log->isStuck())<tr><th></th><td class="err-txt">⚠ عالقة في «قيد الإرسال» — تحقّق من عمل العامل queue:work.</td></tr>@endif
        </table>

        <form method="POST" action="{{ route('admin.email-logs.resend', $log) }}" onsubmit="return confirm('إعادة إرسال هذه الرسالة إلى {{ $log->to_email }}؟');">
            @csrf
            <button type="submit" class="eld-resend" @if(!$log->body) disabled title="لا يوجد محتوى محفوظ" @endif>↻ إعادة الإرسال</button>
        </form>
    </div>

    @if($log->body)
        <div class="eld-card">
            <div class="eld-preview-head">معاينة المحتوى</div>
            <iframe class="eld-preview" sandbox srcdoc="{{ $log->body }}" title="معاينة الرسالة"></iframe>
        </div>
    @endif
</div>

<style>
.eld-wrap { max-width: 800px; margin: 0 auto; }
.elog-alert { padding:12px 16px; border-radius:10px; margin-bottom:16px; font-weight:600; font-size:14px; }
.elog-alert.ok { background:#dcfce7; color:#166534; } .elog-alert.err { background:#fee2e2; color:#991b1b; }
.eld-back { display:inline-block; color:#0ea5e9; text-decoration:none; margin-bottom:14px; font-weight:600; }
.eld-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:22px; margin-bottom:18px; }
.eld-head { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
.eld-head h3 { margin:0; font-size:18px; color:#1e293b; }
.eld-head .badge { padding:4px 12px; border-radius:20px; font-size:12.5px; font-weight:700; }
.eld-meta { width:100%; border-collapse:collapse; font-size:14px; }
.eld-meta th { text-align:right; color:#64748b; font-weight:700; padding:8px 12px 8px 0; white-space:nowrap; vertical-align:top; width:130px; }
.eld-meta td { padding:8px 0; color:#1e293b; }
.eld-meta .mono { font-family:monospace; font-size:12px; word-break:break-all; }
.eld-meta .err-txt { color:#dc2626; }
.eld-resend { margin-top:16px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; padding:11px 26px; border-radius:10px; font-weight:700; cursor:pointer; }
.eld-resend:disabled { background:#cbd5e1; cursor:not-allowed; }
.eld-preview-head { font-weight:700; color:#475569; margin-bottom:12px; }
.eld-preview { width:100%; height:520px; border:1px solid #e5e7eb; border-radius:10px; background:#fff; }
[data-theme="dark"] .eld-card { background:#1e293b; border-color:#334155; }
[data-theme="dark"] .eld-head h3, [data-theme="dark"] .eld-meta td { color:#f1f5f9; }
</style>
@endsection
