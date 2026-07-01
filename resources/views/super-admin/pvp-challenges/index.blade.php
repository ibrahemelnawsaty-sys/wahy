@extends('layouts.admin')

@section('title', 'تحديات PvP')
@section('page-title', 'إدارة تحديات PvP')

@push('styles')
<style>
/* Wahy dark-mode coverage — pvp-challenges/index (ألوان inline مُصلَّبة) */
html[data-theme="dark"] #sa-pvp-index h2[style*="#1e293b"] { color: #F1F5F9 !important; }
html[data-theme="dark"] #sa-pvp-index div[style*="background: #fff"],
html[data-theme="dark"] #sa-pvp-index div[style*="background:#fff"] {
    background: rgba(30, 41, 59, 0.85) !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35) !important;
}
html[data-theme="dark"] #sa-pvp-index thead[style*="#f8fafc"] { background: rgba(15, 23, 42, 0.6) !important; }
html[data-theme="dark"] #sa-pvp-index th[style*="#475569"] { color: #CBD5E1 !important; }
html[data-theme="dark"] #sa-pvp-index tr[style*="border-top: 1px solid #f1f5f9"] { border-top-color: rgba(255, 255, 255, 0.06) !important; }
html[data-theme="dark"] #sa-pvp-index td { color: #E2E8F0; }
html[data-theme="dark"] #sa-pvp-index td[style*="#64748b"] { color: #94A3B8 !important; }
html[data-theme="dark"] #sa-pvp-index button[style*="background: #fff"] {
    background: rgba(15, 23, 42, 0.6) !important;
    border-color: rgba(255, 255, 255, 0.18) !important;
    color: #E2E8F0 !important;
}
</style>
@endpush

@section('content')
<div id="sa-pvp-index" style="padding: 24px; max-width: 1200px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #1e293b;">
            <i class="fas fa-fist-raised" aria-hidden="true"></i> تحديات PvP
        </h2>
        <a href="{{ route('admin.pvp-challenges.create') }}" class="btn btn-primary"
           style="background: linear-gradient(135deg, #8b5cf6, #ec4899); color: #fff; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700;">
            <i class="fas fa-plus" aria-hidden="true"></i> تحدي جديد
        </a>
    </div>

    @if(session('success'))
        <div role="status" style="background: #d1fae5; color: #065f46; padding: 14px 18px; border-radius: 10px; margin-bottom: 18px; font-weight: 600;">
            {{ session('success') }}
        </div>
    @endif

    <div style="background: #fff; border-radius: 14px; box-shadow: 0 4px 18px rgba(0,0,0,0.06); overflow: hidden;">
        @if($challenges->count() > 0)
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8fafc;">
                <tr>
                    <th style="padding: 14px; text-align: right; font-size: 13px; color: #475569;">العنوان</th>
                    <th style="padding: 14px; text-align: right; font-size: 13px; color: #475569;">القيمة</th>
                    <th style="padding: 14px; text-align: right; font-size: 13px; color: #475569;">الصعوبة</th>
                    <th style="padding: 14px; text-align: right; font-size: 13px; color: #475569;">الأسئلة</th>
                    <th style="padding: 14px; text-align: right; font-size: 13px; color: #475569;">الوقت</th>
                    <th style="padding: 14px; text-align: right; font-size: 13px; color: #475569;">المباريات</th>
                    <th style="padding: 14px; text-align: right; font-size: 13px; color: #475569;">الحالة</th>
                    <th style="padding: 14px; text-align: right; font-size: 13px; color: #475569;">منشئ</th>
                    <th style="padding: 14px; text-align: right; font-size: 13px; color: #475569;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($challenges as $challenge)
                <tr style="border-top: 1px solid #f1f5f9;">
                    <td style="padding: 14px; font-weight: 600;">{{ $challenge->title }}</td>
                    <td style="padding: 14px;">
                        @if($challenge->value)
                            <span style="background: #ede9fe; color: #5b21b6; padding: 4px 10px; border-radius: 10px; font-size: 12px; font-weight: 600;">{{ $challenge->value->name }}</span>
                        @else
                            <span style="color: #94a3b8; font-size: 12px;">عام</span>
                        @endif
                    </td>
                    <td style="padding: 14px;">
                        @php
                            $difficultyMap = ['easy' => ['سهل', '#d1fae5', '#065f46'], 'medium' => ['متوسط', '#fef3c7', '#92400e'], 'hard' => ['صعب', '#fee2e2', '#991b1b']];
                            $diff = $difficultyMap[$challenge->difficulty ?? 'medium'] ?? $difficultyMap['medium'];
                        @endphp
                        <span style="background: {{ $diff[1] }}; color: {{ $diff[2] }}; padding: 4px 10px; border-radius: 10px; font-size: 12px; font-weight: 600;">{{ $diff[0] }}</span>
                    </td>
                    <td style="padding: 14px;">{{ $challenge->question_count }}</td>
                    <td style="padding: 14px;">{{ $challenge->time_limit }}s</td>
                    <td style="padding: 14px;">{{ $challenge->matches_count }}</td>
                    <td style="padding: 14px;">
                        @if($challenge->is_active)
                            <span style="background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">نشط</span>
                        @else
                            <span style="background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">معطل</span>
                        @endif
                    </td>
                    <td style="padding: 14px; font-size: 13px; color: #64748b;">{{ $challenge->creator->name ?? '—' }}</td>
                    <td style="padding: 14px;">
                        <div style="display: flex; gap: 8px;">
                            <form method="POST" action="{{ route('admin.pvp-challenges.toggle', $challenge->id) }}" style="display: inline; margin: 0;">
                                @csrf
                                <button type="submit" aria-label="تبديل حالة التحدي {{ $challenge->title }}"
                                        style="background: #fff; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 8px; cursor: pointer; min-height: 36px;">
                                    {{ $challenge->is_active ? '⏸️ تعطيل' : '▶️ تفعيل' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.pvp-challenges.destroy', $challenge->id) }}"
                                  style="display: inline; margin: 0;"
                                  onsubmit="return confirm('هل أنت متأكد من حذف التحدي «{{ $challenge->title }}»؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="حذف التحدي {{ $challenge->title }}"
                                        style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 6px 12px; border-radius: 8px; cursor: pointer; min-height: 36px;">
                                    🗑️ حذف
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div style="padding: 16px;">{{ $challenges->links() }}</div>
        @else
        <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
            <div style="font-size: 48px; margin-bottom: 16px;">⚔️</div>
            <p style="font-size: 16px; font-weight: 600;">لا توجد تحديات PvP بعد</p>
            <a href="{{ route('admin.pvp-challenges.create') }}"
               style="display: inline-block; margin-top: 16px; background: #8b5cf6; color: #fff; padding: 10px 24px; border-radius: 10px; text-decoration: none; font-weight: 700;">إنشاء أول تحدي</a>
        </div>
        @endif
    </div>
</div>
@endsection
