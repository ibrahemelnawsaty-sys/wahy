@extends('layouts.student-app')

@section('title', 'نتيجة التحدي')

@push('styles')
<style>
    .pvp-result-container { max-width: 800px; margin: 0 auto; padding: 20px; padding-bottom: 120px; }

    .result-banner { text-align: center; padding: 50px 20px; margin-bottom: 30px; border-radius: 24px; position: relative; overflow: hidden; }
    .result-banner.win { background: linear-gradient(135deg, rgba(16,185,129,0.3), rgba(52,211,153,0.15)); border: 2px solid rgba(16,185,129,0.3); }
    .result-banner.lose { background: linear-gradient(135deg, rgba(239,68,68,0.3), rgba(248,113,113,0.15)); border: 2px solid rgba(239,68,68,0.3); }
    .result-banner.draw { background: linear-gradient(135deg, rgba(251,191,36,0.3), rgba(252,211,77,0.15)); border: 2px solid rgba(251,191,36,0.3); }

    .result-icon { font-size: 72px; margin-bottom: 12px; animation: tada 1s; }
    @keyframes tada {
        0% { transform: scale(1); } 10% { transform: scale(0.9) rotate(-3deg); }
        20%, 40%, 60%, 80% { transform: scale(1.1) rotate(3deg); } 30%, 50%, 70% { transform: scale(1.1) rotate(-3deg); }
        100% { transform: scale(1); }
    }
    .result-text { font-size: 32px; font-weight: 900; color: white; }
    .result-sub { color: rgba(255,255,255,0.7); margin-top: 8px; font-size: 16px; }

    .vs-scoreboard { display: grid; grid-template-columns: 1fr auto 1fr; gap: 16px; align-items: center; margin-bottom: 30px; }
    .player-score { background: rgba(255,255,255,0.08); border-radius: 20px; padding: 28px; text-align: center; border: 1px solid rgba(255,255,255,0.12); }
    .player-score.winner { border-color: #10b981; box-shadow: 0 0 30px rgba(16,185,129,0.2); }
    .player-name { color: white; font-weight: 700; font-size: 18px; margin-bottom: 8px; }
    .player-percent { font-size: 48px; font-weight: 900; color: white; }
    .player-time { color: rgba(255,255,255,0.5); font-size: 14px; margin-top: 6px; }
    .vs-divider { font-size: 28px; color: #fbbf24; font-weight: 900; }

    .result-details { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 25px; }
    .detail-card { background: rgba(255,255,255,0.06); border-radius: 14px; padding: 18px; text-align: center; border: 1px solid rgba(255,255,255,0.08); }
    .detail-val { font-size: 24px; font-weight: 800; color: white; }
    .detail-lbl { font-size: 12px; color: rgba(255,255,255,0.5); margin-top: 4px; }

    .result-actions { display: flex; gap: 14px; justify-content: center; margin-top: 25px; }
    .result-btn { padding: 14px 30px; border-radius: 14px; font-weight: 700; font-size: 15px; text-decoration: none; transition: transform 0.2s; }
    .result-btn:hover { transform: scale(1.03); }
    .btn-pvp { background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; }
    .btn-back { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 900px; margin: 0 auto;">
<div class="pvp-result-container fade-in">
    @php
        $isWinner = $match->winner_id === $student->id;
        $isDraw = $match->winner_id === null;
        $resultClass = $isDraw ? 'draw' : ($isWinner ? 'win' : 'lose');
    @endphp

    <div class="result-banner {{ $resultClass }}">
        <div class="result-icon">
            {{ $isDraw ? '🤝' : ($isWinner ? '🏆' : '😔') }}
        </div>
        <div class="result-text">
            {{ $isDraw ? 'تعادل!' : ($isWinner ? 'فوز! مبروك! 🎉' : 'خسارة — حظ أوفر!') }}
        </div>
        @if($isWinner)
            <div class="result-sub">حصلت على 20 نقطة إضافية! ✨</div>
        @endif
    </div>

    <div class="vs-scoreboard">
        <div class="player-score {{ $match->winner_id === $match->player1_id ? 'winner' : '' }}">
            <div class="player-name">{{ $match->player1->name ?? 'لاعب 1' }}</div>
            <div class="player-percent">{{ $match->player1_score }}%</div>
            <div class="player-time">⏱️ {{ $match->player1_time ? gmdate('i:s', $match->player1_time) : '-' }}</div>
            @if($match->winner_id === $match->player1_id) <div style="color: #6ee7b7; font-weight: 700; margin-top: 6px;">🏆 الفائز</div> @endif
        </div>
        <div class="vs-divider">VS</div>
        <div class="player-score {{ $match->winner_id === $match->player2_id ? 'winner' : '' }}">
            <div class="player-name">{{ $match->player2->name ?? 'لاعب 2' }}</div>
            <div class="player-percent">{{ $match->player2_score }}%</div>
            <div class="player-time">⏱️ {{ $match->player2_time ? gmdate('i:s', $match->player2_time) : '-' }}</div>
            @if($match->winner_id === $match->player2_id) <div style="color: #6ee7b7; font-weight: 700; margin-top: 6px;">🏆 الفائز</div> @endif
        </div>
    </div>

    <div class="result-details">
        <div class="detail-card">
            <div class="detail-val">{{ $match->challenge->title ?? '' }}</div>
            <div class="detail-lbl">التحدي</div>
        </div>
        <div class="detail-card">
            <div class="detail-val">{{ count($match->challenge->questions ?? []) }}</div>
            <div class="detail-lbl">عدد الأسئلة</div>
        </div>
    </div>

    <div class="result-actions">
        <a href="{{ route('student.pvp.lobby') }}" class="result-btn btn-pvp">⚔️ تحدي آخر</a>
        <a href="{{ route('student.practice') }}" class="result-btn btn-back">📝 العودة للتمارين</a>
    </div>
</div>
</div>
@endsection
