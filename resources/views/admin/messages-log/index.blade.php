@extends('layouts.admin')

@section('title', 'سجل الرسائل')

@section('content')
<style>
/* ===== Wahy — سجل الرسائل (سوبر أدمن) — طبقة بصرية فاخرة =====
   كل الأسطح مبنيّة على متغيّرات النظام الموحّد (--w-*) المعرّفة للوضعين (light/dark)
   في partials/theme-toggle، فتعمل التغطية اللونية تلقائياً في الوضعين.
   المرجع القياسي: resources/views/teacher/messages.blade.php */
.ml-page {
    padding: 0;
    --ml-grad: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --ml-grad-soft: linear-gradient(135deg, rgba(102,126,234,0.12), rgba(118,75,162,0.12));
    --ml-grad-tint: linear-gradient(135deg, rgba(102,126,234,0.05) 0%, rgba(118,75,162,0.05) 100%);
    color: var(--w-text, #0f172a);
}

.ml-hero {
    background: var(--ml-grad);
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 28px;
    color: #fff;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.35);
    position: relative;
    overflow: hidden;
}
.ml-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    inset-inline-end: -30%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    border-radius: 50%;
}
.ml-hero-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    flex-wrap: wrap;
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
.ml-hero h1 { font-size: 28px; font-weight: 800; margin: 0 0 6px; color: #fff; }
.ml-hero p { opacity: 0.9; font-size: 15px; margin: 0; }
.ml-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }
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
    color: #fff;
}
.ml-btn-glass:hover { background: rgba(255,255,255,0.3); transform: translateY(-2px); color: #fff; }
.ml-btn-white {
    background: #fff;
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
    background: var(--w-card, #fff);
    border-radius: 16px;
    padding: 22px;
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    box-shadow: var(--w-shadow, 0 10px 40px rgba(2,6,23,0.08));
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 16px;
}
.ml-stat:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(102,126,234,0.16);
    border-color: rgba(102,126,234,0.4);
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
    color: var(--w-text, #0f172a);
    line-height: 1;
}
.ml-stat-label {
    font-size: 13px;
    color: var(--w-text-muted, #475569);
    font-weight: 500;
    margin-top: 4px;
}

/* Cards */
.ml-card {
    background: var(--w-card, #fff);
    border-radius: 18px;
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    box-shadow: var(--w-shadow, 0 10px 40px rgba(2,6,23,0.08));
    margin-bottom: 24px;
    overflow: hidden;
}
.ml-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--w-border, rgba(15,23,42,0.08));
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--ml-grad-tint);
}
.ml-card-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--w-text, #0f172a);
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
    color: var(--w-text-muted, #475569);
    margin-bottom: 6px;
}
.ml-filter-group input,
.ml-filter-group select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 10px;
    font-size: 14px;
    background: var(--w-bg, #f8fafc);
    transition: all 0.3s;
    color: var(--w-text, #0f172a);
}
.ml-filter-group input::placeholder { color: var(--w-text-muted, #94a3b8); }
.ml-filter-group input:focus,
.ml-filter-group select:focus {
    outline: none;
    border-color: #667eea;
    background: var(--w-card, #fff);
    box-shadow: 0 4px 12px rgba(102,126,234,0.15);
}
.ml-filter-actions {
    margin-top: 18px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
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
    background: var(--ml-grad);
    color: #fff;
    box-shadow: 0 4px 14px rgba(102,126,234,0.3);
}
.ml-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }
.ml-btn-secondary {
    background: var(--w-bg, #f1f5f9);
    color: var(--w-text-muted, #475569);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
}
.ml-btn-secondary:hover { background: var(--ml-grad-soft); color: var(--w-text, #0f172a); }

/* Table */
.ml-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.ml-table {
    width: 100%;
    border-collapse: collapse;
}
.ml-table thead th {
    padding: 14px 16px;
    font-size: 13px;
    font-weight: 700;
    color: var(--w-text-muted, #475569);
    text-align: right;
    background: var(--w-bg, #f8fafc);
    border-bottom: 1px solid var(--w-border, rgba(15,23,42,0.08));
    white-space: nowrap;
}
.ml-table tbody tr {
    transition: all 0.2s;
}
.ml-table tbody tr:hover {
    background: var(--ml-grad-tint);
}
.ml-table tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--w-border, rgba(15,23,42,0.08));
    font-size: 14px;
    vertical-align: middle;
    color: var(--w-text, #0f172a);
}

/* Cell helpers */
.ml-id { font-weight: 700; color: #667eea; }
.ml-msg-text {
    max-width: 280px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--w-text-muted, #475569);
}
.ml-datecell { font-size: 13px; }
.ml-datecell .ml-date { font-weight: 600; color: var(--w-text, #0f172a); }
.ml-datecell .ml-time { color: var(--w-text-muted, #94a3b8); }
.ml-dash { color: var(--w-text-muted, #cbd5e1); }

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
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}
.ml-user-name { font-weight: 600; font-size: 14px; color: var(--w-text, #0f172a); }
.ml-user-role { font-size: 11px; color: var(--w-text-muted, #94a3b8); }

/* Badges */
.ml-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.ml-badge-read { background: rgba(34,197,94,0.15); color: #16a34a; }
.ml-badge-unread { background: rgba(245,158,11,0.15); color: #d97706; }

/* Action Buttons */
.ml-actions { display: flex; gap: 6px; }
.ml-action-btn {
    width: 34px; height: 34px;
    border-radius: 8px;
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    background: var(--w-card, #fff);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
    text-decoration: none;
    color: var(--w-text-muted, #475569);
}
.ml-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.ml-action-btn.view:hover { border-color: #667eea; color: #667eea; background: rgba(102,126,234,0.12); }
.ml-action-btn.chat:hover { border-color: #10b981; color: #10b981; background: rgba(16,185,129,0.12); }
.ml-action-btn.delete:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.12); }

/* Top Senders */
.ml-senders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
}
.ml-sender-card {
    padding: 18px;
    background: var(--w-bg, #f8fafc);
    border-radius: 14px;
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    transition: all 0.3s;
}
.ml-sender-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(102,126,234,0.16);
    border-color: rgba(102,126,234,0.4);
}
.ml-sender-head { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.ml-sender-name { font-weight: 700; color: var(--w-text, #0f172a); }
.ml-sender-role { font-size: 12px; color: var(--w-text-muted, #94a3b8); }
.ml-rank {
    font-size: 28px;
    line-height: 1;
}
.ml-rank-num { font-size: 18px; font-weight: 800; color: var(--w-text-muted, #94a3b8); }
.ml-sender-count {
    font-size: 22px;
    font-weight: 800;
    background: var(--ml-grad);
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
    background: var(--ml-grad-soft);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    font-size: 36px;
    margin: 0 auto 16px;
}
.ml-empty h3 { font-size: 20px; font-weight: 700; color: var(--w-text, #0f172a); margin: 0 0 6px; }
.ml-empty p { color: var(--w-text-muted, #94a3b8); margin: 0; }

.ml-pagination { padding: 20px; }

/* ===== الاستجابة ===== */
@media (max-width: 1024px) {
    .ml-hero { padding: 26px; }
    .ml-stats { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; }
    .ml-card-body { padding: 20px; }
}
@media (max-width: 640px) {
    .ml-hero { padding: 22px 18px; border-radius: 16px; }
    .ml-hero-top { flex-direction: column; }
    .ml-hero h1 { font-size: 22px; }
    .ml-hero p { font-size: 13.5px; }
    .ml-hero-actions { width: 100%; }
    .ml-hero-actions .ml-btn { flex: 1; justify-content: center; }

    .ml-stats { grid-template-columns: 1fr 1fr; gap: 12px; }
    .ml-stat { padding: 16px; gap: 12px; }
    .ml-stat-icon { width: 46px; height: 46px; font-size: 21px; }
    .ml-stat-value { font-size: 23px; }

    .ml-card-header { padding: 16px 18px; }
    .ml-card-header h3 { font-size: 16px; }
    .ml-card-body { padding: 18px 14px; }

    .ml-filters-grid { grid-template-columns: 1fr; }
    .ml-filter-actions { flex-direction: column; }
    .ml-filter-actions .ml-btn-action { width: 100%; justify-content: center; }

    .ml-senders-grid { grid-template-columns: 1fr; }
    .ml-pagination { padding: 16px 14px; }
}
@media (max-width: 400px) {
    .ml-stats { grid-template-columns: 1fr; }
}

/* ===== Wahy dark-mode — لمسات صريحة إضافية =====
   الأسطح تعمل أصلاً عبر --w-* في الوضعين؛ هنا فقط تحسينات خاصة بالوضع الليلي. */
html[data-theme="dark"] .ml-id { color: #a5b4fc; }
html[data-theme="dark"] .ml-stat:hover,
html[data-theme="dark"] .ml-sender-card:hover {
    border-color: rgba(129,140,248,0.6) !important;
    box-shadow: 0 12px 30px rgba(0,0,0,0.4) !important;
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
                <div class="ml-table-wrap">
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
                                    <td><span class="ml-id">#{{ $message->id }}</span></td>
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
                                        <div class="ml-msg-text" title="{{ $message->message }}">
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
                                        <div class="ml-datecell">
                                            <div class="ml-date">{{ $message->created_at->format('Y-m-d') }}</div>
                                            <div class="ml-time">{{ $message->created_at->format('H:i') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($message->read_at)
                                            <div class="ml-datecell">
                                                <div class="ml-date">{{ $message->read_at->format('Y-m-d') }}</div>
                                                <div class="ml-time">{{ $message->read_at->format('H:i') }}</div>
                                            </div>
                                        @else
                                            <span class="ml-dash">—</span>
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
                <div class="ml-pagination">
                    {{ $messages->links() }}
                </div>
            @else
                <div class="ml-empty">
                    <div class="ml-empty-icon">📭</div>
                    <h3>لا توجد رسائل</h3>
                    <p>لم يتم العثور على رسائل تطابق معايير البحث</p>
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
            <div class="ml-senders-grid">
                @foreach($topSenders as $index => $sender)
                    <div class="ml-sender-card">
                        <div class="ml-sender-head">
                            <div class="ml-rank">
                                @if($index === 0) 🥇
                                @elseif($index === 1) 🥈
                                @elseif($index === 2) 🥉
                                @else <span class="ml-rank-num">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div class="ml-sender-name">{{ $sender->sender->name ?? 'غير معروف' }}</div>
                                <div class="ml-sender-role">{{ $sender->sender->role ?? '-' }}</div>
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
