@extends('layouts.admin')

@section('title', 'عرض المحادثة')

@section('content')
{{-- ===== Wahy — عرض محادثة (مراقبة السوبر أدمن) — طبقة بصرية فاخرة =====
     كل الأسطح مبنيّة على متغيّرات النظام الموحّد (--w-*) المعرّفة للوضعين (light/dark)
     في partials/theme-toggle، فتعمل التغطية اللونية تلقائياً في الوضعين.
     الحفاظ: كل المسارات ومتغيّرات blade ومنطق sender_id===user1->id وإدراج النص
     عبر {{ }} (هروب Blade) محفوظة — التغيير بصري فقط. --}}
<div class="admin-container">
    <div class="mlog-shell">

        {{-- ===== الهيدر ===== --}}
        <div class="mlog-topbar">
            <div class="mlog-topbar-titles">
                <h1 class="mlog-title">💬 محادثة {{ $conversation->user1->name }} و {{ $conversation->user2->name }}</h1>
                <p class="mlog-sub">مراقبة كاملة لجميع رسائل هذه المحادثة</p>
            </div>
            <a href="{{ route('admin.messages-log.index') }}" class="mlog-back">→ العودة للسجل</a>
        </div>

        {{-- ===== إحصائيات المحادثة ===== --}}
        <div class="mlog-stats">
            <div class="mlog-stat">
                <div class="mlog-stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">💬</div>
                <div class="mlog-stat-body">
                    <div class="mlog-stat-value">{{ $stats['total_messages'] }}</div>
                    <div class="mlog-stat-label">إجمالي الرسائل</div>
                </div>
            </div>
            <div class="mlog-stat">
                <div class="mlog-stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">📬</div>
                <div class="mlog-stat-body">
                    <div class="mlog-stat-value">{{ $stats['unread_messages'] }}</div>
                    <div class="mlog-stat-label">رسائل غير مقروءة</div>
                </div>
            </div>
            <div class="mlog-stat">
                <div class="mlog-stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">📅</div>
                <div class="mlog-stat-body">
                    <div class="mlog-stat-value">{{ $stats['first_message_date'] ? $stats['first_message_date']->format('Y-m-d') : '-' }}</div>
                    <div class="mlog-stat-label">أول رسالة</div>
                </div>
            </div>
            <div class="mlog-stat">
                <div class="mlog-stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">🕐</div>
                <div class="mlog-stat-body">
                    <div class="mlog-stat-value">{{ $stats['last_message_date'] ? $stats['last_message_date']->format('Y-m-d') : '-' }}</div>
                    <div class="mlog-stat-label">آخر رسالة</div>
                </div>
            </div>
        </div>

        {{-- ===== المشاركون ===== --}}
        <div class="mlog-people">
            {{-- User 1 --}}
            <div class="mlog-person mlog-person--u1">
                <div class="mlog-person-top">
                    <div class="mlog-person-avatar mlog-av-u1">{{ mb_substr($conversation->user1->name, 0, 1, "UTF-8") }}</div>
                    <div class="mlog-person-info">
                        <div class="mlog-person-name">{{ $conversation->user1->name }}</div>
                        <div class="mlog-person-email">{{ $conversation->user1->email }}</div>
                        <span class="mlog-role mlog-role--u1">{{ $conversation->user1->role }}</span>
                    </div>
                </div>
                <div class="mlog-person-foot">
                    <strong>الرسائل المرسلة:</strong> {{ $messages->where('sender_id', $conversation->user1->id)->count() }}
                </div>
            </div>

            {{-- User 2 --}}
            <div class="mlog-person mlog-person--u2">
                <div class="mlog-person-top">
                    <div class="mlog-person-avatar mlog-av-u2">{{ mb_substr($conversation->user2->name, 0, 1, "UTF-8") }}</div>
                    <div class="mlog-person-info">
                        <div class="mlog-person-name">{{ $conversation->user2->name }}</div>
                        <div class="mlog-person-email">{{ $conversation->user2->email }}</div>
                        <span class="mlog-role mlog-role--u2">{{ $conversation->user2->role }}</span>
                    </div>
                </div>
                <div class="mlog-person-foot">
                    <strong>الرسائل المرسلة:</strong> {{ $messages->where('sender_id', $conversation->user2->id)->count() }}
                </div>
            </div>
        </div>

        {{-- ===== لوحة المحادثة ===== --}}
        <div class="mlog-chat-panel">
            <div class="mlog-chat-head">
                <span class="mlog-chat-head-title">📜 سجل الرسائل</span>
                <span class="mlog-chat-count">{{ $messages->count() }}</span>
            </div>

            <div class="mlog-chat">
                @if($messages->count() > 0)
                    @foreach($messages as $message)
                        @php $isUser1 = $message->sender_id === $conversation->user1->id; @endphp
                        <div class="mlog-row {{ $isUser1 ? 'is-u1' : 'is-u2' }}">
                            {{-- Avatar --}}
                            <div class="mlog-msg-avatar {{ $isUser1 ? 'mlog-av-u1' : 'mlog-av-u2' }}">
                                {{ mb_substr($message->sender->name, 0, 1, "UTF-8") }}
                            </div>

                            {{-- Message --}}
                            <div class="mlog-msg">
                                <div class="mlog-msg-meta">
                                    <span class="mlog-msg-name">{{ $message->sender->name }}</span>
                                    <span class="mlog-msg-time">{{ $message->created_at->format('Y-m-d H:i') }}</span>
                                    @if($message->is_read)
                                        <span class="mlog-tick read" title="مقروءة">✓✓</span>
                                    @else
                                        <span class="mlog-tick" title="غير مقروءة">✓</span>
                                    @endif
                                </div>
                                <div class="mlog-bubble {{ $isUser1 ? 'sent' : 'recv' }}">{{ $message->message }}</div>
                                <a href="{{ route('admin.messages-log.show', $message->id) }}" class="mlog-detail">عرض التفاصيل →</a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="mlog-empty">
                        <div class="mlog-empty-icon">📭</div>
                        <p>لا توجد رسائل في هذه المحادثة</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<style>
