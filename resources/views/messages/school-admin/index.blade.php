@extends('layouts.school-admin')

@section('page-title', 'الرسائل - مدير المدرسة')

@section('content')
<!-- Page Header -->
<div style="background: white; border-radius: 16px; padding: 24px 28px; margin-bottom: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); border-right: 5px solid #667eea;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 10px;">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 22px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">
                    💬
                </div>
                <div>
                    <h1 style="font-size: 26px; font-weight: 700; color: #1e293b; margin: 0;">الرسائل</h1>
                    <p style="margin: 4px 0 0 0; color: #64748b; font-size: 14px;">تواصل مع فريق المدرسة</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #64748b;">
                <a href="{{ route('school-admin.dashboard') }}" style="color: #667eea; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.color='#764ba2'" onmouseout="this.style.color='#667eea'">
                    <i class="fas fa-home"></i> الرئيسية
                </a>
                <span style="color: #cbd5e1;">›</span>
                <span style="color: #1e293b; font-weight: 600;">الرسائل</span>
            </div>
        </div>
        <div style="text-align: center; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 12px; padding: 16px 24px; border: 2px solid #3b82f6;">
            <div style="font-size: 28px; font-weight: 800; color: #1e40af; margin-bottom: 4px;">{{ $conversations->count() }}</div>
            <div style="font-size: 12px; color: #3b82f6; font-weight: 600;">محادثة</div>
        </div>
    </div>
</div>

<style>
.school-messages-container {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 22px;
    height: calc(100vh - 300px);
}

.school-conversations-list {
    background: white;
    border-radius: 16px;
    padding: 26px;
    overflow-y: auto;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.09);
    border: 2px solid #e2e8f0;
}

.school-conversations-list h3 {
    font-size: 19px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 18px 0;
    padding-bottom: 14px;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 10px;
}

.school-new-conversation-btn {
    width: 100%;
    padding: 15px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 13px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    box-shadow: 0 5px 18px rgba(102, 126, 234, 0.35);
    transition: all 0.3s ease;
}

.school-new-conversation-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 7px 24px rgba(102, 126, 234, 0.45);
}

.school-conversation-item {
    padding: 15px;
    border-radius: 13px;
    margin-bottom: 11px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
}

.school-conversation-item:hover {
    background: white;
    border-color: #667eea;
    transform: translateX(-4px);
    box-shadow: 0 5px 16px rgba(102, 126, 234, 0.18);
}

.school-user-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 19px;
    box-shadow: 0 5px 14px rgba(102, 126, 234, 0.35);
}

.school-user-info {
    flex: 1;
}

.school-user-name {
    font-weight: 600;
    font-size: 15px;
    color: #1e293b;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.school-role-badge {
    font-size: 11px;
    padding: 3px 9px;
    border-radius: 6px;
    font-weight: 600;
}

.school-last-message {
    font-size: 13px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.school-unread-badge {
    background: #ef4444;
    color: white;
    border-radius: 12px;
    padding: 3px 11px;
    font-size: 12px;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(239, 68, 68, 0.3);
}

.school-chat-container {
    background: white;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.09);
    border: 2px solid #e2e8f0;
}

.school-empty-state {
    text-align: center;
    padding: 40px;
    color: #64748b;
}

.school-empty-state i {
    font-size: 58px;
    color: #cbd5e1;
    margin-bottom: 18px;
}

.school-empty-state h3 {
    font-size: 22px;
    font-weight: 700;
    color: #475569;
    margin: 14px 0 10px 0;
}

