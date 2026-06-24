@extends('layouts.admin')

@section('page-title', 'إدارة الدروس')

@section('content')
<style>
.lessons-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.lessons-title {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
}

.lessons-filters {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.filter-input,
.filter-select {
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.lessons-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table thead {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}

.table th {
    padding: 16px;
    text-align: right;
    font-weight: 600;
    color: #475569;
    font-size: 14px;
}

.table td {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.lesson-info h4 {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.lesson-info p {
    color: #64748b;
    font-size: 13px;
}

.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
}

.type-text { background: #dbeafe; color: #1e40af; }
.type-video { background: #fce7f3; color: #9f1239; }
.type-audio { background: #fef3c7; color: #92400e; }

.meaning-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #f1f5f9;
    border-radius: 6px;
    font-size: 12px;
    color: #475569;
    margin-bottom: 4px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
}

.status-active { background: #dcfce7; color: #166534; }
.status-draft { background: #fee2e2; color: #991b1b; }

.stats-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.stat-item {
    font-size: 13px;
    color: #64748b;
}

.stat-value {
    font-weight: 700;
    color: var(--color-primary);
}

.action-btn {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    transition: transform 0.2s;
}

.action-btn:hover {
    transform: scale(1.1);
}

.btn-view { background: #e0f2fe; }
.btn-edit { background: #fef3c7; }
.btn-toggle { background: #e9d5ff; }
.btn-delete { background: #fee2e2; }

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-primary { background: var(--color-primary); color: white; }

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 16px;
}

.empty-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.alert {
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success { background: #dcfce7; color: #166534; }
.alert-error { background: #fee2e2; color: #991b1b; }
</style>

<div class="lessons-header">
    <h1 class="lessons-title">📚 إدارة الدروس</h1>
    <a href="{{ route('admin.lessons.create', request()->only('concept_id')) }}" class="btn btn-primary">➕ إضافة درس جديد</a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<form method="GET" action="{{ route('admin.lessons.index') }}">
<div class="lessons-filters">
    <div class="filter-group">
        <label class="filter-label">بحث</label>
        <input type="text" name="search" class="filter-input" placeholder="ابحث عن درس..." value="{{ request('search') }}">
    </div>
    
    <div class="filter-group">
        <label class="filter-label">المفهوم</label>
        <select name="concept_id" class="filter-select">
            <option value="">جميع المفاهيم</option>
            @foreach($concepts as $concept)
            <option value="{{ $concept->id }}" {{ request('concept_id') == $concept->id ? 'selected' : '' }}>
                {{ $concept->value->icon }} {{ $concept->name }}
            </option>
            @endforeach
        </select>
    </div>
    
    <div class="filter-group">
        <label class="filter-label">نوع الدرس</label>
        <select name="type" class="filter-select">
            <option value="">جميع الأنواع</option>
            <option value="text" {{ request('type') == 'text' ? 'selected' : '' }}>📝 نص</option>
            <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>🎥 فيديو</option>
            <option value="audio" {{ request('type') == 'audio' ? 'selected' : '' }}>🎵 صوت</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label class="filter-label">الحالة</label>
        <select name="status" class="filter-select">
            <option value="">جميع الحالات</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
        </select>
    </div>
    
    <div class="filter-group" style="display: flex; align-items: flex-end;">
        <button type="submit" class="btn btn-primary" style="width: 100%;">🔍 بحث</button>
    </div>
</div>
</form>

@if($lessons->isEmpty())
<div class="empty-state">
    <div class="empty-icon">📚</div>
    <h3 class="empty-title">لا توجد دروس</h3>
    <p style="color: #64748b; margin-bottom: 20px;">ابدأ بإضافة الدروس التعليمية للمعاني</p>
    <a href="{{ route('admin.lessons.create') }}" class="btn btn-primary">➕ إضافة درس جديد</a>
</div>
@else
<div class="lessons-table">
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table class="table">
        <thead>
            <tr>
                <th>الدرس</th>
                <th>النوع</th>
                <th>المعنى و المفهوم</th>
                <th>الحالة</th>
                <th>البيانات</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lessons as $lesson)
            <tr>
                <td>
                    <div class="lesson-info">
                        <h4>{{ $lesson->title }}</h4>
                        @if($lesson->content)
                        <p>{{ html_excerpt($lesson->content, 60) }}</p>
                        @endif
                    </div>
                </td>
                <td>
                    @if($lesson->type == 'text')
                    <span class="type-badge type-text">📝 نص</span>
                    @elseif($lesson->type == 'video')
                    <span class="type-badge type-video">🎥 فيديو</span>
                    @elseif($lesson->type == 'audio')
                    <span class="type-badge type-audio">🎵 صوت</span>
                    @endif
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <span class="meaning-badge">
                            {{ $lesson->concept?->value?->icon }} {{ $lesson->concept?->value?->name }}
                        </span>
                        <span class="meaning-badge">
                            💡 {{ $lesson->concept?->name }}
                        </span>
                    </div>
                </td>
                <td>
                    <span class="status-badge status-{{ $lesson->status }}">
                        @if($lesson->status == 'active')
                        ✅ نشط
                        @else
                        ⏸️ غير نشط
                        @endif
                    </span>
                </td>
                <td>
                    <div class="stats-group">
                        @if($lesson->duration)
                        <span class="stat-item">المدة: <span class="stat-value">{{ $lesson->duration }}</span> دقيقة</span>
                        @endif
                        @if($lesson->points)
                        <span class="stat-item">النقاط: <span class="stat-value">{{ $lesson->points }}</span> 🪙</span>
                        @endif
                        <span class="stat-item">الترتيب: <span class="stat-value">#{{ $lesson->order }}</span></span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="{{ route('admin.lessons.show', $lesson) }}" class="action-btn btn-view" title="عرض">👁️</a>
                        <a href="{{ route('admin.lessons.edit', $lesson) }}" class="action-btn btn-edit" title="تعديل">✏️</a>
                        <form action="{{ route('admin.lessons.toggle-status', $lesson) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="action-btn btn-toggle" title="تغيير الحالة">🔄</button>
                        </form>
                        <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الدرس؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn btn-delete" title="حذف">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $lessons->links() }}
</div>
@endif

@endsection
