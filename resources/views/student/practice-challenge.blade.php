@extends('layouts.student-app')

@section('title', 'تحدي')

@push('styles')
<style>
    .challenge-container {
        max-width: 800px;
        margin: 0 auto;
        padding: var(--spacing-xl) var(--spacing-md);
        padding-bottom: 120px;
    }
    
    .challenge-header {
        background: var(--glass-bg-heavy);
        border: 2px solid;
        border-image: linear-gradient(135deg, #F59E0B, #EF4444) 1;
        border-radius: var(--radius-2xl);
        padding: var(--spacing-2xl);
        text-align: center;
        margin-bottom: var(--spacing-xl);
    }
    
    .challenge-icon {
        font-size: 80px;
        margin-bottom: var(--spacing-md);
        animation: bounce 2s ease-in-out infinite;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    .challenge-title {
        font-size: 32px;
        font-weight: 800;
        background: linear-gradient(135deg, #F59E0B, #EF4444);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: var(--spacing-sm);
    }
    
    .challenge-subtitle {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.7);
    }
    
    .timer-box {
        background: var(--glass-bg-medium);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        text-align: center;
        margin-bottom: var(--spacing-xl);
    }
    
    .timer-display {
        font-size: 48px;
        font-weight: 800;
        color: #F59E0B;
        font-family: 'Courier New', monospace;
    }
    
    .timer-label {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.6);
        margin-top: var(--spacing-sm);
    }
    
    .challenge-question {
        background: var(--glass-bg-heavy);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-2xl);
        padding: var(--spacing-2xl);
        margin-bottom: var(--spacing-lg);
    }
    
    .question-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg);
    }
    
    .question-num {
        background: linear-gradient(135deg, #F59E0B, #EF4444);
        color: white;
        padding: 8px 20px;
        border-radius: var(--radius-full);
        font-weight: 700;
    }
    
    .question-points {
        background: rgba(251, 191, 36, 0.2);
        color: #FBBF24;
        padding: 8px 16px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 14px;
    }
    
    .question-text {
        font-size: 20px;
        font-weight: 700;
        color: white;
        margin-bottom: var(--spacing-xl);
        text-align: right;
    }
    
    .challenge-option {
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-xl);
        padding: var(--spacing-md) var(--spacing-lg);
        margin-bottom: var(--spacing-sm);
        cursor: pointer;
        transition: all var(--transition-base);
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }
    
    .challenge-option:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: #F59E0B;
        transform: translateX(-4px);
    }
    
    .challenge-option.selected {
        background: rgba(245, 158, 11, 0.2);
        border-color: #F59E0B;
    }
    
    .option-letter {
        width: 35px;
        height: 35px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
    }
    
    .option-text {
        flex: 1;
        color: white;
        font-size: 16px;
        text-align: right;
    }
    
    .submit-challenge-btn {
        background: linear-gradient(135deg, #F59E0B, #EF4444);
        color: white;
        border: none;
        padding: 18px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 18px;
        cursor: pointer;
        width: 100%;
        transition: all var(--transition-base);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
    }
    
    .submit-challenge-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(245, 158, 11, 0.6);
    }
</style>
@endpush