/* ===== Wahy — عرض محادثة الأدمن — أسطح مبنيّة على --w-* (تعمل للوضعين) ===== */
.mlog-shell {
    --mlog-grad: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --mlog-pink: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    max-width: 980px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ===== الهيدر ===== */
.mlog-topbar {
    display: flex; justify-content: space-between; align-items: center;
    gap: 16px; flex-wrap: wrap;
}
.mlog-topbar-titles { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.mlog-title {
    font-size: 23px; font-weight: 800; margin: 0; line-height: 1.35;
    color: var(--w-text, #0f172a);
}
.mlog-sub { font-size: 13.5px; margin: 0; color: var(--w-text-muted, #475569); }
.mlog-back {
    background: var(--mlog-grad); color: #fff; text-decoration: none;
    padding: 11px 20px; border-radius: 12px; font-weight: 700; font-size: 14px;
    white-space: nowrap;
    box-shadow: 0 6px 18px rgba(102,126,234,0.35);
    transition: transform 0.2s, box-shadow 0.2s;
}
.mlog-back:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(102,126,234,0.45); }

/* ===== الإحصائيات ===== */
.mlog-stats {
    display: grid; gap: 14px;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}
.mlog-stat {
    display: flex; align-items: center; gap: 14px;
    background: var(--w-card, #fff);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 16px; padding: 16px 18px;
    box-shadow: var(--w-shadow, 0 8px 24px rgba(15,23,42,0.06));
}
.mlog-stat-icon {
    flex-shrink: 0; width: 52px; height: 52px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 6px 16px rgba(102,126,234,0.28);
}
.mlog-stat-body { min-width: 0; }
.mlog-stat-value { font-size: 22px; font-weight: 800; color: var(--w-text, #0f172a); line-height: 1.2; }
.mlog-stat-label { font-size: 12.5px; color: var(--w-text-muted, #475569); margin-top: 2px; }

/* ===== المشاركون ===== */
.mlog-people {
    display: grid; gap: 16px;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}
.mlog-person {
    background: var(--w-card, #fff);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 16px; padding: 20px;
    box-shadow: var(--w-shadow, 0 8px 24px rgba(15,23,42,0.06));
}
.mlog-person--u1 {
    background: linear-gradient(rgba(102,126,234,0.06), rgba(118,75,162,0.06)), var(--w-card, #fff);
    border-inline-start: 4px solid #667eea;
}
.mlog-person--u2 {
    background: linear-gradient(rgba(240,147,251,0.06), rgba(245,87,108,0.06)), var(--w-card, #fff);
    border-inline-start: 4px solid #f093fb;
}
.mlog-person-top { display: flex; align-items: center; gap: 14px; }
.mlog-person-avatar {
    flex-shrink: 0; width: 58px; height: 58px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 800; font-size: 24px;
}
.mlog-av-u1 { background: var(--mlog-grad); box-shadow: 0 6px 16px rgba(102,126,234,0.35); }
.mlog-av-u2 { background: var(--mlog-pink); box-shadow: 0 6px 16px rgba(240,147,251,0.35); }
.mlog-person-info { flex: 1; min-width: 0; }
.mlog-person-name { font-weight: 700; font-size: 17px; margin-bottom: 4px; color: var(--w-text, #0f172a); overflow-wrap: anywhere; }
.mlog-person-email { font-size: 13px; color: var(--w-text-muted, #475569); margin-bottom: 8px; overflow-wrap: anywhere; }
.mlog-role {
    display: inline-block; color: #fff; padding: 4px 12px;
    border-radius: 999px; font-size: 12px; font-weight: 700;
}
.mlog-role--u1 { background: #667eea; }
.mlog-role--u2 { background: #f093fb; }
.mlog-person-foot {
    margin-top: 15px; padding-top: 15px;
    border-top: 1px solid var(--w-border, rgba(15,23,42,0.08));
    font-size: 13px; color: var(--w-text-muted, #475569);
}
.mlog-person-foot strong { color: var(--w-text, #0f172a); }

/* ===== لوحة المحادثة ===== */
.mlog-chat-panel {
    background: var(--w-card, #fff);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 20px; overflow: hidden;
    box-shadow: var(--w-shadow, 0 8px 24px rgba(15,23,42,0.06));
}
.mlog-chat-head {
    display: flex; align-items: center; gap: 10px;
    padding: 16px 22px; color: #fff;
    background: var(--mlog-grad);
}
.mlog-chat-head-title { font-size: 16px; font-weight: 800; }
.mlog-chat-count {
    background: rgba(255,255,255,0.22); color: #fff;
    padding: 2px 12px; border-radius: 999px; font-size: 13px; font-weight: 700;
}

/* منطقة الرسائل = خلفية دردشة (داكنة في الوضع الليلي عبر --w-bg) */
.mlog-chat {
    max-height: 720px; overflow-y: auto;
    padding: 24px 22px;
    display: flex; flex-direction: column; gap: 18px;
    background:
        radial-gradient(circle at 20% 0%, rgba(102,126,234,0.06), transparent 55%),
        var(--w-bg, #f8fafc);
}

/* صفّ الرسالة — user1 (يمين RTL) / user2 (يسار) */
.mlog-row { display: flex; align-items: flex-start; gap: 12px; }
.mlog-row.is-u1 { flex-direction: row; }
.mlog-row.is-u2 { flex-direction: row-reverse; }

.mlog-msg-avatar {
    flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 800; font-size: 17px;
}

.mlog-msg { display: flex; flex-direction: column; gap: 6px; max-width: 74%; min-width: 0; }
.mlog-row.is-u1 .mlog-msg { align-items: flex-start; }
.mlog-row.is-u2 .mlog-msg { align-items: flex-end; }

.mlog-msg-meta { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; }
.mlog-msg-name { font-weight: 700; font-size: 13.5px; color: var(--w-text, #0f172a); }
.mlog-msg-time { font-size: 11.5px; color: var(--w-text-muted, #94a3b8); }
.mlog-tick { font-size: 13px; color: var(--w-text-muted, #94a3b8); }
.mlog-tick.read { color: #22c55e; }

/* الفقاعات — sent = تدرّج العلامة + أبيض ؛ recv = سطح البطاقة + حد */
.mlog-bubble {
    padding: 13px 18px; border-radius: 16px;
    font-size: 14px; line-height: 1.65;
    white-space: pre-wrap; word-wrap: break-word; overflow-wrap: anywhere;
    box-shadow: 0 2px 10px rgba(2,6,23,0.08);
}
.mlog-bubble.sent { background: var(--mlog-grad); color: #fff; border-top-right-radius: 6px; }
.mlog-bubble.recv {
    background: var(--w-card, #fff); color: var(--w-text, #0f172a);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-top-left-radius: 6px;
}

.mlog-detail {
    font-size: 11.5px; font-weight: 600; text-decoration: none;
    color: #6366f1;
}
.mlog-detail:hover { text-decoration: underline; }

/* حالة فارغة */
.mlog-empty { text-align: center; padding: 60px 20px; color: var(--w-text-muted, #94a3b8); }
.mlog-empty-icon {
    width: 88px; height: 88px; margin: 0 auto 16px; border-radius: 26px;
    display: flex; align-items: center; justify-content: center; font-size: 42px;
    background: linear-gradient(135deg, rgba(102,126,234,0.12), rgba(118,75,162,0.12));
}
.mlog-empty p { font-size: 17px; margin: 0; }

/* شريط التمرير */
.mlog-chat::-webkit-scrollbar { width: 8px; }
.mlog-chat::-webkit-scrollbar-track { background: transparent; }
.mlog-chat::-webkit-scrollbar-thumb { background: rgba(102,126,234,0.55); border-radius: 10px; }
.mlog-chat::-webkit-scrollbar-thumb:hover { background: rgba(102,126,234,0.8); }

/* ===== الاستجابة ===== */
@media (max-width: 1024px) {
    .mlog-stats { grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
}
@media (max-width: 640px) {
    .mlog-shell { gap: 16px; }
    .mlog-title { font-size: 19px; }
    .mlog-back { width: 100%; text-align: center; }
    .mlog-stats { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .mlog-stat { padding: 13px 14px; gap: 11px; }
    .mlog-stat-icon { width: 44px; height: 44px; font-size: 20px; border-radius: 12px; }
    .mlog-stat-value { font-size: 18px; }
    .mlog-people { grid-template-columns: 1fr; }
    .mlog-chat { padding: 16px 14px; gap: 14px; }
    .mlog-msg { max-width: 85%; }
    .mlog-msg-avatar { width: 38px; height: 38px; font-size: 15px; }
    .mlog-bubble { padding: 11px 15px; font-size: 13.5px; }
}
@media (max-width: 380px) {
    .mlog-stats { grid-template-columns: 1fr; }
}

/* ===== لمسات صريحة للوضع الليلي (الأسطح تعمل أصلاً عبر --w-*) ===== */
html[data-theme="dark"] .mlog-chat {
    background:
        radial-gradient(circle at 20% 0%, rgba(102,126,234,0.10), transparent 55%),
        var(--w-bg) !important;
}
html[data-theme="dark"] .mlog-detail { color: #a5b4fc; }
html[data-theme="dark"] .mlog-bubble.recv { box-shadow: 0 2px 10px rgba(0,0,0,0.35); }
</style>
@endsection
