@extends('layouts.admin')

@section('page-title', 'تعديل مدرسة')

@section('content')
<style>
.form-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 800px;
    margin: 0 auto;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    font-weight: 600;
    color: #334155;
    font-size: 14px;
}

.form-label.required::after {
    content: " *";
    color: #dc2626;
}

.form-input,
.form-select,
.form-textarea {
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}

.btn-secondary {
    background: #e2e8f0;
    color: #475569;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.form-help { font-size: 12px; color: #64748b; margin: 0 0 12px; }
.levels-section { grid-column: 1 / -1; margin-top: 8px; border-top: 1px dashed #e2e8f0; padding-top: 20px; }
.levels-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; margin-top: 4px; }
.level-chip { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: border-color .15s, background .15s; font-size: 14px; color: #334155; background: #f8fafc; }
.level-chip:hover { border-color: var(--color-primary); background: #fff; }
.level-chip input { width: 18px; height: 18px; cursor: pointer; accent-color: var(--color-primary); flex-shrink: 0; }
.level-chip input:checked ~ span { font-weight: 700; color: var(--color-primary); }
.levels-empty { font-size: 13px; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 12px 16px; border-radius: 8px; }
.levels-empty a { color: var(--color-primary); font-weight: 700; }
</style>

<div class="form-card">
    <h2 style="margin-bottom: 24px;">✏️ تعديل مدرسة: {{ $school->name }}</h2>

    <form method="POST" action="{{ route('admin.schools.update', $school) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label required">اسم المدرسة</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $school->name) }}" required>
            </div>

            <div class="form-group full-width">
                <label class="form-label">الوصف</label>
                <textarea name="description" class="form-textarea">{{ old('description', $school->description) }}</textarea>
            </div>

            <div class="form-group full-width">
                <label class="form-label required">العنوان</label>
                <input type="text" name="address" class="form-input" value="{{ old('address', $school->address) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label required">المدينة</label>
                <input type="text" name="city" class="form-input" value="{{ old('city', $school->city) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">QR Code</label>
                <input type="text" name="qr_code" class="form-input" value="{{ $school->qr_code }}" readonly style="background: #f1f5f9;">
            </div>

            <div class="form-group">
                <label class="form-label required">البريد الإلكتروني</label>
                <input type="email" name="contact_email" class="form-input" value="{{ old('contact_email', $school->contact_email) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label required">رقم الهاتف</label>
                <input type="text" name="contact_phone" class="form-input" value="{{ old('contact_phone', $school->contact_phone) }}" required>
            </div>

            <div class="form-group full-width">
                <label class="form-label required">الحالة</label>
                <select name="status" class="form-select" required>
                    <option value="active" {{ old('status', $school->status) == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status', $school->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>

            {{-- المراحل الدراسية — ربط المدرسة بالمراحل (يحدّد الصفوف المتاحة عند إنشاء الفصول) --}}
            <div class="form-group full-width levels-section">
                <label class="form-label">المراحل الدراسية</label>
                <p class="form-help">حدِّد المراحل التي تخدمها المدرسة. تُستخدم لتحديد الصفوف المتاحة عند إنشاء الفصول.</p>
                @if($educationLevels->isEmpty())
                    <div class="levels-empty">
                        لا توجد مراحل دراسية بعد.
                        <a href="{{ route('admin.education-levels') }}">أنشئ المراحل الدراسية أولاً</a> ثم عُد لربطها بالمدرسة.
                    </div>
                @else
                    @php $checkedLevels = old('education_levels', $linkedIds); @endphp
                    <div class="levels-grid">
                        @foreach($educationLevels as $level)
                            <label class="level-chip">
                                <input type="checkbox" name="education_levels[]" value="{{ $level->id }}"
                                       {{ in_array($level->id, $checkedLevels) ? 'checked' : '' }}>
                                <span>{{ $level->name }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 تحديث المدرسة</button>
            <a href="{{ route('admin.schools.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

@endsection
