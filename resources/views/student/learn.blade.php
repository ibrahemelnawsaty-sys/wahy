@extends('layouts.student-app')

@section('title', 'التعلم - رحلتي التعليمية')

@push('styles')
<style>
    /* Page-specific animations */
    @keyframes pulseGlow {
        0%, 100% { 
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
        }
        50% { 
            box-shadow: 0 0 40px rgba(16, 185, 129, 0.6);
        }
    }
    
    .hero-card-glow {
        animation: pulseGlow 3s ease-in-out infinite;
    }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
<div class="fade-in">
    <!-- Hero: Current Lesson Card -->
    @if(isset($currentLesson) && $currentLesson)
    <div class="hero-lesson-card hero-card-glow" onclick="window.location.href='{{ route('student.lesson', $currentLesson->id) }}'">
        <div class="hero-lesson-content">
            <div class="hero-lesson-icon">
                {{ $currentLesson->icon ?? '📚' }}
            </div>
            <div class="hero-lesson-info">
                <div class="hero-lesson-subject">{{ $currentLesson->concept->value->name ?? 'القيم' }}</div>
                <div class="hero-lesson-title">{{ $currentLesson->title }}</div>
                <div style="font-size: 14px; color: rgba(255,255,255,0.8); margin-bottom: 16px;">
                    {{ $currentLesson->description ?? 'ابدأ رحلتك في تعلم هذا الدرس' }}
                </div>

                {{-- مؤشّر مضغوط لمكافأة الالتزام اليومي (كهرماني ذاتي التباين) --}}
                @if($currentLesson->hasStreakEnabled())
                @php
                    $__clsDone    = (int) (($currentLessonStreak ?? null)->completed_days ?? 0);
                    $__clsMin     = (int) $currentLesson->streak_min_days;
                    $__clsClaimed = (bool) (($currentLessonStreak ?? null)->bonus_claimed ?? false);
                    $__clsPct     = $__clsMin > 0 ? min(100, round($__clsDone / $__clsMin * 100)) : 0;
                @endphp
                <div style="display:inline-flex; flex-direction:column; gap:6px; background:linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border:1.5px solid #f59e0b; border-radius:12px; padding:8px 14px; margin-bottom:16px; max-width:100%;">
                    @if($__clsClaimed)
                        <span style="display:flex; align-items:center; gap:7px; font-size:12.5px; font-weight:800; color:#92400e;"><span style="font-size:15px;">🏆</span> مكافأة الالتزام محقّقة!</span>
                    @elseif($__clsDone > 0)
                        <span style="display:flex; align-items:center; gap:7px; font-size:12.5px; font-weight:800; color:#92400e;"><span style="font-size:15px;">🔥</span> التزام: يوم {{ $__clsDone }} من {{ $__clsMin }} — استمرّ! 🚀</span>
                        <span style="display:block; background:rgba(255,255,255,0.55); border-radius:8px; height:6px; overflow:hidden;"><span style="display:block; height:100%; border-radius:8px; background:linear-gradient(90deg,#f59e0b,#d97706); width:{{ $__clsPct }}%;"></span></span>
                    @else
                        <span style="display:flex; align-items:center; gap:7px; font-size:12.5px; font-weight:800; color:#92400e;"><span style="font-size:15px;">🔥</span> مكافأة التزام — ابدأ اليوم بأوّل نشاط!</span>
                    @endif
                </div>
                @endif

                <div class="hero-lesson-progress">
                    <!-- Progress Ring -->
                    <div class="progress-ring-container">
                        <svg class="progress-ring-svg" width="60" height="60">
                            <circle class="progress-ring-circle-bg" cx="30" cy="30" r="26"></circle>
                            <circle class="progress-ring-circle" cx="30" cy="30" r="26"
                                    style="stroke-dasharray: 163.36; stroke-dashoffset: {{ 163.36 - (163.36 * ($currentLesson->progress ?? 0) / 100) }};"></circle>
                        </svg>
                        <div class="progress-ring-text">{{ $currentLesson->progress ?? 0 }}%</div>
                    </div>
                    
                    <button class="hero-lesson-cta">
                        <span>{{ $currentLesson->progress > 0 ? 'تابع التعلم' : 'ابدأ الآن' }}</span>
                        <span style="font-size: 20px;">🚀</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="hero-lesson-card" style="text-align: center; padding: 60px 40px;">
        <div style="font-size: 80px; margin-bottom: 20px;">🎯</div>
        <h2 style="font-size: 28px; font-weight: 700; color: white; margin-bottom: 12px;">ابدأ رحلتك التعليمية</h2>
        <p style="font-size: 16px; color: rgba(255,255,255,0.8); margin-bottom: 24px;">اختر أول درس من خريطة التعلم</p>
        <button class="hero-lesson-cta" onclick="window.location.href='{{ route('student.path') }}'">
            <span>استكشف الدروس</span>
            <span style="font-size: 20px;">🗺️</span>
        </button>
    </div>
    @endif

    <!-- Desktop Layout: Two Columns -->
    <div style="display: grid; grid-template-columns: 1fr; gap: var(--spacing-xl);" class="learn-content-grid">
        <!-- Left Column: Main Content -->
        <div class="main-content-left">
            <!-- Daily Goal Card -->
            <div class="daily-goal-card slide-up">
                <div class="daily-goal-header">
                    <div class="daily-goal-title">
                        <span style="font-size: 24px;">🎯</span>
                        <span>هدف اليوم</span>
                    </div>
                    @php
                        $dailyGoal = 2; // عدد الدروس المطلوبة
                        $completedToday = $stats['completed_today'] ?? 0;
                        $goalPercent = min(($completedToday / $dailyGoal) * 100, 100);
                        $isCompleted = $completedToday >= $dailyGoal;
                    @endphp
                    <div class="daily-goal-status" style="background: {{ $isCompleted ? 'rgba(34, 197, 94, 0.3)' : 'rgba(251, 191, 36, 0.3)' }}; color: {{ $isCompleted ? '#22C55E' : '#FBBF24' }};">
                        {{ $isCompleted ? '✓ مكتمل' : $completedToday . ' / ' . $dailyGoal }}
                    </div>
                </div>
                <div class="daily-goal-progress-bar">
                    <div class="daily-goal-progress-fill" style="width: {{ $goalPercent }}%"></div>
                </div>
                <div class="daily-goal-text">
                    @if($isCompleted)
                        🎉 رائع! لقد أكملت هدف اليوم
                    @else
                        {{ $dailyGoal - $completedToday }} {{ $dailyGoal - $completedToday == 1 ? 'درس' : 'دروس' }} متبقية لإكمال هدف اليوم
                    @endif
                </div>
            </div>

            <!-- Quick Practice Section -->
            <div class="quick-practice-section slide-up" style="animation-delay: 0.1s;">
                <div class="section-title">
                    <span style="font-size: 28px;">⚡</span>
                    <span>تمارين سريعة</span>
                </div>
                <div class="quick-practice-grid">
                    <!-- Practice Card 1: Review -->
                    <div class="quick-practice-card" onclick="window.location.href='{{ route('student.practice') }}?type=review'">
                        <div class="quick-practice-icon">📝</div>
                        <div class="quick-practice-title">مراجعة سريعة</div>
                        <div class="quick-practice-xp">
                            <span>⭐</span>
                            <span>+10 XP</span>
                        </div>
                        <div style="font-size: 12px; color: rgba(255,255,255,0.7); margin-top: 8px;">5 دقائق</div>
                    </div>

                    <!-- Practice Card 2: Quiz -->
                    <div class="quick-practice-card" onclick="window.location.href='{{ route('student.practice') }}?type=quiz'">
                        <div class="quick-practice-icon">🎯</div>
                        <div class="quick-practice-title">اختبار سريع</div>
                        <div class="quick-practice-xp">
                            <span>⭐</span>
                            <span>+15 XP</span>
                        </div>
                        <div style="font-size: 12px; color: rgba(255,255,255,0.7); margin-top: 8px;">3 أسئلة</div>
                    </div>

                    <!-- Practice Card 3: Challenge -->
                    <div class="quick-practice-card" onclick="window.location.href='{{ route('student.practice') }}?type=challenge'">
                        <div class="quick-practice-icon">🏆</div>
                        <div class="quick-practice-title">تحدي</div>
                        <div class="quick-practice-xp">
                            <span>⭐</span>
                            <span>+25 XP</span>
                        </div>
                        <div style="font-size: 12px; color: rgba(255,255,255,0.7); margin-top: 8px;">10 دقائق</div>
                    </div>

                    <!-- Practice Card 4: Story -->
                    <div class="quick-practice-card" onclick="window.location.href='{{ route('student.practice') }}?type=story'">
                        <div class="quick-practice-icon">📖</div>
                        <div class="quick-practice-title">قصة تفاعلية</div>
                        <div class="quick-practice-xp">
                            <span>⭐</span>
                            <span>+20 XP</span>
                        </div>
                        <div style="font-size: 12px; color: rgba(255,255,255,0.7); margin-top: 8px;">8 دقائق</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            @if(isset($recentActivities) && $recentActivities->count() > 0)
            <div class="quick-practice-section slide-up" style="animation-delay: 0.2s;">
                <div class="section-title">
                    <span style="font-size: 28px;">📚</span>
                    <span>آخر أنشطتك</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
                    @foreach($recentActivities as $activity)
                    <div class="glass-card" style="padding: var(--spacing-lg); cursor: pointer;" 
                         onclick="window.location.href='{{ route('student.activity', $activity->id) }}'">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: var(--spacing-md);">
                            <div style="flex: 1;">
                                <div style="font-weight: 700; font-size: 16px; color: white; margin-bottom: 6px;">
                                    {{ $activity->activity->title ?? 'نشاط' }}
                                </div>
                                <div style="font-size: 13px; color: rgba(255,255,255,0.7);">
                                    {{ $activity->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div style="text-align: center;">
                                @if($activity->status == 'completed')
                                    <div style="background: linear-gradient(135deg, #22C55E 0%, #16A34A 100%); 
                                                color: white; padding: 8px 16px; border-radius: var(--radius-full); 
                                                font-weight: 700; font-size: 14px;">
                                        ✓ {{ $activity->score ?? 100 }}%
                                    </div>
                                @elseif($activity->status == 'pending')
                                    <div style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); 
                                                color: white; padding: 8px 16px; border-radius: var(--radius-full); 
                                                font-weight: 700; font-size: 14px;">
                                        ⏳ قيد المراجعة
                                    </div>
                                @else
                                    <div style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); 
                                                color: white; padding: 8px 16px; border-radius: var(--radius-full); 
                                                font-weight: 700; font-size: 14px;">
                                        📝 جاري العمل
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Side Cards (Desktop Only) -->
        <div class="main-content-right" style="display: none;">
            <!-- Achievements Card -->
            <div class="glass-card scale-in" style="padding: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
                <div style="font-size: 20px; font-weight: 700; color: white; margin-bottom: var(--spacing-md); display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 28px;">🏅</span>
                    <span>إنجازاتي</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: rgba(255,255,255,0.9); font-size: 14px;">الدروس المكتملة</span>
                        <span style="font-weight: 700; color: var(--color-success); font-size: 18px;">{{ $stats['completed_activities'] ?? 0 }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: rgba(255,255,255,0.9); font-size: 14px;">الشارات المكتسبة</span>
                        <span style="font-weight: 700; color: var(--color-warning); font-size: 18px;">{{ $stats['total_badges'] ?? 0 }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: rgba(255,255,255,0.9); font-size: 14px;">متوسط الدرجات</span>
                        <span style="font-weight: 700; color: var(--color-secondary); font-size: 18px;">{{ $stats['average_score'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>

            <!-- Badges Preview -->
            @if(isset($badges) && $badges->count() > 0)
            <div class="glass-card scale-in" style="padding: var(--spacing-lg); animation-delay: 0.1s;">
                <div style="font-size: 20px; font-weight: 700; color: white; margin-bottom: var(--spacing-md); display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 28px;">🎖️</span>
                    <span>أحدث الشارات</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                    @foreach($badges->take(3) as $badge)
                    <div style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.3) 0%, rgba(109, 40, 217, 0.3) 100%);
                                border-radius: var(--radius-lg); padding: 16px; text-align: center;
                                border: 1px solid rgba(139, 92, 246, 0.4);">
                        <div style="font-size: 32px; margin-bottom: 6px;">{{ $badge->icon ?? '🏆' }}</div>
                        <div style="font-size: 11px; font-weight: 600; color: white;">{{ $badge->name }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Show side column on large screens
    if (window.innerWidth >= 1024) {
        document.querySelector('.learn-content-grid').style.gridTemplateColumns = '2fr 1fr';
        document.querySelector('.main-content-right').style.display = 'block';
    }
    
    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            document.querySelector('.learn-content-grid').style.gridTemplateColumns = '2fr 1fr';
            document.querySelector('.main-content-right').style.display = 'block';
        } else {
            document.querySelector('.learn-content-grid').style.gridTemplateColumns = '1fr';
            document.querySelector('.main-content-right').style.display = 'none';
        }
    });
</script>
@endpush
</div>