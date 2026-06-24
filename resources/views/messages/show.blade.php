@extends(auth()->user()->role === 'super_admin' ? 'layouts.admin' : (auth()->user()->role === 'school_admin' ? 'layouts.school-admin' : (auth()->user()->role === 'teacher' ? 'layouts.teacher' : (auth()->user()->role === 'parent' ? 'layouts.parent' : (auth()->user()->role === 'student' ? 'layouts.student-app' : 'layouts.student-app'))))))

@section('page-title', 'محادثة مع ' . $otherUser->name)

@section('content')
<style>
/* P1-E: دعم Dark Mode للمحادثة */
.chat-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 280px);
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(226, 232, 240, 0.8);
}
html[data-theme="dark"] .chat-container {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    border-color: rgba(255, 255, 255, 0.1);
    color: #e2e8f0;
}
html[data-theme="dark"] .chat-header {
    background: linear-gradient(135deg, rgba(102,126,234,0.15) 0%, rgba(118,75,162,0.15) 100%);
    border-bottom-color: rgba(255,255,255,0.1);
}
html[data-theme="dark"] .message.received .message-bubble {
    background: rgba(30, 41, 59, 0.9);
    color: #e2e8f0;
    border-color: rgba(255, 255, 255, 0.1);
}
html[data-theme="dark"] .user-info h3 { color: #e2e8f0; }
html[data-theme="dark"] .user-info p { color: #94a3b8; }

.chat-header {
    padding: 24px 28px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    border-bottom: 2px solid rgba(241, 245, 249, 0.8);
    display: flex;
    align-items: center;
    gap: 16px;
    backdrop-filter: blur(10px);
}

.user-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 22px;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.35);
    border: 3px solid white;
}

.chat-messages {
    flex: 1;
    padding: 28px;
    overflow-y: auto;
    background: linear-gradient(180deg, #fafbfc 0%, #f1f5f9 100%);
}

.chat-messages::-webkit-scrollbar {
    width: 8px;
}

.chat-messages::-webkit-scrollbar-track {
    background: transparent;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

.message {
    margin-bottom: 20px;
    display: flex;
    gap: 12px;
    animation: messageSlide 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes messageSlide {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.message.sent {
    flex-direction: row-reverse;
}

.message-bubble {
    max-width: 65%;
    padding: 14px 18px;
    border-radius: 18px;
    word-wrap: break-word;
    white-space: pre-wrap;
    position: relative;
    transition: all 0.3s ease;
    line-height: 1.6;
}

.message-bubble:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}

.message.received .message-bubble {
    background: white;
    border: 2px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    border-bottom-right-radius: 4px;
}

.message.sent .message-bubble {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 6px 18px rgba(102, 126, 234, 0.35);
    border-bottom-left-radius: 4px;
}

.message-time {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 6px;
    font-weight: 500;
}

.message.sent .message-time {
    color: rgba(255, 255, 255, 0.8);
}

/* تنسيق الصور داخل الرسائل */
.message-bubble img {
    max-width: 100%;
    max-height: 300px;
    border-radius: 12px;
    cursor: pointer;
    display: block;
    margin: 8px 0;
    object-fit: cover;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.message-bubble img:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.message.sent .message-bubble img {
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.message.received .message-bubble img {
    border: 2px solid #e2e8f0;
}

/* Lightbox لعرض الصورة بالحجم الكامل */
.image-lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(10px);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    cursor: zoom-out;
    animation: lightboxFadeIn 0.25s ease;
}

.image-lightbox.active {
    display: flex;
}

.image-lightbox img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    animation: lightboxZoomIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.lightbox-close {
    position: absolute;
    top: 20px;
    left: 20px;
    width: 44px;
    height: 44px;
    background: rgba(255, 255, 255, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    color: white;
    font-size: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.lightbox-close:hover {
    background: rgba(239, 68, 68, 0.8);
    border-color: transparent;
    transform: scale(1.1);
}

@keyframes lightboxFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes lightboxZoomIn {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}

.chat-input {
    padding: 24px 28px;
    background: white;
    border-top: 2px solid rgba(241, 245, 249, 0.8);
    display: flex;
    gap: 14px;
    align-items: flex-end;
}

.chat-input textarea {
    flex: 1;
    padding: 14px 18px;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    resize: none;
    font-family: inherit;
    font-size: 14px;
    max-height: 120px;
    min-height: 52px;
    transition: all 0.3s ease;
    background: #f8fafc;
    line-height: 1.5;
}

.chat-input textarea:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15);
}

.send-btn {
    padding: 14px 28px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 14px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
    height: 52px;
}

.send-btn:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(102, 126, 234, 0.45);
}

.send-btn:active:not(:disabled) {
    transform: translateY(-1px);
}

.send-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.toolbar-btn {
    width: 34px;
    height: 34px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 13px;
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

.back-btn {
    padding: 12px 24px;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    color: #334155;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.back-btn:hover {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    transform: translateX(-5px);
    box-shadow: 0 4px 12px rgba(51, 65, 85, 0.1);
}

.empty-messages {
    text-align: center;
    padding: 80px 20px;
    color: #64748b;
}

.empty-messages i {
    font-size: 64px;
    color: #cbd5e1;
    margin-bottom: 20px;
}

.empty-messages h3 {
    font-size: 24px;
    font-weight: 700;
    color: #475569;
    margin: 16px 0 8px 0;
}

.empty-messages p {
    font-size: 15px;
    color: #94a3b8;
}

.user-info h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
}

.user-info p {
    margin: 4px 0 0 0;
    font-size: 13px;
    color: #64748b;
}
/* Link Modal */
.link-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(8px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    animation: linkFadeIn 0.25s ease;
}

@keyframes linkFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.link-modal-box {
    background: white;
    border-radius: 20px;
    padding: 32px;
    width: 90%;
    max-width: 480px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
    animation: linkSlideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes linkSlideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.link-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f1f5f9;
}

.link-modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

.link-modal-close {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    color: #64748b;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.link-modal-close:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
    transform: rotate(90deg);
}

.link-modal-field {
    margin-bottom: 20px;
}

.link-modal-field label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
}

.link-modal-field input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.3s ease;
    background: #f8fafc;
    direction: ltr;
    text-align: left;
}

.link-modal-field input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15);
}

