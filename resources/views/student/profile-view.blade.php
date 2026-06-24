@extends('layouts.student-app')

@section('title', 'حسابي')

@push('styles')
<style>
    .profile-container {
        max-width: 900px;
        margin: 0 auto;
        padding: var(--spacing-xl) var(--spacing-md);
        padding-bottom: 120px;
    }
    
    .profile-header-card {
        background: var(--glass-bg-heavy);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-2xl);
        padding: var(--spacing-2xl);
        margin-bottom: var(--spacing-xl);
        text-align: center;
    }
    
    .profile-avatar-large {
        width: 120px;
        height: 120px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 56px;
        margin: 0 auto var(--spacing-lg);
        border: 4px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 12px 40px rgba(16, 185, 129, 0.4);
        animation: float 3s ease-in-out infinite;
    }
    
    .profile-name {
        font-size: 28px;
        font-weight: 700;
        color: white;
        margin-bottom: var(--spacing-sm);
    }
    
    .profile-school {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: var(--spacing-lg);
    }
    
    .profile-level-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #FFD700, #FFA500);
        color: #7C3AED;
        padding: 10px 24px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 18px;
        box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-xl);
    }
    
    .stat-card {
        background: var(--glass-bg-medium);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-xl);
        padding: var(--spacing-lg);
        text-align: center;
    }
    
    .stat-icon {
        font-size: 36px;
        margin-bottom: var(--spacing-sm);
    }
    
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: white;
        margin-bottom: 6px;
    }
    
    .stat-label {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.7);
    }
    
    .badges-section {
        background: var(--glass-bg-medium);
        backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-xl);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
    }
    
    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: white;
        margin-bottom: var(--spacing-lg);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .badges-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: var(--spacing-md);
    }
    
    .badge-item {
        text-align: center;
        padding: var(--spacing-md);
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-lg);
        transition: all var(--transition-base);
    }
    
    .badge-item:hover {
        transform: translateY(-4px);
        background: rgba(255, 255, 255, 0.1);
    }
    
    .badge-icon {
        font-size: 48px;
        margin-bottom: 8px;
    }
    
    .badge-name {
        font-size: 12px;
        font-weight: 600;
        color: white;
    }
    
    .logout-btn {
        background: linear-gradient(135deg, #EF4444, #DC2626);
        color: white;
        border: none;
        padding: 14px 32px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: all var(--transition-base);
        width: 100%;
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
    }
    
    .logout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
    }
    
    .badges-section[style*="cursor: pointer"]:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
    }
</style>
@endpush

