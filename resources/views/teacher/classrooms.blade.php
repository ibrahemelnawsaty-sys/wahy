@extends('layouts.teacher')

@section('title', 'فصولي الدراسية')

@push('styles')
<style>
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide { animation: slideIn 0.5s ease-out; }
    .hover-lift { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="animate-slide" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 25px; padding: 35px; margin-bottom: 30px; box-shadow: 0 15px 50px rgba(102, 126, 234, 0.3);">
    <div style="display: flex; align-items: center; gap: 15px;">
        <div style="font-size: 56px;">📚</div>
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: white; margin-bottom: 8px;">فصولي الدراسية</h1>
            <p style="color: rgba(255,255,255,0.95); font-size: 16px;">{{ $stats['total_classrooms'] }} فصل دراسي - {{ $stats['total_students'] }} طالب</p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="animate-slide" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 35px;">
    <div class="hover-lift" style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(66, 153, 225, 0.3);">
        <div style="font-size: 56px; margin-bottom: 15px;">🏫</div>
        <div style="font-size: 42px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $stats['total_classrooms'] }}</div>
        <div style="color: rgba(255,255,255,0.95); font-size: 15px; font-weight: 600;">إجمالي الفصول</div>
    </div>
    
    <div class="hover-lift" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3);">
        <div style="font-size: 56px; margin-bottom: 15px;">👥</div>
        <div style="font-size: 42px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $stats['total_students'] }}</div>
        <div style="color: rgba(255,255,255,0.95); font-size: 15px; font-weight: 600;">إجمالي الطلاب</div>
    </div>
    
    <div class="hover-lift" style="background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%); border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(159, 122, 234, 0.3);">
        <div style="font-size: 56px; margin-bottom: 15px;">✅</div>
        <div style="font-size: 42px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $stats['active_classrooms'] }}</div>
        <div style="color: rgba(255,255,255,0.95); font-size: 15px; font-weight: 600;">فصول نشطة</div>
    </div>
</div>

<!-- Classrooms Grid -->
<div class="animate-slide" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px;">
    @forelse($classrooms as $classroom)
    @php
        // استخدام البيانات الحقيقية من Controller
        $progressPercent = $classroom->progress_percent ?? 0;
        $statusColor = $classroom->students_count > 0 ? '#48bb78' : '#cbd5e0';
    @endphp
    
    <div class="hover-lift" style="background: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
        <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: {{ $statusColor }}15; border-radius: 50%;"></div>
        
        <div style="position: relative; z-index: 1;">
            <!-- Header -->
            <div style="display: flex; align-items: start; justify-content: space-between; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                        <div style="width: 55px; height: 55px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 6px 15px rgba(102, 126, 234, 0.3);">📖</div>
                        <div>
                            <h3 style="font-size: 20px; font-weight: 700; color: #1a202c; margin-bottom: 4px;">{{ $classroom->name }}</h3>
                            <p style="color: #718096; font-size: 13px;">{{ $classroom->grade_level }}</p>
                        </div>
                    </div>
                </div>
                <div style="background: {{ $statusColor }}20; color: {{ $statusColor }}; padding: 6px 12px; border-radius: 15px; font-size: 12px; font-weight: 700;">
                    {{ $classroom->students_count > 0 ? '🟢 نشط' : '⚪ غير نشط' }}
                </div>
            </div>
            
            <!-- Stats -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px;">
                <div style="text-align: center; background: #f7fafc; padding: 15px 10px; border-radius: 12px;">
                    <div style="font-size: 28px; margin-bottom: 5px;">👥</div>
                    <div style="font-size: 24px; font-weight: 700; color: #2d3748;">{{ $classroom->students_count }}</div>
                    <div style="font-size: 11px; color: #718096; font-weight: 600;">طالب</div>
                </div>
                <div style="text-align: center; background: #f7fafc; padding: 15px 10px; border-radius: 12px;">
                    <div style="font-size: 28px; margin-bottom: 5px;">📊</div>
                    <div style="font-size: 24px; font-weight: 700; color: #2d3748;">{{ $progressPercent }}%</div>
                    <div style="font-size: 11px; color: #718096; font-weight: 600;">نسبة التفاعل</div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            @if($classroom->students_count > 0)
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 13px; color: #4a5568; font-weight: 600;">الأداء العام</span>
                    <span style="font-size: 13px; color: {{ $statusColor }}; font-weight: 700;">{{ $progressPercent }}%</span>
                </div>
                <div style="background: #e2e8f0; border-radius: 20px; height: 8px; overflow: hidden;">
                    <div style="width: {{ $progressPercent }}%; height: 100%; background: linear-gradient(90deg, {{ $statusColor }}, {{ $statusColor }}cc); border-radius: 20px; transition: width 0.6s ease;"></div>
                </div>
            </div>
            @endif
            
            <!-- Students List -->
            @if($classroom->students->count() > 0)
            <div style="margin-bottom: 20px;">
                <div style="font-size: 13px; color: #4a5568; font-weight: 600; margin-bottom: 10px;">الطلاب:</div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    @foreach($classroom->students->take(5) as $student)
                    <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);" title="{{ $student->name }}">
                        {{ mb_substr($student->name, 0, 1, "UTF-8") }}
                    </div>
                    @endforeach
                    @if($classroom->students->count() > 5)
                    <div style="width: 35px; height: 35px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #718096; font-weight: 700; font-size: 12px;">
                        +{{ $classroom->students->count() - 5 }}
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- Actions -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <a href="{{ route('teacher.classrooms.detail', $classroom->id) }}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px; border-radius: 12px; border: none; font-weight: 600; font-size: 13px; cursor: pointer; text-decoration: none; text-align: center; transition: all 0.3s;"
                   onmouseover="this.style.opacity='0.9'"
                   onmouseout="this.style.opacity='1'">
                    👁️ عرض التفاصيل
                </a>
                <a href="{{ route('teacher.reports.classroom', $classroom->id) }}" style="background: white; color: #667eea; padding: 12px; border-radius: 12px; border: 2px solid #667eea; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.3s; text-decoration: none; text-align: center;"
                        onmouseover="this.style.background='#667eea'; this.style.color='white'"
                        onmouseout="this.style.background='white'; this.style.color='#667eea'">
                    📊 التقارير
                </a>
            </div>
        </div>
    </div>
    @empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
        <div style="font-size: 80px; margin-bottom: 20px; opacity: 0.5;">📚</div>
        <h3 style="font-size: 24px; font-weight: 700; color: #2d3748; margin-bottom: 10px;">لا توجد فصول دراسية</h3>
        <p style="color: #718096; font-size: 16px;">لم يتم تعيين أي فصول دراسية لك بعد</p>
    </div>
    @endforelse
</div>

@endsection
