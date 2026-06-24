@extends('layouts.teacher')

@section('title', 'المراسلات')

@push('styles')
<style>
/* ===== Messages Page ===== */
.msg-wrap { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
.msg-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.msg-header h2 { font-size: 24px; font-weight: 700; }
.btn-new-msg {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white; padding: 12px 25px; border-radius: 12px;
    border: none; cursor: pointer; font-weight: 600; font-size: 14px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn-new-msg:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }
.conv-list { display: grid; gap: 15px; }
.conv-item {
    border: 2px solid #e2e8f0; border-radius: 12px; padding: 20px;
    cursor: pointer; transition: all 0.3s; display: flex; justify-content: space-between; align-items: start;
}
.conv-item:hover { border-color: #667eea; background: #f8f7ff; }
.conv-snippet {
    color: #4a5568; font-size: 14px; margin-top: 8px;
    /* نعرض نص بدون تنسيق HTML هنا */
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 500px;
}
.badge-new { background: #f56565; color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }

/* ===== Modal ===== */
.modal-overlay {
    display: none; position: fixed; top: 0; left: 0;
    width: 100%; height: 100%; background: rgba(0,0,0,0.6);
    z-index: 1000; justify-content: center; align-items: center;
    padding: 16px;
}
.modal-box {
    background: white; border-radius: 20px; width: 100%;
    max-width: 720px; max-height: 90vh; display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.modal-head {
    padding: 20px 24px; border-bottom: 2px solid #e2e8f0;
    display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;
}
.modal-head h3 { font-size: 20px; font-weight: 700; color: #2d3748; }
.btn-close { background: #f56565; color: white; border: none; border-radius: 8px; padding: 8px 15px; cursor: pointer; font-weight: 700; font-size: 16px; }

/* Messages container */
#messagesContainer {
    flex: 1; overflow-y: auto; padding: 20px;
    display: flex; flex-direction: column; gap: 12px;
    min-height: 250px;
}
.msg-bubble {
    max-width: 72%; padding: 12px 18px; border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.msg-bubble.me {
    align-self: flex-end;
    background: linear-gradient(135deg, #667eea, #764ba2); color: white;
    border-bottom-right-radius: 4px;
}
.msg-bubble.other {
    align-self: flex-start; background: #f7fafc; color: #2d3748;
    border-bottom-left-radius: 4px; border: 1px solid #e2e8f0;
}
.msg-bubble .msg-time { font-size: 11px; opacity: 0.65; margin-top: 6px; }

/* Rich content inside bubbles */
.msg-bubble .msg-body img { max-width: 100%; border-radius: 8px; margin: 4px 0; height: auto; }
.msg-bubble .msg-body a { text-decoration: underline; }
.msg-bubble.me .msg-body a { color: #c7d2fe; }
.msg-bubble.other .msg-body a { color: #3b82f6; }

/* ===== Compose area ===== */
.compose-area { padding: 16px 20px; border-top: 2px solid #e2e8f0; flex-shrink: 0; }

/* Mini RTE toolbar */
.rte-toolbar {
    display: flex; flex-wrap: wrap; gap: 4px;
    padding: 6px 10px; background: #f8fafc;
    border: 2px solid #e2e8f0; border-bottom: none;
    border-radius: 10px 10px 0 0; align-items: center;
}
.rte-btn-mini {
    padding: 3px 8px; border: 1px solid #cbd5e1; border-radius: 4px;
    background: white; cursor: pointer; font-size: 12px; color: #334155;
    transition: background 0.15s;
}
.rte-btn-mini:hover { background: #e2e8f0; }
.rte-sep { width: 1px; height: 20px; background: #cbd5e1; margin: 0 3px; }

.rte-editor-msg {
    min-height: 90px; max-height: 200px; overflow-y: auto;
    padding: 12px; border: 2px solid #e2e8f0;
    border-radius: 0 0 0 0;
    font-family: 'Cairo', sans-serif; font-size: 14px; line-height: 1.7;
    outline: none; direction: rtl;
    background: white;
}
.rte-editor-msg:empty::before {
    content: 'اكتب رسالتك هنا...';
    color: #a0aec0; pointer-events: none;
}

.compose-bottom {
    display: flex; justify-content: flex-end; margin-top: 8px;
}
.btn-send {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white; border: none; border-radius: 10px;
    padding: 10px 28px; cursor: pointer; font-weight: 700;
    font-size: 14px; transition: transform 0.2s;
}
.btn-send:hover { transform: translateY(-2px); }
.btn-send:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

/* new message form */
.form-modal-box {
    background: white; border-radius: 20px; width: 100%; max-width: 600px;
    padding: 32px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    max-height: 90vh; overflow-y: auto;
}
.form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #4a5568; }
.form-select-field, .form-field {
    width: 100%; border: 2px solid #e2e8f0; border-radius: 12px;
    padding: 12px; font-family: 'Cairo', sans-serif; font-size: 14px;
    background: white; transition: border 0.2s;
}
.form-select-field:focus, .form-field:focus { border-color: #667eea; outline: none; }
.new-msg-editor {
    min-height: 120px; max-height: 280px; overflow-y: auto;
    padding: 12px; border: 2px solid #e2e8f0;
    border-radius: 0 0 10px 10px;
    font-family: 'Cairo', sans-serif; font-size: 14px; line-height: 1.7;
    outline: none; direction: rtl; background: white;
}
.new-msg-editor:empty::before {
    content: 'اكتب رسالتك...';
    color: #a0aec0; pointer-events: none;
}
.form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 6px; }
.btn-cancel { background: #edf2f7; color: #2d3748; border: none; border-radius: 10px; padding: 10px 24px; cursor: pointer; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="msg-wrap">
    <div class="msg-header">
        <h2>💬 المراسلات مع أولياء الأمور</h2>
        <button class="btn-new-msg" onclick="showNewMessageModal()">✉️ رسالة جديدة</button>
    </div>

    {{-- قائمة المحادثات --}}
    <div class="conv-list">
        @forelse($conversations as $conversation)
        @if(!$conversation->parent) @continue @endif
        <div class="conv-item" onclick="openConversation({{ $conversation->parent_id }}, {{ $conversation->student_id ?? 'null' }})">
            <div style="flex:1; min-width:0;">
                <h3 style="font-size:18px; font-weight:600; color:#2d3748; margin-bottom:5px;">
                    👤 {{ $conversation->parent->name ?? 'غير معروف' }}
                </h3>
                @if($conversation->student)
                <div style="color:#718096; font-size:13px; margin-bottom:6px;">
                    بخصوص: {{ $conversation->student->name }}
                </div>
                @endif
                <div class="conv-snippet">
                    {{ html_excerpt($conversation->message, 90) }}
                </div>
            </div>
            <div style="text-align:left; flex-shrink:0; margin-right:16px;">
                <div style="color:#718096; font-size:12px; margin-bottom:5px;">
                    {{ $conversation->created_at->diffForHumans() }}
                </div>
                @if($conversation->sender_type === 'parent' && !$conversation->is_read)
                <span class="badge-new">جديد</span>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align:center; padding:60px 20px; color:#718096;">
            <div style="font-size:60px; margin-bottom:15px;">📭</div>
            <p style="font-size:16px;">لا توجد رسائل بعد</p>
        </div>
        @endforelse
    </div>

    {{ $conversations->links() }}
</div>

{{-- نافذة المحادثة --}}
<div id="conversationModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="conversationTitle">💬 محادثة</h3>
            <button class="btn-close" onclick="closeConversation()">✕</button>
        </div>

        <div id="messagesContainer">
            {{-- الرسائل تُضاف هنا --}}
        </div>

        {{-- منطقة الكتابة --}}
        <div class="compose-area">
            {{-- شريط أدوات بسيط --}}
            <div class="rte-toolbar">
                <button type="button" class="rte-btn-mini" onclick="msgExec('bold')" title="غامق"><b>B</b></button>
                <button type="button" class="rte-btn-mini" onclick="msgExec('italic')" title="مائل"><i>I</i></button>
                <button type="button" class="rte-btn-mini" onclick="msgExec('underline')" title="تسطير"><u>U</u></button>
                <span class="rte-sep"></span>
                <input type="color" value="#000000" title="لون النص"
                       onchange="msgExec('foreColor', this.value)"
                       style="width:24px;height:24px;border:1px solid #cbd5e1;border-radius:4px;cursor:pointer;padding:0;">
                <span class="rte-sep"></span>
                <button type="button" class="rte-btn-mini" onclick="msgInsertImg()" title="إدراج صورة">🖼️</button>
                <button type="button" class="rte-btn-mini" onclick="msgExec('insertUnorderedList')" title="قائمة">•≡</button>
            </div>
            <div id="msgEditor" class="rte-editor-msg" contenteditable="true" dir="rtl"></div>
            <div class="compose-bottom">
                <button class="btn-send" id="btnSend" onclick="sendMessage()">
                    ↑ إرسال
                </button>
            </div>
        </div>
    </div>
</div>

{{-- نافذة رسالة جديدة --}}
<div id="newMessageModal" class="modal-overlay">
    <div class="form-modal-box">
        <h3 style="font-size:22px; font-weight:700; margin-bottom:20px;">✉️ رسالة جديدة</h3>

        <form id="newMessageForm" style="display:flex; flex-direction:column; gap:16px;">
            <div>
                <label class="form-label">ولي الأمر</label>
                <select id="newParentId" required class="form-select-field" onchange="populateNewStudents(this)">
                    <option value="">اختر ولي الأمر...</option>
                    @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" data-children='@json($parent->children->map(fn($c) => ["id" => $c->id, "name" => $c->name])->values())'>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">الطالب (اختياري)</label>
                <select id="newStudentId" class="form-select-field">
                    <option value="">عام - غير محدد</option>
                </select>
            </div>

            <div>
                <label class="form-label">الرسالة</label>
                {{-- شريط أدوات للرسالة الجديدة --}}
                <div class="rte-toolbar">
                    <button type="button" class="rte-btn-mini" onclick="newMsgExec('bold')"><b>B</b></button>
                    <button type="button" class="rte-btn-mini" onclick="newMsgExec('italic')"><i>I</i></button>
                    <button type="button" class="rte-btn-mini" onclick="newMsgExec('underline')"><u>U</u></button>
                    <span class="rte-sep"></span>
                    <input type="color" value="#000000" title="لون النص"
                           onchange="newMsgExec('foreColor', this.value)"
                           style="width:24px;height:24px;border:1px solid #cbd5e1;border-radius:4px;cursor:pointer;padding:0;">
                    <span class="rte-sep"></span>
                    <button type="button" class="rte-btn-mini" onclick="newMsgInsertImg()">🖼️</button>
                </div>
                <div id="newMsgEditor" class="new-msg-editor" contenteditable="true" dir="rtl" required></div>
            </div>

            <div class="form-actions">
                <button type="button" onclick="closeNewMessageModal()" class="btn-cancel">إلغاء</button>
                <button type="submit" class="btn-send">✉️ إرسال</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentParentId = null;
let currentStudentId = null;

// ===== تعبئة قائمة الطالب حسب ولي الأمر المختار =====
function populateNewStudents(parentSelect) {
    const studentSel = document.getElementById('newStudentId');
    studentSel.innerHTML = '<option value="">عام - غير محدد</option>';
    const opt = parentSelect.options[parentSelect.selectedIndex];
    let children = [];
    try { children = JSON.parse(opt.getAttribute('data-children') || '[]'); } catch (e) {}
    children.forEach(c => {
        const o = document.createElement('option');
        o.value = c.id;
        o.textContent = c.name;
        studentSel.appendChild(o);
    });
}

// ===== Open/Close =====
function showNewMessageModal() {
    document.getElementById('newMessageModal').style.display = 'flex';
    document.getElementById('newMsgEditor').innerHTML = '';
}

function closeNewMessageModal() {
    document.getElementById('newMessageModal').style.display = 'none';
    document.getElementById('newMessageForm').reset();
    document.getElementById('newMsgEditor').innerHTML = '';
}

function openConversation(parentId, studentId = null) {
    currentParentId = parentId;
    currentStudentId = studentId;
    document.getElementById('conversationModal').style.display = 'flex';
    document.getElementById('msgEditor').innerHTML = '';
    loadMessages();
}

function closeConversation() {
    document.getElementById('conversationModal').style.display = 'none';
    currentParentId = null;
    currentStudentId = null;
}

// ===== Load Messages =====
function loadMessages() {
    const url = `{{ route('teacher.messages.conversation') }}?parent_id=${currentParentId}` +
                (currentStudentId ? `&student_id=${currentStudentId}` : '');
    fetch(url)
        .then(r => r.json())
        .then(messages => {
            const c = document.getElementById('messagesContainer');
            c.innerHTML = '';
            if (!messages.length) {
                c.innerHTML = '<div style="text-align:center;color:#a0aec0;padding:40px 20px;">ابدأ المحادثة...</div>';
                return;
            }
            messages.forEach(msg => {
                const isMe = msg.sender_type === 'teacher';
                const div = document.createElement('div');
                div.className = 'msg-bubble ' + (isMe ? 'me' : 'other');
                // أمان (XSS مخزّن): نعرض النص فقط عبر textContent — لا ننفّذ HTML المُرسَل من المستخدم.
                // DOMParser يستخرج النص من أي HTML قديم بأمان دون تنفيذ سكربت/تحميل موارد.
                const body = document.createElement('div');
                body.className = 'msg-body';
                body.textContent = new DOMParser().parseFromString(msg.message || '', 'text/html').body.textContent || '';
                const time = document.createElement('div');
                time.className = 'msg-time';
                time.textContent = new Date(msg.created_at).toLocaleString('ar-EG');
                div.appendChild(body);
                div.appendChild(time);
                c.appendChild(div);
            });
            c.scrollTop = c.scrollHeight;
        })
        .catch(err => console.error('Load messages error:', err));
}

// ===== Send Message =====
function sendMessage() {
    const editor = document.getElementById('msgEditor');
    const html = editor.innerHTML.trim();
    if (!html || html === '<br>') return;

    const btn = document.getElementById('btnSend');
    btn.disabled = true;

    fetch('{{ route('teacher.messages.send') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            parent_id: currentParentId,
            student_id: currentStudentId,
            message: html
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            editor.innerHTML = '';
            loadMessages();
        } else {
            alert('خطأ: ' + (data.error || 'حاول مرة أخرى'));
        }
        btn.disabled = false;
    })
    .catch(err => {
        console.error('Send error:', err);
        btn.disabled = false;
    });
}

// ===== New Message Submit =====
document.getElementById('newMessageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const parentId = document.getElementById('newParentId').value;
    const studentId = document.getElementById('newStudentId').value || null;
    const html = document.getElementById('newMsgEditor').innerHTML.trim();
    if (!html || html === '<br>') { alert('الرجاء كتابة رسالتك'); return; }

    fetch('{{ route('teacher.messages.send') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ parent_id: parentId, student_id: studentId, message: html })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeNewMessageModal();
            location.reload();
        } else {
            alert('حدث خطأ: ' + (data.error || 'حاول مرة أخرى'));
        }
    })
    .catch(err => { console.error(err); alert('خطأ في الاتصال'); });
});

// ===== RTE helpers =====
function msgExec(cmd, val) {
    document.getElementById('msgEditor').focus();
    document.execCommand(cmd, false, val || null);
}
function newMsgExec(cmd, val) {
    document.getElementById('newMsgEditor').focus();
    document.execCommand(cmd, false, val || null);
}
function msgInsertImg() {
    const url = prompt('أدخل رابط الصورة:');
    if (url) {
        document.getElementById('msgEditor').focus();
        document.execCommand('insertHTML', false, `<img src="${url}" style="max-width:100%;border-radius:8px;margin:4px 0;height:auto;">`);
    }
}
function newMsgInsertImg() {
    const url = prompt('أدخل رابط الصورة:');
    if (url) {
        document.getElementById('newMsgEditor').focus();
        document.execCommand('insertHTML', false, `<img src="${url}" style="max-width:100%;border-radius:8px;margin:4px 0;height:auto;">`);
    }
}

// Enter لإرسال (Shift+Enter لسطر جديد)
document.getElementById('msgEditor').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});
</script>
@endpush

@endsection
