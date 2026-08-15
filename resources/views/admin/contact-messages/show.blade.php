@extends('layouts.admin')

@section('title', 'رسالة تواصل')
@section('page-title', 'رسالة تواصل')

@section('content')
<div class="cm-show">
    @if(session('success'))
        <div class="cm-flash">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.contact-messages.index') }}" class="cm-back">↩ كلّ الرسائل</a>

    <div class="cm-card">
        <div class="cm-card-head">
            <div>
                <div class="cm-from-name">{{ $message->full_name }}</div>
                <a href="mailto:{{ $message->email }}" class="cm-from-email">{{ $message->email }}</a>
            </div>
            <div class="cm-meta">
                <span class="cm-type">{{ $message->user_type_arabic }}</span>
                <span class="cm-when">{{ $message->created_at?->format('Y-m-d H:i') }}</span>
            </div>
        </div>

        <div class="cm-body">{{ $message->message }}</div>

        <div class="cm-tech">
            <span>IP: <code>{{ $message->ip_address ?: '—' }}</code></span>
            @if($message->replied_at)<span>تاريخ الردّ: {{ $message->replied_at->format('Y-m-d H:i') }}</span>@endif
        </div>

        <div class="cm-toolbar">
            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('ردّ على رسالتك - ' . setting('site_name', 'أثيل مكة')) }}"
               class="btn btn-primary btn-sm">✉ الردّ بالبريد</a>

            <form method="POST" action="{{ route('admin.contact-messages.status', $message) }}" class="cm-inline">
                @csrf
                <input type="hidden" name="status" value="replied">
                <button type="submit" class="btn btn-success btn-sm" {{ $message->status==='replied' ? 'disabled' : '' }}>وسمها «تمّ الردّ»</button>
            </form>

            <form method="POST" action="{{ route('admin.contact-messages.status', $message) }}" class="cm-inline">
                @csrf
                <input type="hidden" name="status" value="unread">
                <button type="submit" class="btn btn-outline-secondary btn-sm" {{ $message->status==='unread' ? 'disabled' : '' }}>وسمها غير مقروءة</button>
            </form>

            <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" class="cm-inline"
                  onsubmit="return confirm('حذف هذه الرسالة نهائيّاً؟');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">حذف</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .cm-flash{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:10px;margin-bottom:16px;font-weight:700}
    .cm-back{display:inline-block;margin-bottom:14px;color:#475569;font-weight:700;text-decoration:none}
    .cm-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:22px;max-width:760px}
    .cm-card-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;
        padding-bottom:16px;border-bottom:1px solid #f1f5f9}
    .cm-from-name{font-weight:800;font-size:1.15rem}
    .cm-from-email{font-size:.9rem;color:#4338ca;direction:ltr;text-decoration:none}
    .cm-meta{display:flex;flex-direction:column;align-items:flex-end;gap:4px}
    .cm-type{background:#eef2ff;color:#4338ca;border-radius:999px;padding:3px 12px;font-size:.8rem;font-weight:700}
    .cm-when{color:#94a3b8;font-size:.83rem}
    .cm-body{white-space:pre-wrap;line-height:1.8;padding:20px 0;color:#1f2937;font-size:1.02rem}
    .cm-tech{display:flex;gap:16px;flex-wrap:wrap;color:#94a3b8;font-size:.82rem;padding-bottom:16px;border-bottom:1px solid #f1f5f9}
    .cm-tech code{direction:ltr;background:#f1f5f9;padding:1px 6px;border-radius:5px}
    .cm-toolbar{display:flex;gap:8px;flex-wrap:wrap;margin-top:16px}
    .cm-inline{display:inline}
    /* الوضع الليلي عبر مفتاح التطبيق لا عبر نظام التشغيل — انظر التعليق في index.blade.php. */
    html[data-theme="dark"] .cm-card{background:#0b1220;border-color:#1f2937}
    html[data-theme="dark"] .cm-card-head,
    html[data-theme="dark"] .cm-tech{border-color:#1f2937}
    html[data-theme="dark"] .cm-body{color:#e2e8f0}
    html[data-theme="dark"] .cm-tech code{background:#1f2937}
</style>
@endpush
