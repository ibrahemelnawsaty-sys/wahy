@extends('layouts.admin')

@section('page-title', 'إدارة المعلمين')

@section('content')
<style>
.teachers-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.btn-add {
    padding: 12px 24px;
    background: var(--color-primary);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}

.filters-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.teachers-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
}

.teachers-table table {
    width: 100%;
    border-collapse: collapse;
}

.teachers-table th {
    background: #f8fafc;
    padding: 16px;
    text-align: right;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.teachers-table td {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
}

.teacher-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.teacher-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.status-active { background: #dcfce7; color: #16a34a; padding: 6px 12px; border-radius: 6px; font-size: 12px; }
.status-inactive { background: #f3f4f6; color: #6b7280; padding: 6px 12px; border-radius: 6px; font-size: 12px; }

.btn-action {
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    border: none;
    text-decoration: none;
}

.btn-edit { background: #dbeafe; color: #2563eb; }
.btn-delete { background: #fee2e2; color: #dc2626; }

.alert-success {
    background: #dcfce7;
    color: #16a34a;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 24px;
}
</style>


<div class="teachers-header">
    <div>
        <h2 style="margin: 0 0 8px 0;">👨‍🏫 إدارة المعلمين</h2>
        <p style="margin: 0; color: #64748b;">عرض وإدارة جميع المعلمين</p>
    </div>
    <a href="{{ route('admin.teachers.create') }}" class="btn-add">➕ إضافة معلم</a>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET">
        <div class="filters-grid">
            <input type="text" name="search" placeholder="🔍 بحث..." value="{{ request('search') }}" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
            
            <select name="school_id" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
                <option value="">كل المدارس</option>
                @foreach($schools as $school)
                <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                @endforeach
            </select>

            <select name="status" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
                <option value="">كل الحالات</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
            </select>

            <button type="submit" style="padding: 10px 20px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">تطبيق</button>
        </div>
    </form>
</div>

<!-- Table -->
<div class="teachers-table">
    @if($teachers->count() > 0)
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table>
        <thead>
            <tr>
                <th>المعلم</th>
                <th>المدرسة</th>
                <th>الجوال</th>
                <th>QR Code</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teachers as $teacher)
            <tr>
                <td>
                    <div class="teacher-info">
                        <div class="teacher-avatar">{{ mb_substr($teacher->name, 0, 1, "UTF-8") }}</div>
                        <div>
                            <div style="font-weight: 600;">{{ $teacher->name }}</div>
                            <div style="font-size: 13px; color: #64748b;">{{ $teacher->email }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ $teacher->school->name ?? '-' }}</td>
                <td>{{ $teacher->phone }}</td>
                <td><code style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">{{ $teacher->qr_code }}</code></td>
                <td><span class="status-{{ $teacher->status }}">{{ $teacher->status == 'active' ? 'نشط' : 'غير نشط' }}</span></td>
                <td>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn-action btn-edit"
                           aria-label="تعديل بيانات المعلم {{ $teacher->name }}" title="تعديل">✏️</a>
                        <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" style="display: inline;" onsubmit="return confirm('هل أنت متأكد؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete"
                                    aria-label="حذف المعلم {{ $teacher->name }}" title="حذف">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div style="padding: 20px;">{{ $teachers->links() }}</div>
    @else
    <div style="text-align: center; padding: 60px; color: #64748b;">
        <div style="font-size: 64px; margin-bottom: 16px;">👨‍🏫</div>
        <h3>لا يوجد معلمين</h3>
    </div>
    @endif
</div>

@endsection
