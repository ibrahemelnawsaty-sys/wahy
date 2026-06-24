@extends('layouts.admin')

@section('title', 'لوحة صدارة الطلاب')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('leaderboard.index') }}" class="text-blue-600 hover:text-blue-700">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-user-graduate text-blue-500 ml-2"></i>
                    لوحة صدارة الطلاب
                </h1>
            </div>
            <p class="text-gray-600">أفضل الطلاب المتفوقين في المنصة</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">إجمالي الطلاب</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ count($leaderboard) }}</h3>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-user-graduate text-blue-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">إجمالي النقاط</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ number_format(array_sum(array_column($leaderboard, 'points'))) }}</h3>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-star text-green-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">متوسط النقاط</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ count($leaderboard) > 0 ? number_format(array_sum(array_column($leaderboard, 'points')) / count($leaderboard), 0) : 0 }}</h3>
                </div>
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas fa-chart-line text-purple-500 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Scope Filter -->
    <div class="flex gap-3 mb-6 overflow-x-auto pb-2">
        <a href="?scope=school" class="scope-btn {{ $scope === 'school' ? 'active' : '' }}">
            <i class="fas fa-school"></i> مدرستي
        </a>
        <a href="?scope=all" class="scope-btn {{ $scope === 'all' ? 'active' : '' }}">
            <i class="fas fa-globe"></i> الكل
        </a>
    </div>

    @if($userRank)
    <!-- User Rank Card -->
    <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center text-3xl">
                    {{ $userRank['badge']['icon'] }}
                </div>
                <div>
                    <h3 class="text-xl font-bold">{{ $userRank['name'] }}</h3>
                    <p class="text-blue-200">{{ $userRank['badge']['label'] }}</p>
                </div>
            </div>
            <div class="flex gap-8">
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-300">#{{ $userRank['rank'] }}</div>
                    <div class="text-sm text-blue-200">ترتيبك</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-300">{{ number_format($userRank['points']) }}</div>
                    <div class="text-sm text-blue-200">نقاطك</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Leaderboard List -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-trophy text-yellow-500 ml-2"></i>
                قائمة المتصدرين
            </h2>
        </div>

<style>
    .leaderboard-page {
        max-width: 900px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .page-header h1 {
        font-size: 32px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 10px;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #6366f1;
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .scope-tabs {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .scope-tab {
        padding: 12px 24px;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        background: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        color: #64748b;
    }

    .scope-tab.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }

    @if($userRank)
    .my-rank-card {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: white;
    }

    .my-rank-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .my-rank-badge {
        font-size: 40px;
    }

    .my-rank-text h3 {
        font-size: 18px;
        margin: 0 0 5px;
    }

    .my-rank-text p {
        margin: 0;
        opacity: 0.8;
    }

    .my-rank-number {
        font-size: 36px;
        font-weight: 800;
    }
    @endif

    .leaderboard-list {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    }

    .leader-row {
        display: flex;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s;
    }

    .leader-row:hover {
        background: #f8fafc;
    }

    .leader-row.top-1 { background: linear-gradient(135deg, #fefce8, #fef9c3); }
    .leader-row.top-2 { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
    .leader-row.top-3 { background: linear-gradient(135deg, #fff7ed, #ffedd5); }

    .rank-badge {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 800;
        margin-left: 20px;
        background: #f1f5f9;
        color: #64748b;
    }

    .rank-badge.gold { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; font-size: 24px; }
    .rank-badge.silver { background: linear-gradient(135deg, #94a3b8, #64748b); color: white; font-size: 24px; }
    .rank-badge.bronze { background: linear-gradient(135deg, #f97316, #ea580c); color: white; font-size: 24px; }

    .leader-avatar {
        width: 55px;
        height: 55px;
        border-radius: 14px;
        object-fit: cover;
        margin-left: 15px;
    }

    .leader-info {
        flex: 1;
    }

    .leader-name {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .leader-meta {
        font-size: 13px;
        color: #64748b;
    }

    .leader-points {
        text-align: left;
    }

    .points-value {
        font-size: 22px;
        font-weight: 800;
        color: #6366f1;
    }

    .points-label {
        font-size: 12px;
        color: #94a3b8;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #94a3b8;
    }

    .empty-icon {
        font-size: 80px;
        margin-bottom: 20px;
    }

    .scope-btn {
        padding: 12px 24px;
        border-radius: 10px;
        border: 2px solid #e5e7eb;
        background: white;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        color: #6b7280;
        white-space: nowrap;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .scope-btn.active {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .scope-btn:hover:not(.active) {
        border-color: #3b82f6;
        background: #f9fafb;
    }
</style>

        <!-- List -->
        <div class="divide-y divide-gray-100">
            @forelse($leaderboard as $leader)
            <div class="flex items-center p-4 hover:bg-gray-50 transition-colors {{ $leader['rank'] <= 3 ? 'bg-gradient-to-l from-yellow-50' : '' }}">
                <div class="w-10 h-10 rounded-lg {{ $leader['rank'] == 1 ? 'bg-gradient-to-br from-yellow-400 to-yellow-500 text-white' : ($leader['rank'] == 2 ? 'bg-gradient-to-br from-gray-400 to-gray-500 text-white' : ($leader['rank'] == 3 ? 'bg-gradient-to-br from-orange-400 to-orange-500 text-white' : 'bg-gray-100 text-gray-600')) }} flex items-center justify-center font-bold ml-3">
                    @if($leader['rank'] <= 3)
                        {{ ['', '🥇', '🥈', '🥉'][$leader['rank']] }}
                    @else
                        {{ $leader['rank'] }}
                    @endif
                </div>
                @php $initial = mb_substr($leader['name'] ?? '?', 0, 1); @endphp
                <img src="{{ $leader['avatar'] }}" alt="{{ $leader['name'] ?? '' }}"
                     class="w-12 h-12 rounded-lg object-cover ml-3"
                     onerror="this.outerHTML='<div class=&quot;w-12 h-12 rounded-lg ml-3 flex items-center justify-center font-bold text-white&quot; style=&quot;background:linear-gradient(135deg,#667eea,#764ba2);font-size:18px;&quot;>{{ $initial }}</div>'">
                <div class="flex-1">
                    <div class="font-bold text-gray-800">{{ $leader['name'] }}</div>
                    <div class="text-sm text-gray-500">{{ $leader['school'] ?? '-' }}</div>
                </div>
                <div class="text-left">
                    <div class="text-lg font-bold text-blue-600">{{ number_format($leader['points']) }}</div>
                    <div class="text-xs text-gray-500">نقطة</div>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-user-graduate text-5xl mb-3"></i>
                <p>لا يوجد طلاب حتى الآن</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
