@extends('layouts.admin')

@section('title', 'التقارير والإحصائيات')

@section('content')
<div class="reports-dashboard">
    <!-- Header with Filters -->
    <div class="page-header">
        <div class="header-content">
            <h1>📊 التقارير والإحصائيات</h1>
            <p>لوحة تحكم شاملة لمتابعة الأداء والتقدم</p>
        </div>
        
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label>من تاريخ</label>
                <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
            </div>
            <div class="filter-group">
                <label>إلى تاريخ</label>
                <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="btn btn-primary">تطبيق</button>
        </form>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">👨‍🎓</div>
            <div class="stat-content">
                <h3>{{ number_format($stats['total_students']) }}</h3>
                <p>إجمالي الطلاب</p>
                <span class="stat-meta">منهم {{ $stats['active_students'] }} نشط</span>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">👨‍🏫</div>
            <div class="stat-content">
                <h3>{{ number_format($stats['total_teachers']) }}</h3>
                <p>المعلمين</p>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">🏫</div>
            <div class="stat-content">
                <h3>{{ number_format($stats['total_schools']) }}</h3>
                <p>المدارس المسجلة</p>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3>{{ number_format($stats['total_activities']) }}</h3>
                <p>الأنشطة المتاحة</p>
            </div>
        </div>

        <div class="stat-card secondary">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <h3>{{ number_format($stats['total_submissions']) }}</h3>
                <p>المشاركات</p>
                <span class="stat-meta">خلال الفترة المحددة</span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <!-- Daily Progress Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>📈 التقدم اليومي</h3>
                <p>عدد المشاركات في آخر 30 يوم</p>
            </div>
            <canvas id="dailyProgressChart"></canvas>
        </div>

        <!-- Activities by Type Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>🎯 توزيع الأنشطة</h3>
                <p>الأنشطة حسب النوع</p>
            </div>
            <canvas id="activitiesByTypeChart"></canvas>
        </div>
    </div>

    <!-- Top Students & Schools -->
    <div class="rankings-row">
        <!-- Top Students -->
        <div class="ranking-card">
            <div class="ranking-header">
                <h3>🏆 أفضل 10 طلاب</h3>
                <p>مرتبون حسب النقاط المكتسبة</p>
            </div>
            <div class="ranking-list">
                @forelse($topStudents as $index => $student)
                <div class="ranking-item">
                    <div class="rank-badge rank-{{ $index + 1 <= 3 ? 'gold' : 'silver' }}">
                        {{ $index + 1 }}
                    </div>
                    <div class="rank-info">
                        <h4>{{ $student->name }}</h4>
                        <p>{{ $student->school->name ?? 'غير محدد' }}</p>
                    </div>
                    <div class="rank-points">
                        <span>{{ number_format($student->total_points ?? 0) }}</span>
                        <small>نقطة</small>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <p>لا يوجد طلاب بعد</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Active Schools -->
        <div class="ranking-card">
            <div class="ranking-header">
                <h3>🌟 أنشط المدارس</h3>
                <p>مرتبة حسب عدد الطلاب النشطين</p>
            </div>
            <div class="ranking-list">
                @forelse($activeSchools as $index => $school)
                <div class="ranking-item">
                    <div class="rank-badge rank-{{ $index + 1 <= 3 ? 'gold' : 'silver' }}">
                        {{ $index + 1 }}
                    </div>
                    <div class="rank-info">
                        <h4>{{ $school->name }}</h4>
                        <p>{{ $school->city }}</p>
                    </div>
                    <div class="rank-points">
                        <span>{{ number_format($school->active_students) }}</span>
                        <small>طالب نشط</small>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <p>لا توجد مدارس بعد</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Values -->
    <div class="values-section">
        <div class="section-header">
            <h3>💎 القيم الأكثر تطبيقاً</h3>
            <p>القيم التي تحتوي على أكبر عدد من المفاهيم</p>
        </div>
        <div class="values-grid">
            @forelse($topValues as $value)
            <div class="value-card">
                <div class="value-emoji">{{ $value->emoji }}</div>
                <h4>{{ $value->name }}</h4>
                <div class="value-meta">
                    <span>{{ $value->concepts_count }} مفهوم</span>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <p>لا توجد قيم بعد</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Export Center -->
    <div class="quick-links" style="margin-bottom: 20px; border: 2px solid #d1fae5;">
        <h3 style="display: flex; align-items: center; gap: 10px;">📥 مركز التصدير <span style="font-size: 13px; color: #64748b; font-weight: 400;">— تصدير البيانات بصيغة Excel</span></h3>
        <div class="links-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <form method="POST" action="{{ route('admin.reports.export') }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="type" value="students">
                <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 12px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid rgba(16,185,129,0.2); cursor: pointer; transition: all 0.2s; text-align: right;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(16,185,129,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span style="font-size: 28px;">👨‍🎓</span>
                    <span style="font-weight: 700; color: #065f46;">الطلاب</span>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.reports.export') }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="type" value="teachers">
                <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 12px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 2px solid rgba(59,130,246,0.2); cursor: pointer; transition: all 0.2s; text-align: right;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(59,130,246,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span style="font-size: 28px;">👩‍🏫</span>
                    <span style="font-weight: 700; color: #1e40af;">المعلمين</span>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.reports.export') }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="type" value="parents">
                <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 12px; background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%); border: 2px solid rgba(234,179,8,0.2); cursor: pointer; transition: all 0.2s; text-align: right;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(234,179,8,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span style="font-size: 28px;">👪</span>
                    <span style="font-weight: 700; color: #854d0e;">أولياء الأمور</span>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.reports.export') }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="type" value="schools">
                <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 12px; background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%); border: 2px solid rgba(236,72,153,0.2); cursor: pointer; transition: all 0.2s; text-align: right;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(236,72,153,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span style="font-size: 28px;">🏫</span>
                    <span style="font-weight: 700; color: #9d174d;">المدارس</span>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.reports.export') }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="type" value="activities">
                <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 12px; background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border: 2px solid rgba(139,92,246,0.2); cursor: pointer; transition: all 0.2s; text-align: right;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(139,92,246,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span style="font-size: 28px;">📝</span>
                    <span style="font-weight: 700; color: #5b21b6;">الأنشطة</span>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.reports.export') }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="type" value="values">
                <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 12px; background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 2px solid rgba(249,115,22,0.2); cursor: pointer; transition: all 0.2s; text-align: right;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(249,115,22,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <span style="font-size: 28px;">💎</span>
                    <span style="font-weight: 700; color: #9a3412;">القيم</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <h3>📑 تقارير تفصيلية</h3>
        <div class="links-grid">
            <a href="{{ route('admin.reports.students') }}" class="quick-link">
                <div class="link-icon">👨‍🎓</div>
                <div class="link-content">
                    <h4>تقارير الطلاب</h4>
                    <p>تفاصيل شاملة عن أداء الطلاب</p>
                </div>
            </a>

            <a href="{{ route('admin.reports.schools') }}" class="quick-link">
                <div class="link-icon">🏫</div>
                <div class="link-content">
                    <h4>تقارير المدارس</h4>
                    <p>إحصائيات ومعلومات المدارس</p>
                </div>
            </a>

            <a href="{{ route('admin.reports.activities') }}" class="quick-link">
                <div class="link-icon">📝</div>
                <div class="link-content">
                    <h4>تقارير الأنشطة</h4>
                    <p>تحليل الأنشطة ومعدلات الإنجاز</p>
                </div>
            </a>

            <a href="{{ route('admin.reports.values') }}" class="quick-link">
                <div class="link-icon">💎</div>
                <div class="link-content">
                    <h4>تقارير القيم</h4>
                    <p>التقدم في تطبيق القيم</p>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.reports-dashboard {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
    gap: 20px;
}

.header-content h1 {
    font-size: 28px;
    margin-bottom: 8px;
}

.header-content p {
    color: #64748b;
    font-size: 14px;
}

.filters-form {
    display: flex;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.filter-group input {
    padding: 10px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 2px solid #f1f5f9;
}

.stat-icon {
    font-size: 40px;
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #f8fafc;
}

.stat-card.primary .stat-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-card.success .stat-icon { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.stat-card.warning .stat-icon { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.stat-card.info .stat-icon { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
.stat-card.secondary .stat-icon { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }

.stat-content h3 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 4px;
}

.stat-content p {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 4px;
}

.stat-meta {
    font-size: 12px;
    color: #94a3b8;
}

.charts-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.chart-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 2px solid #f1f5f9;
}

.chart-header {
    margin-bottom: 20px;
}

.chart-header h3 {
    font-size: 18px;
    margin-bottom: 4px;
}

.chart-header p {
    font-size: 13px;
    color: #64748b;
}

.rankings-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.ranking-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 2px solid #f1f5f9;
}

.ranking-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f5f9;
}

.ranking-header h3 {
    font-size: 18px;
    margin-bottom: 4px;
}

.ranking-header p {
    font-size: 13px;
    color: #64748b;
}

.ranking-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ranking-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    border-radius: 10px;
    background: #f8fafc;
    transition: all 0.2s;
}

.ranking-item:hover {
    background: #f1f5f9;
    transform: translateX(-4px);
}

.rank-badge {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
}

.rank-badge.rank-gold {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: white;
}

.rank-badge.rank-silver {
    background: #e2e8f0;
    color: #475569;
}

.rank-info {
    flex: 1;
}

.rank-info h4 {
    font-size: 15px;
    margin-bottom: 2px;
}

.rank-info p {
    font-size: 12px;
    color: #64748b;
}

.rank-points {
    text-align: left;
}

.rank-points span {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: var(--color-primary);
}

.rank-points small {
    font-size: 11px;
    color: #94a3b8;
}

.values-section {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 2px solid #f1f5f9;
}

.section-header {
    margin-bottom: 20px;
}

.section-header h3 {
    font-size: 18px;
    margin-bottom: 4px;
}

.section-header p {
    font-size: 13px;
    color: #64748b;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.value-card {
    text-align: center;
    padding: 20px;
    border-radius: 12px;
    background: #f8fafc;
    transition: all 0.2s;
}

.value-card:hover {
    background: #f1f5f9;
    transform: translateY(-4px);
}

.value-emoji {
    font-size: 48px;
    margin-bottom: 10px;
}

.value-card h4 {
    font-size: 15px;
    margin-bottom: 8px;
}

.value-meta {
    font-size: 12px;
    color: #64748b;
}

.quick-links {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 2px solid #f1f5f9;
}

.quick-links h3 {
    font-size: 18px;
    margin-bottom: 20px;
}

.links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.quick-link {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border-radius: 12px;
    background: #f8fafc;
    text-decoration: none;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.quick-link:hover {
    background: #f1f5f9;
    border-color: var(--color-primary);
    transform: translateY(-2px);
}

.link-icon {
    font-size: 36px;
}

.link-content h4 {
    font-size: 15px;
    margin-bottom: 4px;
    color: #0f172a;
}

.link-content p {
    font-size: 12px;
    color: #64748b;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #94a3b8;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
    }
    
    .filters-form {
        width: 100%;
    }
    
    .charts-row,
    .rankings-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Daily Progress Chart
const dailyProgressCtx = document.getElementById('dailyProgressChart').getContext('2d');
const dailyProgressChart = new Chart(dailyProgressCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($dailyProgress->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!},
        datasets: [{
            label: 'المشاركات',
            data: {!! json_encode($dailyProgress->pluck('count')) !!},
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Activities by Type Chart — يدعم كل أنواع الأنشطة الـ 8
@php
    $activityTypeLabels = [
        'quiz' => 'اختبار',
        'exercise' => 'تمرين',
        'project' => 'مشروع',
        'creative' => 'إبداعي',
        'upload' => 'رفع ملف',
        'practical' => 'عملي',
        'discussion' => 'نقاش',
        'image_order' => 'ترتيب صور',
    ];
    $activityTypeColors = [
        'rgb(102, 126, 234)', 'rgb(16, 185, 129)', 'rgb(245, 158, 11)',
        'rgb(236, 72, 153)', 'rgb(168, 85, 247)', 'rgb(34, 197, 94)',
        'rgb(59, 130, 246)', 'rgb(239, 68, 68)',
    ];
@endphp
const activitiesByTypeCtx = document.getElementById('activitiesByTypeChart').getContext('2d');
const activitiesByTypeChart = new Chart(activitiesByTypeCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($activitiesByType->pluck('type')->map(function($t) use ($activityTypeLabels) {
            return $activityTypeLabels[$t] ?? $t;
        })) !!},
        datasets: [{
            data: {!! json_encode($activitiesByType->pluck('count')) !!},
            backgroundColor: {!! json_encode(array_slice($activityTypeColors, 0, $activitiesByType->count() ?: 1)) !!}
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endsection
