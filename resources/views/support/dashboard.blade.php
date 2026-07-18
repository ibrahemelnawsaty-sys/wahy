@extends('layouts.support')

@section('title', 'لوحة الدعم الفنيّ')
@section('page-title', 'لوحة الدعم الفنيّ')

@section('content')
    <!-- Stats Cards -->
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-icon primary">🎫</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">إجمالي التذاكر</div>
                <div class="admin-stat-value">{{ number_format($stats['total_tickets']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon warning">📂</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">مفتوحة</div>
                <div class="admin-stat-value">{{ number_format($stats['open_tickets']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon info">💬</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">تم الرد</div>
                <div class="admin-stat-value">{{ number_format($stats['answered_tickets']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon success">✅</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">محلولة</div>
                <div class="admin-stat-value">{{ number_format($stats['resolved_tickets']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon accent">⬆️</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">مُصعّدة</div>
                <div class="admin-stat-value">{{ number_format($stats['escalated_tickets']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon secondary">👥</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">إجمالي المستخدمين</div>
                <div class="admin-stat-value">{{ number_format($stats['total_users']) }}</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon primary">🏅</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">محلولاتي</div>
                <div class="admin-stat-value">{{ number_format($stats['my_resolved']) }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="admin-card" style="margin-top: 24px;">
        <div class="admin-card-header">
            <h3 class="admin-card-title">⚡ إجراءات سريعة</h3>
        </div>
        <div class="admin-card-body">
            <div class="admin-actions-grid">
                <a href="{{ route('support.tickets.index') }}" class="admin-action-btn gradient-purple">
                    <span class="admin-action-icon">🎫</span>
                    <span class="admin-action-text">كل التذاكر</span>
                </a>
                <a href="{{ route('support.tickets.index', ['status' => 'open']) }}" class="admin-action-btn gradient-orange">
                    <span class="admin-action-icon">📂</span>
                    <span class="admin-action-text">التذاكر المفتوحة</span>
                </a>
                <a href="{{ route('support.tickets.index', ['escalated' => 1]) }}" class="admin-action-btn gradient-blue">
                    <span class="admin-action-icon">⬆️</span>
                    <span class="admin-action-text">المُصعّدة</span>
                </a>
                <a href="{{ route('support.users.index') }}" class="admin-action-btn gradient-green">
                    <span class="admin-action-icon">👥</span>
                    <span class="admin-action-text">إدارة المستخدمين</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Tickets -->
    <div class="admin-card" style="margin-top: 24px;">
        <div class="admin-card-header">
            <h3 class="admin-card-title">🕑 أحدث التذاكر</h3>
            <a href="{{ route('support.tickets.index') }}" class="admin-btn admin-btn-outline" style="padding: 8px 16px; font-size: 12px;">
                عرض الكل
            </a>
        </div>
        <div class="admin-card-body" style="padding: 0;">
            @if($recent_tickets->count() > 0)
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table class="support-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الموضوع</th>
                            <th>صاحب التذكرة</th>
                            <th>الحالة</th>
                            <th>مُسنَدة إلى</th>
                            <th>آخر تحديث</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent_tickets as $ticket)
                        <tr>
                            <td style="color: #94a3b8; font-weight: 700;">#{{ $ticket->id }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $ticket->subject }}</div>
                                @if($ticket->escalated)
                                <span class="support-badge escalate" style="margin-top: 4px;">⬆️ مُصعّدة</span>
                                @endif
                            </td>
                            <td>{{ $ticket->user->name ?? 'غير معروف' }}</td>
                            <td><span class="support-badge {{ $ticket->statusColor() }}">{{ $ticket->statusLabel() }}</span></td>
                            <td>
                                @if($ticket->assignee)
                                    {{ $ticket->assignee->name }}
                                @else
                                    <span style="color: #cbd5e1;">—</span>
                                @endif
                            </td>
                            <td style="color: #64748b; font-size: 13px; white-space: nowrap;">{{ $ticket->updated_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('support.tickets.show', $ticket) }}" class="support-btn support-btn-primary">فتح</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="text-align: center; padding: 48px 20px; color: #64748b;">
                لا توجد تذاكر بعد.
            </div>
            @endif
        </div>
    </div>
@endsection
