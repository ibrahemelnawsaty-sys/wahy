@extends('layouts.school-admin')

@section('page-title', 'تفاعل أولياء الأمور')

@section('content')
<style>
    .pe-card-light { background:white; border-radius:14px; padding:24px; box-shadow:0 6px 24px rgba(15,23,42,.06); margin-bottom:24px; }
    .pe-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:14px; margin-bottom:24px; }
    .pe-stat { background:#f8fafc; border-radius:12px; padding:18px; text-align:center; border-right:4px solid #6366f1; }
    .pe-stat .num { font-size:30px; font-weight:800; color:#0f172a; line-height:1; }
    .pe-stat .lbl { font-size:13px; color:#64748b; margin-top:6px; }
    .pe-row { display:flex; gap:14px; align-items:center; padding:14px 0; border-bottom:1px solid #f1f5f9; }
    .pe-row:last-child { border-bottom:none; }
    .pe-avatar { width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg,#6366f1,#8b5cf6); display:flex; align-items:center; justify-content:center; color:white; font-weight:800; font-size:20px; flex-shrink:0; }
    .pe-info { flex:1; min-width:160px; }
    .pe-info h4 { margin:0 0 2px; font-size:15px; font-weight:700; color:#0f172a; }
    .pe-info p { margin:0; color:#64748b; font-size:12px; }
    .pe-metrics { display:flex; gap:8px; flex-wrap:wrap; }
    .pe-metric { background:#eef2ff; color:#4338ca; padding:5px 10px; border-radius:8px; font-size:12px; font-weight:700; }
    .badge-active { background:#ecfdf5; color:#047857; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700; margin-inline-start:6px; }
    .badge-quiet { background:#f1f5f9; color:#64748b; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700; margin-inline-start:6px; }
</style>

<div class="pe-card-light">
    <h2 style="margin:0 0 20px;font-size:22px;font-weight:800;color:#0f172a;">👨‍👩‍👧 تفاعل أولياء الأمور</h2>

    <div class="pe-stats">
        <div class="pe-stat"><div class="num">{{ $totals['parents_count'] }}</div><div class="lbl">إجمالي أولياء الأمور</div></div>
        <div class="pe-stat"><div class="num">{{ $totals['active_parents'] }}</div><div class="lbl">المتفاعلون</div></div>
        <div class="pe-stat"><div class="num">{{ $totals['total_praises'] }}</div><div class="lbl">رسائل تشجيع</div></div>
        <div class="pe-stat"><div class="num">{{ $totals['total_gifts'] }}</div><div class="lbl">هدايا</div></div>
        <div class="pe-stat"><div class="num">{{ $totals['total_messages'] }}</div><div class="lbl">رسائل للمعلمين</div></div>
    </div>

    @forelse($rows as $row)
        <div class="pe-row">
            <div class="pe-avatar">{{ mb_substr($row['name'], 0, 1) }}</div>
            <div class="pe-info">
                <h4>
                    {{ $row['name'] }}
                    @if($row['engagement_score'] > 0) <span class="badge-active">نشط</span>
                    @else <span class="badge-quiet">صامت</span>
                    @endif
                </h4>
                <p>
                    {{ $row['email'] ?? '—' }}
                    @if($row['last_engagement']) · آخر تفاعل: {{ $row['last_engagement']->diffForHumans() }} @endif
                </p>
            </div>
            <div class="pe-metrics">
                <span class="pe-metric">💬 {{ $row['praises_count'] }} تشجيع</span>
                <span class="pe-metric">🎁 {{ $row['gifts_count'] }} هدية</span>
                <span class="pe-metric">📨 {{ $row['messages_count'] }} رسالة</span>
                @if($row['gifts_points'] > 0)
                    <span class="pe-metric">⭐ {{ $row['gifts_points'] }} نقطة</span>
                @endif
            </div>
        </div>
    @empty
        <div style="text-align:center;padding:40px;color:#94a3b8;">
            <div style="font-size:48px;margin-bottom:14px;">👋</div>
            <p>لا يوجد أولياء أمور بعد. سيظهرون هنا عند التسجيل وربطهم بالطلاب.</p>
        </div>
    @endforelse
</div>
@endsection
