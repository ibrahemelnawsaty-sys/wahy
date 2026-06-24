@extends(auth()->user()->role === 'super_admin' ? 'layouts.admin' : (auth()->user()->role === 'school_admin' ? 'layouts.school-admin' : (auth()->user()->role === 'teacher' ? 'layouts.teacher' : (auth()->user()->role === 'parent' ? 'layouts.parent' : (auth()->user()->role === 'student' ? 'layouts.student-app' : 'layouts.student-app')))))

@section('title', 'صندوق الوارد')

@section('content')
<style>
.bi-page { padding: 0; }

.bi-hero {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
    border-radius: 18px;
    padding: 32px;
    margin-bottom: 28px;
    color: white;
    box-shadow: 0 10px 30px rgba(245, 158, 11, 0.35);
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
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}
.bi-hero::after {
    content: '';
    position: absolute;
    bottom: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
    border-radius: 50%;
}
.bi-hero-icon {
    width: 60px; height: 60px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    margin-bottom: 14px;
}
.bi-hero h1 { font-size: 28px; font-weight: 800; margin: 0 0 6px; position: relative; z-index: 1; }
.bi-hero p { opacity: 0.9; font-size: 15px; margin: 0; position: relative; z-index: 1; }
.bi-hero-stats {
    position: relative; z-index: 1;
    margin-top: 18px;
    display: flex;
    gap: 24px;
}
.bi-hero-stat {
    display: flex;
    align-items: center;
    gap: 8px;
}
.bi-hero-stat-value {
    font-size: 28px;
    font-weight: 800;
}
.bi-hero-stat-label {
    font-size: 13px;
    opacity: 0.85;
}

/* Card */
.bi-card {
    background: white;
    border-radius: 18px;
    border: 2px solid #f1f5f9;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    overflow: hidden;
}
.bi-card-header {
    padding: 20px 24px;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, rgba(245,158,11,0.03) 0%, rgba(217,119,6,0.03) 100%);
}
.bi-card-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Message Item */
.bi-msg-item {
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s;
    cursor: pointer;
}
.bi-msg-item:hover {
    background: linear-gradient(135deg, rgba(245,158,11,0.02) 0%, rgba(217,119,6,0.02) 100%);
}
.bi-msg-item:last-child { border-bottom: none; }
.bi-msg-item.unread {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border-right: 4px solid #f59e0b;
}
.bi-msg-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}
.bi-msg-subject {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
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
    color: #64748b;
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
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
}
.bi-msg-action {
    padding: 8px 18px;
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    background: white;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}
.bi-msg-action:hover {
    border-color: #f59e0b;
    color: #d97706;
    background: #fffbeb;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(245,158,11,0.15);
}

/* Alert */
.bi-alert {
    padding: 14px 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #dcfce7, #d1fae5);
    color: #166534;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 2px solid #bbf7d0;
}

/* Empty State */
.bi-empty {
    text-align: center;
    padding: 70px 20px;
}
.bi-empty-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    font-size: 40px;
    margin: 0 auto 18px;
    box-shadow: 0 6px 20px rgba(245,158,11,0.15);
}

/* Modal */
.bi-modal .modal-content {
    border-radius: 18px;
    overflow: hidden;
    border: none;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}
.bi-modal-header {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    padding: 20px 24px;
}
.bi-modal-header h5 { font-weight: 700; margin: 0; color: white; }
.bi-modal-header .btn-close { filter: brightness(0) invert(1); }
.bi-modal-body { padding: 24px; }
.bi-modal-meta {
    display: flex; gap: 16px; flex-wrap: wrap;
    padding: 14px 18px;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 16px;
}
.bi-modal-content {
    padding: 18px;
    background: #f8fafc;
    border-radius: 12px;
    line-height: 1.8;
    color: #334155;
}
/* تنسيق HTML المحرر */
.bi-modal-content img { max-width: 100%; border-radius: 8px; margin: 6px 0; height: auto; }
.bi-modal-content a { color: #3b82f6; text-decoration: underline; }
.bi-modal-content p { margin-bottom: 8px; }
.bi-modal-footer {
    padding: 16px 24px;
    border-top: 2px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.bi-modal-btn {
    padding: 10px 22px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.bi-modal-btn-close { background: #f1f5f9; color: #475569; }
.bi-modal-btn-close:hover { background: #e2e8f0; }
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
                <span style="padding: 5px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e;">
                    {{ $unreadCount }} غير مقروءة
                </span>
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
                    <div class="bi-modal-backdrop" id="messageModal{{ $recipient->id }}" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:1050; align-items:center; justify-content:center; padding:20px;">
                        <div style="background:white; border-radius:16px; max-width:720px; width:100%; max-height:90vh; overflow:auto; box-shadow:0 25px 50px rgba(0,0,0,0.25);">
                            <div class="modal-content">
                                <div class="bi-modal-header" style="display:flex; justify-content:space-between; align-items:center; padding:18px 24px; border-bottom:1px solid #e2e8f0;">
                                    <h5 style="margin:0;">📨 {{ $message->subject }}</h5>
                                    <button type="button" onclick="biCloseModal({{ $recipient->id }})" style="background:transparent; border:none; font-size:22px; cursor:pointer; color:#64748b;">✕</button>
                                </div>
                                <div class="bi-modal-body">
                                    <div class="bi-modal-meta">
                                        <div>
                                            <small style="color: #94a3b8;">المرسل</small><br>
                                            <strong style="color: #1e293b;">{{ $message->sender->name ?? 'غير محدد' }}</strong>
                                        </div>
                                        <div>
                                            <small style="color: #94a3b8;">تاريخ الإرسال</small><br>
                                            <strong style="color: #1e293b;">{{ $message->sent_at->format('Y-m-d H:i') }}</strong>
                                        </div>
                                        @if($isRead)
                                        <div>
                                            <small style="color: #94a3b8;">تم القراءة</small><br>
                                            <strong style="color: #10b981;">{{ $recipient->read_at->format('Y-m-d H:i') }}</strong>
                                        </div>
                                        @endif
                                    </div>
                                    <h6 style="font-weight: 700; color: #475569; margin-bottom: 10px;">📝 نص الرسالة</h6>
                                    <div class="bi-modal-content">{!! safe_html($message->message) !!}</div>
                                </div>
                                <div class="bi-modal-footer" style="padding:14px 24px; border-top:1px solid #e2e8f0; text-align:left;">
                                    <button type="button" onclick="biCloseModal({{ $recipient->id }})" style="background:#f1f5f9; border:none; padding:10px 22px; border-radius:10px; font-weight:700; cursor:pointer; color:#475569;">
                                        إغلاق
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Pagination -->
                <div style="padding: 20px; border-top: 2px solid #f1f5f9;">
                    {{ $messages->links() }}
                </div>
            @else
                <div class="bi-empty">
                    <div class="bi-empty-icon">📬</div>
                    <h3 style="font-size: 20px; font-weight: 700; color: #475569; margin: 0 0 6px;">لا توجد رسائل في صندوق الوارد</h3>
                    <p style="color: #94a3b8; margin: 0; font-size: 14px;">ستظهر هنا الرسائل الجماعية المرسلة إليك</p>
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
