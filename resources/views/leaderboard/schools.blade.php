@extends('layouts.admin')

@section('title', 'لوحة صدارة المدارس')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('leaderboard.index') }}" class="text-orange-600 hover:text-orange-700">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-school text-orange-500 ml-2"></i>
                    لوحة صدارة المدارس
                </h1>
            </div>
            <p class="text-gray-600">المدارس المتميزة بإنجازات منسوبيها</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">إجمالي المدارس</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ count($leaderboard) }}</h3>
                </div>
                <div class="bg-orange-100 p-4 rounded-full">
                    <i class="fas fa-school text-orange-500 text-2xl"></i>
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

        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">إجمالي الطلاب</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ number_format(array_sum(array_column($leaderboard, 'students_count'))) }}</h3>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-user-graduate text-blue-500 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Filter -->
    <div class="flex gap-3 mb-6 overflow-x-auto pb-2">
        <a href="?period=weekly" class="scope-btn {{ $period === 'weekly' ? 'active' : '' }}">
            <i class="fas fa-calendar-week"></i> هذا الأسبوع
        </a>
        <a href="?period=monthly" class="scope-btn {{ $period === 'monthly' ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> هذا الشهر
        </a>
        <a href="?period=all" class="scope-btn {{ $period === 'all' ? 'active' : '' }}">
            <i class="fas fa-infinity"></i> كل الأوقات
        </a>
    </div>

    @if($schoolRank)
    <!-- School Rank Card -->
    <div class="bg-gradient-to-br from-orange-600 to-red-600 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center text-3xl">
                    🏫
                </div>
                <div>
                    <h3 class="text-xl font-bold">{{ $schoolRank['name'] }}</h3>
                    <p class="text-orange-200">مدرستك</p>
                </div>
            </div>
            <div class="flex gap-8">
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-300">#{{ $schoolRank['rank'] }}</div>
                    <div class="text-sm text-orange-200">الترتيب</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-300">{{ number_format($schoolRank['points']) }}</div>
                    <div class="text-sm text-orange-200">النقاط</div>
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

        @if(count($leaderboard) >= 3)
        <!-- Top 3 Podium -->
        <div class="bg-gradient-to-b from-gray-50 to-white p-8">
            <div class="flex justify-center items-end gap-4 max-w-3xl mx-auto">
                @foreach([1 => $leaderboard[1] ?? null, 0 => $leaderboard[0] ?? null, 2 => $leaderboard[2] ?? null] as $idx => $school)
                    @if($school)
                    <div class="flex-1 text-center {{ $idx === 0 ? 'transform scale-110' : '' }}">
                        <div class="text-4xl mb-2">{{ ['🥇', '🥈', '🥉'][$idx] }}</div>
                        <div class="w-{{ $idx === 0 ? '20' : '16' }} h-{{ $idx === 0 ? '20' : '16' }} rounded-xl mx-auto mb-2 border-4 {{ ['border-yellow-400', 'border-gray-400', 'border-orange-400'][$idx] }} bg-gray-100 p-2 flex items-center justify-center text-4xl">
                            🏫
                        </div>
                        <div class="font-bold text-sm text-gray-800 truncate px-2">{{ $school['name'] }}</div>
                        <div class="text-orange-600 font-semibold text-xs">{{ number_format($school['points']) }} نقطة</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $school['students_count'] }} طالب</div>
                        <div class="mt-3 rounded-t-lg {{ ['bg-gradient-to-t from-yellow-400 to-yellow-300 h-24', 'bg-gradient-to-t from-gray-400 to-gray-300 h-16', 'bg-gradient-to-t from-orange-400 to-orange-300 h-12'][$idx] }}"></div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Rest of the list -->
        <div class="divide-y divide-gray-100">
            @forelse(array_slice($leaderboard, 3) as $school)
            <div class="flex items-center p-4 hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 rounded-lg {{ $school['rank'] <= 10 ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center font-bold ml-3">
                    {{ $school['rank'] }}
                </div>
                <div class="w-14 h-14 rounded-xl bg-gray-100 p-2 flex items-center justify-center text-2xl ml-3">
                    🏫
                </div>
                <div class="flex-1">
                    <div class="font-bold text-gray-800">{{ $school['name'] }}</div>
                    <div class="flex gap-4 text-sm text-gray-500 mt-1">
                        <span><i class="fas fa-user-graduate ml-1"></i>{{ $school['students_count'] }}</span>
                        <span><i class="fas fa-chalkboard-teacher ml-1"></i>{{ $school['teachers_count'] }}</span>
                    </div>
                </div>
                <div class="text-left">
                    <div class="text-lg font-bold text-orange-600">{{ number_format($school['points']) }}</div>
                    <div class="text-xs text-gray-500">نقطة</div>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-school text-5xl mb-3"></i>
                <p>لا توجد مدارس حتى الآن</p>
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
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
    }

    .scope-btn:hover:not(.active) {
        border-color: #f97316;
        background: #f9fafb;
    }
</style>
@endsection
