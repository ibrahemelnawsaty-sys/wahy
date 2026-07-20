@extends('layouts.teacher')

@section('title', 'مركز التدريس - المعلم')

@push('styles')
<style>
    @keyframes slideInLeft {
        from { opacity: 0; transform: translateX(30px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    .animate-slide { animation: slideInLeft 0.5s ease-out; }
    .hover-lift { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.15); }

    /* P2-D: تحسين الأداء على الجوال */
    @media (max-width: 768px) {
        .animate-slide { animation: none !important; }
        .hover-lift:hover { transform: none !important; box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important; }
        [style*="animation: bounce"] { animation: none !important; }
        [style*="box-shadow: 0 15px"],
        [style*="box-shadow: 0 10px 30px"] { box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important; }
    }
</style>
@endpush

@section('content')

<!-- Welcome Banner -->
<div class="animate-slide" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 25px; padding: 35px; margin-bottom: 30px; box-shadow: 0 15px 50px rgba(102, 126, 234, 0.3); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
            <div style="font-size: 56px; animation: bounce 2s infinite;">👩‍🏫</div>
            <div>
                <h1 style="font-size: 32px; font-weight: 700; color: white; margin-bottom: 8px;">مرحباً أستاذ {{ auth()->user()->name }}</h1>
                <p style="color: rgba(255,255,255,0.95); font-size: 16px;">لديك {{ $pendingSubmissions->count() }} أنشطة تحتاج مراجعة اليوم</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="animate-slide" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 35px;">
    <!-- Total Students -->
    <div class="hover-lift" style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(66, 153, 225, 0.3); position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; left: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        <div style="font-size: 56px; margin-bottom: 15px; position: relative;">👥</div>
        <div style="font-size: 42px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $totalStudents }}</div>
        <div style="color: rgba(255,255,255,0.95); font-size: 15px; font-weight: 600;">إجمالي طلابي</div>
    </div>
    
    <!-- Classrooms -->
    <div class="hover-lift" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3); position: relative; overflow: hidden;">
        <div style="position: absolute; bottom: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        <div style="font-size: 56px; margin-bottom: 15px; position: relative;">📚</div>
        <div style="font-size: 42px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $classrooms->count() }}</div>
        <div style="color: rgba(255,255,255,0.95); font-size: 15px; font-weight: 600;">الفصول الدراسية</div>
    </div>
    
    <!-- Pending Reviews -->
    <div class="hover-lift" style="background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(237, 137, 54, 0.3); position: relative; overflow: hidden; cursor: pointer;"
         onclick="document.querySelector('#pending-section').scrollIntoView({behavior: 'smooth'})">
        <div style="position: absolute; top: -15px; right: -15px; width: 80px; height: 80px; background: rgba(255,255,255,0.15); border-radius: 50%; animation: bounce 3s infinite;"></div>
        <div style="font-size: 56px; margin-bottom: 15px; position: relative;">⏳</div>
        <div style="font-size: 42px; font-weight: 700; color: white; margin-bottom: 8px;">{{ $pendingSubmissions->count() }}</div>
        <div style="color: rgba(255,255,255,0.95); font-size: 15px; font-weight: 600;">تحتاج مراجعة</div>
    </div>
    
    <!-- Teacher Points -->
    @php
        try {
            $teacherPoints = \App\Models\TeacherPoint::where('teacher_id', auth()->id())->first();
            if (!$teacherPoints) {
                // إنشاء سجل جديد إذا لم يكن موجوداً
                \App\Models\TeacherPoint::updateTeacherPoints(auth()->id());
                $teacherPoints = \App\Models\TeacherPoint::where('teacher_id', auth()->id())->first();
            }
            $teacherPointsValue = $teacherPoints ? $teacherPoints->points : 0;
        } catch (\Exception $e) {
            $teacherPointsValue = 0;
        }
    @endphp
    <div class="hover-lift" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3); position: relative; overflow: hidden; cursor: pointer;"
         onclick="window.location.href='{{ route('teacher.leaderboard.teachers') }}'">
        <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        <div style="font-size: 56px; margin-bottom: 15px; position: relative;">⭐</div>
        <div style="font-size: 42px; font-weight: 700; color: white; margin-bottom: 8px;">{{ number_format($teacherPointsValue) }}</div>
        <div style="color: rgba(255,255,255,0.95); font-size: 15px; font-weight: 600;">نقاطي</div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="animate-slide" style="background: white; border-radius: 25px; padding: 35px; margin-bottom: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
    <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 36px;">⚡</span> 
        <span>إجراءات سريعة</span>
    </h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <!-- بنك الأنشطة -->
        <a href="{{ route('teacher.activity-bank.index') }}" class="hover-lift" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 18px; padding: 25px; text-align: center; text-decoration: none; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3); transition: all 0.3s; cursor: pointer;"
           onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 32px rgba(102, 126, 234, 0.4)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 24px rgba(102, 126, 234, 0.3)'">
            <div style="font-size: 48px; margin-bottom: 12px;">📚</div>
            <div style="color: white; font-size: 18px; font-weight: 700; margin-bottom: 6px;">بنك الأنشطة</div>
            <div style="color: rgba(255,255,255,0.9); font-size: 13px;">إضافة نشاط إبداعي</div>
        </a>
        
        <!-- بنك الأسئلة -->
        <a href="{{ route('teacher.question-bank.index') }}" class="hover-lift" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 18px; padding: 25px; text-align: center; text-decoration: none; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3); transition: all 0.3s; cursor: pointer;"
           onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 32px rgba(16, 185, 129, 0.4)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 24px rgba(16, 185, 129, 0.3)'">
            <div style="font-size: 48px; margin-bottom: 12px;">❓</div>
            <div style="color: white; font-size: 18px; font-weight: 700; margin-bottom: 6px;">بنك الأسئلة</div>
            <div style="color: rgba(255,255,255,0.9); font-size: 13px;">إضافة سؤال جديد</div>
        </a>
        
        <!-- لوحة صدارة المعلمين -->
        <a href="{{ route('teacher.leaderboard.teachers', ['scope' => 'local']) }}" class="hover-lift" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 18px; padding: 25px; text-align: center; text-decoration: none; box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3); transition: all 0.3s; cursor: pointer;"
           onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 32px rgba(245, 158, 11, 0.4)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 24px rgba(245, 158, 11, 0.3)'">
            <div style="font-size: 48px; margin-bottom: 12px;">🏆</div>
            <div style="color: white; font-size: 18px; font-weight: 700; margin-bottom: 6px;">صدارة المعلمين</div>
            <div style="color: rgba(255,255,255,0.9); font-size: 13px;">محلي ودولي</div>
        </a>
        
        <!-- لوحة صدارة الطلاب -->
        <a href="{{ route('teacher.leaderboard.students', ['scope' => 'classroom']) }}" class="hover-lift" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); border-radius: 18px; padding: 25px; text-align: center; text-decoration: none; box-shadow: 0 8px 24px rgba(236, 72, 153, 0.3); transition: all 0.3s; cursor: pointer;"
           onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 12px 32px rgba(236, 72, 153, 0.4)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 24px rgba(236, 72, 153, 0.3)'">
            <div style="font-size: 48px; margin-bottom: 12px;">👑</div>
            <div style="color: white; font-size: 18px; font-weight: 700; margin-bottom: 6px;">صدارة الطلاب</div>
            <div style="color: rgba(255,255,255,0.9); font-size: 13px;">فصل - مدرسة - مدينة</div>
        </a>
    </div>
