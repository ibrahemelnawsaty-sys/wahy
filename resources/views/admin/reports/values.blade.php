@extends('layouts.admin')

@section('title', 'تقارير القيم')

@section('content')
<div class="values-reports">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>💎 تقارير القيم</h1>
            <p>التقدم في تطبيق القيم والمفاهيم</p>
        </div>
        <form method="POST" action="{{ route('admin.reports.export') }}">
            @csrf
            <input type="hidden" name="type" value="values">
            <button type="submit" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 10px 22px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(16,185,129,0.3);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">📥 تصدير Excel</button>
        </form>
    </div>

    <div class="values-grid">
        @forelse($values as $value)
        <div class="value-card">
            <div class="value-header">
                <div class="value-emoji">{{ $value->emoji }}</div>
                <h3>{{ $value->name }}</h3>
            </div>
            
            <div class="value-stats">
                <div class="stat-item">
                    <span class="stat-label">المفاهيم</span>
                    <span class="stat-value">{{ $value->concepts_count }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">الدروس</span>
                    <span class="stat-value">{{ $value->total_lessons }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">الأنشطة</span>
                    <span class="stat-value">{{ $value->total_activities }}</span>
                </div>
            </div>

            @if($value->description)
            <div class="value-description">
                {{ \Illuminate\Support\Str::limit($value->description, 100) }}
            </div>
            @endif

            <div class="value-concepts">
                <h4>المفاهيم:</h4>
                <div class="concepts-list">
                    @foreach($value->concepts as $concept)
                    <span class="concept-tag">{{ $concept->name }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <p>لا توجد قيم بعد</p>
        </div>
        @endforelse
    </div>
</div>

<style>
.values-reports {
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 28px;
    margin-bottom: 8px;
}

.page-header p {
    color: #64748b;
    font-size: 14px;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}

.value-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 2px solid #f1f5f9;
    transition: all 0.3s;
}

.value-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.value-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f5f9;
}

.value-emoji {
    font-size: 48px;
}

.value-header h3 {
    font-size: 22px;
}

.value-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
}

.stat-label {
    display: block;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 4px;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: var(--color-primary);
}

.value-description {
    font-size: 14px;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
}

.value-concepts h4 {
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 10px;
}

.concepts-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.concept-tag {
    padding: 6px 12px;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;
}

@media (max-width: 768px) {
    .values-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