@section('content')
<div class="challenge-container fade-in">
    <!-- Challenge Header -->
    <div class="challenge-header">
        <div class="challenge-icon">🏆</div>
        <h1 class="challenge-title">تحدي القيم</h1>
        <p class="challenge-subtitle">5 أسئلة صعبة - 10 دقائق - 25 نقطة</p>
    </div>

    <!-- Timer -->
    <div class="timer-box">
        <div class="timer-display" id="timer">10:00</div>
        <div class="timer-label">⏱️ الوقت المتبقي</div>
    </div>

    <form id="challengeForm">
        <!-- Question 1 -->
        <div class="challenge-question scale-in">
            <div class="question-header">
                <div class="question-num">1/5</div>
                <div class="question-points">⭐ 5 نقاط</div>
            </div>
            <div class="question-text">ماذا يحدث عندما نكون صادقين مع أنفسنا ومع الآخرين؟</div>
            <label class="challenge-option">
                <div class="option-letter">أ</div>
                <div class="option-text">نبني علاقات قوية ونكسب ثقة الناس</div>
                <input type="radio" name="q1" value="1" style="display: none;">
            </label>
            <label class="challenge-option">
                <div class="option-letter">ب</div>
                <div class="option-text">نخسر الأصدقاء</div>
                <input type="radio" name="q1" value="0" style="display: none;">
            </label>
            <label class="challenge-option">
                <div class="option-letter">ج</div>
                <div class="option-text">لا يوجد فرق</div>
                <input type="radio" name="q1" value="0" style="display: none;">
            </label>
        </div>

        <!-- Question 2 -->
        <div class="challenge-question scale-in" style="animation-delay: 0.1s;">
            <div class="question-header">
                <div class="question-num">2/5</div>
                <div class="question-points">⭐ 5 نقاط</div>
            </div>
            <div class="question-text">إذا رأيت شخصاً يحتاج للمساعدة، ما أفضل تصرف؟</div>
            <label class="challenge-option">
                <div class="option-letter">أ</div>
                <div class="option-text">تجاهله والمرور</div>
                <input type="radio" name="q2" value="0" style="display: none;">
            </label>
            <label class="challenge-option">
                <div class="option-letter">ب</div>
                <div class="option-text">مساعدته دون انتظار مقابل</div>
                <input type="radio" name="q2" value="1" style="display: none;">
            </label>
            <label class="challenge-option">
                <div class="option-letter">ج</div>
                <div class="option-text">الانتظار حتى يطلب المساعدة</div>
                <input type="radio" name="q2" value="0" style="display: none;">
            </label>
        </div>

        <!-- Question 3 -->
        <div class="challenge-question scale-in" style="animation-delay: 0.2s;">
            <div class="question-header">
                <div class="question-num">3/5</div>
                <div class="question-points">⭐ 5 نقاط</div>
            </div>
            <div class="question-text">ما هي أفضل طريقة لحل الخلاف مع صديق؟</div>
            <label class="challenge-option">
                <div class="option-letter">أ</div>
                <div class="option-text">الحوار الهادئ والاستماع لوجهة نظره</div>
                <input type="radio" name="q3" value="1" style="display: none;">
            </label>
            <label class="challenge-option">
                <div class="option-letter">ب</div>
                <div class="option-text">تجاهله نهائياً</div>
                <input type="radio" name="q3" value="0" style="display: none;">
            </label>
            <label class="challenge-option">
                <div class="option-letter">ج</div>
                <div class="option-text">الإصرار على رأيك فقط</div>
                <input type="radio" name="q3" value="0" style="display: none;">
            </label>
        </div>

        <!-- Question 4 -->
        <div class="challenge-question scale-in" style="animation-delay: 0.3s;">
            <div class="question-header">
                <div class="question-num">4/5</div>
                <div class="question-points">⭐ 5 نقاط</div>
            </div>
            <div class="question-text">لماذا من المهم أن نحترم اختلافات الآخرين؟</div>
            <label class="challenge-option">
                <div class="option-letter">أ</div>
                <div class="option-text">لأن الاختلاف يثري حياتنا ويعلمنا أشياء جديدة</div>
                <input type="radio" name="q4" value="1" style="display: none;">
            </label>
            <label class="challenge-option">
                <div class="option-letter">ب</div>
                <div class="option-text">لأننا مجبرون على ذلك</div>
                <input type="radio" name="q4" value="0" style="display: none;">
            </label>
            <label class="challenge-option">
                <div class="option-letter">ج</div>
                <div class="option-text">الاختلاف غير مهم</div>
                <input type="radio" name="q4" value="0" style="display: none;">
            </label>
        </div>

        <!-- Question 5 -->
        <div class="challenge-question scale-in" style="animation-delay: 0.4s;">
            <div class="question-header">
                <div class="question-num">5/5</div>
                <div class="question-points">⭐ 5 نقاط</div>
            </div>
            <div class="question-text">ما هو الدرس الأهم من الفشل؟</div>
            <label class="challenge-option">
                <div class="option-letter">أ</div>
                <div class="option-text">أن نتعلم منه ونحاول مرة أخرى</div>
                <input type="radio" name="q5" value="1" style="display: none;">
            </label>
            <label class="challenge-option">
                <div class="option-letter">ب</div>
                <div class="option-text">أن نستسلم ولا نحاول مجدداً</div>
                <input type="radio" name="q5" value="0" style="display: none;">
            </label>
            <label class="challenge-option">
                <div class="option-letter">ج</div>
                <div class="option-text">أن نلوم الآخرين</div>
                <input type="radio" name="q5" value="0" style="display: none;">
            </label>
        </div>

        <button type="submit" class="submit-challenge-btn">
            إنهاء التحدي 🏆
        </button>
    </form>
