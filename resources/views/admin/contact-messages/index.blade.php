@extends('layouts.admin')

@section('title', 'رسائل التواصل')
@section('page-title', 'رسائل التواصل')

@section('content')
<div class="cm-wrap">
    @if(session('success'))
        <div class="cm-flash">{{ session('success') }}</div>
    @endif

    <div class="cm-head">
        <div>
            <h2 class="cm-title">رسائل «تواصل معنا»</h2>
            <p class="cm-sub">الرسائل الواردة من نموذج الموقع — تُحفَظ هنا دائماً حتى لو تعذّر إرسال الإشعار البريديّ.</p>
        </div>
    </div>

    <div class="cm-filters">
        @php $mk = fn($k,$l) => route('admin.contact-messages.index', $k ? ['status'=>$k] : []); @endphp
        <a href="{{ $mk(null,'') }}" class="cm-chip {{ !$activeStatus ? 'is-active' : '' }}">الكل <span>{{ $counts['all'] }}</span></a>
        <a href="{{ $mk('unread','') }}" class="cm-chip cm-chip-unread {{ $activeStatus==='unread' ? 'is-active' : '' }}">غير مقروءة <span>{{ $counts['unread'] }}</span></a>
        <a href="{{ $mk('read','') }}" class="cm-chip {{ $activeStatus==='read' ? 'is-active' : '' }}">مقروءة <span>{{ $counts['read'] }}</span></a>
        <a href="{{ $mk('replied','') }}" class="cm-chip {{ $activeStatus==='replied' ? 'is-active' : '' }}">تمّ الردّ <span>{{ $counts['replied'] }}</span></a>
    </div>

    @if($messages->isEmpty())
        <div class="cm-empty"><div class="cm-empty-emoji">📭</div><p>لا رسائل في هذا التصنيف.</p></div>
    @else
        <div class="cm-table-wrap">
            <table class="cm-table">
                <thead>
                    <tr><th>الحالة</th><th>المُرسِل</th><th>النوع</th><th>الرسالة</th><th>التاريخ</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($messages as $m)
                        <tr class="{{ $m->status==='unread' ? 'cm-row-unread' : '' }}">
                            <td>
                                @if($m->status==='unread')<span class="cm-badge cm-badge-unread">جديدة</span>
                                @elseif($m->status==='replied')<span class="cm-badge cm-badge-replied">تمّ الردّ</span>
                                @else<span class="cm-badge cm-badge-read">مقروءة</span>@endif
                            </td>
                            <td>
                                <div class="cm-from-name">{{ $m->full_name }}</div>
                                <div class="cm-from-email">{{ $m->email }}</div>
                            </td>
                            <td>{{ $m->user_type_arabic }}</td>
                            <td class="cm-msg-cell">{{ \Illuminate\Support\Str::limit($m->message, 80) }}</td>
                            <td class="cm-date">{{ $m->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="cm-actions">
                                <a href="{{ route('admin.contact-messages.show', $m) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="cm-pagination">{{ $messages->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .cm-flash{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:10px;margin-bottom:16px;font-weight:700}
    .cm-title{margin:0 0 4px;font-size:1.4rem;font-weight:800}
    .cm-sub{margin:0 0 18px;color:#6b7280;font-size:.92rem;max-width:640px}
    .cm-filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
    .cm-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:999px;border:1px solid #e5e7eb;
        background:#f8fafc;color:#475569;text-decoration:none;font-weight:700;font-size:.88rem}
    .cm-chip span{background:#e2e8f0;border-radius:999px;padding:1px 8px;font-size:.78rem}
    .cm-chip.is-active{background:#4338ca;color:#fff;border-color:#4338ca}
    .cm-chip.is-active span{background:rgba(255,255,255,.25)}
    .cm-chip-unread span{background:#fee2e2;color:#b91c1c}
    .cm-table-wrap{overflow-x:auto;border:1px solid #e5e7eb;border-radius:14px;background:#fff}
    .cm-table{width:100%;border-collapse:collapse;font-size:.93rem}
    .cm-table th,.cm-table td{padding:12px 16px;text-align:start;border-bottom:1px solid #f1f5f9;vertical-align:top}
    .cm-table th{background:#f8fafc;font-weight:700;color:#475569;font-size:.83rem}
    .cm-row-unread{background:#fefce8}
    .cm-from-name{font-weight:700}
    .cm-from-email{font-size:.82rem;color:#94a3b8;direction:ltr;text-align:start}
    .cm-msg-cell{max-width:340px;color:#475569}
    .cm-date{white-space:nowrap;color:#94a3b8;font-size:.85rem}
    .cm-actions{white-space:nowrap}
    .cm-badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:.76rem;font-weight:700}
    .cm-badge-unread{background:#fee2e2;color:#b91c1c}
    .cm-badge-read{background:#f1f5f9;color:#64748b}
    .cm-badge-replied{background:#dcfce7;color:#166534}
    .cm-empty{text-align:center;padding:60px 20px;border:2px dashed #e5e7eb;border-radius:16px;color:#6b7280}
    .cm-empty-emoji{font-size:2.5rem;margin-bottom:8px}
    .cm-pagination{margin-top:16px}
    @media (prefers-color-scheme: dark){
        .cm-table-wrap{background:#0b1220;border-color:#1f2937}
        .cm-table th{background:#111827;color:#94a3b8}
        .cm-table th,.cm-table td{border-color:#1f2937}
        .cm-row-unread{background:#1c1917}
    }
</style>
@endpush
