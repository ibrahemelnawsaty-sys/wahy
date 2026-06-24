<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} — منصة وحي</title>
    <style>
        @page { margin: 18mm 14mm; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            direction: rtl;
        }
        .hdr { border-bottom: 2px solid #6366f1; padding-bottom: 10px; margin-bottom: 18px; }
        .hdr h1 { margin: 0; font-size: 22px; color: #4338ca; }
        .hdr .meta { color: #64748b; font-size: 10px; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 7px 9px; text-align: right; vertical-align: top; }
        th { background: #f1f5f9; color: #0f172a; font-size: 11px; }
        tr:nth-child(even) td { background: #fafafa; }
        .footer { margin-top: 22px; text-align: center; color: #94a3b8; font-size: 9px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 8px; font-size: 9px; }
        .b-up   { background:#ecfdf5; color:#047857; }
        .b-down { background:#fef2f2; color:#b91c1c; }
        .b-mute { background:#f1f5f9; color:#475569; }
    </style>
</head>
<body>
    <div class="hdr">
        <h1>📊 {{ $title }}</h1>
        <div class="meta">
            تاريخ التوليد: {{ $generatedAt }}
            @if(!empty($generatedBy)) · بواسطة: {{ $generatedBy }} @endif
            · إجمالي السجلات: {{ count($rows ?? []) }}
        </div>
    </div>

    @if(($rows ?? collect())->isEmpty())
        <p style="text-align:center;color:#94a3b8;padding:30px;">لا توجد بيانات لعرضها.</p>
    @else
        @switch($type)
            @case('students')
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>المدرسة</th>
                            <th style="width:70px;">النقاط</th>
                            <th style="width:90px;">عدد التسليمات</th>
                            <th style="width:70px;">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $s)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->email }}</td>
                                <td>{{ $s->school->name ?? '—' }}</td>
                                <td>{{ (int) ($s->total_points ?? 0) }}</td>
                                <td>{{ $s->activity_submissions_count ?? 0 }}</td>
                                <td>
                                    <span class="badge {{ $s->status === 'active' ? 'b-up' : 'b-mute' }}">
                                        {{ $s->status === 'active' ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @case('teachers')
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>المدرسة</th>
                            <th style="width:70px;">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $t)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $t->name }}</td>
                                <td>{{ $t->email }}</td>
                                <td>{{ $t->school->name ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $t->status === 'active' ? 'b-up' : 'b-mute' }}">
                                        {{ $t->status === 'active' ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @case('schools')
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>اسم المدرسة</th>
                            <th>المدينة</th>
                            <th style="width:80px;">الطلاب</th>
                            <th style="width:80px;">المعلمون</th>
                            <th style="width:90px;">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $sch)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $sch->name }}</td>
                                <td>{{ $sch->city ?? '—' }}</td>
                                <td>{{ $sch->students_count ?? 0 }}</td>
                                <td>{{ $sch->teachers_count ?? 0 }}</td>
                                <td>
                                    <span class="badge {{ $sch->status === 'active' ? 'b-up' : 'b-mute' }}">
                                        {{ $sch->status === 'active' ? 'نشطة' : 'غير نشطة' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @case('activities')
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>عنوان النشاط</th>
                            <th>القيمة</th>
                            <th style="width:80px;">النوع</th>
                            <th style="width:80px;">التسليمات</th>
                            <th style="width:90px;">متوسط الدرجة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $a)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $a->title }}</td>
                                <td>{{ $a->lesson->concept->value->name ?? '—' }}</td>
                                <td>{{ $a->type }}</td>
                                <td>{{ $a->submissions_count ?? 0 }}</td>
                                <td>{{ $a->average_score ? number_format($a->average_score, 1) . '%' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @case('values')
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>اسم القيمة</th>
                            <th>الوصف</th>
                            <th style="width:90px;">عدد المفاهيم</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $v)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $v->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($v->description ?? '', 120) }}</td>
                                <td>{{ $v->concepts_count ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @default
                <p style="text-align:center;color:#94a3b8;">نوع تقرير غير معروف.</p>
        @endswitch
    @endif

    <div class="footer">
        منصة وحي · تم التوليد آلياً · {{ $generatedAt }}
    </div>
</body>
</html>