</div>

@push('scripts')
<script>
    // Timer countdown
    let timeLeft = 600; // 10 minutes
    const timerDisplay = document.getElementById('timer');
    
    const countdown = setInterval(() => {
        timeLeft--;
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(countdown);
            alert('⏰ انتهى الوقت!');
            document.getElementById('challengeForm').submit();
        }
        
        // Warning color at 1 minute
        if (timeLeft <= 60) {
            timerDisplay.style.color = '#EF4444';
        }
    }, 1000);

    // Handle answer selection
    document.querySelectorAll('.challenge-option').forEach(option => {
        option.addEventListener('click', function() {
            const name = this.querySelector('input').name;
            document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
                input.closest('.challenge-option').classList.remove('selected');
            });
            this.classList.add('selected');
            this.querySelector('input').checked = true;
        });
    });

    // Handle form submission
    document.getElementById('challengeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearInterval(countdown);
        
        const form = new FormData(this);
        let score = 0;
        
        for (let [key, value] of form.entries()) {
            if (value === '1') score++;
        }
        
        const percentage = Math.round((score / 5) * 100);
        const xp = score * 5;
        const timeUsed = 600 - timeLeft;
        const bonusXP = timeLeft > 300 ? 5 : 0; // Bonus for fast completion
        
        // modal بدل alert() لتجربة أفضل و a11y
        const overlay = document.createElement('div');
        overlay.setAttribute('role','dialog'); overlay.setAttribute('aria-modal','true');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,0.7);display:flex;align-items:center;justify-content:center;z-index:99999;backdrop-filter:blur(8px);direction:rtl;';
        const card = document.createElement('div');
        card.style.cssText = 'background:#fff;border-radius:24px;padding:32px;max-width:420px;width:90%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,0.3);';
        const h = document.createElement('h2'); h.textContent = '🏆 تحدي مكتمل!'; h.style.cssText='margin:0 0 16px;font-size:26px;color:#1e293b;font-weight:700;';
        const p1 = document.createElement('p'); p1.textContent='النتيجة: '+score+'/5 ('+percentage+'%)'; p1.style.cssText='font-size:17px;color:#475569;margin:6px 0;';
        const p2 = document.createElement('p'); p2.textContent='الوقت: '+Math.floor(timeUsed/60)+':'+(timeUsed%60).toString().padStart(2,'0'); p2.style.cssText='font-size:15px;color:#64748b;margin:6px 0;';
        const p3 = document.createElement('p');
        p3.textContent = 'النقاط: +'+xp+' XP'+(bonusXP>0?' (+'+bonusXP+' مكافأة السرعة)':'')+' • العملات: +'+Math.floor((xp+bonusXP)/2);
        p3.style.cssText='font-size:15px;color:#10b981;font-weight:600;margin:6px 0 20px;';
        const btn = document.createElement('button'); btn.type='button'; btn.textContent='متابعة';
        btn.style.cssText='padding:14px 36px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:16px;cursor:pointer;';
        btn.onclick = function(){ window.location.href = '{{ route('student.dashboard') }}'; };
        card.append(h,p1,p2,p3,btn); overlay.appendChild(card); document.body.appendChild(overlay); btn.focus();
    });
</script>
@endpush
@endsection
