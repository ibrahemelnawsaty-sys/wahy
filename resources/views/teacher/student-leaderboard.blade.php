@extends('layouts.teacher')

@section('title', 'لوحة صدارة الطلاب')

@push('styles')
<style>
    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-up { animation: slideInUp 0.6s ease-out; }
    .hover-lift { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.12); }
</style>
@endpush

@section('content')

<!-- Header Section -->
<div class="animate-up" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); border-radius: 25px; padding: 35px; margin-bottom: 30px; box-shadow: 0 15px 50px rgba(236, 72, 153, 0.3); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1;">
        <h1 style="font-size: 32px; font-weight: 700; color: white; margin-bottom: 8px;">👑 لوحة صدارة الطلاب</h1>
        <p style="color: rgba(255,255,255,0.95); font-size: 16px;">ترتيب الطلاب حسب النقاط</p>
        
        <div style="display: flex; gap: 15px; margin-top: 20px; flex-wrap: wrap;">
            <a href="{{ route('teacher.leaderboard.students', ['scope' => 'classroom']) }}" 
               style="background: {{ $scope === 'classroom' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}; backdrop-filter: blur(10px); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; border: 2px solid rgba(255,255,255,0.3); transition: all 0.3s;"
               onmouseover="this.style.background='rgba(255,255,255,0.3)'"
               onmouseout="this.style.background='{{ $scope === 'classroom' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}'">
                📚 الفصل
            </a>
            <a href="{{ route('teacher.leaderboard.students', ['scope' => 'school']) }}" 
               style="background: {{ $scope === 'school' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}; backdrop-filter: blur(10px); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; border: 2px solid rgba(255,255,255,0.3); transition: all 0.3s;"
               onmouseover="this.style.background='rgba(255,255,255,0.3)'"
               onmouseout="this.style.background='{{ $scope === 'school' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}'">
                🏫 المدرسة
            </a>
            <a href="{{ route('teacher.leaderboard.students', ['scope' => 'city']) }}" 
               style="background: {{ $scope === 'city' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}; backdrop-filter: blur(10px); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; border: 2px solid rgba(255,255,255,0.3); transition: all 0.3s;"
               onmouseover="this.style.background='rgba(255,255,255,0.3)'"
               onmouseout="this.style.background='{{ $scope === 'city' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}'">
                🏙️ المدينة
            </a>
            <a href="{{ route('teacher.leaderboard.students', ['scope' => 'country']) }}" 
               style="background: {{ $scope === 'country' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}; backdrop-filter: blur(10px); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; border: 2px solid rgba(255,255,255,0.3); transition: all 0.3s;"
               onmouseover="this.style.background='rgba(255,255,255,0.3)'"
               onmouseout="this.style.background='{{ $scope === 'country' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}'">
                🌍 الدولة
            </a>
        </div>
    </div>
</div>

<!-- Leaderboard Table -->
<div class="animate-up" style="background: white; border-radius: 25px; padding: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
    <h2 style="font-size: 24px; font-weight: 700; color: #1a202c; margin-bottom: 25px;">قائمة الطلاب</h2>
    
    @if($leaders->isEmpty())
    <div style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 80px; margin-bottom: 20px; opacity: 0.3;">👑</div>
        <h3 style="font-size: 20px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">لا يوجد طلاب بعد</h3>
    </div>
    @else
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-bottom: 3px solid #e2e8f0;">
                    <th style="padding: 15px; text-align: right; font-weight: 700; color: #1a202c; font-size: 14px;">الترتيب</th>
                    <th style="padding: 15px; text-align: right; font-weight: 700; color: #1a202c; font-size: 14px;">الطالب</th>
                    <th style="padding: 15px; text-align: center; font-weight: 700; color: #1a202c; font-size: 14px;">النقاط</th>
                    @if($scope !== 'classroom')
                    <th style="padding: 15px; text-align: center; font-weight: 700; color: #1a202c; font-size: 14px;">الفصل</th>
                    @endif
                    @if($scope === 'school' || $scope === 'city' || $scope === 'country')
                    <th style="padding: 15px; text-align: center; font-weight: 700; color: #1a202c; font-size: 14px;">المدرسة</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($leaders as $index => $leader)
                @php
                    $rank = $leaders->firstItem() + $index;
                    $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : ''));
                @endphp
                <tr class="hover-lift" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 20px; text-align: center;">
                        @if($medal)
                        <div style="font-size: 32px;">{{ $medal }}</div>
                        @else
                        <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #475569; margin: 0 auto;">
                            {{ $rank }}
                        </div>
                        @endif
                    </td>
                    <td style="padding: 20px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            @if($leader->avatar)
                            <img src="{{ asset('storage/app/public/data/' . $leader->avatar) }}" alt="{{ $leader->name }}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            @else
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white; font-weight: 700; box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);">
                                {{ mb_substr($leader->name, 0, 1) }}
                            </div>
                            @endif
                            <div>
                                <div style="font-size: 16px; font-weight: 700; color: #1a202c; margin-bottom: 4px;">{{ $leader->name }}</div>
                                <div style="font-size: 13px; color: #718096;">{{ $leader->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 20px; text-align: center;">
                        <div style="font-size: 22px; font-weight: 800; color: #ec4899; margin-bottom: 4px;">{{ number_format($leader->total_points ?? 0) }}</div>
                        <div style="font-size: 12px; color: #718096;">نقطة</div>
                    </td>
                    @if($scope !== 'classroom')
                    <td style="padding: 20px; text-align: center;">
                        @php
                            $classroom = $leader->classrooms->where('pivot.status', 'active')->first();
                        @endphp
                        <div style="font-size: 14px; color: #4a5568;">{{ $classroom ? $classroom->name : 'غير محدد' }}</div>
                    </td>
                    @endif
                    @if($scope === 'school' || $scope === 'city' || $scope === 'country')
                    <td style="padding: 20px; text-align: center;">
                        <div style="font-size: 14px; color: #4a5568;">{{ $leader->school->name ?? 'غير محدد' }}</div>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div style="margin-top: 30px; display: flex; justify-content: center;">
        {{ $leaders->links() }}
    </div>
    @endif
</div>

@endsection
