@extends('layouts.admin')

@section('page-title', 'نظام الرسائل - مدير النظام')

@section('content')
<!-- Page Header -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 30px; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3); color: white;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
                <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                    💬
                </div>
                <div>
                    <h1 style="font-size: 28px; font-weight: 800; margin: 0;">نظام الرسائل الشامل</h1>
                    <p style="margin: 4px 0 0 0; opacity: 0.9; font-size: 15px;">إدارة جميع محادثات المنصة</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.95;">
                <a href="{{ route('dashboard') }}" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                    <span style="width: 24px; height: 24px; background: rgba(255,255,255,0.2); border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;"><i class="fas fa-home" style="font-size: 12px;"></i></span> لوحة البيانات
                </a>
                <span>›</span>
                <span style="font-weight: 600;">الرسائل</span>
            </div>
        </div>
        <div style="text-align: center; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 16px 24px;">
            <div style="font-size: 32px; font-weight: 800; margin-bottom: 4px;">{{ $conversations->count() }}</div>
            <div style="font-size: 13px; opacity: 0.9;">محادثة نشطة</div>
        </div>
    </div>
</div>

<style>
.admin-messages-container {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 24px;
    height: calc(100vh - 320px);
}

.admin-conversations-list {
    background: white;
    border-radius: 18px;
    padding: 28px;
    overflow-y: auto;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    border: 2px solid #e2e8f0;
}

.admin-conversations-list h3 {
    font-size: 20px;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 20px 0;
    padding-bottom: 16px;
    border-bottom: 3px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 10px;
}

.admin-new-conversation-btn {
    width: 100%;
    padding: 16px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
}

.admin-new-conversation-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(102, 126, 234, 0.5);
}

.admin-conversation-item {
    padding: 16px;
    border-radius: 14px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #e2e8f0;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.admin-conversation-item:hover,
.admin-conversation-item.active {
    background: white;
    border-color: #667eea;
    transform: translateX(-5px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.2);
}

.admin-conversation-item.active {
    border-color: #764ba2;
    background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
}

.admin-user-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 800;
    font-size: 20px;
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.admin-user-info {
    flex: 1;
}

.admin-user-name {
    font-weight: 700;
    font-size: 16px;
    color: #1e293b;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.admin-role-badge {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 600;
}

.admin-last-message {
    font-size: 13px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.admin-unread-badge {
    background: #ef4444;
    color: white;
    border-radius: 14px;
    padding: 4px 12px;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.admin-chat-container {
    background: white;
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    border: 2px solid #e2e8f0;
}

.admin-empty-state {
    text-align: center;
    padding: 40px;
    color: #64748b;
}

.admin-empty-state h3 {
    font-size: 24px;
    font-weight: 700;
    color: #475569;
    margin: 16px 0 12px 0;
}

.admin-empty-state p {
    font-size: 16px;
    color: #94a3b8;
    margin: 4px 0;
}

/* Premium Icon Containers */
.icon-box {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    flex-shrink: 0;
}

.icon-box-sm {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    font-size: 14px;
}

.icon-box-md {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    font-size: 16px;
}

.icon-box-lg {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    font-size: 24px;
}

.icon-box-xl {
    width: 80px;
    height: 80px;
    border-radius: 18px;
    font-size: 32px;
}

.icon-box-purple {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
}

.icon-box-blue {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
}

.icon-box-green {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
}

.icon-box-slate {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    color: #475569;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* Inline Chat Styles */
.chat-header-inline {
    padding: 20px 24px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.06) 0%, rgba(118, 75, 162, 0.06) 100%);
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 16px;
}

.chat-messages-inline {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
    background: linear-gradient(180deg, #fafbfc 0%, #f1f5f9 100%);
}

.chat-messages-inline::-webkit-scrollbar {
    width: 6px;
}

.chat-messages-inline::-webkit-scrollbar-track {
    background: transparent;
}

.chat-messages-inline::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

.message-row {
    margin-bottom: 8px;
    display: flex;
    gap: 8px;
    animation: msgSlide 0.3s ease;
}

@keyframes msgSlide {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.message-row.sent {
    flex-direction: row-reverse;
}

.message-bubble-inline {
    max-width: 70%;
    padding: 8px 12px;
    border-radius: 14px;
    word-wrap: break-word;
    white-space: pre-wrap;
    line-height: 1.5;
    font-size: 13px;
    transition: transform 0.2s;
}

.message-bubble-inline:hover {
    transform: translateY(-1px);
}

.message-row.received .message-bubble-inline {
    background: white;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    border-bottom-right-radius: 4px;
    color: #1e293b;
}

.message-row.sent .message-bubble-inline {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 14px rgba(102, 126, 234, 0.3);
    border-bottom-left-radius: 4px;
}

.message-time-inline {
    font-size: 10px;
    color: #94a3b8;
    margin-top: 4px;
}

.message-row.sent .message-time-inline {
    color: rgba(255, 255, 255, 0.7);
}

.chat-input-inline {
    padding: 20px 24px;
    background: white;
    border-top: 2px solid #f1f5f9;
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.chat-input-inline textarea {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    resize: none;
    font-family: inherit;
    font-size: 14px;
    max-height: 100px;
    min-height: 48px;
    transition: all 0.3s;
    background: #f8fafc;
    line-height: 1.5;
}

.chat-input-inline textarea:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.12);
}

.chat-send-btn {
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 14px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
    height: 48px;
    white-space: nowrap;
}

.chat-send-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.45);
}

.chat-send-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.toolbar-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 12px;
    transition: all 0.2s ease;
}

