@extends('layouts.admin')

@section('page-title', 'إدارة المدارس')

@section('content')
<style>
.schools-header {
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
    transition: all 0.2s;
}

.btn-add:hover {
    background: var(--color-primary-hover);
}

.schools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 24px;
}

.school-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.3s;
}

.school-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.school-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 16px;
}

.school-name {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.school-qr {
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-family: monospace;
    color: #64748b;
}

.school-info {
    margin-bottom: 16px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 14px;
    color: #64748b;
}

.school-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 2px solid #f1f5f9;
}

.stat-box {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: 4px;
}

.stat-label {
    font-size: 12px;
    color: #64748b;
}

.school-actions {
    display: flex;
    gap: 8px;
    margin-top: 16px;
}

.btn-action {
    flex: 1;
    padding: 10px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
    text-align: center;
}

.btn-view {
    background: #dbeafe;
    color: #2563eb;
}

.btn-edit {
    background: #fef3c7;
    color: #d97706;
}

.btn-delete {
    background: #fee2e2;
    color: #dc2626;
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
}
</style>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="schools-header">
    <div>
        <h2 style="margin: 0 0 8px 0;">🏫 إدارة المدارس</h2>
        <p style="margin: 0; color: #64748b;">عرض وإدارة جميع المدارس المسجلة</p>
    </div>
    <a href="{{ route('admin.schools.create') }}" class="btn-add">➕ إضافة مدرسة</a>
</div>

@if($schools->count() > 0)
<div class="schools-grid">
    @foreach($schools as $school)
    <div class="school-card">
        <div class="school-header">
            <div>
                <h3 class="school-name">{{ $school->name }}</h3>
                <span class="school-qr">{{ $school->qr_code }}</span>
            </div>
            <span style="font-size: 24px;">{{ $school->status === 'active' ? '✅' : '⏸️' }}</span>
        </div>

        <div class="school-info">
            <div class="info-item">
                <span>📍</span>
                <span>{{ $school->city }}</span>
            </div>
            <div class="info-item">
                <span>📧</span>
                <span>{{ $school->contact_email }}</span>
            </div>
            <div class="info-item">
                <span>📞</span>
                <span>{{ $school->contact_phone }}</span>
            </div>
        </div>

        <div class="school-stats">
            <div class="stat-box">
                <span class="stat-number">{{ $school->users()->where('role', 'teacher')->count() }}</span>
                <span class="stat-label">معلم</span>
            </div>
            <div class="stat-box">
                <span class="stat-number">{{ $school->users()->where('role', 'student')->count() }}</span>
                <span class="stat-label">طالب</span>
            </div>
            <div class="stat-box">
                <span class="stat-number">{{ $school->users()->count() }}</span>
                <span class="stat-label">إجمالي</span>
            </div>
        </div>

        <div class="school-actions">
            <a href="{{ route('admin.schools.show', $school) }}" class="btn-action btn-view">👁️ عرض</a>
            <a href="{{ route('admin.schools.edit', $school) }}" class="btn-action btn-edit">✏️ تعديل</a>
            <a href="{{ route('admin.schools.active-values', $school) }}" class="btn-action btn-edit">🎯 القيم المفعّلة</a>
            <form method="POST" action="{{ route('admin.schools.destroy', $school) }}" style="flex: 1;" onsubmit="return confirm('هل أنت متأكد؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-delete" style="width: 100%;">🗑️</button>
            </form>
        </div>
    </div>
    @endforeach
</div>

<div style="margin-top: 24px;">
    {{ $schools->links() }}
</div>
@else
<div style="text-align: center; padding: 60px; background: white; border-radius: 12px;">
    <div style="font-size: 64px; margin-bottom: 16px;">🏫</div>
    <h3>لا توجد مدارس</h3>
    <p style="color: #64748b;">ابدأ بإضافة مدرسة جديدة</p>
</div>
@endif

@endsection
