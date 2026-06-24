@extends('layouts.teacher')

@section('title', 'التحليلات والإحصائيات')

@section('content')

<div class="container">
    <div class="page-header">
        <h1 class="page-title">📊 التحليلات والإحصائيات</h1>
    </div>
    
    <!-- إحصائيات سريعة -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 15px; padding: 25px; text-align: center; color: white; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
            <div style="font-size: 48px; font-weight: 700; margin-bottom: 5px;">{{ $activityStats->total ?? 0 }}</div>
            <div style="font-size: 15px; opacity: 0.9;">إجمالي التسليمات</div>
        </div>
        <div style="background: linear-gradient(135deg, #10b981, #059669); border-radius: 15px; padding: 25px; text-align: center; color: white; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);">
            <div style="font-size: 48px; font-weight: 700; margin-bottom: 5px;">{{ $activityStats->completed ?? 0 }}</div>
            <div style="font-size: 15px; opacity: 0.9;">أنشطة مكتملة</div>
        </div>
        <div style="background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 15px; padding: 25px; text-align: center; color: white; box-shadow: 0 10px 30px rgba(251, 191, 36, 0.3);">
            <div style="font-size: 48px; font-weight: 700; margin-bottom: 5px;">{{ $activityStats->pending ?? 0 }}</div>
            <div style="font-size: 15px; opacity: 0.9;">قيد المراجعة</div>
        </div>
        <div style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 15px; padding: 25px; text-align: center; color: white; box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);">
            <div style="font-size: 48px; font-weight: 700; margin-bottom: 5px;">{{ number_format($activityStats->avg_score ?? 0, 1) }}</div>
            <div style="font-size: 15px; opacity: 0.9;">متوسط الدرجات</div>
        </div>
    </div>
    
    <!-- الرسم البياني: التسليمات خلال 30 يوم -->
    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 20px; color: #2d3748;">📈 التسليمات خلال آخر 30 يوم</h3>
        <div style="height: 350px; position: relative;">
            <canvas id="submissionsChart"></canvas>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- توزيع الدرجات -->
        <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 20px; color: #2d3748;">📝 توزيع الدرجات</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="gradeChart"></canvas>
            </div>
        </div>
        
        <!-- التفاعل الأسبوعي -->
        <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 20px; color: #2d3748;">📅 التفاعل الأسبوعي</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- أفضل 10 طلاب -->
    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 20px; color: #2d3748;">🏆 أفضل 10 طلاب</h3>
        <div style="display: grid; gap: 15px;">
            @foreach($topStudents as $index => $student)
            <div style="display: flex; align-items: center; gap: 15px; background: #f7fafc; border-radius: 12px; padding: 15px; border-right: 4px solid {{ $index < 3 ? '#fbbf24' : '#667eea' }};">
                <div style="font-size: 28px; font-weight: 700; color: #718096; min-width: 40px;">
                    @if($index === 0) 🥇
                    @elseif($index === 1) 🥈
                    @elseif($index === 2) 🥉
                    @else {{ $index + 1 }}
                    @endif
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: #2d3748; margin-bottom: 3px;">{{ $student->name }}</div>
                    <div style="color: #718096; font-size: 13px;">{{ $student->email }}</div>
                </div>
                <div style="text-align: center; background: linear-gradient(135deg, #ffd700, #ffed4e); padding: 10px 20px; border-radius: 10px;">
                    <div style="font-size: 22px; font-weight: 700; color: #7c3aed;">{{ $student->total_points ?? 0 }}</div>
                    <div style="font-size: 11px; color: #7c3aed;">نقطة</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- الأنشطة الأكثر تفاعلاً -->
    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 20px; color: #2d3748;">🎯 الأنشطة الأكثر تفاعلاً</h3>
        <div style="display: grid; gap: 15px;">
            @foreach($topActivities as $activity)
            <div style="display: flex; justify-content: space-between; align-items: center; background: #f7fafc; border-radius: 12px; padding: 20px;">
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">{{ $activity->title }}</div>
                    <div style="color: #718096; font-size: 13px;">{{ $activity->description ?? 'لا يوجد وصف' }}</div>
                </div>
                <div style="display: flex; gap: 20px;">
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: 700; color: #667eea;">{{ $activity->submissions_count }}</div>
                        <div style="font-size: 12px; color: #718096;">تسليم</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: 700; color: #10b981;">{{ number_format($activity->avg_score ?? 0, 1) }}</div>
                        <div style="font-size: 12px; color: #718096;">متوسط</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// التسليمات خلال 30 يوم
new Chart(document.getElementById('submissionsChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode(collect($submissionsData)->pluck('date')) !!},
        datasets: [{
            label: 'عدد التسليمات',
            data: {!! json_encode(collect($submissionsData)->pluck('count')) !!},
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// توزيع الدرجات
new Chart(document.getElementById('gradeChart'), {
    type: 'doughnut',
    data: {
        labels: ['ممتاز (90+)', 'جيد جداً (80-89)', 'جيد (70-79)', 'مقبول (60-69)', 'ضعيف (<60)'],
        datasets: [{
            data: [
                {{ $gradeDistribution->excellent ?? 0 }},
                {{ $gradeDistribution->very_good ?? 0 }},
                {{ $gradeDistribution->good ?? 0 }},
                {{ $gradeDistribution->acceptable ?? 0 }},
                {{ $gradeDistribution->weak ?? 0 }}
            ],
            backgroundColor: ['#10b981', '#3b82f6', '#fbbf24', '#f97316', '#ef4444'],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// التفاعل الأسبوعي
new Chart(document.getElementById('weeklyChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(collect($weeklyEngagement)->pluck('week')) !!},
        datasets: [{
            label: 'الأنشطة المكتملة',
            data: {!! json_encode(collect($weeklyEngagement)->pluck('count')) !!},
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: '#10b981',
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

@endsection
