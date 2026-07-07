@extends(auth()->user()->role === 'super_admin' ? 'layouts.admin' : (auth()->user()->role === 'school_admin' ? 'layouts.school-admin' : (auth()->user()->role === 'teacher' ? 'layouts.teacher' : (auth()->user()->role === 'parent' ? 'layouts.parent' : (auth()->user()->role === 'student' ? 'layouts.student-app' : 'layouts.student-app')))))

@section('page-title', 'الرسائل')

@section('content')
@php
    // إحصاءات خفيفة للوحة الترحيب (لا تُغيّر منطق العرض)
    $authId = auth()->id();
    $convCount = is_countable($conversations) ? count($conversations) : 0;
    $unreadTotal = 0;
    foreach ($conversations as $__conv) { $unreadTotal += $__conv->unreadCount($authId); }
@endphp
<!-- Container with padding for status bar and bottom nav -->
<div style="padding-top: 100px; padding-bottom: 120px; padding-left: 20px; padding-right: 20px; max-width: 1400px; margin: 0 auto;">
<!-- Page Header -->
<div class="msg-page-header">
    <div class="msg-ph-top">
        <div class="msg-ph-icon">💬</div>
        <h1 class="msg-ph-title">الرسائل</h1>
    </div>
    <div class="msg-ph-crumbs">
        <a href="{{ auth()->user()->role === 'school_admin' ? route('school-admin.dashboard') : route('dashboard') }}" class="msg-ph-link" onmouseover="this.style.opacity='0.75'" onmouseout="this.style.opacity='1'">
            <i class="fas fa-home"></i> الرئيسية
        </a>
        <span class="msg-ph-sep">›</span>
        <span class="msg-ph-current">الرسائل</span>
    </div>
</div>

<style>
/* ===================================================================
   Wahy — صندوق الوارد العام (System A) — طبقة بصرية فاخرة
   كل الأسطح مبنيّة على متغيّرات الثيم (--w-*) فتعمل في الوضعَين تلقائياً.
   =================================================================== */

