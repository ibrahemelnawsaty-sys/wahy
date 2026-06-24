@extends(auth()->user()->role === 'super_admin' ? 'layouts.admin' : (auth()->user()->role === 'school_admin' ? 'layouts.school-admin' : (auth()->user()->role === 'teacher' ? 'layouts.teacher' : (auth()->user()->role === 'parent' ? 'layouts.parent' : (auth()->user()->role === 'student' ? 'layouts.student-app' : 'layouts.student-app')))))

@section('page-title', 'الرسائل')

@section('content')
<!-- Container with padding for status bar and bottom nav -->
<div style="padding-top: 100px; padding-bottom: 120px; padding-left: 20px; padding-right: 20px; max-width: 1400px; margin: 0 auto;">
<!-- Page Header -->
<div style="background: white; border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
            💬
        </div>
        <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;">الرسائل</h1>
    </div>
    <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #64748b;">
        <a href="{{ auth()->user()->role === 'school_admin' ? route('school-admin.dashboard') : route('dashboard') }}" style="color: #667eea; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.color='#764ba2'" onmouseout="this.style.color='#667eea'">
            <i class="fas fa-home"></i> الرئيسية
        </a>
        <span style="color: #cbd5e1;">›</span>
        <span style="color: #1e293b; font-weight: 600;">الرسائل</span>
    </div>
</div>

<style>
.messages-container {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 20px;
    height: calc(100vh - 280px);
    padding-bottom: 100px; /* مسافة للشريط السفلي */
}

@media (max-width: 768px) {
    .messages-container {
        grid-template-columns: 1fr;
        height: auto;
        min-height: calc(100vh - 200px);
    }
}

.conversations-list {
    background: white;
    border-radius: 16px;
    padding: 24px;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
}

.conversations-list h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #f1f5f9;
}

.conversation-item {
    padding: 14px;
    border-radius: 12px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
}

.conversation-item:hover {
    background: white;
    border-color: #667eea;
    transform: translateX(-3px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.conversation-item.active {
    background: white;
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
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
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.user-info {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.last-message {
    font-size: 13px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-badge {
    background: #ef4444;
    color: white;
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 12px;
    font-weight: 600;
}

.chat-container {
    background: white;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-header {
    padding: 20px;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f8fafc;
}

.message {
    margin-bottom: 16px;
    display: flex;
    gap: 10px;
}

.message.sent {
    flex-direction: row-reverse;
}

.message-bubble {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 12px;
    word-wrap: break-word;
}

.message.received .message-bubble {
    background: white;
    border: 2px solid #e2e8f0;
}

.message.sent .message-bubble {
    background: var(--color-primary);
    color: white;
}

.message-time {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 4px;
}

.chat-input {
    padding: 20px;
    border-top: 2px solid #f1f5f9;
    display: flex;
    gap: 12px;
}

.chat-input textarea {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    resize: none;
    font-family: inherit;
}

.send-btn {
    padding: 12px 24px;
    background: var(--color-primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.send-btn:hover {
    opacity: 0.9;
}

.new-conversation-btn {
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
}

.new-conversation-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #64748b;
}

.empty-state i {
    font-size: 48px;
    color: #cbd5e1;
    margin-bottom: 16px;
}

.empty-state h4 {
    font-size: 18px;
    font-weight: 600;
    color: #475569;
    margin: 12px 0 8px 0;
}

.empty-state p {
    font-size: 14px;
    color: #94a3b8;
    margin: 4px 0;
}

.user-select-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 28px;
    max-width: 500px;
    width: 90%;
    max-height: 75vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { 
        opacity: 0;
        transform: translateY(20px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

.user-list-item {
    padding: 14px;
    border-radius: 10px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-list-item:hover {
    background: white;
    border-color: #667eea;
    transform: translateX(-4px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.user-list-item .user-avatar {
    flex-shrink: 0;
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
            <div class="empty-state">
                <div style="font-size: 56px; opacity: 0.5;">💬</div>
                <h4>لا توجد محادثات حالياً</h4>
                <p>ابدأ محادثة جديدة من خلال الزر أعلاه</p>
            </div>
        @endforelse
    </div>

    <!-- منطقة الدردشة -->
    <div class="chat-container">
        <div class="empty-state" style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div style="font-size: 72px; color: #cbd5e1; margin-bottom: 24px;">📬</div>
            <h3 style="font-size: 22px; font-weight: 700; color: #475569; margin: 0 0 12px 0;">مرحباً بك في الرسائل</h3>
            <p style="font-size: 15px; color: #94a3b8; margin: 0;">اختر محادثة من القائمة أو ابدأ محادثة جديدة</p>
        </div>
    </div>
</div>

<!-- مودال اختيار مستخدم -->
<div class="user-select-modal" id="userSelectModal">
    <div class="modal-content" style="max-width: 650px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #f1f5f9;">
            <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px;">
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
            <h4 style="margin: 0 0 14px 0; color: #475569; font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
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
                @endforeach
            </div>
        </div>
        
        <button style="width: 100%; padding: 12px; margin-top: 20px; border: 2px solid #e2e8f0; background: white; border-radius: 10px; cursor: pointer; font-weight: 600; color: #64748b; transition: all 0.2s;" 
                onclick="hideUserSelect()"
                onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1';"
                onmouseout="this.style.background='white'; this.style.borderColor='#e2e8f0';">
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
