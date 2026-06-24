@extends('layouts.admin')

@section('title', 'سجل الرسائل')

@section('content')
<style>
.ml-page { padding: 0; }

.ml-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 18px;
    padding: 32px;
    margin-bottom: 28px;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.35);
    position: relative;
    overflow: hidden;
}
.ml-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -30%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    border-radius: 50%;
}
.ml-hero-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    position: relative;
    z-index: 1;
}
.ml-hero-icon {
    width: 60px; height: 60px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    margin-bottom: 14px;
}
.ml-hero h1 { font-size: 28px; font-weight: 800; margin: 0 0 6px; }
.ml-hero p { opacity: 0.9; font-size: 15px; margin: 0; }
.ml-hero-actions { display: flex; gap: 10px; }
.ml-hero-actions .ml-btn {
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    border: none;
    cursor: pointer;
    display: flex; align-items: center; gap: 8px;
    transition: all 0.3s;
    text-decoration: none;
}
.ml-btn-glass {
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    color: white;
}
.ml-btn-glass:hover { background: rgba(255,255,255,0.3); transform: translateY(-2px); color: white; }
.ml-btn-white {
    background: white;
    color: #667eea;
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
}
.ml-btn-white:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); color: #667eea; }

/* Stats Grid */
.ml-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}
.ml-stat {
    background: white;
    border-radius: 16px;
    padding: 22px;
    border: 2px solid #f1f5f9;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 16px;
}
.ml-stat:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    border-color: #e2e8f0;
}
.ml-stat-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}
.ml-stat-value {
    font-size: 28px;
    font-weight: 800;
    color: #1e293b;
    line-height: 1;
}
.ml-stat-label {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
    margin-top: 4px;
}

/* Cards */
.ml-card {
    background: white;
    border-radius: 18px;
    border: 2px solid #f1f5f9;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    margin-bottom: 24px;
    overflow: hidden;
}
.ml-card-header {
    padding: 20px 24px;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, rgba(102,126,234,0.03) 0%, rgba(118,75,162,0.03) 100%);
}
.ml-card-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.ml-card-body { padding: 24px; }

/* Filters */
.ml-filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}
.ml-filter-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}
.ml-filter-group input,
.ml-filter-group select {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    background: #f8fafc;
    transition: all 0.3s;
    color: #1e293b;
}
.ml-filter-group input:focus,
.ml-filter-group select:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 4px 12px rgba(102,126,234,0.12);
}
.ml-filter-actions {
    margin-top: 18px;
    display: flex;
    gap: 10px;
}
.ml-filter-actions .ml-btn-action {
    padding: 10px 22px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s;
}
.ml-btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 14px rgba(102,126,234,0.3);
}
.ml-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }
.ml-btn-secondary {
    background: #f1f5f9;
    color: #475569;
    border: 2px solid #e2e8f0;
}
.ml-btn-secondary:hover { background: #e2e8f0; color: #475569; }

/* Table */
.ml-table {
    width: 100%;
    border-collapse: collapse;
}
.ml-table thead th {
    padding: 14px 16px;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    text-align: right;
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}
.ml-table tbody tr {
    transition: all 0.2s;
}
.ml-table tbody tr:hover {
    background: linear-gradient(135deg, rgba(102,126,234,0.03) 0%, rgba(118,75,162,0.03) 100%);
}
.ml-table tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
    vertical-align: middle;
}

