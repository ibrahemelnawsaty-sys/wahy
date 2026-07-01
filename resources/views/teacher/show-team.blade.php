@extends('layouts.teacher')

@section('title', 'تفاصيل فريق: ' . $team->name)

@section('content')
<style>
    .show-team-page { max-width: 1000px; margin: 0 auto; padding: 24px; direction: rtl; }

    /* Header */
    .team-detail-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
        border-radius: 24px;
        padding: 40px;
        color: white;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(99, 102, 241, 0.3);
    }
    .team-detail-header::before {
        content: '';
        position: absolute;
        top: -60%; left: -20%;
        width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
        border-radius: 50%;
    }
    .header-inner { position: relative; z-index: 1; }
    .header-top { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
    .header-title { font-size: 28px; font-weight: 800; margin: 0 0 6px; }
    .header-sub { opacity: 0.85; font-size: 14px; margin: 0; display: flex; align-items: center; gap: 8px; }
    .btn-back-teams {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);
        padding: 12px 24px; border-radius: 14px; color: white;
        text-decoration: none; font-weight: 700; font-size: 14px;
        border: 1px solid rgba(255,255,255,0.3);
        transition: all 0.3s;
    }
    .btn-back-teams:hover { background: rgba(255,255,255,0.35); transform: translateY(-2px); }

    /* Header Info Grid */
    .header-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
    }
    .header-info-item {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 14px 18px;
        border: 1px solid rgba(255,255,255,0.2);
        text-align: center;
    }
    .header-info-label { color: rgba(255,255,255,0.8); font-size: 12px; font-weight: 600; }
    .header-info-value { color: white; font-size: 20px; font-weight: 800; margin-top: 4px; }

    /* Section Card */
    .detail-section {
        background: white;
        border-radius: 20px;
        padding: 28px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
    }
    /* الوضع الليلي: البطاقة مُعرّفة بكلاس (لا inline) فلا يلتقطها البلوك المركزي — نعتّمها هنا
       لتوازن عناوينها التي يُفتّحها اللايوت (وإلا فاتح-على-أبيض مخفي). */
    html[data-theme="dark"] .detail-section {
        background: var(--w-card, #1e293b) !important;
        border-color: var(--w-border, rgba(255,255,255,0.1)) !important;
    }
    html[data-theme="dark"] .section-head-title,
    html[data-theme="dark"] .member-name {
        color: var(--w-text, #f1f5f9) !important;
    }
    html[data-theme="dark"] .section-head { border-bottom-color: var(--w-border, rgba(255,255,255,0.1)) !important; }
    html[data-theme="dark"] .member-item { border-bottom-color: var(--w-border, rgba(255,255,255,0.1)) !important; }
    .section-head {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 20px; padding-bottom: 14px;
        border-bottom: 2px solid #f1f5f9;
    }
    .section-head-icon {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
    }
    .section-head-title { font-size: 20px; font-weight: 700; color: #1e293b; }

    /* Leader Card */
    .leader-card {
        display: flex; align-items: center; gap: 16px;
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border-radius: 16px; padding: 20px;
        border: 1px solid #fbbf24;
    }
    .leader-avatar {
        width: 56px; height: 56px; border-radius: 16px;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        display: flex; align-items: center; justify-content: center;
        font-size: 28px; color: white; font-weight: 800;
    }
    .leader-name { font-size: 18px; font-weight: 700; color: #92400e; }
    .leader-role { font-size: 13px; color: #b45309; font-weight: 600; }

    /* Members List */
    .member-item {
        display: flex; align-items: center; gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .member-item:last-child { border-bottom: none; }
    .member-avatar {
        width: 44px; height: 44px; border-radius: 12px;
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: #4f46e5; font-weight: 700;
    }
    .member-name { font-size: 15px; font-weight: 600; color: #1e293b; }
    .member-badge {
        margin-right: auto;
        padding: 4px 12px; border-radius: 8px;
        font-size: 12px; font-weight: 600;
        background: #ede9fe; color: #7c3aed;
    }

    /* Description */
    .team-description {
        font-size: 14px; color: #64748b; line-height: 1.8;
        padding: 16px 20px;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 14px;
        border-right: 4px solid #8b5cf6;
    }

    /* Empty State */
    .empty-mini {
        text-align: center; padding: 30px; color: #94a3b8;
    }
    .empty-mini .icon { font-size: 40px; margin-bottom: 8px; opacity: 0.5; }
    .empty-mini .text { font-size: 14px; font-weight: 600; }

    @media (max-width: 768px) {
        .team-detail-header { padding: 28px; }
        .header-top { flex-direction: column; text-align: center; }
        .header-info-grid { grid-template-columns: 1fr 1fr; }
    }
</style>

<div class="show-team-page">

    <!-- Header -->
    <div class="team-detail-header">
        <div class="header-inner">
            <div class="header-top">
                <div>
                    <h1 class="header-title">👥 {{ $team->name }}</h1>
                    <p class="header-sub">📚 {{ $team->classroom->name ?? 'بدون فصل' }}</p>
                </div>
                <a href="{{ route('teacher.teams') }}" class="btn-back-teams">
                    ↩️ رجوع للفرق
                </a>
            </div>
            <div class="header-info-grid">
                <div class="header-info-item">
                    <div class="header-info-label">الحالة</div>
                    <div class="header-info-value">{{ $team->status == 'active' ? '✅ نشط' : '⏸️ مؤرشف' }}</div>
                </div>
                <div class="header-info-item">
                    <div class="header-info-label">عدد الأعضاء</div>
                    <div class="header-info-value">{{ $team->members->count() }}</div>
                </div>
                <div class="header-info-item">
                    <div class="header-info-label">النقاط</div>
                    <div class="header-info-value">⭐ {{ $team->points ?? 0 }}</div>
                </div>
                <div class="header-info-item">
                    <div class="header-info-label">تاريخ الإنشاء</div>
                    <div class="header-info-value" style="font-size: 15px;">{{ $team->created_at->format('Y-m-d') }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($team->description)
    <!-- Description -->
    <div class="detail-section">
        <div class="section-head">
            <div class="section-head-icon" style="background: #f5f3ff;">📝</div>
            <div class="section-head-title">وصف الفريق</div>
        </div>
        <div class="team-description">{{ $team->description }}</div>
    </div>
    @endif

    <!-- Leader -->
    <div class="detail-section">
        <div class="section-head">
            <div class="section-head-icon" style="background: #fef3c7;">👑</div>
            <div class="section-head-title">قائد الفريق</div>
        </div>
        @if($leader)
        <div class="leader-card">
            <div class="leader-avatar">{{ mb_substr($leader->name, 0, 1) }}</div>
            <div>
                <div class="leader-name">{{ $leader->name }}</div>
                <div class="leader-role">👑 قائد الفريق</div>
            </div>
        </div>
        @else
        <div class="empty-mini">
            <div class="icon">👑</div>
            <div class="text">لم يتم تعيين قائد للفريق</div>
        </div>
        @endif
    </div>

    <!-- Members -->
    <div class="detail-section">
        <div class="section-head">
            <div class="section-head-icon" style="background: #e0e7ff;">👤</div>
            <div class="section-head-title">أعضاء الفريق</div>
            <div style="margin-right: auto; background: #ede9fe; color: #7c3aed; padding: 5px 14px; border-radius: 10px; font-size: 13px; font-weight: 700;">
                {{ $members->count() }} عضو
            </div>
        </div>
        @if($members->count() > 0)
            @foreach($members as $member)
            <div class="member-item">
                <div class="member-avatar">{{ mb_substr($member->name, 0, 1) }}</div>
                <div class="member-name">{{ $member->name }}</div>
                <div class="member-badge">عضو</div>
            </div>
            @endforeach
        @else
        <div class="empty-mini">
            <div class="icon">👤</div>
            <div class="text">لا يوجد أعضاء في هذا الفريق بعد</div>
        </div>
        @endif
    </div>

</div>
@endsection