.toolbar-btn:hover {
    background: #e2e8f0;
    color: #334155;
}

.toolbar-btn:active {
    background: #667eea;
    color: white;
}

.rich-editor:empty::before {
    content: attr(data-placeholder);
    color: #94a3b8;
    pointer-events: none;
}

.rich-editor img {
    max-width: 100%;
    border-radius: 8px;
    margin: 4px 0;
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<div class="admin-messages-container">
    <!-- قائمة المحادثات -->
    <div class="admin-conversations-list">
        <h3>
            <span class="icon-box icon-box-sm icon-box-purple"><i class="fas fa-inbox"></i></span>
            <span>جميع المحادثات</span>
            <span style="margin-right: auto; font-size: 14px; background: #667eea; color: white; padding: 4px 12px; border-radius: 8px;">{{ $conversations->count() }}</span>
        </h3>
        
        <button class="admin-new-conversation-btn" onclick="showUserSelect()">
            <span class="icon-box icon-box-sm" style="background: rgba(255,255,255,0.2); color: white;"><i class="fas fa-plus-circle"></i></span>
            <span>بدء محادثة جديدة</span>
        </button>
        
        @forelse($conversations as $conversation)
            @php
                $otherUser = $conversation->getOtherUser(auth()->id());
                $unreadCount = $conversation->unreadCount(auth()->id());
            @endphp
            <div class="admin-conversation-item" data-user-id="{{ $otherUser->id }}" onclick="loadConversation({{ $otherUser->id }}, '{{ addslashes($otherUser->name) }}', this)">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div class="admin-user-avatar" style="overflow: hidden;">
                        @if($otherUser->avatar)
                            <img src="{{ $otherUser->avatar_url }}" alt="{{ $otherUser->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            {{ mb_substr($otherUser->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="admin-user-info">
                        <div class="admin-user-name">
                            <span>{{ $otherUser->name }}</span>
                            @if($otherUser->role === 'school_admin')
                                <span class="admin-role-badge" style="background: #e0e7ff; color: #4338ca;">
                                    <i class="fas fa-user-tie"></i> مدير مدرسة
                                </span>
                            @elseif($otherUser->role === 'teacher')
                                <span class="admin-role-badge" style="background: #dbeafe; color: #1e40af;">
                                    <i class="fas fa-chalkboard-teacher"></i> معلم
                                </span>
                            @elseif($otherUser->role === 'student')
                                <span class="admin-role-badge" style="background: #dcfce7; color: #166534;">
                                    <i class="fas fa-user-graduate"></i> طالب
                                </span>
                            @elseif($otherUser->role === 'parent')
                                <span class="admin-role-badge" style="background: #fef3c7; color: #92400e;">
                                    <i class="fas fa-users"></i> ولي أمر
                                </span>
                            @endif
                        </div>
                        @if($conversation->lastMessage)
                            <div class="admin-last-message">
                                {{ html_excerpt($conversation->lastMessage->message, 60) }}
                            </div>
                        @endif
                        @if($otherUser->school)
                            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                <i class="fas fa-school" style="font-size: 10px;"></i> {{ $otherUser->school->name }}
                            </div>
                        @endif
                    </div>
                    @if($unreadCount > 0)
                        <span class="admin-unread-badge">{{ $unreadCount }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="admin-empty-state" style="padding: 60px 20px;">
                <span class="icon-box icon-box-lg icon-box-slate" style="margin: 0 auto 16px;"><i class="fas fa-comments"></i></span>
                <h4 style="font-size: 18px; font-weight: 700; color: #475569; margin: 16px 0 8px 0;">لا توجد محادثات حالياً</h4>
                <p style="font-size: 14px;">ابدأ محادثة جديدة مع أي مستخدم في المنصة</p>
            </div>
        @endforelse
    </div>

    <!-- منطقة الدردشة -->
    <div class="admin-chat-container" id="chatContainer">
        <div class="admin-empty-state" id="emptyState" style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <span class="icon-box icon-box-xl icon-box-purple" style="margin-bottom: 20px;">
                <i class="fas fa-envelope-open"></i>
            </span>
            <h3>نظام الرسائل الشامل</h3>
            <p>اختر محادثة من القائمة أو ابدأ محادثة جديدة</p>
            <p style="font-size: 13px; margin-top: 12px; padding: 12px 20px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 10px; color: #1e40af; display: flex; align-items: center; gap: 8px; border: 1px solid #bae6fd;">
                <span class="icon-box icon-box-sm icon-box-blue"><i class="fas fa-info-circle" style="font-size: 12px;"></i></span>
                يمكنك مراسلة جميع مستخدمي المنصة كمدير نظام
            </p>
        </div>
    </div>
</div>

<!-- مودال اختيار مستخدم -->
@include('messages.partials.user-select-modal', ['availableUsers' => $availableUsers, 'role' => 'admin'])

<script>
let currentUserId = null;
let currentUserName = '';
let refreshInterval = null;

function loadConversation(userId, userName, clickedEl) {
    currentUserId = userId;
    currentUserName = userName;

    // تحديث الحالة النشطة
    document.querySelectorAll('.admin-conversation-item').forEach(el => el.classList.remove('active'));
    if (clickedEl) clickedEl.classList.add('active');

    // عرض حالة التحميل
    const container = document.getElementById('chatContainer');
    container.innerHTML = `
        <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;">
            <div class="loading-spinner" style="width: 40px; height: 40px; border-width: 4px; border-color: rgba(102,126,234,0.2); border-top-color: #667eea;"></div>
            <p style="color: #64748b; font-weight: 600;">جاري تحميل المحادثة...</p>
        </div>`;

    // جلب المحادثة عبر AJAX
    fetch('/messages/conversation/' + userId, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderChat(data);
            startAutoRefresh(userId);
        } else {
            container.innerHTML = `
                <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span class="icon-box icon-box-lg" style="background: linear-gradient(135deg, #fecaca, #fca5a5); color: #dc2626; margin-bottom: 16px;"><i class="fas fa-exclamation-triangle"></i></span>
                    <p style="color: #ef4444; font-weight: 600;">${data.error || 'حدث خطأ'}</p>
                </div>`;
        }
    })
    .catch(err => {
        container.innerHTML = `
            <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <span class="icon-box icon-box-lg" style="background: linear-gradient(135deg, #fecaca, #fca5a5); color: #dc2626; margin-bottom: 16px;"><i class="fas fa-exclamation-triangle"></i></span>
                <p style="color: #ef4444; font-weight: 600;">حدث خطأ في الاتصال</p>
            </div>`;
    });

    // إزالة badge عدد الرسائل غير المقروءة
    if (clickedEl) {
        const badge = clickedEl.querySelector('.admin-unread-badge');
        if (badge) badge.remove();
    }
}

