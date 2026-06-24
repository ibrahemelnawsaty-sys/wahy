<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير فصل: {{ $classroom->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif !important;
        }

        body {
            background: #f0f2f5;
            color: #1a202c;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* === SCREEN STYLES === */
        .page-wrapper {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Action Bar - only on screen */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 12px;
            flex-wrap: wrap;
        }
        .action-btn {
            padding: 12px 28px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            border: none;
        }
        .btn-print {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
        }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 12px 35px rgba(102, 126, 234, 0.45); }
        .btn-back {
            background: white;
            color: #4a5568;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .btn-back:hover { background: #f7fafc; border-color: #cbd5e0; }

        /* === A4 PRINT DOCUMENT === */
        .a4-document {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* Report Header */
        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        .report-header::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .report-header::after {
            content: '';
            position: absolute;
            bottom: -40px; left: -40px;
            width: 150px; height: 150px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }

        .header-content { position: relative; z-index: 1; }
        .header-title {
            font-size: 30px;
            font-weight: 800;
            color: white;
            margin-bottom: 6px;
        }
        .header-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 15px;
            margin-bottom: 3px;
        }
        .header-date {
            color: rgba(255,255,255,0.7);
            font-size: 13px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 25px;
        }
        .info-item {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 14px 18px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .info-label { color: rgba(255,255,255,0.8); font-size: 12px; font-weight: 600; }
        .info-value { color: white; font-size: 17px; font-weight: 700; margin-top: 3px; }

        /* Report Body */
        .report-body {
            padding: 35px 40px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 35px;
        }
        .stat-card {
            text-align: center;
            padding: 22px 16px;
            background: #f8fafc;
            border-radius: 14px;
            border: 2px solid #edf2f7;
        }
        .stat-icon { font-size: 34px; margin-bottom: 8px; }
        .stat-value { font-size: 30px; font-weight: 800; margin-bottom: 4px; }
        .stat-label { font-size: 13px; color: #718096; font-weight: 600; }

        /* Section */
        .section {
            margin-bottom: 30px;
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #edf2f7;
        }
        .section-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a202c;
        }
        .section-badge {
            margin-right: auto;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .data-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 14px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }
        .data-table td {
            padding: 11px 14px;
            text-align: center;
            color: #2d3748;
            border-bottom: 1px solid #f0f0f0;
        }
        .data-table tbody tr:last-child td { border-bottom: none; }

        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px; height: 30px;
            border-radius: 50%;
            font-weight: 800;
            font-size: 13px;
        }
        .rank-1 { background: linear-gradient(135deg, #ffd700, #ffb700); color: #7c5800; }
        .rank-2 { background: linear-gradient(135deg, #c0c0c0, #a0a0a0); color: #4a4a4a; }
        .rank-3 { background: linear-gradient(135deg, #cd7f32, #b87333); color: white; }
        .rank-default { background: #f0f0f0; color: #718096; }

        .score-bar {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 4px;
        }
        .score-fill {
            height: 100%;
            border-radius: 10px;
        }
        .score-good { background: linear-gradient(90deg, #48bb78, #38a169); }
        .score-mid { background: linear-gradient(90deg, #ecc94b, #d69e2e); }
        .score-low { background: linear-gradient(90deg, #f56565, #e53e3e); }

        .no-data-box {
            text-align: center;
            padding: 40px;
            color: #a0aec0;
        }
        .no-data-box .icon { font-size: 50px; margin-bottom: 12px; opacity: 0.5; }
        .no-data-box .text { font-size: 16px; font-weight: 600; }

        /* Footer */
        .report-footer {
            text-align: center;
            padding: 20px 40px;
            color: #a0aec0;
            font-size: 12px;
            border-top: 2px solid #edf2f7;
        }

        /* === PRINT STYLES === */
        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }

            body {
                background: white !important;
                margin: 0;
                padding: 0;
            }

            .page-wrapper {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            .action-bar {
                display: none !important;
            }

            .a4-document {
                border-radius: 0;
                box-shadow: none;
            }

            .report-header {
                padding: 25px 30px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .header-title { font-size: 24px; }

            .info-grid {
                gap: 8px;
            }
            .info-item {
                padding: 10px 14px;
            }

            .report-body {
                padding: 25px 30px;
            }

            .stats-grid {
                gap: 10px;
                margin-bottom: 25px;
            }
            .stat-card {
                padding: 16px 12px;
                border: 1px solid #d0d5dd;
            }
            .stat-icon { font-size: 26px; }
            .stat-value { font-size: 24px; }

            .section {
                break-inside: avoid;
            }

            .data-table th,
            .data-table td {
                padding: 8px 10px;
                font-size: 12px;
            }

            .report-footer {
                padding: 15px 30px;
            }

            /* Ensure gradients print */
            .report-header,
            .data-table th,
            .rank-1, .rank-2, .rank-3,
            .score-fill {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        @media (max-width: 768px) {
            .info-grid { grid-template-columns: repeat(2, 1fr); }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .report-header { padding: 25px; }
            .report-body { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    <!-- Action Bar (hidden when printing) -->
    <div class="action-bar">
        <a href="{{ route('teacher.classrooms') }}" class="action-btn btn-back">
            ↩️ رجوع للفصول
        </a>
        <button onclick="window.print()" class="action-btn btn-print">
            🖨️ طباعة التقرير
        </button>
    </div>

    <!-- A4 Document -->
    <div class="a4-document">

        <!-- Header -->
        <div class="report-header">
            <div class="header-content">
                <h1 class="header-title">📊 تقرير الفصل الدراسي</h1>
                <p class="header-subtitle">منصة وحي - نظام إدارة القيم التعليمية</p>
                <p class="header-date">{{ now()->translatedFormat('l j F Y - h:i A') }}</p>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">اسم الفصل</div>
                        <div class="info-value">{{ $classroom->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">المعلم</div>
                        <div class="info-value">{{ $classroom->teacher->name ?? 'غير محدد' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">عدد الطلاب</div>
                        <div class="info-value">{{ $students->count() }} طالب</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">الصف الدراسي</div>
                        <div class="info-value">{{ $classroom->grade_level ?? $classroom->grade ?? 'غير محدد' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="report-body">

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-value" style="color: #667eea;">{{ number_format($classStats['average_performance'], 1) }}%</div>
                    <div class="stat-label">متوسط الأداء</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value" style="color: #48bb78;">{{ $classStats['completed_activities'] }}</div>
                    <div class="stat-label">الأنشطة المكتملة</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value" style="color: #ed8936;">{{ $classStats['pending_activities'] }}</div>
                    <div class="stat-label">الأنشطة المعلقة</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-value" style="color: #9f7aea;">{{ number_format($classStats['completion_rate'], 1) }}%</div>
                    <div class="stat-label">نسبة الإكمال</div>
                </div>
            </div>

            <!-- Students Performance -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon" style="background: linear-gradient(135deg, #667eea20, #764ba220);">👥</div>
                    <div class="section-title">أداء الطلاب</div>
                    <div class="section-badge" style="background: #667eea15; color: #667eea;">
                        {{ $students->count() }} طالب
                    </div>
                </div>

                @if($students->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
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
                        <tr>
                            <td>
                                @if($index < 3)
                                    <span class="rank-badge rank-{{ $index + 1 }}">
                                        {{ $index === 0 ? '🥇' : ($index === 1 ? '🥈' : '🥉') }}
                                    </span>
                                @else
                                    <span class="rank-badge rank-default">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td style="font-weight: 600; text-align: right; padding-right: 18px;">
                                {{ $student->name }}
                            </td>
                            <td>
                                <span style="background: #667eea15; color: #667eea; padding: 3px 10px; border-radius: 8px; font-weight: 700; font-size: 12px;">
                                    {{ $student->level ?? 1 }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 700;">{{ $student->completed_activities }}</span>
                            </td>
                            <td>
                                @php
                                    $scoreColor = $student->average_score >= 80 ? 'good' : ($student->average_score >= 50 ? 'mid' : 'low');
                                @endphp
                                <div>
                                    <span style="font-weight: 700;">{{ number_format($student->average_score, 1) }}%</span>
                                    <div class="score-bar">
                                        <div class="score-fill score-{{ $scoreColor }}" style="width: {{ min($student->average_score, 100) }}%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: #d69e2e;">⭐ {{ number_format($student->total_points) }}</span>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: #9f7aea;">🏅 {{ $student->total_badges }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="no-data-box">
                    <div class="icon">👥</div>
                    <div class="text">لا يوجد طلاب في هذا الفصل</div>
                </div>
                @endif
            </div>

            <!-- Activities Summary -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon" style="background: linear-gradient(135deg, #48bb7820, #38a16920);">📝</div>
                    <div class="section-title">ملخص الأنشطة</div>
                    <div class="section-badge" style="background: #48bb7815; color: #48bb78;">
                        {{ $activities->count() }} نشاط
                    </div>
                </div>

                @if($activities->count() > 0)
                <table class="data-table">
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
                            <td style="font-weight: 600; text-align: right; padding-right: 18px;">{{ $activity->title }}</td>
                            <td>
                                @php
                                    $typeInfo = match($activity->type) {
                                        'quiz' => ['اختبار', '📝', '#667eea'],
                                        'exercise' => ['تمرين', '📋', '#48bb78'],
                                        'project' => ['مشروع', '🏗️', '#ed8936'],
                                        'upload' => ['تحميل', '📤', '#9f7aea'],
                                        'practical' => ['عملي', '🎯', '#e53e3e'],
                                        default => ['نشاط', '📄', '#718096'],
                                    };
                                @endphp
                                <span style="background: {{ $typeInfo[2] }}15; color: {{ $typeInfo[2] }}; padding: 3px 10px; border-radius: 8px; font-weight: 700; font-size: 12px;">
                                    {{ $typeInfo[1] }} {{ $typeInfo[0] }}
                                </span>
                            </td>
                            <td><span style="font-weight: 700;">{{ $activity->submissions_count }}</span></td>
                            <td>
                                @php
                                    $actScore = $activity->average_score ?? 0;
                                    $actColor = $actScore >= 80 ? 'good' : ($actScore >= 50 ? 'mid' : 'low');
                                @endphp
                                <div>
                                    <span style="font-weight: 700;">{{ number_format($actScore, 1) }}%</span>
                                    <div class="score-bar">
                                        <div class="score-fill score-{{ $actColor }}" style="width: {{ min($actScore, 100) }}%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td style="color: #718096; font-size: 12px;">{{ $activity->created_at->format('Y-m-d') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="no-data-box">
                    <div class="icon">📝</div>
                    <div class="text">لا توجد أنشطة مسجلة لهذا الفصل</div>
                </div>
                @endif
            </div>

        </div>

        <!-- Footer -->
        <div class="report-footer">
            <p>تم إنشاء هذا التقرير بواسطة منصة وحي التعليمية</p>
            <p>جميع الحقوق محفوظة © {{ now()->year }}</p>
        </div>

    </div>
</div>

</body>
</html>
