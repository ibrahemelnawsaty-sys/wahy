@extends('layouts.admin')

@section('page-title', 'تعديل مستخدم')

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

.form-help { font-size: 12px; color: #64748b; margin: 0 0 4px; }
.roles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-top: 4px; }
.role-chip { display: flex; align-items: center; gap: 10px; padding: 11px 14px; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: border-color .15s, background .15s; font-size: 14px; color: #334155; background: #f8fafc; user-select: none; }
.role-chip:hover { border-color: var(--color-primary); background: #fff; }
.role-chip input { width: 18px; height: 18px; cursor: pointer; accent-color: var(--color-primary); flex-shrink: 0; }
.role-chip input:checked ~ span { font-weight: 700; color: var(--color-primary); }
.role-chip.disabled { opacity: .5; cursor: not-allowed; background: #f1f5f9; }
.role-chip.disabled:hover { border-color: #e2e8f0; }
.role-warning { margin-top: 10px; padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; }
.pw-fields { grid-column: 1 / -1; display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
@media (max-width: 640px) { .pw-fields { grid-template-columns: 1fr; } }
</style>

<div class="form-card">
    <div style="margin-bottom: 32px;">
        <h2 style="margin: 0 0 8px 0; color: #1e293b;">✏️ تعديل مستخدم</h2>
        <p style="margin: 0; color: #64748b;">تحديث بيانات: <strong>{{ $user->name }}</strong></p>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <!-- Name -->
            <div class="form-group full-width">
                <label class="form-label required">الاسم الكامل</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Email -->
            <div class="form-group">
                <label class="form-label required">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label class="form-label">رقم الجوال</label>
                <input type="text" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}">
                @error('phone')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Role -->
            <div class="form-group">
                <label class="form-label required">الدور</label>
                <select name="role" class="form-select" required>
                    <option value="">اختر الدور</option>
                    <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>سوبر أدمن</option>
                    <option value="school_admin" {{ old('role', $user->role) == 'school_admin' ? 'selected' : '' }}>مدير مدرسة</option>
                    <option value="teacher" {{ old('role', $user->role) == 'teacher' ? 'selected' : '' }}>معلم</option>
                    <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>طالب</option>
                    <option value="parent" {{ old('role', $user->role) == 'parent' ? 'selected' : '' }}>ولي أمر</option>
                    <option value="technical_support" {{ old('role', $user->role) == 'technical_support' ? 'selected' : '' }}>الدعم الفنيّ</option>
                </select>
                @error('role')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- School -->
            <div class="form-group" id="schoolIdGroup">
                <label class="form-label">المدرسة</label>
                <select name="school_id" class="form-select">
                    <option value="">بدون مدرسة</option>
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ old('school_id', $user->school_id) == $school->id ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                    @endforeach
                </select>
                @error('school_id')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Managed Schools (school_admin) -->
            @php $selectedSchoolIds = old('school_ids', $user->managedSchoolIds()); @endphp
            <div class="form-group full-width" id="schoolIdsGroup" style="display: none;">
                <label class="form-label">المدارس المُدارة (لمدير المدرسة)</label>
                <select name="school_ids[]" class="form-select" multiple size="5" style="height: auto;">
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ in_array($school->id, (array) $selectedSchoolIds) ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                    @endforeach
                </select>
                <span class="error-message" style="color: #64748b;">اضغط Ctrl لاختيار عدّة مدارس. المدرسة الأولى المختارة هي الأساسيّة، ويبدّل المدير بينها.</span>
                @error('school_ids')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Secondary Roles -->
            @php
                $allRolesList = [
                    'super_admin' => 'سوبر أدمن',
                    'school_admin' => 'مدير مدرسة',
                    'teacher' => 'معلم',
                    'student' => 'طالب',
                    'parent' => 'ولي أمر',
                    'technical_support' => 'الدعم الفنيّ',
                ];
                $selectedSecondary = old('secondary_roles', $user->secondary_roles ?? []);
            @endphp
            <div class="form-group full-width">
                <label class="form-label">الأدوار الثانوية (اختياري)</label>
                <p class="form-help">يمكن للمستخدم امتلاك أكثر من دور والتبديل بينها. الدور الأساسيّ يُستبعد تلقائياً — وأزِل التحديد لإلغاء أيّ دور ثانويّ.</p>
                <div class="roles-grid" id="secondaryRolesGrid">
                    @foreach($allRolesList as $rKey => $rLabel)
                    <label class="role-chip" data-role="{{ $rKey }}">
                        <input type="checkbox" name="secondary_roles[]" value="{{ $rKey }}"
                               {{ in_array($rKey, (array) $selectedSecondary) ? 'checked' : '' }}>
                        <span>{{ $rLabel }}</span>
                    </label>
                    @endforeach
                </div>
                <div id="roleSchoolWarning" class="role-warning" style="display: none;"></div>
                @error('secondary_roles')
                    <span class="error-message">{{ $message }}</span>
                @enderror
                @error('secondary_roles.*')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- QR Code -->
            <div class="form-group">
                <label class="form-label">QR Code</label>
                <input type="text" name="qr_code" class="form-input" value="{{ old('qr_code', $user->qr_code) }}" readonly style="background: #f1f5f9;">
                @error('qr_code')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label required">الحالة</label>
                <select name="status" class="form-select" required>
                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>موقوف</option>
                </select>
                @error('status')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password — مخفيّة خلف زرّ؛ تُفتح فقط عند الرغبة بتغييرها -->
            <div class="form-group full-width" id="pwToggleGroup" style="display: {{ $errors->has('password') ? 'none' : 'flex' }};">
                <label class="form-label">كلمة المرور</label>
                <button type="button" class="btn btn-secondary" id="togglePwBtn" style="align-self: flex-start;">🔑 تغيير كلمة المرور</button>
                <span class="error-message" style="color: #64748b;">تبقى كلمة المرور الحالية دون تغيير ما لم تفتح الحقل وتُدخل واحدة جديدة.</span>
            </div>
            <div class="pw-fields" id="passwordFields" style="display: {{ $errors->has('password') ? 'grid' : 'none' }};">
                <div class="form-group">
                    <label class="form-label">كلمة المرور الجديدة</label>
                    <input type="password" name="password" class="form-input" placeholder="8 أحرف على الأقل">
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" class="form-input">
                </div>
            </div>

            <!-- Two Factor -->
            <div class="form-group full-width">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="two_factor_enabled" value="1" {{ old('two_factor_enabled', $user->two_factor_enabled) ? 'checked' : '' }}>
                    <span class="form-label" style="margin: 0;">تفعيل المصادقة الثنائية</span>
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 تحديث المستخدم</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

<script>
(function () {
    var roleSelect = document.querySelector('select[name="role"]');
    var schoolIdsGroup = document.getElementById('schoolIdsGroup');
    var schoolIdGroup = document.getElementById('schoolIdGroup');
    var schoolIdSelect = document.querySelector('select[name="school_id"]');
    var schoolIdsSelect = document.querySelector('select[name="school_ids[]"]');
    var grid = document.getElementById('secondaryRolesGrid');
    var warn = document.getElementById('roleSchoolWarning');
    var roleLabels = @json($allRolesList);
    // الأدوار المرتبطة بمدرسة (تُعطَّل عملياً بلا مدرسة) — مطابق UserRole::isScopedToSchool
    var schoolScoped = { school_admin: 1, teacher: 1, student: 1, parent: 1 };

    function sync() {
        var role = roleSelect ? roleSelect.value : '';
        var isSchoolAdmin = (role === 'school_admin');
        if (schoolIdsGroup) { schoolIdsGroup.style.display = isSchoolAdmin ? 'flex' : 'none'; }
        if (schoolIdGroup) { schoolIdGroup.style.display = isSchoolAdmin ? 'none' : ''; }

        var flagged = [];
        if (grid) {
            Array.prototype.forEach.call(grid.querySelectorAll('.role-chip'), function (chip) {
                var cb = chip.querySelector('input');
                var r = chip.getAttribute('data-role');
                if (r === role) {
                    cb.checked = false;
                    cb.disabled = true;
                    chip.classList.add('disabled');
                } else {
                    cb.disabled = false;
                    chip.classList.remove('disabled');
                }
                if (cb.checked && schoolScoped[r]) { flagged.push(r); }
            });
        }

        var hasSchool = isSchoolAdmin
            ? (schoolIdsSelect && schoolIdsSelect.selectedOptions.length > 0)
            : (schoolIdSelect && schoolIdSelect.value !== '');
        if (warn) {
            if (flagged.length && !hasSchool) {
                var names = flagged.map(function (r) { return roleLabels[r] || r; });
                warn.innerHTML = '⚠️ الأدوار الثانوية (' + names.join('، ') + ') تتطلّب إسناد مدرسة — لن تُفعَّل لهذا المستخدم حتى تُسنِد له مدرسة.';
                warn.style.display = 'block';
            } else {
                warn.style.display = 'none';
            }
        }
    }

    if (roleSelect) { roleSelect.addEventListener('change', sync); }
    if (schoolIdSelect) { schoolIdSelect.addEventListener('change', sync); }
    if (schoolIdsSelect) { schoolIdsSelect.addEventListener('change', sync); }
    if (grid) { grid.addEventListener('change', sync); }
    sync();

    // زرّ إظهار حقول تغيير كلمة المرور
    var togglePwBtn = document.getElementById('togglePwBtn');
    var passwordFields = document.getElementById('passwordFields');
    var pwToggleGroup = document.getElementById('pwToggleGroup');
    if (togglePwBtn && passwordFields) {
        togglePwBtn.addEventListener('click', function () {
            passwordFields.style.display = 'grid';
            if (pwToggleGroup) { pwToggleGroup.style.display = 'none'; }
            var first = passwordFields.querySelector('input');
            if (first) { first.focus(); }
        });
    }
})();
</script>

@endsection
