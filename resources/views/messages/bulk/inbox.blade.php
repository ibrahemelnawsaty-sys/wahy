@php($__layoutRole = auth()->user()->getCurrentRole())
@extends($__layoutRole === 'super_admin' ? 'layouts.admin' : ($__layoutRole === 'school_admin' ? 'layouts.school-admin' : ($__layoutRole === 'teacher' ? 'layouts.teacher' : ($__layoutRole === 'parent' ? 'layouts.parent' : 'layouts.student-app'))))

@section('title', 'صندوق الوارد')

@section('content')
<style>
/* ============================================================
   صندوق الوارد الجماعي — طبقة بصرية فاخرة + استجابة + وضع ليلي
   الأسطح المحايدة مبنية على متغيّرات الثيم (--w-*) فتعمل في الوضعَين
   تلقائياً؛ اللكنة الكهرمانية (وارد) محفوظة كدلالة وظيفية.
   ============================================================ */
:root {
    --bi-accent: #f59e0b;
    --bi-accent-strong: #d97706;
}

.bi-page { padding: 0; max-width: 1040px; margin: 0 auto; }

/* ===== Hero ===== */
.bi-hero {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 24px;
    color: #fff;
    box-shadow: 0 18px 45px rgba(217, 119, 6, 0.32);
    position: relative;
    overflow: hidden;
}
.bi-hero::before {
    content: '';
    position: absolute;
    top: -40%;
    left: -20%;
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
    border-radius: 50%;
}
.bi-hero::after {
    content: '';
    position: absolute;
    bottom: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.07) 0%, transparent 70%);
    border-radius: 50%;
}
.bi-hero-icon {
    width: 60px; height: 60px;
    background: rgba(255,255,255,0.2);
    -webkit-backdrop-filter: blur(10px);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    margin-bottom: 14px;
    position: relative;
    z-index: 1;
}
.bi-hero h1 { font-size: 28px; font-weight: 800; margin: 0 0 6px; position: relative; z-index: 1; }
.bi-hero p { opacity: 0.92; font-size: 15px; margin: 0; position: relative; z-index: 1; }
.bi-hero-stats {
    position: relative; z-index: 1;
    margin-top: 20px;
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}
.bi-hero-stat {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.14);
    border: 1px solid rgba(255,255,255,0.20);
    border-radius: 14px;
    padding: 12px 18px;
    -webkit-backdrop-filter: blur(6px);
    backdrop-filter: blur(6px);
}
.bi-hero-stat-value {
    font-size: 28px;
    font-weight: 800;
    line-height: 1;
}
.bi-hero-stat-label {
    font-size: 12.5px;
    opacity: 0.9;
    line-height: 1.35;
}

/* ===== Card ===== */
.bi-card {
    background: var(--w-card, #fff);
    color: var(--w-text, #0f172a);
    border-radius: 20px;
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    box-shadow: 0 10px 40px rgba(2, 6, 23, 0.08);
    overflow: hidden;
}
.bi-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--w-border, rgba(15,23,42,0.08));
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: linear-gradient(135deg, rgba(245,158,11,0.06) 0%, rgba(217,119,6,0.03) 100%);
}
.bi-card-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--w-text, #1e293b);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.bi-header-badge {
    padding: 5px 14px;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 700;
    background: rgba(245,158,11,0.14);
    color: #b45309;
    border: 1px solid rgba(245,158,11,0.28);
    white-space: nowrap;
}

