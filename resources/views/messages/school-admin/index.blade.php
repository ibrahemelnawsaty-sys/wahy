@extends('layouts.school-admin')

@section('page-title', 'الرسائل - مدير المدرسة')

@section('content')

<style>
/* ============================================================
   Wahy — رسائل مدير المدرسة (طبقة بصرية مُعاد تصميمها)
   مبني بالكامل على متغيّرات الثيم --w-* (تعمل في الوضعين تلقائياً)
   ============================================================ */

/* ===== الهيدر (Hero) ===== */
.sa-msg-hero{
    position:relative; overflow:hidden;
    display:flex; align-items:center; justify-content:space-between; gap:20px;
    background:var(--w-card,#fff);
    border:1px solid var(--w-border,rgba(15,23,42,.08));
    border-radius:20px;
    padding:22px 26px;
    margin-bottom:22px;
    box-shadow:0 10px 40px rgba(2,6,23,.08);
}
.sa-msg-hero::before{
    content:""; position:absolute; top:0; bottom:0; right:0; width:5px;
    background:linear-gradient(180deg,#667eea,#764ba2);
}
.sa-msg-hero__left{ min-width:0; }
.sa-msg-hero__id{ display:flex; align-items:center; gap:14px; margin-bottom:10px; }
.sa-msg-hero__icon{
    width:52px; height:52px; flex-shrink:0; border-radius:14px;
    background:linear-gradient(135deg,#667eea,#764ba2);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:24px;
    box-shadow:0 8px 20px rgba(102,126,234,.35);
}
.sa-msg-hero__title{ font-size:24px; font-weight:800; color:var(--w-text,#0f172a); margin:0; line-height:1.2; }
.sa-msg-hero__sub{ margin:4px 0 0; color:var(--w-text-muted,#475569); font-size:13.5px; }
.sa-msg-hero__crumbs{ display:flex; align-items:center; gap:8px; font-size:13px; color:var(--w-text-muted,#475569); flex-wrap:wrap; }
.sa-msg-hero__crumbs a{ color:#667eea; text-decoration:none; font-weight:600; transition:color .2s; }
.sa-msg-hero__crumbs a:hover{ color:#764ba2; }
.sa-msg-hero__crumbs .sep{ opacity:.5; }
.sa-msg-hero__crumbs .cur{ color:var(--w-text,#0f172a); font-weight:700; }
.sa-msg-hero__count{
    flex-shrink:0; text-align:center;
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff; border-radius:16px; padding:14px 24px;
    box-shadow:0 8px 22px rgba(102,126,234,.35);
}
.sa-msg-hero__count b{ display:block; font-size:26px; font-weight:800; line-height:1; }
.sa-msg-hero__count span{ font-size:11.5px; font-weight:600; opacity:.92; }

/* ===== تخطيط عمودَي القائمة + الدردشة ===== */
.school-messages-container{
    display:grid;
    grid-template-columns:380px 1fr;
    gap:22px;
    height:calc(100vh - 250px);
    min-height:540px;
}

/* ===== قائمة المحادثات ===== */
.school-conversations-list{
    background:var(--w-card,#fff);
    border-radius:20px;
    padding:22px;
    overflow-y:auto;
    box-shadow:0 10px 40px rgba(2,6,23,.08);
    border:1px solid var(--w-border,rgba(15,23,42,.08));
}
.school-conversations-list::-webkit-scrollbar,
.sa-chat-body::-webkit-scrollbar{ width:8px; }
.school-conversations-list::-webkit-scrollbar-thumb,
.sa-chat-body::-webkit-scrollbar-thumb{ background:var(--w-border,rgba(15,23,42,.14)); border-radius:8px; }

.school-conversations-list h3{
    font-size:17px; font-weight:800; color:var(--w-text,#0f172a);
    margin:0 0 16px; padding-bottom:14px;
    border-bottom:1px solid var(--w-border,rgba(15,23,42,.08));
    display:flex; align-items:center; gap:10px;
}
.school-conversations-list h3 i{ color:#667eea; }

.school-new-conversation-btn{
    width:100%; padding:14px 20px;
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff; border:none; border-radius:14px;
    font-size:15px; font-weight:700; cursor:pointer;
    margin-bottom:20px;
    display:flex; align-items:center; justify-content:center; gap:9px;
    box-shadow:0 6px 18px rgba(102,126,234,.35);
    transition:transform .25s ease, box-shadow .25s ease;
}
.school-new-conversation-btn:hover{ transform:translateY(-2px); box-shadow:0 10px 26px rgba(102,126,234,.45); }

.school-conversation-item{
    padding:14px; border-radius:14px; margin-bottom:10px; cursor:pointer;
    border:1px solid var(--w-border,rgba(15,23,42,.08));
    background:var(--w-bg,#f8fafc);
    transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease, background .25s ease;
}
.school-conversation-item:hover{
    border-color:#667eea; transform:translateX(-4px);
    box-shadow:0 8px 22px rgba(102,126,234,.18);
}
.school-conversation-item.active{
    border-color:#667eea;
    background:linear-gradient(135deg, rgba(102,126,234,.12), rgba(118,75,162,.07));
    box-shadow:0 8px 22px rgba(102,126,234,.20);
}

.school-user-avatar{
    width:50px; height:50px; flex-shrink:0; border-radius:50%;
    background:linear-gradient(135deg,#667eea,#764ba2);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-weight:800; font-size:18px;
    box-shadow:0 6px 16px rgba(102,126,234,.35);
}
.school-user-info{ flex:1; min-width:0; }
.school-user-name{
    font-weight:700; font-size:14.5px; color:var(--w-text,#0f172a);
    margin-bottom:5px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;
}
.school-role-badge{
    font-size:10.5px; padding:3px 9px; border-radius:7px; font-weight:700;
    display:inline-flex; align-items:center; gap:4px;
}
.rb-admin{ background:rgba(245,158,11,.16); color:#b45309; }
.rb-teacher{ background:rgba(59,130,246,.16); color:#2563eb; }
.rb-student{ background:rgba(34,197,94,.16); color:#16a34a; }
.rb-parent{ background:rgba(245,158,11,.16); color:#b45309; }
html[data-theme="dark"] .rb-admin{ color:#fbbf24; }
html[data-theme="dark"] .rb-teacher{ color:#93c5fd; }
html[data-theme="dark"] .rb-student{ color:#86efac; }
html[data-theme="dark"] .rb-parent{ color:#fbbf24; }

.school-last-message{
    font-size:12.5px; color:var(--w-text-muted,#475569);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.school-unread-badge{
    flex-shrink:0; background:#ef4444; color:#fff; border-radius:12px;
    padding:3px 10px; font-size:11.5px; font-weight:800;
    box-shadow:0 4px 12px rgba(239,68,68,.35);
}

/* ===== منطقة الدردشة ===== */
.school-chat-container{
    background:var(--w-card,#fff);
    border-radius:20px;
    display:flex; flex-direction:column; overflow:hidden;
    box-shadow:0 10px 40px rgba(2,6,23,.08);
    border:1px solid var(--w-border,rgba(15,23,42,.08));
}

/* رأس الدردشة */
.sa-chat-head{
    padding:18px 22px;
    border-bottom:1px solid var(--w-border,rgba(15,23,42,.08));
    background:var(--w-card,#fff);
    display:flex; align-items:center; gap:14px;
    position:sticky; top:0; z-index:2;
}
.sa-chat-head__avatar{
    width:50px; height:50px; flex-shrink:0; border-radius:50%;
    background:linear-gradient(135deg,#667eea,#764ba2);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-weight:800; font-size:18px; text-transform:uppercase;
    box-shadow:0 6px 16px rgba(102,126,234,.35);
}
.sa-chat-head__meta{ flex:1; min-width:0; }
.sa-chat-head__name{
    margin:0; font-size:17px; font-weight:800; color:var(--w-text,#0f172a);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.sa-chat-head__role{ margin:3px 0 0; font-size:12.5px; color:var(--w-text-muted,#475569); }
.sa-chat-close{
    flex-shrink:0; width:38px; height:38px; border-radius:50%; cursor:pointer;
    background:var(--w-bg,#f1f5f9); border:1px solid var(--w-border,rgba(15,23,42,.08));
    color:var(--w-text-muted,#475569);
    display:flex; align-items:center; justify-content:center;
    transition:transform .25s ease, background .2s, color .2s, border-color .2s;
}
.sa-chat-close:hover{ background:#ef4444; border-color:#ef4444; color:#fff; transform:rotate(90deg); }

/* جسم الرسائل (خلفية دردشة أنيقة غائرة) */
.sa-chat-body{
    flex:1; overflow-y:auto; padding:22px;
    background:
        radial-gradient(circle at 100% 0, rgba(102,126,234,.06), transparent 42%),
        var(--w-bg,#f8fafc);
}

/* منطقة الكتابة */
.sa-compose{
    padding:16px 20px;
    border-top:1px solid var(--w-border,rgba(15,23,42,.08));
    background:var(--w-card,#fff);
}
.sa-compose__form{ display:flex; gap:12px; align-items:flex-end; }
.sa-compose__input{
    flex:1; padding:13px 16px;
    border:1.5px solid var(--w-border,rgba(15,23,42,.12)); border-radius:14px;
    resize:none; font-family:inherit; font-size:14px; line-height:1.6;
    min-height:48px; max-height:120px;
    background:var(--w-bg,#f8fafc); color:var(--w-text,#0f172a);
    transition:border-color .2s, box-shadow .2s;
}
.sa-compose__input::placeholder{ color:var(--w-text-muted,#475569); }
.sa-compose__input:focus{ outline:none; border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.15); }
.sa-compose__send{
    flex-shrink:0; height:48px; padding:0 22px; white-space:nowrap;
    background:linear-gradient(135deg,#667eea,#764ba2); color:#fff;
    border:none; border-radius:14px; font-weight:700; font-size:14px; cursor:pointer;
    display:flex; align-items:center; gap:8px;
    box-shadow:0 6px 16px rgba(102,126,234,.32);
    transition:transform .2s, box-shadow .2s;
}
.sa-compose__send:hover{ transform:translateY(-2px); box-shadow:0 10px 22px rgba(102,126,234,.42); }
.sa-compose__send:disabled{ opacity:.65; cursor:not-allowed; transform:none; }

/* ===== الحالات الفارغة ===== */
.sa-empty-hero{ text-align:center; padding:40px 24px; color:var(--w-text-muted,#475569); }
.sa-empty-hero__icon{
    width:96px; height:96px; margin:0 auto 20px; border-radius:28px;
    display:flex; align-items:center; justify-content:center; font-size:42px; color:#fff;
    background:linear-gradient(135deg,#667eea,#764ba2);
    box-shadow:0 18px 40px rgba(102,126,234,.35);
    animation:saFloat 4s ease-in-out infinite;
}
@keyframes saFloat{ 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-8px); } }
.sa-empty-hero h3{ font-size:21px; font-weight:800; color:var(--w-text,#0f172a); margin:0 0 8px; }
.sa-empty-hero p{ font-size:14px; color:var(--w-text-muted,#475569); margin:0; max-width:340px; }
.sa-empty-hero__tip{
    margin-top:18px; display:inline-flex; align-items:center; gap:8px;
    font-size:12.5px; font-weight:600; padding:10px 16px; border-radius:12px;
    background:rgba(102,126,234,.10); color:#667eea; border:1px solid rgba(102,126,234,.25);
}

.sa-empty-list{ text-align:center; padding:44px 18px; }
.sa-empty-list__icon{
    width:72px; height:72px; margin:0 auto 16px; border-radius:22px;
    display:flex; align-items:center; justify-content:center; font-size:30px; color:#667eea;
    background:rgba(102,126,234,.10); border:1px solid rgba(102,126,234,.2);
}
.sa-empty-list h4{ font-size:16px; font-weight:800; color:var(--w-text,#0f172a); margin:0 0 6px; }
.sa-empty-list p{ font-size:13px; color:var(--w-text-muted,#475569); margin:0; line-height:1.6; }

/* ===== فقاعات الرسائل ===== */
.message{ margin-bottom:14px; display:flex; animation:messageSlideIn .3s ease; }
@keyframes messageSlideIn{ from{ opacity:0; transform:translateY(10px); } to{ opacity:1; transform:translateY(0); } }
.message.sent{ justify-content:flex-start; }
.message.received{ justify-content:flex-end; }
.message-bubble{
    max-width:72%; padding:12px 16px; border-radius:16px; position:relative;
    box-shadow:0 4px 14px rgba(2,6,23,.08);
}
.message.sent .message-bubble{
    background:linear-gradient(135deg,#667eea,#764ba2); color:#fff;
    border-bottom-left-radius:5px;
}
.message.received .message-bubble{
    background:var(--w-card,#fff);
    border:1px solid var(--w-border,rgba(15,23,42,.10));
    color:var(--w-text,#0f172a);
    border-bottom-right-radius:5px;
}
.message-text{ font-size:14px; line-height:1.65; word-wrap:break-word; overflow-wrap:anywhere; white-space:pre-line; }
.message-time{ font-size:10.5px; opacity:.7; text-align:left; margin-top:5px; }
.message.sent .message-time{ color:rgba(255,255,255,.85); }
.message.received .message-time{ color:var(--w-text-muted,#475569); }

/* ============================================================
   الاستجابة
   ============================================================ */
/* تابلت */
@media (max-width:1024px){
    .school-messages-container{ grid-template-columns:300px 1fr; gap:16px; height:calc(100vh - 230px); }
    .school-conversations-list{ padding:18px; }
    .message-bubble{ max-width:80%; }
}

/* جوال */
@media (max-width:640px){
    .sa-msg-hero{ flex-direction:column; align-items:stretch; gap:14px; padding:18px; border-radius:16px; }
    .sa-msg-hero__count{ align-self:flex-start; padding:12px 20px; }
    .sa-msg-hero__title{ font-size:21px; }

    .school-messages-container{ grid-template-columns:1fr; gap:14px; height:auto; min-height:0; }
    .school-conversations-list{ padding:16px; border-radius:16px; max-height:none; }
    .school-chat-container{ border-radius:16px; min-height:62vh; }

    .sa-chat-head{ padding:14px 16px; }
    .sa-chat-body{ padding:16px; }
    .sa-compose{ padding:12px 14px; }
    .sa-compose__send{ padding:0 16px; }
    .message-bubble{ max-width:85%; }

    /* تحسين تقدّمي: على الجوال تصبح لوحة الدردشة شاشة كاملة عند فتح محادثة،
       وتُخفى تماماً (لا فراغ ميت) قبل اختيار أي محادثة — دون أي تعديل على JS */
    @supports selector(:has(*)){
        .school-chat-container:has(#chatInterface[style*="display: none"]){ display:none; }
        .school-chat-container:has(#chatInterface[style*="display: flex"]){
            position:fixed; inset:0; z-index:1300;
            border:none; border-radius:0; min-height:0; height:100%;
        }
    }
}
</style>

<!-- Page Header -->
<div class="sa-msg-hero">
    <div class="sa-msg-hero__left">
        <div class="sa-msg-hero__id">
            <div class="sa-msg-hero__icon">💬</div>
            <div>
                <h1 class="sa-msg-hero__title">الرسائل</h1>
                <p class="sa-msg-hero__sub">تواصل مع فريق المدرسة</p>
            </div>
        </div>
        <div class="sa-msg-hero__crumbs">
            <a href="{{ route('school-admin.dashboard') }}"><i class="fas fa-home"></i> الرئيسية</a>
            <span class="sep">›</span>
            <span class="cur">الرسائل</span>
        </div>
    </div>
    <div class="sa-msg-hero__count">
        <b>{{ $conversations->count() }}</b>
        <span>محادثة</span>
    </div>
</div>

<div class="school-messages-container">
    <!-- قائمة المحادثات -->
    <div class="school-conversations-list">
        <h3>
            <i class="fas fa-comments"></i>
            <span>المحادثات</span>
            @if($conversations->count() > 0)
                <span style="margin-right: auto; font-size: 13px; background: #667eea; color: white; padding: 3px 10px; border-radius: 7px;">{{ $conversations->count() }}</span>
            @endif
        </h3>

        <button class="school-new-conversation-btn" onclick="showUserSelect()">
            <i class="fas fa-plus-circle" style="font-size: 18px;"></i>
            <span>محادثة جديدة</span>
        </button>

        @forelse($conversations as $conversation)
            @php
                $otherUser = $conversation->getOtherUser(auth()->id());
                $unreadCount = $conversation->unreadCount(auth()->id());
            @endphp
            <div class="school-conversation-item" onclick="loadConversation({{ $otherUser->id }}, '{{ $otherUser->name }}')" data-user-id="{{ $otherUser->id }}">
                <div style="display: flex; align-items: center; gap: 13px;">
                    <div class="school-user-avatar" style="overflow: hidden;">
                        @if($otherUser->avatar)
                            <img src="{{ $otherUser->avatar_url }}" alt="{{ $otherUser->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            {{ mb_substr($otherUser->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="school-user-info">
                        <div class="school-user-name">
                            <span>{{ $otherUser->name }}</span>
                            @if($otherUser->role === 'super_admin')
                                <span class="school-role-badge rb-admin">
                                    <i class="fas fa-crown"></i> مدير النظام
                                </span>
                            @elseif($otherUser->role === 'teacher')
                                <span class="school-role-badge rb-teacher">
                                    <i class="fas fa-chalkboard-teacher"></i> معلم
                                </span>
                            @elseif($otherUser->role === 'student')
                                <span class="school-role-badge rb-student">
                                    <i class="fas fa-user-graduate"></i> طالب
                                </span>
                            @elseif($otherUser->role === 'parent')
                                <span class="school-role-badge rb-parent">
                                    <i class="fas fa-users"></i> ولي أمر
                                </span>
                            @endif
                        </div>
                        @if($conversation->lastMessage)
                            <div class="school-last-message">
                                {{ html_excerpt($conversation->lastMessage->message, 55) }}
                            </div>
                        @endif
                    </div>
                    @if($unreadCount > 0)
                        <span class="school-unread-badge">{{ $unreadCount }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="school-empty-state sa-empty-list">
                <div class="sa-empty-list__icon"><i class="fas fa-comments"></i></div>
                <h4>لا توجد محادثات حالياً</h4>
                <p>ابدأ محادثة مع المعلمين أو الطلاب أو أولياء الأمور</p>
            </div>
        @endforelse
    </div>

    <!-- منطقة الدردشة -->
    <div class="school-chat-container" id="chatContainer">
        <div class="school-empty-state sa-empty-hero" id="emptyState" style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="sa-empty-hero__icon"><i class="fas fa-envelope-open-text"></i></div>
            <h3>مرحباً بك في الرسائل</h3>
            <p>اختر محادثة من القائمة أو ابدأ محادثة جديدة للتواصل مع فريق مدرستك</p>
            <div class="sa-empty-hero__tip">
                <i class="fas fa-school"></i>
                <span>يمكنك مراسلة جميع أعضاء مدرستك</span>
            </div>
        </div>

        <!-- Chat Interface (مخفي في البداية) -->
        <div id="chatInterface" style="display: none; height: 100%; flex-direction: column;">
            <!-- Chat Header -->
            <div class="sa-chat-head">
                <div id="chatUserAvatar" class="sa-chat-head__avatar"></div>
                <div class="sa-chat-head__meta">
                    <h3 id="chatUserName" class="sa-chat-head__name"></h3>
                    <p id="chatUserRole" class="sa-chat-head__role"></p>
                </div>
                <button type="button" onclick="closeChat()" class="sa-chat-close" aria-label="إغلاق المحادثة">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Messages Container -->
            <div id="messagesContainer" class="chat-messages-container sa-chat-body">
                <!-- الرسائل ستظهر هنا -->
            </div>

            <!-- Message Input -->
            <div class="sa-compose">
                <form id="messageForm" onsubmit="sendMessage(event)" class="sa-compose__form">
                    <textarea
                        id="messageInput"
                        class="sa-compose__input"
                        placeholder="اكتب رسالتك هنا... (Ctrl+Enter للإرسال)"
                        rows="1"
                        oninput="autoResize(this)"
                        onkeydown="handleKeyPress(event)"
                    ></textarea>
                    <button
                        type="submit"
                        id="sendBtn"
                        class="sa-compose__send"
                    >
                        <span>إرسال</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// متغيرات عامة للـ Real-Time System
window.currentUserId = {{ auth()->id() }};
window.currentChatUserId = null;

let currentChatUserId = null;
let currentChatUserName = null;

// دالة عامة لفتح محادثة
window.loadConversation = function(userId, userName) {
    // تحديث المتغير العام للـ Real-Time
    window.currentChatUserId = userId;
    currentChatUserId = userId;
    currentChatUserName = userName;

    // تحديث الـ Real-Time System
    if (window.messagesRealTime) {
        window.messagesRealTime.currentConversationUserId = userId;
        console.log('✅ Real-Time updated to conversation:', userId);
    }

    // تحديث active state
    document.querySelectorAll('.school-conversation-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.userId == userId) {
            item.classList.add('active');
        }
    });

    // إخفاء empty state وإظهار chat interface
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('chatInterface').style.display = 'flex';

    // جلب المحادثة
    fetch(`/school-admin/messages/conversation/${userId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayConversation(data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء تحميل المحادثة');
    });
}

function displayConversation(data) {
    const { messages, otherUser, currentUser } = data;

    // تحديث header
    document.getElementById('chatUserAvatar').textContent = otherUser.name.substring(0, 2);
    document.getElementById('chatUserName').textContent = otherUser.name;

    let roleText = '';
    if (otherUser.role === 'super_admin') roleText = '👑 مدير النظام';
    else if (otherUser.role === 'teacher') roleText = '👨‍🏫 معلم';
    else if (otherUser.role === 'student') roleText = '👨‍🎓 طالب';
    else if (otherUser.role === 'parent') roleText = '👨‍👩‍👧 ولي أمر';
    document.getElementById('chatUserRole').textContent = roleText;

    // عرض الرسائل
    const messagesContainer = document.getElementById('messagesContainer');
    messagesContainer.innerHTML = '';

    if (messages.length === 0) {
        messagesContainer.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #94a3b8;">
                <i class="fas fa-comments" style="font-size: 48px; margin-bottom: 16px; color: #cbd5e1;"></i>
                <p style="font-size: 15px; font-weight: 600; color: #64748b;">لا توجد رسائل بعد</p>
                <p style="font-size: 13px; margin-top: 8px;">ابدأ المحادثة بإرسال أول رسالة!</p>
            </div>
        `;
    } else {
        messages.forEach(message => {
            const isSent = message.sender_id === currentUser.id;
            const messageHtml = createMessageElement(message, isSent);
            messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
        });
        scrollToBottom();
    }

    // Focus على input
    document.getElementById('messageInput').focus();
}

// دالة عامة لإنشاء عنصر رسالة
window.createMessageElement = function(message, isSent) {
    const time = new Date(message.created_at).toLocaleTimeString('ar-SA', {
        hour: '2-digit',
        minute: '2-digit'
    });

    const messageClass = isSent ? 'sent' : 'received';

    return `
        <div class="message ${messageClass}" data-message-id="${message.id}">
            <div class="message-bubble">
                <div class="message-text">${escapeHtml(messageToLines(message.message))}</div>
                <div class="message-time">${time}</div>
            </div>
        </div>
    `;
}

function createMessageElement(message, isSent) {
    return window.createMessageElement(message, isSent);
}

function sendMessage(event) {
    event.preventDefault();

    if (!currentChatUserId) {
        alert('الرجاء اختيار محادثة أولاً');
        return;
    }

    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();

    if (!message) return;

    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span>جاري الإرسال...</span>';

    fetch('/school-admin/messages/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            receiver_id: currentChatUserId,
            message: message
        })
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || 'حدث خطأ');
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            const messageHtml = createMessageElement(data.message, true);
            document.getElementById('messagesContainer').insertAdjacentHTML('beforeend', messageHtml);
            messageInput.value = '';
            messageInput.style.height = 'auto';
            scrollToBottom();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'حدث خطأ أثناء إرسال الرسالة');
    })
    .finally(() => {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<span>إرسال</span><i class="fas fa-paper-plane"></i>';
        messageInput.focus();
    });
}

function closeChat() {
    currentChatUserId = null;
    currentChatUserName = null;

    // مسح المتغير العام
    window.currentChatUserId = null;

    // تحديث الـ Real-Time System
    if (window.messagesRealTime) {
        window.messagesRealTime.currentConversationUserId = null;
        console.log('✅ Real-Time conversation closed');
    }

    document.getElementById('emptyState').style.display = 'flex';
    document.getElementById('chatInterface').style.display = 'none';

    document.querySelectorAll('.school-conversation-item').forEach(item => {
        item.classList.remove('active');
    });
}

// دالة عامة للتمرير للأسفل
window.scrollToBottom = function() {
    const container = document.getElementById('messagesContainer');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function scrollToBottom() {
    window.scrollToBottom();
}

function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

function handleKeyPress(event) {
    if (event.ctrlKey && event.key === 'Enter') {
        event.preventDefault();
        document.getElementById('messageForm').dispatchEvent(new Event('submit'));
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// يحوّل فواصل الأسطر إلى أسطر نصّية: div/p/br القادمة من المحرّر الغنيّ (فكانت تظهر
// كوسوم &lt;div&gt; مهرَّبة) و\n من الـtextarea، وينزع باقي الوسوم (عرض نصّيّ آمن).
// النتيجة تُمرَّر لـescapeHtml وتُعرَض بـwhite-space:pre-line فتظهر الأسطر الجديدة صحيحة.
function messageToLines(html) {
    var s = String(html == null ? '' : html);
    s = s.replace(/<br\s*\/?>/gi, '\n');
    s = s.replace(/<\/(div|p)>/gi, '');
    s = s.replace(/<(div|p)[^>]*>/gi, '\n');
    s = s.replace(/<[^>]+>/g, '');
    var ta = document.createElement('textarea');
    ta.innerHTML = s;                 // فكّ الكيانات (&lt; &amp; &nbsp; ...)
    s = ta.value;
    s = s.replace(/[\u200B\u200C\u200D\u2060\uFEFF]/g, '').replace(/\u00A0/g, ' ');
    s = s.replace(/\n{3,}/g, '\n\n');
    return s.replace(/^\n+|\n+$/g, '');
}
</script>

<!-- مودال اختيار مستخدم -->
@include('messages.partials.user-select-modal', ['availableUsers' => $availableUsers, 'role' => 'school-admin'])

@endsection
