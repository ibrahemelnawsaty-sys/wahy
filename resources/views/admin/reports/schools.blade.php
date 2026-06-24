@extends('layouts.admin')

@section('title', 'تقارير المدارس')

@section('content')
<div class="schools-reports">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1>🏫 تقارير المدارس</h1>
        <form method="POST" action="{{ route('admin.reports.export') }}">
            @csrf
            <input type="hidden" name="type" value="schools">
            <button type="submit" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 10px 22px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(16,185,129,0.3);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">📥 تصدير Excel</button>
        </form>
    </div>

    <div class="filters-card">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label>المدينة</label>
                <select name="city">
                    <option value="">جميع المدن</option>
                    @foreach($cities as $city)
                    <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
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
            <button type="submit" class="btn btn-primary">تطبيق</button>
        </form>
    </div>

    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>المدرسة</th>
                    <th>المدينة</th>
                    <th>الطلاب</th>
                    <th>المعلمين</th>
                    <th>الطلاب النشطين</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schools as $school)
                <tr>
                    <td><strong>{{ $school->name }}</strong></td>
                    <td>{{ $school->city }}</td>
                    <td><span class="badge badge-primary">{{ $school->students_count }}</span></td>
                    <td><span class="badge badge-info">{{ $school->teachers_count }}</span></td>
                    <td><span class="badge badge-success">{{ $school->active_students_count }}</span></td>
                    <td><a href="{{ route('admin.reports.schools.detail', $school->id) }}" class="btn btn-sm btn-info">عرض</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center">لا توجد مدارس</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination-wrapper">{{ $schools->links() }}</div>
    </div>
</div>

<style>
.schools-reports { padding: 20px; }
.page-header { margin-bottom: 30px; }
.page-header h1 { font-size: 28px; }
.filters-card { background: white; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.filters-form { display: flex; gap: 15px; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.filter-group label { font-size: 13px; font-weight: 600; }
.filter-group select { padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; }
.table-card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: #f8fafc; padding: 12px; text-align: right; font-size: 13px; border-bottom: 2px solid #e2e8f0; }
.data-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; }
.badge { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; }
.badge-primary { background: #dbeafe; color: #1e40af; }
.badge-info { background: #e0e7ff; color: #3730a3; }
.badge-success { background: #d1fae5; color: #065f46; }
</style>
@endsection
