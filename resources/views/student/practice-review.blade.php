@extends('layouts.student-app')

@section('title', 'مراجعة سريعة')

@push('styles')
<style>
    .practice-container {
        max-width: 800px;
        margin: 0 auto;
        padding: var(--spacing-xl) var(--spacing-md);
        padding-bottom: 120px;
    }
    
    .practice-header {
        text-align: center;
        margin-bottom: var(--spacing-2xl);
    }
    
    .practice-icon-large {
        font-size: 80px;
        margin-bottom: var(--spacing-md);
    }
    
    .practice-title {
        font-size: 32px;
        font-weight: 800;
        color: white;
        margin-bottom: var(--spacing-sm);
    }
    
    .practice-subtitle {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: var(--spacing-xl);
    }
    
    .question-card {
        background: var(--glass-bg-heavy);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-2xl);
        padding: var(--spacing-2xl);
        margin-bottom: var(--spacing-xl);
    }
    
    .question-number {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
        padding: 8px 20px;
        border-radius: var(--radius-full);
        font-weight: 700;
        display: inline-block;
        margin-bottom: var(--spacing-lg);
    }
    
    .question-text {
        font-size: 22px;
        font-weight: 700;
        color: white;
        margin-bottom: var(--spacing-xl);
        text-align: right;
        line-height: 1.6;
    }
    
    .answer-option {
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-md);
        cursor: pointer;
        transition: all var(--transition-base);
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }
    
    .answer-option:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--color-primary);
        transform: translateX(-4px);
    }
    
    .answer-option.selected {
        background: rgba(16, 185, 129, 0.2);
        border-color: var(--color-primary);
    }
    
    .answer-letter {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
        color: white;
        flex-shrink: 0;
    }
    
    .answer-text {
        flex: 1;
        font-size: 18px;
        color: white;
        text-align: right;
    }
    
    .submit-btn {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
        border: none;
        padding: 18px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 18px;
        cursor: pointer;
        width: 100%;
        transition: all var(--transition-base);
    }
    
    .submit-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
    }
    
    .submit-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .text-input-field {
        width: 100%;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        color: white;
        font-size: 16px;
        font-family: inherit;
        transition: all var(--transition-base);
    }
    
    .text-input-field:focus {
        outline: none;
        border-color: var(--color-primary);
        background: rgba(255, 255, 255, 0.1);
    }
    
    .text-input-field::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }
</style>
@endpush

