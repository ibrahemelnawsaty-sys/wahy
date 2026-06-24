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
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 حفظ المدرسة</button>
            <a href="{{ route('admin.schools.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

@endsection