@section('content')
<div class="container-wrapper" style="padding-top: 100px; padding-bottom: 100px; padding-left: 20px; padding-right: 20px; max-width: 1200px; margin: 0 auto;">
<div class="profile-container fade-in">
    <!-- Profile Header -->
    <div class="profile-header-card scale-in">
        <div class="profile-avatar-large">
            {{ mb_substr(auth()->user()->name, 0, 1) }}
        </div>
        <h1 class="profile-name">{{ auth()->user()->name }}</h1>
        <div class="profile-school">🏫 {{ auth()->user()->school->name ?? 'مدرسة' }}</div>
        <div class="profile-level-badge">
            <span>⭐</span>
            <span>المستوى {{ $level }}</span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid slide-up">
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-value">{{ $stats['total_points'] ?? 0 }}</div>
            <div class="stat-label">إجمالي النقاط</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🔥</div>
            <div class="stat-value">{{ $stats['current_streak'] ?? 0 }}</div>
            <div class="stat-label">أيام متتالية</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🏅</div>
            <div class="stat-value">{{ $stats['total_badges'] ?? 0 }}</div>
            <div class="stat-label">الشارات</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">👑</div>
            <div class="stat-value">{{ $stats['total_crowns'] ?? 0 }}</div>
            <div class="stat-label">التيجان</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✓</div>
            <div class="stat-value">{{ $stats['completed_activities'] ?? 0 }}</div>
            <div class="stat-label">أنشطة مكتملة</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-value">{{ $stats['average_score'] ?? 0 }}%</div>
            <div class="stat-label">متوسط الدرجات</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value">{{ $stats['total_coins'] ?? 0 }}</div>
            <div class="stat-label">العملات</div>
        </div>
    </div>

    <!-- Badges Section -->
    @if($badges->count() > 0)
    <div class="badges-section slide-up" style="animation-delay: 0.1s;">
        <h2 class="section-title">
            <span style="font-size: 28px;">🎖️</span>
            <span>شاراتي ({{ $badges->count() }})</span>
        </h2>
        <div class="badges-grid">
            @foreach($badges as $badge)
            <div class="badge-item">
                <div class="badge-icon">{{ $badge->icon ?? '🏆' }}</div>
                <div class="badge-name">{{ $badge->name }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Account Info -->
    <div class="badges-section slide-up" style="animation-delay: 0.2s;">
        <h2 class="section-title">
            <span style="font-size: 28px;">ℹ️</span>
            <span>معلومات الحساب</span>
        </h2>
        <div style="display: flex; flex-direction: column; gap: var(--spacing-md); color: rgba(255,255,255,0.9);">
            <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <span style="color: rgba(255,255,255,0.6);">البريد الإلكتروني</span>
                <span style="font-weight: 600;">{{ auth()->user()->email }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <span style="color: rgba(255,255,255,0.6);">الهاتف</span>
                <span style="font-weight: 600;">{{ auth()->user()->phone ?? 'غير محدد' }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <span style="color: rgba(255,255,255,0.6);">تاريخ التسجيل</span>
                <span style="font-weight: 600;">{{ auth()->user()->created_at->format('Y/m/d') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0;">
                <span style="color: rgba(255,255,255,0.6);">QR Code</span>
                <span style="font-weight: 600;">{{ auth()->user()->qr_code }}</span>
            </div>
        </div>
    </div>

    <!-- Quick Links Grid -->
    <h2 class="section-title slide-up" style="animation-delay: 0.25s; margin-top: var(--spacing-xl);">
        <span style="font-size: 28px;">🚀</span>
        <span>روابط سريعة</span>
    </h2>
    
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--spacing-md); margin-bottom: var(--spacing-lg);">
        <!-- Crowns -->
        <a href="{{ route('student.crowns') }}" style="text-decoration: none;">
            <div class="badges-section slide-up" style="animation-delay: 0.3s; cursor: pointer; transition: all 0.3s ease; padding: var(--spacing-lg); text-align: center;">
                <span style="font-size: 48px; display: block; margin-bottom: var(--spacing-sm);">👑</span>
                <div style="font-size: 18px; font-weight: 700; color: white;">التيجان</div>
                <div style="font-size: 12px; color: rgba(255,255,255,0.6);">قيمك المتقنة</div>
            </div>
        </a>
        
        <!-- Gifts -->
        <a href="{{ route('student.gifts') }}" style="text-decoration: none;">
            <div class="badges-section slide-up" style="animation-delay: 0.35s; cursor: pointer; transition: all 0.3s ease; padding: var(--spacing-lg); text-align: center;">
                <span style="font-size: 48px; display: block; margin-bottom: var(--spacing-sm);">🎁</span>
                <div style="font-size: 18px; font-weight: 700; color: white;">الهدايا</div>
                <div style="font-size: 12px; color: rgba(255,255,255,0.6);">مدح ولي الأمر</div>
            </div>
        </a>
        
        <!-- Teams -->
        <a href="{{ route('student.teams') }}" style="text-decoration: none;">
            <div class="badges-section slide-up" style="animation-delay: 0.4s; cursor: pointer; transition: all 0.3s ease; padding: var(--spacing-lg); text-align: center;">
                <span style="font-size: 48px; display: block; margin-bottom: var(--spacing-sm);">👥</span>
                <div style="font-size: 18px; font-weight: 700; color: white;">فرقي</div>
                <div style="font-size: 12px; color: rgba(255,255,255,0.6);">العمل الجماعي</div>
            </div>
        </a>
        
        <!-- Badges -->
        <a href="{{ route('student.badges') }}" style="text-decoration: none;">
            <div class="badges-section slide-up" style="animation-delay: 0.45s; cursor: pointer; transition: all 0.3s ease; padding: var(--spacing-lg); text-align: center;">
                <span style="font-size: 48px; display: block; margin-bottom: var(--spacing-sm);">🎖️</span>
                <div style="font-size: 18px; font-weight: 700; color: white;">الشارات</div>
                <div style="font-size: 12px; color: rgba(255,255,255,0.6);">إنجازاتي</div>
            </div>
        </a>
    </div>

    <!-- Shop Link -->
    <a href="{{ route('student.shop') }}" style="text-decoration: none; display: block; margin-top: var(--spacing-lg);">
        <div class="badges-section slide-up" style="animation-delay: 0.5s; cursor: pointer; transition: all 0.3s ease; background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 165, 0, 0.1)); border: 1px solid rgba(255, 215, 0, 0.3);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                    <span style="font-size: 40px;">🛒</span>
                    <div>
                        <div style="font-size: 20px; font-weight: 700; color: white; margin-bottom: 4px;">المتجر</div>
                        <div style="font-size: 14px; color: rgba(255,255,255,0.6);">استبدل عملاتك بمكافآت رائعة! - رصيدك: <strong style="color: #FFD700;">{{ $stats['total_coins'] ?? 0 }}</strong> عملة</div>
                    </div>
                </div>
                <span style="font-size: 24px; color: rgba(255,255,255,0.5);">←</span>
            </div>
        </div>
    </a>

    <!-- Leaderboard Link -->
    <a href="{{ route('student.leaderboard') }}" style="text-decoration: none; display: block; margin-top: var(--spacing-lg);">
        <div class="badges-section slide-up" style="animation-delay: 0.55s; cursor: pointer; transition: all 0.3s ease;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                    <span style="font-size: 40px;">🏆</span>
                    <div>
                        <div style="font-size: 20px; font-weight: 700; color: white; margin-bottom: 4px;">المراتب</div>
                        <div style="font-size: 14px; color: rgba(255,255,255,0.6);">شاهد ترتيبك بين الطلاب</div>
                    </div>
                </div>
                <span style="font-size: 24px; color: rgba(255,255,255,0.5);">←</span>
            </div>
        </div>
    </a>

    <!-- Logout Button -->
    <form method="POST" action="{{ route('logout') }}" style="margin-top: var(--spacing-xl);">
        @csrf
        <button type="submit" class="logout-btn">
            تسجيل الخروج
        </button>
    </form>
</div>
</div>
@endsection
