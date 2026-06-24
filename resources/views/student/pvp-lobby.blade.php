@extends('layouts.student-app')

@section('title', 'تحدي PvP')

@push('styles')
<style>
    .pvp-container { max-width: 900px; margin: 0 auto; padding: 20px; padding-bottom: 120px; }
    .pvp-hero { text-align: center; margin-bottom: 30px; }
    .pvp-hero-icon { font-size: 64px; margin-bottom: 10px; animation: bounce 2s infinite; }
    @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    .pvp-hero-title { font-size: 32px; font-weight: 900; color: white; }
    .pvp-hero-sub { color: rgba(255,255,255,0.6); margin-top: 6px; }

    .pvp-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 30px; }
    .pvp-stat { background: rgba(255,255,255,0.08); border-radius: 16px; padding: 22px; text-align: center; border: 1px solid rgba(255,255,255,0.1); }
    .pvp-stat-val { font-size: 36px; font-weight: 800; color: white; }
    .pvp-stat-lbl { font-size: 13px; color: rgba(255,255,255,0.6); }

    .challenge-card { background: linear-gradient(135deg, rgba(139,92,246,0.2), rgba(236,72,153,0.2)); backdrop-filter: blur(10px); border-radius: 20px; padding: 30px; border: 1px solid rgba(139,92,246,0.3); margin-bottom: 16px; position: relative; overflow: hidden; }
    .challenge-card::after { content: '⚔️'; position: absolute; top: -10px; left: -10px; font-size: 80px; opacity: 0.08; }
    .challenge-title { font-size: 20px; font-weight: 800; color: white; margin-bottom: 8px; position: relative; z-index: 1; }
    .challenge-info { display: flex; justify-content: center; gap: 20px; color: rgba(255,255,255,0.6); font-size: 13px; margin-bottom: 18px; position: relative; z-index: 1; }
    .challenge-btn { display: inline-block; background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; padding: 14px 36px; border-radius: 14px; border: none; font-weight: 800; font-size: 16px; cursor: pointer; position: relative; z-index: 1; box-shadow: 0 8px 25px rgba(139,92,246,0.4); transition: transform 0.2s; }
    .challenge-btn:hover { transform: scale(1.05); }
    .challenge-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .waiting-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.85); z-index: 999; justify-content: center; align-items: center; flex-direction: column; }
    .waiting-overlay.active { display: flex; }
    .waiting-spinner { width: 60px; height: 60px; border: 4px solid rgba(255,255,255,0.1); border-top-color: #8b5cf6; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 20px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .waiting-text { color: white; font-size: 22px; font-weight: 700; }
    .waiting-sub { color: rgba(255,255,255,0.5); font-size: 14px; margin-top: 8px; }
    .cancel-btn { margin-top: 30px; background: rgba(255,255,255,0.1); color: white; padding: 10px 30px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.2); cursor: pointer; font-weight: 600; }

    .match-card { background: rgba(255,255,255,0.06); border-radius: 14px; padding: 16px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.08); }
    .match-players { color: white; font-weight: 600; font-size: 14px; }
    .match-result { padding: 4px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; }
    .match-win { background: rgba(16,185,129,0.2); color: #6ee7b7; }
    .match-lose { background: rgba(239,68,68,0.2); color: #fca5a5; }
    .match-draw { background: rgba(251,191,36,0.2); color: #fcd34d; }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 900px; margin: 0 auto;">
<div class="pvp-container fade-in">
    <div class="pvp-hero">
        <div class="pvp-hero-icon">⚔️</div>
        <h1 class="pvp-hero-title">تحدي طالب ضد طالب</h1>
        <p class="pvp-hero-sub">اختر تحدي وتنافس مع طالب آخر بالسرعة والدقة!</p>
    </div>

    <div class="pvp-stats">
        <div class="pvp-stat">
            <div class="pvp-stat-val">{{ $stats['total_matches'] }}</div>
            <div class="pvp-stat-lbl">مبارياتي</div>
        </div>
        <div class="pvp-stat">
            <div class="pvp-stat-val">{{ $stats['wins'] }} 🏆</div>
            <div class="pvp-stat-lbl">انتصارات</div>
        </div>
    </div>

    <h3 style="color: white; font-size: 20px; font-weight: 700; margin-bottom: 16px;">🎮 التحديات المتاحة</h3>
    @forelse($challenges as $challenge)
    <div class="challenge-card">
        <div class="challenge-title">🏆 {{ $challenge->title }}</div>
        <div class="challenge-info">
            <span>📋 {{ count($challenge->questions ?? []) }} سؤال</span>
            <span>⏱️ {{ $challenge->time_limit }} ثانية/سؤال</span>
        </div>
        <div style="text-align: center; position: relative; z-index: 1;">
            <button class="challenge-btn" onclick="joinChallenge({{ $challenge->id }})">⚔️ ابدأ التحدي</button>
        </div>
    </div>
    @empty
    <div style="text-align: center; padding: 50px; color: rgba(255,255,255,0.5);">
        <div style="font-size: 48px; margin-bottom: 10px;">🎮</div>
        <p>لا توجد تحديات متاحة حالياً</p>
    </div>
    @endforelse

    @if($myMatches->count() > 0)
    <h3 style="color: white; font-size: 20px; font-weight: 700; margin: 30px 0 16px;">📊 آخر مبارياتي</h3>
    @foreach($myMatches as $match)
    <div class="match-card">
        <div>
            <div class="match-players">{{ $match->player1->name ?? '?' }} ⚡ {{ $match->player2->name ?? '?' }}</div>
            <div style="font-size: 12px; color: rgba(255,255,255,0.4); margin-top: 4px;">{{ $match->challenge->title ?? '' }} • {{ $match->completed_at?->diffForHumans() }}</div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <span style="color: white; font-weight: 700;">{{ $match->player1_score }}% - {{ $match->player2_score }}%</span>
            @if($match->winner_id === Auth::id())
                <span class="match-result match-win">فوز 🏆</span>
            @elseif($match->winner_id)
                <span class="match-result match-lose">خسارة</span>
            @else
                <span class="match-result match-draw">تعادل</span>
            @endif
        </div>
    </div>
    @endforeach
    @endif
</div>
</div>

{{-- Waiting Overlay --}}
<div class="waiting-overlay" id="waitingOverlay">
    <div class="waiting-spinner"></div>
    <div class="waiting-text">🔍 جاري البحث عن خصم...</div>
    <div class="waiting-sub" id="waitingStatus">في انتظار طالب آخر ينضم للتحدي</div>
    <button class="cancel-btn" onclick="cancelWaiting()">إلغاء</button>
</div>

<script>
let pollingInterval = null;
let currentMatchId = null;

function joinChallenge(challengeId) {
    document.getElementById('waitingOverlay').classList.add('active');

    fetch(`/student/pvp/${challengeId}/join`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            currentMatchId = data.match_id;
            if (data.status === 'playing') {
                window.location.href = `/student/pvp/${data.match_id}/play`;
            } else {
                startPolling(data.match_id);
            }
        }
    });
}

function startPolling(matchId) {
    pollingInterval = setInterval(() => {
        fetch(`/student/pvp/${matchId}/status`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'playing') {
                clearInterval(pollingInterval);
                document.getElementById('waitingStatus').textContent = '🎮 تم العثور على خصم: ' + data.player2 + '!';
                setTimeout(() => {
                    window.location.href = `/student/pvp/${matchId}/play`;
                }, 1500);
            }
        });
    }, 3000);
}

function cancelWaiting() {
    if (pollingInterval) clearInterval(pollingInterval);
    document.getElementById('waitingOverlay').classList.remove('active');
}
</script>
@endsection