.link-modal-field input.error {
    border-color: #ef4444;
    background: #fef2f2;
}

.link-modal-field .field-hint {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 6px;
}

.link-modal-field .field-error {
    font-size: 12px;
    color: #ef4444;
    margin-top: 6px;
    display: none;
}

.link-modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.link-modal-actions .btn-insert {
    flex: 1;
    padding: 14px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
    transition: all 0.3s ease;
}

.link-modal-actions .btn-insert:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(102, 126, 234, 0.45);
}

.link-modal-actions .btn-cancel {
    padding: 14px 24px;
    background: #f1f5f9;
    color: #475569;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s;
}

.link-modal-actions .btn-cancel:hover {
    background: #e2e8f0;
}
</style>

<!-- Container with padding for status bar and bottom nav -->
<div style="padding-top: 100px; padding-bottom: 120px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">

<div style="margin-bottom: 24px;">
    <button class="back-btn" onclick="window.location.href='{{ auth()->user()->role === 'school_admin' ? route('school-admin.messages.index') : route('messages.index') }}'">
        <span style="font-size: 18px;">→</span>
        <span>العودة للرسائل</span>
    </button>
</div>

<div class="chat-container">
    <!-- رأس المحادثة -->
    <div class="chat-header">
        <div class="user-avatar">
            @if($otherUser->avatar)
                <img src="{{ $otherUser->avatar_url }}" alt="{{ $otherUser->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            @else
                {{ mb_substr($otherUser->name, 0, 1) }}
            @endif
        </div>
        <div class="user-info">
            <h3>{{ $otherUser->name }}</h3>
            <p><span style="margin-left: 4px;">✉️</span> {{ $otherUser->email }}</p>
        </div>
    </div>

    <!-- الرسائل -->
    <div class="chat-messages" id="messagesContainer">
        @forelse($messages as $message)
            <div class="message {{ $message->sender_id == auth()->id() ? 'sent' : 'received' }}">
                <div class="message-bubble">
                    {!! safe_html($message->message) !!}
                    <div class="message-time">
                        <span style="margin-left: 4px;">🕒</span>
                        {{ $message->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-messages">
                <i class="fas fa-comments"></i>
                <h3>💬 لا توجد رسائل بعد</h3>
                <p>ابدأ المحادثة بإرسال رسالة!</p>
            </div>
        @endforelse
    </div>

    <!-- صندوق الإرسال مع محرر نصوص -->
    <div class="chat-input" style="flex-direction: column; gap: 8px;">
        <!-- شريط الأدوات -->
        <div class="editor-toolbar" style="display: flex; align-items: center; gap: 4px; padding: 8px 12px; background: #f8fafc; border-radius: 12px; border: 2px solid #e2e8f0; flex-wrap: wrap;">
            <button type="button" class="toolbar-btn" onclick="execCmd('bold')" title="غامق" style="font-weight:900;">B</button>
            <button type="button" class="toolbar-btn" onclick="execCmd('italic')" title="مائل" style="font-style:italic;font-weight:700;">I</button>
            <button type="button" class="toolbar-btn" onclick="execCmd('underline')" title="تسطير" style="text-decoration:underline;font-weight:700;">U</button>
            <div style="width: 1px; height: 24px; background: #e2e8f0; margin: 0 4px;"></div>
            <button type="button" class="toolbar-btn" onclick="execCmd('justifyRight')" title="محاذاة يمين">⇥</button>
            <button type="button" class="toolbar-btn" onclick="execCmd('justifyCenter')" title="محاذاة وسط">≡</button>
            <button type="button" class="toolbar-btn" onclick="execCmd('justifyLeft')" title="محاذاة يسار">⇤</button>
            <div style="width: 1px; height: 24px; background: #e2e8f0; margin: 0 4px;"></div>
            <button type="button" class="toolbar-btn" onclick="insertLink()" title="إدراج رابط">🔗</button>
            <button type="button" class="toolbar-btn" onclick="document.getElementById('chatImageUpload').click()" title="إدراج صورة">🖼️</button>
            <input type="file" id="chatImageUpload" accept="image/*" style="display: none;" onchange="insertImage(this)">
        </div>
        <!-- محرر النص -->
        <div style="display: flex; gap: 14px; align-items: flex-end; width: 100%;">
            <div 
                id="messageInput" 
                contenteditable="true" 
                class="rich-editor"
                style="flex: 1; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 14px; min-height: 52px; max-height: 200px; overflow-y: auto; font-family: inherit; font-size: 14px; line-height: 1.6; background: #f8fafc; outline: none; transition: all 0.3s ease; direction: rtl;"
                data-placeholder="اكتب رسالتك هنا... (Ctrl+Enter للإرسال)"
                onkeydown="handleKeyPress(event)"
                onfocus="this.style.borderColor='#667eea'; this.style.background='white'; this.style.boxShadow='0 4px 16px rgba(102,126,234,0.15)'"
                onblur="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc'; this.style.boxShadow='none'"
            ></div>
            <button class="send-btn" onclick="sendMessage()" id="sendBtn">
                <span>إرسال</span>
                <span style="font-size: 16px;">✈️</span>
            </button>
        </div>
    </div>
</div>

<!-- مودال إدراج رابط -->
<div class="link-modal-overlay" id="linkModal" onclick="if(event.target===this)closeLinkModal()">
    <div class="link-modal-box">
        <div class="link-modal-header">
            <h3><i class="fas fa-link" style="color: #667eea;"></i> إدراج رابط</h3>
            <button class="link-modal-close" onclick="closeLinkModal()">×</button>
        </div>
        <div class="link-modal-field">
            <label><i class="fas fa-globe" style="color: #667eea; margin-left: 6px;"></i> عنوان الرابط (URL)</label>
            <input type="url" id="linkUrlInput" placeholder="https://example.com" dir="ltr" onkeydown="if(event.key==='Enter'){event.preventDefault();confirmInsertLink()}">
            <div class="field-hint">أدخل الرابط الكامل بما في ذلك https://</div>
            <div class="field-error" id="linkUrlError"><i class="fas fa-exclamation-circle"></i> الرجاء إدخال رابط صحيح</div>
        </div>
        <div class="link-modal-field">
            <label><i class="fas fa-font" style="color: #764ba2; margin-left: 6px;"></i> نص العرض (اختياري)</label>
            <input type="text" id="linkTextInput" placeholder="اضغط هنا" dir="rtl" onkeydown="if(event.key==='Enter'){event.preventDefault();confirmInsertLink()}">
            <div class="field-hint">النص الذي سيظهر في الرسالة بدل الرابط</div>
        </div>
        <div class="link-modal-actions">
            <button class="btn-insert" onclick="confirmInsertLink()">
                <i class="fas fa-check-circle"></i> إدراج الرابط
            </button>
            <button class="btn-cancel" onclick="closeLinkModal()">إلغاء</button>
        </div>
    </div>
</div>

<script>
const messagesContainer = document.getElementById('messagesContainer');
const messageInput = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendBtn');

// التمرير لأسفل عند تحميل الصفحة
scrollToBottom();

function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function handleKeyPress(event) {
    // Ctrl/Cmd + Enter للإرسال
    if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
        sendMessage();
        event.preventDefault();
    }
}

// دوال محرر النصوص
function execCmd(command) {
    document.execCommand(command, false, null);
    messageInput.focus();
}

function insertLink() {
    // حفظ موضع المؤشر الحالي في المحرر
    const sel = window.getSelection();
    if (sel.rangeCount > 0) {
        window._savedRange = sel.getRangeAt(0).cloneRange();
    }
    
    // مسح الحقول
    document.getElementById('linkUrlInput').value = '';
    document.getElementById('linkTextInput').value = '';
    document.getElementById('linkUrlInput').classList.remove('error');
    document.getElementById('linkUrlError').style.display = 'none';
    
    // فتح المودال
    document.getElementById('linkModal').style.display = 'flex';
    
    // التركيز على حقل الرابط
    setTimeout(() => document.getElementById('linkUrlInput').focus(), 200);
}

function confirmInsertLink() {
    const urlInput = document.getElementById('linkUrlInput');
    const textInput = document.getElementById('linkTextInput');
    const errorEl = document.getElementById('linkUrlError');
    
    let url = urlInput.value.trim();
    let text = textInput.value.trim();
    
    // التحقق
    if (!url) {
        urlInput.classList.add('error');
        errorEl.style.display = 'block';
        urlInput.focus();
        return;
    }
    
    // إضافة https:// لو ما موجود
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        url = 'https://' + url;
    }
    
    // إقفال المودال
    closeLinkModal();
    
    // التركيز على المحرر واستعادة المؤشر
    messageInput.focus();
    if (window._savedRange) {
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(window._savedRange);
    }
    
    // إدراج الرابط
    if (text) {
        // إدراج رابط مع نص مخصص
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
            // وضع المؤشر بعد الرابط
            range.setStartAfter(link);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
        }
    } else {
        // إدراج الرابط مباشرة
        document.execCommand('createLink', false, url);
        // تنسيق الروابط
        messageInput.querySelectorAll('a').forEach(a => {
            a.target = '_blank';
            a.style.color = '#667eea';
            a.style.textDecoration = 'underline';
        });
    }
}

