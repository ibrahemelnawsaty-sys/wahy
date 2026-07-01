@extends('layouts.school-admin')

@push('styles')
<style>
    /* Wahy dark-mode coverage — هذه الصفحة تستخدم أنماطاً inline مُصلَّبة (بلا كلاسات)
       فنستهدف قيمها الحرفية بمحددات attribute جراحية تعدّل الألوان فقط. */
    html[data-theme="dark"] .sa-students-page [style*="background: white"],
    html[data-theme="dark"] .sa-students-page [style*="background:#fff"],
    html[data-theme="dark"] .sa-students-page [style*="background: #f7fafc"] {
        background: var(--w-card) !important;
    }
    html[data-theme="dark"] .sa-students-page [style*="color: #1a202c"],
    html[data-theme="dark"] .sa-students-page [style*="color: #2d3748"] { color: var(--w-text) !important; }
    html[data-theme="dark"] .sa-students-page [style*="color: #718096"] { color: var(--w-text-muted) !important; }
    /* حقول البحث/الاختيار بحدود مُصلَّبة */
    html[data-theme="dark"] .sa-students-page input[type="text"],
    html[data-theme="dark"] .sa-students-page select {
        background: rgba(255,255,255,0.05) !important;
        color: var(--w-text) !important;
        border-color: var(--w-border) !important;
    }
</style>
@endpush

@section('content')
<div class="sa-students-page" style="padding: 30px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">إدارة الطلاب</h1>
            <p style="color: #718096; font-size: 16px;">جميع طلاب المدرسة وتقدمهم</p>
        </div>
        <button onclick="showAddStudentModal()" style="padding: 15px 30px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; box-shadow: 0 5px 15px rgba(67, 233, 123, 0.3); transition: all 0.3s; display: flex; align-items: center; gap: 10px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(67, 233, 123, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(67, 233, 123, 0.3)'">
            <span style="font-size: 20px;">+</span>
            إضافة طالب جديد
        </button>
    </div>

    <!-- Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 35px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $totalStudents }}</div>
            <div style="opacity: 0.9;">إجمالي الطلاب</div>
        </div>

        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $activeStudents }}</div>
            <div style="opacity: 0.9;">طلاب نشطين</div>
        </div>

        <div style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $totalBadges }}</div>
            <div style="opacity: 0.9;">شارات محققة</div>
        </div>

        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $averageProgress }}%</div>
            <div style="opacity: 0.9;">متوسط التقدم</div>
        </div>
    </div>

    <!-- Students Table -->
    <div style="background: white; border-radius: 20px; padding: 35px; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
        <!-- Search & Filters -->
        <div style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
            <input type="text" placeholder="🔍 ابحث عن طالب..." style="flex: 1; min-width: 250px; padding: 15px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.3s;" onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
            
            <select style="padding: 15px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; min-width: 150px;" onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e2e8f0'">
                <option>جميع الفصول</option>
                @foreach($classrooms as $classroom)
                <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                @endforeach
            </select>
            
            <select style="padding: 15px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; min-width: 120px;" onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e2e8f0'">
                <option>الكل</option>
                <option>نشط</option>
                <option>غير نشط</option>
            </select>
        </div>

        <!-- Students Grid -->
        <div style="display: grid; gap: 15px;">
            @forelse($students as $student)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #f7fafc; border-radius: 12px; border-right: 4px solid #43e97b; transition: all 0.3s;" onmouseover="this.style.background='#edf2f7'; this.style.paddingRight='25px'" onmouseout="this.style.background='#f7fafc'; this.style.paddingRight='20px'">
                
                <div style="display: flex; align-items: center; gap: 20px; flex: 1;">
                    <!-- Avatar -->
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
                        {{ mb_substr($student->name, 0, 1, "UTF-8") }}
                    </div>
                    
                    <!-- Info -->
                    <div style="flex: 1;">
                        <h3 style="font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 5px;">{{ $student->name }}</h3>
                        <p style="font-size: 14px; color: #718096; margin-bottom: 8px;">{{ $student->classroom->name ?? 'غير مسجل' }} • {{ $student->email }}</p>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <span style="font-size: 13px; color: #667eea; font-weight: 600;">⭐ {{ $student->total_points ?? 0 }} نقطة</span>
                            <span style="font-size: 13px; color: #43e97b; font-weight: 600;">🏅 {{ $student->badges_count ?? 0 }} شارة</span>
                            <span style="font-size: 13px; color: #fa709a; font-weight: 600;">🔥 {{ $student->streak_days ?? 0 }} يوم</span>
                            <span style="font-size: 13px; color: #ffd700; font-weight: 600;">👑 {{ $student->crowns_count ?? 0 }} تاج</span>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div style="width: 120px; margin: 0 20px;">
                    <div style="font-size: 13px; color: #718096; margin-bottom: 5px; text-align: center;">التقدم</div>
                    <div style="width: 100%; height: 8px; background: white; border-radius: 10px; overflow: hidden;">
                        <div style="width: {{ $student->progress ?? 0 }}%; height: 100%; background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%); transition: width 0.5s;"></div>
                    </div>
                    <div style="font-size: 12px; font-weight: 700; color: #43e97b; margin-top: 5px; text-align: center;">{{ $student->progress ?? 0 }}%</div>
                </div>

                <!-- QR Code -->
                <div style="width: 60px; height: 60px; background: white; padding: 8px; border-radius: 10px; border: 2px solid #e2e8f0; cursor: pointer;" onclick="showQRCode('{{ $student->qr_code }}')" title="عرض QR Code">
                    <img src="{{ $student->qr_code_url }}" alt="QR" style="width: 100%; height: 100%;">
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 10px; margin-right: 15px;">
                    <a href="{{ route('school-admin.students.show', $student->id) }}" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s; display: inline-block;" onmouseover="this.style.background='#5568d3'" onmouseout="this.style.background='#667eea'">
                        عرض
                    </a>
                    <button onclick="editStudent({{ $student->id }})" style="padding: 10px 20px; background: #4facfe; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#3b9fe8'" onmouseout="this.style.background='#4facfe'">
                        تعديل
                    </button>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 60px;">
                <div style="font-size: 64px; margin-bottom: 20px;">👨‍🎓</div>
                <h3 style="font-size: 22px; font-weight: 600; color: #2d3748; margin-bottom: 10px;">لا يوجد طلاب</h3>
                <p style="color: #718096;">ابدأ بإضافة طلاب جدد للمدرسة</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($students->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 30px;">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function showAddStudentModal() {
    glassNotify.info('قريباً', 'سيتم فتح نافذة إضافة طالب جديد');
}

function editStudent(id) {
    glassNotify.info('قريباً', 'سيتم فتح نافذة تعديل الطالب #' + id);
}

function showQRCode(qrCode) {
    glassNotify.info('QR Code', 'سيتم عرض QR Code للطالب');
}
</script>
@endpush

@endsection

