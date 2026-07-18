@extends('layouts.support')

@section('title', 'تذكرة #' . $ticket->id)
@section('page-title', 'تذكرة #' . $ticket->id)

@section('content')
    <div style="margin-bottom: 16px;">
        <a href="{{ route('support.tickets.index') }}" class="support-btn support-btn-ghost">← رجوع للتذاكر</a>
    </div>

    <div style="display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 24px; align-items: start;" class="support-show-grid">
        <!-- Main Column: Ticket + Thread -->
        <div>
            <!-- Ticket Header -->
            <div class="support-card" style="padding: 24px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 0;">
                        <h2 style="margin: 0 0 8px; font-size: 20px; font-weight: 700;">{{ $ticket->subject }}</h2>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                            <span class="support-badge {{ $ticket->statusColor() }}">{{ $ticket->statusLabel() }}</span>
                            @php $pc = $ticket->priority === 'high' ? 'danger' : ($ticket->priority === 'low' ? 'secondary' : 'info'); @endphp
                            <span class="support-badge {{ $pc }}">أولوية: {{ $ticket->priorityLabel() }}</span>
                            <span class="support-badge secondary">{{ $ticket->categoryLabel() }}</span>
                            @if($ticket->escalated)
                            <span class="support-badge escalate">⬆️ مُصعّدة</span>
                            @endif
                        </div>
                    </div>
                    <div style="text-align: left; color: #94a3b8; font-size: 13px; white-space: nowrap;">
                        #{{ $ticket->id }}<br>
                        {{ $ticket->created_at->format('Y-m-d H:i') }}
                    </div>
                </div>

                <!-- Original message -->
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eef2f7;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                            {{ mb_substr($ticket->user->name ?? 'غ', 0, 1, 'UTF-8') }}
                        </div>
                        <div>
                            <div style="font-weight: 700;">{{ $ticket->user->name ?? 'غير معروف' }}</div>
                            <div style="font-size: 12px; color: #94a3b8;">{{ $ticket->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div style="line-height: 1.9; color: inherit;">{!! safe_html($ticket->message) !!}</div>
                </div>
            </div>

            <!-- Thread -->
            <div class="support-card" style="padding: 24px; margin-bottom: 24px;">
                <h3 style="margin: 0 0 20px; font-size: 16px; font-weight: 700;">💬 سلسلة الردود ({{ $ticket->replies->count() }})</h3>

                @forelse($ticket->replies as $reply)
                    @php $staff = $reply->is_staff_reply; @endphp
                    <div style="display: flex; gap: 12px; margin-bottom: 20px; {{ $staff ? 'flex-direction: row-reverse;' : '' }}">
                        <div style="width: 38px; height: 38px; flex-shrink: 0; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; background: {{ $staff ? 'linear-gradient(135deg,#16a34a,#059669)' : 'linear-gradient(135deg,#64748b,#475569)' }};">
                            {{ mb_substr($reply->user->name ?? 'غ', 0, 1, 'UTF-8') }}
                        </div>
                        <div style="max-width: 78%; {{ $staff ? 'text-align: right;' : '' }}">
                            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px; {{ $staff ? 'flex-direction: row-reverse;' : '' }}">
                                <span style="font-weight: 700; font-size: 14px;">{{ $reply->user->name ?? 'غير معروف' }}</span>
                                @if($staff)<span class="support-badge success" style="font-size: 10px; padding: 2px 8px;">الدعم</span>@endif
                                <span style="font-size: 12px; color: #94a3b8;">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="padding: 12px 16px; border-radius: 14px; line-height: 1.8; {{ $staff ? 'background: rgba(22,163,74,.10); border-top-right-radius: 4px;' : 'background: rgba(100,116,139,.14); border-top-left-radius: 4px;' }}">
                                {!! safe_html($reply->message) !!}
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 24px; color: #94a3b8;">لا توجد ردود بعد. كن أوّل من يردّ.</div>
                @endforelse
            </div>

            <!-- Reply Form -->
            @if($ticket->status !== 'closed')
            <div class="support-card" style="padding: 24px;">
                <h3 style="margin: 0 0 16px; font-size: 16px; font-weight: 700;">✍️ ردّ على المستخدم</h3>
                <form method="POST" action="{{ route('support.tickets.reply', $ticket) }}">
                    @csrf
                    <textarea name="message" rows="4" required placeholder="اكتب ردّك هنا..."
                              style="width: 100%; padding: 14px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 14px; line-height: 1.8; resize: vertical;"></textarea>
                    <div style="display: flex; justify-content: flex-end; margin-top: 12px;">
                        <button type="submit" class="support-btn support-btn-primary">📨 إرسال الردّ</button>
                    </div>
                </form>
            </div>
            @else
            <div class="support-card" style="padding: 20px; text-align: center; color: #94a3b8;">
                هذه التذكرة مغلقة. أعِد فتحها للردّ عليها.
            </div>
            @endif
        </div>

        <!-- Sidebar: Details + Actions -->
        <div>
            <!-- Actions -->
            <div class="support-card" style="padding: 20px; margin-bottom: 20px;">
                <h3 style="margin: 0 0 16px; font-size: 15px; font-weight: 700;">⚙️ الإجراءات</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @if($ticket->status !== 'resolved')
                    <form method="POST" action="{{ route('support.tickets.resolve', $ticket) }}">
                        @csrf
                        <button type="submit" class="support-btn support-btn-success" style="width: 100%; justify-content: center;">✅ وضعها كمحلولة</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('support.tickets.reopen', $ticket) }}">
                        @csrf
                        <button type="submit" class="support-btn support-btn-warning" style="width: 100%; justify-content: center;">🔓 إعادة الفتح</button>
                    </form>
                    @endif

                    @if($ticket->status === 'closed')
                    <form method="POST" action="{{ route('support.tickets.reopen', $ticket) }}">
                        @csrf
                        <button type="submit" class="support-btn support-btn-warning" style="width: 100%; justify-content: center;">🔓 إعادة الفتح</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('support.tickets.close', $ticket) }}" onsubmit="return confirm('هل أنت متأكد من إغلاق التذكرة؟')">
                        @csrf
                        <button type="submit" class="support-btn support-btn-secondary" style="width: 100%; justify-content: center;">🔒 إغلاق التذكرة</button>
                    </form>
                    @endif

                    @if($ticket->assigned_to !== auth()->id())
                    <form method="POST" action="{{ route('support.tickets.assign', $ticket) }}">
                        @csrf
                        <button type="submit" class="support-btn support-btn-ghost" style="width: 100%; justify-content: center;">🙋 إسناد التذكرة إليّ</button>
                    </form>
                    @endif

                    @if(!$ticket->escalated)
                    <form method="POST" action="{{ route('support.tickets.escalate', $ticket) }}" onsubmit="return confirm('تصعيد هذه التذكرة للسوبر أدمن؟')">
                        @csrf
                        <button type="submit" class="support-btn support-btn-escalate" style="width: 100%; justify-content: center;">⬆️ تصعيد للسوبر أدمن</button>
                    </form>
                    @endif
                </div>
            </div>

            <!-- Details -->
            <div class="support-card" style="padding: 20px;">
                <h3 style="margin: 0 0 16px; font-size: 15px; font-weight: 700;">📋 تفاصيل التذكرة</h3>
                <div style="display: flex; flex-direction: column; gap: 14px; font-size: 14px;">
                    <div>
                        <div style="color: #94a3b8; font-size: 12px; margin-bottom: 2px;">صاحب التذكرة</div>
                        <div style="font-weight: 600;">{{ $ticket->user->name ?? 'غير معروف' }}</div>
                        <div style="font-size: 12px; color: #94a3b8;">{{ $ticket->user->email ?? '' }}</div>
                    </div>
                    <div>
                        <div style="color: #94a3b8; font-size: 12px; margin-bottom: 2px;">المدرسة</div>
                        <div style="font-weight: 600;">{{ $ticket->school->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="color: #94a3b8; font-size: 12px; margin-bottom: 2px;">مُسنَدة إلى</div>
                        <div style="font-weight: 600;">{{ $ticket->assignee->name ?? 'غير مُسنَدة' }}</div>
                    </div>
                    @if($ticket->resolved_at)
                    <div>
                        <div style="color: #94a3b8; font-size: 12px; margin-bottom: 2px;">حُلّت بواسطة</div>
                        <div style="font-weight: 600;">{{ $ticket->resolver->name ?? '—' }}</div>
                        <div style="font-size: 12px; color: #94a3b8;">{{ $ticket->resolved_at->format('Y-m-d H:i') }}</div>
                    </div>
                    @endif
                    @if($ticket->escalated && $ticket->escalated_at)
                    <div>
                        <div style="color: #94a3b8; font-size: 12px; margin-bottom: 2px;">صُعّدت في</div>
                        <div style="font-weight: 600;">{{ $ticket->escalated_at->format('Y-m-d H:i') }}</div>
                    </div>
                    @endif
                    <div>
                        <div style="color: #94a3b8; font-size: 12px; margin-bottom: 2px;">أُنشئت</div>
                        <div style="font-weight: 600;">{{ $ticket->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media (max-width: 900px) {
            .support-show-grid { grid-template-columns: 1fr !important; }
        }
    </style>
@endsection
