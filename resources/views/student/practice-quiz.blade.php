@extends('layouts.student-app')

@section('title', 'اختبار سريع')

@push('styles')
<style>
    .quiz-container {
        max-width: 700px;
        margin: 0 auto;
        padding: var(--spacing-xl) var(--spacing-md);
        padding-bottom: 120px;
    }
    
    .quiz-progress {
        background: var(--glass-bg-medium);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
        text-align: center;
    }
    
    .quiz-progress-text {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: var(--spacing-sm);
    }
    
    .quiz-progress-bar {
        height: 8px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-full);
        overflow: hidden;
        margin-bottom: var(--spacing-sm);
    }
    
    .quiz-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
        transition: width 0.3s ease;
    }
    
    .quiz-card {
        background: var(--glass-bg-heavy);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-2xl);
        padding: var(--spacing-2xl);
    }
    
    .quiz-icon {
        font-size: 60px;
        text-align: center;
        margin-bottom: var(--spacing-lg);
    }
    
    .quiz-question {
        font-size: 24px;
        font-weight: 700;
        color: white;
        text-align: center;
        margin-bottom: var(--spacing-2xl);
        line-height: 1.5;
    }
    
    .quiz-options {
        display: grid;
        gap: var(--spacing-md);
    }
    
    .quiz-option {
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg) var(--spacing-xl);
        cursor: pointer;
        transition: all var(--transition-base);
        font-size: 18px;
        color: white;
        text-align: center;
        font-weight: 600;
    }
    
    .quiz-option:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--color-primary);
        transform: scale(1.02);
    }
    
    .quiz-option.correct {
        background: rgba(34, 197, 94, 0.3);
        border-color: #22C55E;
    }
    
    .quiz-option.incorrect {
        background: rgba(239, 68, 68, 0.3);
        border-color: #EF4444;
    }
    
    .quiz-feedback {
        margin-top: var(--spacing-xl);
        padding: var(--spacing-lg);
        border-radius: var(--radius-xl);
        text-align: center;
        font-weight: 600;
        display: none;
    }
    
    .quiz-feedback.show {
        display: block;
    }
    
    .quiz-feedback.correct {
        background: rgba(34, 197, 94, 0.2);
        color: #22C55E;
    }
    
    .quiz-feedback.incorrect {
        background: rgba(239, 68, 68, 0.2);
        color: #EF4444;
    }
    
    .next-btn {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
        border: none;
        padding: 16px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        margin-top: var(--spacing-lg);
        display: none;
    }
    
    .next-btn.show {
        display: block;
    }
</style>
@endpush

@section('content')
<div class="quiz-container fade-in">
    <!-- Progress -->
    <div class="quiz-progress">
        <div class="quiz-progress-text">السؤال <span id="currentQ">1</span> من 3</div>
        <div class="quiz-progress-bar">
            <div class="quiz-progress-fill" id="progressBar" style="width: 33.33%"></div>
        </div>
    </div>

    <!-- Quiz Card -->
    <div class="quiz-card" id="quizCard">
        <div class="quiz-icon" id="quizIcon">🎯</div>
        <div class="quiz-question" id="quizQuestion">جاري التحميل...</div>
        <div class="quiz-options" id="quizOptions"></div>
        <div class="quiz-feedback" id="quizFeedback"></div>
        <button class="next-btn" id="nextBtn">السؤال التالي →</button>
    </div>
</div>

