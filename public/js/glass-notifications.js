/**
 * Glass Notification System
 * نظام إشعارات زجاجي فاخر
 */

class GlassNotification {
    constructor() {
        this.activeToasts = [];
        this.init();
    }

    init() {
        // Create overlay if not exists
        if (!document.getElementById('glassNotificationOverlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'glassNotificationOverlay';
            overlay.className = 'glass-notification-overlay';
            document.body.appendChild(overlay);
        }
    }

    /**
     * Show Modal Notification
     * @param {Object} options - Configuration options
     */
    show(options = {}) {
        const defaults = {
            type: 'info', // success, error, warning, info, question
            title: 'إشعار',
            message: '',
            icon: null, // Custom icon (emoji or FontAwesome class)
            buttons: [
                { text: 'حسناً', type: 'primary', action: 'close' }
            ],
            closable: true,
            onClose: null
        };

        const config = { ...defaults, ...options };

        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'glass-notification';
        notification.innerHTML = this.buildModalHTML(config);

        // Add to body
        document.body.appendChild(notification);

        // Show overlay
        const overlay = document.getElementById('glassNotificationOverlay');
        setTimeout(() => overlay.classList.add('active'), 10);

        // Show notification
        setTimeout(() => notification.classList.add('active'), 10);

        // Handle buttons
        const buttons = notification.querySelectorAll('.glass-notification-button');
        buttons.forEach((btn, index) => {
            btn.addEventListener('click', () => {
                const buttonConfig = config.buttons[index];
                if (buttonConfig.callback && typeof buttonConfig.callback === 'function') {
                    buttonConfig.callback();
                }
                if (buttonConfig.action === 'close') {
                    this.close(notification);
                }
            });
        });

        // Handle close button
        if (config.closable) {
            const closeBtn = notification.querySelector('.glass-notification-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.close(notification));
            }
        }

        // Handle overlay click
        overlay.addEventListener('click', () => {
            if (config.closable) {
                this.close(notification);
            }
        });

        return notification;
    }

    /**
     * Close Modal Notification
     */
    close(notification) {
        const overlay = document.getElementById('glassNotificationOverlay');

        notification.classList.remove('active');
        overlay.classList.remove('active');

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 400);
    }

    /**
     * Build Modal HTML
     */
    buildModalHTML(config) {
        const icon = this.getIcon(config.type, config.icon);
        const closeButton = config.closable ? '<button class="glass-notification-close">✕</button>' : '';

        const buttonsHTML = config.buttons.map(btn => {
            const buttonClass = btn.type || 'primary';
            return `<button class="glass-notification-button ${buttonClass}"><span>${btn.text}</span></button>`;
        }).join('');

        return `
            ${closeButton}
            <div class="glass-notification-header">
                <div class="glass-notification-icon ${config.type}">
                    ${icon}
                </div>
                <h3 class="glass-notification-title">${config.title}</h3>
                <p class="glass-notification-message">${config.message}</p>
            </div>
            <div class="glass-notification-content">
                <div class="glass-notification-buttons">
                    ${buttonsHTML}
                </div>
            </div>
        `;
    }

    /**
     * Show Toast Notification
     */
    toast(options = {}) {
        const defaults = {
            type: 'info',
            title: '',
            message: '',
            duration: 5000,
            closable: true,
            position: 'top-right' // top-right, top-left, bottom-right, bottom-left
        };

        const config = { ...defaults, ...options };

        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'glass-toast';
        toast.innerHTML = this.buildToastHTML(config);

        // Set position
        this.setToastPosition(toast, config.position);

        // Add to body
        document.body.appendChild(toast);

        // Show toast
        setTimeout(() => toast.classList.add('active'), 10);

        // Add to active toasts
        this.activeToasts.push(toast);

        // Handle close button
        if (config.closable) {
            const closeBtn = toast.querySelector('.glass-toast-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.closeToast(toast));
            }
        }

        // Auto close
        if (config.duration > 0) {
            // Animate progress bar
            const progressBar = toast.querySelector('.glass-toast-progress');
            if (progressBar) {
                progressBar.style.width = '100%';
                setTimeout(() => {
                    progressBar.style.transition = `width ${config.duration}ms linear`;
                    progressBar.style.width = '0%';
                }, 10);
            }

            setTimeout(() => this.closeToast(toast), config.duration);
        }

        return toast;
    }

