@extends('layouts.super-admin')

@section('content')
<div style="padding: 30px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 10px;">إدارة المدارس</h1>
            <p style="color: #718096; font-size: 16px;">جميع المدارس المسجلة في المنصة</p>
        </div>
        <button onclick="showAddSchoolModal()" style="padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); transition: all 0.3s; display: flex; align-items: center; gap: 10px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(102, 126, 234, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(102, 126, 234, 0.3)'">
            <span style="font-size: 20px;">+</span>
            إضافة مدرسة جديدة
        </button>
    </div>

    <!-- Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $totalSchools }}</div>
                    <div style="opacity: 0.9;">إجمالي المدارس</div>
                </div>
                <div style="font-size: 36px;">🏫</div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $activeSchools }}</div>
                    <div style="opacity: 0.9;">مدارس نشطة</div>
                </div>
                <div style="font-size: 36px;">✅</div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $totalStudents }}</div>
                    <div style="opacity: 0.9;">إجمالي الطلاب</div>
                </div>
                <div style="font-size: 36px;">👨‍🎓</div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 25px; border-radius: 15px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <div style="font-size: 38px; font-weight: 700; margin-bottom: 8px;">{{ $pendingRequests }}</div>
                    <div style="opacity: 0.9;">طلبات معلقة</div>
                </div>
                <div style="font-size: 36px;">⏳</div>
            </div>
        </div>
    </div>

    <!-- Schools Grid -->
    <div style="background: white; border-radius: 20px; padding: 35px; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
        <!-- Search & Filter -->
        <div style="display: flex; gap: 15px; margin-bottom: 25px;">
            <input type="text" placeholder="🔍 ابحث عن مدرسة..." style="flex: 1; padding: 15px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.3s;" onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
            
            <select style="padding: 15px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; min-width: 150px;" onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e2e8f0'">
                <option>جميع المدارس</option>
                <option>نشطة</option>
                <option>معلقة</option>
                <option>غير نشطة</option>
            </select>
        </div>

        <!-- Schools List -->
        <div style="display: grid; gap: 20px;">
            @forelse($schools as $school)
            <div style="background: #f7fafc; padding: 30px; border-radius: 15px; border-right: 5px solid {{ $school->is_active ? '#43e97b' : '#cbd5e0' }}; transition: all 0.3s;" onmouseover="this.style.background='#edf2f7'; this.style.paddingRight='35px'" onmouseout="this.style.background='#f7fafc'; this.style.paddingRight='30px'">
                
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                    <!-- School Info -->
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                🏫
                            </div>
                            <div>
                                <h3 style="font-size: 22px; font-weight: 700; color: #2d3748; margin-bottom: 5px;">{{ $school->name }}</h3>
                                <p style="font-size: 14px; color: #718096;">{{ $school->city }} • {{ $school->email }}</p>
                            </div>
                        </div>
                        
                        <!-- Stats -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px;">
                            <div style="background: white; padding: 15px; border-radius: 10px; text-align: center;">
                                <div style="font-size: 24px; font-weight: 700; color: #667eea; margin-bottom: 5px;">{{ $school->teachers_count }}</div>
                                <div style="font-size: 13px; color: #718096;">معلم</div>
                            </div>
                            <div style="background: white; padding: 15px; border-radius: 10px; text-align: center;">
                                <div style="font-size: 24px; font-weight: 700; color: #43e97b; margin-bottom: 5px;">{{ $school->students_count }}</div>
                                <div style="font-size: 13px; color: #718096;">طالب</div>
                            </div>
                            <div style="background: white; padding: 15px; border-radius: 10px; text-align: center;">
                                <div style="font-size: 24px; font-weight: 700; color: #4facfe; margin-bottom: 5px;">{{ $school->classrooms_count }}</div>
                                <div style="font-size: 13px; color: #718096;">فصل</div>
                            </div>
                            <div style="background: white; padding: 15px; border-radius: 10px; text-align: center;">
                                <div style="font-size: 24px; font-weight: 700; color: #ffd700; margin-bottom: 5px;">{{ $school->activities_count }}</div>
                                <div style="font-size: 13px; color: #718096;">نشاط</div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div style="width: 90px; height: 90px; background: white; padding: 10px; border-radius: 12px; border: 3px solid #e2e8f0; margin-right: 20px; cursor: pointer;" onclick="showQRCode('{{ $school->qr_code }}')" title="عرض QR Code">
                        <img src="{{ $school->qr_code_url }}" alt="QR" style="width: 100%; height: 100%;">
                    </div>
                </div>

                <!-- School Admin Info -->
                <div style="background: white; padding: 18px; border-radius: 10px; margin-bottom: 20px;">
                    <div style="font-size: 13px; color: #718096; margin-bottom: 8px;">مدير المدرسة</div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: 700;">
                            {{ mb_substr($school->admin->name ?? 'غير محدد', 0, 1) }}
                        </div>
                        <div>
                            <div style="font-size: 16px; font-weight: 600; color: #2d3748;">{{ $school->admin->name ?? 'غير محدد' }}</div>
                            <div style="font-size: 13px; color: #718096;">{{ $school->admin->email ?? '' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('super-admin.school.view', $school->id) }}" style="padding: 12px 25px; background: #667eea; color: white; border-radius: 10px; text-decoration: none; font-weight: 600; transition: all 0.3s; display: inline-block;" onmouseover="this.style.background='#5568d3'" onmouseout="this.style.background='#667eea'">
                        عرض التفاصيل
                    </a>
                    <button onclick="editSchool({{ $school->id }})" style="padding: 12px 25px; background: #4facfe; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#3b9fe8'" onmouseout="this.style.background='#4facfe'">
                        تعديل
                    </button>
                    @if($school->is_active)
                    <button onclick="toggleSchool({{ $school->id }}, false)" style="padding: 12px 25px; background: #fa709a; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#e9608a'" onmouseout="this.style.background='#fa709a'">
                        إيقاف
                    </button>
                    @else
                    <button onclick="toggleSchool({{ $school->id }}, true)" style="padding: 12px 25px; background: #43e97b; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#32d86b'" onmouseout="this.style.background='#43e97b'">
                        تفعيل
                    </button>
                    @endif
                    <button onclick="viewReports({{ $school->id }})" style="padding: 12px 25px; background: white; color: #667eea; border: 2px solid #667eea; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#667eea'; this.style.color='white'" onmouseout="this.style.background='white'; this.style.color='#667eea'">
                        التقارير
                    </button>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 60px;">
                <div style="font-size: 64px; margin-bottom: 20px;">🏫</div>
                <h3 style="font-size: 22px; font-weight: 600; color: #2d3748; margin-bottom: 10px;">لا توجد مدارس</h3>
                <p style="color: #718096;">ابدأ بإضافة مدارس جديدة للمنصة</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($schools->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 30px;">
            {{ $schools->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function showAddSchoolModal() {
    showInfo('سيتم فتح نافذة إضافة مدرسة جديدة', 'إضافة مدرسة');
}

function editSchool(id) {
    showInfo('سيتم فتح نافذة تعديل المدرسة #' + id, 'تعديل مدرسة');
}

function toggleSchool(id, activate) {
    showConfirm(
        'هل أنت متأكد من ' + (activate ? 'تفعيل' : 'إيقاف') + ' هذه المدرسة؟',
        () => {
            showSuccess((activate ? 'تم التفعيل' : 'تم الإيقاف') + ' بنجاح', 'نجاح!');
        },
        'تأكيد العملية',
        'نعم، متأكد',
        'إلغاء'
    );
}

function viewReports(id) {
    window.location.href = '/super-admin/school/' + id + '/reports';
}

function showQRCode(qrCode) {
    showInfo('سيتم عرض QR Code للمدرسة', 'عرض QR Code');
}
</script>
@endsection
