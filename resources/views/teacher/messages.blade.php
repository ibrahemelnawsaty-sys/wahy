@extends('layouts.teacher')

@section('title', 'المراسلات')

@push('styles')
<style>
/* ===== Wahy — صفحة المراسلات (معلم ⇄ ولي الأمر) — طبقة بصرية فاخرة =====
   كل الأسطح مبنيّة على متغيّرات النظام الموحّد (--w-*) المعرّفة للوضعين (light/dark)
   في partials/theme-toggle، فتعمل التغطية اللونية تلقائياً في الوضعين. */
:root,
.msg-wrap,
#conversationModal,
#newMessageModal {
    --msg-grad: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --msg-grad-soft: linear-gradient(135deg, rgba(102,126,234,0.12), rgba(118,75,162,0.12));
}

/* ===== إطار الصفحة ===== */
.msg-wrap {
    background: var(--w-card, #fff);
    color: var(--w-text, #0f172a);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 22px;
    padding: 26px 28px;
    box-shadow: var(--w-shadow, 0 10px 40px rgba(2,6,23,0.08));
}

/* ===== الهيدر ===== */
.msg-header {
    display: flex; justify-content: space-between; align-items: center;
    gap: 16px; flex-wrap: wrap;
    margin-bottom: 24px; padding-bottom: 20px;
    border-bottom: 1px solid var(--w-border, rgba(15,23,42,0.08));
}
.msg-header-titles { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.msg-header h2 {
    font-size: 24px; font-weight: 800; margin: 0;
    color: var(--w-text, #0f172a);
    display: flex; align-items: center; gap: 10px;
}
.msg-header .msg-sub { font-size: 13.5px; color: var(--w-text-muted, #475569); }

.btn-new-msg {
    background: var(--msg-grad); color: #fff;
    padding: 12px 22px; border-radius: 12px; border: none; cursor: pointer;
    font-weight: 700; font-size: 14px;
    display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 6px 18px rgba(102,126,234,0.35);
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn-new-msg:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(102,126,234,0.45); }
.btn-new-msg:active { transform: translateY(0); }

/* ===== قائمة المحادثات — شبكة بطاقات مستجيبة ===== */
.conv-list {
    display: grid; gap: 14px;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
}
.conv-item {
    position: relative; display: flex; align-items: flex-start; gap: 14px;
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 16px; padding: 18px;
    background: var(--w-card, #fff);
    cursor: pointer; overflow: hidden;
    transition: transform 0.18s, box-shadow 0.2s, border-color 0.2s;
}
.conv-item::before {
    content: ''; position: absolute; inset-inline-start: 0; top: 0; bottom: 0; width: 4px;
    background: var(--msg-grad); opacity: 0; transition: opacity 0.2s;
}
.conv-item:hover {
    transform: translateY(-3px);
    border-color: rgba(102,126,234,0.55);
    box-shadow: 0 12px 30px rgba(102,126,234,0.16);
}
.conv-item:hover::before { opacity: 1; }

.conv-avatar {
    flex-shrink: 0; width: 50px; height: 50px; border-radius: 14px;
    background: var(--msg-grad); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; font-weight: 800; line-height: 1;
    box-shadow: 0 6px 16px rgba(102,126,234,0.35);
}
.conv-main { flex: 1; min-width: 0; }
.conv-name {
    font-size: 16.5px; font-weight: 700; margin: 0 0 4px;
    color: var(--w-text, #0f172a);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.conv-about {
    display: inline-block; font-size: 12px; font-weight: 600;
    color: #6d28d9; background: var(--msg-grad-soft);
    padding: 2px 10px; border-radius: 999px; margin-bottom: 6px;
    max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.conv-snippet {
    color: var(--w-text-muted, #475569); font-size: 13.5px; line-height: 1.5;
    /* نص بلا تنسيق HTML — سطر واحد بحذف زائد */
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.conv-side {
    flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;
    text-align: end;
}
.conv-time { color: var(--w-text-muted, #475569); font-size: 12px; white-space: nowrap; }
.badge-new {
    background: #ef4444; color: #fff; padding: 3px 11px; border-radius: 999px;
    font-size: 11px; font-weight: 800; box-shadow: 0 3px 10px rgba(239,68,68,0.4);
}

/* حالة فارغة (القائمة) */
.conv-empty {
    grid-column: 1 / -1; text-align: center; padding: 64px 20px;
    color: var(--w-text-muted, #475569);
}
.conv-empty .ce-icon {
    width: 96px; height: 96px; margin: 0 auto 18px; border-radius: 28px;
    background: var(--msg-grad-soft);
    display: flex; align-items: center; justify-content: center; font-size: 46px;
}
.conv-empty h3 { font-size: 18px; font-weight: 700; margin-bottom: 6px; color: var(--w-text, #0f172a); }
.conv-empty p { font-size: 14px; }

/* ===== Modal ===== */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(2,6,23,0.62); backdrop-filter: blur(6px);
    z-index: 1000; justify-content: center; align-items: center; padding: 16px;
}
.modal-box {
    background: var(--w-card, #fff); color: var(--w-text, #0f172a);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 22px; width: 100%; max-width: 760px; max-height: 90vh;
    display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 30px 80px rgba(2,6,23,0.5);
}
.modal-head {
    padding: 18px 22px; flex-shrink: 0;
    display: flex; justify-content: space-between; align-items: center;
    background: var(--msg-grad); color: #fff;
}
.modal-head h3 {
    font-size: 18px; font-weight: 800; color: #fff; margin: 0;
    display: flex; align-items: center; gap: 10px;
}
.btn-close {
    background: rgba(255,255,255,0.18); color: #fff; border: none; border-radius: 10px;
    width: 38px; height: 38px; cursor: pointer; font-weight: 700; font-size: 16px;
    display: inline-flex; align-items: center; justify-content: center; transition: background 0.15s;
}
.btn-close:hover { background: rgba(255,255,255,0.32); }

/* لوحة الرسائل = خلفية الدردشة */
#messagesContainer {
    flex: 1; overflow-y: auto; padding: 22px 20px;
    display: flex; flex-direction: column; gap: 12px; min-height: 260px;
    background:
        radial-gradient(circle at 20% 0%, rgba(102,126,234,0.06), transparent 55%),
        var(--w-bg, #f8fafc);
}
.msg-bubble {
    max-width: 74%; padding: 11px 16px; border-radius: 16px;
    font-size: 14px; line-height: 1.6; word-wrap: break-word; overflow-wrap: anywhere;
    box-shadow: 0 2px 10px rgba(2,6,23,0.08);
}
.msg-bubble.me {
    align-self: flex-end;
    background: var(--msg-grad); color: #fff;
    border-bottom-right-radius: 5px;
}
.msg-bubble.other {
    align-self: flex-start;
    background: var(--w-card, #fff); color: var(--w-text, #0f172a);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-bottom-left-radius: 5px;
}
.msg-bubble .msg-time { font-size: 10.5px; opacity: 0.7; margin-top: 6px; }

/* محتوى غنيّ داخل الفقاعات */
.msg-bubble .msg-body img { max-width: 100%; border-radius: 8px; margin: 4px 0; height: auto; }
.msg-bubble .msg-body a { text-decoration: underline; }
.msg-bubble.me .msg-body a { color: #e0e7ff; }
.msg-bubble.other .msg-body a { color: #6366f1; }

/* حالة فارغة داخل الدردشة (تُنشأ من JS) */
.chat-empty { margin: auto; text-align: center; color: var(--w-text-muted, #475569); padding: 40px 20px; }
.chat-empty .ce-ic {
    width: 72px; height: 72px; margin: 0 auto 12px; border-radius: 22px;
    background: var(--msg-grad-soft);
    display: flex; align-items: center; justify-content: center; font-size: 34px;
}

/* ===== منطقة الكتابة ===== */
.compose-area {
    padding: 14px 18px; flex-shrink: 0;
    border-top: 1px solid var(--w-border, rgba(15,23,42,0.08));
    background: var(--w-card, #fff);
}

/* شريط أدوات مصغّر */
.rte-toolbar {
    display: flex; flex-wrap: wrap; gap: 5px; align-items: center;
    padding: 6px 10px; background: var(--w-bg, #f8fafc);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08)); border-bottom: none;
    border-radius: 12px 12px 0 0;
}
.rte-btn-mini {
    padding: 4px 9px; border: 1px solid var(--w-border, rgba(15,23,42,0.08)); border-radius: 6px;
    background: var(--w-card, #fff); color: var(--w-text, #0f172a); cursor: pointer; font-size: 12.5px;
    transition: background 0.15s, border-color 0.15s;
}
.rte-btn-mini:hover { background: var(--msg-grad-soft); border-color: rgba(102,126,234,0.4); }
.rte-sep { width: 1px; height: 20px; background: var(--w-border, rgba(15,23,42,0.12)); margin: 0 3px; }

.rte-editor-msg {
    min-height: 88px; max-height: 200px; overflow-y: auto; padding: 12px 14px;
    border: 1px solid var(--w-border, rgba(15,23,42,0.08)); border-radius: 0 0 12px 12px;
    font-family: 'Cairo', sans-serif; font-size: 14px; line-height: 1.7;
    outline: none; direction: rtl;
    background: var(--w-card, #fff); color: var(--w-text, #0f172a);
    transition: border-color 0.2s;
}
.rte-editor-msg:focus { border-color: rgba(102,126,234,0.55); }
.rte-editor-msg:empty::before {
    content: 'اكتب رسالتك هنا...';
    color: var(--w-text-muted, #a0aec0); pointer-events: none;
}

.compose-bottom { display: flex; justify-content: flex-end; margin-top: 10px; }
.btn-send {
    background: var(--msg-grad); color: #fff; border: none; border-radius: 12px;
    padding: 11px 30px; cursor: pointer; font-weight: 700; font-size: 14px;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 6px 18px rgba(102,126,234,0.35);
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn-send:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(102,126,234,0.45); }
.btn-send:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }

/* ===== نموذج رسالة جديدة ===== */
.form-modal-box {
    background: var(--w-card, #fff); color: var(--w-text, #0f172a);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 22px; width: 100%; max-width: 620px; padding: 28px;
    box-shadow: 0 30px 80px rgba(2,6,23,0.5);
    max-height: 90vh; overflow-y: auto;
}
.form-modal-title {
    font-size: 21px; font-weight: 800; margin-bottom: 20px;
    color: var(--w-text, #0f172a);
    display: flex; align-items: center; gap: 10px;
}
.form-label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 14px; color: var(--w-text, #0f172a); }
.form-select-field, .form-field {
    width: 100%; border: 1px solid var(--w-border, rgba(15,23,42,0.08)); border-radius: 12px;
    padding: 12px 14px; font-family: 'Cairo', sans-serif; font-size: 14px;
    background: var(--w-card, #fff); color: var(--w-text, #0f172a); transition: border-color 0.2s;
}
.form-select-field:focus, .form-field:focus { border-color: rgba(102,126,234,0.55); outline: none; }
.new-msg-editor {
    min-height: 120px; max-height: 280px; overflow-y: auto; padding: 12px 14px;
    border: 1px solid var(--w-border, rgba(15,23,42,0.08)); border-radius: 0 0 12px 12px;
    font-family: 'Cairo', sans-serif; font-size: 14px; line-height: 1.7;
    outline: none; direction: rtl;
    background: var(--w-card, #fff); color: var(--w-text, #0f172a);
}
.new-msg-editor:empty::before {
    content: 'اكتب رسالتك...';
    color: var(--w-text-muted, #a0aec0); pointer-events: none;
}
.form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px; }
.btn-cancel {
    background: var(--w-bg, #edf2f7); color: var(--w-text, #2d3748);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08)); border-radius: 12px;
    padding: 11px 24px; cursor: pointer; font-weight: 700; transition: background 0.15s;
}
.btn-cancel:hover { background: var(--w-border, #e2e8f0); }

/* ===== الاستجابة ===== */
@media (max-width: 1024px) {
    .msg-wrap { padding: 22px 20px; }
    .conv-list { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
}
@media (max-width: 640px) {
    .msg-wrap { padding: 18px 14px; border-radius: 18px; }
    .msg-header { margin-bottom: 18px; padding-bottom: 16px; }
    .msg-header h2 { font-size: 20px; }
    .btn-new-msg { width: 100%; justify-content: center; }
    .conv-list { grid-template-columns: 1fr; gap: 12px; }
    .conv-item { padding: 14px; gap: 12px; }
    .conv-avatar { width: 44px; height: 44px; font-size: 18px; }

    /* المودال يصبح لوحاً كامل الشاشة */
    .modal-overlay { padding: 0; align-items: stretch; }
    .modal-box {
        max-width: 100%; width: 100%; max-height: none;
        height: 100vh; height: 100dvh; border-radius: 0; border: none;
    }
    #messagesContainer { padding: 16px 14px; }
    .msg-bubble { max-width: 85%; }
    .compose-area { padding: 12px 14px; }
    .rte-toolbar { gap: 4px; padding: 6px 8px; }

    .form-modal-box {
        max-width: 100%; width: 100%; max-height: none;
        min-height: 100vh; min-height: 100dvh; border-radius: 0; border: none; padding: 22px 16px;
    }
    .form-actions { flex-direction: column-reverse; }
    .form-actions .btn-cancel, .form-actions .btn-send { width: 100%; justify-content: center; }
}

/* ===== Wahy dark-mode — تحسينات صريحة إضافية =====
   الأسطح تعمل أصلاً عبر --w-* في الوضعين؛ هنا فقط لمسات خاصة بالوضع الليلي. */
html[data-theme="dark"] .conv-item:hover {
    border-color: rgba(129,140,248,0.6) !important;
    box-shadow: 0 12px 30px rgba(0,0,0,0.4) !important;
}
html[data-theme="dark"] .conv-about { color: #c4b5fd !important; }
html[data-theme="dark"] #messagesContainer {
    background:
        radial-gradient(circle at 20% 0%, rgba(102,126,234,0.10), transparent 55%),
        var(--w-bg) !important;
}
html[data-theme="dark"] .rte-toolbar input[type="color"] { border-color: var(--w-border) !important; }
html[data-theme="dark"] .msg-bubble.other .msg-body a { color: #a5b4fc !important; }
</style>
@endpush

@section('content')
<div class="msg-wrap">
    <div class="msg-header">
        <div class="msg-header-titles">
            <h2>💬 المراسلات مع أولياء الأمور</h2>
            <span class="msg-sub">تواصل مباشر مع أولياء الأمور حول أبنائهم الطلاب</span>
        </div>
        <button class="btn-new-msg" onclick="showNewMessageModal()">✉️ رسالة جديدة</button>
    </div>

    {{-- قائمة المحادثات --}}
    <div class="conv-list">
        @forelse($conversations as $conversation)
        @if(!$conversation->parent) @continue @endif
        <div class="conv-item" onclick="openConversation({{ $conversation->parent_id }}, {{ $conversation->student_id ?? 'null' }})">
            <div class="conv-avatar">{{ mb_substr($conversation->parent->name ?? '👤', 0, 1) }}</div>
            <div class="conv-main">
                <h3 class="conv-name">{{ $conversation->parent->name ?? 'غير معروف' }}</h3>
                @if($conversation->student)
                <span class="conv-about">بخصوص: {{ $conversation->student->name }}</span>
                @endif
                <div class="conv-snippet">
                    {{ html_excerpt($conversation->message, 90) }}
                </div>
            </div>
            <div class="conv-side">
                <span class="conv-time">{{ $conversation->created_at->diffForHumans() }}</span>
                @if($conversation->sender_type === 'parent' && !$conversation->is_read)
                <span class="badge-new">جديد</span>
                @endif
            </div>
        </div>
        @empty
        <div class="conv-empty">
            <div class="ce-icon">📭</div>
            <h3>لا توجد رسائل بعد</h3>
            <p>ابدأ رسالة جديدة للتواصل مع أولياء أمور طلابك.</p>
        </div>
        @endforelse
    </div>

    {{ $conversations->links() }}
</div>

{{-- نافذة المحادثة --}}
<div id="conversationModal" class="modal-overlay">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="conversationTitle">
        <div class="modal-head">
            <h3 id="conversationTitle">💬 محادثة</h3>
            <button class="btn-close" onclick="closeConversation()" aria-label="إغلاق">✕</button>
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
    <div class="form-modal-box" role="dialog" aria-modal="true" aria-labelledby="newMsgTitle">
        <h3 id="newMsgTitle" class="form-modal-title">✉️ رسالة جديدة</h3>

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
                c.innerHTML = '<div class="chat-empty"><div class="ce-ic">💬</div><div>ابدأ المحادثة الآن</div></div>';
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

// فتح محادثة ولي أمر تلقائياً عند القدوم من صفحة تفاعل أولياء الأمور (?parent_id=)
(function () {
    const params = new URLSearchParams(window.location.search);
    const pid = params.get('parent_id');
    if (pid && typeof openConversation === 'function') {
        const sid = params.get('student_id') || null;
        openConversation(parseInt(pid, 10), sid ? parseInt(sid, 10) : null);
    }
})();
</script>
@endpush

@endsection
