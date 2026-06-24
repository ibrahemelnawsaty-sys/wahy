@extends('layouts.admin')

@section('page-title', 'إدارة الأنشطة')

@section('content')
<style>
.activities-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.activities-title {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
}

.activities-filters {
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

.activities-table {
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

.activity-info h4 {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.activity-info p {
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

.type-quiz { background: #e0e7ff; color: #4338ca; }
.type-exercise { background: #dbeafe; color: #1e40af; }
.type-project { background: #fce7f3; color: #9f1239; }

.lesson-badge {
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
.status-inactive { background: #fee2e2; color: #991b1b; }

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

<div class="activities-header">
    <h1 class="activities-title">🎯 إدارة الأنشطة</h1>
    <a href="{{ route('admin.activities.create', request()->only('lesson_id')) }}" class="btn btn-primary">➕ إضافة نشاط جديد</a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<form method="GET" action="{{ route('admin.activities.index') }}">
<div class="activities-filters">
    <div class="filter-group">
        <label class="filter-label">بحث</label>
        <input type="text" name="search" class="filter-input" placeholder="ابحث عن نشاط..." value="{{ request('search') }}">
    </div>
    
    <div class="filter-group">
        <label class="filter-label">الدرس</label>
        <select name="lesson_id" class="filter-select">
            <option value="">جميع الدروس</option>
            @foreach($lessons as $lesson)
            <option value="{{ $lesson->id }}" {{ request('lesson_id') == $lesson->id ? 'selected' : '' }}>
                {{ $lesson->concept->value->icon }} {{ $lesson->title }}
            </option>
            @endforeach
        </select>
    </div>
    
    <div class="filter-group">
        <label class="filter-label">نوع النشاط</label>
        <select name="type" class="filter-select">
            <option value="">جميع الأنواع</option>
            <option value="quiz" {{ request('type') == 'quiz' ? 'selected' : '' }}>📋 اختبار</option>
            <option value="exercise" {{ request('type') == 'exercise' ? 'selected' : '' }}>✍️ تمرين</option>
            <option value="project" {{ request('type') == 'project' ? 'selected' : '' }}>🎨 مشروع</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label class="filter-label">الحالة</label>
        <select name="status" class="filter-select">
            <option value="">جميع الحالات</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
        </select>
    </div>
    
    <div class="filter-group" style="display: flex; align-items: flex-end;">
        <button type="submit" class="btn btn-primary" style="width: 100%;">🔍 بحث</button>
    </div>
</div>
</form>

@if($activities->isEmpty())
<div class="empty-state">
    <div class="empty-icon">🎯</div>
    <h3 class="empty-title">لا توجد أنشطة</h3>
    <p style="color: #64748b; margin-bottom: 20px;">ابدأ بإضافة الأنشطة التفاعلية للدروس</p>
    <a href="{{ route('admin.activities.create') }}" class="btn btn-primary">➕ إضافة نشاط جديد</a>
</div>
@else
<div class="activities-table">
    <table class="table">
        <thead>
            <tr>
                <th>النشاط</th>
                <th>النوع</th>
                <th>الدرس</th>
                <th>الحالة</th>
                <th>البيانات</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $activity)
            <tr>
                <td>
                    <div class="activity-info">
                        <h4>{{ $activity->title }}</h4>
                        @if($activity->description)
                        <p>{{ \Illuminate\Support\Str::limit($activity->description, 60) }}</p>
                        @endif
                    </div>
                </td>
                <td>
                    @if($activity->type == 'quiz')
                    <span class="type-badge type-quiz">📋 اختبار</span>
                    @elseif($activity->type == 'exercise')
                    <span class="type-badge type-exercise">✍️ تمرين</span>
                    @elseif($activity->type == 'project')
                    <span class="type-badge type-project">🎨 مشروع</span>
                    @endif
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <span class="lesson-badge">
                            📚 {{ $activity->lesson->title }}
                        </span>
                        <span class="lesson-badge">
                            {{ $activity->lesson->concept->value->icon }} {{ $activity->lesson->concept->name }}
                        </span>
                    </div>
                </td>
                <td>
                    <span class="status-badge status-{{ $activity->status }}">
                        @if($activity->status == 'active')
                        ✅ نشط
                        @else
                        ⏸️ غير نشط
                        @endif
                    </span>
                </td>
                <td>
                    <div class="stats-group">
                        @if($activity->points)
                        <span class="stat-item">النقاط: <span class="stat-value">{{ $activity->points }}</span> 🪙</span>
                        @endif
                        @if($activity->passing_score)
                        <span class="stat-item">النجاح: <span class="stat-value">{{ $activity->passing_score }}%</span></span>
                        @endif
                        @if($activity->questions && is_array($activity->questions))
                        <span class="stat-item">الأسئلة: <span class="stat-value">{{ count($activity->questions) }}</span></span>
                        @endif
                        <span class="stat-item">الترتيب: <span class="stat-value">#{{ $activity->order }}</span></span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="{{ route('admin.activities.show', $activity) }}" class="action-btn btn-view" title="عرض">👁️</a>
                        <a href="{{ route('admin.activities.edit', $activity) }}" class="action-btn btn-edit" title="تعديل">✏️</a>
                        <form action="{{ route('admin.activities.toggle-status', $activity) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="action-btn btn-toggle" title="تغيير الحالة">🔄</button>
                        </form>
                        <form action="{{ route('admin.activities.destroy', $activity) }}" method="POST" style="display: inline;" id="delete-form-{{ $activity->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="action-btn btn-delete" title="حذف" onclick="deleteActivity({{ $activity->id }}, '{{ $activity->title }}')">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top: 24px;">
    {{ $activities->links() }}
</div>
@endif

<script>
function deleteActivity(activityId, activityTitle) {
    const message = `هل أنت متأكد من حذف النشاط "<strong>${activityTitle}</strong>"؟<br><br>⚠️ هذا الإجراء لا يمكن التراجع عنه!`;
    
    showConfirm(
        message,
        function() {
            // عند الضغط على "نعم، احذف"
            document.getElementById('delete-form-' + activityId).submit();
        },
        '⚠️ تأكيد الحذف',
        'نعم، احذف',
        'إلغاء'
    );
}
</script>

@endsection
