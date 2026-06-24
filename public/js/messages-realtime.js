// Real-Time Messages System
class MessagesRealTime {
    constructor() {
        this.pollingInterval = null;
        this.userIdCheckInterval = null;
        this.pollingDelay = 5000; // 5 seconds
        this.lastNotificationTime = Date.now();
        this.notificationSound = null;
        this.isInMessagesPage = window.location.pathname.includes('/messages');
        this.currentConversationUserId = null;

        // === Fix #24: تتبع المحادثات المُعلَم عنها لمنع التكرار ===
        this.notifiedConversations = new Set();

        // === Fix #24: دعم كتم الإشعارات ===
        this.isMuted = localStorage.getItem('messages_muted') === 'true';

        this.init();
    }

    init() {
        console.log('🚀 Messages Real-Time System initialized');

        // تهيئة صوت الإشعار
        this.initNotificationSound();

        // بدء المراقبة
        this.startPolling();

        // إيقاف المراقبة عند مغادرة الصفحة
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                console.log('⏸️ Tab hidden - polling stopped');
                this.stopPolling();
            } else {
                console.log('▶️ Tab visible - polling started');
                this.startPolling();
            }
        });

        // إذا كنا في صفحة محادثة، احفظ الـ userId
        this.detectCurrentConversation();

        // تحديث أيقونة الكتم عند البدء
        this.updateMuteUI();

        console.log('📍 Current conversation user:', this.currentConversationUserId);
        console.log('🔇 Muted:', this.isMuted);
    }

    initNotificationSound() {
        // إنشاء صوت بسيط باستخدام Web Audio API
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            this.audioContext = new AudioContext();
        } catch (e) {
            console.log('Web Audio API not supported');
        }
    }

    playNotificationSound() {
        // === Fix #24: لا تشغل الصوت إذا كان مكتوماً ===
        if (this.isMuted) return;
        if (!this.audioContext) return;

        try {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.5);

            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.5);
        } catch (e) {
            console.log('Could not play sound');
        }
    }

    detectCurrentConversation() {
        // استخراج userId من الـ URL أو من المتغير الموجود في الصفحة
        const pathParts = window.location.pathname.split('/');
        const messagesIndex = pathParts.indexOf('messages');
        if (messagesIndex !== -1 && pathParts[messagesIndex + 1]) {
            this.currentConversationUserId = pathParts[messagesIndex + 1];
        }

        // أو من المتغير العام في صفحة المحادثة
        if (window.currentChatUserId) {
            this.currentConversationUserId = window.currentChatUserId;
        }

        // مراقبة التغيير في currentChatUserId — مع تنظيف لمنع memory leak
        if (this.userIdCheckInterval) {
            clearInterval(this.userIdCheckInterval);
        }
        const checkUserId = () => {
            if (window.currentChatUserId && window.currentChatUserId !== this.currentConversationUserId) {
                this.currentConversationUserId = window.currentChatUserId;
            }
        };
        this.userIdCheckInterval = setInterval(checkUserId, 1000);

        // إيقاف عند إخفاء التبويب / مغادرة الصفحة
        if (!this._pageHideHandlerBound) {
            this._pageHideHandlerBound = true;
            window.addEventListener('pagehide', () => {
                if (this.userIdCheckInterval) {
                    clearInterval(this.userIdCheckInterval);
                    this.userIdCheckInterval = null;
                }
                this.stopPolling();
            });
        }
    }

    startPolling() {
        if (this.pollingInterval) return;
        console.log('✅ Polling started - checking every 5 seconds');
        // فحص فوري
        this.checkForNewMessages();

        // ثم فحص دوري
        this.pollingInterval = setInterval(() => {
            this.checkForNewMessages();
        }, this.pollingDelay);
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }

    async checkForNewMessages() {
        try {
            console.log('🔍 Checking for new messages...');

            // إذا كنا في صفحة محادثة محددة، تحقق من رسائل جديدة في تلك المحادثة
            if (this.currentConversationUserId) {
                console.log('💬 Checking conversation:', this.currentConversationUserId);
                await this.checkConversationMessages();
            }

            // دائماً تحقق من جميع الرسائل الجديدة لتحديث العداد
            await this.checkAllMessages();

        } catch (error) {
            console.error('❌ Error checking messages:', error);
        }
    }

    async checkConversationMessages() {
        if (!this.currentConversationUserId) return;

        try {
            const isSchoolAdmin = window.location.pathname.includes('/school-admin/');
            const baseUrl = isSchoolAdmin ? '/school-admin' : '';

            console.log(`📨 Fetching: ${baseUrl}/messages/check-new/${this.currentConversationUserId}`);

            const response = await fetch(`${baseUrl}/messages/check-new/${this.currentConversationUserId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                console.error('❌ Response not OK:', response.status);
                return;
            }

            const data = await response.json();
            console.log('📬 Conversation check result:', data);

            if (data.hasNew && data.messages.length > 0) {
                console.log(`✉️ ${data.messages.length} new message(s) found!`);

                // إذا كانت المحادثة مفتوحة في single-page chat
                if (window.currentChatUserId && window.currentChatUserId == this.currentConversationUserId) {
                    console.log('💬 Adding messages to open chat');
                    this.appendNewMessagesToChat(data.messages);
                } else {
                    console.log('📄 Adding messages to conversation page');
                    this.appendNewMessages(data.messages);
                }
                this.playNotificationSound();
            } else {
                console.log('✅ No new messages in this conversation');
            }
        } catch (error) {
            console.error('❌ Error checking conversation messages:', error);
        }
    }

    appendNewMessagesToChat(messages) {
        const messagesContainer = document.getElementById('messagesContainer') ||
            document.querySelector('.chat-messages-container');
        if (!messagesContainer) {
            // إذا لم يكن موجود، استخدم الطريقة العادية
            this.appendNewMessages(messages);
            return;
        }

        console.log(`💬 Adding ${messages.length} message(s) to chat interface`);

        messages.forEach(message => {
            // تحقق من عدم وجود الرسالة مسبقاً
            if (document.querySelector(`[data-message-id="${message.id}"]`)) {
                console.log(`⚠️ Message ${message.id} already exists`);
                return;
            }

            const isSent = message.sender_id == (window.currentUserId || document.body.dataset.userId);
            const messageHtml = window.createMessageElement ?
                window.createMessageElement(message, isSent) :
                this.createSimpleMessageElement(message, isSent);

            messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
            console.log(`✅ Message ${message.id} added to chat`);
        });

        // Scroll to bottom
        if (window.scrollToBottom) {
            window.scrollToBottom();
        } else {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        console.log('✅ Scrolled to bottom');
    }

    createSimpleMessageElement(message, isSent) {
        const time = new Date(message.created_at).toLocaleTimeString('ar-SA', {
            hour: '2-digit',
            minute: '2-digit'
        });

        return `
            <div class="message ${isSent ? 'sent' : 'received'}" data-message-id="${message.id}">
                <div class="message-bubble">
                    <div class="message-text">${this.escapeHtml(message.message)}</div>
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async checkAllMessages() {
        try {
            const isSchoolAdmin = window.location.pathname.includes('/school-admin/');
            const baseUrl = isSchoolAdmin ? '/school-admin' : '';

            const response = await fetch(`${baseUrl}/messages/check-all/new`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            // تحديث عداد الرسائل في الـ sidebar (دائماً يتم حتى لو مكتوم)
            this.updateUnreadBadge(data.total);

            // === فلترة الإشعارات المكررة بناءً على message_id ===
            if (data.hasNew && data.notifications.length > 0) {
                // فلترة الرسائل التي تم عرض إشعار عنها مسبقاً
                const newNotifications = data.notifications.filter(n => {
                    const key = String(n.message_id || n.conversation_id || (n.sender && n.sender.id) || '');
                    return key && !this.notifiedConversations.has(key);
                });

                if (newNotifications.length > 0) {
                    // تسجيل الرسائل كمُعلَم عنها
                    newNotifications.forEach(n => {
                        const key = String(n.message_id || n.conversation_id || (n.sender && n.sender.id) || '');
                        if (key) this.notifiedConversations.add(key);
                    });

                    // منع تضخم الذاكرة — الاحتفاظ بآخر 500 فقط
                    if (this.notifiedConversations.size > 500) {
                        const arr = [...this.notifiedConversations];
                        this.notifiedConversations = new Set(arr.slice(-200));
                    }

                    // تشغيل الصوت (مرة واحدة فقط، وفقط إذا غير مكتوم)
                    if (!this.isMuted && Date.now() - this.lastNotificationTime > 5000) {
                        this.playNotificationSound();
                        this.lastNotificationTime = Date.now();
                    }

                    // إظهار Toast Notification (فقط إذا غير مكتوم ولم نكن في صفحة محادثة)
                    if (!this.isMuted && !this.currentConversationUserId) {
                        this.showToastNotification(newNotifications[0]);
                    }
                }
            }
        } catch (error) {
            console.error('❌ Error checking all messages:', error);
        }
    }

    appendNewMessages(messages) {
        const messagesContainer = document.getElementById('messagesContainer');
        if (!messagesContainer) return;

        messages.forEach(message => {
            const messageHtml = this.createMessageElement(message);
            messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
        });

        // التمرير لأسفل
        this.scrollToBottom();
    }

    createMessageElement(message) {
        const time = new Date(message.created_at).toLocaleTimeString('ar-SA', {
            hour: '2-digit',
            minute: '2-digit'
        });

        return `
            <div class="message received" style="animation: slideIn 0.3s ease;">
                <div class="message-bubble">
                    ${this.escapeHtml(message.message)}
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('messagesContainer');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    updateUnreadBadge(count) {
        // تحديث جميع الـ badges في الصفحة
        const badges = document.querySelectorAll('.nav-badge, .unread-count');

        badges.forEach(badge => {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';

                // إضافة تأثير نبض
                badge.style.animation = 'pulse 0.5s ease';
                setTimeout(() => {
                    badge.style.animation = '';
                }, 500);
            } else {
                badge.textContent = '';
                badge.style.display = 'none';
            }
        });
    }

    showToastNotification(notification) {
        // === Fix #24: لا تعرض الإشعار إذا كان مكتوماً ===
        if (this.isMuted) return;

        // التحقق من وجود container للإشعارات
        let toastContainer = document.getElementById('toast-notifications');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-notifications';
            toastContainer.style.cssText = `
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            border-right: 4px solid #667eea;
            animation: slideInLeft 0.3s ease;
            cursor: pointer;
            transition: all 0.3s ease;
        `;

        toast.innerHTML = `
            <div style="display: flex; align-items: start; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px; flex-shrink: 0;">
                    ${notification.sender.name.substring(0, 2)}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; color: #1e293b; margin-bottom: 4px; font-size: 14px;">
                        ${this.escapeHtml(notification.sender.name)}
                        ${notification.count > 1 ? `<span style="background: #667eea; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-right: 6px;">${notification.count}</span>` : ''}
                    </div>
                    <div style="color: #64748b; font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        ${this.escapeHtml(notification.message).substring(0, 60)}${notification.message.length > 60 ? '...' : ''}
                    </div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 18px; padding: 0; width: 24px; height: 24px; flex-shrink: 0;">×</button>
            </div>
        `;

        // عند النقر على الإشعار، الذهاب للمحادثة
        toast.addEventListener('click', (e) => {
            if (e.target.tagName !== 'BUTTON') {
                const isSchoolAdmin = window.location.pathname.includes('/school-admin/');
                const baseUrl = isSchoolAdmin ? '/school-admin' : '';

                // إذا كنا في صفحة الرسائل وتوجد دالة loadConversation، استخدمها
                if (window.location.pathname.includes('/messages') && typeof window.loadConversation === 'function') {
                    console.log('🔗 Opening conversation in single-page chat:', notification.sender.id);
                    window.loadConversation(notification.sender.id, notification.sender.name);
                    toast.remove();
                } else if (window.location.pathname.includes('/messages') && typeof loadConversation === 'function') {
                    console.log('🔗 Opening conversation in single-page chat:', notification.sender.id);
                    loadConversation(notification.sender.id, notification.sender.name);
                    toast.remove();
                } else {
                    // انتقل لصفحة المحادثات
                    window.location.href = `${baseUrl}/messages/${notification.sender.id}`;
                }
            }
        });

        toast.addEventListener('mouseenter', () => {
            toast.style.transform = 'translateX(-5px)';
        });

        toast.addEventListener('mouseleave', () => {
            toast.style.transform = 'translateX(0)';
        });

        toastContainer.appendChild(toast);

        // إزالة الإشعار تلقائياً بعد 7 ثواني
        setTimeout(() => {
            toast.style.animation = 'slideOutLeft 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 7000);
    }

    // === Fix #24: API عام للكتم/إلغاء الكتم ===
    toggleMute() {
        this.isMuted = !this.isMuted;
        localStorage.setItem('messages_muted', this.isMuted);
        this.updateMuteUI();
        console.log('🔇 Mute toggled:', this.isMuted);
        return this.isMuted;
    }

    isSoundMuted() {
        return this.isMuted;
    }

    updateMuteUI() {
        // تحديث أيقونة ونص زر الكتم داخل لوحة الإشعارات
        const muteBtn = document.getElementById('muteNotificationsBtn');
        if (muteBtn) {
            muteBtn.style.background = this.isMuted ? 'rgba(245,101,101,0.3)' : 'rgba(255,255,255,0.2)';
        }
        const muteIcon = document.getElementById('muteIconInPanel');
        if (muteIcon) {
            muteIcon.className = this.isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
        }
        const muteText = document.getElementById('muteTextInPanel');
        if (muteText) {
            muteText.textContent = this.isMuted ? 'مكتوم' : 'الصوت';
        }
    }
}

// إضافة animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideOutLeft {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(-100%);
        }
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.2);
        }
    }
`;
document.head.appendChild(style);

// تشغيل النظام تلقائياً
document.addEventListener('DOMContentLoaded', () => {
    window.messagesRealTime = new MessagesRealTime();
});
