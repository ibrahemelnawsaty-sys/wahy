@extends('layouts.admin')

@section('page-title', 'إدارة القيم')

@section('content')
<style>
.values-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.value-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
}

.value-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.value-icon {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
}

.value-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 16px;
    border: 2px solid #e2e8f0;
}

.value-image-container {
    position: relative;
    margin-bottom: 16px;
}

.value-name {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.value-description {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 16px;
    line-height: 1.6;
}

.value-stats {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
}

.value-stat {
    font-size: 13px;
    color: #64748b;
}

.value-stat strong {
    color: var(--color-primary);
    font-weight: 600;
}

.value-actions {
    display: flex;
    gap: 8px;
    justify-content: space-between;
    align-items: center;
}

.value-status {
    position: absolute;
    top: 16px;
    left: 16px;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status-active { background: #dcfce7; color: #16a34a; }
.status-inactive { background: #f3f4f6; color: #6b7280; }

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
    display: inline-flex;
    align-items: center;
    gap: 4px;
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

<div class="values-header">
    <div>
        <h2 style="margin: 0 0 8px 0;">💎 إدارة القيم</h2>
        <p style="margin: 0; color: #64748b;">عرض وإدارة جميع القيم التعليمية</p>
    </div>
    <a href="{{ route('admin.values.create') }}" class="btn-add">
        ➕ إضافة قيمة جديدة
    </a>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET">
        <div class="filters-grid">
            <input type="text" name="search" placeholder="🔍 بحث..." value="{{ request('search') }}" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
            
            <select name="status" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
                <option value="">كل الحالات</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
            </select>

            <button type="submit" style="padding: 10px 20px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">تطبيق</button>
        </div>
    </form>
</div>

<!-- Values Grid -->
@if($values->count() > 0)
<div class="values-grid">
    @foreach($values as $value)
    <div class="value-card">
        <span class="value-status status-{{ $value->status }}">
            {{ $value->status == 'active' ? 'نشط' : 'غير نشط' }}
        </span>
        
        @if($value->image)
        <div class="value-image-container">
            <img src="{{ asset('storage/app/public/data/' . $value->image) }}" alt="{{ $value->name }}" class="value-image">
        </div>
        @else
        <span class="value-icon">{{ $value->icon ?? '💎' }}</span>
        @endif
        
        <h3 class="value-name">{{ $value->name }}</h3>
        <p class="value-description">{{ \Illuminate\Support\Str::limit($value->description, 100) ?? 'لا يوجد وصف' }}</p>
        
        <div class="value-stats">
            <div class="value-stat">
                المفاهيم: <strong>{{ $value->concepts->count() }}</strong>
            </div>
            <div class="value-stat">
                الترتيب: <strong>#{{ $value->order }}</strong>
            </div>
        </div>
        
        <div class="value-actions">
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('admin.values.show', $value) }}" class="btn-action btn-view">👁️ عرض</a>
                <a href="{{ route('admin.values.edit', $value) }}" class="btn-action btn-edit">✏️ تعديل</a>
            </div>
            <form method="POST" action="{{ route('admin.values.destroy', $value) }}" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه القيمة؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-delete">🗑️</button>
            </form>
        </div>
    </div>
    @endforeach
</div>

<div style="padding: 20px; background: white; border-radius: 12px;">
    {{ $values->links() }}
</div>
@else
<div style="text-align: center; padding: 60px; background: white; border-radius: 12px;">
    <div style="font-size: 64px; margin-bottom: 16px;">💎</div>
    <h3>لا توجد قيم</h3>
    <p style="color: #64748b; margin-bottom: 24px;">ابدأ بإضافة أول قيمة تعليمية</p>
    <a href="{{ route('admin.values.create') }}" class="btn-add">➕ إضافة قيمة جديدة</a>
</div>
@endif

@endsection
