@extends('layouts.student-app')

@section('title', 'تحدي PvP')

@push('styles')
<style>
    .pvp-play-container { max-width: 800px; margin: 0 auto; padding: 20px; padding-bottom: 120px; }
    .vs-header { display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, rgba(139,92,246,0.2), rgba(236,72,153,0.2)); border-radius: 20px; padding: 20px 28px; margin-bottom: 20px; border: 1px solid rgba(139,92,246,0.3); }
    .vs-player { text-align: center; }
    .vs-name { color: white; font-weight: 700; font-size: 16px; }
    .vs-badge { font-size: 32px; }
    .vs-icon { font-size: 36px; color: #fbbf24; font-weight: 900; }

    .pvp-timer { text-align: center; margin-bottom: 16px; }
    .pvp-timer-bar { height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; margin-bottom: 8px; }
    .pvp-timer-fill { height: 100%; background: linear-gradient(90deg, #10b981, #fbbf24, #ef4444); border-radius: 3px; transition: width 0.1s linear; }
    .pvp-timer-text { color: white; font-weight: 800; font-size: 20px; }

    .pvp-question { background: rgba(255,255,255,0.08); border-radius: 18px; padding: 28px; border: 1px solid rgba(255,255,255,0.12); margin-bottom: 16px; display: none; }
    .pvp-question.active { display: block; animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .pvp-q-num { color: rgba(255,255,255,0.5); font-size: 13px; font-weight: 700; margin-bottom: 10px; }
    .pvp-q-text { font-size: 18px; font-weight: 700; color: white; margin-bottom: 20px; line-height: 1.7; }

    .pvp-options { display: grid; gap: 10px; }
    .pvp-option { display: flex; align-items: center; gap: 14px; padding: 16px 20px; border-radius: 14px; border: 2px solid rgba(255,255,255,0.12); cursor: pointer; transition: all 0.2s; color: white; font-size: 15px; }
    .pvp-option:hover { background: rgba(255,255,255,0.08); }
    .pvp-option.selected { background: rgba(102,126,234,0.2); border-color: #667eea; }

    .pvp-progress { display: flex; gap: 6px; justify-content: center; margin-bottom: 16px; }
    .pvp-dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,0.15); transition: background 0.3s; }
    .pvp-dot.answered { background: #667eea; }
    .pvp-dot.current { background: #fbbf24; transform: scale(1.3); }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 900px; margin: 0 auto;">
<div class="pvp-play-container fade-in">
    <div class="vs-header">
        <div class="vs-player">
            <div class="vs-badge">🧑</div>
            <div class="vs-name">{{ $match->player1->name ?? 'لاعب 1' }}</div>
        </div>
        <div class="vs-icon">⚡VS⚡</div>
        <div class="vs-player">
            <div class="vs-badge">🧑</div>
            <div class="vs-name">{{ $match->player2->name ?? 'لاعب 2' }}</div>
        </div>
    </div>

    <div class="pvp-progress" id="progressDots">
        @foreach($questions as $i => $q)
            <div class="pvp-dot {{ $i === 0 ? 'current' : '' }}" id="dot-{{ $i }}"></div>
        @endforeach
    </div>

    <div class="pvp-timer">
        <div class="pvp-timer-bar"><div class="pvp-timer-fill" id="timerFill" style="width: 100%"></div></div>
        <div class="pvp-timer-text" id="timerText">{{ $match->challenge->time_limit }}</div>
    </div>

    @foreach($questions as $index => $question)
    <div class="pvp-question {{ $index === 0 ? 'active' : '' }}" id="question-{{ $index }}" data-qid="{{ $question['key'] }}">
        <div class="pvp-q-num">السؤال {{ $index + 1 }} من {{ $questions->count() }} · <span style="color:#fbbf24;">{{ $question['points'] }} نقطة</span></div>
        <div class="pvp-q-text">{{ $question['text'] }}</div>

        @if($question['type'] === 'multiple_choice')
            <div class="pvp-options">
                @foreach($question['options'] as $oi => $opt)
                <div class="pvp-option" onclick="selectPvpAnswer({{ $index }}, '{{ $question['key'] }}', '{{ $oi }}', this)">
                    <span style="font-weight: 700; opacity: 0.5;">{{ chr(65 + $oi) }}</span>
                    <span>{{ $opt['text'] ?? '' }}</span>
                </div>
                @endforeach
            </div>
        @elseif($question['type'] === 'true_false')
            <div class="pvp-options" style="grid-template-columns: 1fr 1fr;">
                <div class="pvp-option" onclick="selectPvpAnswer({{ $index }}, '{{ $question['key'] }}', 'true', this)">✅ صح</div>
                <div class="pvp-option" onclick="selectPvpAnswer({{ $index }}, '{{ $question['key'] }}', 'false', this)">❌ خطأ</div>
            </div>
        @endif
    </div>
    @endforeach
</div>
</div>

<script>
const totalQuestions = {{ $questions->count() }};
const timePerQuestion = {{ $match->challenge->time_limit }};
const matchId = {{ $match->id }};
let currentQuestion = 0;
let answers = {};
let times = {};                 // زمن الإجابة لكل سؤال (ثواني) — للتسجيل حسب السرعة
let questionStartTime = Date.now();
let totalTimeElapsed = 0;
let questionTimer = null;

function startQuestionTimer() {
    questionStartTime = Date.now();   // بداية توقيت السؤال الحالي
    let timeLeft = timePerQuestion;
    const fill = document.getElementById('timerFill');
    const text = document.getElementById('timerText');

    clearInterval(questionTimer);
    questionTimer = setInterval(() => {
        timeLeft -= 0.1;
        totalTimeElapsed += 0.1;
        fill.style.width = (timeLeft / timePerQuestion * 100) + '%';
        text.textContent = Math.ceil(timeLeft);

        if (timeLeft <= 5) fill.style.background = '#ef4444';
        else fill.style.background = 'linear-gradient(90deg, #10b981, #fbbf24, #ef4444)';

        if (timeLeft <= 0) {
            clearInterval(questionTimer);
            nextQuestion();
        }
    }, 100);
}

function selectPvpAnswer(index, qId, answer, el) {
    el.closest('.pvp-options').querySelectorAll('.pvp-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    answers[qId] = answer;
    // زمن الإجابة لهذا السؤال (ثواني) — كلما أسرع، درجة أعلى
    times[qId] = (Date.now() - questionStartTime) / 1000;

    document.getElementById('dot-' + index).classList.add('answered');

    setTimeout(() => nextQuestion(), 500);
}

function nextQuestion() {
    clearInterval(questionTimer);
    document.getElementById('dot-' + currentQuestion).classList.remove('current');

    currentQuestion++;
    if (currentQuestion >= totalQuestions) {
        submitPvpAnswers();
        return;
    }

    document.querySelectorAll('.pvp-question').forEach(q => q.classList.remove('active'));
    document.getElementById('question-' + currentQuestion).classList.add('active');
    document.getElementById('dot-' + currentQuestion).classList.add('current');
    startQuestionTimer();
}

function submitPvpAnswers() {
    fetch(`/student/pvp/${matchId}/submit`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ answers: answers, times: times, time_taken: Math.round(totalTimeElapsed) })
    })
    .then(r => r.json())
    .then(data => {
        if (data.both_submitted) {
            window.location.href = `/student/pvp/${matchId}/result`;
        } else {
            // انتظار الخصم
            waitForOpponent();
        }
    });
}

function waitForOpponent() {
    document.querySelector('.pvp-play-container').innerHTML = `
        <div style="text-align: center; padding: 60px;">
            <div style="width: 50px; height: 50px; border: 4px solid rgba(255,255,255,0.1); border-top-color: #8b5cf6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
            <div style="font-size: 22px; font-weight: 700; color: white;">⏳ في انتظار الخصم...</div>
            <div style="color: rgba(255,255,255,0.5); margin-top: 8px;">أنهيت إجاباتك! في انتظار الخصم لإنهاء إجاباته</div>
        </div>
    `;

    // تخزين المرجع على window لتمكين التنظيف عند مغادرة الصفحة
    if (window.__pvpPollInterval) {
        clearInterval(window.__pvpPollInterval);
        window.__pvpPollInterval = null;
    }
    window.__pvpPollInterval = setInterval(() => {
        if (document.hidden) return; // إيقاف عند إخفاء التبويب لتوفير البطارية
        fetch(`/student/pvp/${matchId}/status`)
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (data && data.status === 'completed') {
                    clearInterval(window.__pvpPollInterval);
                    window.__pvpPollInterval = null;
                    window.location.href = `/student/pvp/${matchId}/result`;
                }
            })
            .catch(err => console.error('PvP status poll failed:', err));
    }, 3000);

    // تنظيف عند مغادرة الصفحة لمنع memory leak
    window.addEventListener('pagehide', () => {
        if (window.__pvpPollInterval) {
            clearInterval(window.__pvpPollInterval);
            window.__pvpPollInterval = null;
        }
    }, { once: true });
}

startQuestionTimer();
</script>
@endsection
