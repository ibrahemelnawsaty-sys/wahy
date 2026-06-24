@extends('layouts.student-app')

@section('title', $exercise->title)

@push('styles')
<style>
    .quiz-container { max-width: 800px; margin: 0 auto; padding: 20px; padding-bottom: 120px; }
    .quiz-header { background: rgba(255,255,255,0.08); backdrop-filter: blur(10px); border-radius: 20px; padding: 24px; border: 1px solid rgba(255,255,255,0.12); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
    .quiz-title { font-size: 20px; font-weight: 700; color: white; }
    .quiz-timer { background: linear-gradient(135deg, #ef4444, #dc2626); padding: 10px 22px; border-radius: 14px; color: white; font-weight: 800; font-size: 18px; }

    .question-card { background: rgba(255,255,255,0.08); backdrop-filter: blur(10px); border-radius: 18px; padding: 28px; border: 1px solid rgba(255,255,255,0.12); margin-bottom: 18px; }
    .question-number { color: rgba(255,255,255,0.5); font-size: 13px; font-weight: 700; margin-bottom: 8px; }
    .question-text { font-size: 17px; font-weight: 600; color: white; margin-bottom: 18px; line-height: 1.7; }

    .options-list { display: grid; gap: 10px; }
    .option-label { display: flex; align-items: center; gap: 14px; padding: 14px 18px; border-radius: 12px; border: 2px solid rgba(255,255,255,0.12); cursor: pointer; transition: all 0.2s; color: white; }
    .option-label:hover { background: rgba(255,255,255,0.08); border-color: rgba(102,126,234,0.5); }
    .option-label.selected { background: rgba(102,126,234,0.15); border-color: #667eea; }
    .option-radio { width: 20px; height: 20px; accent-color: #667eea; }
    .option-text { flex: 1; font-size: 15px; }

    .tf-options { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .tf-btn { padding: 14px; border-radius: 12px; border: 2px solid rgba(255,255,255,0.12); text-align: center; cursor: pointer; font-weight: 700; font-size: 16px; color: white; transition: all 0.2s; }
    .tf-btn:hover { border-color: rgba(102,126,234,0.5); }
    .tf-btn.selected { background: rgba(102,126,234,0.15); border-color: #667eea; }

    .short-input { width: 100%; padding: 14px 18px; border-radius: 12px; border: 2px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.05); color: white; font-size: 15px; font-family: inherit; }
    .short-input:focus { outline: none; border-color: #667eea; }

    .submit-section { text-align: center; margin-top: 30px; }
    .submit-btn { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 16px 50px; border-radius: 16px; border: none; font-weight: 800; font-size: 18px; cursor: pointer; box-shadow: 0 8px 30px rgba(102,126,234,0.4); transition: transform 0.2s; }
    .submit-btn:hover { transform: scale(1.05); }

    .progress-bar { height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; margin-bottom: 20px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); border-radius: 2px; transition: width 0.3s; }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 900px; margin: 0 auto;">
<div class="quiz-container fade-in">
    <form id="quizForm" action="{{ route('student.practice.submit', $exercise->id) }}" method="POST">
        @csrf
        <input type="hidden" name="time_taken" id="timeTaken" value="0">

        <div class="quiz-header">
            <div>
                <div class="quiz-title">{{ $exercise->title }}</div>
                <div style="color: rgba(255,255,255,0.5); font-size: 13px; margin-top: 4px;">
                    محاولة {{ $attemptsCount + 1 }} من {{ $exercise->max_attempts }} • {{ $questions->count() }} سؤال
                </div>
            </div>
            @if($exercise->time_limit)
                <div class="quiz-timer" id="timer">{{ $exercise->time_limit }}:00</div>
            @endif
        </div>

        <div class="progress-bar"><div class="progress-fill" id="progressFill" style="width: 0%"></div></div>

        @foreach($questions as $index => $question)
        <div class="question-card">
            <div class="question-number">السؤال {{ $index + 1 }} من {{ $questions->count() }}</div>
            <div class="question-text">{{ $question->question_text }}</div>

            @if($question->question_type === 'multiple_choice')
                @php $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []); @endphp
                <div class="options-list">
                    @foreach($options as $oi => $opt)
                    <label class="option-label" onclick="selectOption(this, 'q_{{ $question->id }}')">
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $oi }}" class="option-radio" onchange="updateProgress()">
                        <span class="option-text">{{ $opt['text'] ?? '' }}</span>
                    </label>
                    @endforeach
                </div>
            @elseif($question->question_type === 'true_false')
                <div class="tf-options">
                    <label class="tf-btn" onclick="selectTF(this, 'tf_{{ $question->id }}')">
                        <input type="radio" name="answers[{{ $question->id }}]" value="true" style="display:none" onchange="updateProgress()">
                        ✅ صح
                    </label>
                    <label class="tf-btn" onclick="selectTF(this, 'tf_{{ $question->id }}')">
                        <input type="radio" name="answers[{{ $question->id }}]" value="false" style="display:none" onchange="updateProgress()">
                        ❌ خطأ
                    </label>
                </div>
            @else
                <input type="text" name="answers[{{ $question->id }}]" class="short-input" placeholder="اكتب إجابتك هنا..." onkeyup="updateProgress()">
            @endif
        </div>
        @endforeach

        <div class="submit-section">
            <button type="submit" class="submit-btn" onclick="document.getElementById('timeTaken').value=elapsedSeconds">📤 إرسال الإجابات</button>
        </div>
    </form>
</div>
</div>

<script>
let elapsedSeconds = 0;
const totalQuestions = {{ $questions->count() }};

setInterval(() => { elapsedSeconds++; }, 1000);

function selectOption(el, group) {
    el.closest('.options-list').querySelectorAll('.option-label').forEach(l => l.classList.remove('selected'));
    el.classList.add('selected');
}
function selectTF(el, group) {
    el.closest('.tf-options').querySelectorAll('.tf-btn').forEach(l => l.classList.remove('selected'));
    el.classList.add('selected');
}
function updateProgress() {
    const answered = document.querySelectorAll('input[type="radio"]:checked, input[type="text"]').length;
    let count = document.querySelectorAll('input[type="radio"]:checked').length;
    document.querySelectorAll('.short-input').forEach(i => { if(i.value.trim()) count++; });
    document.getElementById('progressFill').style.width = (count / totalQuestions * 100) + '%';
}

@if($exercise->time_limit)
let timeLeft = {{ $exercise->time_limit * 60 }};
const timerEl = document.getElementById('timer');
const countdown = setInterval(() => {
    timeLeft--;
    const m = Math.floor(timeLeft / 60);
    const s = timeLeft % 60;
    timerEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
    if (timeLeft <= 30) timerEl.style.animation = 'pulse 0.5s infinite';
    if (timeLeft <= 0) {
        clearInterval(countdown);
        document.getElementById('timeTaken').value = elapsedSeconds;
        document.getElementById('quizForm').submit();
    }
}, 1000);
@endif
</script>
@endsection
