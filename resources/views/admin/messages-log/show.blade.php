@extends('layouts.admin')

@section('title', 'تفاصيل الرسالة')

@push('styles')
<style>
/* ===== Wahy — تفاصيل رسالة (سوبر أدمن) — طبقة بصرية فاخرة =====
   كل الأسطح مبنيّة على متغيّرات النظام الموحّد (--w-*) المعرّفة للوضعين (light/dark)
   في partials/theme-toggle، فتعمل التغطية اللونية تلقائياً في الوضعين. لا فاتح-على-فاتح. */
.mlog {
    --mlog-grad: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --mlog-grad-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    display: flex;
    flex-direction: column;
    gap: 20px;
    color: var(--w-text, #0f172a);
}

/* ===== الهيدر ===== */
.mlog-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.mlog-head-titles { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.mlog-title {
    font-size: 24px; font-weight: 800; margin: 0;
    color: var(--w-text, #0f172a);
    display: flex; align-items: center; gap: 10px;
}
.mlog-subtitle { font-size: 13.5px; color: var(--w-text-muted, #475569); margin: 0; }
.mlog-head-actions { display: flex; gap: 10px; flex-wrap: wrap; }

/* ===== بطاقات ===== */
.mlog-card {
    background: var(--w-card, #fff);
    color: var(--w-text, #0f172a);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 18px;
    box-shadow: var(--w-shadow, 0 10px 40px rgba(2,6,23,0.08));
    overflow: hidden;
}
.mlog-card-head {
    padding: 16px 22px;
    border-bottom: 1px solid var(--w-border, rgba(15,23,42,0.08));
}
.mlog-card-head h3 {
    margin: 0; font-size: 17px; font-weight: 800;
    color: var(--w-text, #0f172a);
    display: flex; align-items: center; gap: 8px;
}
.mlog-card-body { padding: 22px; }

/* ===== شبكة الأطراف (مُرسِل / مُستقبِل / الحالة) ===== */
.mlog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
}
.mlog-party {
    padding: 18px;
    border-radius: 14px;
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-inline-start: 4px solid var(--mlog-accent, #667eea);
    background: var(--mlog-tint, rgba(102,126,234,0.10));
}
.mlog-party--sender   { --mlog-accent: #667eea; --mlog-tint: rgba(102,126,234,0.10); }
.mlog-party--receiver { --mlog-accent: #f093fb; --mlog-tint: rgba(240,147,251,0.10); }
.mlog-party--status   { --mlog-accent: #43e97b; --mlog-tint: rgba(67,233,123,0.10); }

.mlog-party-label {
    font-size: 12px; font-weight: 700; letter-spacing: 0.2px;
    color: var(--w-text-muted, #475569);
    margin-bottom: 10px;
}
.mlog-person { display: flex; align-items: center; gap: 12px; }
.mlog-avatar {
    flex-shrink: 0;
    width: 48px; height: 48px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 800; font-size: 18px; line-height: 1;
    box-shadow: 0 6px 16px rgba(102,126,234,0.30);
}
.mlog-avatar--a { background: var(--mlog-grad); }
.mlog-avatar--b { background: var(--mlog-grad-2); box-shadow: 0 6px 16px rgba(240,147,251,0.30); }
.mlog-person-info { min-width: 0; }
.mlog-person-name { font-weight: 700; font-size: 16px; color: var(--w-text, #0f172a); }
.mlog-person-email { font-size: 13px; color: var(--w-text-muted, #475569); word-break: break-word; }
.mlog-role {
    display: inline-block; margin-top: 4px;
    color: #fff; padding: 2px 10px; border-radius: 999px;
    font-size: 12px; font-weight: 600;
}
.mlog-role--a { background: #667eea; }
.mlog-role--b { background: #f093fb; }

/* ===== شارات الحالة ===== */
.mlog-status-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 999px; font-weight: 700; font-size: 13.5px;
    border: 1px solid transparent;
    margin-bottom: 12px;
}
.mlog-status-pill .ic { font-size: 17px; line-height: 1; }
.mlog-status-pill.is-read   { background: rgba(34,197,94,0.15);  color: #15803d; border-color: rgba(34,197,94,0.35); }
.mlog-status-pill.is-unread { background: rgba(245,158,11,0.15); color: #b45309; border-color: rgba(245,158,11,0.35); }

.mlog-times { font-size: 13px; color: var(--w-text-muted, #475569); line-height: 1.6; }
.mlog-times strong { color: var(--w-text, #0f172a); }
.mlog-times > div + div { margin-top: 8px; }

/* ===== محتوى الرسالة ===== */
.mlog-content {
    padding: 20px;
    background: var(--w-bg, #f8fafc);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-inline-start: 4px solid #667eea;
    border-radius: 12px;
    min-height: 100px;
    white-space: pre-wrap;
    line-height: 1.7;
    color: var(--w-text, #0f172a);
    word-wrap: break-word; overflow-wrap: anywhere;
}

/* ===== سياق المحادثة ===== */
.mlog-thread {
    max-height: 600px; overflow-y: auto;
    display: flex; flex-direction: column; gap: 12px;
    padding-inline-end: 4px;
}
.mlog-msg {
    padding: 15px;
    border-radius: 12px;
    background: var(--w-bg, #f8fafc);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
}
.mlog-msg.is-current {
    background: rgba(245,158,11,0.12);
    border: 2px solid #f59e0b;
}
.mlog-msg-head {
    display: flex; justify-content: space-between; align-items: flex-start;
    gap: 10px; margin-bottom: 10px;
}
.mlog-msg-who { display: flex; align-items: center; gap: 10px; min-width: 0; }
.mlog-msg-avatar {
    flex-shrink: 0;
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: 14px; line-height: 1;
}
.mlog-msg-avatar--a { background: var(--mlog-grad); }
.mlog-msg-avatar--b { background: var(--mlog-grad-2); }
.mlog-msg-name { font-weight: 700; color: var(--w-text, #0f172a); }
.mlog-msg-time { font-size: 12px; color: var(--w-text-muted, #475569); }
.mlog-msg-tags { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
.mlog-tag-current {
    background: #f59e0b; color: #fff; padding: 4px 10px; border-radius: 999px;
    font-size: 11px; font-weight: 700; white-space: nowrap;
}
.mlog-check { font-size: 18px; line-height: 1; }
.mlog-check.ok  { color: #22c55e; }
.mlog-check.wait { color: #f59e0b; }
.mlog-msg-body {
    padding-inline-start: 46px;
    white-space: pre-wrap; line-height: 1.6;
    color: var(--w-text, #0f172a);
    word-wrap: break-word; overflow-wrap: anywhere;
}

/* ===== أزرار الإجراءات السفلية ===== */
.mlog-actions {
    margin-top: 4px;
    display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;
}
.mlog-actions form { display: inline; margin: 0; }

/* ===== الاستجابة ===== */
@media (max-width: 1024px) {
    .mlog-card-body { padding: 18px; }
    .mlog-grid { gap: 14px; }
}
@media (max-width: 640px) {
    .mlog { gap: 16px; }
    .mlog-title { font-size: 20px; }
    .mlog-head-actions { width: 100%; }
    .mlog-head-actions .btn { flex: 1; justify-content: center; text-align: center; }
    .mlog-card-head { padding: 14px 16px; }
    .mlog-card-body { padding: 16px; }
    .mlog-grid { grid-template-columns: 1fr; }
    .mlog-party { padding: 15px; }
    .mlog-content { padding: 16px; }
    .mlog-msg-body { padding-inline-start: 0; margin-top: 4px; }
    .mlog-thread { max-height: 460px; }
    .mlog-actions { flex-direction: column; }
    .mlog-actions .btn, .mlog-actions form, .mlog-actions form .btn { width: 100%; justify-content: center; }
}

/* ===== لمسات خاصة بالوضع الليلي (الأسطح تعمل أصلاً عبر --w-*) ===== */
html[data-theme="dark"] .mlog-status-pill.is-read   { color: #4ade80; }
html[data-theme="dark"] .mlog-status-pill.is-unread { color: #fbbf24; }
html[data-theme="dark"] .mlog-person-email,
html[data-theme="dark"] .mlog-msg-time,
html[data-theme="dark"] .mlog-times { color: var(--w-text-muted) !important; }
</style>
@endpush

@section('content')
<div class="admin-container">
    <div class="mlog">
        <!-- Header -->
        <div class="mlog-head">
            <div class="mlog-head-titles">
                <h1 class="mlog-title">📨 تفاصيل الرسالة #{{ $message->id }}</h1>
                <p class="mlog-subtitle">عرض تفاصيل الرسالة والسياق الكامل للمحادثة</p>
            </div>
            <div class="mlog-head-actions">
                <a href="{{ route('admin.messages-log.conversation', $message->conversation_id) }}" class="btn btn-secondary">
                    💬 عرض المحادثة الكاملة
                </a>
                <a href="{{ route('admin.messages-log.index') }}" class="btn btn-primary">
                    ← العودة للقائمة
                </a>
            </div>
        </div>

        <!-- Message Details Card -->
        <div class="mlog-card">
            <div class="mlog-card-head">
                <h3>📋 معلومات الرسالة</h3>
            </div>
            <div class="mlog-card-body">
                <div class="mlog-grid">
                    <!-- Sender Info -->
                    <div class="mlog-party mlog-party--sender">
                        <div class="mlog-party-label">المرسل</div>
                        <div class="mlog-person">
                            <div class="mlog-avatar mlog-avatar--a">
                                {{ mb_substr($message->sender->name ?? 'غ', 0, 1) }}
                            </div>
                            <div class="mlog-person-info">
                                <div class="mlog-person-name">{{ $message->sender->name ?? 'غير معروف' }}</div>
                                <div class="mlog-person-email">{{ $message->sender->email ?? '-' }}</div>
                                <span class="mlog-role mlog-role--a">{{ $message->sender->role ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Receiver Info -->
                    <div class="mlog-party mlog-party--receiver">
                        <div class="mlog-party-label">المستقبل</div>
                        <div class="mlog-person">
                            <div class="mlog-avatar mlog-avatar--b">
                                {{ mb_substr($message->receiver->name ?? 'غ', 0, 1) }}
                            </div>
                            <div class="mlog-person-info">
                                <div class="mlog-person-name">{{ $message->receiver->name ?? 'غير معروف' }}</div>
                                <div class="mlog-person-email">{{ $message->receiver->email ?? '-' }}</div>
                                <span class="mlog-role mlog-role--b">{{ $message->receiver->role ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Info -->
                    <div class="mlog-party mlog-party--status">
                        <div class="mlog-party-label">حالة الرسالة</div>
                        @if($message->is_read)
                            <span class="mlog-status-pill is-read">
                                <span class="ic">✓</span> مقروءة
                            </span>
                        @else
                            <span class="mlog-status-pill is-unread">
                                <span class="ic">⏳</span> غير مقروءة
                            </span>
                        @endif
                        <div class="mlog-times">
                            <div>
                                <strong>تاريخ الإرسال:</strong><br>
                                {{ $message->created_at->format('Y-m-d H:i:s') }}
                            </div>
                            @if($message->read_at)
                                <div>
                                    <strong>تاريخ القراءة:</strong><br>
                                    {{ $message->read_at->format('Y-m-d H:i:s') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Content -->
        <div class="mlog-card">
            <div class="mlog-card-head">
                <h3>💬 محتوى الرسالة</h3>
            </div>
            <div class="mlog-card-body">
                <div class="mlog-content">{{ $message->message }}</div>
            </div>
        </div>

        <!-- Conversation Context -->
        <div class="mlog-card">
            <div class="mlog-card-head">
                <h3>🔄 سياق المحادثة ({{ $conversationMessages->count() }} رسالة)</h3>
            </div>
            <div class="mlog-card-body">
                <div class="mlog-thread">
                    @foreach($conversationMessages as $msg)
                        <div class="mlog-msg {{ $msg->id === $message->id ? 'is-current' : '' }}">
                            <div class="mlog-msg-head">
                                <div class="mlog-msg-who">
                                    <div class="mlog-msg-avatar {{ $msg->sender_id === $message->sender_id ? 'mlog-msg-avatar--a' : 'mlog-msg-avatar--b' }}">
                                        {{ mb_substr($msg->sender->name ?? 'غ', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="mlog-msg-name">{{ $msg->sender->name ?? 'غير معروف' }}</div>
                                        <div class="mlog-msg-time">{{ $msg->created_at->format('Y-m-d H:i') }}</div>
                                    </div>
                                </div>
                                <div class="mlog-msg-tags">
                                    @if($msg->id === $message->id)
                                        <span class="mlog-tag-current">الرسالة الحالية</span>
                                    @endif
                                    @if($msg->is_read)
                                        <span class="mlog-check ok" title="مقروءة">✓</span>
                                    @else
                                        <span class="mlog-check wait" title="غير مقروءة">⏳</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mlog-msg-body">{{ $msg->message }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mlog-actions">
            <a href="{{ route('admin.messages-log.conversation', $message->conversation_id) }}" class="btn btn-primary">
                💬 عرض المحادثة الكاملة
            </a>
            <a href="{{ route('admin.messages-log.index') }}" class="btn btn-secondary">
                📋 العودة للسجل
            </a>
            <form action="{{ route('admin.messages-log.destroy', $message->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة؟ هذا الإجراء لا يمكن التراجع عنه.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    🗑️ حذف الرسالة
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
