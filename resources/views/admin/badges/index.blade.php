@extends('layouts.admin')

@section('page-title', 'إدارة الشارات')

@section('content')
<style>
.badges-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.badge-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
    border-top: 4px solid var(--badge-color, var(--color-primary));
}

.badge-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.badge-icon {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
}

.badge-image {
    width: 72px;
    height: 72px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 16px;
    border: 3px solid var(--badge-color, #e2e8f0);
    display: block;
}

.badge-name {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}
/* الوضع الليلي: العنوان مُعرّف بكلاس على بطاقة يعتّمها اللايوت — نفتّحه وإلا داكن-على-داكن مخفي. */
html[data-theme="dark"] .badge-name { color: var(--w-text, #f1f5f9) !important; }

.badge-description {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 16px;
    line-height: 1.6;
}

.badge-condition {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f1f5f9;
    color: #334155;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 16px;
}
html[data-theme="dark"] .badge-condition { background: var(--w-surface-2, #1e293b); color: var(--w-text, #f1f5f9); }

.badge-stats {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

.badge-stat {
    font-size: 13px;
    color: #64748b;
}

.badge-stat strong {
    color: var(--color-primary);
    font-weight: 600;
}

.badge-actions {
    display: flex;
    gap: 8px;
    justify-content: space-between;
    align-items: center;
}

.badge-status {
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
.btn-activate { background: #dcfce7; color: #16a34a; }
.btn-deactivate { background: #fef3c7; color: #b45309; }

.filters-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

<div class="badges-header">
    <div>
        <h2 style="margin: 0 0 8px 0;">🏅 إدارة الشارات</h2>
        <p style="margin: 0; color: #64748b;">عرض وإدارة شارات الإنجاز وشروط كسبها</p>
    </div>
    <a href="{{ route('admin.badges.create') }}" class="btn-add">
        ➕ إضافة شارة جديدة
    </a>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET">
        <div class="filters-grid">
            <input type="text" name="search" placeholder="🔍 بحث..." value="{{ request('search') }}" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">

            <select name="condition_type" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
                <option value="">كل الشروط</option>
                @foreach($conditionTypes as $key => $label)
                <option value="{{ $key }}" {{ request('condition_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="status" style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px;">
                <option value="">كل الحالات</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
            </select>

            <button type="submit" style="padding: 10px 20px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">تطبيق</button>
        </div>
    </form>
</div>

<!-- Badges Grid -->
@if($badges->count() > 0)
<div class="badges-grid">
    @foreach($badges as $badge)
    <div class="badge-card" style="--badge-color: {{ $badge->color ?: 'var(--color-primary)' }};">
        <span class="badge-status status-{{ $badge->status }}">
            {{ $badge->status == 'active' ? 'نشط' : 'غير نشط' }}
        </span>

        @if($badge->image)
        <img src="{{ asset('storage/' . $badge->image) }}" alt="{{ $badge->name }}" class="badge-image">
        @else
        <span class="badge-icon">{{ $badge->icon ?: '🏅' }}</span>
        @endif

        <h3 class="badge-name">{{ $badge->name }}</h3>
        <p class="badge-description">{{ \Illuminate\Support\Str::limit($badge->description, 90) ?: 'لا يوجد وصف' }}</p>

        <div class="badge-condition">🎯 {{ $badge->conditionLabel() }}</div>

        <div class="badge-stats">
            <div class="badge-stat">
                المكافأة: <strong>{{ $badge->coins_reward }} 🪙</strong>
            </div>
            <div class="badge-stat">
                المكتسبون: <strong>{{ $badge->users_count }}</strong>
            </div>
            <div class="badge-stat">
                الترتيب: <strong>#{{ $badge->order }}</strong>
            </div>
        </div>

        <div class="badge-actions">
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="{{ route('admin.badges.show', $badge) }}" class="btn-action btn-view">👁️ عرض</a>
                <a href="{{ route('admin.badges.edit', $badge) }}" class="btn-action btn-edit">✏️ تعديل</a>
                <form method="POST" action="{{ route('admin.badges.toggle-status', $badge) }}" style="display: inline;">
                    @csrf
                    @if($badge->status == 'active')
                    <button type="submit" class="btn-action btn-deactivate" title="تعطيل الشارة">⏸️ تعطيل</button>
                    @else
                    <button type="submit" class="btn-action btn-activate" title="تفعيل الشارة">▶️ تفعيل</button>
                    @endif
                </form>
            </div>
            @if($badge->users_count == 0)
            <form method="POST" action="{{ route('admin.badges.destroy', $badge) }}" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الشارة؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-delete">🗑️</button>
            </form>
            @else
            <span class="btn-action btn-delete" style="opacity: 0.5; cursor: not-allowed;" title="لا يمكن الحذف — الشارة مكتسبة">🔒</span>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div style="padding: 20px; background: white; border-radius: 12px;">
    {{ $badges->links() }}
</div>
@else
<div style="text-align: center; padding: 60px; background: white; border-radius: 12px;">
    <div style="font-size: 64px; margin-bottom: 16px;">🏅</div>
    <h3>لا توجد شارات</h3>
    <p style="color: #64748b; margin-bottom: 24px;">ابدأ بإضافة أول شارة إنجاز</p>
    <a href="{{ route('admin.badges.create') }}" class="btn-add">➕ إضافة شارة جديدة</a>
</div>
@endif

@endsection
