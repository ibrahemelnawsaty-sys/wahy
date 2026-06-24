@extends('layouts.teacher')

@section('title', 'لوحة صدارة المعلمين')

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
<div class="animate-up" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 25px; padding: 35px; margin-bottom: 30px; box-shadow: 0 15px 50px rgba(245, 158, 11, 0.3); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1;">
        <h1 style="font-size: 32px; font-weight: 700; color: white; margin-bottom: 8px;">🏆 لوحة صدارة المعلمين</h1>
        <p style="color: rgba(255,255,255,0.95); font-size: 16px;">ترتيب المعلمين حسب النقاط</p>
        
        <div style="display: flex; gap: 15px; margin-top: 20px;">
            <a href="{{ route('teacher.leaderboard.teachers', ['scope' => 'local']) }}" 
               style="background: {{ $scope === 'local' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}; backdrop-filter: blur(10px); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; border: 2px solid rgba(255,255,255,0.3); transition: all 0.3s;"
               onmouseover="this.style.background='rgba(255,255,255,0.3)'"
               onmouseout="this.style.background='{{ $scope === 'local' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}'">
                🏫 محلي
            </a>
            <a href="{{ route('teacher.leaderboard.teachers', ['scope' => 'global']) }}" 
               style="background: {{ $scope === 'global' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}; backdrop-filter: blur(10px); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; border: 2px solid rgba(255,255,255,0.3); transition: all 0.3s;"
               onmouseover="this.style.background='rgba(255,255,255,0.3)'"
               onmouseout="this.style.background='{{ $scope === 'global' ? 'rgba(255,255,255,0.3)' : 'rgba(255,255,255,0.2)' }}'">
                🌍 دولي
            </a>
        </div>
    </div>
</div>

<!-- Current Teacher Rank -->
@if($currentTeacher)
<div class="animate-up" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 25px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -30px; right: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1; display: flex; align-items: center; gap: 20px;">
        <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 700; color: white;">
            #{{ $currentTeacherRank ?? '?' }}
        </div>
        <div style="flex: 1;">
            <div style="font-size: 18px; font-weight: 700; color: white; margin-bottom: 5px;">ترتيبك</div>
            <div style="font-size: 24px; font-weight: 800; color: white; margin-bottom: 8px;">{{ number_format($currentTeacher->points) }} نقطة</div>
            <div style="font-size: 14px; color: rgba(255,255,255,0.9);">
                {{ $currentTeacher->students_count }} طالب • {{ $currentTeacher->activities_created }} نشاط • {{ $currentTeacher->questions_approved }} سؤال
            </div>
        </div>
    </div>
</div>
@endif

<!-- Leaderboard Table -->
<div class="animate-up" style="background: white; border-radius: 25px; padding: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
    <h2 style="font-size: 24px; font-weight: 700; color: #1a202c; margin-bottom: 25px;">قائمة المعلمين</h2>
    
    @if($leaders->isEmpty())
    <div style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 80px; margin-bottom: 20px; opacity: 0.3;">🏆</div>
        <h3 style="font-size: 20px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">لا يوجد معلمين بعد</h3>
    </div>
    @else
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-bottom: 3px solid #e2e8f0;">
                    <th style="padding: 15px; text-align: right; font-weight: 700; color: #1a202c; font-size: 14px;">الترتيب</th>
                    <th style="padding: 15px; text-align: right; font-weight: 700; color: #1a202c; font-size: 14px;">المعلم</th>
                    <th style="padding: 15px; text-align: center; font-weight: 700; color: #1a202c; font-size: 14px;">النقاط</th>
                    <th style="padding: 15px; text-align: center; font-weight: 700; color: #1a202c; font-size: 14px;">الطلاب</th>
                    <th style="padding: 15px; text-align: center; font-weight: 700; color: #1a202c; font-size: 14px;">الأنشطة</th>
                    <th style="padding: 15px; text-align: center; font-weight: 700; color: #1a202c; font-size: 14px;">الأسئلة</th>
                    @if($scope === 'global')
                    <th style="padding: 15px; text-align: center; font-weight: 700; color: #1a202c; font-size: 14px;">المدرسة</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($leaders as $index => $leader)
                @php
                    $rank = $leaders->firstItem() + $index;
                    $isCurrent = $leader->teacher_id === auth()->id();
                    $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : ''));
                @endphp
                <tr class="hover-lift" style="border-bottom: 1px solid #e2e8f0; {{ $isCurrent ? 'background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);' : '' }}">
                    <td style="padding: 20px; text-align: center;">
                        @if($medal)
                        <div style="font-size: 32px;">{{ $medal }}</div>
                        @else
                        <div style="width: 40px; height: 40px; background: {{ $isCurrent ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : '#f1f5f9' }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: {{ $isCurrent ? 'white' : '#475569' }}; margin: 0 auto;">
                            {{ $rank }}
                        </div>
                        @endif
                    </td>
                    <td style="padding: 20px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white; font-weight: 700; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">
                                {{ mb_substr($leader->teacher->name ?? 'م', 0, 1) }}
                            </div>
                            <div>
                                <div style="font-size: 16px; font-weight: 700; color: #1a202c; margin-bottom: 4px;">{{ $leader->teacher->name ?? 'غير معروف' }}</div>
                                @if($scope === 'global' && $leader->teacher->school)
                                <div style="font-size: 13px; color: #718096;">{{ $leader->teacher->school->name }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="padding: 20px; text-align: center;">
                        <div style="font-size: 20px; font-weight: 800; color: #f59e0b; margin-bottom: 4px;">{{ number_format($leader->points) }}</div>
                        <div style="font-size: 12px; color: #718096;">{{ number_format($leader->students_total_points) }} من طلابه</div>
                    </td>
                    <td style="padding: 20px; text-align: center;">
                        <div style="font-size: 18px; font-weight: 700; color: #1a202c;">{{ $leader->students_count }}</div>
                    </td>
                    <td style="padding: 20px; text-align: center;">
                        <div style="font-size: 18px; font-weight: 700; color: #1a202c;">{{ $leader->activities_created }}</div>
                    </td>
                    <td style="padding: 20px; text-align: center;">
                        <div style="font-size: 18px; font-weight: 700; color: #1a202c;">{{ $leader->questions_approved }}</div>
                    </td>
                    @if($scope === 'global')
                    <td style="padding: 20px; text-align: center;">
                        <div style="font-size: 14px; color: #4a5568;">{{ $leader->teacher->school->name ?? 'غير محدد' }}</div>
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