/* ---- الهيدر ---- */
.msg-page-header {
    background: var(--w-card, #fff);
    border: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    border-radius: 18px;
    padding: 22px 26px;
    margin-bottom: 22px;
    box-shadow: 0 10px 40px rgba(2, 6, 23, 0.06);
}
.msg-ph-top { display: flex; align-items: center; gap: 14px; margin-bottom: 10px; }
.msg-ph-icon {
    width: 46px; height: 46px; border-radius: 13px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 22px; flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.35);
}
.msg-ph-title { font-size: 24px; font-weight: 800; color: var(--w-text, #0f172a); margin: 0; }
.msg-ph-crumbs { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--w-text-muted, #64748b); }
.msg-ph-link { color: #667eea; text-decoration: none; font-weight: 600; transition: opacity 0.2s; }
.msg-ph-sep { color: var(--w-text-muted, #cbd5e1); opacity: 0.55; }
.msg-ph-current { color: var(--w-text, #1e293b); font-weight: 700; }

/* ---- الشبكة الرئيسية ---- */
.messages-container {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 20px;
    height: calc(100vh - 280px);
    min-height: 500px;
    padding-bottom: 20px;
}

/* ---- قائمة المحادثات ---- */
.conversations-list {
    background: var(--w-card, #fff);
    border: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    border-radius: 20px;
    padding: 22px;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(2, 6, 23, 0.06);
    display: flex;
    flex-direction: column;
}
.conversations-list h3 {
    font-size: 18px;
    font-weight: 800;
    color: var(--w-text, #0f172a);
    margin: 0 0 16px 0;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--w-border, #f1f5f9);
    display: flex;
    align-items: center;
    gap: 8px;
}

.conversation-item {
    padding: 14px;
    border-radius: 14px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease, background 0.25s ease;
    border: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    background: var(--w-bg, #f8fafc);
}
.conversation-item:hover {
    background: var(--w-card, #fff);
    border-color: #667eea;
    transform: translateX(-4px);
    box-shadow: 0 8px 22px rgba(102, 126, 234, 0.18);
}
.conversation-item.active {
    background: var(--w-card, #fff);
    border-color: #667eea;
    box-shadow: 0 8px 22px rgba(102, 126, 234, 0.22);
}

.conversation-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 18px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.user-info { flex: 1; min-width: 0; }

.user-name {
    font-weight: 700;
    color: var(--w-text, #0f172a);
    margin-bottom: 4px;
}

.last-message {
    font-size: 13px;
    color: var(--w-text-muted, #64748b);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-badge {
    background: #ef4444;
    color: white;
    border-radius: 999px;
    padding: 2px 9px;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(239, 68, 68, 0.35);
}

/* ---- الحالة الفارغة لقائمة المحادثات ---- */
.conv-empty {
    text-align: center;
    padding: 44px 16px;
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.conv-empty-icon { font-size: 56px; margin-bottom: 8px; opacity: 0.85; }
.conv-empty h4 { font-size: 17px; font-weight: 700; color: var(--w-text, #475569); margin: 0; }
.conv-empty p { font-size: 13px; color: var(--w-text-muted, #94a3b8); margin: 4px 0 0 0; }

/* ---- لوحة الدردشة / الترحيب ---- */
.chat-container {
    background: var(--w-card, #fff);
    border: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(2, 6, 23, 0.06);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
}
.chat-welcome {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
    overflow-y: auto;
    background:
        radial-gradient(circle at 18% 18%, rgba(102, 126, 234, 0.07), transparent 42%),
        radial-gradient(circle at 82% 82%, rgba(118, 75, 162, 0.07), transparent 42%);
}
.chat-welcome-inner {
    width: 100%;
    max-width: 880px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.cw-badge {
    width: 96px; height: 96px; border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 44px;
    margin-bottom: 22px;
    box-shadow: 0 16px 40px rgba(102, 126, 234, 0.4);
}
.cw-title { font-size: 24px; font-weight: 800; color: var(--w-text, #1e293b); margin: 0 0 10px 0; }
.cw-sub { font-size: 15px; line-height: 1.9; color: var(--w-text-muted, #64748b); margin: 0 0 22px 0; max-width: 540px; }
.cw-stats { display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; margin-bottom: 22px; }
.cw-stat {
    min-width: 112px;
    padding: 14px 18px;
    border-radius: 14px;
    background: var(--w-bg, #f8fafc);
    border: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.cw-stat-num { font-size: 26px; font-weight: 800; color: #667eea; line-height: 1; }
.cw-stat-unread .cw-stat-num { color: #ef4444; }
.cw-stat-label { font-size: 12px; color: var(--w-text-muted, #64748b); font-weight: 600; }
.cw-hints { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; margin-bottom: 26px; }
.cw-hint {
    font-size: 13px;
    color: var(--w-text-muted, #64748b);
    background: var(--w-bg, #f8fafc);
    border: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    padding: 8px 14px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cw-hint i { color: #667eea; }
.cw-cta {
    padding: 14px 28px;
    border: none;
    border-radius: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 10px 26px rgba(102, 126, 234, 0.4);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.cw-cta:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(102, 126, 234, 0.5); }

/* ---- (أنماط الدردشة المضمّنة — تبقى متاحة ومبنيّة على الثيم) ---- */
.chat-header {
    padding: 20px;
    border-bottom: 1px solid var(--w-border, #f1f5f9);
    display: flex;
    align-items: center;
    gap: 12px;
}
.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: var(--w-bg, #f8fafc);
}
.message { margin-bottom: 16px; display: flex; gap: 10px; }
.message.sent { flex-direction: row-reverse; }
.message-bubble {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 16px;
    word-wrap: break-word;
}
.message.received .message-bubble {
    background: var(--w-card, #fff);
    color: var(--w-text, #0f172a);
    border: 1px solid var(--w-border, #e2e8f0);
}
.message.sent .message-bubble {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.message-time { font-size: 11px; color: var(--w-text-muted, #94a3b8); margin-top: 4px; }
.chat-input {
    padding: 20px;
    border-top: 1px solid var(--w-border, #f1f5f9);
    display: flex;
    gap: 12px;
}
.chat-input textarea {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid var(--w-border, #e2e8f0);
    border-radius: 10px;
    resize: none;
    font-family: inherit;
    background: var(--w-bg, #fff);
    color: var(--w-text, #0f172a);
}
.send-btn {
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.2s;
}
.send-btn:hover { transform: translateY(-2px); }

/* ---- زر محادثة جديدة ---- */
.new-conversation-btn {
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 8px 22px rgba(102, 126, 234, 0.32);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.new-conversation-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(102, 126, 234, 0.42);
}

/* ---- المودال ---- */
.user-select-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(2, 6, 23, 0.6);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 16px;
    animation: fadeIn 0.2s ease;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.modal-content {
    background: var(--w-card, #fff);
    border: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    border-radius: 20px;
    padding: 28px;
    max-width: 500px;
    width: 100%;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 24px 70px rgba(2, 6, 23, 0.35);
    animation: slideUp 0.3s ease;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.user-list-item {
    padding: 12px 14px;
    border-radius: 14px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease, background 0.25s ease;
    border: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    background: var(--w-bg, #f8fafc);
}
.user-list-item:hover {
    background: var(--w-card, #fff);
    border-color: #667eea;
    transform: translateX(-4px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.16);
}
/* غلاف داخلي مرن: يحافظ على صفّ الأفاتار+المعلومات حتى حين يضبط JS العنصر إلى display:block */
.uli-inner { display: flex; align-items: center; gap: 12px; }
.user-list-item .user-avatar { flex-shrink: 0; }

/* ---- أشرطة تمرير أنيقة ---- */
.conversations-list::-webkit-scrollbar,
.chat-welcome::-webkit-scrollbar,
.modal-content::-webkit-scrollbar,
#usersList::-webkit-scrollbar { width: 8px; }
.conversations-list::-webkit-scrollbar-thumb,
.chat-welcome::-webkit-scrollbar-thumb,
.modal-content::-webkit-scrollbar-thumb,
#usersList::-webkit-scrollbar-thumb {
    background: var(--w-border, rgba(15, 23, 42, 0.18));
    border-radius: 8px;
}

/* ---- تلميع الوضع الليلي لألوان العلامة فوق الأسطح الداكنة (أصناف لا يلتقطها dark-coverage) ---- */
html[data-theme="dark"] .msg-ph-link,
html[data-theme="dark"] .cw-stat-num,
html[data-theme="dark"] .cw-hint i { color: #a5b4fc; }
html[data-theme="dark"] .cw-stat-unread .cw-stat-num { color: #fca5a5; }

/* ===== الاستجابة ===== */
/* لابتوب >=1024px: التخطيط الكامل (افتراضي أعلاه) */

/* تابلت 640–1024px: قلّل عرض القائمة والحشو */
@media (max-width: 1024px) {
    .messages-container {
        grid-template-columns: 300px 1fr;
        gap: 16px;
        height: calc(100vh - 250px);
    }
    .conversations-list { padding: 18px; }
    .chat-welcome { padding: 32px 20px; }
    .cw-title { font-size: 22px; }
}

/* جوال <=640px: عمود واحد، القائمة تملأ العرض، اللوحة تتكيّف */
@media (max-width: 640px) {
    .messages-container {
        grid-template-columns: 1fr;
        height: auto;
        min-height: 0;
        gap: 16px;
        padding-bottom: 24px;
    }
    .conversations-list {
        max-height: none;
        padding: 16px;
        border-radius: 18px;
    }
    .chat-container { min-height: 360px; border-radius: 18px; }
    .chat-welcome { padding: 32px 18px; }
    .cw-badge { width: 80px; height: 80px; font-size: 36px; margin-bottom: 18px; }
    .cw-title { font-size: 20px; }
    .cw-sub { font-size: 14px; }
    .cw-stat { min-width: 92px; padding: 12px 14px; }
    .cw-stat-num { font-size: 22px; }
    .msg-page-header { padding: 18px; border-radius: 16px; }
    .msg-ph-title { font-size: 20px; }
    .msg-ph-icon { width: 42px; height: 42px; font-size: 20px; }
    .modal-content { padding: 20px; border-radius: 18px; max-height: 88vh; }
    .conversation-item:hover,
    .user-list-item:hover { transform: none; }
}
</style>

<div class="messages-container">
    <!-- قائمة المحادثات -->
    <div class="conversations-list">
        <h3>💬 المحادثات</h3>

        <button class="new-conversation-btn" onclick="showUserSelect()">
            <span style="font-size: 18px;">➕</span>
            <span>محادثة جديدة</span>
        </button>

        @forelse($conversations as $conversation)
            @php
                $otherUser = $conversation->getOtherUser(auth()->id());
                $unreadCount = $conversation->unreadCount(auth()->id());
            @endphp
            <div class="conversation-item" onclick="window.location.href='{{ auth()->user()->role === 'school_admin' ? route('school-admin.messages.show', $otherUser->id) : route('messages.show', $otherUser->id) }}'">
                <div class="conversation-user">
                    <div class="user-avatar">
                        @if($otherUser->avatar)
                            <img src="{{ $otherUser->avatar_url }}" alt="{{ $otherUser->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            {{ mb_substr($otherUser->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ $otherUser->name }}</div>
                        @if($conversation->lastMessage)
                            <div class="last-message">
                                {{ html_excerpt($conversation->lastMessage->message, 50) }}
                            </div>
                        @endif
                    </div>
                    @if($unreadCount > 0)
                        <span class="unread-badge">{{ $unreadCount }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="conv-empty">
                <div class="conv-empty-icon">💬</div>
                <h4>لا توجد محادثات حالياً</h4>
                <p>ابدأ محادثة جديدة من خلال الزر أعلاه</p>
            </div>
        @endforelse
    </div>

    <!-- منطقة الدردشة / لوحة الترحيب -->
    <div class="chat-container">
        <div class="chat-welcome">
            <div class="chat-welcome-inner">
                <div class="cw-badge">📬</div>
                <h2 class="cw-title">مرحباً بك في مركز الرسائل</h2>
                <p class="cw-sub">اختر محادثة من القائمة لعرض تفاصيلها، أو ابدأ محادثة جديدة مع أي مستخدم في المنصّة.</p>

                @if($convCount > 0)
                    <div class="cw-stats">
                        <div class="cw-stat">
                            <span class="cw-stat-num">{{ $convCount }}</span>
                            <span class="cw-stat-label">محادثة</span>
                        </div>
                        @if($unreadTotal > 0)
                            <div class="cw-stat cw-stat-unread">
                                <span class="cw-stat-num">{{ $unreadTotal }}</span>
                                <span class="cw-stat-label">غير مقروءة</span>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="cw-hints">
                    <div class="cw-hint"><i class="fas fa-hand-pointer"></i> انقر على أي محادثة لفتحها</div>
                    <div class="cw-hint"><i class="fas fa-shield-halved"></i> رسائلك خاصة ومحميّة</div>
                </div>

                <button class="cw-cta" onclick="showUserSelect()">
                    <span style="font-size: 18px;">➕</span>
                    <span>بدء محادثة جديدة</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- مودال اختيار مستخدم -->
<div class="user-select-modal" id="userSelectModal">
    <div class="modal-content" style="max-width: 650px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--w-border, #f1f5f9);">
            <h3 style="margin: 0; font-size: 20px; font-weight: 800; color: var(--w-text, #1e293b); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-user-plus"></i>
                بدء محادثة جديدة
            </h3>
            <button onclick="hideUserSelect()" style="background: #ef4444; color: white; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: all 0.2s;">
                ×
            </button>
        </div>

        <!-- حقل البحث -->
        <div style="position: relative; margin-bottom: 20px;">
            <i class="fas fa-search" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
            <input
                type="text"
                id="userSearch"
                placeholder="ابحث عن مستخدم بالاسم أو البريد الإلكتروني..."
                style="width: 100%; padding: 12px 16px 12px 42px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.2s;"
                onkeyup="filterUsers()"
                onfocus="this.style.borderColor='#667eea'"
                onblur="this.style.borderColor='#e2e8f0'"
            >
        </div>

        <!-- خيار اختيار المدرسة (للسوبر أدمن فقط) -->
        @if(auth()->user()->role === 'super_admin')
            <div style="margin-bottom: 20px; padding: 18px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 2px solid #3b82f6; border-radius: 12px;">
                <h4 style="margin: 0 0 12px 0; color: #1e40af; display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 600;">
                    <i class="fas fa-school"></i> إرسال لجميع مستخدمي مدرسة
                </h4>
                <select
                    id="schoolSelect"
                    style="width: 100%; padding: 12px 14px; border: 2px solid #60a5fa; border-radius: 8px; cursor: pointer; font-size: 14px; background: white;"
                    onchange="selectSchool()"
                >
                    <option value="">-- اختر مدرسة --</option>
                    @php
                        $schools = \App\Models\School::with('branches')->get();
                    @endphp
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                    @endforeach
                </select>
                <small style="color: #64748b; font-size: 12px; display: block; margin-top: 8px;">
                    سيتم إرسال الرسالة لجميع المدرسين والطلاب وأولياء الأمور في هذه المدرسة
                </small>
            </div>
        @endif

        <!-- قائمة المستخدمين -->
        <div style="margin-top: 20px;">
            <h4 style="margin: 0 0 14px 0; color: var(--w-text-muted, #475569); font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-users"></i>
                المستخدمون المتاحون ({{ count($availableUsers) }})
            </h4>
            <div id="usersList" style="max-height: 380px; overflow-y: auto; padding: 2px;">
                @foreach($availableUsers as $user)
                    <div class="user-list-item"
                         data-name="{{ strtolower($user->name) }}"
                         data-email="{{ strtolower($user->email) }}"
                         data-role="{{ $user->role }}"
                         onclick="startConversation({{ $user->id }})">
                        <div class="uli-inner">
                            <div class="user-avatar">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                @else
                                    {{ mb_substr($user->name, 0, 1) }}
                                @endif
                            </div>
                            <div class="user-info" style="flex: 1;">
                                <div class="user-name" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span>{{ $user->name }}</span>
                                    @if($user->role === 'teacher')
                                        <span style="background: #dbeafe; color: #1e40af; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                            <i class="fas fa-chalkboard-teacher"></i> معلم
                                        </span>
                                    @elseif($user->role === 'student')
                                        <span style="background: #dcfce7; color: #166534; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                            <i class="fas fa-user-graduate"></i> طالب
                                        </span>
                                    @elseif($user->role === 'parent')
                                        <span style="background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                            <i class="fas fa-users"></i> ولي أمر
                                        </span>
                                    @elseif($user->role === 'school_admin')
                                        <span style="background: #e0e7ff; color: #4338ca; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                            <i class="fas fa-user-tie"></i> مدير مدرسة
                                        </span>
                                    @endif
                                </div>
                                <div class="last-message">{{ $user->email }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button style="width: 100%; padding: 12px; margin-top: 20px; border: 1px solid var(--w-border, #e2e8f0); background: var(--w-bg, #fff); border-radius: 10px; cursor: pointer; font-weight: 600; color: var(--w-text-muted, #64748b); transition: all 0.2s;"
                onclick="hideUserSelect()">
            <i class="fas fa-times"></i> إغلاق
        </button>
    </div>
</div>

<script>
function showUserSelect() {
    document.getElementById('userSelectModal').style.display = 'flex';
    document.getElementById('userSearch').value = '';
    document.getElementById('userSearch').focus();
    filterUsers(); // إعادة عرض جميع المستخدمين
}

function hideUserSelect() {
    document.getElementById('userSelectModal').style.display = 'none';
}

function startConversation(userId) {
    @if(auth()->user()->role === 'school_admin')
        window.location.href = '/school-admin/messages/' + userId;
    @else
        window.location.href = '/messages/' + userId;
    @endif
}

// دالة البحث
function filterUsers() {
    const searchTerm = document.getElementById('userSearch').value.toLowerCase();
    const userItems = document.querySelectorAll('.user-list-item');

    let visibleCount = 0;
    userItems.forEach(item => {
        const name = item.getAttribute('data-name');
        const email = item.getAttribute('data-email');

        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // عرض رسالة إذا لم يتم العثور على نتائج
    let noResults = document.getElementById('noResultsMessage');
    if (visibleCount === 0) {
        if (!noResults) {
            noResults = document.createElement('div');
            noResults.id = 'noResultsMessage';
            noResults.style.cssText = 'text-align: center; padding: 40px; color: #64748b;';
            noResults.innerHTML = '<p>🔍 لا توجد نتائج</p><p style="font-size: 13px;">جرب البحث بكلمات أخرى</p>';
            document.getElementById('usersList').appendChild(noResults);
        }
        noResults.style.display = 'block';
    } else if (noResults) {
        noResults.style.display = 'none';
    }
}

// دالة اختيار المدرسة
function selectSchool() {
    const schoolId = document.getElementById('schoolSelect').value;

    if (!schoolId) {
        // إعادة عرض جميع المستخدمين
        filterUsers();
        return;
    }

    // إظهار تأكيد
    if (confirm('هل أنت متأكد من إرسال رسالة جماعية لجميع المستخدمين في هذه المدرسة؟')) {
        // هنا يمكن إرسال الرسالة الجماعية
        // يمكن تطوير هذه الميزة لاحقاً بصفحة منفصلة
        alert('⚠️ ميزة الرسائل الجماعية قيد التطوير.\n\nحالياً يمكنك اختيار المستخدمين واحداً تلو الآخر.');
        document.getElementById('schoolSelect').value = '';
    } else {
        document.getElementById('schoolSelect').value = '';
    }
}

// إغلاق المودال عند الضغط خارجه
document.getElementById('userSelectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideUserSelect();
    }
});
</script>
</div> <!-- End container -->

@endsection
