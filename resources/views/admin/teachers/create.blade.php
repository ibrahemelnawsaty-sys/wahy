@extends('layouts.admin')

@section('page-title', 'إضافة معلم')

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
    <h2 style="margin-bottom: 24px;">👨‍🏫 إضافة معلم جديد</h2>

    @if($errors->any())
    <div style="background: #fee2e2; border: 2px solid #fca5a5; border-radius: 10px; padding: 16px 20px; margin-bottom: 24px;">
        <div style="font-weight: 700; color: #dc2626; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <span>⚠️</span> يرجى تصحيح الأخطاء التالية:
        </div>
        <ul style="margin: 0; padding-right: 20px; color: #b91c1c; font-size: 14px; line-height: 1.8;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.teachers.store') }}">
        @csrf

        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label required">الاسم الكامل</label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}" required style="@error('name') border-color: #dc2626; @enderror">
                @error('name') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label required">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-input" value="{{ old('email') }}" required style="@error('email') border-color: #dc2626; @enderror">
                @error('email') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label required">رقم الجوال</label>
                <input type="text" name="phone" class="form-input" value="{{ old('phone') }}" required style="@error('phone') border-color: #dc2626; @enderror">
                @error('phone') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label required">المدرسة</label>
                <select name="school_id" class="form-select" required style="@error('school_id') border-color: #dc2626; @enderror">
                    <option value="">اختر المدرسة</option>
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                    @endforeach
                </select>
                @error('school_id') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">QR Code</label>
                <input type="text" name="qr_code" class="form-input" value="{{ old('qr_code') }}" placeholder="مثال: SA-TCH-0001">
            </div>

            <div class="form-group">
                <label class="form-label required">الحالة</label>
                <select name="status" class="form-select" required>
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label required">كلمة المرور</label>
                <input type="password" name="password" class="form-input" required style="@error('password') border-color: #dc2626; @enderror">
                @error('password') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label required">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" class="form-input" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 حفظ المعلم</button>
            <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

@endsection
