@extends('layouts.student-app')

@section('title', 'الترتيب')

@push('styles')
<style>
    .leaderboard-container {
        max-width: 800px;
        margin: 0 auto;
        padding: var(--spacing-xl) var(--spacing-md);
        padding-bottom: 120px;
    }
    
    .period-tabs {
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-xl);
    }
    
    .period-tab {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: rgba(255, 255, 255, 0.7);
        padding: 12px 24px;
        border-radius: var(--radius-full);
        cursor: pointer;
        transition: all var(--transition-base);
        font-weight: 600;
    }
    
    .period-tab.active {
        background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
        border-color: var(--color-primary);
        color: white;
    }
    
    .podium {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-2xl);
    }
    
    .podium-place {
        text-align: center;
        flex: 1;
        max-width: 200px;
    }
    
    .podium-avatar {
        width: 80px;
        height: 80px;
        border-radius: var(--radius-full);
        margin: 0 auto var(--spacing-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        border: 4px solid;
        animation: float 3s ease-in-out infinite;
    }
    
    .podium-place.first .podium-avatar {
        width: 100px;
        height: 100px;
        font-size: 48px;
        background: linear-gradient(135deg, #FFD700, #FFA500);
        border-color: #FFD700;
        box-shadow: 0 8px 24px rgba(255, 215, 0, 0.5);
    }
    
    .podium-place.second .podium-avatar {
        background: linear-gradient(135deg, #C0C0C0, #A8A8A8);
        border-color: #C0C0C0;
    }
    
    .podium-place.third .podium-avatar {
        background: linear-gradient(135deg, #CD7F32, #B87333);
        border-color: #CD7F32;
    }
    
    .podium-name {
        font-weight: 700;
        color: white;
        margin-bottom: 6px;
    }
    
    .podium-xp {
        font-size: 20px;
        font-weight: 700;
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .my-rank-card {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.1));
        backdrop-filter: blur(40px) saturate(180%);
        border: 2px solid var(--color-primary);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .rank-list {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .rank-item {
        background: var(--glass-bg-light);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-lg);
        padding: var(--spacing-md);
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        transition: all var(--transition-base);
    }
    
    /* P2-B: تحسين الأداء على الجوال — تعطيل backdrop-filter والـ animations الثقيلة */
    @media (max-width: 768px) {
        .podium-avatar { animation: none !important; }
        .my-rank-card,
        .rank-item {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        .my-rank-card { background: rgba(16, 185, 129, 0.12); }
        .rank-item { background: rgba(255, 255, 255, 0.05); }
        .rank-item:hover { transform: none !important; }
    }

    .rank-item:hover {
        transform: translateX(-8px);
        background: rgba(255, 255, 255, 0.15);
    }
    
    .rank-number {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-full);
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }
    
    .rank-avatar {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    
    .rank-info {
        flex: 1;
    }
    
    .rank-name {
        font-weight: 700;
        color: white;
        margin-bottom: 4px;
    }
    
    .rank-school {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.6);
    }
    
    .rank-xp {
        font-weight: 700;
        color: var(--color-warning);
        font-size: 16px;
    }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
<div class="leaderboard-container fade-in">
    <!-- Period Tabs -->
    <div class="period-tabs">
        <button class="period-tab {{ $period === 'week' ? 'active' : '' }}" data-period="week">هذا الأسبوع</button>
        <button class="period-tab {{ $period === 'month' ? 'active' : '' }}" data-period="month">هذا الشهر</button>
        <button class="period-tab {{ $period === 'all' ? 'active' : '' }}" data-period="all">كل الأوقات</button>
    </div>

    <!-- Top 3 Podium -->
    @if($topThree->count() >= 3)
    <div class="podium scale-in">
        <!-- Second Place -->
        <div class="podium-place second">
            <div class="podium-avatar">🥈</div>
            <div class="podium-name">{{ $topThree[1]->name }}</div>
            <div class="podium-xp">{{ $topThree[1]->total_xp }} XP</div>
        </div>
        
        <!-- First Place -->
        <div class="podium-place first">
            <div class="podium-avatar">👑</div>
            <div class="podium-name">{{ $topThree[0]->name }}</div>
            <div class="podium-xp">{{ $topThree[0]->total_xp }} XP</div>
        </div>
        
        <!-- Third Place -->
        <div class="podium-place third">
            <div class="podium-avatar">🥉</div>
            <div class="podium-name">{{ $topThree[2]->name }}</div>
            <div class="podium-xp">{{ $topThree[2]->total_xp }} XP</div>
        </div>
    </div>
    @endif

    <!-- My Rank -->
    <div class="my-rank-card slide-up">
        <div style="display: flex; align-items: center; gap: var(--spacing-md);">
            <div class="rank-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
            <div>
                <div style="font-weight: 700; color: white; margin-bottom: 4px;">ترتيبك</div>
                <div style="font-size: 13px; color: rgba(255,255,255,0.7);">{{ auth()->user()->name }}</div>
            </div>
        </div>
        <div style="text-align: left;">
            <div style="font-size: 28px; font-weight: 700; color: var(--color-primary);">#{{ $myRank ?? '—' }}</div>
            @php $periodLabel = $period === 'week' ? 'هذا الأسبوع' : ($period === 'month' ? 'هذا الشهر' : 'كل الأوقات'); @endphp
            <div style="font-size: 14px; color: rgba(255,255,255,0.7);">{{ $myPeriodXp ?? 0 }} XP · {{ $periodLabel }}</div>
        </div>
    </div>

    <!-- Rank List -->
    <div class="rank-list slide-up" style="animation-delay: 0.1s;">
        @foreach($leaderboard as $index => $student)
        <div class="rank-item {{ $student->id == auth()->id() ? 'my-rank-card' : '' }}">
            <div class="rank-number">#{{ $student->actual_rank ?? ($index + ($leaderboardStartRank ?? 4)) }}</div>
            <div class="rank-avatar">{{ mb_substr($student->name, 0, 1) }}</div>
            <div class="rank-info">
                <div class="rank-name">{{ $student->name }}</div>
                <div class="rank-school">{{ $student->school->name ?? 'مدرسة' }}</div>
            </div>
            <div class="rank-xp">{{ $student->total_xp }} XP</div>
        </div>
        @endforeach
    </div>

    @if($leaderboard->isEmpty() && $topThree->isEmpty())
    <div class="glass-card" style="text-align: center; padding: 60px 40px;">
        <div style="font-size: 80px; margin-bottom: 20px;">🏆</div>
        <h3 style="font-size: 24px; font-weight: 700; color: white; margin-bottom: 12px;">كن أول المتصدرين!</h3>
        <p style="font-size: 16px; color: rgba(255,255,255,0.7);">ابدأ رحلتك التعليمية واكسب النقاط</p>
    </div>
    @endif
</div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.period-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            const period = this.getAttribute('data-period');
            window.location.href = '{{ route("student.leaderboard") }}?period=' + period;
        });
    });
</script>
@endpush
