@extends('layouts.student-app')

@section('title', 'التحديات')

@push('styles')
<style>
    .practice-container { max-width: 1200px; margin: 0 auto; padding: 20px; padding-bottom: 120px; }
    .practice-hero { text-align: center; margin-bottom: 30px; }
    .practice-title { font-size: 32px; font-weight: 800; color: white; margin-bottom: 8px; }
    .practice-subtitle { color: rgba(255,255,255,0.7); font-size: 15px; }

    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 30px; }
    .stat-card { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 16px; padding: 20px; text-align: center; border: 1px solid rgba(255,255,255,0.15); }
    .stat-value { font-size: 32px; font-weight: 800; color: white; }
    .stat-label { font-size: 13px; color: rgba(255,255,255,0.7); margin-top: 4px; }

    .section-title { font-size: 22px; font-weight: 700; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }

    .exercise-card { background: rgba(255,255,255,0.08); backdrop-filter: blur(10px); border-radius: 16px; padding: 22px; border: 1px solid rgba(255,255,255,0.12); margin-bottom: 14px; transition: all 0.3s; cursor: pointer; }
    .exercise-card:hover { background: rgba(255,255,255,0.14); transform: translateY(-2px); }
    .exercise-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .exercise-title { font-size: 17px; font-weight: 700; color: white; }
    .exercise-badge { padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; }
    .badge-easy { background: rgba(16,185,129,0.2); color: #6ee7b7; }
    .badge-medium { background: rgba(251,191,36,0.2); color: #fcd34d; }
    .badge-hard { background: rgba(239,68,68,0.2); color: #fca5a5; }
    .exercise-meta { display: flex; gap: 16px; color: rgba(255,255,255,0.6); font-size: 13px; }
    .exercise-teacher { color: rgba(255,255,255,0.5); font-size: 12px; margin-top: 8px; }

    .pvp-section { margin-top: 30px; }
    .pvp-card { background: linear-gradient(135deg, rgba(139,92,246,0.3), rgba(236,72,153,0.3)); backdrop-filter: blur(10px); border-radius: 20px; padding: 28px; border: 1px solid rgba(139,92,246,0.3); margin-bottom: 14px; text-align: center; position: relative; overflow: hidden; }
    .pvp-card::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: conic-gradient(transparent, rgba(139,92,246,0.1), transparent, rgba(236,72,153,0.1)); animation: pvpRotate 8s linear infinite; }
    @keyframes pvpRotate { to { transform: rotate(360deg); } }
    .pvp-title { font-size: 22px; font-weight: 800; color: white; position: relative; z-index: 1; }
    .pvp-desc { color: rgba(255,255,255,0.7); font-size: 14px; margin: 10px 0 20px; position: relative; z-index: 1; }
    .pvp-btn { display: inline-block; background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; padding: 12px 32px; border-radius: 14px; font-weight: 700; font-size: 16px; border: none; cursor: pointer; position: relative; z-index: 1; text-decoration: none; transition: transform 0.2s; }
    .pvp-btn:hover { transform: scale(1.05); }
    .pvp-info { display: flex; justify-content: center; gap: 24px; margin-top: 14px; color: rgba(255,255,255,0.6); font-size: 13px; position: relative; z-index: 1; }

    .empty-state { text-align: center; padding: 50px; color: rgba(255,255,255,0.5); }
    .empty-icon { font-size: 48px; margin-bottom: 10px; }

    .pvp-btn { border: none; }
    .pvp-alt-link { color: rgba(255,255,255,0.75); font-size: 13px; text-decoration: none; border-bottom: 1px dashed rgba(255,255,255,0.35); padding-bottom: 2px; }
    .pvp-alt-link:hover { color: #fff; }

    /* شاشة انتظار مطابقة الخصم (نفس تجربة الصالة) */
    .waiting-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.85); z-index: 999; justify-content: center; align-items: center; flex-direction: column; }
    .waiting-overlay.active { display: flex; }
    .waiting-spinner { width: 60px; height: 60px; border: 4px solid rgba(255,255,255,0.1); border-top-color: #8b5cf6; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 20px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .waiting-text { color: white; font-size: 22px; font-weight: 700; }
    .waiting-sub { color: rgba(255,255,255,0.5); font-size: 14px; margin-top: 8px; }
    .cancel-btn { margin-top: 30px; background: rgba(255,255,255,0.1); color: white; padding: 10px 30px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.2); cursor: pointer; font-weight: 600; }

    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
<div class="practice-container fade-in">
    {{-- Hero --}}
    <div class="practice-hero">
        <div style="font-size: 48px; margin-bottom: 8px;">⚔️</div>
        <h1 class="practice-title">التحديات</h1>
        <p class="practice-subtitle">تنافس مع زملائك في تحديات مباشرة — الأسرع والأدقّ يفوز!</p>
    </div>

    {{-- الإحصائيات --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $practiceStats['pvp_wins'] }}</div>
            <div class="stat-label">انتصارات</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $practiceStats['matches_played'] }}</div>
            <div class="stat-label">مباريات لعبتها</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $practiceStats['available'] }}</div>
            <div class="stat-label">تحديات متاحة</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">⚔️</div>
            <div class="stat-label">استعدّ للتحدي!</div>
        </div>
    </div>

    {{-- تحديات PvP --}}
    <div class="pvp-section">
        <h2 class="section-title">⚔️ التحديات المتاحة</h2>
        @forelse($pvpChallenges as $challenge)
            <div class="pvp-card">
                <div class="pvp-title">🏆 {{ $challenge->title }}</div>
                <div class="pvp-desc">ادخل التحدي وتنافس مع طالب آخر — الأسرع والأدق يفوز!</div>
                <button type="button" class="pvp-btn" onclick="joinChallenge({{ $challenge->id }})">⚔️ ادخل التحدي الآن</button>
                <div style="margin-top:12px;position:relative;z-index:1;">
                    <a href="{{ route('student.pvp.lobby') }}" class="pvp-alt-link">🎯 اختر منافساً محدّداً أو تابع مبارياتك</a>
                </div>
                <div class="pvp-info">
                    <span>📋 {{ $challenge->question_count }} سؤال</span>
                    <span>⏱️ {{ $challenge->time_limit }} ثانية/سؤال</span>
                    <span>🎮 {{ $challenge->matches_count }} مباراة</span>
                </div>
            </div>
        @empty
            <div class="pvp-card" style="background: rgba(255,255,255,0.05);">
                <div style="font-size: 48px; position: relative; z-index: 1;">⚔️</div>
                <div class="pvp-title">قريباً</div>
                <div class="pvp-desc">تحديات طالب ضد طالب ستكون متاحة قريباً!</div>
            </div>
        @endforelse
    </div>
