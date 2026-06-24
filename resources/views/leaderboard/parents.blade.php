@extends('layouts.admin')

@section('title', 'لوحة صدارة أولياء الأمور')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('leaderboard.index') }}" class="text-purple-600 hover:text-purple-700">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-users text-purple-500 ml-2"></i>
                    لوحة صدارة أولياء الأمور
                </h1>
            </div>
            <p class="text-gray-600">أولياء الأمور المتميزون بمتابعة أبنائهم</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">إجمالي أولياء الأمور</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ count($leaderboard) }}</h3>
                </div>
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas fa-users text-purple-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">إجمالي النقاط</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ number_format(array_sum(array_column($leaderboard, 'points'))) }}</h3>
                </div>
                <div class="bg-yellow-100 p-4 rounded-full">
                    <i class="fas fa-star text-yellow-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-pink-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">متوسط النقاط</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ count($leaderboard) > 0 ? number_format(array_sum(array_column($leaderboard, 'points')) / count($leaderboard), 0) : 0 }}</h3>
                </div>
                <div class="bg-pink-100 p-4 rounded-full">
                    <i class="fas fa-chart-line text-pink-500 text-2xl"></i>
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
    <div class="bg-gradient-to-br from-purple-600 to-pink-600 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center text-3xl">
                    {{ $userRank['badge']['icon'] }}
                </div>
                <div>
                    <h3 class="text-xl font-bold">{{ $userRank['name'] }}</h3>
                    <p class="text-purple-200">{{ $userRank['badge']['label'] }}</p>
                </div>
            </div>
            <div class="flex gap-8">
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-300">#{{ $userRank['rank'] }}</div>
                    <div class="text-sm text-purple-200">ترتيبك</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-300">{{ number_format($userRank['points']) }}</div>
                    <div class="text-sm text-purple-200">نقاطك</div>
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

        <!-- List -->
        <div class="divide-y divide-gray-100">
            @forelse($leaderboard as $leader)
            <div class="flex items-center p-4 hover:bg-gray-50 transition-colors {{ $leader['rank'] <= 3 ? 'bg-gradient-to-l from-purple-50' : '' }}">
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
                     onerror="this.outerHTML='<div class=&quot;w-12 h-12 rounded-lg ml-3 flex items-center justify-center font-bold text-white&quot; style=&quot;background:linear-gradient(135deg,#f59e0b,#d97706);font-size:18px;&quot;>{{ $initial }}</div>'">
                <div class="flex-1">
                    <div class="font-bold text-gray-800">{{ $leader['name'] }}</div>
                    <div class="text-sm text-gray-500">{{ $leader['children_count'] ?? 0 }} أبناء</div>
                </div>
                <div class="text-left">
                    <div class="text-lg font-bold text-purple-600">{{ number_format($leader['points']) }}</div>
                    <div class="text-xs text-gray-500">نقطة</div>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-users text-5xl mb-3"></i>
                <p>لا يوجد أولياء أمور حتى الآن</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
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
        background: linear-gradient(135deg, #8b5cf6, #a855f7);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
    }

    .scope-btn:hover:not(.active) {
        border-color: #8b5cf6;
        background: #f9fafb;
    }
</style>
@endsection
