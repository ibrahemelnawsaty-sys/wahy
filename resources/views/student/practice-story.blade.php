@extends('layouts.student-app')

@section('title', 'قصة تفاعلية')

@push('styles')
<style>
    .story-container {
        max-width: 900px;
        margin: 0 auto;
        padding: var(--spacing-xl) var(--spacing-md);
        padding-bottom: 120px;
    }
    
    .story-card {
        background: var(--glass-bg-heavy);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-2xl);
        padding: var(--spacing-2xl);
        margin-bottom: var(--spacing-xl);
    }
    
    .story-header {
        text-align: center;
        margin-bottom: var(--spacing-2xl);
    }
    
    .story-icon {
        font-size: 80px;
        margin-bottom: var(--spacing-md);
    }
    
    .story-title {
        font-size: 32px;
        font-weight: 800;
        color: white;
        margin-bottom: var(--spacing-sm);
    }
    
    .story-meta {
        display: flex;
        justify-content: center;
        gap: var(--spacing-lg);
        flex-wrap: wrap;
    }
    
    .story-badge {
        background: rgba(139, 92, 246, 0.2);
        color: #A78BFA;
        padding: 8px 16px;
        border-radius: var(--radius-full);
        font-size: 14px;
        font-weight: 600;
    }
    
    .story-content {
        background: rgba(255, 255, 255, 0.03);
        border-radius: var(--radius-xl);
        padding: var(--spacing-2xl);
        margin-bottom: var(--spacing-2xl);
    }
    
    .story-text {
        font-size: 18px;
        line-height: 2;
        color: rgba(255, 255, 255, 0.9);
        text-align: right;
        margin-bottom: var(--spacing-xl);
    }
    
    .story-highlight {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(168, 85, 247, 0.2));
        padding: var(--spacing-lg);
        border-right: 4px solid #8B5CF6;
        border-radius: var(--radius-lg);
        font-weight: 600;
        margin: var(--spacing-lg) 0;
    }
    
    .story-question {
        background: var(--glass-bg-medium);
        border: 2px solid rgba(139, 92, 246, 0.3);
        border-radius: var(--radius-xl);
        padding: var(--spacing-xl);
        margin-top: var(--spacing-2xl);
    }
    
    .question-label {
        font-size: 18px;
        font-weight: 700;
        color: #A78BFA;
        margin-bottom: var(--spacing-lg);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }
    
    .question-text {
        font-size: 20px;
        font-weight: 700;
        color: white;
        margin-bottom: var(--spacing-xl);
        text-align: right;
    }
    
    .story-choice {
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-md);
        cursor: pointer;
        transition: all var(--transition-base);
        text-align: right;
    }
    
    .story-choice:hover {
        background: rgba(139, 92, 246, 0.2);
        border-color: #8B5CF6;
        transform: translateX(-4px);
    }
    
    .story-choice.selected {
        background: rgba(139, 92, 246, 0.3);
        border-color: #8B5CF6;
    }
    
    .choice-number {
        display: inline-block;
        width: 30px;
        height: 30px;
        background: rgba(139, 92, 246, 0.3);
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        font-weight: 700;
        color: white;
        margin-left: var(--spacing-sm);
    }
    
    .choice-text {
        display: inline;
        font-size: 16px;
        color: white;
        font-weight: 600;
    }
    
    .continue-story-btn {
        background: linear-gradient(135deg, #8B5CF6, #A855F7);
        color: white;
        border: none;
        padding: 18px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 18px;
        cursor: pointer;
        width: 100%;
        transition: all var(--transition-base);
        margin-top: var(--spacing-xl);
    }
    
    .continue-story-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
    }
    
    .continue-story-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
