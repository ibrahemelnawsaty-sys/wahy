@extends('layouts.teacher')

@section('title', $classroom->name)

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
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 56px;">📖</div>
            <div>
                <h1 style="font-size: 32px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $classroom->name }}</h1>
                <p style="color: rgba(255,255,255,0.95); font-size: 16px;">{{ $classroom->grade_level }} - {{ $stats['total_students'] }} طالب</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px;">
            <a href="{{ route('teacher.reports.classroom', $classroom->id) }}" style="background: rgba(255,255,255,0.3); color: white; padding: 12px 25px; border-radius: 15px; text-decoration: none; font-weight: 600; transition: all 0.3s;"
               onmouseover="this.style.background='rgba(255,255,255,0.4)'"
               onmouseout="this.style.background='rgba(255,255,255,0.3)'">
                📄 تصدير PDF
            </a>
            <a href="{{ route('teacher.classrooms') }}" style="background: rgba(255,255,255,0.2); color: white; padding: 12px 25px; border-radius: 15px; text-decoration: none; font-weight: 600; transition: all 0.3s;"
               onmouseover="this.style.background='rgba(255,255,255,0.3)'"
               onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                ← رجوع
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="animate-slide" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; margin-bottom: 35px;">
    <div class="hover-lift" style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(66, 153, 225, 0.3);">
        <div style="font-size: 48px; margin-bottom: 12px;">👥</div>
        <div style="font-size: 38px; font-weight: 700; color: white; margin-bottom: 6px;">{{ $stats['total_students'] }}</div>
        <div style="color: rgba(255,255,255,0.95); font-size: 14px; font-weight: 600;">إجمالي الطلاب</div>
    </div>
    
    <div class="hover-lift" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3);">
        <div style="font-size: 48px; margin-bottom: 12px;">✅</div>
        <div style="font-size: 38px; font-weight: 700; color: white; margin-bottom: 6px;">{{ $stats['completed_activities'] }}</div>
        <div style="color: rgba(255,255,255,0.95); font-size: 14px; font-weight: 600;">أنشطة مكتملة</div>
    </div>
    
    <div class="hover-lift" style="background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(237, 137, 54, 0.3);">
        <div style="font-size: 48px; margin-bottom: 12px;">⏳</div>
        <div style="font-size: 38px; font-weight: 700; color: white; margin-bottom: 6px;">{{ $stats['pending_activities'] }}</div>
        <div style="color: rgba(255,255,255,0.95); font-size: 14px; font-weight: 600;">قيد الانتظار</div>
    </div>
    
    <div class="hover-lift" style="background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%); border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(159, 122, 234, 0.3);">
        <div style="font-size: 48px; margin-bottom: 12px;">📊</div>
        <div style="font-size: 38px; font-weight: 700; color: white; margin-bottom: 6px;">{{ number_format($stats['average_performance'], 1) }}%</div>
        <div style="color: rgba(255,255,255,0.95); font-size: 14px; font-weight: 600;">متوسط الأداء</div>
    </div>
</div>

<!-- Students List -->
<div class="animate-slide" style="background: white; border-radius: 25px; padding: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
    <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px; display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 36px;">👥</span> 
        <span>طلاب الفصل</span>
    </h2>
    
    <div style="display: grid; gap: 20px;">
        @forelse($classroom->students as $student)
        <div class="hover-lift" style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border: 2px solid #e2e8f0; border-radius: 18px; padding: 25px; cursor: pointer;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 24px; box-shadow: 0 6px 15px rgba(102, 126, 234, 0.3);">
                        {{ mb_substr($student->name, 0, 1, "UTF-8") }}
                    </div>
                    <div>
                        <h3 style="font-weight: 700; font-size: 18px; color: #2d3748; margin-bottom: 4px;">{{ $student->name }}</h3>
                        <p style="color: #718096; font-size: 14px;">{{ $student->email }}</p>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div style="text-align: center; background: white; padding: 12px 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="font-size: 14px; color: #718096; margin-bottom: 4px;">النقاط</div>
                        <div style="font-size: 20px; font-weight: 700; color: #667eea;">{{ $student->totalPoints() ?? 0 }}</div>
                    </div>
                    <div style="text-align: center; background: white; padding: 12px 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="font-size: 14px; color: #718096; margin-bottom: 4px;">الأنشطة</div>
                        <div style="font-size: 20px; font-weight: 700; color: #48bb78;">{{ \App\Models\ActivitySubmission::where('student_id', $student->id)->where('status', 'completed')->count() }}</div>
                    </div>
                    <a href="{{ route('teacher.students.detail', $student->id) }}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 20px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.3s;"
                       onmouseover="this.style.opacity='0.9'"
                       onmouseout="this.style.opacity='1'">
                        عرض التفاصيل →
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 60px;">
            <div style="font-size: 80px; margin-bottom: 20px; opacity: 0.5;">👥</div>
            <h3 style="font-size: 24px; font-weight: 700; color: #2d3748; margin-bottom: 10px;">لا يوجد طلاب</h3>
            <p style="color: #718096; font-size: 16px;">لم يتم إضافة أي طلاب إلى هذا الفصل بعد</p>
        </div>
        @endforelse
    </div>
</div>

@endsection
