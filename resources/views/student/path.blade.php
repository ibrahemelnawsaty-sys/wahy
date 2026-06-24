@extends('layouts.student-app')

@section('title', 'خريطة التعلم')

@push('styles')
<style>
    /* Learning Path Styles */
    .learning-path-container {
        max-width: 800px;
        margin: 0 auto;
        padding: var(--spacing-xl) var(--spacing-md);
        padding-bottom: 120px;
    }
    
    .unit-container {
        margin-bottom: var(--spacing-2xl);
        position: relative;
    }
    
    .unit-header {
        background: var(--glass-bg-medium);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    .unit-title {
        font-size: 24px;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }
    
    .unit-description {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.8);
    }
    
    /* Path Line */
    .path-line {
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, 
            rgba(16, 185, 129, 0.3) 0%, 
            rgba(59, 130, 246, 0.3) 50%, 
            rgba(139, 92, 246, 0.3) 100%
        );
        transform: translateX(-50%);
        border-radius: var(--radius-full);
    }
    
    /* Lesson Node */
    .lesson-node {
        position: relative;
        margin: var(--spacing-xl) 0;
        display: flex;
        justify-content: center;
        z-index: 2;
    }
    
    .lesson-node.zigzag-left {
        justify-content: flex-start;
        padding-right: 50%;
        padding-left: 0;
    }
    
    .lesson-node.zigzag-right {
        justify-content: flex-end;
        padding-left: 50%;
        padding-right: 0;
    }
    
    .lesson-card {
        background: var(--glass-bg-medium);
        backdrop-filter: blur(40px) saturate(180%);
        border: 2px solid;
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        width: 280px;
        cursor: pointer;
        transition: all var(--transition-base);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        position: relative;
    }
    
    /* Lesson States */
    .lesson-card.completed {
        border-color: var(--color-success);
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.2) 0%, rgba(22, 163, 74, 0.1) 100%);
    }
    
    .lesson-card.current {
        border-color: var(--color-primary);
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.1) 100%);
        animation: pulseGlow 2s ease-in-out infinite;
    }
    
    .lesson-card.locked {
        border-color: rgba(203, 213, 224, 0.3);
        background: rgba(255, 255, 255, 0.05);
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .lesson-card:not(.locked):hover {
        transform: translateY(-8px) scale(1.05);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
    }
    
    /* Lesson Icon */
    .lesson-icon {
        width: 64px;
        height: 64px;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        margin: 0 auto var(--spacing-md);
        position: relative;
    }
    
    .lesson-card.completed .lesson-icon {
        background: linear-gradient(135deg, var(--color-success) 0%, #16A34A 100%);
        box-shadow: 0 8px 24px rgba(34, 197, 94, 0.4);
    }
    
    .lesson-card.current .lesson-icon {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
        animation: pulse 2s ease-in-out infinite;
    }
    
    .lesson-card.locked .lesson-icon {
        background: rgba(203, 213, 224, 0.3);
        box-shadow: none;
    }
    
    /* Status Badge */
    .lesson-status {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 32px;
        height: 32px;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .lesson-info {
        text-align: center;
    }
    
    .lesson-title {
        font-size: 18px;
        font-weight: 700;
        color: white;
        margin-bottom: 8px;
    }
    
    .lesson-meta {
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
        font-size: 13px;
        color: rgba(255, 255, 255, 0.8);
    }
    
    .lesson-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    /* Progress Stats */
    .progress-stats {
        background: var(--glass-bg-medium);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--spacing-md);
    }
    
    .stat-item {
        text-align: center;
    }
    
    /* Issue #54: استبدال gradient text (الذي يظهر باهتاً على الخلفية البنفسجية)
       بلون أصفر فاتح متباين بقوة مع البنفسجي. */
    .stat-value {
        font-size: 32px;
        font-weight: 800;
        color: #FCD34D;
        text-shadow: 0 2px 8px rgba(0,0,0,0.35);
    }
    
    .stat-label {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 4px;
    }
    
    @keyframes pulseGlow {
        0%, 100% { 
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        }
        50% { 
            box-shadow: 0 8px 40px rgba(16, 185, 129, 0.6);
        }
    }
    
    /* Mobile Adjustments */
    @media (max-width: 767px) {
        .lesson-node.zigzag-left,
        .lesson-node.zigzag-right {
            justify-content: center;
            padding: 0;
        }
        
        .lesson-card {
            width: 100%;
            max-width: 320px;
        }
        
        .progress-stats {
            grid-template-columns: 1fr;
            gap: var(--spacing-sm);
        }
    }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
<div class="learning-path-container fade-in">
    <!-- Progress Overview -->
    <div class="progress-stats scale-in">
        <div class="stat-item">
            <div class="stat-value">{{ $totalLessons }}</div>
            <div class="stat-label">إجمالي الدروس</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $completedLessons }}</div>
            <div class="stat-label">دروس مكتملة</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $progressPercent }}%</div>
            <div class="stat-label">نسبة الإنجاز</div>
        </div>
    </div>

    @foreach($values as $valueIndex => $value)
    <div class="unit-container slide-up" style="animation-delay: {{ $valueIndex * 0.1 }}s;">
        <!-- Unit Header -->
        <div class="unit-header">
            <div class="unit-title">
                <span style="font-size: 36px;">{{ $value->icon ?? '📚' }}</span>
                <span>{{ $value->name }}</span>
            </div>
            <div class="unit-description">{{ $value->description }}</div>
        </div>

        <!-- Lessons Path -->
        <div style="position: relative;">
            <div class="path-line"></div>
            
            @foreach($value->concepts as $conceptIndex => $concept)
                @foreach($concept->lessons as $lessonIndex => $lesson)
                    @php
                        $isCompleted = isset($lesson->is_completed) && $lesson->is_completed;
                        $isCurrent = isset($lesson->is_current) && $lesson->is_current;
                        $isLocked = isset($lesson->is_locked) && $lesson->is_locked;
                        
                        // Zigzag pattern
                        $totalIndex = $lessonIndex + $conceptIndex * 2;
                        $position = $totalIndex % 3;
                            $positionClass = $position === 0 ? '' : ($position === 1 ? 'zigzag-left' : 'zigzag-right');
                            
                            // State class
                            $stateClass = $isCompleted ? 'completed' : ($isCurrent ? 'current' : ($isLocked ? 'locked' : ''));
                        @endphp
                        
                        <div class="lesson-node {{ $positionClass }}">
                            <div class="lesson-card {{ $stateClass }}" 
                                 onclick="{{ !$isLocked ? "window.location.href='" . route('student.lesson', $lesson->id) . "'" : '' }}">
                                
                                <!-- Status Badge -->
                                @if($isCompleted)
                                    <div class="lesson-status">✓</div>
                                @elseif($isCurrent)
                                    <div class="lesson-status">🔥</div>
                                @elseif($isLocked)
                                    <div class="lesson-status">🔒</div>
                                @endif
                                
                                <!-- Lesson Icon -->
                                <div class="lesson-icon">
                                    @if($isLocked)
                                        🔒
                                    @elseif($isCompleted)
                                        ✓
                                    @else
                                        📖
                                    @endif
                                </div>
                                
                                <!-- Lesson Info -->
                                <div class="lesson-info">
                                    <div class="lesson-title">{{ $lesson->title }}</div>
                                    <div class="lesson-meta">
                                        <div class="lesson-meta-item">
                                            <span>⭐</span>
                                            <span>{{ $lesson->points ?? 10 }} XP</span>
                                        </div>
                                        <div class="lesson-meta-item">
                                            <span>⏱️</span>
                                            <span>{{ $lesson->duration ?? 10 }} دقيقة</span>
                                        </div>
                                    </div>
                                    
                                    @if($isLocked)
                                        <div style="margin-top: 12px; font-size: 12px; color: rgba(255,255,255,0.6);">
                                            أكمل الدروس السابقة أولاً
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
            @endforeach
        </div>
    </div>
    @endforeach

    @if($values->isEmpty())
    <!-- Empty State -->
    <div class="glass-card" style="text-align: center; padding: 60px 40px;">
        <div style="font-size: 80px; margin-bottom: 20px;">🗺️</div>
        <h2 style="font-size: 28px; font-weight: 700; color: white; margin-bottom: 12px;">لا توجد دروس متاحة حالياً</h2>
        <p style="font-size: 16px; color: rgba(255,255,255,0.8);">سيتم إضافة الدروس قريباً</p>
    </div>
    @endif
</div>
</div>
@endsection

@push('scripts')
<script>
    // Add intersection observer for animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.lesson-node').forEach(node => {
        node.style.opacity = '0';
        node.style.transform = 'translateY(30px)';
        node.style.transition = 'all 0.6s ease-out';
        observer.observe(node);
    });
</script>
@endpush
