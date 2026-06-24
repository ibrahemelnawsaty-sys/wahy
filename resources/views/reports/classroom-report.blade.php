<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تقرير الفصل الدراسي</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #10B981;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #10B981;
            margin: 0 0 5px 0;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .info-box {
            background: #F0FDF4;
            border: 2px solid #10B981;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-label {
            font-weight: bold;
            color: #065F46;
        }
        .info-value {
            color: #111827;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background: #10B981;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #F0FDF4 0%, #D1FAE5 100%);
            border: 2px solid #10B981;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #065F46;
            margin: 5px 0;
        }
        .stat-label {
            font-size: 12px;
            color: #047857;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #D1D5DB;
            padding: 10px;
            text-align: center;
            font-size: 12px;
        }
        table th {
            background: #D1FAE5;
            color: #065F46;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background: #F9FAFB;
        }
        .top-performer {
            background: #FEF3C7 !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #D1D5DB;
            text-align: center;
            font-size: 12px;
            color: #9CA3AF;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #9CA3AF;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📚 تقرير الفصل الدراسي</h1>
        <p>منصة قِيَم - نظام إدارة القيم التعليمية</p>
        <p>{{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <!-- Classroom Info -->
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">اسم الفصل:</span>
            <span class="info-value">{{ $classroom->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">المعلم:</span>
            <span class="info-value">{{ $classroom->teacher->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">عدد الطلاب:</span>
            <span class="info-value">{{ $students->count() }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">الصف الدراسي:</span>
            <span class="info-value">{{ $classroom->grade }}</span>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="section">
        <div class="section-title">📊 الإحصائيات العامة للفصل</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">متوسط الأداء</div>
                <div class="stat-value">{{ number_format($classStats['average_performance'], 1) }}%</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">الأنشطة المكتملة</div>
                <div class="stat-value">{{ $classStats['completed_activities'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">الأنشطة المعلقة</div>
                <div class="stat-value">{{ $classStats['pending_activities'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">نسبة الإكمال</div>
                <div class="stat-value">{{ number_format($classStats['completion_rate'], 1) }}%</div>
            </div>
        </div>
    </div>

    <!-- Students Performance -->
    <div class="section">
        <div class="section-title">👥 أداء الطلاب</div>
        @if($students->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الطالب</th>
                        <th>المستوى</th>
                        <th>الأنشطة المكتملة</th>
                        <th>متوسط الدرجات</th>
                        <th>النقاط</th>
                        <th>الشارات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $index => $student)
                        <tr class="{{ $index < 3 ? 'top-performer' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->level }}</td>
                            <td>{{ $student->completed_activities }}</td>
                            <td>{{ number_format($student->average_score, 1) }}%</td>
                            <td>{{ number_format($student->total_points) }}</td>
                            <td>{{ $student->total_badges }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">لا يوجد طلاب في هذا الفصل</div>
        @endif
    </div>

    <!-- Activities Summary -->
    <div class="section">
        <div class="section-title">📝 ملخص الأنشطة</div>
        @if($activities->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>النشاط</th>
                        <th>النوع</th>
                        <th>عدد المشاركين</th>
                        <th>متوسط الدرجات</th>
                        <th>تاريخ الإنشاء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                        <tr>
                            <td>{{ $activity->title }}</td>
                            <td>
                                @if($activity->type === 'quiz') اختبار
                                @elseif($activity->type === 'upload') تحميل
                                @elseif($activity->type === 'practical') عملي
                                @else مناقشة
                                @endif
                            </td>
                            <td>{{ $activity->submissions_count }}</td>
                            <td>{{ number_format($activity->average_score ?? 0, 1) }}%</td>
                            <td>{{ $activity->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">لا توجد أنشطة مسجلة</div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>تم إنشاء هذا التقرير بواسطة منصة قِيَم التعليمية</p>
        <p>جميع الحقوق محفوظة © {{ now()->year }}</p>
    </div>
</body>
</html>
