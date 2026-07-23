@php
    // اختيار لايوت الدور الحالي — الصفحة عابرة لكل الأدوار. نعتمد الدور **النشط** (active_role)
    // لا العمود الأساسيّ role، وإلا ظهرت لوحة المعلّم لمعلّمةٍ بدّلت لدور وليّ الأمر (تسريب طبقة).
    $__role = auth()->user()->active_role;
    $__layout = $__role === 'student' ? 'layouts.student-app'
        : ($__role === 'school_admin' ? 'layouts.school-admin'
        : ($__role === 'teacher' ? 'layouts.teacher'
        : ($__role === 'parent' ? 'layouts.parent'
        : ($__role === 'technical_support' ? 'layouts.support'
        : 'layouts.admin')))); // technical_support => لوحة الدعم؛ super_admin => admin
    $__isClosed = in_array($ticket->status, ['closed', 'resolved'], true);
@endphp

@extends($__layout)

@section('title', 'تذكرة #' . $ticket->id)
@section('page-title', 'تفاصيل التذكرة')

@section('content')
<div class="tickets-page">
    @include('tickets.partials.styles')

    <a href="{{ route('tickets.index') }}" class="tk-back">→ العودة إلى تذاكري</a>

    {{-- رأس التذكرة --}}
    <div class="tk-detail-head">
        <div class="tk-detail-head-top">
            <h1 class="tk-detail-subj">{{ $ticket->subject }}</h1>
            <span class="tk-badge tk-badge-{{ $ticket->statusColor() }}">{{ $ticket->statusLabel() }}</span>
        </div>
        <div class="tk-detail-meta">
            <span>🎫 رقم التذكرة: <b>#{{ $ticket->id }}</b></span>
            <span>🏷️ التصنيف: <b>{{ $ticket->categoryLabel() }}</b></span>
            <span>⚡ الأولوية: <b>{{ $ticket->priorityLabel() }}</b></span>
            <span>🗓️ التاريخ: <b>{{ $ticket->created_at->format('Y-m-d') }}</b></span>
            @if($ticket->assignee)
                <span>🧑‍💻 المسؤول: <b>{{ $ticket->assignee->name }}</b></span>
            @endif
            @if($ticket->status === 'resolved' && $ticket->resolver)
                <span>✅ حُلّت بواسطة: <b>{{ $ticket->resolver->name }}</b></span>
            @endif
        </div>
    </div>

    {{-- سلسلة الرسائل --}}
    <h2 class="tk-section-title">💬 المحادثة</h2>
    <div class="tk-thread">
        {{-- الرسالة الأصلية (صاحب التذكرة) --}}
        <div class="tk-msg">
            <div class="tk-msg-head">
                <div class="tk-msg-avatar">{{ mb_substr($ticket->user->name ?? auth()->user()->name, 0, 1) }}</div>
                <span class="tk-msg-author">{{ $ticket->user->name ?? auth()->user()->name }}</span>
                <span class="tk-msg-time">{{ $ticket->created_at->diffForHumans() }}</span>
            </div>
            <div class="tk-msg-body">{!! safe_html($ticket->message) !!}</div>
        </div>

        {{-- الردود --}}
        @foreach($ticket->replies as $reply)
            <div class="tk-msg {{ $reply->is_staff_reply ? 'staff' : '' }}">
                <div class="tk-msg-head">
                    <div class="tk-msg-avatar {{ $reply->is_staff_reply ? 'staff' : '' }}">
                        {{ $reply->is_staff_reply ? '🛟' : mb_substr($reply->user->name ?? '؟', 0, 1) }}
                    </div>
                    <span class="tk-msg-author">{{ $reply->is_staff_reply ? 'فريق الدعم الفنيّ' : ($reply->user->name ?? 'مستخدم') }}</span>
                    @if($reply->is_staff_reply)
                        <span class="tk-msg-role">الدعم</span>
                    @endif
                    <span class="tk-msg-time">{{ $reply->created_at->diffForHumans() }}</span>
                </div>
                <div class="tk-msg-body">{!! safe_html($reply->message) !!}</div>
            </div>
        @endforeach
    </div>

    {{-- نموذج الردّ / الإغلاق --}}
    @if($ticket->status === 'closed')
        <div class="tk-closed-note">🔒 هذه التذكرة مغلقة. إذا احتجت مساعدة إضافية، ارفع تذكرة جديدة.</div>
    @else
        <div class="tk-panel">
            <h2 class="tk-section-title">✍️ أضِف ردّاً</h2>
            <form method="POST" action="{{ route('tickets.reply', $ticket) }}">
                @csrf
                <div class="tk-field">
                    <textarea name="message" class="tk-textarea" placeholder="اكتب ردّك هنا..." required>{{ old('message') }}</textarea>
                    @error('message')
                        <span class="tk-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="tk-form-actions">
                    <button type="submit" class="tk-btn tk-btn-primary">📨 إرسال الردّ</button>
                </div>
            </form>

            <form method="POST" action="{{ route('tickets.close', $ticket) }}"
                  onsubmit="return confirm('هل تريد إغلاق هذه التذكرة؟ يمكنك رفع تذكرة جديدة لاحقاً إذا لزم الأمر.')"
                  style="margin-top:16px;border-top:1px solid var(--tk-border);padding-top:16px;">
                @csrf
                <button type="submit" class="tk-btn tk-btn-danger">🔒 إغلاق التذكرة</button>
                <span class="tk-help" style="margin-inline-start:8px;">أغلق التذكرة عند حلّ مشكلتك.</span>
            </form>
        </div>
    @endif
</div>
@endsection
