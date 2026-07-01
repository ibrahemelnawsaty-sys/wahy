@extends('layouts.teacher')

@section('title', 'تفاصيل الطالب')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/teacher-glass.css') }}?v={{ time() }}">
<style>
    .detail-header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
    .detail-avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.3); }
    .detail-avatar-placeholder { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 32px; border: 3px solid rgba(255,255,255,0.3); }
    .detail-name { font-size: 28px; font-weight: 800; color: white; }
    .detail-email { color: rgba(255,255,255,0.6); font-size: 14px; margin-top: 4px; }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 30px; }
    .stat-card { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 16px; padding: 20px; text-align: center; border: 1px solid rgba(255,255,255,0.15); }
    .stat-value { font-size: 32px; font-weight: 800; color: white; }
    .stat-label { font-size: 13px; color: rgba(255,255,255,0.6); margin-top: 4px; }

    .section-title-main { font-size: 20px; font-weight: 700; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }

    .activities-list { display: flex; flex-direction: column; gap: 10px; }
    .activity-item { background: rgba(255,255,255,0.08); border-radius: 14px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.1); }
    .activity-title { font-weight: 600; color: white; font-size: 15px; }
    .activity-sub { color: rgba(255,255,255,0.5); font-size: 12px; margin-top: 4px; }
    .activity-score { font-weight: 800; font-size: 18px; }
    .score-good { color: #6ee7b7; }
    .score-mid { color: #fcd34d; }
    .score-low { color: #fca5a5; }

    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
    .status-completed { background: rgba(16,185,129,0.2); color: #6ee7b7; }
    .status-pending { background: rgba(251,191,36,0.2); color: #fcd34d; }
    .status-rejected { background: rgba(239,68,68,0.2); color: #fca5a5; }

    .chart-container { background: rgba(255,255,255,0.08); border-radius: 16px; padding: 24px; border: 1px solid rgba(255,255,255,0.1); margin-top: 20px; }
</style>
@endpush

@section('content')
<div class="teacher-glass-container">

    <!-- Back Button -->
    <a href="{{ route('teacher.students') }}" style="display: inline-flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.7); text-decoration: none; margin-bottom: 20px; font-weight: 600;">
        ← العودة لقائمة الطلاب
    </a>

    <!-- Student Header -->
    <div class="detail-header">
        <img src="{{ $student->avatar_url }}" class="detail-avatar"
             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex'"
             style="display: block;">
        <div class="detail-avatar-placeholder" style="display:none;">{{ mb_substr($student->name, 0, 1) }}</div>
        <div>
            <h1 class="detail-name">{{ $student->name }}</h1>
            <p class="detail-email">{{ $student->email }} • {{ $student->school?->name ?? '-' }}</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_xp'] ?? 0 }}</div>
            <div class="stat-label">⭐ نقاط الخبرة</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_coins'] ?? 0 }}</div>
            <div class="stat-label">🪙 العملات</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['current_level'] ?? 1 }}</div>
            <div class="stat-label">📈 المستوى</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['streak_days'] ?? 0 }}</div>
            <div class="stat-label">🔥 أيام الالتزام</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['completed_activities'] ?? 0 }}</div>
            <div class="stat-label">✅ أنشطة مكتملة</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['pending_activities'] ?? 0 }}</div>
            <div class="stat-label">⏳ أنشطة معلقة</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['average_score'] ?? 0, 1) }}%</div>
            <div class="stat-label">📊 متوسط الدرجات</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['badges_count'] ?? 0 }}</div>
            <div class="stat-label">🏅 الشارات</div>
        </div>
    </div>

    <!-- XP Progress Chart -->
    <div class="section-title-main">📊 تقدم النقاط هذا الشهر</div>
    <div class="chart-container">
        <canvas id="xpChart" height="200"></canvas>
    </div>

    <!-- Recent Activities -->
    <div style="margin-top: 30px;">
        <div class="section-title-main">📋 آخر الأنشطة</div>
        <div class="activities-list">
            @forelse($recentActivities as $activity)
            <div class="activity-item">
                <div>
                    <div class="activity-title">{{ $activity->activity?->title ?? 'نشاط محذوف' }}</div>
                    <div class="activity-sub">
                        {{ $activity->activity?->lesson?->title ?? '-' }}
                        @if($activity->activity?->lesson?->concept?->value)
                            • 💎 {{ $activity->activity->lesson->concept->value->name }}
                        @endif
                        • {{ $activity->submitted_at?->format('Y/m/d') ?? $activity->created_at->format('Y/m/d') }}
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    @php
                        $st = $activity->status;
                        $label = match($st) {
                            'completed' => 'مكتمل',
                            'approved'  => 'مُقيَّم',
                            'pending'   => 'قيد المراجعة',
                            'needs_review' => 'لم يجتَز',
                            'rejected'  => 'مرفوض',
                            default     => $st,
                        };
                        $badgeClass = in_array($st, ['completed','approved'], true) ? 'status-completed'
                            : (in_array($st, ['pending','needs_review'], true) ? 'status-pending' : 'status-rejected');
                    @endphp
                    @if($activity->score !== null && in_array($st, ['completed','approved','needs_review','rejected'], true))
                        <span class="activity-score {{ ($activity->score ?? 0) >= 70 ? 'score-good' : (($activity->score ?? 0) >= 40 ? 'score-mid' : 'score-low') }}">
                            {{ $activity->score }}%
                        </span>
                    @endif
                    <span class="status-badge {{ $badgeClass }}">{{ $label }}</span>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.5);">
                <div style="font-size: 40px; margin-bottom: 8px;">📭</div>
                <p>لا توجد أنشطة بعد</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const xpData = @json($xpProgress);
const ctx = document.getElementById('xpChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: xpData.map(d => d.date),
        datasets: [{
            label: 'النقاط اليومية',
            data: xpData.map(d => d.total),
            borderColor: '#667eea',
            backgroundColor: 'rgba(102,126,234,0.1)',
            fill: true,
            borderWidth: 3,
            tension: 0.4,
            pointBackgroundColor: '#667eea',
            pointRadius: 5,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { labels: { color: 'rgba(255,255,255,0.7)', font: { family: 'IBM Plex Sans Arabic' } } }
        },
        scales: {
            x: { ticks: { color: 'rgba(255,255,255,0.5)' }, grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { ticks: { color: 'rgba(255,255,255,0.5)' }, grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true }
        }
    }
});
</script>
@endsection
