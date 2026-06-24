@extends('layouts.admin')

@section('page-title', 'تفاصيل المفهوم')

@section('content')
<style>
.concept-header {
    background: white;
    border-radius: 12px;
    padding: 32px;
    margin-bottom: 24px;
}

.concept-title {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 12px;
}

.value-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f1f5f9;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 16px;
}

.concept-description {
    color: #64748b;
    font-size: 16px;
    line-height: 1.8;
    margin-bottom: 24px;
}

.concept-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    padding: 24px 0;
    border-top: 2px solid #f1f5f9;
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.meta-label {
    font-size: 13px;
    color: #94a3b8;
    font-weight: 600;
}

.meta-value {
    font-size: 16px;
    color: #1e293b;
    font-weight: 600;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 32px 0 16px;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.meanings-list {
    background: white;
    border-radius: 12px;
    overflow: hidden;
}

.meaning-item {
    padding: 20px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
}

.meaning-item:last-child {
    border-bottom: none;
}

.meaning-item:hover {
    background: #f8fafc;
}

.meaning-name {
    font-weight: 600;
    color: #1e293b;
    font-size: 16px;
    margin-bottom: 8px;
}

.meaning-description {
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
}

.meaning-meta {
    display: flex;
    gap: 16px;
    margin-top: 12px;
    font-size: 13px;
}

.meta-badge {
    padding: 4px 12px;
    background: #f1f5f9;
    border-radius: 6px;
    color: #475569;
    font-weight: 600;
}

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

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    border: none;
}

.btn-primary { background: var(--color-primary); color: white; }
.btn-secondary { background: #e2e8f0; color: #475569; }

.header-actions {
    display: flex;
    gap: 12px;
}
</style>

<div class="concept-header">
    <div class="value-badge">
        {{ $concept->value->icon }} {{ $concept->value->name }}
    </div>
    
    <h1 class="concept-title">💡 {{ $concept->name }}</h1>
    
    @if($concept->description)
    <p class="concept-description">{{ $concept->description }}</p>
    @endif

    <div class="concept-meta">
        <div class="meta-item">
            <span class="meta-label">الترتيب</span>
            <span class="meta-value">#{{ $concept->order }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">عدد المعاني</span>
            <span class="meta-value">{{ $lessonsCount }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">تاريخ الإضافة</span>
            <span class="meta-value">{{ $concept->created_at->format('Y-m-d') }}</span>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('admin.concepts.edit', $concept) }}" class="btn btn-primary">✏️ تعديل</a>
        <a href="{{ route('admin.values.show', $concept->value) }}" class="btn btn-secondary">⬅️ العودة للقيمة</a>
    </div>
</div>

<div class="section-header">
    <h2 class="section-title">� الدروس ({{ $lessonsCount }})</h2>
    <a href="{{ route('admin.lessons.create', ['concept_id' => $concept->id]) }}" class="btn btn-primary">➕ إضافة درس جديد</a>
</div>

@if($concept->lessons->isEmpty())
<div class="empty-state">
    <div class="empty-icon">📚</div>
    <h3 class="empty-title">لا توجد دروس لهذا المفهوم</h3>
    <p style="color: #64748b; margin-bottom: 20px;">ابدأ بإضافة الدروس المختلفة لهذا المفهوم</p>
    <a href="{{ route('admin.lessons.create', ['concept_id' => $concept->id]) }}" class="btn btn-primary">➕ إضافة درس جديد</a>
</div>
@else
<div class="meanings-list">
    @foreach($concept->lessons as $lesson)
    <div class="meaning-item">
        <h3 class="meaning-name">{{ $lesson->title }}</h3>
        @if($lesson->content)
        <p class="meaning-description">
            {{ html_excerpt($lesson->content, 150) }}
        </p>
        @endif
        <div class="meaning-meta">
            <span class="meta-badge">الترتيب: #{{ $lesson->order }}</span>
            <span class="meta-badge">النوع: {{ $lesson->type }}</span>
            <span class="meta-badge">النقاط: {{ $lesson->points ?? 0 }}</span>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
