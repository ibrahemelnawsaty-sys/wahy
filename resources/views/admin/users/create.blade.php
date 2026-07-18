@extends('layouts.admin')

@section('page-title', 'إضافة مستخدم جديد')

@section('content')
<style>
.form-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 800px;
    margin: 0 auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
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
    transition: all 0.2s;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: var(--color-primary);
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 2px solid #e2e8f0;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--color-primary-hover);
}

.btn-secondary {
    background: #e2e8f0;
    color: #475569;
}

.btn-secondary:hover {
    background: #cbd5e1;
}

.error-message {
    color: #dc2626;
    font-size: 13px;
    margin-top: 4px;
}
</style>

<div class="form-card">
    <div style="margin-bottom: 32px;">
        <h2 style="margin: 0 0 8px 0; color: #1e293b;">➕ إضافة مستخدم جديد</h2>
        <p style="margin: 0; color: #64748b;">أدخل بيانات المستخدم الجديد</p>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div class="form-grid">
            <!-- Name -->
            <div class="form-group full-width">
                <label class="form-label required">الاسم الكامل</label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}" required>
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Email -->
            <div class="form-group">
                <label class="form-label required">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label class="form-label">رقم الجوال</label>
                <input type="text" name="phone" class="form-input" value="{{ old('phone') }}">
                @error('phone')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Role -->
            <div class="form-group">
                <label class="form-label required">الدور</label>
                <select name="role" class="form-select" required>
                    <option value="">اختر الدور</option>
                    <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>سوبر أدمن</option>
                    <option value="school_admin" {{ old('role') == 'school_admin' ? 'selected' : '' }}>مدير مدرسة</option>
                    <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>معلم</option>
                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>طالب</option>
                    <option value="parent" {{ old('role') == 'parent' ? 'selected' : '' }}>ولي أمر</option>
                    <option value="technical_support" {{ old('role') == 'technical_support' ? 'selected' : '' }}>الدعم الفنيّ</option>
                </select>
                @error('role')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- School -->
            <div class="form-group">
                <label class="form-label">المدرسة</label>
                <select name="school_id" class="form-select">
                    <option value="">بدون مدرسة</option>
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                    @endforeach
                </select>
                @error('school_id')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- QR Code -->
            <div class="form-group">
                <label class="form-label">QR Code</label>
                <input type="text" name="qr_code" class="form-input" value="{{ old('qr_code') }}" placeholder="سيتم توليده تلقائياً">
                @error('qr_code')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label required">الحالة</label>
                <select name="status" class="form-select" required>
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>موقوف</option>
                </select>
                @error('status')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label required">كلمة المرور</label>
                <input type="password" name="password" class="form-input" required>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="form-group">
                <label class="form-label required">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" class="form-input" required>
            </div>

            <!-- Two Factor -->
            <div class="form-group full-width">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="two_factor_enabled" value="1" {{ old('two_factor_enabled') ? 'checked' : '' }}>
                    <span class="form-label" style="margin: 0;">تفعيل المصادقة الثنائية</span>
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 حفظ المستخدم</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

@endsection