function closeLinkModal() {
    document.getElementById('linkModal').style.display = 'none';
    document.getElementById('linkUrlInput').classList.remove('error');
    document.getElementById('linkUrlError').style.display = 'none';
    messageInput.focus();
}

// إغلاق مودال الرابط بالـ Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('linkModal').style.display === 'flex') {
        closeLinkModal();
    }
});

function insertImage(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    
    // رفع الصورة للسيرفر
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'image');
    formData.append('_token', '{{ csrf_token() }}');

    // مؤشر تحميل أثناء الرفع — تغذية راجعة على الاتصال البطيء/الصور الكبيرة
    const uploadingBadge = document.createElement('div');
    uploadingBadge.textContent = '⏳ جاري رفع الصورة...';
    uploadingBadge.style.cssText = 'position:fixed;bottom:90px;left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;padding:8px 18px;border-radius:20px;z-index:9999;font-size:13px;box-shadow:0 4px 14px rgba(0,0,0,.3);';
    document.body.appendChild(uploadingBadge);

    fetch('{{ route("messages.upload") }}', {
        method: 'POST',
        body: formData
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            document.execCommand('insertImage', false, data.url);
        } else {
            alert(data.message || 'فشل رفع الصورة');
        }
    })
    .catch(err => alert('تعذّر رفع الصورة: ' + err.message))
    .finally(() => uploadingBadge.remove());

    input.value = '';
}