@section('content')
<div class="practice-container fade-in">
    <div class="practice-header">
        <div class="practice-icon-large">📝</div>
        <h1 class="practice-title">مراجعة سريعة</h1>
        <p class="practice-subtitle">اختبر معلوماتك في 8 أسئلة متنوعة</p>
    </div>

    <form id="reviewForm">
        @csrf
        
        <!-- Question 1 -->
        <div class="question-card scale-in">
            <div class="question-number">السؤال 1 من 5</div>
            <div class="question-text">ما هي أهمية الصدق في حياتنا؟</div>
            <div class="answers-list">
                <label class="answer-option">
                    <div class="answer-letter">أ</div>
                    <div class="answer-text">يبني الثقة بين الناس</div>
                    <input type="radio" name="q1" value="1" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ب</div>
                    <div class="answer-text">يجعل الحياة أصعب</div>
                    <input type="radio" name="q1" value="0" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ج</div>
                    <div class="answer-text">ليس له أهمية</div>
                    <input type="radio" name="q1" value="0" style="display: none;">
                </label>
            </div>
        </div>

        <!-- Question 2 -->
        <div class="question-card scale-in" style="animation-delay: 0.1s;">
            <div class="question-number">السؤال 2 من 5</div>
            <div class="question-text">أي من هذه يعتبر من الأمانة؟</div>
            <div class="answers-list">
                <label class="answer-option">
                    <div class="answer-letter">أ</div>
                    <div class="answer-text">إعادة الأشياء المستعارة</div>
                    <input type="radio" name="q2" value="1" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ب</div>
                    <div class="answer-text">الاحتفاظ بما وجدته</div>
                    <input type="radio" name="q2" value="0" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ج</div>
                    <div class="answer-text">عدم الاهتمام بالآخرين</div>
                    <input type="radio" name="q2" value="0" style="display: none;">
                </label>
            </div>
        </div>

        <!-- Question 3 -->
        <div class="question-card scale-in" style="animation-delay: 0.2s;">
            <div class="question-number">السؤال 3 من 5</div>
            <div class="question-text">كيف نظهر الاحترام للآخرين؟</div>
            <div class="answers-list">
                <label class="answer-option">
                    <div class="answer-letter">أ</div>
                    <div class="answer-text">الاستماع لهم بإنصات</div>
                    <input type="radio" name="q3" value="1" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ب</div>
                    <div class="answer-text">مقاطعتهم أثناء الكلام</div>
                    <input type="radio" name="q3" value="0" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ج</div>
                    <div class="answer-text">تجاهل آرائهم</div>
                    <input type="radio" name="q3" value="0" style="display: none;">
                </label>
            </div>
        </div>

        <!-- Question 4 -->
        <div class="question-card scale-in" style="animation-delay: 0.3s;">
            <div class="question-number">السؤال 4 من 5</div>
            <div class="question-text">ما هو التعاون؟</div>
            <div class="answers-list">
                <label class="answer-option">
                    <div class="answer-letter">أ</div>
                    <div class="answer-text">العمل مع الآخرين لتحقيق هدف مشترك</div>
                    <input type="radio" name="q4" value="1" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ب</div>
                    <div class="answer-text">العمل بمفردك دائماً</div>
                    <input type="radio" name="q4" value="0" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ج</div>
                    <div class="answer-text">عدم مساعدة الآخرين</div>
                    <input type="radio" name="q4" value="0" style="display: none;">
                </label>
            </div>
        </div>

        <!-- Question 5: Fill in the Blank -->
        <div class="question-card scale-in" style="animation-delay: 0.4s;">
            <div class="question-number">السؤال 5 من 8</div>
            <div class="question-text">أكمل الفراغ: المثابرة تعني الاستمرار في العمل حتى نحقق ______</div>
            <input type="text" name="q5" class="text-input-field" placeholder="اكتب الكلمة المناسبة..." 
                   style="width: 100%; padding: 16px; border-radius: 12px; border: 2px solid rgba(255,255,255,0.2); 
                          background: rgba(255,255,255,0.05); color: white; font-size: 16px; margin-bottom: 10px;">
            <div style="font-size: 13px; color: rgba(255,255,255,0.6); text-align: right;">💡 تلميح: ما الذي نسعى لتحقيقه؟</div>
        </div>

        <!-- Question 6: Video Question -->
        <div class="question-card scale-in" style="animation-delay: 0.5s;">
            <div class="question-number">السؤال 6 من 8</div>
            <div class="question-text">شاهد الفيديو ثم أجب:</div>
            
            <!-- Video Player -->
            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 16px; margin-bottom: 20px; background: rgba(0,0,0,0.3);">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                    <div style="font-size: 60px; margin-bottom: 12px;">🎥</div>
                    <div style="color: rgba(255,255,255,0.7); font-size: 14px;">فيديو تعليمي عن التعاون</div>
                    <button type="button" onclick="playVideo(this)" style="margin-top: 12px; padding: 10px 24px; background: linear-gradient(135deg, #10B981, #059669); border: none; border-radius: 20px; color: white; font-weight: 600; cursor: pointer;">
                        ▶ تشغيل الفيديو
                    </button>
                </div>
            </div>
            
            <div class="question-text" style="font-size: 18px; margin-top: 20px;">ما هي الفائدة الرئيسية من التعاون كما ظهر في الفيديو؟</div>
            <div class="answers-list">
                <label class="answer-option">
                    <div class="answer-letter">أ</div>
                    <div class="answer-text">إنجاز المهام بشكل أسرع وأفضل</div>
                    <input type="radio" name="q6" value="1" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ب</div>
                    <div class="answer-text">تجنب العمل الجماعي</div>
                    <input type="radio" name="q6" value="0" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ج</div>
                    <div class="answer-text">العمل بمفردك أفضل</div>
                    <input type="radio" name="q6" value="0" style="display: none;">
                </label>
            </div>
        </div>

        <!-- Question 7: Audio Question -->
        <div class="question-card scale-in" style="animation-delay: 0.6s;">
            <div class="question-number">السؤال 7 من 8</div>
            <div class="question-text">استمع للمقطع الصوتي ثم أجب:</div>
            
            <!-- Audio Player -->
            <div style="background: rgba(255,255,255,0.05); border: 2px solid rgba(255,255,255,0.2); border-radius: 16px; padding: 24px; text-align: center; margin-bottom: 20px;">
                <div style="font-size: 50px; margin-bottom: 12px;">🎧</div>
                <div style="color: rgba(255,255,255,0.7); font-size: 14px; margin-bottom: 16px;">مقطع صوتي عن الصدق والأمانة</div>
                <button type="button" onclick="playAudio(this)" style="padding: 12px 32px; background: linear-gradient(135deg, #8B5CF6, #A855F7); border: none; border-radius: 24px; color: white; font-weight: 700; cursor: pointer; font-size: 16px;">
                    🔊 تشغيل الصوت
                </button>
                <div style="margin-top: 12px; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; overflow: hidden;">
                    <div class="audio-progress" style="width: 0%; height: 100%; background: linear-gradient(90deg, #8B5CF6, #A855F7); transition: width 0.1s;"></div>
                </div>
            </div>
            
            <div class="question-text" style="font-size: 18px; margin-top: 20px;">وفقاً للمقطع الصوتي، ما هي أهمية الصدق؟</div>
            <div class="answers-list">
                <label class="answer-option">
                    <div class="answer-letter">أ</div>
                    <div class="answer-text">يبني الثقة ويقوي العلاقات</div>
                    <input type="radio" name="q7" value="1" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ب</div>
                    <div class="answer-text">يجعل الحياة معقدة</div>
                    <input type="radio" name="q7" value="0" style="display: none;">
                </label>
                <label class="answer-option">
                    <div class="answer-letter">ج</div>
                    <div class="answer-text">ليس له أهمية كبيرة</div>
                    <input type="radio" name="q7" value="0" style="display: none;">
                </label>
            </div>
        </div>

        <!-- Question 8: Matching -->
        <div class="question-card scale-in" style="animation-delay: 0.7s;">
            <div class="question-number">السؤال 8 من 8</div>
            <div class="question-text">وصّل كل قيمة بمثالها المناسب:</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <!-- Right Side: Values -->
                <div style="order: 2;">
                    <div style="font-size: 14px; font-weight: 700; color: rgba(255,255,255,0.7); margin-bottom: 12px; text-align: right;">القيم</div>
                    <div class="match-item" data-value="honesty" style="background: rgba(16,185,129,0.2); border: 2px solid rgba(16,185,129,0.4); border-radius: 12px; padding: 16px; margin-bottom: 10px; text-align: right; font-weight: 600; color: white; cursor: pointer; transition: all 0.3s;">
                        الصدق 🤝
                    </div>
                    <div class="match-item" data-value="respect" style="background: rgba(59,130,246,0.2); border: 2px solid rgba(59,130,246,0.4); border-radius: 12px; padding: 16px; margin-bottom: 10px; text-align: right; font-weight: 600; color: white; cursor: pointer; transition: all 0.3s;">
                        الاحترام 🙏
                    </div>
                    <div class="match-item" data-value="cooperation" style="background: rgba(245,158,11,0.2); border: 2px solid rgba(245,158,11,0.4); border-radius: 12px; padding: 16px; text-align: right; font-weight: 600; color: white; cursor: pointer; transition: all 0.3s;">
                        التعاون 🤲
                    </div>
                </div>
                
                <!-- Left Side: Examples -->
                <div style="order: 1;">
                    <div style="font-size: 14px; font-weight: 700; color: rgba(255,255,255,0.7); margin-bottom: 12px; text-align: right;">الأمثلة</div>
                    <div class="match-target" data-answer="honesty" style="background: rgba(255,255,255,0.05); border: 2px dashed rgba(255,255,255,0.3); border-radius: 12px; padding: 16px; margin-bottom: 10px; text-align: right; color: rgba(255,255,255,0.8); min-height: 56px; display: flex; align-items: center; justify-content: flex-end;">
                        قول الحقيقة دائماً
                    </div>
                    <div class="match-target" data-answer="respect" style="background: rgba(255,255,255,0.05); border: 2px dashed rgba(255,255,255,0.3); border-radius: 12px; padding: 16px; margin-bottom: 10px; text-align: right; color: rgba(255,255,255,0.8); min-height: 56px; display: flex; align-items: center; justify-content: flex-end;">
                        الاستماع للآخرين
                    </div>
                    <div class="match-target" data-answer="cooperation" style="background: rgba(255,255,255,0.05); border: 2px dashed rgba(255,255,255,0.3); border-radius: 12px; padding: 16px; text-align: right; color: rgba(255,255,255,0.8); min-height: 56px; display: flex; align-items: center; justify-content: flex-end;">
                        مساعدة الفريق
                    </div>
                </div>
            </div>
            <input type="hidden" name="q8" id="matchingAnswer" value="">
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">
            إنهاء المراجعة ✓
        </button>
    </form>
