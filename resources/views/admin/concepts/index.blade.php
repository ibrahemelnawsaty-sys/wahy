@extends('layouts.admin')

@section('page-title', 'إدارة المفاهيم')

@section('content')
<style>
.concepts-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.concepts-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
}

.concepts-table table {
    width: 100%;
    border-collapse: collapse;
}

.concepts-table th {
    background: #f8fafc;
    padding: 16px;
    text-align: right;
    font-weight: 600;
    border-bottom: 2px solid #e2e8f0;
}

.concepts-table td {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
}

.concept-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.concept-desc {
    font-size: 13px;
    color: #64748b;
}

.value-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #f1f5f9;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}

.btn-add {
    padding: 12px 24px;
    background: var(--color-primary);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-action {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    border: none;
    text-decoration: none;
}

.btn-view { background: #e0e7ff; color: #4f46e5; }
.btn-edit { background: #dbeafe; color: #2563eb; }
.btn-delete { background: #fee2e2; color: #dc2626; }

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

.alert-success {
    background: #dcfce7;
    color: #16a34a;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 24px;
}

.alert-error {
    background: #fee2e2;
    color: #dc2626;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 24px;
}
</style>

@if(session('success'))
<div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert-error">{{ session('error') }}</div>
@endif

<div class="concepts-header">
    <div>
        <h2 style="margin: 0 0 8px 0;">💡 إدارة المفاهيم</h2>
        <p style="margin: 0; color: #64748b;">عرض وإدارة جميع المفاهيم التعليمية</p>
    </div>
    <a href="{{ route('admin.concepts.create', ['value_id' => request('value_id')]) }}" class="btn-add">
        ➕ إضافة مفهوم جديد
    </a>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET">
        <div class="filters-grid">
            <input type="text" name="search" placeholder="🔍 بحث..." value="{{ request('search') }}" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
            
            <select name="value_id" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
                <option value="">كل القيم</option>
                @foreach($values as $value)
                <option value="{{ $value->id }}" {{ request('value_id') == $value->id ? 'selected' : '' }}>
                    {{ $value->icon }} {{ $value->name }}
                </option>
                @endforeach
            </select>

            <button type="submit" style="padding: 10px 20px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">تطبيق</button>
        </div>
    </form>
</div>

<!-- Concepts Table -->
@if($concepts->count() > 0)
<div class="concepts-table">
    <table>
        <thead>
            <tr>
                <th>المفهوم</th>
                <th>القيمة</th>
                <th>الدروس</th>
                <th>الترتيب</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($concepts as $concept)
            <tr>
                <td>
                    <div class="concept-name">{{ $concept->name }}</div>
                    <div class="concept-desc">{{ \Illuminate\Support\Str::limit($concept->description, 80) ?? 'لا يوجد وصف' }}</div>
                </td>
                <td>
                    <span class="value-badge">
                        {{ $concept->value->icon ?? '💎' }} {{ $concept->value->name }}
                    </span>
                </td>
                <td>
                    <strong style="color: var(--color-primary);">{{ $concept->lessons->count() }}</strong>
                </td>
                <td>
                    <code style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">#{{ $concept->order }}</code>
                </td>
                <td>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('admin.concepts.show', $concept) }}" class="btn-action btn-view">👁️</a>
                        <a href="{{ route('admin.concepts.edit', $concept) }}" class="btn-action btn-edit">✏️</a>
                        <form method="POST" action="{{ route('admin.concepts.destroy', $concept) }}" style="display: inline;" onsubmit="return confirm('هل أنت متأكد؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="padding: 20px; background: white; border-radius: 12px; margin-top: 1px;">
    {{ $concepts->links() }}
</div>
@else
<div style="text-align: center; padding: 60px; background: white; border-radius: 12px;">
    <div style="font-size: 64px; margin-bottom: 16px;">💡</div>
    <h3>لا توجد مفاهيم</h3>
    <p style="color: #64748b; margin-bottom: 24px;">ابدأ بإضافة أول مفهوم تعليمي</p>
    <a href="{{ route('admin.concepts.create') }}" class="btn-add">➕ إضافة مفهوم جديد</a>
</div>
@endif

@endsection
