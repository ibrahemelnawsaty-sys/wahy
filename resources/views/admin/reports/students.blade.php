@extends('layouts.admin')

@section('title', 'تقارير الطلاب')

@section('content')
<div class="students-reports">
    <div class="page-header">
        <div class="header-content">
            <h1>👨‍🎓 تقارير الطلاب</h1>
            <p>تقارير تفصيلية عن أداء وتقدم الطلاب</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label>المدرسة</label>
                <select name="school_id">
                    <option value="">جميع المدارس</option>
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>الحالة</label>
                <select name="status">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>

            <div class="filter-group">
                <label>من تاريخ</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}">
            </div>

            <div class="filter-group">
                <label>إلى تاريخ</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}">
            </div>

            <button type="submit" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 12px 28px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(99,102,241,0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(99,102,241,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(99,102,241,0.3)'">🔍 تطبيق الفلاتر</button>
            <a href="{{ route('admin.reports.students') }}" style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #64748b 0%, #475569 100%); color: white; padding: 12px 24px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(100,116,139,0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(100,116,139,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(100,116,139,0.3)'">🔄 إعادة تعيين</a>
        </form>
    </div>

    <!-- Students Table -->
    <div class="table-card">
        <div class="table-header">
            <h3>📋 قائمة الطلاب ({{ $students->total() }})</h3>
            <form method="POST" action="{{ route('admin.reports.export') }}">
                @csrf
                <input type="hidden" name="type" value="students">
                <button type="submit" name="format" value="excel" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 10px 22px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(16,185,129,0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(16,185,129,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(16,185,129,0.3)'">📥 تصدير Excel</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الطالب</th>
                        <th>المدرسة</th>
                        <th>إجمالي النقاط</th>
                        <th>المشاركات</th>
                        <th>الشارات</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar">
                                    @if($student->avatar)
                                        <img src="{{ $student->avatar_url }}" alt="{{ $student->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                    @else
                                        {{ mb_substr($student->name, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="user-name">{{ $student->name }}</div>
                                    <div class="user-email">{{ $student->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $student->school->name ?? '-' }}</td>
                        <td>
                            <span class="badge badge-primary">
                                {{ number_format($student->total_points ?? 0) }} نقطة
                            </span>
                        </td>
                        <td>{{ $student->activity_submissions_count }}</td>
                        <td>{{ $student->badges->count() }}</td>
                        <td>
                            <span class="badge badge-{{ $student->status == 'active' ? 'success' : 'secondary' }}">
                                {{ $student->status == 'active' ? 'نشط' : 'غير نشط' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.reports.students.detail', $student->id) }}" style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(59,130,246,0.3);" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(59,130,246,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(59,130,246,0.3)'">📊 عرض التفاصيل</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">لا توجد بيانات</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $students->links() }}
        </div>
    </div>
</div>

<style>
.students-reports {
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.header-content h1 {
    font-size: 28px;
    margin-bottom: 8px;
}

.header-content p {
    color: #64748b;
    font-size: 14px;
}

.filters-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 2px solid #f1f5f9;
}

.filters-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.filter-group select,
.filter-group input {
    padding: 10px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.table-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 2px solid #f1f5f9;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f5f9;
}

.table-header h3 {
    font-size: 18px;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f8fafc;
    padding: 12px;
    text-align: right;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--color-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    overflow: hidden;
}

.user-name {
    font-weight: 600;
    margin-bottom: 2px;
}

.user-email {
    font-size: 12px;
    color: #64748b;
}

.badge {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.badge-primary {
    background: #dbeafe;
    color: #1e40af;
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
}

.badge-secondary {
    background: #e2e8f0;
    color: #475569;
}

.pagination-wrapper {
    margin-top: 20px;
}
</style>
@endsection