.school-empty-state p {
    font-size: 15px;
    color: #94a3b8;
    margin: 4px 0;
}
</style>

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
                                <span class="school-role-badge" style="background: #fef3c7; color: #92400e;">
                                    <i class="fas fa-crown"></i> مدير النظام
                                </span>
                            @elseif($otherUser->role === 'teacher')
                                <span class="school-role-badge" style="background: #dbeafe; color: #1e40af;">
                                    <i class="fas fa-chalkboard-teacher"></i> معلم
                                </span>
                            @elseif($otherUser->role === 'student')
                                <span class="school-role-badge" style="background: #dcfce7; color: #166534;">
                                    <i class="fas fa-user-graduate"></i> طالب
                                </span>
                            @elseif($otherUser->role === 'parent')
                                <span class="school-role-badge" style="background: #fef3c7; color: #92400e;">
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
            <div class="school-empty-state" style="padding: 50px 20px;">
                <i class="fas fa-comments"></i>
                <h4 style="font-size: 17px; font-weight: 700; color: #475569; margin: 14px 0 8px 0;">لا توجد محادثات حالياً</h4>
                <p style="font-size: 14px;">ابدأ محادثة مع المعلمين أو الطلاب أو أولياء الأمور</p>
            </div>
        @endforelse
    </div>

    <!-- منطقة الدردشة -->
    <div class="school-chat-container" id="chatContainer">
        <div class="school-empty-state" id="emptyState" style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <i class="fas fa-envelope-open"></i>
            <h3>مرحباً بك في الرسائل</h3>
            <p>اختر محادثة من القائمة أو ابدأ محادثة جديدة</p>
            <p style="font-size: 13px; margin-top: 10px; padding: 11px 18px; background: #f0f9ff; border-radius: 8px; color: #1e40af; border: 1px solid #3b82f6;">
                <i class="fas fa-school"></i> يمكنك مراسلة جميع أعضاء مدرستك
            </p>
        </div>
        
        <!-- Chat Interface (مخفي في البداية) -->
        <div id="chatInterface" style="display: none; height: 100%; flex-direction: column;">
            <!-- Chat Header -->
            <div style="padding: 20px 24px; border-bottom: 2px solid #f1f5f9; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div id="chatUserAvatar" style="width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 19px; box-shadow: 0 5px 14px rgba(102, 126, 234, 0.35);"></div>
                    <div style="flex: 1;">
                        <h3 id="chatUserName" style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b;"></h3>
                        <p id="chatUserRole" style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;"></p>
                    </div>
                    <button onclick="closeChat()" style="background: #f8fafc; border: 2px solid #e2e8f0; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #64748b; transition: all 0.2s;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Messages Container -->
            <div id="messagesContainer" class="chat-messages-container" style="flex: 1; overflow-y: auto; padding: 24px; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);">
                <!-- الرسائل ستظهر هنا -->
            </div>
            
            <!-- Message Input -->
            <div style="padding: 20px 24px; border-top: 2px solid #f1f5f9; background: white;">
                <form id="messageForm" onsubmit="sendMessage(event)" style="display: flex; gap: 12px; align-items: flex-end;">
                    <textarea 
                        id="messageInput" 
                        placeholder="اكتب رسالتك هنا... (Ctrl+Enter للإرسال)"
                        style="flex: 1; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 12px; resize: none; font-family: inherit; font-size: 14px; min-height: 48px; max-height: 120px; transition: all 0.2s;"
                        rows="1"
                        onfocus="this.style.borderColor='#667eea'"
                        onblur="this.style.borderColor='#e2e8f0'"
                        oninput="autoResize(this)"
                        onkeydown="handleKeyPress(event)"
                    ></textarea>
                    <button 
                        type="submit" 
                        id="sendBtn"
                        style="padding: 14px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); transition: all 0.3s; height: 48px;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(102, 126, 234, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(102, 126, 234, 0.3)'"
                    >
                        <span>إرسال</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.message {
    margin-bottom: 16px;
    animation: messageSlideIn 0.3s ease;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message.sent {
    display: flex;
    justify-content: flex-start;
}

.message.received {
    display: flex;
    justify-content: flex-end;
}

.message-bubble {
    max-width: 70%;
    padding: 14px 18px;
    border-radius: 16px;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.message.sent .message-bubble {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-left-radius: 4px;
}

.message.received .message-bubble {
    background: white;
    border: 2px solid #e2e8f0;
    color: #1e293b;
    border-bottom-right-radius: 4px;
}

.message-text {
    font-size: 14px;
    line-height: 1.6;
    word-wrap: break-word;
    margin-bottom: 6px;
}

.message-time {
    font-size: 11px;
    opacity: 0.7;
    text-align: left;
    margin-top: 6px;
}

.message.sent .message-time {
    color: rgba(255, 255, 255, 0.8);
}

.message.received .message-time {
    color: #94a3b8;
}

.school-conversation-item.active {
    background: white;
    border-color: #667eea;
    box-shadow: 0 5px 16px rgba(102, 126, 234, 0.25);
}
</style>

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
                <div class="message-text">${escapeHtml(message.message)}</div>
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
</script>

<!-- مودال اختيار مستخدم -->
@include('messages.partials.user-select-modal', ['availableUsers' => $availableUsers, 'role' => 'school-admin'])

@endsection
