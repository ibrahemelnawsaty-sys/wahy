@extends('layouts.school-admin')

@section('content')
<div style="padding: 30px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">إدارة المعلمين</h1>
            <p style="color: #718096; font-size: 16px;">جميع معلمي المدرسة وحساباتهم</p>
        </div>
        <button onclick="showAddTeacherModal()" style="padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); transition: all 0.3s; display: flex; align-items: center; gap: 10px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(102, 126, 234, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(102, 126, 234, 0.3)'">
            <span style="font-size: 20px;">+</span>
            إضافة معلم جديد
        </button>
    </div>

    <!-- Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 35px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $totalTeachers }}</div>
                    <div style="opacity: 0.9;">إجمالي المعلمين</div>
                </div>
                <div style="font-size: 36px;">👨‍🏫</div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $activeTeachers }}</div>
                    <div style="opacity: 0.9;">معلمين نشطين</div>
                </div>
                <div style="font-size: 36px;">✅</div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $totalClassrooms }}</div>
                    <div style="opacity: 0.9;">فصول دراسية</div>
                </div>
                <div style="font-size: 36px;">📚</div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $averageRating }}</div>
                    <div style="opacity: 0.9;">متوسط التقييم</div>
                </div>
                <div style="font-size: 36px;">⭐</div>
            </div>
        </div>
    </div>

    <!-- Teachers Table -->
    <div style="background: white; border-radius: 20px; padding: 35px; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
        <!-- Search & Filter -->
        <div style="display: flex; gap: 15px; margin-bottom: 25px;">
            <input type="text" placeholder="🔍 ابحث عن معلم..." style="flex: 1; padding: 15px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.3s;" onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
            
            <select style="padding: 15px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; min-width: 150px; transition: all 0.3s;" onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e2e8f0'">
                <option>جميع المعلمين</option>
                <option>نشط</option>
                <option>غير نشط</option>
            </select>
        </div>

        <!-- Teachers List -->
        <div style="display: grid; gap: 15px;">
            @forelse($teachers as $teacher)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #f7fafc; border-radius: 12px; border-right: 4px solid #667eea; transition: all 0.3s;" onmouseover="this.style.background='#edf2f7'" onmouseout="this.style.background='#f7fafc'">
                
                <div style="display: flex; align-items: center; gap: 20px; flex: 1;">
                    <!-- Avatar -->
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
                        {{ mb_substr($teacher->name, 0, 1, "UTF-8") }}
                    </div>
                    
                    <!-- Info -->
                    <div style="flex: 1;">
                        <h3 style="font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 5px;">{{ $teacher->name }}</h3>
                        <p style="font-size: 14px; color: #718096; margin-bottom: 8px;">{{ $teacher->email }}</p>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <span style="font-size: 13px; color: #667eea; font-weight: 600;">📚 {{ $teacher->classrooms_count ?? 0 }} فصل</span>
                            <span style="font-size: 13px; color: #43e97b; font-weight: 600;">👨‍🎓 {{ $teacher->students_count ?? 0 }} طالب</span>
                            <span style="font-size: 13px; color: #ffd700; font-weight: 600;">⭐ {{ $teacher->rating ?? 0 }}/5</span>
                        </div>
                    </div>
                </div>

                <!-- QR Code -->
                <div style="width: 60px; height: 60px; background: white; padding: 8px; border-radius: 10px; border: 2px solid #e2e8f0; cursor: pointer; transition: all 0.3s;" onclick="showQRCode('{{ $teacher->qr_code }}')" title="عرض QR Code">
                    <img src="{{ $teacher->qr_code_url }}" alt="QR" style="width: 100%; height: 100%;">
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 10px; margin-right: 15px;">
                    <button onclick="viewTeacher({{ $teacher->id }})" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#5568d3'" onmouseout="this.style.background='#667eea'">
                        عرض
                    </button>
                    <button onclick="editTeacher({{ $teacher->id }})" style="padding: 10px 20px; background: #4facfe; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#3b9fe8'" onmouseout="this.style.background='#4facfe'">
                        تعديل
                    </button>
                    @if($teacher->is_active)
                    <button onclick="toggleTeacher({{ $teacher->id }}, false)" style="padding: 10px 20px; background: #fa709a; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#e9608a'" onmouseout="this.style.background='#fa709a'">
                        إيقاف
                    </button>
                    @else
                    <button onclick="toggleTeacher({{ $teacher->id }}, true)" style="padding: 10px 20px; background: #43e97b; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#32d86b'" onmouseout="this.style.background='#43e97b'">
                        تفعيل
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 60px;">
                <div style="font-size: 64px; margin-bottom: 20px;">👨‍🏫</div>
                <h3 style="font-size: 22px; font-weight: 600; color: #2d3748; margin-bottom: 10px;">لا يوجد معلمين</h3>
                <p style="color: #718096;">ابدأ بإضافة معلمين جدد للمدرسة</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($teachers->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 30px; gap: 10px;">
            {{ $teachers->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
console.log('🔍 Script loaded, glassNotify type:', typeof glassNotify);
console.log('🔍 glassNotify object:', glassNotify);

function showAddTeacherModal() {
    console.log('🔍 showAddTeacherModal called');
    glassNotify.info('قريباً', 'سيتم فتح نافذة إضافة معلم جديد');
}

function viewTeacher(id) {
    window.location.href = '/school-admin/teacher/' + id;
}

function editTeacher(id) {
    console.log('🔍 editTeacher called');
    glassNotify.info('قريباً', 'سيتم فتح نافذة تعديل المعلم #' + id);
}

function toggleTeacher(id, activate) {
    console.log('🔍 toggleTeacher called, activate:', activate);
    console.log('🔍 About to call glassNotify.confirm');
    
    glassNotify.confirm(
        activate ? 'تفعيل المعلم' : 'إيقاف المعلم',
        'هل أنت متأكد من ' + (activate ? 'تفعيل' : 'إيقاف') + ' هذا المعلم؟',
        function() {
            console.log('🔍 Confirm callback executed');
            // TODO: Send AJAX request
            glassNotify.toastSuccess((activate ? 'تم التفعيل' : 'تم الإيقاف') + ' بنجاح');
        },
        {
            confirmText: activate ? 'نعم، فعّل' : 'نعم، أوقف',
            cancelText: 'إلغاء',
            confirmType: activate ? 'success' : 'warning'
        }
    );
    
    console.log('🔍 glassNotify.confirm called');
}

function showQRCode(qrCode) {
    console.log('🔍 showQRCode called');
    glassNotify.info('QR Code', 'سيتم عرض QR Code للمعلم');
}
</script>
@endpush

@endsection

