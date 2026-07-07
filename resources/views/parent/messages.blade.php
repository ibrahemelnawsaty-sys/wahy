@extends('layouts.parent')

@section('title', 'المراسلات')

@push('styles')
<style>
/* ============================================================
   Wahy · مراسلات ولي الأمر ↔ المعلم — طبقة بصرية فاخرة.
   الوظائف محفوظة بالكامل: نُبنى كل الأسطح على متغيّرات النظام
   الموحّد (--w-*) فتعمل في الوضعين الليلي/النهاري دون فاتح-على-فاتح.
   إضافة: استجابة كاملة (لابتوب/تابلت/جوال) + لمسات فخامة.
   ============================================================ */
:root {
    --msg-brand: linear-gradient(135deg, #667eea, #764ba2);
    --msg-brand-soft: linear-gradient(135deg, rgba(102,126,234,.14), rgba(118,75,162,.14));
}

/* بطاقة الغلاف */
.msg-wrap {
    background: var(--w-card, #fff);
    color: var(--w-text, #0f172a);
    border: 1px solid var(--w-border, rgba(15,23,42,.06));
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 10px 40px rgba(2,6,23,.08);
}

/* الهيدر */
.msg-header {
    display: flex; justify-content: space-between; align-items: center;
    gap: 16px; margin-bottom: 24px;
    padding-bottom: 20px; border-bottom: 1px solid var(--w-border, rgba(15,23,42,.06));
}
.msg-header-titles { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.msg-header h2 { font-size: 22px; font-weight: 800; color: var(--w-text, #0f172a); letter-spacing: -.02em; }
.msg-header-sub { font-size: 13px; color: var(--w-text-muted, #64748b); font-weight: 500; }

.btn-new-msg {
    background: var(--msg-brand);
    color: #fff; padding: 12px 22px; border-radius: 12px;
    border: none; cursor: pointer; font-weight: 700; font-size: 14px;
    display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;
    box-shadow: 0 6px 18px rgba(102,126,234,.28);
    transition: transform .2s, box-shadow .2s;
}
.btn-new-msg:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(102,126,234,.4); }
.btn-new-msg:active { transform: translateY(0); }

/* قائمة المحادثات */
.conv-list { display: grid; gap: 12px; }
.conv-item {
    border: 1px solid var(--w-border, rgba(15,23,42,.08)); border-radius: 16px;
    padding: 16px 18px; cursor: pointer; background: var(--w-card, #fff);
    display: flex; align-items: center; gap: 14px;
    transition: transform .18s, border-color .18s, box-shadow .18s, background .18s;
}
.conv-item:hover {
    border-color: #a5b4fc; background: var(--w-bg, #f8f7ff);
    transform: translateY(-2px); box-shadow: 0 10px 26px rgba(102,126,234,.16);
}
.conv-avatar {
    flex-shrink: 0; width: 52px; height: 52px; border-radius: 15px;
    background: var(--msg-brand); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 24px;
    box-shadow: 0 6px 16px rgba(102,126,234,.32);
}
.conv-main { flex: 1; min-width: 0; }
.conv-name { font-size: 16px; font-weight: 700; color: var(--w-text, #0f172a); margin-bottom: 3px; }
.conv-about { color: var(--w-text-muted, #64748b); font-size: 12.5px; margin-bottom: 5px; }
.conv-snippet {
    color: var(--w-text-muted, #64748b); font-size: 13.5px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.conv-meta { text-align: left; flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
.conv-time { color: var(--w-text-muted, #94a3b8); font-size: 12px; white-space: nowrap; }
.badge-new {
    background: linear-gradient(135deg, #f56565, #e53e3e); color: #fff;
    padding: 4px 11px; border-radius: 20px; font-size: 11px; font-weight: 800;
    box-shadow: 0 3px 10px rgba(245,101,101,.4);
}

/* حالة فارغة أنيقة */
.conv-empty { text-align: center; padding: 56px 20px; }
.conv-empty-icon {
    width: 88px; height: 88px; margin: 0 auto 18px; border-radius: 26px;
    background: var(--msg-brand-soft);
    display: flex; align-items: center; justify-content: center; font-size: 44px;
}
.conv-empty-title { font-size: 18px; font-weight: 800; color: var(--w-text, #0f172a); margin-bottom: 6px; }
.conv-empty-text { font-size: 14px; color: var(--w-text-muted, #64748b); max-width: 420px; margin: 0 auto; line-height: 1.7; }

/* ===== الطبقة والنوافذ ===== */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(2,6,23,.62); -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px);
    z-index: 1000; justify-content: center; align-items: center; padding: 16px;
}
.modal-box {
    background: var(--w-card, #fff); color: var(--w-text, #0f172a);
    border: 1px solid var(--w-border, rgba(15,23,42,.06)); border-radius: 20px;
    width: 100%; max-width: 760px; max-height: 88vh;
    display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 24px 70px rgba(2,6,23,.4);
    animation: msgModalIn .28s cubic-bezier(.16,.84,.44,1);
}
@keyframes msgModalIn { from { opacity: 0; transform: translateY(16px) scale(.98); } to { opacity: 1; transform: none; } }

.modal-head {
    padding: 16px 20px; border-bottom: 1px solid var(--w-border, rgba(15,23,42,.08));
    display: flex; align-items: center; gap: 12px; flex-shrink: 0;
    background: var(--w-card, #fff); position: sticky; top: 0; z-index: 2;
}
.modal-head-avatar {
    width: 44px; height: 44px; border-radius: 13px; flex-shrink: 0;
    background: var(--msg-brand); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
    box-shadow: 0 5px 14px rgba(102,126,234,.32);
}
.modal-head-info { flex: 1; min-width: 0; }
.modal-head h3 { font-size: 17px; font-weight: 800; color: var(--w-text, #0f172a); }
.modal-head-sub { font-size: 12px; color: var(--w-text-muted, #64748b); margin-top: 2px; }
.btn-close {
    background: var(--w-bg, #f1f5f9); color: var(--w-text-muted, #64748b);
    border: 1px solid var(--w-border, rgba(15,23,42,.08)); border-radius: 10px;
    width: 36px; height: 36px; flex-shrink: 0; cursor: pointer; font-weight: 700; font-size: 16px;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s, color .15s;
}
.btn-close:hover { background: rgba(245,101,101,.15); color: #f87171; }

/* حاوية الرسائل — خلفية دردشة أنيقة */
#messagesContainer {
    flex: 1; overflow-y: auto; padding: 20px;
    display: flex; flex-direction: column; gap: 10px; min-height: 260px;
    background:
        radial-gradient(1200px 220px at 15% -5%, rgba(102,126,234,.06), transparent 70%),
        var(--w-bg, #f7f8fc);
}
#messagesContainer::-webkit-scrollbar { width: 8px; }
#messagesContainer::-webkit-scrollbar-thumb { background: var(--w-border, rgba(15,23,42,.15)); border-radius: 8px; }

/* الفقاعات */
.msg-bubble {
    max-width: 74%; padding: 11px 16px; border-radius: 16px;
    box-shadow: 0 2px 10px rgba(2,6,23,.06); line-height: 1.7; font-size: 14px;
    word-wrap: break-word; overflow-wrap: anywhere;
    animation: msgBubbleIn .22s ease-out;
}
@keyframes msgBubbleIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
.msg-bubble.me {
    align-self: flex-end; background: var(--msg-brand); color: #fff;
    border-bottom-right-radius: 5px; box-shadow: 0 6px 18px rgba(102,126,234,.3);
}
.msg-bubble.other {
    align-self: flex-start; background: var(--w-card, #fff); color: var(--w-text, #2d3748);
    border: 1px solid var(--w-border, rgba(15,23,42,.08)); border-bottom-left-radius: 5px;
}
.msg-bubble .msg-time { font-size: 11px; opacity: .7; margin-top: 5px; }
.msg-bubble .msg-body img { max-width: 100%; border-radius: 8px; margin: 4px 0; height: auto; }
.msg-bubble .msg-body a { text-decoration: underline; }
.msg-bubble.me .msg-body a { color: #c7d2fe; }
.msg-bubble.other .msg-body a { color: #3b82f6; }

/* ===== منطقة الكتابة ===== */
.compose-area {
    padding: 14px 18px; border-top: 1px solid var(--w-border, rgba(15,23,42,.08));
    flex-shrink: 0; background: var(--w-card, #fff);
}
.rte-toolbar {
    display: flex; flex-wrap: wrap; gap: 4px; padding: 6px 10px;
    background: var(--w-bg, #f8fafc);
    border: 1px solid var(--w-border, rgba(15,23,42,.08)); border-bottom: none;
    border-radius: 12px 12px 0 0; align-items: center;
}
.rte-btn-mini {
    padding: 4px 9px; border: 1px solid var(--w-border, rgba(15,23,42,.12)); border-radius: 6px;
    background: var(--w-card, #fff); cursor: pointer; font-size: 12px; color: var(--w-text, #334155);
    transition: background .15s, transform .1s;
}
.rte-btn-mini:hover { background: var(--w-bg, #e2e8f0); }
.rte-btn-mini:active { transform: scale(.94); }
.rte-sep { width: 1px; height: 20px; background: var(--w-border, #cbd5e1); margin: 0 3px; }

.rte-editor-msg {
    min-height: 88px; max-height: 200px; overflow-y: auto;
    padding: 12px; border: 1px solid var(--w-border, rgba(15,23,42,.12)); border-radius: 0 0 12px 12px;
    font-family: 'Cairo', sans-serif; font-size: 14px; line-height: 1.7;
    outline: none; direction: rtl; background: var(--w-card, #fff); color: var(--w-text, #0f172a);
    transition: border-color .15s;
}
.rte-editor-msg:focus { border-color: #a5b4fc; }
.rte-editor-msg:empty::before { content: 'اكتب رسالتك هنا...'; color: var(--w-text-muted, #a0aec0); pointer-events: none; }

.compose-bottom { display: flex; justify-content: flex-end; margin-top: 10px; }
.btn-send {
    background: var(--msg-brand); color: #fff; border: none; border-radius: 12px;
    padding: 10px 26px; cursor: pointer; font-weight: 700; font-size: 14px;
    box-shadow: 0 6px 16px rgba(102,126,234,.28); transition: transform .2s, box-shadow .2s;
}
.btn-send:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(102,126,234,.4); }
.btn-send:disabled { opacity: .55; cursor: not-allowed; transform: none; box-shadow: none; }

/* ===== نافذة رسالة جديدة ===== */
.form-modal-box {
    background: var(--w-card, #fff); color: var(--w-text, #0f172a);
    border: 1px solid var(--w-border, rgba(15,23,42,.06)); border-radius: 20px;
    width: 100%; max-width: 600px; padding: 28px;
    box-shadow: 0 24px 70px rgba(2,6,23,.4); max-height: 90vh; overflow-y: auto;
    animation: msgModalIn .28s cubic-bezier(.16,.84,.44,1);
}
.form-modal-box h3 { color: var(--w-text, #0f172a); }
.form-label { display: block; margin-bottom: 8px; font-weight: 700; color: var(--w-text-muted, #4a5568); font-size: 14px; }
.form-select-field {
    width: 100%; border: 1px solid var(--w-border, rgba(15,23,42,.12)); border-radius: 12px;
    padding: 12px; font-family: 'Cairo', sans-serif; font-size: 14px;
    background: var(--w-card, #fff); color: var(--w-text, #0f172a); transition: border-color .15s;
}
.form-select-field:focus { border-color: #a5b4fc; outline: none; }
.new-msg-editor {
    min-height: 120px; max-height: 280px; overflow-y: auto;
    padding: 12px; border: 1px solid var(--w-border, rgba(15,23,42,.12)); border-radius: 0 0 12px 12px;
    font-family: 'Cairo', sans-serif; font-size: 14px; line-height: 1.7;
    outline: none; direction: rtl; background: var(--w-card, #fff); color: var(--w-text, #0f172a);
}
.new-msg-editor:empty::before { content: 'اكتب رسالتك...'; color: var(--w-text-muted, #a0aec0); pointer-events: none; }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 6px; }
.btn-cancel {
    background: var(--w-bg, #edf2f7); color: var(--w-text, #2d3748);
    border: 1px solid var(--w-border, rgba(15,23,42,.08)); border-radius: 12px;
    padding: 10px 22px; cursor: pointer; font-weight: 700; transition: background .15s;
}
.btn-cancel:hover { background: var(--w-border, #e2e8f0); }

/* ============================================================
   تغطية الوضع الليلي — للعناصر الجديدة الخاصة بهذه الصفحة.
   (الأصناف القاعدية مغطّاة أصلاً في layout؛ نُكمل الفجوات هنا
   بنفس منطق teacher/messages: لا فاتح-على-فاتح ولا داكن-على-داكن.)
   ============================================================ */
html[data-theme="dark"] .msg-wrap,
html[data-theme="dark"] .modal-box,
html[data-theme="dark"] .form-modal-box {
    background: var(--w-card) !important;
    border-color: var(--w-border) !important;
    box-shadow: var(--w-shadow) !important;
}
html[data-theme="dark"] .msg-header,
html[data-theme="dark"] .modal-head,
html[data-theme="dark"] .compose-area { border-color: var(--w-border) !important; }
html[data-theme="dark"] .msg-header h2,
html[data-theme="dark"] .modal-head h3,
html[data-theme="dark"] .conv-name,
html[data-theme="dark"] .conv-empty-title,
html[data-theme="dark"] .form-modal-box h3 { color: var(--w-text) !important; }
html[data-theme="dark"] .msg-header-sub,
html[data-theme="dark"] .modal-head-sub,
html[data-theme="dark"] .conv-about,
html[data-theme="dark"] .conv-snippet,
html[data-theme="dark"] .conv-time,
html[data-theme="dark"] .conv-empty-text,
html[data-theme="dark"] .form-label { color: var(--w-text-muted) !important; }
html[data-theme="dark"] .modal-head { background: var(--w-card) !important; }
html[data-theme="dark"] .btn-close {
    background: rgba(255,255,255,.06) !important; color: var(--w-text-muted) !important;
    border-color: var(--w-border) !important;
}
html[data-theme="dark"] .btn-close:hover { background: rgba(248,113,113,.18) !important; color: #f87171 !important; }
html[data-theme="dark"] #messagesContainer {
    background:
        radial-gradient(1200px 220px at 15% -5%, rgba(102,126,234,.12), transparent 70%),
        var(--w-bg) !important;
}
html[data-theme="dark"] .msg-bubble.other {
    background: rgba(255,255,255,.06) !important; color: var(--w-text) !important;
    border-color: var(--w-border) !important;
}
html[data-theme="dark"] .rte-sep { background: var(--w-border) !important; }
html[data-theme="dark"] .rte-editor-msg:empty::before,
html[data-theme="dark"] .new-msg-editor:empty::before { color: var(--w-text-muted) !important; }

/* ============================================================
   الاستجابة — لابتوب / تابلت / جوال.
   ============================================================ */
@media (max-width: 1024px) {
    .msg-wrap { padding: 22px; }
    .modal-box { max-width: 92vw; }
}
@media (max-width: 640px) {
    .msg-wrap { padding: 16px; border-radius: 16px; }
    .msg-header { flex-direction: column; align-items: stretch; gap: 12px; margin-bottom: 18px; padding-bottom: 16px; }
    .btn-new-msg { width: 100%; justify-content: center; }
    .conv-item { padding: 13px 14px; gap: 11px; border-radius: 14px; }
    .conv-avatar { width: 44px; height: 44px; border-radius: 13px; font-size: 20px; }
    .conv-name { font-size: 15px; }
    .conv-snippet { font-size: 13px; }

    .modal-overlay { padding: 0; align-items: stretch; }
    .modal-box {
        max-width: none; width: 100%; max-height: none;
        height: 100vh; height: 100dvh; border-radius: 0; border: none;
    }
    .form-modal-box {
        max-width: none; width: 100%; max-height: 100vh; max-height: 100dvh;
        min-height: 100dvh; border-radius: 0; border: none; padding: 22px 18px;
    }
    #messagesContainer { padding: 16px; }
    .msg-bubble { max-width: 85%; }
    .compose-area { padding: 12px 14px; }
    .rte-editor-msg { min-height: 70px; }
}
@media (max-width: 380px) {
    .rte-toolbar { gap: 3px; padding: 5px 7px; }
    .rte-btn-mini { padding: 3px 7px; }
    .btn-send { padding: 10px 20px; }
}
</style>
@endpush

@section('content')
<div class="msg-wrap">
    <div class="msg-header">
        <div class="msg-header-titles">
            <h2>💬 المراسلات مع المعلمين</h2>
            <span class="msg-header-sub">تواصل مباشر وآمن مع معلمي أبنائك</span>
        </div>
        <button class="btn-new-msg" onclick="showNewMessageModal()"><span>✉️</span><span>رسالة جديدة</span></button>
    </div>

    <div class="conv-list">
        @forelse($conversations as $conversation)
        <div class="conv-item" onclick="openConversation({{ $conversation->teacher_id }}, {{ $conversation->student_id ?? 'null' }})">
            <div class="conv-avatar">👨‍🏫</div>
            <div class="conv-main">
                <h3 class="conv-name">{{ $conversation->teacher->name }}</h3>
                @if($conversation->student)
                <div class="conv-about">بخصوص: {{ $conversation->student->name }}</div>
                @endif
                <div class="conv-snippet">
                    {{ html_excerpt($conversation->message, 90) }}
                </div>
            </div>
            <div class="conv-meta">
                <div class="conv-time">
                    {{ $conversation->created_at->diffForHumans() }}
                </div>
                @if($conversation->sender_type === 'teacher' && !$conversation->is_read)
                <span class="badge-new">جديد</span>
                @endif
            </div>
        </div>
        @empty
        <div class="conv-empty">
            <div class="conv-empty-icon">📭</div>
            <div class="conv-empty-title">لا توجد رسائل بعد</div>
            <div class="conv-empty-text">ابدأ محادثة جديدة مع أحد المعلمين للاطمئنان على أبنائك ومتابعة تقدّمهم.</div>
        </div>
        @endforelse
    </div>

    {{ $conversations->links() }}
</div>

{{-- نافذة المحادثة --}}
<div id="conversationModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <div class="modal-head-avatar">💬</div>
            <div class="modal-head-info">
                <h3 id="conversationTitle">محادثة</h3>
                <div class="modal-head-sub">مراسلة مباشرة مع المعلم</div>
            </div>
            <button class="btn-close" onclick="closeConversation()" aria-label="إغلاق">✕</button>
        </div>

        <div id="messagesContainer"></div>

        <div class="compose-area">
            <div class="rte-toolbar">
                <button type="button" class="rte-btn-mini" onclick="msgExec('bold')"><b>B</b></button>
                <button type="button" class="rte-btn-mini" onclick="msgExec('italic')"><i>I</i></button>
                <button type="button" class="rte-btn-mini" onclick="msgExec('underline')"><u>U</u></button>
                <span class="rte-sep"></span>
                <input type="color" value="#000000" title="لون النص"
                       onchange="msgExec('foreColor', this.value)"
                       style="width:24px;height:24px;border:1px solid #cbd5e1;border-radius:4px;cursor:pointer;padding:0;">
                <span class="rte-sep"></span>
                <button type="button" class="rte-btn-mini" onclick="msgInsertImg()">🖼️</button>
                <button type="button" class="rte-btn-mini" onclick="msgExec('insertUnorderedList')">•≡</button>
            </div>
            <div id="msgEditor" class="rte-editor-msg" contenteditable="true" dir="rtl"></div>
            <div class="compose-bottom">
                <button class="btn-send" id="btnSend" onclick="sendMessage()">↑ إرسال</button>
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
                <label class="form-label">المعلم</label>
                <select id="newTeacherId" required class="form-select-field">
                    <option value="">اختر المعلم...</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">الطالب (اختياري)</label>
                <select id="newStudentId" class="form-select-field">
                    <option value="">عام - غير محدد</option>
                    @foreach(auth()->user()->children as $child)
                    <option value="{{ $child->id }}">{{ $child->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">الرسالة</label>
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
                <div id="newMsgEditor" class="new-msg-editor" contenteditable="true" dir="rtl"></div>
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
let currentTeacherId = null;
let currentStudentId = null;

function showNewMessageModal() {
    document.getElementById('newMessageModal').style.display = 'flex';
    document.getElementById('newMsgEditor').innerHTML = '';
}
function closeNewMessageModal() {
    document.getElementById('newMessageModal').style.display = 'none';
    document.getElementById('newMessageForm').reset();
    document.getElementById('newMsgEditor').innerHTML = '';
}
function openConversation(teacherId, studentId = null) {
    currentTeacherId = teacherId;
    currentStudentId = studentId;
    document.getElementById('conversationModal').style.display = 'flex';
    document.getElementById('msgEditor').innerHTML = '';
    loadMessages();
}
function closeConversation() {
    document.getElementById('conversationModal').style.display = 'none';
    currentTeacherId = null;
    currentStudentId = null;
}

function loadMessages() {
    const url = `{{ route('parent.messages.conversation') }}?teacher_id=${currentTeacherId}` +
                (currentStudentId ? `&student_id=${currentStudentId}` : '');
    fetch(url)
        .then(r => r.json())
        .then(messages => {
            const c = document.getElementById('messagesContainer');
            c.innerHTML = '';
            if (!messages.length) {
                c.innerHTML = '<div style="text-align:center;color:#a0aec0;padding:40px 20px;">لا توجد رسائل. ابدأ المحادثة...</div>';
                return;
            }
            messages.forEach(msg => {
                const isMe = msg.sender_type === 'parent';
                const div = document.createElement('div');
                div.className = 'msg-bubble ' + (isMe ? 'me' : 'other');
                // أمان (XSS مخزّن): نعرض النص فقط عبر textContent بدل innerHTML
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

function sendMessage() {
    const editor = document.getElementById('msgEditor');
    const html = editor.innerHTML.trim();
    if (!html || html === '<br>') return;
    const btn = document.getElementById('btnSend');
    btn.disabled = true;

    fetch('{{ route('parent.messages.send') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ teacher_id: currentTeacherId, student_id: currentStudentId, message: html })
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
    .catch(err => { console.error(err); btn.disabled = false; });
}

document.getElementById('newMessageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const teacherId = document.getElementById('newTeacherId').value;
    const studentId = document.getElementById('newStudentId').value || null;
    const html = document.getElementById('newMsgEditor').innerHTML.trim();
    if (!html || html === '<br>') { alert('الرجاء كتابة رسالتك'); return; }

    fetch('{{ route('parent.messages.send') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ teacher_id: teacherId, student_id: studentId, message: html })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { closeNewMessageModal(); location.reload(); }
        else { alert('حدث خطأ: ' + (data.error || 'حاول مرة أخرى')); }
    })
    .catch(err => { console.error(err); alert('خطأ في الاتصال'); });
});

function msgExec(cmd, val) { document.getElementById('msgEditor').focus(); document.execCommand(cmd, false, val || null); }
function newMsgExec(cmd, val) { document.getElementById('newMsgEditor').focus(); document.execCommand(cmd, false, val || null); }
function msgInsertImg() {
    const url = prompt('أدخل رابط الصورة:');
    if (url) { document.getElementById('msgEditor').focus(); document.execCommand('insertHTML', false, `<img src="${url}" style="max-width:100%;border-radius:8px;margin:4px 0;height:auto;">`); }
}
function newMsgInsertImg() {
    const url = prompt('أدخل رابط الصورة:');
    if (url) { document.getElementById('newMsgEditor').focus(); document.execCommand('insertHTML', false, `<img src="${url}" style="max-width:100%;border-radius:8px;margin:4px 0;height:auto;">`); }
}

document.getElementById('msgEditor').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});
</script>
@endpush

@endsection