function sendMessage() {
    const message = messageInput.innerHTML.trim();
    
    if (!message) {
        messageInput.focus();
        return;
    }
    
    // تعطيل الزر
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span>جاري الإرسال...</span><i class="fas fa-spinner fa-spin"></i>';
    
    // إرسال الرسالة
    fetch('{{ auth()->user()->role === 'school_admin' ? route('school-admin.messages.send') : route('messages.send') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            receiver_id: {{ $otherUser->id }},
            message: message
        })
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        
        // التحقق من نوع المحتوى
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('الاستجابة ليست JSON - قد تكون هناك مشكلة في المصادقة');
        }
        
        const data = await response.json();
        
        // التحقق من حالة الاستجابة
        if (!response.ok) {
            // خطأ في التحقق من صحة البيانات
            if (response.status === 422 && data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                throw new Error(errorMessages);
            }
            // خطأ آخر
            throw new Error(data.error || data.message || 'حدث خطأ أثناء إرسال الرسالة');
        }
        
        return data;
    })
    .then(data => {
        if (data.success) {
            // إضافة الرسالة للواجهة
            const messageHtml = `
                <div class="message sent">
                    <div class="message-bubble">
                        ${message}
                        <div class="message-time">
                            <i class="far fa-clock" style="margin-left: 4px;"></i>
                            الآن
                        </div>
                    </div>
                </div>
            `;
            messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
            
            // مسح حقل الإدخال
            messageInput.innerHTML = '';
            
            // التمرير لأسفل
            scrollToBottom();
            
            // إزالة رسالة "لا توجد رسائل"
            const emptyMsg = messagesContainer.querySelector('.empty-messages');
            if (emptyMsg) {
                emptyMsg.remove();
            }
        } else {
            showError(data.error || 'حدث خطأ أثناء إرسال الرسالة');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError(error.message || 'حدث خطأ أثناء إرسال الرسالة');
    })
    .finally(() => {
        // إعادة تفعيل الزر
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<span>إرسال</span><i class="fas fa-paper-plane"></i>';
        messageInput.focus();
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    // إنشاء toast notification للخطأ
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(239, 68, 68, 0.35);
        z-index: 9999;
        font-weight: 600;
        animation: slideDown 0.3s ease;
    `;
    // حماية XSS: استخدام DOM API بدلًا من innerHTML للنص user-controlled
    const errIcon = document.createElement('i');
    errIcon.className = 'fas fa-exclamation-circle';
    errIcon.style.marginLeft = '8px';
    const errText = document.createElement('span');
    errText.textContent = ' ' + String(message ?? '');
    toast.appendChild(errIcon);
    toast.appendChild(errText);
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideUp 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// إضافة animations للـ toast
const toastStyle = document.createElement('style');
toastStyle.textContent = `
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
    
    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        to {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
    }
`;
document.head.appendChild(toastStyle);

// التركيز على حقل الإدخال
messageInput.focus();

// === Lightbox للصور ===
// إضافة click listener لكل صورة في الرسائل
document.addEventListener('click', function(e) {
    if (e.target.tagName === 'IMG' && e.target.closest('.message-bubble')) {
        e.preventDefault();
        const lightbox = document.getElementById('imageLightbox');
        const lightboxImg = document.getElementById('lightboxImage');
        lightboxImg.src = e.target.src;
        lightbox.classList.add('active');
    }
});

function closeLightbox() {
    const lightbox = document.getElementById('imageLightbox');
    lightbox.classList.remove('active');
    document.getElementById('lightboxImage').src = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});
</script>

<!-- Lightbox عرض الصور بالحجم الكامل -->
<div id="imageLightbox" class="image-lightbox" onclick="if(event.target === this) closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()">×</button>
    <img id="lightboxImage" src="" alt="صورة">
</div>

</div> <!-- End container -->

@endsection
