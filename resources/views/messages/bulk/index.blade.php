@extends('layouts.admin')

@section('title', 'الرسائل الجماعية')

@section('content')
<style>
/* =========================================================================
   Bulk Messages (System C) — طبقة بصرية فاخرة + وضع ليلي + استجابة.
   الأسطح المحايدة مبنيّة على متغيّرات الثيم var(--w-*) فتعمل في الوضعين تلقائياً
   (المصدر: partials/theme-toggle). اللكنات الوظيفية (أزرق/أخضر/كهرماني/بنفسجي)
   محفوظة كهوية للبث الجماعي. لا أسماء أصناف مُعاد تسميتها.
   ========================================================================= */
.bm-page { padding: 0; }

.bm-hero {
    background: linear-gradient(135deg, #3b82f6 0%, #6366f1 50%, #8b5cf6 100%);
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 28px;
    color: white;
    box-shadow: 0 18px 44px rgba(99, 102, 241, 0.35);
    position: relative;
    overflow: hidden;
}
.bm-hero::before {
    content: '';
    position: absolute;
    top: -40%;
    left: -20%;
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
    border-radius: 50%;
}
.bm-hero::after {
    content: '';
    position: absolute;
    bottom: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.07) 0%, transparent 70%);
    border-radius: 50%;
}
.bm-hero-icon {
    width: 60px; height: 60px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    margin-bottom: 14px;
    position: relative; z-index: 1;
}
.bm-hero h1 { font-size: 28px; font-weight: 800; margin: 0 0 6px; position: relative; z-index: 1; }
.bm-hero p { opacity: 0.9; font-size: 15px; margin: 0; position: relative; z-index: 1; }
.bm-hero-actions { position: relative; z-index: 1; margin-top: 18px; display: flex; flex-wrap: wrap; gap: 10px; }
.bm-hero-btn {
    padding: 10px 24px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s, background 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.bm-hero-btn-primary { background: white; color: #6366f1; box-shadow: 0 4px 14px rgba(0,0,0,0.15); }
.bm-hero-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); color: #6366f1; }
.bm-hero-btn-outline { background: rgba(255,255,255,0.15); color: white; border: 2px solid rgba(255,255,255,0.3); }
.bm-hero-btn-outline:hover { background: rgba(255,255,255,0.25); color: white; transform: translateY(-2px); }

/* Stats Grid */
.bm-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 18px;
    margin-bottom: 28px;
}
.bm-stat {
    background: var(--w-card, #fff);
    border-radius: 18px;
    padding: 22px;
    border: 1px solid var(--w-border, #f1f5f9);
    box-shadow: 0 10px 30px rgba(2, 6, 23, 0.05);
    transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
    display: flex;
    align-items: center;
    gap: 18px;
}
.bm-stat:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(2, 6, 23, 0.10);
    border-color: rgba(99, 102, 241, 0.35);
}
.bm-stat-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.bm-stat-icon i { font-size: 24px; color: white; }
.bm-stat-value {
    font-size: 30px;
    font-weight: 800;
    color: var(--w-text, #1e293b);
    line-height: 1;
}
.bm-stat-label {
    font-size: 13px;
    color: var(--w-text-muted, #64748b);
    font-weight: 500;
    margin-top: 4px;
}

/* Card */
.bm-card {
    background: var(--w-card, #fff);
    border-radius: 20px;
    border: 1px solid var(--w-border, #f1f5f9);
    box-shadow: 0 10px 40px rgba(2, 6, 23, 0.08);
    overflow: hidden;
}
.bm-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--w-border, #f1f5f9);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    background: linear-gradient(135deg, rgba(59,130,246,0.05) 0%, rgba(139,92,246,0.05) 100%);
}
.bm-card-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--w-text, #1e293b);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Table */
.bm-table-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.bm-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 720px;
}
.bm-table thead th {
    padding: 14px 16px;
    font-size: 13px;
    font-weight: 700;
    color: var(--w-text-muted, #475569);
    text-align: right;
    background: var(--w-bg, #f8fafc);
    border-bottom: 1px solid var(--w-border, #e2e8f0);
    white-space: nowrap;
}
.bm-table tbody tr {
    transition: background 0.2s;
}
.bm-table tbody tr:hover {
    background: linear-gradient(135deg, rgba(59,130,246,0.05) 0%, rgba(139,92,246,0.05) 100%);
}
.bm-table tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--w-border, #f1f5f9);
    font-size: 14px;
    vertical-align: middle;
    color: var(--w-text, #1e293b);
}

/* Badge */
.bm-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}
.bm-badge-primary { background: linear-gradient(135deg, #dbeafe, #e0e7ff); color: #3b4ec4; }
.bm-badge-success { background: linear-gradient(135deg, #dcfce7, #d1fae5); color: #166534; }
.bm-badge-warning { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
.bm-badge-info { background: linear-gradient(135deg, #cffafe, #e0f2fe); color: #155e75; }
.bm-badge-danger { background: linear-gradient(135deg, #fce4ec, #fecdd3); color: #991b1b; }
.bm-badge-secondary { background: #f1f5f9; color: #64748b; }

/* Progress */
.bm-progress-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.bm-progress-bar {
    flex: 1;
    height: 8px;
    background: rgba(100, 116, 139, 0.16);
    border-radius: 10px;
    overflow: hidden;
    min-width: 80px;
}
.bm-progress-fill {
    height: 100%;
    border-radius: 10px;
    transition: width 0.6s ease;
    background: linear-gradient(135deg, #10b981, #059669);
}

/* Date */
.bm-date {
    font-size: 13px;
    color: var(--w-text-muted, #94a3b8);
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Action Buttons */
.bm-actions { display: flex; gap: 6px; }
.bm-action-btn {
    width: 36px; height: 36px;
    border-radius: 10px;
    border: 1px solid var(--w-border, #e2e8f0);
    background: var(--w-card, #fff);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s, background 0.2s, color 0.2s;
    text-decoration: none;
    color: var(--w-text-muted, #475569);
}
.bm-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(2, 6, 23, 0.12);
}
.bm-action-btn.view:hover { border-color: #6366f1; color: #6366f1; background: rgba(99, 102, 241, 0.12); }
.bm-action-btn i { font-size: 13px; }

/* Empty State */
.bm-empty {
    text-align: center;
    padding: 70px 20px;
}
.bm-empty-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, #dbeafe, #e0e7ff);
    border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    font-size: 40px;
    margin: 0 auto 18px;
    box-shadow: 0 6px 20px rgba(99,102,241,0.15);
}
.bm-empty h3 { color: var(--w-text, #475569); }
.bm-empty p { color: var(--w-text-muted, #94a3b8); }

/* Alert */
.bm-alert {
    padding: 14px 20px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.bm-alert-success { background: linear-gradient(135deg, #dcfce7, #d1fae5); color: #166534; border: 1px solid #bbf7d0; }
.bm-alert-danger { background: linear-gradient(135deg, #fce4ec, #fecdd3); color: #991b1b; border: 1px solid #fca5a5; }

/* Modal */
.bm-modal .modal-content {
    border-radius: 18px;
    overflow: hidden;
    border: none;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}
.bm-modal-card {
    background: var(--w-card, #fff);
    box-shadow: 0 25px 50px rgba(2, 6, 23, 0.35);
}
.bm-modal-header {
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: white;
    padding: 20px 24px;
}
.bm-modal-header h5 { font-weight: 700; margin: 0; color: white; }
.bm-modal-header .btn-close { filter: brightness(0) invert(1); }
.bm-modal-body { padding: 24px; }
.bm-modal-body .bm-msg-meta {
    display: flex; gap: 16px; flex-wrap: wrap;
    padding: 14px 18px;
    background: rgba(100, 116, 139, 0.10);
    border-radius: 12px;
    margin-bottom: 16px;
}
.bm-modal-body .bm-msg-content {
    padding: 18px;
    background: rgba(100, 116, 139, 0.10);
    border-radius: 12px;
    line-height: 1.8;
    color: var(--w-text, #334155);
    white-space: pre-wrap;
}

/* Subject cell */
.bm-subject {
    font-weight: 700;
    color: var(--w-text, #1e293b);
    font-size: 14px;
}
.bm-subject-school {
    font-size: 12px;
    color: var(--w-text-muted, #94a3b8);
    margin-top: 3px;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* =========================================================================
   الوضع الليلي — الشارات/التنبيهات الوظيفية (نسخ شفّافة ملوّنة بنص فاتح، بدل
   الجزر الباستيل الفاتحة على السطح الداكن). بقيّة الأسطح تُدار عبر var(--w-*).
   ========================================================================= */
html[data-theme="dark"] .bm-badge-primary   { background: rgba(99, 102, 241, 0.18); color: #c7d2fe; }
html[data-theme="dark"] .bm-badge-success   { background: rgba(16, 185, 129, 0.16); color: #6ee7b7; }
html[data-theme="dark"] .bm-badge-warning   { background: rgba(245, 158, 11, 0.16); color: #fcd34d; }
html[data-theme="dark"] .bm-badge-info      { background: rgba(6, 182, 212, 0.18); color: #67e8f9; }
html[data-theme="dark"] .bm-badge-danger    { background: rgba(239, 68, 68, 0.16); color: #fca5a5; }
html[data-theme="dark"] .bm-badge-secondary { background: rgba(148, 163, 184, 0.16); color: #cbd5e1; }
html[data-theme="dark"] .bm-alert-success   { background: rgba(16, 185, 129, 0.14); color: #6ee7b7; border-color: rgba(16, 185, 129, 0.35); }
html[data-theme="dark"] .bm-alert-danger    { background: rgba(239, 68, 68, 0.14); color: #fca5a5; border-color: rgba(239, 68, 68, 0.35); }
html[data-theme="dark"] .bm-empty-icon {
    background: rgba(99, 102, 241, 0.16);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

/* =========================================================================
   الاستجابة — لابتوب (افتراضي) / تابلت 641–1024 / جوال ≤640.
   الجدول يبقى دائماً ضمن .bm-table-scroll (overflow-x:auto) + min-width للتمرير.
   ========================================================================= */
@media (max-width: 1024px) {
    .bm-hero { padding: 26px; }
    .bm-stats { gap: 14px; }
}
@media (min-width: 641px) and (max-width: 1024px) {
    .bm-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .bm-hero { padding: 22px; border-radius: 16px; margin-bottom: 20px; }
    .bm-hero h1 { font-size: 22px; }
    .bm-hero p { font-size: 13px; }
    .bm-hero-icon { width: 52px; height: 52px; font-size: 24px; margin-bottom: 12px; }
    .bm-hero-actions { flex-direction: column; align-items: stretch; }
    .bm-hero-btn { justify-content: center; width: 100%; }
    .bm-stats { grid-template-columns: 1fr; gap: 12px; margin-bottom: 20px; }
    .bm-stat { padding: 18px; }
    .bm-stat-value { font-size: 26px; }
    .bm-card-header { padding: 16px 18px; }
    .bm-card-header h3 { font-size: 16px; }
    .bm-table thead th, .bm-table tbody td { padding: 12px 12px; }
    .bm-modal-body { padding: 18px; }
    .bm-modal-body .bm-msg-meta { gap: 12px; padding: 12px 14px; }
    .bm-empty { padding: 48px 16px; }
}
</style>

<div class="bm-page">
    <!-- Hero Header -->
    <div class="bm-hero">
        <div class="bm-hero-icon">📨</div>
        <h1>الرسائل الجماعية</h1>
        <p>إدارة وإرسال الرسائل الجماعية لجميع المستخدمين</p>
        <div class="bm-hero-actions">
            <a href="{{ route('messages.bulk.create') }}" class="bm-hero-btn bm-hero-btn-primary">
                <i class="fas fa-plus"></i> إرسال رسالة جديدة
            </a>
            <a href="{{ route('messages.bulk.inbox') }}" class="bm-hero-btn bm-hero-btn-outline">
                <i class="fas fa-inbox"></i> صندوق الوارد
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bm-alert bm-alert-success">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bm-alert bm-alert-danger">
            ❌ {{ session('error') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="bm-stats">
        <div class="bm-stat">
            <div class="bm-stat-icon" style="background: linear-gradient(135deg, #3b82f6, #6366f1); box-shadow: 0 6px 16px rgba(99,102,241,0.3);">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div>
                <div class="bm-stat-value">{{ $stats['total_sent'] }}</div>
                <div class="bm-stat-label">الرسائل المرسلة</div>
            </div>
        </div>
        <div class="bm-stat">
            <div class="bm-stat-icon" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 16px rgba(16,185,129,0.3);">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="bm-stat-value">{{ $stats['total_recipients'] }}</div>
                <div class="bm-stat-label">إجمالي المستلمين</div>
            </div>
        </div>
        <div class="bm-stat">
            <div class="bm-stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 6px 16px rgba(245,158,11,0.3);">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <div>
                <div class="bm-stat-value">{{ $stats['total_read'] }}</div>
                <div class="bm-stat-label">تم القراءة</div>
            </div>
        </div>
        <div class="bm-stat">
            <div class="bm-stat-icon" style="background: linear-gradient(135deg, #ec4899, #db2777); box-shadow: 0 6px 16px rgba(236,72,153,0.3);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <div class="bm-stat-value">{{ $stats['this_month'] }}</div>
                <div class="bm-stat-label">هذا الشهر</div>
            </div>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="bm-card">
        <div class="bm-card-header">
            <h3><span style="font-size: 20px;">📋</span> سجل الرسائل المرسلة</h3>
            <span class="bm-badge bm-badge-info">{{ $sentMessages->total() }} رسالة</span>
        </div>
        <div>
            @if($sentMessages->count() > 0)
                <div class="bm-table-scroll">
                    <table class="bm-table">
                        <thead>
                            <tr>
                                <th>الموضوع</th>
                                <th>نوع المستلمين</th>
                                <th>عدد المستلمين</th>
                                <th>تاريخ الإرسال</th>
                                <th>نسبة القراءة</th>
                                <th style="width: 80px;">عرض</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sentMessages as $message)
                                <tr>
                                    <td>
                                        <div class="bm-subject">{{ $message->subject }}</div>
                                        @if($message->school)
                                            <div class="bm-subject-school">
                                                <i class="fas fa-school"></i>
                                                {{ $message->school->name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="bm-badge bm-badge-{{ $message->recipient_type_badge }}">
                                            {{ $message->recipient_type_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="bm-badge bm-badge-secondary">
                                            <i class="fas fa-users" style="font-size: 11px;"></i>
                                            {{ $message->recipients->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="bm-date">
                                            <i class="far fa-clock"></i>
                                            {{ $message->sent_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $readCount = $message->recipients->where('read_at', '!=', null)->count();
                                            $totalCount = $message->recipients->count();
                                            $percentage = $totalCount > 0 ? round(($readCount / $totalCount) * 100) : 0;
                                        @endphp
                                        <div class="bm-progress-wrap">
                                            <small style="font-weight: 600; color: #475569; white-space: nowrap;">{{ $readCount }}/{{ $totalCount }}</small>
                                            <div class="bm-progress-bar">
                                                <div class="bm-progress-fill" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="bm-actions">
                                            <button class="bm-action-btn view"
                                                    type="button"
                                                    onclick="bmShowModal({{ $message->id }})"
                                                    title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- View Modal (vanilla — لا يعتمد على Bootstrap JS) -->
                                <div class="bm-modal-backdrop" id="viewModal{{ $message->id }}" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:1050; align-items:center; justify-content:center; padding:20px;">
                                    <div class="bm-modal-card" style="border-radius:16px; max-width:720px; width:100%; max-height:90vh; overflow:auto;">
                                        <div class="bm-modal-content">
                                            <div class="bm-modal-header" style="display:flex; justify-content:space-between; align-items:center; padding:18px 24px; border-bottom:1px solid rgba(255,255,255,0.18);">
                                                <h5 style="margin:0;">📨 {{ $message->subject }}</h5>
                                                <button type="button" onclick="bmHideModal({{ $message->id }})" style="background:transparent; border:none; font-size:22px; cursor:pointer; color:#64748b;">✕</button>
                                            </div>
                                            <div class="bm-modal-body">
                                                <div class="bm-msg-meta">
                                                    <div>
                                                        <small style="color: #94a3b8;">نوع المستلمين</small><br>
                                                        <span class="bm-badge bm-badge-{{ $message->recipient_type_badge }}">{{ $message->recipient_type_label }}</span>
                                                    </div>
                                                    @if($message->school)
                                                    <div>
                                                        <small style="color: #94a3b8;">المدرسة</small><br>
                                                        <strong style="color: #1e293b;">{{ $message->school->name }}</strong>
                                                    </div>
                                                    @endif
                                                    <div>
                                                        <small style="color: #94a3b8;">عدد المستلمين</small><br>
                                                        <strong style="color: #1e293b;">{{ $message->recipients->count() }}</strong>
                                                    </div>
                                                    <div>
                                                        <small style="color: #94a3b8;">تاريخ الإرسال</small><br>
                                                        <strong style="color: #1e293b;">{{ $message->sent_at->format('Y-m-d H:i') }}</strong>
                                                    </div>
                                                    <div>
                                                        <small style="color: #94a3b8;">نسبة القراءة</small><br>
                                                        <strong style="color: #10b981;">{{ $percentage }}%</strong>
                                                    </div>
                                                </div>
                                                <h6 style="font-weight: 700; color: #475569; margin-bottom: 10px;">📝 نص الرسالة</h6>
                                                <div class="bm-msg-content">{{ $message->message }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div style="padding: 20px; border-top: 1px solid var(--w-border, #f1f5f9);">
                    {{ $sentMessages->links() }}
                </div>
            @else
                <div class="bm-empty">
                    <div class="bm-empty-icon">📨</div>
                    <h3 style="font-size: 20px; font-weight: 700; color: #475569; margin: 0 0 6px;">لم يتم إرسال أي رسائل بعد</h3>
                    <p style="color: #94a3b8; margin: 0 0 20px; font-size: 14px;">ابدأ بإرسال رسالتك الجماعية الأولى</p>
                    <a href="{{ route('messages.bulk.create') }}" class="bm-hero-btn bm-hero-btn-primary" style="display: inline-flex;">
                        <i class="fas fa-plus"></i> إرسال رسالة جديدة
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function bmShowModal(id) {
        const el = document.getElementById('viewModal' + id);
        if (el) { el.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
    }
    function bmHideModal(id) {
        const el = document.getElementById('viewModal' + id);
        if (el) { el.style.display = 'none'; document.body.style.overflow = ''; }
    }
    // إغلاق عند الضغط على الخلفية أو زر Escape
    document.addEventListener('click', (e) => {
        if (e.target.classList && e.target.classList.contains('bm-modal-backdrop')) {
            e.target.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.bm-modal-backdrop').forEach(m => m.style.display = 'none');
            document.body.style.overflow = '';
        }
    });
</script>
@endsection
