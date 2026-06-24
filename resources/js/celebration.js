/**
 * @module celebration
 * @description نظام الاحتفالات التفاعلية — يعرض نجوم وألعاب نارية في الواجهة
 *              عند إنجاز الطالب لمهمة (إكمال درس، شارة جديدة، Level Up…).
 *
 * @example
 *   import { showCelebration } from './celebration.js';
 *   showCelebration('level_up', { playSound: false });
 */

/**
 * @typedef {Object} CelebrationConfig
 * @property {number} stars         عدد النجوم المتحركة
 * @property {number} fireworks     عدد الألعاب النارية
 * @property {number} duration      مدة العرض (ms)
 * @property {string} message       الرسالة المعروضة
 * @property {string} color         اللون الأساسي (hex)
 * @property {boolean} [playSound]  تشغيل الصوت (default true)
 */

/**
 * @typedef {'lesson_complete'|'badge_earned'|'level_up'|'activity_complete'|'perfect_score'} CelebrationType
 */

/**
 * عرض احتفال تفاعلي على الشاشة.
 *
 * @param {CelebrationType} [type='lesson_complete']  نوع الاحتفال (يحدد الإعدادات الافتراضية)
 * @param {Partial<CelebrationConfig>} [options={}]    تخصيص إضافي يتجاوز الافتراضي
 * @returns {void}
 *
 * @example
 *   // احتفال level up مع صوت
 *   showCelebration('level_up');
 *
 *   // احتفال بدون صوت
 *   showCelebration('badge_earned', { playSound: false });
 *
 *   // تخصيص كامل
 *   showCelebration('activity_complete', {
 *     stars: 100,
 *     message: 'رائع جداً! 🎉',
 *     duration: 8000
 *   });
 */
export function showCelebration(type = 'lesson_complete', options = {}) {
    const celebrations = {
        lesson_complete: {
            stars: 50,
            fireworks: 5,
            duration: 5000,
            message: 'أحسنت! لقد أكملت الدرس بنجاح! 🎉',
            color: '#4CAF50'
        },
        badge_earned: {
            stars: 30,
            fireworks: 3,
            duration: 4000,
            message: 'مبروك! حصلت على شارة جديدة! 🏆',
            color: '#FFD700'
        },
        level_up: {
            stars: 100,
            fireworks: 10,
            duration: 7000,
            message: 'رائع! ارتقيت إلى مستوى جديد! 🚀',
            color: '#9C27B0'
        },
        activity_complete: {
            stars: 40,
            fireworks: 4,
            duration: 4500,
            message: 'ممتاز! أكملت النشاط بنجاح! ⭐',
            color: '#2196F3'
        },
        perfect_score: {
            stars: 80,
            fireworks: 8,
            duration: 6000,
            message: 'مذهل! حصلت على الدرجة الكاملة! 💯',
            color: '#FF5722'
        }
    };

    const config = { ...celebrations[type], ...options };

    // إنشاء حاوية الاحتفال
    const container = createCelebrationContainer();
    document.body.appendChild(container);

    // إنشاء النجوم
    createStars(container, config.stars, config.color);

    // إنشاء الألعاب النارية
    createFireworks(container, config.fireworks, config.color);

    // عرض الرسالة
    showMessage(container, config.message, config.color);

    // تشغيل الصوت (اختياري)
    if (config.playSound !== false) {
        playSound('celebration');
    }

    // إزالة بعد المدة المحددة
    setTimeout(() => {
        removeCelebration(container);
    }, config.duration);
}

/**
 * إنشاء حاوية الاحتفال
 */
