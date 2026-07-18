@extends('layouts.admin')

@section('page-title', 'إضافة مدرسة')

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

.classrooms-section { grid-column: 1 / -1; margin-top: 8px; border-top: 1px dashed #e2e8f0; padding-top: 20px; }
.classrooms-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; gap: 12px; flex-wrap: wrap; }
.form-help { font-size: 12px; color: #64748b; margin: 0 0 12px; }
.classroom-row { display: grid; grid-template-columns: 2fr 1fr auto; gap: 12px; margin-bottom: 12px; align-items: center; }
.btn-add-classroom { background: #ecfdf5; color: #047857; padding: 8px 16px; font-size: 13px; }
.btn-remove-classroom { background: #fef2f2; color: #dc2626; padding: 12px; }
@media (max-width: 640px) { .classroom-row { grid-template-columns: 1fr; } }
</style>

<div class="form-card">
    <h2 style="margin-bottom: 24px;">🏫 إضافة مدرسة جديدة</h2>

    <form method="POST" action="{{ route('admin.schools.store') }}">
        @csrf

        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label required">اسم المدرسة</label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}" required>
            </div>

            <div class="form-group full-width">
                <label class="form-label">الوصف</label>
                <textarea name="description" class="form-textarea">{{ old('description') }}</textarea>
            </div>

            <div class="form-group full-width">
                <label class="form-label required">العنوان</label>
                <input type="text" name="address" class="form-input" value="{{ old('address') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label required">المدينة</label>
                <input type="text" name="city" class="form-input" value="{{ old('city') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">QR Code</label>
                <input type="text" name="qr_code" class="form-input" value="{{ old('qr_code') }}" placeholder="سيتم توليده تلقائياً">
            </div>

            <div class="form-group">
                <label class="form-label required">البريد الإلكتروني</label>
                <input type="email" name="contact_email" class="form-input" value="{{ old('contact_email') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label required">رقم الهاتف</label>
                <input type="text" name="contact_phone" class="form-input" value="{{ old('contact_phone') }}" required>
            </div>

            <div class="form-group full-width">
                <label class="form-label required">الحالة</label>
                <select name="status" class="form-select" required>
                    <option value="active" selected>نشط</option>
                    <option value="inactive">غير نشط</option>
                </select>
            </div>

            {{-- الفصول (اختياري) — لا تُرسَل ⇒ لا تُنشأ فصول (غير هدّام §3) --}}
            <div class="classrooms-section">
                <div class="classrooms-head">
                    <label class="form-label">الفصول (اختياري)</label>
                    <button type="button" class="btn btn-add-classroom" id="addClassroomBtn">➕ إضافة فصل</button>
                </div>
                <p class="form-help">أضِف فصولاً تُنشأ وترتبط بهذه المدرسة مباشرةً. اترك القسم فارغاً إن لم ترغب.</p>
                <div id="classroomsList">
                    @foreach (old('classrooms', []) as $oldRow)
                        <div class="classroom-row">
                            <input type="text" name="classrooms[{{ $loop->index }}][name]" class="form-input"
                                   value="{{ $oldRow['name'] ?? '' }}" placeholder="اسم الفصل (مثال: الصف الثالث أ)">
                            <input type="text" name="classrooms[{{ $loop->index }}][grade_level]" class="form-input"
                                   value="{{ $oldRow['grade_level'] ?? '' }}" placeholder="المرحلة (اختياري)">
                            <button type="button" class="btn btn-remove-classroom" onclick="this.closest('.classroom-row').remove()">🗑️</button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 حفظ المدرسة</button>
            <a href="{{ route('admin.schools.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

<script>
(function () {
    var list = document.getElementById('classroomsList');
    var addBtn = document.getElementById('addClassroomBtn');
    if (!list || !addBtn) return;
    var counter = list.querySelectorAll('.classroom-row').length; // الصفوف القديمة أُعيد ترقيمها 0..n-1
    addBtn.addEventListener('click', function () {
        var idx = counter++;
        var row = document.createElement('div');
        row.className = 'classroom-row';
        row.innerHTML =
            '<input type="text" name="classrooms[' + idx + '][name]" class="form-input" placeholder="اسم الفصل (مثال: الصف الثالث أ)">' +
            '<input type="text" name="classrooms[' + idx + '][grade_level]" class="form-input" placeholder="المرحلة (اختياري)">' +
            '<button type="button" class="btn btn-remove-classroom">🗑️</button>';
        row.querySelector('.btn-remove-classroom').addEventListener('click', function () { row.remove(); });
        list.appendChild(row);
    });
})();
</script>

@endsection
