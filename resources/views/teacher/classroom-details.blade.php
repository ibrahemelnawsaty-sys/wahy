@extends('layouts.teacher')

@section('content')
<div style="padding: 30px;">
    <!-- Back Button & Header -->
    <div style="margin-bottom: 30px;">
        <a href="{{ route('teacher.classrooms') }}" style="display: inline-flex; align-items: center; gap: 8px; color: #667eea; text-decoration: none; font-weight: 600; margin-bottom: 20px; transition: all 0.3s;" onmouseover="this.style.gap='12px'" onmouseout="this.style.gap='8px'">
            ← العودة للفصول
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 5px;">{{ $classroom->name }}</h1>
                <p style="color: #718096; font-size: 16px;">{{ $classroom->grade }}</p>
            </div>
            <a href="{{ route('teacher.activities.create') }}?classroom_id={{ $classroom->id }}" style="padding: 15px 30px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 50px; text-decoration: none; font-weight: 600; box-shadow: 0 5px 15px rgba(67, 233, 123, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(67, 233, 123, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(67, 233, 123, 0.3)'">
                + إضافة نشاط جديد
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div>
                    <div style="font-size: 36px; font-weight: 700; margin-bottom: 5px;">{{ $classroom->students_count }}</div>
                    <div style="opacity: 0.9;">إجمالي الطلاب</div>
                </div>
                <div style="font-size: 32px;">👨‍🎓</div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div>
                    <div style="font-size: 36px; font-weight: 700; margin-bottom: 5px;">{{ $activeActivitiesCount }}</div>
                    <div style="opacity: 0.9;">أنشطة نشطة</div>
                </div>
                <div style="font-size: 32px;">📝</div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div>
                    <div style="font-size: 36px; font-weight: 700; margin-bottom: 5px;">{{ $pendingSubmissionsCount }}</div>
                    <div style="opacity: 0.9;">تسليمات معلقة</div>
                </div>
                <div style="font-size: 32px;">⏳</div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div>
                    <div style="font-size: 36px; font-weight: 700; margin-bottom: 5px;">{{ $averageProgress }}%</div>
                    <div style="opacity: 0.9;">متوسط التقدم</div>
                </div>
                <div style="font-size: 32px;">📊</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div style="background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); overflow: hidden;">
        <!-- Tab Headers -->
        <div style="display: flex; border-bottom: 2px solid #f7fafc;">
            <button onclick="showTab('students')" id="tab-students" style="flex: 1; padding: 20px; background: #667eea; color: white; border: none; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                الطلاب
            </button>
            <button onclick="showTab('activities')" id="tab-activities" style="flex: 1; padding: 20px; background: transparent; color: #718096; border: none; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                الأنشطة
            </button>
            <button onclick="showTab('submissions')" id="tab-submissions" style="flex: 1; padding: 20px; background: transparent; color: #718096; border: none; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                التسليمات المعلقة
            </button>
        </div>

        <!-- Tab Contents -->
        <div style="padding: 30px;">
            <!-- Students Tab -->
            <div id="content-students">
                <div style="display: grid; gap: 15px;">
                    @forelse($students as $student)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #f7fafc; border-radius: 12px; border-right: 4px solid #667eea;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: 700;">
                                {{ mb_substr($student->name, 0, 1, "UTF-8") }}
                            </div>
                            <div>
                                <h4 style="font-size: 18px; font-weight: 600; color: #2d3748; margin-bottom: 5px;">{{ $student->name }}</h4>
                                <p style="font-size: 14px; color: #718096;">{{ $student->email }}</p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <div style="text-align: center;">
                                <div style="font-size: 20px; font-weight: 700; color: #667eea;">{{ $student->total_points ?? 0 }}</div>
                                <div style="font-size: 12px; color: #718096;">نقطة</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 20px; font-weight: 700; color: #43e97b;">{{ $student->completed_activities ?? 0 }}</div>
                                <div style="font-size: 12px; color: #718096;">نشاط</div>
                            </div>
                            <a href="{{ route('teacher.students.detail', $student->id) }}" style="padding: 10px 20px; background: #667eea; color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#5568d3'" onmouseout="this.style.background='#667eea'">
                                عرض التقدم
                            </a>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 48px; margin-bottom: 15px;">👨‍🎓</div>
                        <p style="color: #718096;">لا يوجد طلاب في هذا الفصل</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Activities Tab -->
            <div id="content-activities" style="display: none;">
                <div style="display: grid; gap: 20px;">
                    @forelse($activities as $activity)
                    <div style="background: #f7fafc; padding: 25px; border-radius: 12px; border-right: 4px solid #4facfe;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <div style="flex: 1;">
                                <h4 style="font-size: 20px; font-weight: 700; color: #2d3748; margin-bottom: 10px;">{{ $activity->title }}</h4>
                                <p style="color: #718096; font-size: 14px; margin-bottom: 10px;">{{ $activity->description }}</p>
                                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                    <span style="padding: 6px 12px; background: white; border-radius: 20px; font-size: 13px; color: #667eea; font-weight: 600;">
                                        {{ $activity->type }}
                                    </span>
                                    <span style="padding: 6px 12px; background: white; border-radius: 20px; font-size: 13px; color: #43e97b; font-weight: 600;">
                                        {{ $activity->points }} نقطة
                                    </span>
                                    <span style="padding: 6px 12px; background: white; border-radius: 20px; font-size: 13px; color: #fa709a; font-weight: 600;">
                                        {{ $activity->submissions_count ?? 0 }} تسليم
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('teacher.review') }}?activity={{ $activity->id }}" style="padding: 12px 24px; background: #667eea; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#5568d3'" onmouseout="this.style.background='#667eea'">
                                عرض التسليمات
                            </a>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 48px; margin-bottom: 15px;">📝</div>
                        <p style="color: #718096;">لا توجد أنشطة في هذا الفصل</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Submissions Tab -->
            <div id="content-submissions" style="display: none;">
                <div style="display: grid; gap: 15px;">
                    @forelse($pendingSubmissions as $submission)
                    <div style="background: #fffaf0; padding: 20px; border-radius: 12px; border-right: 4px solid #fa709a;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="font-size: 18px; font-weight: 600; color: #2d3748; margin-bottom: 5px;">{{ $submission->student->name }}</h4>
                                <p style="color: #718096; font-size: 14px; margin-bottom: 8px;">{{ $submission->activity->title }}</p>
                                <span style="font-size: 13px; color: #718096;">⏰ {{ $submission->created_at->diffForHumans() }}</span>
                            </div>
                            <a href="{{ route('teacher.review.single', $submission->id) }}" style="padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                مراجعة
                            </a>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 48px; margin-bottom: 15px;">✅</div>
                        <p style="color: #718096;">لا توجد تسليمات معلقة</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all contents
    document.getElementById('content-students').style.display = 'none';
    document.getElementById('content-activities').style.display = 'none';
    document.getElementById('content-submissions').style.display = 'none';
    
    // Reset all tab buttons
    document.getElementById('tab-students').style.background = 'transparent';
    document.getElementById('tab-students').style.color = '#718096';
    document.getElementById('tab-activities').style.background = 'transparent';
    document.getElementById('tab-activities').style.color = '#718096';
    document.getElementById('tab-submissions').style.background = 'transparent';
    document.getElementById('tab-submissions').style.color = '#718096';
    
    // Show selected content
    document.getElementById('content-' + tabName).style.display = 'block';
    
    // Highlight selected tab
    document.getElementById('tab-' + tabName).style.background = '#667eea';
    document.getElementById('tab-' + tabName).style.color = 'white';
}
</script>
@endsection