function renderChat(data) {
    const container = document.getElementById('chatContainer');
    const otherUser = data.otherUser;
    const messages = data.messages;
    const currentUser = data.currentUser;

    let messagesHtml = '';
    if (messages.length === 0) {
        messagesHtml = `
            <div style="text-align: center; padding: 60px 20px;">
                <span class="icon-box icon-box-lg icon-box-slate" style="margin: 0 auto 16px;"><i class="fas fa-comments"></i></span>
                <h4 style="font-size: 18px; font-weight: 700; color: #475569; margin: 12px 0 6px;">💬 لا توجد رسائل بعد</h4>
                <p style="font-size: 14px; color: #94a3b8;">ابدأ المحادثة بإرسال رسالة!</p>
            </div>`;
    } else {
        messages.forEach(msg => {
            const isSent = msg.sender_id == currentUser.id;
            const time = new Date(msg.created_at).toLocaleString('ar-SA', { hour: '2-digit', minute: '2-digit', month: 'short', day: 'numeric' });
            messagesHtml += `
                <div class="message-row ${isSent ? 'sent' : 'received'}">
                    <div class="message-bubble-inline">
                        ${msg.message}
                        <div class="message-time-inline">${time}</div>
                    </div>
                </div>`;
        });
    }

    // تحديد الدور بالعربي
    let roleText = '';
    let roleStyle = '';
    if (otherUser.role === 'school_admin') {
        roleText = '<i class="fas fa-user-tie"></i> مدير مدرسة';
        roleStyle = 'background: #e0e7ff; color: #4338ca;';
    } else if (otherUser.role === 'teacher') {
        roleText = '<i class="fas fa-chalkboard-teacher"></i> معلم';
        roleStyle = 'background: #dbeafe; color: #1e40af;';
    } else if (otherUser.role === 'student') {
        roleText = '<i class="fas fa-user-graduate"></i> طالب';
        roleStyle = 'background: #dcfce7; color: #166534;';
    } else if (otherUser.role === 'parent') {
        roleText = '<i class="fas fa-users"></i> ولي أمر';
        roleStyle = 'background: #fef3c7; color: #92400e;';
    }

    container.innerHTML = `
        <div class="chat-header-inline">
            <div class="admin-user-avatar" style="width: 48px; height: 48px; font-size: 18px;">
                ${otherUser.name.substring(0, 2)}
            </div>
            <div style="flex: 1;">
                <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #1e293b;">${escapeHtml(otherUser.name)}</h3>
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                    <span style="font-size: 13px; color: #64748b;"><i class="fas fa-envelope" style="margin-left: 4px; font-size: 11px;"></i> ${otherUser.email}</span>
                    ${roleText ? `<span style="font-size: 11px; padding: 3px 8px; border-radius: 6px; font-weight: 600; ${roleStyle}">${roleText}</span>` : ''}
                </div>
            </div>
        </div>
        <div class="chat-messages-inline" id="messagesArea">${messagesHtml}</div>
        <div class="chat-input-inline" style="flex-direction: column; gap: 8px;">
            <div class="editor-toolbar" style="display: flex; align-items: center; gap: 4px; padding: 6px 10px; background: #f8fafc; border-radius: 10px; border: 2px solid #e2e8f0; flex-wrap: wrap;">
                <button type="button" class="toolbar-btn" onclick="execCmd('bold')" title="غامق"><i class="fas fa-bold"></i></button>
                <button type="button" class="toolbar-btn" onclick="execCmd('italic')" title="مائل"><i class="fas fa-italic"></i></button>
                <button type="button" class="toolbar-btn" onclick="execCmd('underline')" title="تسطير"><i class="fas fa-underline"></i></button>
                <div style="width: 1px; height: 22px; background: #e2e8f0; margin: 0 3px;"></div>
                <button type="button" class="toolbar-btn" onclick="execCmd('justifyRight')" title="محاذاة يمين"><i class="fas fa-align-right"></i></button>
                <button type="button" class="toolbar-btn" onclick="execCmd('justifyCenter')" title="محاذاة وسط"><i class="fas fa-align-center"></i></button>
                <button type="button" class="toolbar-btn" onclick="execCmd('justifyLeft')" title="محاذاة يسار"><i class="fas fa-align-left"></i></button>
                <div style="width: 1px; height: 22px; background: #e2e8f0; margin: 0 3px;"></div>
                <button type="button" class="toolbar-btn" onclick="insertEditorLink()" title="إدراج رابط"><i class="fas fa-link"></i></button>
                <button type="button" class="toolbar-btn" onclick="document.getElementById('adminChatImageUpload').click()" title="إدراج صورة"><i class="fas fa-image"></i></button>
                <input type="file" id="adminChatImageUpload" accept="image/*" style="display: none;" onchange="insertEditorImage(this)">
            </div>
            <div style="display: flex; gap: 12px; align-items: flex-end; width: 100%;">
                <div id="inlineMessageInput" contenteditable="true" class="rich-editor" style="flex: 1; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 14px; min-height: 48px; max-height: 150px; overflow-y: auto; font-family: inherit; font-size: 14px; line-height: 1.5; background: #f8fafc; outline: none; transition: all 0.3s; direction: rtl;" data-placeholder="اكتب رسالتك هنا... (Ctrl+Enter للإرسال)" onkeydown="handleInlineKeyPress(event)" onfocus="this.style.borderColor='#667eea'; this.style.background='white'; this.style.boxShadow='0 4px 16px rgba(102,126,234,0.12)'" onblur="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc'; this.style.boxShadow='none'"></div>
                <button class="chat-send-btn" id="inlineSendBtn" onclick="sendInlineMessage()">
                    <span>إرسال</span>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>`;

    // التمرير لأسفل
    const area = document.getElementById('messagesArea');
    area.scrollTop = area.scrollHeight;

    // التركيز على حقل الإدخال
    document.getElementById('inlineMessageInput').focus();
}

