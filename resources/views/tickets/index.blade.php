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
@endphp

@extends($__layout)

@section('title', 'الدعم الفنيّ — تذاكري')
@section('page-title', 'الدعم الفنيّ')

@section('content')
<div class="tickets-page">
    @include('tickets.partials.styles')

    @php
        $__mine = \App\Models\SupportTicket::where('user_id', auth()->id());
        $__total = (clone $__mine)->count();
        $__open = (clone $__mine)->whereIn('status', ['open', 'answered'])->count();
        $__resolved = (clone $__mine)->where('status', 'resolved')->count();
    @endphp

    <div class="tk-header">
        <div>
            <h1 class="tk-title">🛟 الدعم الفنيّ</h1>
            <p class="tk-subtitle">تابع تذاكرك وتواصل مع فريق الدعم في أيّ وقت.</p>
        </div>
        <a href="{{ route('tickets.create') }}" class="tk-btn tk-btn-primary">➕ رفع تذكرة جديدة</a>
    </div>

    <div class="tk-stats">
        <div class="tk-stat">
            <div class="tk-stat-icon all">🎫</div>
            <div>
                <div class="tk-stat-val">{{ $__total }}</div>
                <div class="tk-stat-lbl">إجمالي تذاكري</div>
            </div>
        </div>
        <div class="tk-stat">
            <div class="tk-stat-icon open">⏳</div>
            <div>
                <div class="tk-stat-val">{{ $__open }}</div>
                <div class="tk-stat-lbl">قيد المتابعة</div>
            </div>
        </div>
        <div class="tk-stat">
            <div class="tk-stat-icon done">✅</div>
            <div>
                <div class="tk-stat-val">{{ $__resolved }}</div>
                <div class="tk-stat-lbl">تذاكر محلولة</div>
            </div>
        </div>
    </div>

    @if($tickets->count() > 0)
        <div class="tk-list">
            @foreach($tickets as $ticket)
                <a href="{{ route('tickets.show', $ticket) }}" class="tk-card">
                    <div class="tk-card-top">
                        <h3 class="tk-card-subj">{{ $ticket->subject }}</h3>
                        <span class="tk-badge tk-badge-{{ $ticket->statusColor() }}">{{ $ticket->statusLabel() }}</span>
                    </div>
                    <div class="tk-card-meta">
                        <span class="tk-meta-chip">🏷️ {{ $ticket->categoryLabel() }}</span>
                        <span class="tk-pill {{ $ticket->priority === 'high' ? 'high' : '' }}">أولوية: {{ $ticket->priorityLabel() }}</span>
                        <span class="tk-meta-chip">💬 {{ $ticket->replies_count }} ردّ</span>
                        <span class="tk-meta-chip">🕒 آخر تحديث {{ ($ticket->last_reply_at ?? $ticket->updated_at)->diffForHumans() }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="tk-pagination">
            {{ $tickets->links() }}
        </div>
    @else
        <div class="tk-empty">
            <div class="tk-empty-icon">🎫</div>
            <h3 class="tk-empty-title">لا توجد تذاكر بعد</h3>
            <p class="tk-empty-text">إذا واجهتك أيّ مشكلة أو كان لديك استفسار، ارفع تذكرة وسيتواصل معك فريق الدعم.</p>
            <a href="{{ route('tickets.create') }}" class="tk-btn tk-btn-primary">➕ رفع تذكرة جديدة</a>
        </div>
    @endif
</div>
@endsection