    /**
     * Close Toast Notification
     */
    closeToast(toast) {
        toast.classList.remove('active');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            // Remove from active toasts
            const index = this.activeToasts.indexOf(toast);
            if (index > -1) {
                this.activeToasts.splice(index, 1);
            }
        }, 400);
    }

    /**
     * Build Toast HTML
     */
    buildToastHTML(config) {
        const icon = this.getIcon(config.type);
        const closeButton = config.closable ? '<button class="glass-toast-close">✕</button>' : '';
        const title = config.title ? `<div class="glass-toast-title">${config.title}</div>` : '';

        return `
            <div class="glass-toast-content">
                <div class="glass-toast-icon ${config.type}">
                    ${icon}
                </div>
                <div class="glass-toast-text">
                    ${title}
                    <div class="glass-toast-message">${config.message}</div>
                </div>
                ${closeButton}
            </div>
            ${config.duration > 0 ? '<div class="glass-toast-progress"></div>' : ''}
        `;
    }

    /**
     * Set Toast Position
     */
    setToastPosition(toast, position) {
        const positions = {
            'top-right': { top: '20px', right: '20px' },
            'top-left': { top: '20px', left: '20px' },
            'bottom-right': { bottom: '20px', right: '20px' },
            'bottom-left': { bottom: '20px', left: '20px' }
        };

        const pos = positions[position] || positions['top-right'];
        Object.assign(toast.style, pos);
    }

    /**
     * Get Icon based on type
     */
    getIcon(type, customIcon = null) {
        if (customIcon) {
            // If it's an emoji
            if (/\p{Emoji}/u.test(customIcon)) {
                return customIcon;
            }
            // If it's a FontAwesome class
            return `<i class="${customIcon}"></i>`;
        }

        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ',
            question: '?'
        };

        return icons[type] || icons.info;
    }

    /**
     * Quick Methods
     */
    success(title, message, options = {}) {
        return this.show({
            type: 'success',
            title: title,
            message: message,
            ...options
        });
    }

    error(title, message, options = {}) {
        return this.show({
            type: 'error',
            title: title,
            message: message,
            ...options
        });
    }

    warning(title, message, options = {}) {
        return this.show({
            type: 'warning',
            title: title,
            message: message,
            ...options
        });
    }

    info(title, message, options = {}) {
        return this.show({
            type: 'info',
            title: title,
            message: message,
            ...options
        });
    }

    confirm(title, message, onConfirm, options = {}) {
        return this.show({
            type: 'question',
            title: title,
            message: message,
            buttons: [
                {
                    text: options.cancelText || 'إلغاء',
                    type: 'secondary',
                    action: 'close'
                },
                {
                    text: options.confirmText || 'تأكيد',
                    type: options.confirmType || 'primary',
                    callback: onConfirm,
                    action: 'close'
                }
            ],
            ...options
        });
    }

    /**
     * Toast Quick Methods
     */
    toastSuccess(message, title = 'نجح!') {
        return this.toast({
            type: 'success',
            title: title,
            message: message
        });
    }

    toastError(message, title = 'خطأ!') {
        return this.toast({
            type: 'error',
            title: title,
            message: message
        });
    }

    toastWarning(message, title = 'تنبيه!') {
        return this.toast({
            type: 'warning',
            title: title,
            message: message
        });
    }

    toastInfo(message, title = '') {
        return this.toast({
            type: 'info',
            title: title,
            message: message
        });
    }
}

// Initialize global instance
window.glassNotify = new GlassNotification();

// Store original functions
window._originalAlert = window.alert;
window._originalConfirm = window.confirm;

// Override native alert — show Arabic glass notification instead of English browser dialog
window.alert = function (message) {
    window.glassNotify.info('تنبيه', message);
};

// Override native confirm for async usage (helper)
window.glassAlert = function (message) {
    window.glassNotify.info('تنبيه', message);
};

window.glassConfirm = function (message, onConfirm) {
    return window.glassNotify.confirm('تأكيد', message, onConfirm);
};

// Auto-convert form onsubmit="return confirm(...)" to Arabic glass notifications
document.addEventListener('DOMContentLoaded', function () {
    // Find all forms that use native confirm() in onsubmit
    document.querySelectorAll('form[onsubmit]').forEach(function (form) {
        var onsubmitAttr = form.getAttribute('onsubmit');
        if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
            // Extract the Arabic message from confirm('...')
            var match = onsubmitAttr.match(/confirm\(['\"]([^'"]+)['"]\)/);
            if (match) {
                var confirmMessage = match[1];
                // Remove the original onsubmit
                form.removeAttribute('onsubmit');
                // Add new submit handler with Arabic glass confirm
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var currentForm = this;
                    window.glassNotify.confirm('تأكيد', confirmMessage, function () {
                        // Re-submit without triggering this handler again
                        var tempSubmit = document.createElement('input');
                        tempSubmit.type = 'hidden';
                        tempSubmit.name = '_glass_confirmed';
                        tempSubmit.value = '1';
                        currentForm.appendChild(tempSubmit);
                        currentForm.submit();
                    }, {
                        confirmText: 'نعم، متأكد',
                        cancelText: 'إلغاء',
                        confirmType: 'danger'
                    });
                });
            }
        }
    });

    // Auto-convert onclick="if(confirm(...))" on buttons/links
    document.querySelectorAll('[onclick]').forEach(function (el) {
        var onclickAttr = el.getAttribute('onclick');
        if (onclickAttr && onclickAttr.includes('confirm(') && !onclickAttr.includes('glassNotify')) {
            var match = onclickAttr.match(/confirm\(['\"]([^'"]+)['"]\)/);
            if (match) {
                var confirmMessage = match[1];
                // Get the action code after confirm succeeds
                var actionCode = onclickAttr.replace(/if\s*\(\s*confirm\([^)]+\)\s*\)\s*/, '');
                if (actionCode === onclickAttr) {
                    // Pattern: onclick="return confirm('...')"
                    return;
                }
                el.removeAttribute('onclick');
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.glassNotify.confirm('تأكيد', confirmMessage, function () {
                        try { eval(actionCode); } catch (err) { }
                    }, {
                        confirmText: 'نعم',
                        cancelText: 'إلغاء',
                        confirmType: 'danger'
                    });
                });
            }
        }
    });
});
