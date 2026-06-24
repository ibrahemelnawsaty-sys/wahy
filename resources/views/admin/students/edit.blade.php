@extends('layouts.admin')

@section('page-title', 'تعديل طالب')

@section('content')
<style>
.form-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 700px;
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
.form-select {
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
}

.btn-primary { background: var(--color-primary); color: white; }
.btn-secondary { background: #e2e8f0; color: #475569; }

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}
</style>

<div class="form-card">
    <h2 style="margin-bottom: 24px;">✏️ تعديل طالب: {{ $student->name }}</h2>

    <form method="POST" action="{{ route('admin.students.update', $student) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label required">الاسم الكامل</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $student->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label required">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $student->email) }}" required>
            </div>

            <div class="form-group full-width">
                <label class="form-label required">المدرسة</label>
                <select name="school_id" class="form-select" required>
                    <option value="">اختر المدرسة</option>
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ old('school_id', $student->school_id) == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">QR Code</label>
                <input type="text" class="form-input" value="{{ $student->qr_code }}" readonly style="background: #f1f5f9;">
            </div>

            <div class="form-group">
                <label class="form-label required">الحالة</label>
                <select name="status" class="form-select" required>
                    <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">كلمة المرور الجديدة</label>
                <input type="password" name="password" class="form-input" placeholder="اتركها فارغة للإبقاء">
            </div>

            <div class="form-group">
                <label class="form-label">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" class="form-input">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 تحديث الطالب</button>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

@endsection
