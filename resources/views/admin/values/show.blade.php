@extends('layouts.admin')

@section('page-title', 'عرض القيمة')

@section('content')
<style>
.value-header {
    background: white;
    border-radius: 12px;
    padding: 32px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: start;
}

.value-icon-large {
    font-size: 72px;
    margin-bottom: 16px;
}

.value-title {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 12px;
}

.value-description {
    color: #64748b;
    line-height: 1.8;
    margin-bottom: 24px;
}

.value-meta {
    display: flex;
    gap: 24px;
    padding-top: 24px;
    border-top: 1px solid #e2e8f0;
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.meta-label {
    font-size: 12px;
    color: #94a3b8;
    text-transform: uppercase;
}

.meta-value {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-edit { background: #dbeafe; color: #2563eb; }
.btn-back { background: #e2e8f0; color: #475569; }

.concepts-section {
    background: white;
    border-radius: 12px;
    padding: 32px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.concepts-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.concept-item {
    padding: 20px;
    background: #f8fafc;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.concept-info h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
}

.concept-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

.empty-state {
    text-align: center;
    padding: 60px;
    color: #94a3b8;
}

.empty-state-icon {
    font-size: 64px;
    margin-bottom: 16px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-active { background: #dcfce7; color: #16a34a; }
.status-inactive { background: #f3f4f6; color: #6b7280; }
</style>

<div class="value-header">
    <div>
        <div class="value-icon-large">{{ $value->icon ?? '💎' }}</div>
        <h1 class="value-title">{{ $value->name }}</h1>
        <p class="value-description">{{ $value->description ?? 'لا يوجد وصف' }}</p>
        
        <div class="value-meta">
            <div class="meta-item">
                <span class="meta-label">الحالة</span>
                <span class="status-badge status-{{ $value->status }}">
                    {{ $value->status == 'active' ? 'نشط' : 'غير نشط' }}
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">الترتيب</span>
                <span class="meta-value">#{{ $value->order }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">المفاهيم</span>
                <span class="meta-value">{{ $conceptsCount }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">الدروس</span>
                <span class="meta-value">{{ $lessonsCount }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">تاريخ الإنشاء</span>
                <span class="meta-value">{{ $value->created_at->format('Y/m/d') }}</span>
            </div>
        </div>
    </div>
    
    <div class="action-buttons">
        <a href="{{ route('admin.values.edit', $value) }}" class="btn btn-edit">
            ✏️ تعديل
        </a>
        <a href="{{ route('admin.values.index') }}" class="btn btn-back">
            ← رجوع
        </a>
    </div>
</div>

<div class="concepts-section">
    <div class="section-header">
        <h2 class="section-title">💡 المفاهيم ({{ $conceptsCount }})</h2>
        <a href="{{ route('admin.concepts.create', ['value_id' => $value->id]) }}" class="btn btn-edit">➕ إضافة مفهوم</a>
    </div>
    
    @if($value->concepts->count() > 0)
    <div class="concepts-list">
        @foreach($value->concepts as $concept)
        <div class="concept-item">
            <div class="concept-info">
                <h4>{{ $concept->name }}</h4>
                <p>{{ \Illuminate\Support\Str::limit($concept->description, 100) ?? 'لا يوجد وصف' }}</p>
                <small style="color: #94a3b8; margin-top: 8px; display: block;">
                    الدروس: {{ $concept->lessons->count() }} | الترتيب: #{{ $concept->order }}
                </small>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('admin.concepts.show', $concept) }}" class="btn btn-edit" style="padding: 8px 16px; font-size: 13px;">عرض</a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon">💡</div>
        <h3 style="margin-bottom: 8px;">لا توجد مفاهيم بعد</h3>
        <p>ابدأ بإضافة أول مفهوم لهذه القيمة</p>
    </div>
    @endif
</div>

@endsection
