@extends('layouts.teacher')
@section('title', 'مقارنات الاستبيانات')

@section('content')
<div style="padding: 24px; max-width: 1200px; margin: 0 auto;">
    <h1 style="font-size: 22px; margin-bottom: 8px; color: #1e293b;">📊 مقارنات الاستبيانات القبلية / البعدية</h1>
    <p style="color: #64748b; margin-bottom: 24px;">
        قارن نتائج طلاب فصولك في التقييم القبلي مقابل البعدي لقياس أثر الدروس على القيم.
    </p>

    {{-- ===== KPIs ===== --}}
    @php
        $kpiCards = [
            ['📚', $kpis['pairs'], 'تقييمات (قبلي+بعدي)', '#6366f1'],
            ['📝', $kpis['total_pre'], 'إجابات القبلي', '#f59e0b'],
            ['📈', $kpis['total_post'], 'إجابات البعدي', '#10b981'],
            ['👥', $kpis['completed_both'], 'أكملوا الاثنين', '#0ea5e9'],
            [($kpis['avg_improvement'] >= 0 ? '⬆️' : '⬇️'), ($kpis['avg_improvement'] > 0 ? '+' : '') . $kpis['avg_improvement'] . '%', 'متوسط التحسّن', ($kpis['avg_improvement'] >= 0 ? '#16a34a' : '#ef4444')],
            ['🏆', $kpis['improved'] . ' / ' . $kpis['declined'], 'تحسّنوا / تراجعوا', '#8b5cf6'],
        ];
    @endphp
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 28px;">
        @foreach($kpiCards as [$icon, $val, $label, $color])
        <div style="background: white; border-radius: 14px; padding: 18px; box-shadow: 0 4px 14px rgba(0,0,0,0.05); border-top: 4px solid {{ $color }}; text-align: center;">
            <div style="font-size: 26px; margin-bottom: 6px;">{{ $icon }}</div>
            <div style="font-size: 24px; font-weight: 800; color: {{ $color }}; line-height: 1;">{{ $val }}</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 6px;">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    @if($rows->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 18px;">
        @foreach($rows as $row)
        @php $st = $row['stats']; $imp = $st['avg_improvement'] ?? 0; @endphp
        <div style="background: white; border-radius: 16px; padding: 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border-right: 4px solid #8b5cf6;">
            {{-- الدرس --}}
            @if($row['lesson'])
                <p style="font-size: 13px; color: #64748b; margin: 0 0 2px;">📚 {{ $row['lesson']->title }}</p>
                @if($row['lesson']->concept?->value)
                    <p style="font-size: 12px; color: #8b5cf6; margin: 0 0 14px; font-weight: 600;">💎 {{ $row['lesson']->concept->value->name }}</p>
                @endif
            @endif

            {{-- القبلي والبعدي جنباً إلى جنب --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 10px 12px;">
                    <div style="font-size: 11px; color: #d97706; font-weight: 700; margin-bottom: 4px;">📝 التقييم القبلي</div>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 600; line-height: 1.4;">{{ $row['pre']->title ?? '— غير مرتبط' }}</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 6px;">الإجابات: <strong>{{ $st['total_pre_responses'] ?? 0 }}</strong></div>
                </div>
                <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 10px 12px;">
                    <div style="font-size: 11px; color: #059669; font-weight: 700; margin-bottom: 4px;">📈 التقييم البعدي</div>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 600; line-height: 1.4;">{{ $row['post']->title ?? '—' }}</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 6px;">الإجابات: <strong>{{ $st['total_post_responses'] ?? 0 }}</strong></div>
                </div>
            </div>

            {{-- المقارنة --}}
            <div style="background: linear-gradient(135deg,#ede9fe,#ddd6fe); border-radius: 10px; padding: 12px 14px; margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: #6d28d9; font-weight: 700;">📊 المقارنة (طلابي)</span>
                    <span style="font-size: 18px; font-weight: 800; color: {{ $imp >= 0 ? '#16a34a' : '#ef4444' }};">
                        {{ $imp > 0 ? '+' : '' }}{{ $imp }}%
                    </span>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 8px; font-size: 12px; color: #4c1d95;">
                    <span>👥 أكملوا: <strong>{{ $st['completed_both'] ?? 0 }}</strong></span>
                    <span>🟢 تحسّن: <strong>{{ $st['improved_count'] ?? 0 }}</strong></span>
                    <span>🔴 تراجع: <strong>{{ $st['declined_count'] ?? 0 }}</strong></span>
                </div>
            </div>

            <a href="{{ route('teacher.surveys.comparison', $row['post']->id) }}"
               style="display: block; text-align: center; background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: white; padding: 10px 18px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px;">
                عرض المقارنة التفصيلية 📊
            </a>
        </div>
        @endforeach
    </div>
    @else
    <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
        <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
        <p style="font-size: 16px; font-weight: 600;">لا توجد استبيانات تقييم قبلي/بعدي حالياً.</p>
    </div>
    @endif
</div>
@endsection
