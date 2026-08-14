@extends('layouts.admin')

@section('title', 'سجلّ البريد')
@section('page-title', 'سجلّ البريد الصادر')

@section('content')
<div class="elog-wrap">

    @if (session('success'))<div class="elog-alert ok">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="elog-alert err">{{ session('error') }}</div>@endif

    @if ($stats['queue_pending'] > 20 || $stats['stuck'] > 0)
        <div class="elog-alert warn">
            ⚠ تنبيه صحّة الطابور:
            @if($stats['queue_pending'] > 20) <b>{{ $stats['queue_pending'] }}</b> مهمّة منتظِرة في الطابور — غالبًا العامل <code>queue:work</code> لا يعمل. @endif
            @if($stats['stuck'] > 0) <b>{{ $stats['stuck'] }}</b> رسالة عالقة في «قيد الإرسال» منذ &gt;10 دقائق. @endif
        </div>
    @endif

    <div class="elog-stats">
        <div class="elog-stat"><span class="n">{{ $stats['total'] }}</span><span class="l">إجماليّ (30 يومًا)</span></div>
        <div class="elog-stat ok"><span class="n">{{ $stats['sent'] }}</span><span class="l">أُرسِل</span></div>
        <div class="elog-stat err"><span class="n">{{ $stats['failed'] }}</span><span class="l">فشل</span></div>
        <div class="elog-stat warn"><span class="n">{{ $stats['sending'] }}</span><span class="l">قيد الإرسال</span></div>
        <div class="elog-stat"><span class="n">{{ $stats['queue_pending'] }}</span><span class="l">منتظِر بالطابور</span></div>
    </div>

    <form method="GET" class="elog-filters">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالبريد أو العنوان…">
        <select name="status">
            <option value="">كل الحالات</option>
            @foreach(['sent'=>'أُرسِل','failed'=>'فشل','sending'=>'قيد الإرسال','opened'=>'فُتِح'] as $k=>$v)
                <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $v }}</option>
            @endforeach
        </select>
        <select name="category">
            <option value="">كل الفئات</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category')===$cat?'selected':'' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}">
        <input type="date" name="to" value="{{ request('to') }}">
        <button type="submit">تصفية</button>
        <a href="{{ route('admin.email-logs.index') }}" class="clear">مسح</a>
    </form>

    <div class="elog-table-wrap">
        <table class="elog-table">
            <thead><tr><th>المستلِم</th><th>العنوان</th><th>الفئة</th><th>الحالة</th><th>التاريخ</th><th></th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->to_email }}@if($log->to_name)<br><span class="muted">{{ $log->to_name }}</span>@endif</td>
                        <td class="subj">{{ \Illuminate\Support\Str::limit($log->subject, 60) }}</td>
                        <td><span class="muted">{{ $log->category }}</span></td>
                        <td><span class="badge" style="background:{{ $log->statusColor() }}1a;color:{{ $log->statusColor() }};">{{ $log->statusLabel() }}@if($log->isStuck()) ⚠@endif</span></td>
                        <td class="muted">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route('admin.email-logs.show', $log) }}" class="view">تفاصيل</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">لا توجد سجلّات بريد بعد. أرسِل رسالة (أو <code>php artisan mail:test</code>) لتظهر هنا.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="elog-pager">{{ $logs->links() }}</div>
</div>

<style>
.elog-wrap { max-width: 1100px; margin: 0 auto; }
.elog-alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; font-size: 14px; }
.elog-alert.ok { background:#dcfce7; color:#166534; }
.elog-alert.err { background:#fee2e2; color:#991b1b; }
.elog-alert.warn { background:#fef3c7; color:#92400e; border:1px solid #fcd34d; }
.elog-stats { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:20px; }
.elog-stat { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:16px; text-align:center; }
.elog-stat .n { display:block; font-size:26px; font-weight:800; color:#1e293b; }
.elog-stat .l { font-size:12.5px; color:#64748b; }
.elog-stat.ok .n{color:#16a34a} .elog-stat.err .n{color:#dc2626} .elog-stat.warn .n{color:#f59e0b}
.elog-filters { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; }
.elog-filters input, .elog-filters select { padding:9px 12px; border:1.5px solid #cbd5e1; border-radius:9px; font-size:13.5px; font-family:inherit; }
.elog-filters button { background:#0ea5e9; color:#fff; border:none; padding:9px 20px; border-radius:9px; font-weight:700; cursor:pointer; }
.elog-filters .clear { align-self:center; color:#64748b; text-decoration:none; font-size:13px; }
.elog-table-wrap { overflow-x:auto; background:#fff; border:1px solid #e2e8f0; border-radius:14px; }
.elog-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.elog-table th { text-align:right; padding:12px 14px; background:#f8fafc; color:#475569; font-weight:700; border-bottom:2px solid #e2e8f0; white-space:nowrap; }
.elog-table td { padding:11px 14px; border-bottom:1px solid #f1f5f9; vertical-align:top; }
.elog-table .subj { color:#1e293b; }
.elog-table .muted { color:#94a3b8; font-size:12px; }
.elog-table .badge { padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700; white-space:nowrap; }
.elog-table .view { color:#0ea5e9; text-decoration:none; font-weight:600; }
.elog-table .empty { text-align:center; color:#94a3b8; padding:40px; }
.elog-pager { margin-top:16px; }
[data-theme="dark"] .elog-stat, [data-theme="dark"] .elog-table-wrap { background:#1e293b; border-color:#334155; }
[data-theme="dark"] .elog-stat .n { color:#f1f5f9; }
[data-theme="dark"] .elog-table th { background:#0f172a; color:#cbd5e1; }
[data-theme="dark"] .elog-table td { border-color:#334155; }
@media(max-width:700px){ .elog-stats{grid-template-columns:repeat(2,1fr)} }
</style>
@endsection
