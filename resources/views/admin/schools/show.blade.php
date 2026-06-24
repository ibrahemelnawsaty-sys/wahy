@extends('layouts.admin')

@section('page-title', 'تفاصيل المدرسة')

@section('content')
<style>
.school-details {
    max-width: 1200px;
    margin: 0 auto;
}

.school-header-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: start;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-top: 24px;
}

.info-item {
    display: flex;
    gap: 12px;
}

.info-icon {
    font-size: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: 8px;
}

.stat-label {
    color: #64748b;
    font-size: 14px;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
}

.btn-edit {
    background: #fef3c7;
    color: #d97706;
}

.btn-back {
    background: #e2e8f0;
    color: #475569;
}
</style>

<div class="school-details">
    <div class="school-header-card">
        <div>
            <h1 style="margin: 0 0 8px 0;">🏫 {{ $school->name }}</h1>
            <p style="color: #64748b; margin: 0 0 16px 0;">{{ $school->description }}</p>
            <code style="background: #f1f5f9; padding: 6px 12px; border-radius: 6px;">{{ $school->qr_code }}</code>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('admin.schools.edit', $school) }}" class="btn btn-edit">✏️ تعديل</a>
            <a href="{{ route('admin.schools.index') }}" class="btn btn-back">← رجوع</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['teachers'] }}</div>
            <div class="stat-label">👨‍🏫 معلم</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['students'] }}</div>
            <div class="stat-label">🎓 طالب</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['parents'] }}</div>
            <div class="stat-label">👨‍👩‍👧 ولي أمر</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_users'] }}</div>
            <div class="stat-label">👥 إجمالي المستخدمين</div>
        </div>
    </div>

    <div style="background: white; border-radius: 12px; padding: 32px;">
        <h3 style="margin: 0 0 24px 0;">📋 معلومات الاتصال</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-icon">📍</span>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">العنوان</strong>
                    <span style="color: #64748b;">{{ $school->address }}</span>
                </div>
            </div>
            <div class="info-item">
                <span class="info-icon">🏙️</span>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">المدينة</strong>
                    <span style="color: #64748b;">{{ $school->city }}</span>
                </div>
            </div>
            <div class="info-item">
                <span class="info-icon">📧</span>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">البريد الإلكتروني</strong>
                    <span style="color: #64748b;">{{ $school->contact_email }}</span>
                </div>
            </div>
            <div class="info-item">
                <span class="info-icon">📞</span>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">رقم الهاتف</strong>
                    <span style="color: #64748b;">{{ $school->contact_phone }}</span>
                </div>
            </div>
            <div class="info-item">
                <span class="info-icon">✅</span>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">الحالة</strong>
                    <span style="color: {{ $school->status === 'active' ? '#16a34a' : '#dc2626' }};">
                        {{ $school->status === 'active' ? 'نشط' : 'غير نشط' }}
                    </span>
                </div>
            </div>
            <div class="info-item">
                <span class="info-icon">📅</span>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">تاريخ التسجيل</strong>
                    <span style="color: #64748b;">{{ $school->created_at->format('Y-m-d') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