function createCelebrationContainer() {
    const container = document.createElement('div');
    container.className = 'celebration-container';
    container.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 99999;
        overflow: hidden;
    `;
    return container;
}

/**
 * إنشاء النجوم المتحركة
 */
function createStars(container, count, color) {
    const starsContainer = document.createElement('div');
    starsContainer.className = 'celebration-stars';
    
    for (let i = 0; i < count; i++) {
        const star = document.createElement('div');
        star.innerHTML = '⭐';
        
        const size = Math.random() * 30 + 20;
        const startX = Math.random() * 100;
        const startY = Math.random() * 100;
        const endY = startY - (Math.random() * 50 + 50);
        const rotation = Math.random() * 720 - 360;
        const delay = Math.random() * 1000;
        const duration = Math.random() * 2000 + 2000;
        
        star.style.cssText = `
            position: absolute;
            font-size: ${size}px;
            left: ${startX}%;
            top: ${startY}%;
            opacity: 0;
            transform: scale(0) rotate(0deg);
            animation: starFloat ${duration}ms ease-out ${delay}ms forwards;
            filter: drop-shadow(0 0 10px ${color});
        `;
        
        starsContainer.appendChild(star);
    }
    
    container.appendChild(starsContainer);
}

/**
 * إنشاء الألعاب النارية
 */
function createFireworks(container, count, color) {
    const fireworksContainer = document.createElement('div');
    fireworksContainer.className = 'celebration-fireworks';
    
    for (let i = 0; i < count; i++) {
        setTimeout(() => {
            const firework = document.createElement('div');
            firework.className = 'firework';
            
            const x = Math.random() * 80 + 10;
            const y = Math.random() * 50 + 10;
            
            firework.style.cssText = `
                position: absolute;
                left: ${x}%;
                top: ${y}%;
            `;
            
            // إنشاء جزيئات الألعاب النارية
            for (let j = 0; j < 30; j++) {
                const particle = document.createElement('div');
                const angle = (Math.PI * 2 * j) / 30;
                const velocity = Math.random() * 100 + 50;
                
                particle.style.cssText = `
                    position: absolute;
                    width: 4px;
                    height: 4px;
                    background: ${color};
                    border-radius: 50%;
                    box-shadow: 0 0 10px ${color};
                    animation: fireworkParticle 1s ease-out forwards;
                    --angle: ${angle}rad;
                    --velocity: ${velocity}px;
                `;
                
                firework.appendChild(particle);
            }
            
            fireworksContainer.appendChild(firework);
            
            setTimeout(() => firework.remove(), 1500);
        }, i * 500);
    }
    
    container.appendChild(fireworksContainer);
}

/**
 * عرض رسالة الاحتفال
 */
function showMessage(container, message, color) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'celebration-message';
    messageDiv.textContent = message;
    
    messageDiv.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        background: linear-gradient(135deg, ${color} 0%, ${adjustColor(color, -20)} 100%);
        color: white;
        padding: 30px 50px;
        border-radius: 20px;
        font-size: 28px;
        font-weight: bold;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        z-index: 100000;
        animation: messagePopIn 0.5s ease-out forwards;
        pointer-events: auto;
        max-width: 80%;
    `;
    
    container.appendChild(messageDiv);
}

/**
 * إزالة الاحتفال
 */
function removeCelebration(container) {
    container.style.animation = 'fadeOut 0.5s ease-out forwards';
    setTimeout(() => {
        container.remove();
    }, 500);
}

/**
 * تشغيل الصوت
 */
function playSound(soundName) {
    try {
        const audio = new Audio(`/sounds/${soundName}.mp3`);
        audio.volume = 0.5;
        audio.play().catch(() => {
            // تجاهل الأخطاء إذا كان الصوت غير متاح
        });
    } catch (e) {
        // تجاهل الأخطاء
    }
}

/**
 * تعديل لون
 */
function adjustColor(color, amount) {
    const num = parseInt(color.replace('#', ''), 16);
    const r = Math.max(0, Math.min(255, (num >> 16) + amount));
    const g = Math.max(0, Math.min(255, ((num >> 8) & 0x00FF) + amount));
    const b = Math.max(0, Math.min(255, (num & 0x0000FF) + amount));
    return `#${((r << 16) | (g << 8) | b).toString(16).padStart(6, '0')}`;
}

// إضافة الأنماط CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes starFloat {
        0% {
            opacity: 0;
            transform: translateY(0) scale(0) rotate(0deg);
        }
        20% {
            opacity: 1;
        }
        80% {
            opacity: 1;
        }
        100% {
            opacity: 0;
            transform: translateY(-200px) scale(1.5) rotate(720deg);
        }
    }

    @keyframes fireworkParticle {
        0% {
            transform: translate(0, 0);
            opacity: 1;
        }
        100% {
            transform: translate(
                calc(cos(var(--angle)) * var(--velocity)),
                calc(sin(var(--angle)) * var(--velocity))
            );
            opacity: 0;
        }
    }

    @keyframes messagePopIn {
        0% {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.5);
        }
        50% {
            transform: translate(-50%, -50%) scale(1.1);
        }
        100% {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// تصدير الدالة للاستخدام العام
window.showCelebration = showCelebration;
