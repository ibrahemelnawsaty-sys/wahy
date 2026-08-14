@extends('layouts.admin')

@section('title', 'حملات البريد')
@section('page-title', 'حملات البريد الجماعيّة')

@section('content')
<div class="ecl-wrap">
    @if (session('success'))<div class="ecl-alert ok">{{ session('success') }}</div>@endif

    <div class="ecl-top">
        <p>أرسِل بريدًا لكل المستخدمين أو لفئة/مدرسة/إيميلات مخصّصة. يُرسَل عبر الطابور ويُتتبَّع في سجلّ البريد.</p>
        <a href="{{ route('admin.email-campaigns.create') }}" class="ecl-new">➕ حملة جديدة</a>
    </div>

    <div class="ecl-table-wrap">
        <table class="ecl-table">
            <thead><tr><th>العنوان</th><th>الجمهور</th><th>الحالة</th><th>المستلمون</th><th>التاريخ</th><th></th></tr></thead>
            <tbody>
                @forelse($campaigns as $c)
                    <tr>
                        <td>{{ \Illuminate\Support\Str::limit($c->subject, 55) }}</td>
                        <td class="muted">{{ $c->audienceLabel() }}</td>
                        <td>{{ $c->statusLabel() }}</td>
                        <td>{{ $c->total_recipients }}</td>
                        <td class="muted">{{ $c->created_at?->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route('admin.email-campaigns.show', $c) }}" class="view">تفاصيل</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">لا توجد حملات بعد. ابدأ بـ«حملة جديدة».</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="ecl-pager">{{ $campaigns->links() }}</div>
</div>

<style>
.ecl-wrap{max-width:1000px;margin:0 auto;}
.ecl-alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:600;font-size:14px;}
.ecl-alert.ok{background:#dcfce7;color:#166534;}
.ecl-top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px;}
.ecl-top p{margin:0;color:#64748b;font-size:14px;}
.ecl-new{background:linear-gradient(135deg,#10b981,#059669);color:#fff;padding:10px 20px;border-radius:10px;text-decoration:none;font-weight:800;white-space:nowrap;}
.ecl-table-wrap{overflow-x:auto;background:#fff;border:1px solid #e2e8f0;border-radius:14px;}
.ecl-table{width:100%;border-collapse:collapse;font-size:14px;}
.ecl-table th{text-align:right;padding:12px 14px;background:#f8fafc;color:#475569;font-weight:700;border-bottom:2px solid #e2e8f0;white-space:nowrap;}
.ecl-table td{padding:11px 14px;border-bottom:1px solid #f1f5f9;}
.ecl-table .muted{color:#94a3b8;font-size:12.5px;} .ecl-table .view{color:#0ea5e9;text-decoration:none;font-weight:600;}
.ecl-table .empty{text-align:center;color:#94a3b8;padding:36px;}
.ecl-pager{margin-top:14px;}
[data-theme="dark"] .ecl-table-wrap{background:#1e293b;border-color:#334155;}
[data-theme="dark"] .ecl-table th{background:#0f172a;color:#cbd5e1;} [data-theme="dark"] .ecl-table td{border-color:#334155;}
</style>
@endsection