function handleInlineKeyPress(event) {
    if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
        sendInlineMessage();
        event.preventDefault();
    }
}

function sendInlineMessage() {
    const input = document.getElementById('inlineMessageInput');
    const btn = document.getElementById('inlineSendBtn');
    const message = input.innerHTML.trim();

    if (!message || !currentUserId) return;

    btn.disabled = true;
    btn.innerHTML = '<span>جاري الإرسال...</span><div class="loading-spinner" style="width: 16px; height: 16px; border-width: 2px;"></div>';

    fetch('{{ route("messages.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            receiver_id: currentUserId,
            message: message
        })
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('استجابة غير صالحة');
        }
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || data.message || 'حدث خطأ');
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            const area = document.getElementById('messagesArea');
            // إزالة رسالة "لا توجد رسائل"
            const emptyDiv = area.querySelector('div[style*="text-align: center"]');
            if (emptyDiv) emptyDiv.remove();

            const now = new Date().toLocaleString('ar-SA', { hour: '2-digit', minute: '2-digit', month: 'short', day: 'numeric' });
            area.insertAdjacentHTML('beforeend', `
                <div class="message-row sent">
                    <div class="message-bubble-inline">
                        ${escapeHtml(message)}
                        <div class="message-time-inline">${now}</div>
                    </div>
                </div>`);

            input.innerHTML = '';
            area.scrollTop = area.scrollHeight;

            // تحديث آخر رسالة في القائمة
            const convItem = document.querySelector(`.admin-conversation-item[data-user-id="${currentUserId}"]`);
            if (convItem) {
                const lastMsg = convItem.querySelector('.admin-last-message');
                if (lastMsg) {
                    lastMsg.textContent = message.length > 60 ? message.substring(0, 60) + '...' : message;
                }
            }
        } else {
            showMsgError(data.error || 'حدث خطأ');
        }
    })
    .catch(err => {
        showMsgError(err.message || 'حدث خطأ في الإرسال');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<span>إرسال</span><i class="fas fa-paper-plane"></i>';
        input.focus();
    });
}