</div>

@push('scripts')
<script>
    // Matching Game
    let selectedMatch = null;
    let matches = {};
    
    document.querySelectorAll('.match-item').forEach(item => {
        item.addEventListener('click', function() {
            if (selectedMatch) {
                selectedMatch.style.transform = '';
                selectedMatch.style.boxShadow = '';
            }
            selectedMatch = this;
            this.style.transform = 'scale(1.05)';
            this.style.boxShadow = '0 8px 20px rgba(16,185,129,0.4)';
        });
    });
    
    document.querySelectorAll('.match-target').forEach(target => {
        target.addEventListener('click', function() {
            if (!selectedMatch) {
                alert('اختر قيمة أولاً');
                return;
            }
            
            const value = selectedMatch.dataset.value;
            const answer = this.dataset.answer;
            
            if (value === answer) {
                // Correct match
                this.style.background = 'rgba(16,185,129,0.3)';
                this.style.borderColor = '#10B981';
                this.style.borderStyle = 'solid';
                
                // Clone the matched item
                const clone = selectedMatch.cloneNode(true);
                clone.style.transform = 'scale(0.9)';
                clone.style.cursor = 'default';
                clone.style.margin = '0';
                this.innerHTML = '';
                this.appendChild(clone);
                
                // Hide original
                selectedMatch.style.opacity = '0.3';
                selectedMatch.style.pointerEvents = 'none';
                
                matches[value] = answer;
                selectedMatch = null;
                
                // Check if all matched
                if (Object.keys(matches).length === 3) {
                    document.getElementById('matchingAnswer').value = '1';
                }
            } else {
                // Wrong match
                this.style.animation = 'shake 0.5s';
                setTimeout(() => {
                    this.style.animation = '';
                }, 500);
            }
        });
    });
    
    // Video player simulation
    function playVideo(btn) {
        btn.innerHTML = '⏸ إيقاف مؤقت';
        btn.style.background = 'linear-gradient(135deg, #EF4444, #DC2626)';
        const parent = btn.closest('div').closest('div');
        parent.style.background = 'rgba(16,185,129,0.1)';
        
        setTimeout(() => {
            btn.innerHTML = '✓ تم المشاهدة';
            btn.disabled = true;
            btn.style.background = 'rgba(16,185,129,0.5)';
        }, 3000);
    }
    
    // Audio player simulation
    function playAudio(btn) {
        btn.innerHTML = '⏸ إيقاف';
        btn.style.background = 'linear-gradient(135deg, #EF4444, #DC2626)';
        const progress = btn.parentElement.querySelector('.audio-progress');
        
        let width = 0;
        const interval = setInterval(() => {
            width += 2;
            progress.style.width = width + '%';
            if (width >= 100) {
                clearInterval(interval);
                btn.innerHTML = '✓ تم الاستماع';
                btn.disabled = true;
                btn.style.background = 'rgba(139,92,246,0.5)';
            }
        }, 60);
    }

    // Handle answer selection
    document.querySelectorAll('.answer-option').forEach(option => {
        option.addEventListener('click', function() {
            const name = this.querySelector('input').name;
            document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
                input.closest('.answer-option').classList.remove('selected');
            });
            this.classList.add('selected');
            this.querySelector('input').checked = true;
        });
    });

    // Handle form submission
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = new FormData(this);
        let score = 0;
        let total = 8;
        
        // Check multiple choice (q1-q4, q6-q7)
        ['q1', 'q2', 'q3', 'q4', 'q6', 'q7'].forEach(key => {
            if (form.get(key) === '1') score++;
        });
        
        // Check fill in blank (q5)
        const q5Answer = (form.get('q5') || '').trim().toLowerCase();
        if (q5Answer === 'أهدافنا' || q5Answer === 'الهدف' || q5Answer === 'النجاح' || q5Answer === 'اهدافنا') {
            score++;
        }
        
        // Check matching (q8)
        if (form.get('q8') === '1') {
            score++;
        }
        
        const percentage = Math.round((score / total) * 100);
        const xp = score * 2;
        
        // modal بدل alert() لتجربة أفضل و a11y
        const overlay = document.createElement('div');
        overlay.setAttribute('role','dialog'); overlay.setAttribute('aria-modal','true');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,0.7);display:flex;align-items:center;justify-content:center;z-index:99999;backdrop-filter:blur(8px);direction:rtl;';
        const card = document.createElement('div');
        card.style.cssText = 'background:#fff;border-radius:24px;padding:32px;max-width:420px;width:90%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,0.3);';
        const h = document.createElement('h2'); h.textContent='🎉 رائع!'; h.style.cssText='margin:0 0 16px;font-size:26px;color:#1e293b;font-weight:700;';
        const p1 = document.createElement('p'); p1.textContent='النتيجة: '+score+' من '+total+' ('+percentage+'%)'; p1.style.cssText='font-size:17px;color:#475569;margin:6px 0;';
        const p2 = document.createElement('p'); p2.textContent='حصلت على: +'+xp+' XP و +'+Math.floor(xp/2)+' عملة'; p2.style.cssText='font-size:15px;color:#10b981;font-weight:600;margin:6px 0 20px;';
        const btn = document.createElement('button'); btn.type='button'; btn.textContent='متابعة';
        btn.style.cssText='padding:14px 36px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:16px;cursor:pointer;';
        btn.onclick = function(){ window.location.href='{{ route('student.dashboard') }}'; };
        card.append(h,p1,p2,btn); overlay.appendChild(card); document.body.appendChild(overlay); btn.focus();
    });
    
    // Add shake animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
    `;
    document.head.appendChild(style);
</script>
@endpush
@endsection
