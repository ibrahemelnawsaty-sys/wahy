@extends('layouts.admin')

@section('page-title', 'إدارة المستخدمين')

@section('content')
<style>
.filters-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-label {
    font-size: 14px;
    font-weight: 600;
    color: #334155;
}

.filter-input,
.filter-select {
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: var(--color-primary);
}

.btn-filter {
    padding: 10px 20px;
    background: var(--color-primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-filter:hover {
    background: var(--color-primary-hover);
    transform: translateY(-1px);
}

.btn-reset {
    padding: 10px 20px;
    background: #64748b;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-reset:hover {
    background: #475569;
}

.users-table {
    width: 100%;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.users-table table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th {
    background: #f8fafc;
    padding: 16px;
    text-align: right;
    font-weight: 600;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
}

.users-table td {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    overflow: hidden;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 2px;
}

.user-email {
    font-size: 13px;
    color: #64748b;
}

.role-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.role-super_admin {
    background: #fee2e2;
    color: #dc2626;
}

.role-school_admin {
    background: #dbeafe;
    color: #2563eb;
}

.role-teacher {
    background: #dcfce7;
    color: #16a34a;
}

.role-student {
    background: #fef3c7;
    color: #d97706;
}

.role-parent {
    background: #e9d5ff;
    color: #9333ea;
}

.role-technical_support {
    background: #cffafe;
    color: #0e7490;
}

.secondary-roles-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 6px;
}

.role-badge.role-secondary {
    font-size: 11px;
    padding: 3px 9px;
    opacity: 0.92;
}

.multi-role-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 700;
    color: #7c3aed;
    background: #f5f3ff;
    border: 1px solid #ddd6fe;
    padding: 2px 8px;
    border-radius: 999px;
    margin-top: 6px;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: #dcfce7;
    color: #16a34a;
}

.status-inactive {
    background: #f3f4f6;
    color: #6b7280;
}

.status-suspended {
    background: #fee2e2;
    color: #dc2626;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-action {
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-edit {
    background: #dbeafe;
    color: #2563eb;
}

.btn-edit:hover {
    background: #bfdbfe;
}

.btn-delete {
    background: #fee2e2;
    color: #dc2626;
}

.btn-delete:hover {
    background: #fecaca;
}

.btn-toggle {
    background: #f3f4f6;
    color: #6b7280;
}

.btn-toggle:hover {
    background: #e5e7eb;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.3;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 24px;
}

.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-weight: 500;
}

.alert-success {
    background: #dcfce7;
    color: #16a34a;
    border: 2px solid #86efac;
}

.alert-error {
    background: #fee2e2;
    color: #dc2626;
    border: 2px solid #fca5a5;
}
</style>

<!-- Header with Add Button -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h2 style="margin: 0 0 8px 0; color: #1e293b;">إدارة المستخدمين</h2>
        <p style="margin: 0; color: #64748b;">عرض وإدارة جميع مستخدمي النظام</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn-filter" style="text-decoration: none;">
        ➕ إضافة مستخدم جديد
    </a>
</div>

<!-- Filters Card -->
<div class="filters-card">
    <form method="GET" action="{{ route('admin.users.index') }}">
        <div class="filters-grid">
            <!-- Search -->
            <div class="filter-group">
                <label class="filter-label">🔍 بحث</label>
                <input 
                    type="text" 
                    name="search" 
                    class="filter-input" 
                    placeholder="الاسم، البريد، QR Code..."
                    value="{{ request('search') }}">
            </div>

            <!-- Role Filter -->
            <div class="filter-group">
                <label class="filter-label">👤 الدور</label>
                <select name="role" class="filter-select">
                    <option value="">الكل</option>
                    <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>سوبر أدمن</option>
                    <option value="school_admin" {{ request('role') == 'school_admin' ? 'selected' : '' }}>مدير مدرسة</option>
                    <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>معلم</option>
                    <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>طالب</option>
                    <option value="parent" {{ request('role') == 'parent' ? 'selected' : '' }}>ولي أمر</option>
                    <option value="technical_support" {{ request('role') == 'technical_support' ? 'selected' : '' }}>الدعم الفنيّ</option>
                </select>
            </div>

            <!-- School Filter -->
            <div class="filter-group">
                <label class="filter-label">🏫 المدرسة</label>
                <select name="school_id" class="filter-select">
                    <option value="">الكل</option>
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="filter-group">
                <label class="filter-label">⚡ الحالة</label>
                <select name="status" class="filter-select">
                    <option value="">الكل</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>موقوف</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button type="submit" class="btn-filter">تطبيق الفلترة</button>
            <a href="{{ route('admin.users.index') }}" class="btn-reset" style="text-decoration: none; display: inline-block;">إعادة تعيين</a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="users-table">
    @if($users->count() > 0)
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table>
        <thead>
            <tr>
                <th>المستخدم</th>
                <th>الدور</th>
                <th>المدرسة</th>
                <th>QR Code</th>
                <th>الحالة</th>
                <th>تاريخ التسجيل</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @php
                $roleLabels = [
                    'super_admin' => 'سوبر أدمن',
                    'school_admin' => 'مدير مدرسة',
                    'teacher' => 'معلم',
                    'student' => 'طالب',
                    'parent' => 'ولي أمر',
                    'technical_support' => 'الدعم الفنيّ',
                ];
            @endphp
            @foreach($users as $user)
            <tr>
                <!-- User Info -->
                <td>
                    <div class="user-info">
                        <div class="user-avatar">
                            @if($user->avatar)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            @else
                                {{ mb_substr($user->name, 0, 1) }}
                            @endif
                        </div>
                        <div class="user-details">
                            <span class="user-name">{{ $user->name }}</span>
                            <span class="user-email">{{ $user->email }}</span>
                        </div>
                    </div>
                </td>

                <!-- Role -->
                <td>
                    <span class="role-badge role-{{ $user->role }}">{{ $roleLabels[$user->role] ?? $user->role }}</span>
                    @php $userSecondary = is_array($user->secondary_roles) ? $user->secondary_roles : []; @endphp
                    @if(count($userSecondary) > 0)
                        <div class="secondary-roles-wrap">
                            @foreach($userSecondary as $sr)
                                <span class="role-badge role-{{ $sr }} role-secondary" title="دور ثانويّ">{{ $roleLabels[$sr] ?? $sr }}</span>
                            @endforeach
                        </div>
                        <span class="multi-role-tag" title="يملك أكثر من دور ويبدّل بينها">👥 متعدّد الأدوار</span>
                    @endif
                </td>

                <!-- School -->
                <td>
                    @if($user->school)
                        <span style="color: #64748b;">{{ $user->school->name }}</span>
                    @else
                        <span style="color: #cbd5e1;">-</span>
                    @endif
                </td>

                <!-- QR Code -->
                <td>
                    <code style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 13px;">
                        {{ $user->qr_code }}
                    </code>
                </td>

                <!-- Status -->
                <td>
                    <span class="status-badge status-{{ $user->status }}">
                        @switch($user->status)
                            @case('active') نشط @break
                            @case('inactive') غير نشط @break
                            @case('suspended') موقوف @break
                            @default {{ $user->status }}
                        @endswitch
                    </span>
                </td>

                <!-- Created At -->
                <td>
                    <span style="color: #64748b; font-size: 13px;">
                        {{ $user->created_at->format('Y-m-d') }}
                    </span>
                </td>

                <!-- Actions -->
                <td>
                    <div class="action-buttons">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-action btn-edit">
                            ✏️ تعديل
                        </a>

                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-action btn-toggle">
                                {{ $user->status === 'active' ? '🔴 تعطيل' : '✅ تفعيل' }}
                            </button>
                        </form>

                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete">
                                🗑️ حذف
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        {{ $users->links() }}
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-users"></i>
        <h3>لا يوجد مستخدمين</h3>
        <p>لم يتم العثور على أي مستخدمين. جرب تغيير الفلاتر أو أضف مستخدم جديد.</p>
    </div>
    @endif
</div>

@endsection
