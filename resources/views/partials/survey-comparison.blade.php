{{--
    Partial مشترك لعرض مقارنة الاستبيان القبلي/البعدي.
    يُستخدم في: admin, school-admin, teacher, parent.
    المتطلبات: $survey, $comparisonData
--}}

<style>
.cmp-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 18px rgba(0,0,0,0.06); margin-bottom: 20px; }
.cmp-stat { display: inline-block; min-width: 160px; text-align: center; padding: 16px; }
.cmp-stat .val { font-size: 32px; font-weight: 800; color: var(--color-primary, #667eea); }
.cmp-stat .lbl { font-size: 13px; color: #64748b; margin-top: 4px; }
.cmp-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.cmp-table th { background: #f8fafc; padding: 12px; text-align: right; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; }
.cmp-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; }
.cmp-table tr:hover { background: #f8fafc; }
.improvement-pos { color: #16a34a; font-weight: 700; }
.improvement-neg { color: #dc2626; font-weight: 700; }
.improvement-zero { color: #64748b; font-weight: 600; }
.bar-container { background: #e2e8f0; border-radius: 8px; height: 28px; position: relative; overflow: hidden; }
.bar-fill { height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px; transition: width 0.5s ease; }
.bar-pre { background: linear-gradient(135deg, #94a3b8, #64748b); }
.bar-post { background: linear-gradient(135deg, #10b981, #059669); }
</style>

<div class="cmp-card">
    <h2 style="font-size: 22px; margin-bottom: 8px; color: #1e293b;">📊 مقارنة: {{ $survey->title }}</h2>
    <p style="color: #64748b; margin-bottom: 16px; font-size: 14px;">
        @if($survey->lesson)
            الدرس: {{ $survey->lesson->title }} —
            @if($survey->lesson->concept && $survey->lesson->concept->value)
                القيمة: {{ $survey->lesson->concept->value->name }}
            @endif
        @endif
    </p>

    @if(isset($comparisonData['error']))
        <div style="background: #fef2f2; color: #991b1b; padding: 14px; border-radius: 10px;">
            ⚠️ {{ $comparisonData['error'] }}
        </div>
    @elseif(empty($comparisonData['comparison']))
        <div style="background: #fef3c7; color: #92400e; padding: 14px; border-radius: 10px;">
            ℹ️ لا يوجد طلاب أكملوا الاستبيانين القبلي والبعدي بعد.
        </div>
    @else
        {{-- ملخص إجمالي --}}
        <div style="display: flex; flex-wrap: wrap; gap: 16px; background: linear-gradient(135deg, rgba(102,126,234,0.05), rgba(118,75,162,0.05)); padding: 20px; border-radius: 12px; margin-bottom: 24px;">
            <div class="cmp-stat">
                <div class="val">{{ count($comparisonData['comparison']) }}</div>
                <div class="lbl">عدد المشاركين</div>
            </div>
            <div class="cmp-stat">
                <div class="val">{{ round($comparisonData['average_pre'] ?? 0, 1) }}</div>
                <div class="lbl">متوسط القبلي</div>
            </div>
            <div class="cmp-stat">
                <div class="val">{{ round($comparisonData['average_post'] ?? 0, 1) }}</div>
                <div class="lbl">متوسط البعدي</div>
            </div>
            <div class="cmp-stat">
                @php $avgImp = round($comparisonData['average_improvement'] ?? 0, 1); @endphp
                <div class="val" style="color: {{ $avgImp > 0 ? '#16a34a' : ($avgImp < 0 ? '#dc2626' : '#64748b') }}">
                    {{ $avgImp > 0 ? '+' : '' }}{{ $avgImp }}%
                </div>
                <div class="lbl">متوسط التحسن</div>
            </div>
        </div>

        {{-- الرسم البياني — متوسط القبلي vs البعدي --}}
        <div style="margin-bottom: 24px;">
            <h3 style="font-size: 16px; margin-bottom: 12px; color: #1e293b;">المقارنة الإجمالية</h3>
            @php
                $maxScore = max($comparisonData['average_pre'] ?? 1, $comparisonData['average_post'] ?? 1, 1);
                $preWidth = ($comparisonData['average_pre'] / $maxScore) * 100;
                $postWidth = ($comparisonData['average_post'] / $maxScore) * 100;
            @endphp
            <div style="margin-bottom: 12px;">
                <div style="font-size: 13px; margin-bottom: 4px; color: #64748b;">📋 قبل التعلّم</div>
                <div class="bar-container">
                    <div class="bar-fill bar-pre" style="width: {{ $preWidth }}%">
                        {{ round($comparisonData['average_pre'] ?? 0, 1) }}
                    </div>
                </div>
            </div>
            <div>
                <div style="font-size: 13px; margin-bottom: 4px; color: #64748b;">🎯 بعد التعلّم</div>
                <div class="bar-container">
                    <div class="bar-fill bar-post" style="width: {{ $postWidth }}%">
                        {{ round($comparisonData['average_post'] ?? 0, 1) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- تفصيل الطلاب --}}
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <h3 style="font-size: 16px; margin-bottom: 12px; color: #1e293b;">تفصيل المشاركين</h3>
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th>الطالب</th>
                        <th>قبل</th>
                        <th>بعد</th>
                        <th>التحسن</th>
                        <th>تاريخ القبلي</th>
                        <th>تاريخ البعدي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparisonData['comparison'] as $row)
                    <tr>
                        <td>
                            <strong>{{ $row['user']->name ?? '—' }}</strong>
                            <div style="font-size: 12px; color: #94a3b8;">{{ $row['user']->email ?? '' }}</div>
                        </td>
                        <td>{{ $row['pre_score'] }}</td>
                        <td>{{ $row['post_score'] }}</td>
                        <td>
                            @php $imp = $row['improvement']; @endphp
                            <span class="@if($imp > 0) improvement-pos @elseif($imp < 0) improvement-neg @else improvement-zero @endif">
                                {{ $imp > 0 ? '+' : '' }}{{ $imp }}%
                            </span>
                        </td>
                        <td style="font-size: 13px; color: #64748b;">{{ optional($row['pre_date'])->format('Y-m-d') }}</td>
                        <td style="font-size: 13px; color: #64748b;">{{ optional($row['post_date'])->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
