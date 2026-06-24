@extends('layouts.teacher')

@section('title', 'تفاعل أولياء الأمور')

@section('content')
<style>
    .pe-page { max-width: 1100px; margin: 0 auto; padding: 24px; direction: rtl; color: white; }
    .pe-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:14px; }
    .pe-title { font-size:26px; font-weight:800; margin:0; color:white; }
    .pe-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:14px; margin-bottom:24px; }
    .pe-stat { background:rgba(255,255,255,.12); backdrop-filter:blur(20px); border:1px solid rgba(255,255,255,.2); border-radius:14px; padding:18px; text-align:center; }
    .pe-stat .num { font-size:30px; font-weight:800; line-height:1; color:#fbbf24; }
    .pe-stat .lbl { font-size:13px; opacity:.85; margin-top:6px; }
    .pe-card { background:rgba(255,255,255,0.08); backdrop-filter:blur(24px); border:1px solid rgba(255,255,255,.15); border-radius:18px; padding:18px 20px; margin-bottom:14px; display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
    .pe-avatar { width:54px; height:54px; border-radius:50%; background:linear-gradient(135deg, #6366f1, #8b5cf6); display:flex; align-items:center; justify-content:center; font-weight:800; color:white; font-size:22px; flex-shrink:0; }
    .pe-info { flex:1; min-width:200px; }
    .pe-info h3 { margin:0 0 4px; font-size:16px; font-weight:700; color:white; }
    .pe-info p { margin:0; opacity:.7; font-size:13px; }
    .pe-metrics { display:flex; gap:14px; flex-wrap:wrap; }
    .pe-metric { background:rgba(255,255,255,.1); padding:8px 14px; border-radius:10px; font-size:13px; min-width:80px; text-align:center; }
    .pe-metric .v { font-weight:800; font-size:18px; }
    .pe-metric .l { opacity:.75; font-size:11px; }
    .pe-empty { background:rgba(255,255,255,.08); border:1px dashed rgba(255,255,255,.25); border-radius:18px; padding:50px 20px; text-align:center; }
    .badge-active { background:rgba(16,185,129,.25); color:#a7f3d0; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700; }
    .badge-quiet  { background:rgba(255,255,255,.1); color:rgba(255,255,255,.6); padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700; }
</style>

<div class="pe-page">
    <div class="pe-header">
        <h1 class="pe-title">👨‍👩‍👧 تفاعل أولياء الأمور</h1>
        <a href="{{ route('teacher.dashboard') }}" style="color:white;text-decoration:none;background:rgba(255,255,255,.15);padding:10px 18px;border-radius:10px;font-weight:600;">← لوحة التحكم</a>
    </div>

    <div class="pe-stats">
        <div class="pe-stat">
            <div class="num">{{ $totals['parents_count'] }}</div>
            <div class="lbl">إجمالي أولياء الأمور</div>
        </div>
        <div class="pe-stat">
            <div class="num">{{ $totals['active_parents'] }}</div>
            <div class="lbl">المتفاعلون</div>
        </div>
        <div class="pe-stat">
            <div class="num">{{ $totals['total_praises'] }}</div>
            <div class="lbl">رسائل تشجيع</div>
        </div>
        <div class="pe-stat">
            <div class="num">{{ $totals['total_gifts'] }}</div>
            <div class="lbl">هدايا مرسلة</div>
        </div>
        <div class="pe-stat">
            <div class="num">{{ $totals['total_messages'] }}</div>
            <div class="lbl">رسائل للمعلم</div>
        </div>
    </div>

    @forelse($rows as $row)
        <div class="pe-card">
            <div class="pe-avatar">{{ mb_substr($row['name'], 0, 1) }}</div>
            <div class="pe-info">
                <h3>
                    {{ $row['name'] }}
                    @if($row['engagement_score'] > 0)
                        <span class="badge-active">نشط</span>
                    @else
                        <span class="badge-quiet">صامت</span>
                    @endif
                </h3>
                <p>
                    {{ $row['email'] ?? '—' }}
                    @if($row['last_engagement'])
                        · آخر تفاعل: {{ $row['last_engagement']->diffForHumans() }}
                    @endif
                </p>
            </div>
            <div class="pe-metrics">
                <div class="pe-metric">
                    <div class="v">💬 {{ $row['praises_count'] }}</div>
                    <div class="l">تشجيع</div>
                </div>
                <div class="pe-metric">
                    <div class="v">🎁 {{ $row['gifts_count'] }}</div>
                    <div class="l">هدايا</div>
                </div>
                <div class="pe-metric">
                    <div class="v">📨 {{ $row['messages_count'] }}</div>
                    <div class="l">رسائل</div>
                </div>
                @if($row['gifts_points'] > 0)
                <div class="pe-metric">
                    <div class="v">⭐ {{ $row['gifts_points'] }}</div>
                    <div class="l">نقاط الهدايا</div>
                </div>
                @endif
            </div>
        </div>
    @empty
        <div class="pe-empty">
            <div style="font-size:64px;margin-bottom:14px;">👋</div>
            <h3 style="margin:0 0 8px;">لا يوجد أولياء أمور بعد</h3>
            <p style="opacity:.7;margin:0;">سيظهرون هنا بمجرد تسجيلهم وربطهم بطلابك.</p>
        </div>
    @endforelse
</div>
@endsection
