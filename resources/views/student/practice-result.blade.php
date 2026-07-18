@extends('layouts.student-app')

@section('title', 'نتيجة التمرين')

@push('styles')
<style>
    .result-container { max-width: 800px; margin: 0 auto; padding: 20px; padding-bottom: 120px; }
    .result-hero { text-align: center; padding: 40px; margin-bottom: 25px; }
    .result-score { font-size: 72px; font-weight: 900; background: linear-gradient(135deg, {{ $attempt->score >= 80 ? '#10b981, #34d399' : ($attempt->score >= 60 ? '#f59e0b, #fbbf24' : '#ef4444, #f87171') }}); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .result-label { font-size: 18px; color: rgba(255,255,255,0.7); margin-top: 8px; }
    .result-emoji { font-size: 56px; margin-bottom: 12px; }

    .result-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 25px; }
    .r-stat { background: rgba(255,255,255,0.08); backdrop-filter: blur(10px); border-radius: 16px; padding: 20px; text-align: center; border: 1px solid rgba(255,255,255,0.1); }
    .r-stat-val { font-size: 28px; font-weight: 800; color: white; }
    .r-stat-lbl { font-size: 13px; color: rgba(255,255,255,0.6); margin-top: 4px; }

    .answer-card { background: rgba(255,255,255,0.06); border-radius: 14px; padding: 20px; margin-bottom: 12px; border-right: 4px solid; }
    .answer-correct { border-color: #10b981; }
    .answer-wrong { border-color: #ef4444; }
    .answer-q { font-weight: 600; color: white; margin-bottom: 8px; font-size: 15px; }
    .answer-detail { font-size: 13px; color: rgba(255,255,255,0.6); }
    .answer-explain { background: rgba(102,126,234,0.1); border-radius: 8px; padding: 10px 14px; margin-top: 10px; font-size: 13px; color: #a5b4fc; }

    .result-actions { display: flex; gap: 14px; justify-content: center; margin-top: 30px; }
    .action-btn { padding: 14px 30px; border-radius: 14px; font-weight: 700; font-size: 15px; text-decoration: none; transition: transform 0.2s; }
    .action-btn:hover { transform: scale(1.03); }
    .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
    .btn-secondary { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 900px; margin: 0 auto;">
<div class="result-container fade-in">
    <div class="result-hero">
        <div class="result-emoji">{{ $attempt->score >= 80 ? '🏆' : ($attempt->score >= 60 ? '👍' : '💪') }}</div>
        <div class="result-score">{{ $attempt->score }}%</div>
        <div class="result-label">
            {{ $attempt->score >= 90 ? 'ممتاز! أداء رائع' : ($attempt->score >= 80 ? 'جيد جداً!' : ($attempt->score >= 60 ? 'جيد، واصل المحاولة' : 'حاول مرة أخرى')) }}
        </div>
    </div>

    <div class="result-stats">
        <div class="r-stat">
            <div class="r-stat-val">{{ $attempt->correct_answers }}/{{ $attempt->total_questions }}</div>
            <div class="r-stat-lbl">إجابات صحيحة</div>
        </div>
        <div class="r-stat">
            <div class="r-stat-val">{{ $attempt->time_taken ? gmdate('i:s', $attempt->time_taken) : '-' }}</div>
            <div class="r-stat-lbl">الوقت المستغرق</div>
        </div>
        <div class="r-stat">
            <div class="r-stat-val">+{{ max(1, round($attempt->score / 10)) }}</div>
            <div class="r-stat-lbl">نقاط مكتسبة</div>
        </div>
    </div>

    <h3 style="color: white; font-size: 18px; font-weight: 700; margin-bottom: 14px;">📋 مراجعة الإجابات</h3>
    @php $gradedAnswers = $attempt->answers ?? []; @endphp
    @foreach($questions as $question)
        @php $graded = $gradedAnswers[$question->id] ?? null; @endphp
        <div class="answer-card {{ $graded && $graded['correct'] ? 'answer-correct' : 'answer-wrong' }}">
            <div class="answer-q">{{ $question->question_text }}</div>
            <div class="answer-detail">
                @if($graded)
                    {{ $graded['correct'] ? '✅ إجابتك صحيحة' : '❌ إجابتك خاطئة' }}
                    @if(!$graded['correct'] && $question->correct_answer)
                        — الإجابة الصحيحة: <strong>{{ $question->correct_answer }}</strong>
                    @endif
                @else
                    ⚠️ لم تتم الإجابة
                @endif
            </div>
            @if($question->explanation)
                <div class="answer-explain">💡 {{ $question->explanation }}</div>
            @endif
        </div>
    @endforeach

    <div class="result-actions">
        <a href="{{ route('student.practice') }}" class="action-btn btn-primary">📝 تمارين أخرى</a>
        <a href="{{ route('student.practice.start', $exercise->id) }}" class="action-btn btn-secondary">🔄 إعادة المحاولة</a>
    </div>
</div>
</div>
@endsection

@push('scripts')
@include('partials.answer-celebration')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // نفس عتبة دلالة الصفحة (👍/🏆 مقابل 💪 «حاول مرة أخرى»): 60% فأعلى احتفال، وإلا تشجيع/حزن
        var __score = {{ (int) ($attempt->score ?? 0) }};
        setTimeout(function () {
            if (__score >= 60) {
                if (window.celebrateCorrect) window.celebrateCorrect();
            } else {
                if (window.celebrateWrong) window.celebrateWrong();
            }
        }, 350);
    });
</script>
@endpush
