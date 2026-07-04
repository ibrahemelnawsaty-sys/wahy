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

    .challenge-btn-alt { background: linear-gradient(135deg, #0ea5e9, #6366f1); box-shadow: 0 8px 25px rgba(14,165,233,0.4); }
    .pvp-section { margin-bottom: 26px; }
    .pvp-h3 { color: white; font-size: 20px; font-weight: 700; margin-bottom: 14px; }
    .invite-card { background: linear-gradient(135deg, rgba(14,165,233,0.18), rgba(99,102,241,0.18)); border: 1px solid rgba(99,102,241,0.35); border-radius: 16px; padding: 16px 18px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
    .invite-from { color: white; font-weight: 800; font-size: 15px; }
    .invite-challenge { color: rgba(255,255,255,0.6); font-size: 12px; margin-top: 4px; }
    .invite-actions { display: flex; gap: 8px; }
    .btn-accept { background: linear-gradient(135deg,#10b981,#059669); color: #fff; border: none; padding: 10px 18px; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; }
    .btn-decline { background: rgba(255,255,255,0.1); color: #fca5a5; border: 1px solid rgba(239,68,68,0.4); padding: 10px 16px; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer; }
    .btn-play { background: linear-gradient(135deg,#8b5cf6,#ec4899); color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 12px; font-weight: 800; font-size: 14px; }

    .picker-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; padding: 20px; }
    .picker-overlay.active { display: flex; }
    .picker-box { background: #1e1b3a; border: 1px solid rgba(139,92,246,0.4); border-radius: 20px; padding: 24px; width: 100%; max-width: 440px; max-height: 80vh; display: flex; flex-direction: column; }
    .picker-title { color: white; font-size: 18px; font-weight: 800; margin-bottom: 6px; }
    .picker-sub { color: rgba(255,255,255,0.55); font-size: 13px; margin-bottom: 14px; }
    .picker-search { width: 100%; padding: 12px 16px; border-radius: 12px; border: 2px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.06); color: white; font-size: 15px; margin-bottom: 14px; }
    .picker-list { overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 8px; min-height: 80px; }
    .picker-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); }
    .picker-name { color: white; font-weight: 700; font-size: 15px; }
    .picker-pick { background: linear-gradient(135deg,#8b5cf6,#ec4899); color: #fff; border: none; padding: 8px 18px; border-radius: 10px; font-weight: 800; cursor: pointer; }
    .picker-empty { color: rgba(255,255,255,0.4); text-align: center; padding: 20px; }
    .picker-close { margin-top: 14px; background: rgba(255,255,255,0.1); color: white; border: none; padding: 10px; border-radius: 10px; cursor: pointer; font-weight: 600; }
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
            <div class="pvp-stat-val">{{ $pvpStats['total_matches'] ?? 0 }}</div>
            <div class="pvp-stat-lbl">مبارياتي</div>
        </div>
        <div class="pvp-stat">
            <div class="pvp-stat-val">{{ $pvpStats['wins'] ?? 0 }} 🏆</div>
            <div class="pvp-stat-lbl">انتصارات</div>
        </div>
    </div>

    @if($pendingInvites->count() > 0)
    <div class="pvp-section">
        <h3 class="pvp-h3">📨 تحديات موجّهة إليك</h3>
        @foreach($pendingInvites as $inv)
        <div class="invite-card" id="invite-{{ $inv->id }}">
            <div class="invite-info">
                <div class="invite-from">⚔️ {{ $inv->player1->name ?? 'طالب' }} يتحدّاك</div>
                <div class="invite-challenge">{{ $inv->challenge->title ?? '' }} • {{ $inv->created_at?->diffForHumans() }}</div>
            </div>
            <div class="invite-actions">
                <button class="btn-accept" onclick="respondInvite({{ $inv->id }}, 'accept', this)">قبول ✅</button>
                <button class="btn-decline" onclick="respondInvite({{ $inv->id }}, 'decline', this)">رفض</button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($readyMatches->count() > 0)
    <div class="pvp-section">
        <h3 class="pvp-h3">🎮 مباريات جاهزة للعب</h3>
        @foreach($readyMatches as $rm)
        @php $opp = $rm->player1_id === Auth::id() ? $rm->player2 : $rm->player1; @endphp
        <div class="match-card">
            <div class="match-players">ضد {{ $opp->name ?? 'خصم' }} — {{ $rm->challenge->title ?? '' }}</div>
            <a href="{{ route('student.pvp.play', $rm->id) }}" class="btn-play">العب الآن ▶</a>
        </div>
        @endforeach
    </div>
    @endif

    <h3 style="color: white; font-size: 20px; font-weight: 700; margin-bottom: 16px;">🎮 التحديات المتاحة</h3>
    @forelse($challenges as $challenge)
    <div class="challenge-card">
        <div class="challenge-title">🏆 {{ $challenge->title }}</div>
        <div class="challenge-info">
            <span>📋 {{ count($challenge->questions ?? []) }} سؤال</span>
            <span>⏱️ {{ $challenge->time_limit }} ثانية/سؤال</span>
        </div>
        <div style="text-align: center; position: relative; z-index: 1; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
            <button class="challenge-btn" onclick="joinChallenge({{ $challenge->id }})">⚔️ منافس عشوائي</button>
            <button class="challenge-btn challenge-btn-alt" onclick="openOpponentPicker({{ $challenge->id }}, @js($challenge->title))">🎯 اختر منافساً</button>
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
    <div class="waiting-text" id="waitingText">🔍 جاري البحث عن خصم...</div>
    <div class="waiting-sub" id="waitingStatus">في انتظار طالب آخر ينضم للتحدي</div>
    <button class="cancel-btn" onclick="cancelWaiting()">إلغاء</button>
</div>

{{-- Opponent Picker Modal --}}
<div class="picker-overlay" id="pickerOverlay">
    <div class="picker-box">
        <div class="picker-title" id="pickerTitle">🎯 اختر منافساً</div>
        <div class="picker-sub">ابحث عن أي طالب في المنصة وتحدَّه مباشرة</div>
        <input type="text" class="picker-search" id="pickerSearch" placeholder="ابحث بالاسم…" oninput="onOpponentSearch(this.value)">
        <div class="picker-list" id="pickerList"></div>
        <button class="picker-close" onclick="closeOpponentPicker()">إغلاق</button>
    </div>
</div>

<script>
const PVP_CSRF = '{{ csrf_token() }}';
let pollingInterval = null;
let currentMatchId = null;
let pickerChallengeId = null;
let searchTimer = null;

// ===== منافس عشوائي =====
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

// ===== اختيار منافس محدّد =====
function openOpponentPicker(challengeId, title) {
    pickerChallengeId = challengeId;
    document.getElementById('pickerTitle').textContent = '🎯 تحدَّ منافساً في: ' + title;
    document.getElementById('pickerSearch').value = '';
    document.getElementById('pickerOverlay').classList.add('active');
    loadOpponents('');
    setTimeout(() => document.getElementById('pickerSearch').focus(), 100);
}
function closeOpponentPicker() {
    document.getElementById('pickerOverlay').classList.remove('active');
    pickerChallengeId = null;
}
function onOpponentSearch(val) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadOpponents(val), 300);
}
function loadOpponents(q) {
    const list = document.getElementById('pickerList');
    list.innerHTML = '<div class="picker-empty">جارٍ التحميل…</div>';
    fetch(`/student/pvp-opponents/search?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        const ops = (data && data.opponents) || [];
        if (ops.length === 0) { list.innerHTML = '<div class="picker-empty">لا يوجد طلاب مطابقون</div>'; return; }
        list.innerHTML = ops.map(o =>
            `<div class="picker-item"><span class="picker-name">${escapeHtml(o.name)}</span>` +
            `<button class="picker-pick" onclick='pickOpponent(${o.id}, ${JSON.stringify(o.name)})'>تحدَّ ⚔️</button></div>`
        ).join('');
    })
    .catch(() => { list.innerHTML = '<div class="picker-empty">تعذّر التحميل</div>'; });
}
function pickOpponent(opponentId, name) {
    if (!pickerChallengeId) return;
    const cid = pickerChallengeId;
    closeOpponentPicker();
    showWaiting('📨 أُرسِلت الدعوة!', 'بانتظار قبول ' + name + '…');
    fetch(`/student/pvp/${cid}/challenge`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': PVP_CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ opponent_id: opponentId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { currentMatchId = data.match_id; startPolling(data.match_id); }
        else { hideWaiting(); alert(data.message || 'تعذّر إرسال الدعوة'); }
    })
    .catch(() => hideWaiting());
}

// ===== الردّ على دعوة واردة =====
function respondInvite(matchId, action, btn) {
    if (btn) btn.disabled = true;
    fetch(`/student/pvp-invite/${matchId}/${action}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': PVP_CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (action === 'accept' && data.redirect) { window.location.href = data.redirect; }
            else { const card = document.getElementById('invite-' + matchId); if (card) card.remove(); }
        } else if (btn) { btn.disabled = false; }
    })
    .catch(() => { if (btn) btn.disabled = false; });
}

// ===== polling (بعد بحث عشوائي أو إرسال دعوة) =====
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
function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
}
</script>
@endsection