/* ===== Message Item ===== */
.bi-msg-item {
    padding: 20px 24px;
    border-bottom: 1px solid var(--w-border, rgba(15,23,42,0.08));
    transition: background 0.2s, box-shadow 0.2s;
    cursor: pointer;
}
.bi-msg-item:hover {
    background: linear-gradient(135deg, rgba(245,158,11,0.06) 0%, rgba(217,119,6,0.04) 100%);
}
.bi-msg-item:last-child { border-bottom: none; }
.bi-msg-item.unread {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border-right: 4px solid var(--bi-accent);
}
.bi-msg-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 8px;
}
.bi-msg-subject {
    font-size: 15px;
    font-weight: 700;
    color: var(--w-text, #1e293b);
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.bi-msg-badge-new {
    padding: 3px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    box-shadow: 0 2px 8px rgba(245,158,11,0.3);
}
.bi-msg-body {
    font-size: 14px;
    color: var(--w-text-muted, #64748b);
    line-height: 1.6;
    margin-bottom: 10px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.bi-msg-meta {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
}
.bi-msg-meta-item {
    font-size: 12px;
    color: var(--w-text-muted, #94a3b8);
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
}
.bi-msg-action {
    padding: 8px 18px;
    border-radius: 10px;
    border: 1px solid var(--w-border, #e2e8f0);
    background: var(--w-bg, #f8fafc);
    font-size: 13px;
    font-weight: 600;
    color: var(--w-text-muted, #475569);
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    flex-shrink: 0;
}
.bi-msg-action:hover {
    border-color: var(--bi-accent);
    color: var(--bi-accent-strong);
    background: rgba(245,158,11,0.12);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245,158,11,0.18);
}

/* ===== Alert ===== */
.bi-alert {
    padding: 14px 20px;
    border-radius: 14px;
    background: linear-gradient(135deg, #dcfce7, #d1fae5);
    color: #166534;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid #bbf7d0;
}

/* ===== Empty State ===== */
.bi-empty {
    text-align: center;
    padding: 70px 20px;
}
.bi-empty-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-radius: 24px;
    display: flex; align-items: center; justify-content: center;
    font-size: 40px;
    margin: 0 auto 18px;
    box-shadow: 0 8px 24px rgba(245,158,11,0.18);
}
.bi-empty h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--w-text, #475569);
    margin: 0 0 6px;
}
.bi-empty p {
    color: var(--w-text-muted, #94a3b8);
    margin: 0;
    font-size: 14px;
}

/* ===== Pagination ===== */
.bi-pagination {
    padding: 18px 24px;
    border-top: 1px solid var(--w-border, rgba(15,23,42,0.08));
}

/* ===== Modal (vanilla — لا يعتمد على Bootstrap) ===== */
.bi-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1050;
    background: rgba(15, 23, 42, 0.62);
    -webkit-backdrop-filter: blur(3px);
    backdrop-filter: blur(3px);
    display: none;              /* يُبدَّل إلى flex عبر JS (inline) */
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.bi-modal-box {
    background: var(--w-card, #fff);
    color: var(--w-text, #0f172a);
    border-radius: 18px;
    width: 100%;
    max-width: 720px;
    max-height: 90vh;
    overflow: auto;
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    box-shadow: 0 28px 60px rgba(2, 6, 23, 0.40);
}
.bi-modal-header {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.bi-modal-header h5 { font-weight: 700; margin: 0; color: #fff; font-size: 17px; }
.bi-modal-close {
    background: rgba(255,255,255,0.18);
    border: none;
    width: 34px; height: 34px;
    border-radius: 10px;
    color: #fff;
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s;
    flex-shrink: 0;
}
.bi-modal-close:hover { background: rgba(255,255,255,0.34); }
.bi-modal-body { padding: 24px; }
.bi-modal-body h6 {
    font-weight: 700;
    color: var(--w-text-muted, #475569);
    margin: 0 0 10px;
    font-size: 14px;
}
.bi-modal-meta {
    display: flex; gap: 16px; flex-wrap: wrap;
    padding: 14px 18px;
    background: var(--w-bg, #f8fafc);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 12px;
    margin-bottom: 16px;
}
.bi-modal-meta small { color: var(--w-text-muted, #94a3b8); }
.bi-modal-meta strong { color: var(--w-text, #1e293b); }
.bi-modal-content {
    padding: 18px;
    background: var(--w-bg, #f8fafc);
    border: 1px solid var(--w-border, rgba(15,23,42,0.08));
    border-radius: 12px;
    line-height: 1.8;
    color: var(--w-text, #334155);
}
/* تنسيق HTML المحرر */
.bi-modal-content img { max-width: 100%; border-radius: 8px; margin: 6px 0; height: auto; }
.bi-modal-content a { color: #3b82f6; text-decoration: underline; }
.bi-modal-content p { margin-bottom: 8px; }
.bi-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--w-border, rgba(15,23,42,0.08));
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.bi-modal-btn {
    padding: 10px 22px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.bi-modal-btn-close {
    background: var(--w-bg, #f1f5f9);
    color: var(--w-text, #475569);
    border: 1px solid var(--w-border, transparent);
}
.bi-modal-btn-close:hover { filter: brightness(0.96); }

/* ===== تغطية الوضع الليلي — لكنات فوق الأسطح المبنية على المتغيّرات ===== */
html[data-theme="dark"] .bi-msg-item.unread { background: rgba(245, 158, 11, 0.10); }
html[data-theme="dark"] .bi-msg-item:hover { background: rgba(245, 158, 11, 0.07); }
html[data-theme="dark"] .bi-msg-action:hover { color: #fcd34d; background: rgba(245, 158, 11, 0.15); }
html[data-theme="dark"] .bi-header-badge { color: #fcd34d; }
html[data-theme="dark"] .bi-alert {
    background: rgba(16, 185, 129, 0.15);
    color: #6ee7b7;
    border-color: rgba(16, 185, 129, 0.32);
}
html[data-theme="dark"] .bi-empty-icon { box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35); }

/* ===== الاستجابة ===== */
@media (max-width: 1024px) {
    .bi-hero { padding: 26px; }
    .bi-card-header { padding: 18px 20px; }
    .bi-msg-item { padding: 18px 20px; }
}
@media (max-width: 640px) {
    .bi-hero { padding: 22px 18px; border-radius: 16px; margin-bottom: 18px; }
    .bi-hero-icon { width: 50px; height: 50px; font-size: 22px; margin-bottom: 10px; }
    .bi-hero h1 { font-size: 22px; }
    .bi-hero p { font-size: 14px; }
    .bi-hero-stats { gap: 10px; margin-top: 16px; }
    .bi-hero-stat { padding: 10px 14px; flex: 1 1 auto; }
    .bi-hero-stat-value { font-size: 22px; }
    .bi-card { border-radius: 16px; }
    .bi-card-header { padding: 16px; }
    .bi-card-header h3 { font-size: 16px; }
    .bi-msg-item { padding: 16px; }
    .bi-msg-header { flex-direction: column; align-items: stretch; gap: 10px; }
    .bi-msg-action { align-self: flex-start; }
    .bi-msg-meta { gap: 10px 14px; }
    .bi-pagination { padding: 16px; }
    .bi-modal-backdrop { padding: 12px; }
    .bi-modal-box { max-height: 94vh; border-radius: 16px; }
    .bi-modal-header { padding: 16px 18px; }
    .bi-modal-header h5 { font-size: 16px; }
    .bi-modal-body { padding: 18px; }
    .bi-modal-meta { flex-direction: column; gap: 10px; }
    .bi-modal-footer { padding: 14px 18px; }
}
</style>

<div class="bi-page">
    <!-- Hero Header -->
    <div class="bi-hero">
        <div class="bi-hero-icon">📬</div>
        <h1>صندوق الوارد</h1>
        <p>الرسائل الجماعية المستلمة</p>
        <div class="bi-hero-stats">
            <div class="bi-hero-stat">
                <div class="bi-hero-stat-value">{{ $messages->total() }}</div>
                <div class="bi-hero-stat-label">إجمالي<br>الرسائل</div>
            </div>
            <div class="bi-hero-stat">
                <div class="bi-hero-stat-value">{{ $unreadCount }}</div>
                <div class="bi-hero-stat-label">رسائل<br>غير مقروءة</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bi-alert">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Messages List -->
    <div class="bi-card">
        <div class="bi-card-header">
            <h3>
                <span style="font-size: 20px;">📨</span>
                الرسائل المستلمة
            </h3>
            @if($unreadCount > 0)
                <span class="bi-header-badge">{{ $unreadCount }} غير مقروءة</span>
            @endif
        </div>
        <div>
            @if($messages->count() > 0)
                @foreach($messages as $recipient)
                    @php
                        $message = $recipient->bulkMessage;
                        $isRead = $recipient->read_at !== null;
                    @endphp
                    <div class="bi-msg-item {{ !$isRead ? 'unread' : '' }}"
                         onclick="biOpenModal({{ $recipient->id }}); @if(!$isRead) markAsRead({{ $recipient->id }}); @endif">
                        <div class="bi-msg-header">
                            <div class="bi-msg-subject">
                                @if(!$isRead)
                                    <span class="bi-msg-badge-new">جديد</span>
                                @endif
                                {{ $message->subject }}
                            </div>
                            <button class="bi-msg-action" onclick="event.stopPropagation(); biOpenModal({{ $recipient->id }})">
                                📖 قراءة
                            </button>
                        </div>
                        <div class="bi-msg-body">
                            {{ html_excerpt($message->message, 200) }}
                        </div>
                        <div class="bi-msg-meta">
                            <div class="bi-msg-meta-item">
                                <span>👤</span>
                                من: {{ $message->sender->name ?? 'غير محدد' }}
                            </div>
                            <div class="bi-msg-meta-item">
                                <span>🕒</span>
                                {{ $message->sent_at->diffForHumans() }}
                            </div>
                            @if($isRead)
                                <div class="bi-msg-meta-item" style="color: #10b981;">
                                    <span>✓✓</span>
                                    تم القراءة {{ $recipient->read_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Message Modal (vanilla — لا يعتمد على Bootstrap) -->
                    <div class="bi-modal-backdrop" id="messageModal{{ $recipient->id }}" style="display:none;">
                        <div class="bi-modal-box" role="dialog" aria-modal="true" aria-label="عرض الرسالة">
                            <div class="bi-modal-header">
                                <h5>📨 {{ $message->subject }}</h5>
                                <button type="button" class="bi-modal-close" onclick="biCloseModal({{ $recipient->id }})" aria-label="إغلاق">✕</button>
                            </div>
                            <div class="bi-modal-body">
                                <div class="bi-modal-meta">
                                    <div>
                                        <small>المرسل</small><br>
                                        <strong>{{ $message->sender->name ?? 'غير محدد' }}</strong>
                                    </div>
                                    <div>
                                        <small>تاريخ الإرسال</small><br>
                                        <strong>{{ $message->sent_at->format('Y-m-d H:i') }}</strong>
                                    </div>
                                    @if($isRead)
                                    <div>
                                        <small>تم القراءة</small><br>
                                        <strong style="color: #10b981;">{{ $recipient->read_at->format('Y-m-d H:i') }}</strong>
                                    </div>
                                    @endif
                                </div>
                                <h6>📝 نص الرسالة</h6>
                                <div class="bi-modal-content">{!! safe_html($message->message) !!}</div>
                            </div>
                            <div class="bi-modal-footer">
                                <button type="button" class="bi-modal-btn bi-modal-btn-close" onclick="biCloseModal({{ $recipient->id }})">
                                    إغلاق
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="bi-pagination">
                    {{ $messages->links() }}
                </div>
            @else
                <div class="bi-empty">
                    <div class="bi-empty-icon">📬</div>
                    <h3>لا توجد رسائل في صندوق الوارد</h3>
                    <p>ستظهر هنا الرسائل الجماعية المرسلة إليك</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function biOpenModal(recipientId) {
    const el = document.getElementById('messageModal' + recipientId);
    if (el) { el.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
}
function biCloseModal(recipientId) {
    const el = document.getElementById('messageModal' + recipientId);
    if (el) { el.style.display = 'none'; document.body.style.overflow = ''; }
}
document.addEventListener('click', (e) => {
    if (e.target.classList && e.target.classList.contains('bi-modal-backdrop')) {
        e.target.style.display = 'none';
        document.body.style.overflow = '';
    }
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.bi-modal-backdrop').forEach(m => m.style.display = 'none');
        document.body.style.overflow = '';
    }
});

function markAsRead(recipientId) {
    fetch(`/messages/bulk/${recipientId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(response => {
        if (response.ok) {
            // إزالة وضع "غير مقروءة" من العنصر بعد القراءة (Issue #18)
            const listItem = document.querySelector(`.bi-msg-item[onclick*="biOpenModal(${recipientId})"]`);
            if (listItem) {
                listItem.classList.remove('unread');
                const badge = listItem.querySelector('.bi-msg-badge-new');
                if (badge) badge.remove();
            }
        }
    });
}
</script>
@endsection
