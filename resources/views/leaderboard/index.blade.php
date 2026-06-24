@extends('layouts.admin')

@section('title', 'لوحة الصدارة')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🏆 لوحة الصدارة</h1>
            <p class="text-gray-600">تنافس واحصد النقاط وتصدر القائمة!</p>
        </div>
    </div>

    @if($userRank)
    <!-- User Rank Card -->
    <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center text-3xl">
                    {{ $userRank['badge']['icon'] }}
                </div>
                <div>
                    <h3 class="text-xl font-bold">{{ $userRank['name'] }}</h3>
                    <p class="text-indigo-200">{{ $userRank['badge']['label'] }}</p>
                </div>
            </div>
            <div class="flex gap-8">
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-300">#{{ $userRank['rank'] }}</div>
                    <div class="text-sm text-indigo-200">ترتيبك</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-300">{{ number_format($userRank['points']) }}</div>
                    <div class="text-sm text-indigo-200">نقاطك</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tabs -->
    <div class="flex gap-3 mb-6 overflow-x-auto pb-2">
        <button class="tab-btn {{ $tab === 'students' ? 'active' : '' }}" onclick="switchTab('students')">
            <i class="fas fa-user-graduate"></i> الطلاب
        </button>
        <button class="tab-btn {{ $tab === 'teachers' ? 'active' : '' }}" onclick="switchTab('teachers')">
            <i class="fas fa-chalkboard-teacher"></i> المعلمون
        </button>
        <button class="tab-btn {{ $tab === 'parents' ? 'active' : '' }}" onclick="switchTab('parents')">
            <i class="fas fa-users"></i> أولياء الأمور
        </button>
        <button class="tab-btn {{ $tab === 'schools' ? 'active' : '' }}" onclick="switchTab('schools')">
            <i class="fas fa-school"></i> المدارس
        </button>
    </div>

    <!-- Students Tab -->
    <div id="section-students" class="tab-content" style="{{ $tab !== 'students' ? 'display:none' : '' }}">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-user-graduate text-blue-500 ml-2"></i>
                    أفضل الطلاب
                </h2>
                <a href="{{ route('leaderboard.students') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                    عرض الكل <i class="fas fa-arrow-left mr-1"></i>
                </a>
            </div>

            @if(count($data['students']) >= 3)
            <!-- Top 3 Podium -->
            <div class="bg-gradient-to-b from-gray-50 to-white p-8">
                <div class="flex justify-center items-end gap-4 max-w-2xl mx-auto">
                    @foreach([$data['students'][1] ?? null, $data['students'][0] ?? null, $data['students'][2] ?? null] as $i => $leader)
                        @if($leader)
                        <div class="flex-1 text-center {{ ['', 'transform scale-110', ''][$i] }}">
                            <div class="text-4xl mb-2">{{ ['🥈', '🥇', '🥉'][$i] }}</div>
                            <img src="{{ $leader['avatar'] }}" alt="" 
                                 class="w-{{ $i === 1 ? '20' : '16' }} h-{{ $i === 1 ? '20' : '16' }} rounded-full mx-auto mb-2 border-4 {{ ['border-gray-400', 'border-yellow-400', 'border-orange-400'][$i] }} object-cover" 
                                 onerror="this.outerHTML='<div class=&quot;w-16 h-16 rounded-full mx-auto mb-2 flex items-center justify-center font-bold text-white&quot; style=&quot;background:linear-gradient(135deg,#667eea,#764ba2);font-size:24px;&quot;>{{ mb_substr($leader['name'] ?? '?', 0, 1) }}</div>'">
                            <div class="font-bold text-sm text-gray-800 truncate">{{ $leader['name'] }}</div>
                            <div class="text-blue-600 font-semibold text-xs">{{ number_format($leader['points']) }} نقطة</div>
                            <div class="mt-3 rounded-t-lg {{ ['bg-gradient-to-t from-gray-400 to-gray-300 h-16', 'bg-gradient-to-t from-yellow-400 to-yellow-300 h-24', 'bg-gradient-to-t from-orange-400 to-orange-300 h-12'][$i] }}"></div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Rest of the list -->
            <div class="divide-y divide-gray-100">
                @forelse(array_slice($data['students'], 3, 7) as $leader)
                <div class="flex items-center p-4 hover:bg-gray-50 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center font-bold text-gray-600 ml-3">
                        {{ $leader['rank'] }}
                    </div>
                    <img src="{{ $leader['avatar'] }}" alt="{{ $leader['name'] ?? '' }}"
                         class="w-12 h-12 rounded-lg object-cover ml-3"
                         onerror="this.outerHTML='<div class=&quot;w-12 h-12 rounded-lg ml-3 flex items-center justify-center font-bold text-white&quot; style=&quot;background:linear-gradient(135deg,#667eea,#764ba2);font-size:18px;&quot;>{{ mb_substr($leader['name'] ?? '?', 0, 1) }}</div>'">
                    <div class="flex-1">
                        <div class="font-bold text-gray-800">{{ $leader['name'] }}</div>
                        <div class="text-sm text-gray-500">{{ $leader['school'] }}</div>
                    </div>
                    <div class="text-left">
                        <div class="text-lg font-bold text-blue-600">{{ number_format($leader['points']) }}</div>
                        <div class="text-xs text-gray-500">نقطة</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-chart-line text-5xl mb-3"></i>
                    <p>لا يوجد طلاب حتى الآن</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Teachers Tab -->
    <div id="section-teachers" class="tab-content" style="{{ $tab !== 'teachers' ? 'display:none' : '' }}">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-chalkboard-teacher text-green-500 ml-2"></i>
                    أفضل المعلمين
                </h2>
                <a href="{{ route('leaderboard.teachers') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                    عرض الكل <i class="fas fa-arrow-left mr-1"></i>
                </a>
            </div>

            @if(count($data['teachers']) >= 3)
            <!-- Top 3 Podium -->
            <div class="bg-gradient-to-b from-gray-50 to-white p-8">
                <div class="flex justify-center items-end gap-4 max-w-2xl mx-auto">
                    @foreach([$data['teachers'][1] ?? null, $data['teachers'][0] ?? null, $data['teachers'][2] ?? null] as $i => $leader)
                        @if($leader)
                        <div class="flex-1 text-center {{ ['', 'transform scale-110', ''][$i] }}">
                            <div class="text-4xl mb-2">{{ ['🥈', '🥇', '🥉'][$i] }}</div>
                            <img src="{{ $leader['avatar'] }}" alt="" 
                                 class="w-{{ $i === 1 ? '20' : '16' }} h-{{ $i === 1 ? '20' : '16' }} rounded-full mx-auto mb-2 border-4 {{ ['border-gray-400', 'border-yellow-400', 'border-orange-400'][$i] }} object-cover" 
                                 onerror="this.outerHTML='<div class=&quot;w-16 h-16 rounded-full mx-auto mb-2 flex items-center justify-center font-bold text-white&quot; style=&quot;background:linear-gradient(135deg,#667eea,#764ba2);font-size:24px;&quot;>{{ mb_substr($leader['name'] ?? '?', 0, 1) }}</div>'">
                            <div class="font-bold text-sm text-gray-800 truncate">{{ $leader['name'] }}</div>
                            <div class="text-green-600 font-semibold text-xs">{{ number_format($leader['points']) }} نقطة</div>
                            <div class="mt-3 rounded-t-lg {{ ['bg-gradient-to-t from-gray-400 to-gray-300 h-16', 'bg-gradient-to-t from-yellow-400 to-yellow-300 h-24', 'bg-gradient-to-t from-orange-400 to-orange-300 h-12'][$i] }}"></div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <div class="divide-y divide-gray-100">
                @forelse(array_slice($data['teachers'], 3, 7) as $leader)
                <div class="flex items-center p-4 hover:bg-gray-50 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center font-bold text-gray-600 ml-3">
                        {{ $leader['rank'] }}
                    </div>
                    <img src="{{ $leader['avatar'] }}" alt="{{ $leader['name'] ?? '' }}"
                         class="w-12 h-12 rounded-lg object-cover ml-3"
                         onerror="this.outerHTML='<div class=&quot;w-12 h-12 rounded-lg ml-3 flex items-center justify-center font-bold text-white&quot; style=&quot;background:linear-gradient(135deg,#667eea,#764ba2);font-size:18px;&quot;>{{ mb_substr($leader['name'] ?? '?', 0, 1) }}</div>'">
                    <div class="flex-1">
                        <div class="font-bold text-gray-800">{{ $leader['name'] }}</div>
                        <div class="text-sm text-gray-500">{{ $leader['students_count'] ?? 0 }} طالب</div>
                    </div>
                    <div class="text-left">
                        <div class="text-lg font-bold text-green-600">{{ number_format($leader['points']) }}</div>
                        <div class="text-xs text-gray-500">نقطة</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-chart-line text-5xl mb-3"></i>
                    <p>لا يوجد معلمون حتى الآن</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Parents Tab -->
    <div id="section-parents" class="tab-content" style="{{ $tab !== 'parents' ? 'display:none' : '' }}">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-users text-purple-500 ml-2"></i>
                    أفضل أولياء الأمور
                </h2>
                <a href="{{ route('leaderboard.parents') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                    عرض الكل <i class="fas fa-arrow-left mr-1"></i>
                </a>
            </div>

            @if(count($data['parents']) >= 3)
            <!-- Top 3 Podium -->
            <div class="bg-gradient-to-b from-gray-50 to-white p-8">
                <div class="flex justify-center items-end gap-4 max-w-2xl mx-auto">
                    @foreach([$data['parents'][1] ?? null, $data['parents'][0] ?? null, $data['parents'][2] ?? null] as $i => $leader)
                        @if($leader)
                        <div class="flex-1 text-center {{ ['', 'transform scale-110', ''][$i] }}">
                            <div class="text-4xl mb-2">{{ ['🥈', '🥇', '🥉'][$i] }}</div>
                            <img src="{{ $leader['avatar'] }}" alt="" 
                                 class="w-{{ $i === 1 ? '20' : '16' }} h-{{ $i === 1 ? '20' : '16' }} rounded-full mx-auto mb-2 border-4 {{ ['border-gray-400', 'border-yellow-400', 'border-orange-400'][$i] }} object-cover" 
                                 onerror="this.outerHTML='<div class=&quot;w-16 h-16 rounded-full mx-auto mb-2 flex items-center justify-center font-bold text-white&quot; style=&quot;background:linear-gradient(135deg,#667eea,#764ba2);font-size:24px;&quot;>{{ mb_substr($leader['name'] ?? '?', 0, 1) }}</div>'">
                            <div class="font-bold text-sm text-gray-800 truncate">{{ $leader['name'] }}</div>
                            <div class="text-purple-600 font-semibold text-xs">{{ number_format($leader['points']) }} نقطة</div>
                            <div class="mt-3 rounded-t-lg {{ ['bg-gradient-to-t from-gray-400 to-gray-300 h-16', 'bg-gradient-to-t from-yellow-400 to-yellow-300 h-24', 'bg-gradient-to-t from-orange-400 to-orange-300 h-12'][$i] }}"></div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <div class="divide-y divide-gray-100">
                @forelse(array_slice($data['parents'], 3, 7) as $leader)
                <div class="flex items-center p-4 hover:bg-gray-50 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center font-bold text-gray-600 ml-3">
                        {{ $leader['rank'] }}
                    </div>
                    <img src="{{ $leader['avatar'] }}" alt="{{ $leader['name'] ?? '' }}"
                         class="w-12 h-12 rounded-lg object-cover ml-3"
                         onerror="this.outerHTML='<div class=&quot;w-12 h-12 rounded-lg ml-3 flex items-center justify-center font-bold text-white&quot; style=&quot;background:linear-gradient(135deg,#667eea,#764ba2);font-size:18px;&quot;>{{ mb_substr($leader['name'] ?? '?', 0, 1) }}</div>'">
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
                    <i class="fas fa-chart-line text-5xl mb-3"></i>
                    <p>لا يوجد أولياء أمور حتى الآن</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Schools Tab -->
    <div id="section-schools" class="tab-content" style="{{ $tab !== 'schools' ? 'display:none' : '' }}">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-school text-orange-500 ml-2"></i>
                    أفضل المدارس
                </h2>
                <a href="{{ route('leaderboard.schools') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                    عرض الكل <i class="fas fa-arrow-left mr-1"></i>
                </a>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($data['schools'] as $school)
                <div class="flex items-center p-4 hover:bg-gray-50 transition-colors">
                    <div class="w-10 h-10 rounded-lg {{ $school['rank'] <= 3 ? 'bg-gradient-to-br from-yellow-400 to-yellow-500' : 'bg-gray-100' }} flex items-center justify-center font-bold {{ $school['rank'] <= 3 ? 'text-white' : 'text-gray-600' }} ml-3">
                        @if($school['rank'] <= 3)
                            {{ ['', '🥇', '🥈', '🥉'][$school['rank']] }}
                        @else
                            {{ $school['rank'] }}
                        @endif
                    </div>
                    <img src="{{ $school['logo'] }}" alt="{{ $school['name'] ?? '' }}"
                         class="w-14 h-14 rounded-xl object-cover ml-3 bg-gray-100 p-1"
                         onerror="this.outerHTML='<div class=&quot;w-14 h-14 rounded-xl ml-3 flex items-center justify-center font-bold text-white&quot; style=&quot;background:linear-gradient(135deg,#10b981,#059669);font-size:24px;&quot;>🏫</div>'">
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
</div>

<style>
    .tab-btn {
        padding: 12px 24px;
        border-radius: 10px;
        border: 2px solid #e5e7eb;
        background: white;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #6b7280;
        white-space: nowrap;
    }

    .tab-btn.active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }

    .tab-btn:hover:not(.active) {
        border-color: #6366f1;
        background: #f9fafb;
    }

    .tab-content {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
function switchTab(tab) {
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url);

    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    event.target.closest('.tab-btn').classList.add('active');

    document.querySelectorAll('.tab-content').forEach(section => {
        section.style.display = 'none';
    });
    
    const section = document.getElementById('section-' + tab);
    if (section) section.style.display = 'block';
}
</script>
@endsection
