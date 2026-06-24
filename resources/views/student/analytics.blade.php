@extends('layouts.student-app')

@section('title', 'التحليلات والإحصائيات')

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">

<div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); margin-bottom: 30px;">
    <h2 style="font-size: 26px; font-weight: 700; margin-bottom: 30px; color: #2d3748;">📊 تحليلات أدائي</h2>
    
    <!-- إحصائيات سريعة -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 15px; padding: 25px; text-align: center; color: white;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 5px;">{{ $activityStatusData->completed ?? 0 }}</div>
            <div style="font-size: 14px; opacity: 0.9;">أنشطة مكتملة</div>
        </div>
        <div style="background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 15px; padding: 25px; text-align: center; color: white;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 5px;">{{ $activityStatusData->pending ?? 0 }}</div>
            <div style="font-size: 14px; opacity: 0.9;">قيد المراجعة</div>
        </div>
        <div style="background: linear-gradient(135deg, #10b981, #059669); border-radius: 15px; padding: 25px; text-align: center; color: white;">
            <div style="font-size: 42px; font-weight: 700; margin-bottom: 5px;">{{ $activityStatusData->in_progress ?? 0 }}</div>
            <div style="font-size: 14px; opacity: 0.9;">قيد التنفيذ</div>
        </div>
    </div>
    
    <!-- الرسم البياني: التقدم خلال 30 يوم -->
    <div style="background: #f7fafc; border-radius: 15px; padding: 25px; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 20px; color: #2d3748;">📈 تقدمي خلال آخر 30 يوم</h3>
        <div style="height: 300px; position: relative;">
            <canvas id="progressChart"></canvas>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- الرسم البياني: الأنشطة حسب الحالة -->
        <div style="background: #f7fafc; border-radius: 15px; padding: 25px;">
            <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 20px; color: #2d3748;">🎯 توزيع الأنشطة</h3>
            <div style="height: 250px; position: relative;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
        
        <!-- الرسم البياني: النقاط حسب القيمة -->
        <div style="background: #f7fafc; border-radius: 15px; padding: 25px;">
            <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 20px; color: #2d3748;">⭐ النقاط حسب القيمة</h3>
            <div style="height: 250px; position: relative;">
                <canvas id="valueChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- الرسم البياني: النشاط الأسبوعي -->
    <div style="background: #f7fafc; border-radius: 15px; padding: 25px; margin-bottom: 30px;">
        <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 20px; color: #2d3748;">📅 نشاطي الأسبوعي</h3>
        <div style="height: 250px; position: relative;">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>
    
    <!-- الرسم البياني: معدل الدرجات -->
    <div style="background: #f7fafc; border-radius: 15px; padding: 25px;">
        <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 20px; color: #2d3748;">📝 معدل درجاتي - آخر 10 أنشطة</h3>
        <div style="height: 250px; position: relative;">
            <canvas id="scoresChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// التقدم خلال 30 يوم
new Chart(document.getElementById('progressChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($progressData['labels']) !!},
        datasets: [
            {
                label: 'النقاط',
                data: {!! json_encode($progressData['points']) !!},
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            },
            {
                label: 'الأنشطة',
                data: {!! json_encode($progressData['activities']) !!},
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'النقاط'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'عدد الأنشطة'
                },
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});

// توزيع الأنشطة
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['مكتملة', 'قيد المراجعة', 'قيد التنفيذ'],
        datasets: [{
            data: [
                {{ $activityStatusData->completed ?? 0 }},
                {{ $activityStatusData->pending ?? 0 }},
                {{ $activityStatusData->in_progress ?? 0 }}
            ],
            backgroundColor: ['#10b981', '#fbbf24', '#667eea'],
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

// النقاط حسب القيمة
new Chart(document.getElementById('valueChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($pointsByValue->pluck('name')) !!},
        datasets: [{
            label: 'النقاط',
            data: {!! json_encode($pointsByValue->pluck('total')) !!},
            backgroundColor: 'rgba(102, 126, 234, 0.8)',
            borderColor: '#667eea',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// النشاط الأسبوعي
new Chart(document.getElementById('weeklyChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(collect($weeklyActivityData)->pluck('label')) !!},
        datasets: [{
            label: 'عدد الأنشطة',
            data: {!! json_encode(collect($weeklyActivityData)->pluck('count')) !!},
            backgroundColor: 'rgba(245, 158, 11, 0.8)',
            borderColor: '#f59e0b',
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

// معدل الدرجات
new Chart(document.getElementById('scoresChart'), {
    type: 'line',
    data: {
        labels: Array.from({length: {{ count($recentScores) }}}, (_, i) => `نشاط ${i + 1}`),
        datasets: [{
            label: 'الدرجة',
            data: {!! json_encode($recentScores) !!},
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>

</div>
@endsection