<div class="story-container fade-in">
    <div class="story-card">
        <!-- Story Header -->
        <div class="story-header">
            <div class="story-icon">📖</div>
            <h1 class="story-title">قصة الصديقين</h1>
            <div class="story-meta">
                <div class="story-badge">📚 قصة تفاعلية</div>
                <div class="story-badge">⏱️ 8 دقائق</div>
                <div class="story-badge">⭐ +20 XP</div>
            </div>
        </div>

        <!-- Story Content -->
        <div id="storyPart1">
            <div class="story-content">
                <div class="story-text">
                    كان هناك صديقان، أحمد وكريم، يدرسان في نفس المدرسة. في يوم من الأيام، وجد أحمد محفظة على الأرض تحتوي على مبلغ من المال.
                </div>
                <div class="story-text">
                    نظر حوله ولم يجد أحداً، فأخذ المحفظة وذهب إلى كريم ليخبره بما حدث.
                </div>
                <div class="story-highlight">
                    💭 ماذا يجب على أحمد أن يفعل؟
                </div>
            </div>

            <div class="story-question">
                <div class="question-label">
                    <span>🤔</span>
                    <span>السؤال الأول</span>
                </div>
                <div class="question-text">ما هو التصرف الصحيح لأحمد؟</div>
                
                <label class="story-choice">
                    <span class="choice-number">1</span>
                    <span class="choice-text">يحتفظ بالمحفظة والمال لنفسه</span>
                    <input type="radio" name="q1" value="0" style="display: none;">
                </label>
                
                <label class="story-choice">
                    <span class="choice-number">2</span>
                    <span class="choice-text">يأخذ المحفظة إلى المدير أو المعلم للبحث عن صاحبها</span>
                    <input type="radio" name="q1" value="1" style="display: none;">
                </label>
                
                <label class="story-choice">
                    <span class="choice-number">3</span>
                    <span class="choice-text">يترك المحفظة في مكانها ويذهب</span>
                    <input type="radio" name="q1" value="0" style="display: none;">
                </label>
            </div>

            <button class="continue-story-btn" onclick="continueStory(2)" disabled id="btn1">
                متابعة القصة →
            </button>
        </div>

        <!-- Part 2 -->
        <div id="storyPart2" style="display: none;">
            <div class="story-content">
                <div class="story-text">
                    قرر أحمد أن يأخذ المحفظة إلى مدير المدرسة. شعر بالسعادة لأنه اختار الطريق الصحيح، وكان كريم فخوراً بصديقه.
                </div>
                <div class="story-text">
                    بعد يومين، جاء طالب آخر يبحث عن محفظته المفقودة. عندما أعطاه المدير المحفظة، كان الطالب سعيداً جداً وشكر أحمد على أمانته.
                </div>
                <div class="story-highlight">
                    ✨ الأمانة تجلب السعادة والراحة للنفس
                </div>
            </div>

            <div class="story-question">
                <div class="question-label">
                    <span>💡</span>
                    <span>السؤال الثاني</span>
                </div>
                <div class="question-text">ما الدرس الأهم من هذه القصة؟</div>
                
                <label class="story-choice">
                    <span class="choice-number">1</span>
                    <span class="choice-text">الأمانة والصدق يجلبان الاحترام والتقدير</span>
                    <input type="radio" name="q2" value="1" style="display: none;">
                </label>
                
                <label class="story-choice">
                    <span class="choice-number">2</span>
                    <span class="choice-text">يجب أن نخفي الأشياء التي نجدها</span>
                    <input type="radio" name="q2" value="0" style="display: none;">
                </label>
                
                <label class="story-choice">
                    <span class="choice-number">3</span>
                    <span class="choice-text">الأمانة ليست مهمة في الحياة</span>
                    <input type="radio" name="q2" value="0" style="display: none;">
                </label>
            </div>

            <button class="continue-story-btn" onclick="finishStory()" disabled id="btn2">
                إنهاء القصة ✓
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let answers = {};
    
    // Handle choice selection
    document.querySelectorAll('.story-choice').forEach(choice => {
        choice.addEventListener('click', function() {
            const name = this.querySelector('input').name;
            const value = this.querySelector('input').value;
            
            // Remove selected class from all choices in this question
            document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
                input.closest('.story-choice').classList.remove('selected');
            });
            
            // Add selected class to clicked choice
            this.classList.add('selected');
            this.querySelector('input').checked = true;
            
            // Store answer
            answers[name] = value;
            
            // Enable button
            if (name === 'q1') {
                document.getElementById('btn1').disabled = false;
            } else if (name === 'q2') {
                document.getElementById('btn2').disabled = false;
            }
        });
    });
    
    function continueStory(part) {
        document.getElementById('storyPart1').style.display = 'none';
        document.getElementById('storyPart2').style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    function finishStory() {
        let score = 0;
        if (answers.q1 === '1') score++;
        if (answers.q2 === '1') score++;
        
        const percentage = Math.round((score / 2) * 100);
        const xp = 20;
        const coins = Math.floor(xp / 2);
        
        const overlay = document.createElement('div');
        overlay.setAttribute('role','dialog'); overlay.setAttribute('aria-modal','true');
        overlay.style.cssText='position:fixed;inset:0;background:rgba(15,23,42,0.7);display:flex;align-items:center;justify-content:center;z-index:99999;backdrop-filter:blur(8px);direction:rtl;';
        const card = document.createElement('div');
        card.style.cssText='background:#fff;border-radius:24px;padding:32px;max-width:440px;width:90%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,0.3);';
        const h = document.createElement('h2'); h.textContent='📖 القصة انتهت!'; h.style.cssText='margin:0 0 12px;font-size:26px;color:#1e293b;font-weight:700;';
        const p0 = document.createElement('p'); p0.textContent='✨ لقد أكملت قصة تفاعلية'; p0.style.cssText='font-size:15px;color:#64748b;margin:6px 0;';
        const p1 = document.createElement('p'); p1.textContent='الإجابات الصحيحة: '+score+'/2 ('+percentage+'%)'; p1.style.cssText='font-size:17px;color:#475569;margin:6px 0;';
        const p2 = document.createElement('p'); p2.textContent='🎁 المكافآت: +'+xp+' XP و +'+coins+' عملة'; p2.style.cssText='font-size:15px;color:#10b981;font-weight:600;margin:6px 0;';
        const p3 = document.createElement('p'); p3.textContent='💡 الأمانة والصدق هما أساس العلاقات القوية'; p3.style.cssText='font-size:13px;color:#94a3b8;margin:12px 0 20px;font-style:italic;';
        const btn = document.createElement('button'); btn.type='button'; btn.textContent='متابعة';
        btn.style.cssText='padding:14px 36px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:16px;cursor:pointer;';
        btn.onclick = function(){ window.location.href='{{ route('student.dashboard') }}'; };
        card.append(h,p0,p1,p2,p3,btn); overlay.appendChild(card); document.body.appendChild(overlay); btn.focus();
    }
</script>
@endpush
@endsection