</div>

<!-- Classrooms Section -->
<div class="animate-slide" style="background: white; border-radius: 25px; padding: 35px; margin-bottom: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 36px;">📚</span> 
            <span>فصولي الدراسية</span>
        </h2>
        <a href="{{ route('teacher.classrooms') }}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 25px; border-radius: 25px; border: none; font-weight: 700; font-size: 14px; cursor: pointer; box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3); transition: all 0.3s; text-decoration: none;"
                onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 30px rgba(102, 126, 234, 0.4)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.3)'">
            📚 عرض كل الفصول
        </a>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">
        @foreach($classrooms as $classroom)
        @php
            // استخدام البيانات الحقيقية من Controller
            $progressPercent = $classroom->engagement_percent ?? 0;
            $engagementColor = $progressPercent >= 80 ? '#48bb78' : ($progressPercent >= 60 ? '#ecc94b' : '#ed8936');
        @endphp
        
        <div class="hover-lift" style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 20px; padding: 25px; border: 3px solid #e2e8f0; cursor: pointer; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: {{ $engagementColor }}15; border-radius: 50%;"></div>
            
            <div style="position: relative; z-index: 1;">
                <div style="display: flex; align-items: start; justify-content: space-between; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 6px 15px rgba(102, 126, 234, 0.3);">📖</div>
                            <div>
                                <h3 style="font-size: 20px; font-weight: 700; color: #1a202c;">{{ $classroom->name }}</h3>
                                <p style="color: #718096; font-size: 13px; margin-top: 2px;">{{ $classroom->grade_level ?? 'غير محدد' }}</p>
                            </div>
                        </div>
                    </div>
                    <div style="background: {{ $engagementColor }}20; color: {{ $engagementColor }}; padding: 6px 12px; border-radius: 15px; font-size: 12px; font-weight: 700;">
                        🟢 نشط
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
                    <div style="text-align: center; background: white; padding: 15px 10px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="font-size: 28px; margin-bottom: 5px;">👥</div>
                        <div style="font-size: 24px; font-weight: 700; color: #2d3748;">{{ $classroom->students_count }}</div>
                        <div style="font-size: 11px; color: #718096; font-weight: 600;">طالب</div>
                    </div>
                    <div style="text-align: center; background: white; padding: 15px 10px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="font-size: 28px; margin-bottom: 5px;">📝</div>
                        <div style="font-size: 24px; font-weight: 700; color: #2d3748;">{{ $classroom->total_activities ?? 0 }}</div>
                        <div style="font-size: 11px; color: #718096; font-weight: 600;">نشاط</div>
                    </div>
                    <div style="text-align: center; background: white; padding: 15px 10px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="font-size: 28px; margin-bottom: 5px;">⏳</div>
                        <div style="font-size: 24px; font-weight: 700; color: #ed8936;">{{ $classroom->pending_count ?? 0 }}</div>
                        <div style="font-size: 11px; color: #718096; font-weight: 600;">معلق</div>
                    </div>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 13px; color: #4a5568; font-weight: 600;">نسبة التفاعل</span>
                        <span style="font-size: 13px; color: {{ $engagementColor }}; font-weight: 700;">{{ $progressPercent }}%</span>
                    </div>
                    <div style="background: #e2e8f0; border-radius: 20px; height: 10px; overflow: hidden;">
                        <div style="width: {{ $progressPercent }}%; height: 100%; background: linear-gradient(90deg, {{ $engagementColor }}, {{ $engagementColor }}cc); border-radius: 20px; transition: width 0.6s ease;"></div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <a href="{{ route('teacher.classrooms.detail', $classroom->id) }}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px; border-radius: 12px; border: none; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.3s; text-decoration: none; text-align: center;"
                            onmouseover="this.style.opacity='0.9'"
                            onmouseout="this.style.opacity='1'">
                        👁️ عرض التفاصيل
                    </a>
                    <a href="{{ route('teacher.activities.create') }}" style="background: white; color: #667eea; padding: 10px; border-radius: 12px; border: 2px solid #667eea; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.3s; text-decoration: none; text-align: center;"
                            onmouseover="this.style.background='#667eea'; this.style.color='white'"
                            onmouseout="this.style.background='white'; this.style.color='#667eea'">
                        ➕ نشاط جديد
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Pending Submissions -->
@if($pendingSubmissions->count() > 0)
<div id="pending-section" class="animate-slide" style="background: white; border-radius: 25px; padding: 35px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
    <h2 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px; display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 36px;">⏳</span> 
        <span>أنشطة تحتاج مراجعة</span>
        <span style="background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); color: white; padding: 6px 15px; border-radius: 20px; font-size: 14px; margin-right: auto;">{{ $pendingSubmissions->count() }} نشاط</span>
    </h2>
    
    <div style="display: grid; gap: 20px;">
        @foreach($pendingSubmissions as $submission)
        <div class="hover-lift" style="background: linear-gradient(135deg, #fff7ed 0%, #fffaf0 100%); border: 3px solid #fed7aa; border-radius: 18px; padding: 25px; cursor: pointer; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -30px; right: -30px; width: 100px; height: 100px; background: rgba(237, 137, 54, 0.1); border-radius: 50%;"></div>
            
            <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: start; gap: 25px;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 6px 15px rgba(237, 137, 54, 0.3);">
                            @php
                                echo match($submission->activity->type) {
                                    'quiz' => '✏️',
                                    'file_upload' => '📤',
                                    'team_activity' => '👥',
                                    'practical' => '🎯',
                                    default => '📝'
                                };
                            @endphp
                        </div>
                        <div>
                            <h3 style="font-weight: 700; font-size: 19px; color: #2d3748; margin-bottom: 5px;">{{ $submission->activity->title }}</h3>
                            <p style="color: #718096; font-size: 14px;">📖 {{ $submission->activity->lesson->title ?? 'بدون درس' }}</p>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 15px; border-radius: 12px; margin-bottom: 15px; border-right: 4px solid #ed8936;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <div style="width: 35px; height: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px;">👤</div>
                            <div>
                                <div style="font-weight: 700; color: #2d3748; font-size: 15px;">{{ $submission->student->name ?? 'طالب' }}</div>
                                <div style="color: #718096; font-size: 12px;">تم الرفع: {{ $submission->created_at ? $submission->created_at->diffForHumans() : 'غير محدد' }}</div>
                            </div>
                        </div>
                        
                        @if($submission->submission_text || $submission->file_path)
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                            @if($submission->submission_text)
                            <div style="color: #4a5568; font-size: 14px; line-height: 1.6;">{{ \Illuminate\Support\Str::limit($submission->submission_text, 100) }}</div>
                            @endif
                            @if($submission->file_path)
                            <div style="margin-top: 8px; display: inline-flex; align-items: center; gap: 6px; background: #f7fafc; padding: 6px 12px; border-radius: 8px; font-size: 13px; color: #4a5568;">
                                📎 ملف مرفق
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <span style="background: #ed893620; color: #ed8936; padding: 6px 12px; border-radius: 15px; font-size: 12px; font-weight: 700;">⏳ قيد الانتظار</span>
                        <span style="color: #718096; font-size: 13px;">⭐ النقاط المحتملة: {{ $submission->activity->points }}</span>
                    </div>
                </div>
                
                {{-- الأزرار الثلاثة كانت <button> بلا أيّ إجراء (لا onclick/form/href) فلا تفعل شيئاً.
                     المراجعة الفعليّة (قبول/رفض + الدرجة) تتمّ في صفحة teacher.review.single عبر submitReview
                     الذي يتطلّب درجة يدويّة — فلا قبول/رفض فوريّ. نجعل الثلاثة روابط لصفحة المراجعة. --}}
                <div style="display: flex; flex-direction: column; gap: 10px; min-width: 140px;">
                    <a href="{{ route('teacher.review.single', $submission->id) }}" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 12px 20px; border-radius: 15px; border: none; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.3s; box-shadow: 0 6px 15px rgba(72, 187, 120, 0.3); display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none;"
                            onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px rgba(72, 187, 120, 0.4)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 15px rgba(72, 187, 120, 0.3)'">
                        ✓ قبول
                    </a>
                    <a href="{{ route('teacher.review.single', $submission->id) }}" style="background: white; color: #f56565; padding: 12px 20px; border-radius: 15px; border: 2px solid #f56565; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none;"
                            onmouseover="this.style.background='#f56565'; this.style.color='white'"
                            onmouseout="this.style.background='white'; this.style.color='#f56565'">
                        ✕ رفض
                    </a>
                    <a href="{{ route('teacher.review.single', $submission->id) }}" style="background: #f7fafc; color: #4a5568; padding: 12px 20px; border-radius: 15px; border: 2px solid #e2e8f0; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none;"
                            onmouseover="this.style.borderColor='#cbd5e0'"
                            onmouseout="this.style.borderColor='#e2e8f0'">
                        👁️ عرض
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
