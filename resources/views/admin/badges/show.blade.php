@extends('layouts.admin')

@section('page-title', 'عرض الشارة')

@section('content')
<style>
.badge-header {
    background: white;
    border-radius: 12px;
    padding: 32px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: start;
    border-top: 5px solid var(--badge-color, var(--color-primary));
}

.badge-icon-large {
    font-size: 72px;
    margin-bottom: 16px;
}

.badge-image-large {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 16px;
    border: 4px solid var(--badge-color, #e2e8f0);
}

.badge-title {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 12px;
}

.badge-description {
    color: #64748b;
    line-height: 1.8;
    margin-bottom: 20px;
}

.condition-banner {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 24px;
}

.badge-meta {
    display: flex;
    gap: 24px;
    padding-top: 24px;
    border-top: 1px solid #e2e8f0;
    flex-wrap: wrap;
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

.status-badge {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-active { background: #dcfce7; color: #16a34a; }
.status-inactive { background: #f3f4f6; color: #6b7280; }
</style>

<div class="badge-header" style="--badge-color: {{ $badge->color ?: 'var(--color-primary)' }};">
    <div>
        @if($badge->image)
        <img src="{{ asset('storage/' . $badge->image) }}" alt="{{ $badge->name }}" class="badge-image-large">
        @else
        <div class="badge-icon-large">{{ $badge->icon ?: '🏅' }}</div>
        @endif

        <h1 class="badge-title">{{ $badge->name }}</h1>
        <p class="badge-description">{{ $badge->description ?: 'لا يوجد وصف' }}</p>

        <div class="condition-banner">🎯 متى تظهر: {{ $badge->conditionLabel() }}</div>

        <div class="badge-meta">
            <div class="meta-item">
                <span class="meta-label">الحالة</span>
                <span class="status-badge status-{{ $badge->status }}">
                    {{ $badge->status == 'active' ? 'نشط' : 'غير نشط' }}
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">مكافأة العملات</span>
                <span class="meta-value">{{ $badge->coins_reward }} 🪙</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">نوع الشرط</span>
                <span class="meta-value">{{ \App\Models\Badge::CONDITION_TYPES[$badge->condition_type] ?? '—' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">قيمة الشرط</span>
                <span class="meta-value">{{ $badge->condition_value }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">التصنيف</span>
                <span class="meta-value">{{ ['achievement' => 'إنجاز', 'streak' => 'مواظبة', 'special' => 'خاصّة'][$badge->type] ?? $badge->type }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">الترتيب</span>
                <span class="meta-value">#{{ $badge->order }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">عدد المكتسبين</span>
                <span class="meta-value">{{ $badge->users_count }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">تاريخ الإنشاء</span>
                <span class="meta-value">{{ $badge->created_at?->format('Y/m/d') ?? '—' }}</span>
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="{{ route('admin.badges.edit', $badge) }}" class="btn btn-edit">
            ✏️ تعديل
        </a>
        <a href="{{ route('admin.badges.index') }}" class="btn btn-back">
            ← رجوع
        </a>
    </div>
</div>

@endsection