@push('scripts')
<script>
    const questions = [
        {
            icon: '🤝',
            type: 'multiple_choice',
            question: 'ما معنى التعاون؟',
            options: [
                { text: 'مساعدة الآخرين لتحقيق هدف مشترك', correct: true },
                { text: 'العمل بمفردك دائماً', correct: false },
                { text: 'تجاهل من حولك', correct: false }
            ]
        },
        {
            icon: '🎥',
            type: 'video',
            question: 'شاهد الفيديو: ما هي أهمية المثابرة؟',
            videoTitle: 'فيديو قصير عن المثابرة',
            options: [
                { text: 'الاستمرار رغم التحديات', correct: true },
                { text: 'الاستسلام عند الصعوبة', correct: false },
                { text: 'تجنب المحاولة', correct: false }
            ]
        },
        {
            icon: '🎧',
            type: 'audio',
            question: 'استمع للمقطع: كيف نظهر الاحترام؟',
            audioTitle: 'مقطع صوتي عن الاحترام',
            options: [
                { text: 'الاستماع للآخرين بإنصات', correct: true },
                { text: 'مقاطعتهم أثناء الكلام', correct: false },
                { text: 'تجاهل آرائهم', correct: false }
            ]
        },
        {
            icon: '✍️',
            type: 'fill_blank',
            question: 'أكمل: الصدق هو أساس ______ بين الناس',
            correctAnswers: ['الثقة', 'ثقة', 'الثقه', 'ثقه'],
            hint: '💡 ما الذي يبنيه الصدق بين الناس؟'
        },
        {
            icon: '🔗',
            type: 'matching',
            question: 'وصّل كل قيمة بمثالها:',
            pairs: [
                { value: 'honesty', valueText: 'الصدق 🤝', example: 'قول الحقيقة' },
                { value: 'kindness', valueText: 'اللطف ❤️', example: 'مساعدة الآخرين' },
                { value: 'patience', valueText: 'الصبر ⏳', example: 'الانتظار بهدوء' }
            ]
        }
    ];

    let currentQuestion = 0;
    let score = 0;
    let answered = false;
    let videoPlayed = false;
    let audioPlayed = false;
    let matchingComplete = {};

    function loadQuestion() {
        answered = false;
        videoPlayed = false;
        audioPlayed = false;
        matchingComplete = {};
        
        const q = questions[currentQuestion];
        
        document.getElementById('currentQ').textContent = currentQuestion + 1;
        document.getElementById('progressBar').style.width = ((currentQuestion + 1) / questions.length * 100) + '%';
        document.getElementById('quizIcon').textContent = q.icon;
        document.getElementById('quizQuestion').textContent = q.question;
        document.getElementById('quizFeedback').classList.remove('show', 'correct', 'incorrect');
        document.getElementById('nextBtn').classList.remove('show');
        
        let optionsHtml = '';
        
        if (q.type === 'video') {
            optionsHtml = `
                <div style="position: relative; padding-bottom: 56.25%; border-radius: 16px; margin-bottom: 20px; background: rgba(0,0,0,0.4); overflow: hidden;">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <div style="font-size: 60px; margin-bottom: 12px;">🎥</div>
                        <div style="color: white; font-size: 14px; margin-bottom: 12px;">${q.videoTitle}</div>
                        <button type="button" onclick="playQuizVideo()" style="padding: 10px 24px; background: linear-gradient(135deg, #10B981, #059669); border: none; border-radius: 20px; color: white; font-weight: 600; cursor: pointer;">
                            ▶ تشغيل
                        </button>
                    </div>
                </div>
                ${q.options.map((opt, i) => 
                    `<div class="quiz-option" onclick="selectAnswer(${i}, ${opt.correct})">${opt.text}</div>`
                ).join('')}
            `;
        } else if (q.type === 'audio') {
            optionsHtml = `
                <div style="background: rgba(139,92,246,0.2); border: 2px solid rgba(139,92,246,0.4); border-radius: 16px; padding: 24px; text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 50px; margin-bottom: 12px;">🎧</div>
                    <div style="color: white; font-size: 14px; margin-bottom: 16px;">${q.audioTitle}</div>
                    <button type="button" onclick="playQuizAudio()" style="padding: 12px 32px; background: linear-gradient(135deg, #8B5CF6, #A855F7); border: none; border-radius: 24px; color: white; font-weight: 700; cursor: pointer; font-size: 16px;">
                        🔊 تشغيل
                    </button>
                    <div style="margin-top: 12px; height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px; overflow: hidden;">
                        <div id="audioProgress" style="width: 0%; height: 100%; background: linear-gradient(90deg, #8B5CF6, #A855F7); transition: width 0.1s;"></div>
                    </div>
                </div>
                ${q.options.map((opt, i) => 
                    `<div class="quiz-option" onclick="selectAnswer(${i}, ${opt.correct})">${opt.text}</div>`
                ).join('')}
            `;
        } else if (q.type === 'fill_blank') {
            optionsHtml = `
                <input type="text" id="fillBlankInput" placeholder="اكتب الكلمة المناسبة..." 
                       style="width: 100%; padding: 18px; font-size: 18px; border-radius: 16px; border: 2px solid rgba(255,255,255,0.3); 
                              background: rgba(255,255,255,0.1); color: white; text-align: center; font-weight: 600; margin-bottom: 12px;">
                <div style="font-size: 14px; color: rgba(255,255,255,0.6); text-align: center; margin-bottom: 20px;">${q.hint}</div>
                <button type="button" onclick="checkFillBlank()" class="quiz-option" style="background: linear-gradient(135deg, #10B981, #059669);">
                    تحقق من الإجابة ✓
                </button>
            `;
        } else if (q.type === 'matching') {
            optionsHtml = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px;">
                    <div style="order: 2;">
                        <div style="font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 10px; text-align: right; font-weight: 600;">القيم</div>
                        ${q.pairs.map(pair => `
                            <div class="match-item" data-value="${pair.value}" onclick="selectMatchItem(this)"
                                 style="background: rgba(16,185,129,0.2); border: 2px solid rgba(16,185,129,0.4); 
                                        border-radius: 12px; padding: 14px; margin-bottom: 8px; text-align: right; 
                                        color: white; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                                ${pair.valueText}
                            </div>
                        `).join('')}
                    </div>
                    <div style="order: 1;">
                        <div style="font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 10px; text-align: right; font-weight: 600;">الأمثلة</div>
                        ${q.pairs.map(pair => `
                            <div class="match-target" data-answer="${pair.value}" onclick="matchTarget(this)"
                                 style="background: rgba(255,255,255,0.05); border: 2px dashed rgba(255,255,255,0.3); 
                                        border-radius: 12px; padding: 14px; margin-bottom: 8px; text-align: right; 
                                        color: rgba(255,255,255,0.8); min-height: 48px; cursor: pointer; transition: all 0.3s;
                                        display: flex; align-items: center; justify-content: flex-end;">
                                ${pair.example}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        } else {
            optionsHtml = q.options.map((opt, i) => 
                `<div class="quiz-option" onclick="selectAnswer(${i}, ${opt.correct})">${opt.text}</div>`
            ).join('');
        }
        
        document.getElementById('quizOptions').innerHTML = optionsHtml;
    }

    window.playQuizVideo = function() {
        videoPlayed = true;
        event.target.innerHTML = '✓ تم المشاهدة';
        event.target.disabled = true;
        event.target.style.background = 'rgba(16,185,129,0.6)';
    };

    window.playQuizAudio = function() {
        audioPlayed = true;
        const btn = event.target;
        btn.innerHTML = '⏸ جارٍ التشغيل...';
        const progress = document.getElementById('audioProgress');
        
        let width = 0;
        const interval = setInterval(() => {
            width += 3;
            progress.style.width = width + '%';
            if (width >= 100) {
                clearInterval(interval);
                btn.innerHTML = '✓ تم الاستماع';
                btn.disabled = true;
                btn.style.background = 'rgba(139,92,246,0.6)';
            }
        }, 40);
    };

    window.checkFillBlank = function() {
        if (answered) return;
        const q = questions[currentQuestion];
        const input = document.getElementById('fillBlankInput');
        const answer = input.value.trim().toLowerCase();
        const feedback = document.getElementById('quizFeedback');
        
        const isCorrect = q.correctAnswers.some(correct => answer === correct.toLowerCase());
        
        answered = true;
        
        if (isCorrect) {
            input.style.borderColor = '#10B981';
            input.style.background = 'rgba(16,185,129,0.2)';
            feedback.textContent = '✓ إجابة صحيحة! ممتاز';
            feedback.classList.add('show', 'correct');
            score++;
        } else {
            input.style.borderColor = '#EF4444';
            input.style.background = 'rgba(239,68,68,0.2)';
            feedback.textContent = `✗ الإجابة الصحيحة: ${q.correctAnswers[0]}`;
            feedback.classList.add('show', 'incorrect');
        }
        
        if (currentQuestion < questions.length - 1) {
            document.getElementById('nextBtn').classList.add('show');
        } else {
            setTimeout(showResults, 1500);
        }
    };

    let selectedMatchItem = null;
    window.selectMatchItem = function(item) {
        if (selectedMatchItem) {
            selectedMatchItem.style.transform = '';
            selectedMatchItem.style.boxShadow = '';
        }
        selectedMatchItem = item;
        item.style.transform = 'scale(1.05)';
        item.style.boxShadow = '0 6px 20px rgba(16,185,129,0.4)';
    };

    window.matchTarget = function(target) {
        if (!selectedMatchItem) return;
        
        const value = selectedMatchItem.dataset.value;
        const answer = target.dataset.answer;
        
        if (value === answer) {
            target.style.background = 'rgba(16,185,129,0.3)';
            target.style.borderColor = '#10B981';
            target.style.borderStyle = 'solid';
            
            const clone = selectedMatchItem.cloneNode(true);
            clone.style.transform = 'scale(0.9)';
            clone.style.cursor = 'default';
            target.innerHTML = '';
            target.appendChild(clone);
            
            selectedMatchItem.style.opacity = '0.3';
            selectedMatchItem.style.pointerEvents = 'none';
            
            matchingComplete[value] = true;
            selectedMatchItem = null;
            
            if (Object.keys(matchingComplete).length === questions[currentQuestion].pairs.length) {
                answered = true;
                score++;
                const feedback = document.getElementById('quizFeedback');
                feedback.textContent = '✓ رائع! جميع التوصيلات صحيحة';
                feedback.classList.add('show', 'correct');
                
                if (currentQuestion < questions.length - 1) {
                    document.getElementById('nextBtn').classList.add('show');
                } else {
                    setTimeout(showResults, 1500);
                }
            }
        } else {
            target.style.animation = 'shake 0.5s';
            setTimeout(() => target.style.animation = '', 500);
        }
    };

    function selectAnswer(index, isCorrect) {
        if (answered) return;
        answered = true;

        const options = document.querySelectorAll('.quiz-option');
        const feedback = document.getElementById('quizFeedback');
        
        if (isCorrect) {
            options[index].classList.add('correct');
            feedback.textContent = '✓ إجابة صحيحة! ممتاز';
            feedback.classList.add('show', 'correct');
            score++;
        } else {
            options[index].classList.add('incorrect');
            questions[currentQuestion].options.forEach((opt, i) => {
                if (opt.correct) options[i].classList.add('correct');
            });
            feedback.textContent = '✗ إجابة خاطئة. حاول التركيز أكثر';
            feedback.classList.add('show', 'incorrect');
        }
        
        if (currentQuestion < questions.length - 1) {
            document.getElementById('nextBtn').classList.add('show');
        } else {
            setTimeout(showResults, 1500);
        }
    }

    function nextQuestion() {
        currentQuestion++;
        loadQuestion();
    }

    function showResults() {
        const percentage = Math.round((score / questions.length) * 100);
        const xp = score * 3;
        const coins = Math.floor(xp/2);
        // إنشاء modal مُنسق RTL يستبدل alert() المُعطّل للـ thread
        const overlay = document.createElement('div');
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', 'qz-result-title');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,0.7);display:flex;align-items:center;justify-content:center;z-index:99999;backdrop-filter:blur(8px);direction:rtl;';
        const card = document.createElement('div');
        card.style.cssText = 'background:linear-gradient(135deg,#fff 0%,#f8fafc 100%);border-radius:24px;padding:36px 32px;max-width:420px;width:90%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,0.3);';
        const h = document.createElement('h2');
        h.id = 'qz-result-title';
        h.textContent = '🎉 أحسنت!';
        h.style.cssText = 'margin:0 0 16px;font-size:28px;color:#1e293b;font-weight:700;';
        const score_p = document.createElement('p');
        score_p.textContent = 'النتيجة: ' + score + ' من ' + questions.length + ' (' + percentage + '%)';
        score_p.style.cssText = 'font-size:18px;color:#475569;margin:8px 0;';
        const xp_p = document.createElement('p');
        xp_p.textContent = 'حصلت على: +' + xp + ' XP و +' + coins + ' عملات';
        xp_p.style.cssText = 'font-size:16px;color:#10b981;font-weight:600;margin:8px 0 24px;';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = 'متابعة';
        btn.style.cssText = 'padding:14px 36px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:12px;font-weight:700;font-size:16px;cursor:pointer;';
        btn.onclick = function() { window.location.href = '{{ route('student.dashboard') }}'; };
        card.append(h, score_p, xp_p, btn);
        overlay.appendChild(card);
        document.body.appendChild(overlay);
        btn.focus();
    }

    document.getElementById('nextBtn').addEventListener('click', nextQuestion);
    
    // Add shake animation
    const style = document.createElement('style');
    style.textContent = '@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-8px); } 75% { transform: translateX(8px); } }';
    document.head.appendChild(style);
    
    loadQuestion();
</script>
@endpush
@endsection
