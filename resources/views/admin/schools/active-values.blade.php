@extends('layouts.admin')

@section('page-title', 'القيم المفعّلة — ' . $school->name)

@section('content')
<style>
    .av-card {
        background: white;
        border-radius: 14px;
        padding: 28px;
        max-width: 980px;
        margin: 0 auto;
        box-shadow: 0 6px 24px rgba(0,0,0,.06);
    }
    .av-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
    .av-title { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0; }
    .av-hint  { color: #64748b; font-size: 14px; line-height: 1.7; background: #f1f5f9; padding: 14px 16px; border-radius: 10px; border-right: 4px solid #6366f1; margin-bottom: 22px; }
    .av-grid  { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
    .av-tile  {
        position: relative;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        cursor: pointer;
        transition: .2s;
        background: #fafafa;
    }
    .av-tile:hover { border-color: #6366f1; transform: translateY(-2px); }
    .av-tile.checked { border-color: #10b981; background: #ecfdf5; }
    .av-tile input { position: absolute; top: 12px; left: 12px; width: 20px; height: 20px; cursor: pointer; }
    .av-tile h4 { margin: 0 0 6px; padding-right: 30px; font-weight: 700; color: #0f172a; font-size: 16px; }
    .av-tile p  { margin: 0; color: #64748b; font-size: 13px; line-height: 1.6; }
    .av-actions { display: flex; gap: 10px; margin-top: 24px; flex-wrap: wrap; }
    .btn-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; border: none; padding: 12px 28px; border-radius: 10px; font-weight: 700; cursor: pointer; }
    .btn-secondary { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 12px 28px; border-radius: 10px; font-weight: 600; cursor: pointer; text-decoration: none; }
    .btn-link-action { background: transparent; color: #6366f1; border: 1px dashed #c7d2fe; padding: 10px 18px; border-radius: 10px; font-weight: 600; cursor: pointer; }
</style>

<div class="av-card">
    <div class="av-header">
        <h2 class="av-title">🎯 القيم المفعّلة لمدرسة «{{ $school->name }}»</h2>
        <a href="{{ route('admin.schools.show', $school) }}" class="btn-secondary">← العودة للمدرسة</a>
    </div>

    <div class="av-hint">
        اختر القيم التي ترغب في تفعيلها لهذه المدرسة فقط. المعلمون والطلاب وأولياء الأمور لن يروا إلا القيم المفعّلة.
        <br>
        <strong>ملاحظة:</strong> إذا لم يتم اختيار أي قيمة (إعدادات افتراضية)، فسيتم إظهار جميع القيم النشطة على المنصة لهذه المدرسة.
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:18px;">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.schools.active-values.update', $school) }}">
        @csrf
        @method('PUT')

        <div class="av-actions" style="margin-top:0;margin-bottom:18px;">
            <button type="button" class="btn-link-action" onclick="setAll(true)">✓ تفعيل الكل</button>
            <button type="button" class="btn-link-action" onclick="setAll(false)">✗ إلغاء الكل</button>
        </div>

        <div class="av-grid">
            @foreach($allValues as $value)
                @php $checked = in_array($value->id, $activeIds); @endphp
                <label class="av-tile {{ $checked ? 'checked' : '' }}" data-tile>
                    <input type="checkbox"
                           name="value_ids[]"
                           value="{{ $value->id }}"
                           {{ $checked ? 'checked' : '' }}
                           onchange="this.closest('[data-tile]').classList.toggle('checked', this.checked)">
                    <h4>{{ $value->icon ?? '⭐' }} {{ $value->name }}</h4>
                    @if($value->description)
                        <p>{{ \Illuminate\Support\Str::limit($value->description, 90) }}</p>
                    @endif
                </label>
            @endforeach
        </div>

        <div class="av-actions">
            <button type="submit" class="btn-primary">💾 حفظ التغييرات</button>
            <a href="{{ route('admin.schools.index') }}" class="btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

<script>
    function setAll(state) {
        document.querySelectorAll('input[name="value_ids[]"]').forEach(cb => {
            cb.checked = state;
            cb.closest('[data-tile]').classList.toggle('checked', state);
        });
    }
</script>
@endsection