/* User Cell */
.ml-user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ml-user-avatar {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}
.ml-user-name { font-weight: 600; font-size: 14px; color: #1e293b; }
.ml-user-role { font-size: 11px; color: #94a3b8; }

/* Badges */
.ml-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}
.ml-badge-read { background: #dcfce7; color: #166534; }
.ml-badge-unread { background: #fef3c7; color: #92400e; }

/* Action Buttons */
.ml-actions { display: flex; gap: 6px; }
.ml-action-btn {
    width: 34px; height: 34px;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    background: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
    text-decoration: none;
    color: #475569;
}
.ml-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.ml-action-btn.view:hover { border-color: #667eea; color: #667eea; background: #f0f0ff; }
.ml-action-btn.chat:hover { border-color: #10b981; color: #10b981; background: #f0fdf4; }
.ml-action-btn.delete:hover { border-color: #ef4444; color: #ef4444; background: #fef2f2; }

/* Top Senders */
.ml-sender-card {
    padding: 18px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 14px;
    border: 2px solid #e2e8f0;
    transition: all 0.3s;
}
.ml-sender-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    border-color: #667eea;
}
.ml-rank {
    font-size: 28px;
    line-height: 1;
}
.ml-sender-count {
    font-size: 22px;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Empty */
.ml-empty {
    text-align: center;
    padding: 60px 20px;
}
.ml-empty-icon {
    width: 80px; height: 80px;
    background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    font-size: 36px;
    margin: 0 auto 16px;
}
</style>

<div class="ml-page">
    <!-- Hero Header -->
    <div class="ml-hero">
        <div class="ml-hero-top">
            <div>
                <div class="ml-hero-icon">📨</div>
                <h1>سجل الرسائل</h1>
                <p>عرض وإدارة جميع الرسائل المرسلة في النظام</p>
            </div>
            <div class="ml-hero-actions">
                <a href="{{ route('admin.messages-log.statistics') }}" class="ml-btn ml-btn-glass">
                    📊 <span>الإحصائيات</span>
                </a>
                <button onclick="document.getElementById('exportForm').submit()" class="ml-btn ml-btn-white">
                    📥 <span>تصدير CSV</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="ml-stats">
        <div class="ml-stat">
            <div class="ml-stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; box-shadow: 0 6px 16px rgba(102,126,234,0.3);">💬</div>
            <div>
                <div class="ml-stat-value">{{ number_format($stats['total_messages']) }}</div>
                <div class="ml-stat-label">إجمالي الرسائل</div>
            </div>
        </div>
        <div class="ml-stat">
            <div class="ml-stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c); color: white; box-shadow: 0 6px 16px rgba(240,147,251,0.3);">👥</div>
            <div>
                <div class="ml-stat-value">{{ number_format($stats['total_conversations']) }}</div>
                <div class="ml-stat-label">المحادثات</div>
            </div>
        </div>
        <div class="ml-stat">
            <div class="ml-stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; box-shadow: 0 6px 16px rgba(79,172,254,0.3);">📬</div>
            <div>
                <div class="ml-stat-value">{{ number_format($stats['unread_messages']) }}</div>
                <div class="ml-stat-label">غير مقروءة</div>
            </div>
        </div>
        <div class="ml-stat">
            <div class="ml-stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7); color: white; box-shadow: 0 6px 16px rgba(67,233,123,0.3);">📅</div>
            <div>
                <div class="ml-stat-value">{{ number_format($stats['messages_today']) }}</div>
                <div class="ml-stat-label">رسائل اليوم</div>
            </div>
        </div>
        <div class="ml-stat">
            <div class="ml-stat-icon" style="background: linear-gradient(135deg, #fa709a, #fee140); color: white; box-shadow: 0 6px 16px rgba(250,112,154,0.3);">📆</div>
            <div>
                <div class="ml-stat-value">{{ number_format($stats['messages_this_week']) }}</div>
                <div class="ml-stat-label">هذا الأسبوع</div>
            </div>
        </div>
        <div class="ml-stat">
            <div class="ml-stat-icon" style="background: linear-gradient(135deg, #30cfd0, #330867); color: white; box-shadow: 0 6px 16px rgba(48,207,208,0.3);">📊</div>
            <div>
                <div class="ml-stat-value">{{ number_format($stats['messages_this_month']) }}</div>
                <div class="ml-stat-label">هذا الشهر</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="ml-card">
        <div class="ml-card-header">
            <span style="font-size: 20px;">🔍</span>
            <h3>البحث والفلترة</h3>
        </div>
        <div class="ml-card-body">
            <form method="GET" action="{{ route('admin.messages-log.index') }}" id="filterForm">
                <div class="ml-filters-grid">
                    <div class="ml-filter-group">
                        <label>البحث في الرسالة</label>
                        <input type="text" name="search" placeholder="ابحث في محتوى الرسائل..." value="{{ request('search') }}">
                    </div>
                    <div class="ml-filter-group">
                        <label>المرسل</label>
                        <select name="sender_id">
                            <option value="">الكل</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('sender_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ml-filter-group">
                        <label>المستقبل</label>
                        <select name="receiver_id">
                            <option value="">الكل</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('receiver_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ml-filter-group">
                        <label>حالة القراءة</label>
                        <select name="read_status">
                            <option value="">الكل</option>
                            <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>مقروءة</option>
                            <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>غير مقروءة</option>
                        </select>
                    </div>
                    <div class="ml-filter-group">
                        <label>من تاريخ</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="ml-filter-group">
                        <label>إلى تاريخ</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="ml-filter-group">
                        <label>الترتيب حسب</label>
                        <select name="sort_by">
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>تاريخ الإرسال</option>
                            <option value="read_at" {{ request('sort_by') == 'read_at' ? 'selected' : '' }}>تاريخ القراءة</option>
                            <option value="sender_id" {{ request('sort_by') == 'sender_id' ? 'selected' : '' }}>المرسل</option>
                            <option value="receiver_id" {{ request('sort_by') == 'receiver_id' ? 'selected' : '' }}>المستقبل</option>
                        </select>
                    </div>
                    <div class="ml-filter-group">
                        <label>اتجاه الترتيب</label>
                        <select name="sort_order">
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>تنازلي</option>
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>تصاعدي</option>
                        </select>
                    </div>
                </div>
                <div class="ml-filter-actions">
                    <button type="submit" class="ml-btn-action ml-btn-primary">🔍 بحث</button>
                    <a href="{{ route('admin.messages-log.index') }}" class="ml-btn-action ml-btn-secondary">🔄 إعادة تعيين</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="ml-card">
        <div class="ml-card-header">
            <span style="font-size: 20px;">📋</span>
            <h3>الرسائل ({{ $messages->total() }})</h3>
        </div>
        <div style="padding: 0;">
            @if($messages->count() > 0)
                <div style="overflow-x: auto;">
                    <table class="ml-table">
                        <thead>
                            <tr>
                                <th style="width: 55px;">ID</th>
                                <th>المرسل</th>
                                <th>المستقبل</th>
                                <th>الرسالة</th>
                                <th style="width: 110px;">الحالة</th>
                                <th style="width: 130px;">تاريخ الإرسال</th>
                                <th style="width: 130px;">تاريخ القراءة</th>
                                <th style="width: 120px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $message)
                                <tr>
                                    <td><span style="font-weight: 700; color: #667eea;">#{{ $message->id }}</span></td>
                                    <td>
                                        <div class="ml-user-cell">
                                            <div class="ml-user-avatar" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                                {{ mb_substr($message->sender->name ?? 'غ', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="ml-user-name">{{ $message->sender->name ?? 'غير معروف' }}</div>
                                                <div class="ml-user-role">{{ $message->sender->role ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ml-user-cell">
                                            <div class="ml-user-avatar" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                                                {{ mb_substr($message->receiver->name ?? 'غ', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="ml-user-name">{{ $message->receiver->name ?? 'غير معروف' }}</div>
                                                <div class="ml-user-role">{{ $message->receiver->role ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #475569;" title="{{ $message->message }}">
                                            {{ \Illuminate\Support\Str::limit($message->message, 80) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($message->is_read)
                                            <span class="ml-badge ml-badge-read">✓ مقروءة</span>
                                        @else
                                            <span class="ml-badge ml-badge-unread">⏳ غير مقروءة</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-size: 13px;">
                                            <div style="font-weight: 600; color: #1e293b;">{{ $message->created_at->format('Y-m-d') }}</div>
                                            <div style="color: #94a3b8;">{{ $message->created_at->format('H:i') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($message->read_at)
                                            <div style="font-size: 13px;">
                                                <div style="font-weight: 600; color: #1e293b;">{{ $message->read_at->format('Y-m-d') }}</div>
                                                <div style="color: #94a3b8;">{{ $message->read_at->format('H:i') }}</div>
                                            </div>
                                        @else
                                            <span style="color: #cbd5e1;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="ml-actions">
                                            <a href="{{ route('admin.messages-log.show', $message->id) }}" class="ml-action-btn view" title="عرض التفاصيل">👁️</a>
                                            <a href="{{ route('admin.messages-log.conversation', $message->conversation_id) }}" class="ml-action-btn chat" title="عرض المحادثة">💬</a>
                                            <form action="{{ route('admin.messages-log.destroy', $message->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ml-action-btn delete" title="حذف">🗑️</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div style="padding: 20px;">
                    {{ $messages->links() }}
                </div>
            @else
                <div class="ml-empty">
                    <div class="ml-empty-icon">📭</div>
                    <h3 style="font-size: 20px; font-weight: 700; color: #475569; margin: 0 0 6px;">لا توجد رسائل</h3>
                    <p style="color: #94a3b8; margin: 0;">لم يتم العثور على رسائل تطابق معايير البحث</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Top Senders -->
    @if($topSenders->count() > 0)
    <div class="ml-card">
        <div class="ml-card-header">
            <span style="font-size: 20px;">🏆</span>
            <h3>أكثر المستخدمين نشاطاً في الإرسال</h3>
        </div>
        <div class="ml-card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
                @foreach($topSenders as $index => $sender)
                    <div class="ml-sender-card">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                            <div class="ml-rank">
                                @if($index === 0) 🥇
                                @elseif($index === 1) 🥈
                                @elseif($index === 2) 🥉
                                @else <span style="font-size: 18px; font-weight: 800; color: #94a3b8;">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1e293b;">{{ $sender->sender->name ?? 'غير معروف' }}</div>
                                <div style="font-size: 12px; color: #94a3b8;">{{ $sender->sender->role ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="ml-sender-count">
                            {{ number_format($sender->message_count) }} رسالة
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Hidden Export Form -->
<form id="exportForm" method="GET" action="{{ route('admin.messages-log.export') }}" style="display: none;">
    <input type="hidden" name="sender_id" value="{{ request('sender_id') }}">
    <input type="hidden" name="receiver_id" value="{{ request('receiver_id') }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <input type="hidden" name="read_status" value="{{ request('read_status') }}">
    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
</form>
@endsection