</div>
</div>

{{-- شاشة انتظار مطابقة الخصم --}}
<div class="waiting-overlay" id="waitingOverlay">
    <div class="waiting-spinner"></div>
    <div class="waiting-text" id="waitingText">🔍 جاري البحث عن خصم...</div>
    <div class="waiting-sub" id="waitingStatus">في انتظار طالب آخر ينضم للتحدي</div>
    <button class="cancel-btn" onclick="cancelWaiting()">إلغاء</button>
</div>

<script>
const PVP_CSRF = '{{ csrf_token() }}';
let pollingInterval = null;
let currentMatchId = null;

// بدء التحدي مباشرةً: مطابقة عشوائية → لعب فور توفّر خصم (بلا صفحة وسيطة)
function joinChallenge(challengeId) {
    showWaiting('🔍 جاري البحث عن خصم...', 'في انتظار طالب آخر ينضم للتحدي');
    fetch(`/student/pvp/${challengeId}/join`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': PVP_CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' }
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
        } else { hideWaiting(); }
    })
    .catch(() => hideWaiting());
}

function startPolling(matchId) {
    pollingInterval = setInterval(() => {
        fetch(`/student/pvp/${matchId}/status`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'playing') {
                clearInterval(pollingInterval);
                document.getElementById('waitingText').textContent = '🎮 المباراة جاهزة!';
                document.getElementById('waitingStatus').textContent = 'جارٍ نقلك للّعب…';
                setTimeout(() => { window.location.href = `/student/pvp/${matchId}/play`; }, 1200);
            } else if (data.status === 'declined') {
                clearInterval(pollingInterval);
                document.getElementById('waitingText').textContent = '😔 اعتذر منافسك';
                document.getElementById('waitingStatus').textContent = 'لم يقبل التحدي هذه المرة.';
            }
        })
        .catch(() => {});
    }, 3000);
}

function showWaiting(text, sub) {
    document.getElementById('waitingText').textContent = text;
    document.getElementById('waitingStatus').textContent = sub;
    document.getElementById('waitingOverlay').classList.add('active');
}
function hideWaiting() {
    document.getElementById('waitingOverlay').classList.remove('active');
}
function cancelWaiting() {
    if (pollingInterval) clearInterval(pollingInterval);
    hideWaiting();
}
</script>
@endsection
