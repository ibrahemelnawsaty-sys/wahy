@extends('layouts.admin')

@section('title', 'محتوى الصفحة الرئيسية')
@section('page-title', 'محتوى الصفحة الرئيسية')

@section('content')
<div class="hc-wrap">

    @if (session('success'))
        <div class="hc-alert hc-alert-success">{{ session('success') }}</div>
    @endif

    <div class="hc-intro">
        <p>عدّل نصوص الصفحة الرئيسية من هنا. الحقول المتروكة فارغة أو المطابقة للنصّ الأصليّ تعود للنصّ الافتراضيّ تلقائيّاً.</p>
        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="hc-preview-link">👁️ معاينة الصفحة الرئيسية</a>
    </div>

    <form action="{{ route('admin.home-content.update') }}" method="POST" novalidate>
        @csrf

        @foreach (config('landing_editable', []) as $sectionKey => $section)
            <div class="hc-card">
                <div class="hc-card-header">
                    <h3>{{ $section['label'] }}</h3>
                </div>
                <div class="hc-card-body">
                    @foreach ($section['fields'] as $fieldKey => $meta)
                        @php
                            $current = $saved[$fieldKey] ?? $meta['default'];
                        @endphp
                        <div class="hc-field">
                            <label for="fld_{{ $fieldKey }}" class="hc-label">{{ $meta['label'] }}</label>
                            @if (($meta['type'] ?? 'text') === 'textarea')
                                <textarea id="fld_{{ $fieldKey }}" name="{{ $fieldKey }}" class="hc-input hc-textarea" rows="3" placeholder="{{ $meta['default'] }}">{{ $current }}</textarea>
                            @else
                                <input type="text" id="fld_{{ $fieldKey }}" name="{{ $fieldKey }}" class="hc-input" value="{{ $current }}" placeholder="{{ $meta['default'] }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="hc-actions">
            <button type="submit" class="hc-save-btn">💾 حفظ التغييرات</button>
        </div>
    </form>

    @if(isset($versions) && count($versions) > 0)
    <div class="hc-card hc-versions">
        <div class="hc-card-header">
            <h3>🕓 النسخ السابقة (تراجع)</h3>
        </div>
        <div class="hc-card-body">
            <p class="hc-versions-note">تُحفَظ لقطة تلقائيّاً قبل كلّ حفظ. يمكنك استرجاع أيّ نسخة سابقة — وتُحفَظ الحالة الحاليّة أوّلاً فالاسترجاع نفسه قابل للتراجع.</p>
            <ul class="hc-versions-list">
                @foreach ($versions as $v)
                    <li class="hc-version-item">
                        <span class="hc-version-time">📸 {{ \Illuminate\Support\Carbon::parse($v->created_at)->format('Y/m/d — H:i') }}</span>
                        <form action="{{ route('admin.home-content.restore', $v->id) }}" method="POST" onsubmit="return confirm('استرجاع هذه النسخة سيستبدل المحتوى الحاليّ. متابعة؟');">
                            @csrf
                            <button type="submit" class="hc-restore-btn">↩️ استرجاع</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</div>

<style>
.hc-wrap { max-width: 900px; margin: 0 auto; }
.hc-intro {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 20px; margin-bottom: 22px;
}
.hc-intro p { margin: 0; color: #475569; font-size: 14px; line-height: 1.7; }
.hc-preview-link {
    background: #0ea5e9; color: #fff; padding: 9px 16px; border-radius: 10px; text-decoration: none;
    font-weight: 700; font-size: 13px; white-space: nowrap; transition: background .2s;
}
.hc-preview-link:hover { background: #0284c7; }
.hc-alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700; }
.hc-alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.hc-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden;
}
.hc-card-header { background: #f1f5f9; padding: 14px 20px; border-bottom: 1px solid #e2e8f0; }
.hc-card-header h3 { margin: 0; font-size: 16px; font-weight: 800; color: #1e293b; }
.hc-card-body { padding: 20px; }
.hc-field { margin-bottom: 18px; }
.hc-field:last-child { margin-bottom: 0; }
.hc-label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 13.5px; color: #334155; }
.hc-input {
    width: 100%; padding: 11px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px;
    font-size: 14px; font-family: inherit; color: #1e293b; background: #fff; transition: border-color .2s, box-shadow .2s;
}
.hc-input:focus { outline: none; border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.15); }
.hc-textarea { resize: vertical; min-height: 78px; line-height: 1.7; }
.hc-actions { position: sticky; bottom: 0; padding: 16px 0; background: transparent; }
.hc-save-btn {
    background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none;
    padding: 14px 40px; border-radius: 12px; font-size: 15px; font-weight: 800; cursor: pointer;
    box-shadow: 0 6px 18px rgba(16,185,129,.35); transition: transform .15s, box-shadow .2s;
}
.hc-save-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(16,185,129,.45); }

[data-theme="dark"] .hc-intro { background: #1e293b; border-color: #334155; }
[data-theme="dark"] .hc-intro p { color: #cbd5e1; }
[data-theme="dark"] .hc-card { background: #1e293b; border-color: #334155; }
[data-theme="dark"] .hc-card-header { background: #0f172a; border-color: #334155; }
[data-theme="dark"] .hc-card-header h3 { color: #f1f5f9; }
[data-theme="dark"] .hc-label { color: #cbd5e1; }
[data-theme="dark"] .hc-input { background: #0f172a; border-color: #334155; color: #f1f5f9; }
[data-theme="dark"] .hc-alert-success { background: #14532d; color: #bbf7d0; border-color: #166534; }

.hc-versions-note { margin: 0 0 14px; color: #64748b; font-size: 13px; line-height: 1.7; }
.hc-versions-list { list-style: none; margin: 0; padding: 0; }
.hc-version-item {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 11px 14px; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 8px; background: #f8fafc;
}
.hc-version-time { font-size: 13.5px; color: #334155; font-weight: 600; }
.hc-version-item form { margin: 0; }
.hc-restore-btn {
    background: #fff; color: #b45309; border: 1.5px solid #fcd34d; padding: 7px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 700; cursor: pointer; transition: background .15s, border-color .15s;
}
.hc-restore-btn:hover { background: #fef3c7; border-color: #f59e0b; }
[data-theme="dark"] .hc-version-item { background: #0f172a; border-color: #334155; }
[data-theme="dark"] .hc-version-time { color: #cbd5e1; }
[data-theme="dark"] .hc-versions-note { color: #94a3b8; }
[data-theme="dark"] .hc-restore-btn { background: #1e293b; color: #fcd34d; border-color: #92400e; }
[data-theme="dark"] .hc-restore-btn:hover { background: #78350f; }
</style>
@endsection