function startAutoRefresh(userId) {
    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(() => {
        if (currentUserId !== userId) return;
        fetch('/messages/check-new/' + userId, { headers: { 'Accept': 'application/json' }})
            .then(r => {
                if (!r.ok) return null;
                return r.json();
            })
            .then(data => {
                if (!data) return;
                if (data.messages && data.messages.length > 0) {
                    const area = document.getElementById('messagesArea');
                    if (!area) return;
                    data.messages.forEach(msg => {
                        const time = new Date(msg.created_at).toLocaleString('ar-SA', { hour: '2-digit', minute: '2-digit', month: 'short', day: 'numeric' });
                        area.insertAdjacentHTML('beforeend', `
                            <div class="message-row received">
                                <div class="message-bubble-inline">
                                    ${msg.message}
                                    <div class="message-time-inline">${time}</div>
                                </div>
                            </div>`);
                    });
                    area.scrollTop = area.scrollHeight;
                }
            }).catch(() => {});
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// دوال محرر النصوص
function execCmd(command) {
    document.execCommand(command, false, null);
    const editor = document.getElementById('inlineMessageInput');
    if (editor) editor.focus();
}

function insertEditorLink() {
    const editor = document.getElementById('inlineMessageInput');
    // حفظ موضع المؤشر
    const sel = window.getSelection();
    if (sel.rangeCount > 0) {
        window._savedRange = sel.getRangeAt(0).cloneRange();
    }
    
    // فتح المودال
    let modal = document.getElementById('adminLinkModal');
    if (!modal) {
        // إنشاء المودال ديناميكياً
        modal = document.createElement('div');
        modal.id = 'adminLinkModal';
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;z-index:10000;animation:linkFadeIn 0.25s ease';
        modal.onclick = function(e) { if(e.target === modal) closeAdminLinkModal(); };
        modal.innerHTML = `
            <div style="background:white;border-radius:20px;padding:32px;width:90%;max-width:480px;box-shadow:0 25px 60px rgba(0,0,0,0.25);animation:linkSlideUp 0.3s cubic-bezier(0.4,0,0.2,1)">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #f1f5f9">
                    <h3 style="margin:0;font-size:20px;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:10px"><i class="fas fa-link" style="color:#667eea"></i> إدراج رابط</h3>
                    <button onclick="closeAdminLinkModal()" style="width:36px;height:36px;border-radius:50%;border:2px solid #e2e8f0;background:#f8fafc;color:#64748b;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s" onmouseover="this.style.background='#ef4444';this.style.borderColor='#ef4444';this.style.color='white'" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0';this.style.color='#64748b'">×</button>
                </div>
                <div style="margin-bottom:20px">
                    <label style="display:block;font-size:14px;font-weight:600;color:#334155;margin-bottom:8px"><i class="fas fa-globe" style="color:#667eea;margin-left:6px"></i> عنوان الرابط (URL)</label>
                    <input type="url" id="adminLinkUrl" placeholder="https://example.com" dir="ltr" style="width:100%;padding:14px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;transition:all 0.3s;background:#f8fafc;direction:ltr;text-align:left" onfocus="this.style.borderColor='#667eea';this.style.background='white';this.style.boxShadow='0 4px 16px rgba(102,126,234,0.15)'" onblur="this.style.borderColor='#e2e8f0';this.style.background='#f8fafc';this.style.boxShadow='none'" onkeydown="if(event.key==='Enter'){event.preventDefault();confirmAdminLink()}">
                    <div style="font-size:12px;color:#94a3b8;margin-top:6px">أدخل الرابط الكامل بما في ذلك https://</div>
                </div>
                <div style="margin-bottom:20px">
                    <label style="display:block;font-size:14px;font-weight:600;color:#334155;margin-bottom:8px"><i class="fas fa-font" style="color:#764ba2;margin-left:6px"></i> نص العرض (اختياري)</label>
                    <input type="text" id="adminLinkText" placeholder="اضغط هنا" dir="rtl" style="width:100%;padding:14px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;transition:all 0.3s;background:#f8fafc" onfocus="this.style.borderColor='#667eea';this.style.background='white';this.style.boxShadow='0 4px 16px rgba(102,126,234,0.15)'" onblur="this.style.borderColor='#e2e8f0';this.style.background='#f8fafc';this.style.boxShadow='none'" onkeydown="if(event.key==='Enter'){event.preventDefault();confirmAdminLink()}">
                    <div style="font-size:12px;color:#94a3b8;margin-top:6px">النص الذي سيظهر في الرسالة بدل الرابط</div>
                </div>
                <div style="display:flex;gap:12px;margin-top:24px">
                    <button onclick="confirmAdminLink()" style="flex:1;padding:14px 24px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border:none;border-radius:12px;font-weight:700;font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 6px 20px rgba(102,126,234,0.35);transition:all 0.3s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'"><i class="fas fa-check-circle"></i> إدراج الرابط</button>
                    <button onclick="closeAdminLinkModal()" style="padding:14px 24px;background:#f1f5f9;color:#475569;border:2px solid #e2e8f0;border-radius:12px;font-weight:600;font-size:15px;cursor:pointer;transition:all 0.2s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">إلغاء</button>
                </div>
            </div>`;
        document.body.appendChild(modal);
        
        // إضافة CSS للأنميشن إن لم تكن موجودة
        if (!document.getElementById('linkModalStyles')) {
            const style = document.createElement('style');
            style.id = 'linkModalStyles';
            style.textContent = '@keyframes linkFadeIn{from{opacity:0}to{opacity:1}}@keyframes linkSlideUp{from{opacity:0;transform:translateY(30px) scale(0.95)}to{opacity:1;transform:translateY(0) scale(1)}}';
            document.head.appendChild(style);
        }
    } else {
        modal.style.display = 'flex';
    }
    
    document.getElementById('adminLinkUrl').value = '';
    document.getElementById('adminLinkText').value = '';
    setTimeout(() => document.getElementById('adminLinkUrl').focus(), 200);
}

function confirmAdminLink() {
    let url = document.getElementById('adminLinkUrl').value.trim();
    let text = document.getElementById('adminLinkText').value.trim();
    
    if (!url) {
        document.getElementById('adminLinkUrl').style.borderColor = '#ef4444';
        document.getElementById('adminLinkUrl').focus();
        return;
    }
    
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        url = 'https://' + url;
    }
    
    closeAdminLinkModal();
    
    const editor = document.getElementById('inlineMessageInput');
    editor.focus();
    
    if (window._savedRange) {
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(window._savedRange);
    }
    
    if (text) {
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.style.color = '#667eea';
        link.style.textDecoration = 'underline';
        link.textContent = text;
        
        const sel = window.getSelection();
        if (sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            range.deleteContents();
            range.insertNode(link);
            range.setStartAfter(link);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
        }
    } else {
        document.execCommand('createLink', false, url);
        editor.querySelectorAll('a').forEach(a => {
            a.target = '_blank';
            a.style.color = '#667eea';
            a.style.textDecoration = 'underline';
        });
    }
}

function closeAdminLinkModal() {
    const modal = document.getElementById('adminLinkModal');
    if (modal) modal.style.display = 'none';
    const editor = document.getElementById('inlineMessageInput');
    if (editor) editor.focus();
}

function insertEditorImage(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'image');
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route("admin.theme.upload") }}', {
        method: 'POST',
        body: formData
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            const editor = document.getElementById('inlineMessageInput');
            if (editor) {
                editor.focus();
                document.execCommand('insertImage', false, data.url);
            }
        } else {
            showMsgError('فشل رفع الصورة');
        }
    })
    .catch(() => showMsgError('حدث خطأ أثناء رفع الصورة'));
    
    input.value = '';
}

function showMsgError(message) {
    const toast = document.createElement('div');
    toast.style.cssText = 'position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 14px 24px; border-radius: 12px; box-shadow: 0 8px 24px rgba(239,68,68,0.35); z-index: 9999; font-weight: 600; display: flex; align-items: center; gap: 8px;';
    // حماية XSS: نستخدم textContent للنص و عنصر منفصل للأيقونة
    const icon = document.createElement('i');
    icon.className = 'fas fa-exclamation-circle';
    const textSpan = document.createElement('span');
    textSpan.textContent = String(message ?? '');
    toast.appendChild(icon);
    toast.appendChild(textSpan);
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 3000);
}

// Override startConversation from the modal partial to load inline
const originalStartConversation = typeof startConversation === 'function' ? startConversation : null;
window.startConversation = function(userId) {
    hideUserSelect();
    // Get user name from the modal
    const userItem = document.querySelector(`.user-list-item[onclick*="${userId}"]`);
    const userName = userItem ? userItem.getAttribute('data-name') : '';
    
    // Try to find existing conversation item
    const existingItem = document.querySelector(`.admin-conversation-item[data-user-id="${userId}"]`);
    loadConversation(userId, userName, existingItem);
};
</script>

@endsection
