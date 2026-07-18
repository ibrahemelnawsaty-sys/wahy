@extends('layouts.admin')

@section('page-title', 'تقرير المقارنة - ' . $survey->title)

@section('content')
<style>
    .comp-container { max-width: 1200px; margin: 0 auto; direction: rtl; }
    .comp-header {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 50%, #5b21b6 100%);
        padding: 40px;
        border-radius: 24px;
        color: white;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(124, 58, 237, 0.3);
    }
    .comp-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }
    .comp-header h1 { font-size: 28px; font-weight: 800; margin: 0 0 8px; position: relative; z-index: 1; }
    .comp-header p { opacity: 0.85; font-size: 15px; margin: 0; position: relative; z-index: 1; }
    .comp-back {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);
        padding: 10px 20px; border-radius: 12px; color: white;
        text-decoration: none; font-size: 14px; font-weight: 600;
        transition: all 0.3s; margin-bottom: 24px; border: 1px solid rgba(255,255,255,0.3);
    }
    .comp-back:hover { background: rgba(255,255,255,0.35); transform: translateX(4px); }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 28px 24px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        transition: all 0.3s;
    }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .stat-icon { font-size: 36px; margin-bottom: 12px; display: block; }
    .stat-value { font-size: 32px; font-weight: 800; color: #1e293b; margin-bottom: 4px; }
    .stat-label { font-size: 13px; color: #64748b; font-weight: 500; }
    .stat-card.positive .stat-value { color: #059669; }
    .stat-card.negative .stat-value { color: #dc2626; }
    .stat-card.purple .stat-value { color: #7c3aed; }

    /* Improvement Bar */
    .improvement-section {
        background: white;
        border-radius: 20px;
        padding: 32px;
        margin-bottom: 32px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .section-title {
        font-size: 20px; font-weight: 800; color: #1e293b;
        margin-bottom: 24px; display: flex; align-items: center; gap: 12px;
    }
    .section-title .icon { font-size: 24px; }

    /* Student Table */
    .students-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        overflow: hidden;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
    }
    .students-table th {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 16px 20px;
        font-weight: 700;
        color: #475569;
        font-size: 13px;
        text-align: right;
        border-bottom: 2px solid #e2e8f0;
    }
    .students-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #334155;
        vertical-align: middle;
    }
    .students-table tr:hover td { background: #fafafe; }
    .students-table tr:last-child td { border-bottom: none; }
    
    .score-badge {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 6px 16px; border-radius: 10px;
        font-weight: 700; font-size: 14px; min-width: 50px;
    }
    .score-pre { background: #fef3c7; color: #d97706; }
    .score-post { background: #d1fae5; color: #059669; }
    .improvement-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 6px 14px; border-radius: 10px; font-weight: 700; font-size: 13px;
    }
    .improvement-positive { background: #d1fae5; color: #059669; }
    .improvement-negative { background: #fee2e2; color: #dc2626; }
    .improvement-neutral { background: #f1f5f9; color: #64748b; }

    /* Progress Bar */
    .progress-bar-container {
        width: 100%;
        height: 24px;
        background: #f1f5f9;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 12px;
        transition: width 1s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: white;
    }
    .progress-improved { background: linear-gradient(90deg, #34d399, #059669); }
    .progress-declined { background: linear-gradient(90deg, #f87171, #dc2626); }
    .progress-same { background: linear-gradient(90deg, #94a3b8, #64748b); }

    /* Chart Container */
    .chart-card {
        background: white;
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        margin-bottom: 32px;
    }
    .chart-bar-container { display: flex; flex-direction: column; gap: 16px; }
    .chart-bar-row { display: flex; align-items: center; gap: 16px; }
    .chart-bar-label { min-width: 120px; font-size: 13px; font-weight: 600; color: #475569; text-align: right; }
    .chart-bar-track { flex: 1; height: 28px; background: #f1f5f9; border-radius: 14px; overflow: hidden; position: relative; }
    .chart-bar-pre {
        position: absolute; top: 0; right: 0; height: 100%;
        background: linear-gradient(90deg, #fbbf24, #f59e0b);
        border-radius: 14px; transition: width 1.5s ease;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; color: white;
    }
    .chart-bar-post {
        position: absolute; top: 0; right: 0; height: 100%;
        background: linear-gradient(90deg, #34d399, #059669);
        border-radius: 14px; transition: width 1.5s ease;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; color: white;
    }
    .chart-legend { display: flex; gap: 24px; justify-content: center; margin-top: 20px; }
    .legend-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #475569; }
    .legend-dot { width: 14px; height: 14px; border-radius: 50%; }
    .legend-pre { background: #f59e0b; }
    .legend-post { background: #059669; }

    /* Empty State */
    .empty-state {
        text-align: center; padding: 60px 40px;
        background: white; border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .empty-state .icon { font-size: 64px; margin-bottom: 16px; display: block; }
    .empty-state h3 { font-size: 20px; font-weight: 700; color: #334155; margin-bottom: 8px; }
    .empty-state p { color: #64748b; font-size: 14px; }
    
    @media (max-width: 768px) {
        .comp-header { padding: 24px; }
        .comp-header h1 { font-size: 22px; }
        .stats-grid { grid-template-columns: 1fr 1fr; }
    }
</style>

<div class="comp-container">
    <!-- Header -->
    <div class="comp-header">
        <a href="{{ route('admin.surveys.index') }}" class="comp-back">→ العودة للاستبيانات</a>
        <h1>📊 تقرير المقارنة القبلي والبعدي</h1>
        <p>
            {{ $comparisonData['pre_survey']->title }}
            @if($comparisonData['lesson'])
                | الدرس: {{ $comparisonData['lesson']->title }}
                @if(optional($comparisonData['lesson']->concept)->value)
                    | القيمة: {{ $comparisonData['lesson']->concept->value->name }}
                @endif
            @elseif($comparisonData['value'] ?? null)
                | القيمة: {{ $comparisonData['value']->icon }} {{ $comparisonData['value']->name }}
            @endif
        </p>
    </div>

    @php $stats = $comparisonData['stats']; @endphp

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card purple">
            <span class="stat-icon">📝</span>
            <div class="stat-value">{{ $stats['total_pre_responses'] }}</div>
            <div class="stat-label">إجابات التقييم القبلي</div>
        </div>
        <div class="stat-card purple">
            <span class="stat-icon">✅</span>
            <div class="stat-value">{{ $stats['total_post_responses'] }}</div>
            <div class="stat-label">إجابات التقييم البعدي</div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">👥</span>
            <div class="stat-value">{{ $stats['completed_both'] }}</div>
            <div class="stat-label">أكملوا الاثنين</div>
        </div>
        <div class="stat-card {{ $stats['avg_improvement'] > 0 ? 'positive' : ($stats['avg_improvement'] < 0 ? 'negative' : '') }}">
            <span class="stat-icon">📈</span>
            <div class="stat-value">
                {{ $stats['avg_improvement'] > 0 ? '+' : '' }}{{ $stats['avg_improvement'] }}%
            </div>
            <div class="stat-label">متوسط التحسن</div>
        </div>
    </div>

    @if($stats['completed_both'] > 0)
    <!-- Improvement Distribution -->
    <div class="improvement-section">
        <h2 class="section-title">
            <span class="icon">📊</span>
            توزيع التحسن
        </h2>
        @php
            $total = $stats['completed_both'];
            $improvedPct = $total > 0 ? round(($stats['improved_count'] / $total) * 100) : 0;
            $declinedPct = $total > 0 ? round(($stats['declined_count'] / $total) * 100) : 0;
            $samePct = $total > 0 ? round(($stats['same_count'] / $total) * 100) : 0;
        @endphp
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
            <div style="text-align: center; padding: 20px; background: #d1fae5; border-radius: 14px;">
                <span style="font-size: 28px; display: block; margin-bottom: 8px;">📈</span>
                <div style="font-size: 32px; font-weight: 800; color: #059669;">{{ $stats['improved_count'] }}</div>
                <div style="font-size: 13px; color: #065f46; font-weight: 600;">تحسنوا ({{ $improvedPct }}%)</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #fee2e2; border-radius: 14px;">
                <span style="font-size: 28px; display: block; margin-bottom: 8px;">📉</span>
                <div style="font-size: 32px; font-weight: 800; color: #dc2626;">{{ $stats['declined_count'] }}</div>
                <div style="font-size: 13px; color: #991b1b; font-weight: 600;">تراجعوا ({{ $declinedPct }}%)</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f1f5f9; border-radius: 14px;">
                <span style="font-size: 28px; display: block; margin-bottom: 8px;">↔️</span>
                <div style="font-size: 32px; font-weight: 800; color: #64748b;">{{ $stats['same_count'] }}</div>
                <div style="font-size: 13px; color: #475569; font-weight: 600;">نفس المستوى ({{ $samePct }}%)</div>
            </div>
        </div>

        <div class="progress-bar-container">
            <div class="progress-bar-fill progress-improved" style="width: {{ $improvedPct }}%;">
                @if($improvedPct > 10) {{ $improvedPct }}% @endif
            </div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #94a3b8;">
            <span>تحسنوا</span>
            <span>تراجعوا</span>
        </div>
    </div>

    <!-- Questions Comparison Chart -->
    @if(count($comparisonData['questions']) > 0)
    <div class="chart-card">
        <h2 class="section-title">
            <span class="icon">📋</span>
            مقارنة الأسئلة (متوسط الإجابات)
        </h2>
        <div class="chart-bar-container">
            @foreach($comparisonData['questions'] as $question)
                @if(in_array($question->question_type, ['radio', 'select', 'rating', 'scale']))
                @php
                    $qId = (string) $question->id;
                    $preAvg = 0; $postAvg = 0; $count = 0;
                    foreach ($comparisonData['comparison'] as $c) {
                        foreach ($c['details'] as $d) {
                            if ($d['question'] === $question->question_text) {
                                $preAvg += is_numeric($d['pre_answer']) ? (float)$d['pre_answer'] : 0;
                                $postAvg += is_numeric($d['post_answer']) ? (float)$d['post_answer'] : 0;
                                $count++;
                            }
                        }
                    }
                    if ($count > 0) { $preAvg = round($preAvg / $count, 1); $postAvg = round($postAvg / $count, 1); }
                    $maxScale = $question->question_type === 'scale' ? 10 : 5;
                    $preWidth = $maxScale > 0 ? ($preAvg / $maxScale) * 100 : 0;
                    $postWidth = $maxScale > 0 ? ($postAvg / $maxScale) * 100 : 0;
                @endphp
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 8px;">{{ Str::limit($question->question_text, 60) }}</div>
                    <div class="chart-bar-row">
                        <div class="chart-bar-label">القبلي</div>
                        <div class="chart-bar-track">
                            <div class="chart-bar-pre" style="width: {{ min($preWidth, 100) }}%;">{{ $preAvg }}</div>
                        </div>
                    </div>
                    <div class="chart-bar-row" style="margin-top: 6px;">
                        <div class="chart-bar-label">البعدي</div>
                        <div class="chart-bar-track">
                            <div class="chart-bar-post" style="width: {{ min($postWidth, 100) }}%;">{{ $postAvg }}</div>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        <div class="chart-legend">
            <div class="legend-item"><span class="legend-dot legend-pre"></span> التقييم القبلي</div>
            <div class="legend-item"><span class="legend-dot legend-post"></span> التقييم البعدي</div>
        </div>
    </div>
    @endif

    <!-- Students Table -->
    <div class="improvement-section">
        <h2 class="section-title">
            <span class="icon">👥</span>
            تفاصيل الطلاب
        </h2>
        <div style="overflow-x: auto;">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الطالب</th>
                        <th>النتيجة القبلية</th>
                        <th>النتيجة البعدية</th>
                        <th>التحسن</th>
                        <th>تاريخ القبلي</th>
                        <th>تاريخ البعدي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparisonData['comparison'] as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div style="font-weight: 600;">
                                {{ $item['user']->name ?? 'مجهول' }}
                            </div>
                        </td>
                        <td>
                            <span class="score-badge score-pre">{{ $item['pre_score'] }}</span>
                        </td>
                        <td>
                            <span class="score-badge score-post">{{ $item['post_score'] }}</span>
                        </td>
                        <td>
                            @if($item['improvement'] > 0)
                                <span class="improvement-badge improvement-positive">📈 +{{ $item['improvement'] }}%</span>
                            @elseif($item['improvement'] < 0)
                                <span class="improvement-badge improvement-negative">📉 {{ $item['improvement'] }}%</span>
                            @else
                                <span class="improvement-badge improvement-neutral">↔️ 0%</span>
                            @endif
                        </td>
                        <td style="font-size: 13px; color: #64748b;">{{ $item['pre_date']->format('Y/m/d H:i') }}</td>
                        <td style="font-size: 13px; color: #64748b;">{{ $item['post_date']->format('Y/m/d H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="empty-state">
        <span class="icon">📭</span>
        <h3>لا توجد بيانات مقارنة بعد</h3>
        <p>لم يكمل أي طالب الاستبيانين القبلي والبعدي حتى الآن.<br>ستظهر المقارنة تلقائياً عند اكتمال الإجابات.</p>
        <a href="{{ route('admin.surveys.index') }}" style="display: inline-flex; align-items: center; gap: 8px; margin-top: 20px; padding: 12px 28px; background: linear-gradient(135deg, #7c3aed, #6d28d9); color: white; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 14px;">
            ← العودة للاستبيانات
        </a>
    </div>
    @endif
</div>
@endsection
