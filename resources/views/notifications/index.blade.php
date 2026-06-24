@extends($layout)

@section('title', 'الإشعارات')

@push('styles')
<style>
    .notifications-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 24px 16px;
        padding-bottom: 120px;
        direction: rtl;
    }
    
    .notifications-header {
        text-align: center;
        margin-bottom: 32px;
        padding: 40px;
        border-radius: 24px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(99, 102, 241, 0.3);
    }
    .notifications-header::before {
        content: '';
        position: absolute;
        top: -60%; left: -20%;
        width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
        border-radius: 50%;
    }
    
    .notifications-title {
        font-size: 32px;
        font-weight: 800;
        color: white;
        margin-bottom: 8px;
        position: relative;
        z-index: 1;
    }
    
    .notifications-subtitle {
        font-size: 15px;
        color: rgba(255, 255, 255, 0.85);
        position: relative;
        z-index: 1;
    }
    
    .notifications-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-bottom: 24px;
    }
    
    .action-btn {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        padding: 12px 28px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        border: none;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
    }
    
    .notification-item {
        background: white;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 18px;
        padding: 20px 24px;
        margin-bottom: 12px;
        transition: all 0.3s;
        cursor: pointer;
        position: relative;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    
    .notification-item.unread {
        border-right: 4px solid #6366f1;
        background: linear-gradient(135deg, #f5f3ff, #ede9fe);
    }
    
    .notification-item:hover {
        transform: translateX(-4px);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.12);
    }
    
    .notification-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 4px;
    }
    
    .notification-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
        background: linear-gradient(135deg, #667eea, #764ba2);
    }
    
    .notification-content { flex: 1; }
    
    .notification-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }
    
    .notification-message {
        font-size: 14px;
        color: #64748b;
        line-height: 1.6;
    }
    
    .notification-time {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 6px;
    }
    
    .notification-actions {
        display: flex;
        gap: 6px;
        position: absolute;
        top: 16px;
        left: 16px;
    }
    
    .notification-action {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: #f1f5f9;
        border: none;
        color: #64748b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        font-size: 16px;
    }
    
    .notification-action:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 24px;
        background: white;
        border-radius: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    
    .empty-state-icon {
        font-size: 72px;
        margin-bottom: 16px;
    }
    
    .empty-state h3 { color: #1e293b; font-size: 20px; font-weight: 700; margin-bottom: 8px; }
    .empty-state p { color: #64748b; font-size: 15px; }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .notification-item { animation: slideIn 0.3s ease-out; }

    @media (max-width: 768px) {
        .notifications-header { padding: 28px 20px; }
        .notifications-title { font-size: 24px; }
        .notification-item { padding: 16px; }
    }
</style>
@endpush

@section('content')
<div class="notifications-container fade-in">
    <!-- Header -->
    <div class="notifications-header slide-up">
        <h1 class="notifications-title">🔔 الإشعارات</h1>
        <p class="notifications-subtitle">تابع آخر التحديثات والإنجازات</p>
    </div>

    <!-- Actions -->
    @if($notifications->count() > 0)
    <div class="notifications-actions">
        <button class="action-btn" onclick="markAllAsRead()">
            ✅ تحديد الكل كمقروء
        </button>
    </div>
    @endif

    <!-- Notifications List -->
    <div class="notifications-list">
        @forelse($notifications as $notification)
        <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}" 
             data-id="{{ $notification->id }}"
             onclick="handleNotificationClick('{{ $notification->id }}', '{{ $notification->action_url }}')">
            
            <div class="notification-header">
                <div class="notification-icon {{ $notification->type }}">
                    @if($notification->type == 'activity_completed')
                        🎉
                    @elseif($notification->type == 'badge_earned')
                        🏅
                    @elseif($notification->type == 'level_up')
                        ⬆️
                    @elseif($notification->type == 'streak_milestone')
                        🔥
                    @elseif($notification->type == 'activity_graded')
                        ✅
                    @else
                        🔔
                    @endif
                </div>
                
                <div class="notification-content">
                    <div class="notification-title">{{ $notification->title }}</div>
                    <div class="notification-message">{{ html_excerpt($notification->message, 500) }}</div>
                    <div class="notification-time">{{ $notification->created_at->diffForHumans() }}</div>
                </div>
            </div>

            <div class="notification-actions" onclick="event.stopPropagation()">
                @if(!$notification->read_at)
                <button class="notification-action" onclick="markAsRead('{{ $notification->id }}')" title="تحديد كمقروء">
                    ✓
                </button>
                @endif
                <button class="notification-action" onclick="deleteNotification('{{ $notification->id }}')" title="حذف">
                    ×
                </button>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-state-icon">🔕</div>
            <h3>لا توجد إشعارات</h3>
            <p>ستظهر هنا جميع إشعاراتك وتحديثاتك</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
    <div style="margin-top: var(--spacing-xl);">
        {{ $notifications->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
function handleNotificationClick(id, actionUrl) {
    // تحديد كمقروء
    markAsRead(id);
    
    // الانتقال للرابط إن وجد
    if (actionUrl) {
        setTimeout(() => {
            window.location.href = actionUrl;
        }, 200);
    }
}

function markAsRead(id) {
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-id="${id}"]`);
            if (item) {
                item.classList.remove('unread');
                const checkBtn = item.querySelector('.notification-action[title="تحديد كمقروء"]');
                if (checkBtn) checkBtn.remove();
            }
        }
    });
}

function markAllAsRead() {
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function deleteNotification(id) {
    if (!confirm('هل تريد حذف هذا الإشعار؟')) return;
    
    fetch(`/notifications/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-id="${id}"]`);
            if (item) {
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
            }
        }
    });
}
</script>
@endpush
@endsection
