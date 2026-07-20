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
                <a href="{{ route('student.pvp.lobby') }}" class="pvp-btn">⚔️ ادخل التحدي الآن</a>
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
@endsection
