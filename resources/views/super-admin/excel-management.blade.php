@extends('layouts.admin')

@section('title', 'إدارة Excel')

@push('styles')
<style>
    .loading {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 20px 40px;
        border-radius: 10px;
        z-index: 1000;
    }
    .loading.active {
        display: block;
    }
</style>
@endpush

@section('content')
<div class="admin-content-header">
    <h1 class="admin-content-title">
        <i class="fas fa-file-excel ml-2"></i>
        إدارة البيانات - Excel
    </h1>
</div>

<div class="admin-content-body">
            <!-- Alerts -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <span class="font-bold">يوجد أخطاء في النموذج:</span>
                    </div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Info Section -->
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-6 py-4 rounded-lg mb-6">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-xl mt-1"></i>
                    <div>
                        <h3 class="font-bold mb-2">معلومات هامة:</h3>
                        <ul class="space-y-1 text-sm">
                            <li>• يمكنك تصدير بيانات الطلاب والأنشطة إلى ملفات Excel لاستخدامها في التحليلات والتقارير</li>
                            <li>• يمكن استيراد الطلاب بشكل جماعي من ملفات Excel أو CSV</li>
                            <li>• تأكد من استخدام القالب الصحيح عند الاستيراد لتجنب الأخطاء</li>
                            <li>• حجم الملف الأقصى للاستيراد: 2 ميجابايت</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Export Section -->
            <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-download text-green-600 ml-2"></i>
                    تصدير البيانات
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Export Students -->
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">تصدير الطلاب</h3>
                                <p class="text-sm text-gray-600">تصدير بيانات الطلاب مع النقاط والعملات والأوسمة</p>
                            </div>
                        </div>

                        <form action="{{ route('admin.export.students') }}" method="GET" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">اختر المدرسة (اختياري)</label>
                                <select name="school_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">جميع المدارس</option>
                                    @foreach(\App\Models\School::all() as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center gap-2">
                                <i class="fas fa-download"></i>
                                <span>تصدير الطلاب</span>
                            </button>
                        </form>

                        <div class="mt-4 text-xs text-gray-600 bg-gray-50 p-3 rounded">
                            <strong>البيانات المصدرة:</strong> الاسم، البريد، المدرسة، الفصول، الهاتف، تاريخ الميلاد، الحالة، النقاط، العملات، الأوسمة، تاريخ التسجيل
                        </div>
                    </div>

                    <!-- Export Activities -->
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tasks text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">تصدير الأنشطة</h3>
                                <p class="text-sm text-gray-600">تصدير بيانات الأنشطة مع إحصائيات التقديمات</p>
                            </div>
                        </div>

                        <form action="{{ route('admin.export.activities') }}" method="GET" class="space-y-4" onsubmit="showLoading()">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">اختر المدرسة (اختياري)</label>
                                <select name="school_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">جميع المدارس</option>
                                    @foreach(\App\Models\School::all() as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center gap-2">
                                <i class="fas fa-download"></i>
                                <span>تصدير الأنشطة</span>
                            </button>
                        </form>

                        <div class="mt-4 text-xs text-gray-600 bg-gray-50 p-3 rounded">
                            <strong>البيانات المصدرة:</strong> العنوان، النوع، المستوى، القيمة، المعلم، النقاط، العملات، عدد التقديمات، المكتملة، قيد المراجعة، الحالة، التاريخ
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Section -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-upload text-purple-600 ml-2"></i>
                    استيراد البيانات
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Import Form -->
                    <div class="lg:col-span-2 border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">استيراد الطلاب</h3>
                        
                        <form id="importStudentsForm" action="{{ route('admin.import.students') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    المدرسة <span class="text-red-500">*</span>
                                </label>
                                <select name="school_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">اختر المدرسة</option>
                                    @foreach(\App\Models\School::all() as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-600 mt-1">سيتم تعيين جميع الطلاب المستوردين لهذه المدرسة</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    ملف Excel/CSV <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="file" 
                                           name="file" 
                                           id="file-input"
                                           accept=".xlsx,.xls,.csv" 
                                           required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                </div>
                                <p class="text-xs text-gray-600 mt-1">الصيغ المدعومة: .xlsx, .xls, .csv (حد أقصى 2 ميجابايت)</p>
                            </div>

                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg text-sm">
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-exclamation-triangle mt-1"></i>
                                    <div>
                                        <strong>تنبيه:</strong>
                                        <ul class="list-disc list-inside mt-1">
                                            <li>استخدم القالب الصحيح لتجنب الأخطاء</li>
                                            <li>سيتم تخطي الطلاب المكررين (البريد الإلكتروني موجود مسبقاً)</li>
                                            <li>كلمة المرور الافتراضية: 123456</li>
                                            <li>جميع الطلاب سيتم تعيينهم كـ "طالب" نشط</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center gap-2">
                                <i class="fas fa-upload"></i>
                                <span>استيراد الطلاب</span>
                            </button>
                        </form>
                    </div>

                    <!-- Template Download -->
                    <div class="border border-gray-200 rounded-lg p-6 bg-gradient-to-br from-purple-50 to-blue-50">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">
                            <i class="fas fa-file-download text-purple-600 ml-2"></i>
                            تنزيل القالب
                        </h3>
                        
                        <p class="text-sm text-gray-700 mb-4">
                            قم بتنزيل قالب Excel الجاهز وقم بتعبئته ببيانات الطلاب
                        </p>

                        <a href="{{ route('admin.download.students-template') }}" 
                           class="block w-full bg-white hover:bg-gray-50 text-purple-700 border-2 border-purple-600 px-6 py-3 rounded-lg font-medium transition text-center mb-4">
                            <i class="fas fa-download ml-2"></i>
                            تنزيل القالب
                        </a>

                        <div class="bg-white rounded-lg p-4 text-xs space-y-2">
                            <h4 class="font-bold text-gray-900 mb-2">الأعمدة المطلوبة:</h4>
                            <div class="space-y-1 text-gray-700">
                                <p><strong>1. الاسم</strong> - اسم الطالب الكامل</p>
                                <p><strong>2. البريد الإلكتروني</strong> - بريد فريد لكل طالب</p>
                                <p><strong>3. كلمة المرور</strong> - اختياري (افتراضي: 123456)</p>
                                <p><strong>4. الهاتف</strong> - رقم الهاتف (اختياري)</p>
                                <p><strong>5. تاريخ الميلاد</strong> - YYYY-MM-DD (اختياري)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="bg-white rounded-xl shadow-sm border p-6 mt-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-chart-bar text-blue-600 ml-2"></i>
                    إحصائيات سريعة
                </h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <div class="text-3xl font-bold text-blue-600 mb-1">
                            {{ \App\Models\User::where('role', 'student')->count() }}
                        </div>
                        <div class="text-sm text-gray-700">إجمالي الطلاب</div>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <div class="text-3xl font-bold text-green-600 mb-1">
                            {{ \App\Models\Activity::count() }}
                        </div>
                        <div class="text-sm text-gray-700">إجمالي الأنشطة</div>
                    </div>

                    <div class="bg-purple-50 rounded-lg p-4 text-center">
                        <div class="text-3xl font-bold text-purple-600 mb-1">
                            {{ \App\Models\School::count() }}
                        </div>
                        <div class="text-sm text-gray-700">المدارس المسجلة</div>
                    </div>

                    <div class="bg-orange-50 rounded-lg p-4 text-center">
                        <div class="text-3xl font-bold text-orange-600 mb-1">
                            {{ \App\Models\ActivitySubmission::count() }}
                        </div>
                        <div class="text-sm text-gray-700">التقديمات الكلية</div>
                    </div>
                </div>
            </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading">
    <i class="fas fa-spinner fa-spin text-2xl ml-3"></i>
    <span>جاري معالجة البيانات...</span>
</div>
@endsection

@push('scripts')
<script>
    function showLoading() {
        document.getElementById('loading-overlay').classList.add('active');
    }

    // نموذج الاستيراد: عرض التحميل فقط إذا كان النموذج صالح
    const importForm = document.getElementById('importStudentsForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            const fileInput = this.querySelector('input[type="file"]');
            const schoolSelect = this.querySelector('select[name="school_id"]');
            
            // التحقق من اختيار ملف ومدرسة
            if (!fileInput.files.length || !schoolSelect.value) {
                // لا تظهر التحميل إذا كان النموذج غير مكتمل
                return;
            }
            
            // عرض التحميل فقط عند إرسال النموذج بنجاح
            showLoading();
        });
    }

    // إخفاء التحميل عند تحميل الصفحة (بعد إعادة التوجيه مع أخطاء)
    window.addEventListener('load', function() {
        document.getElementById('loading-overlay').classList.remove('active');
    });

    // إخفاء التحميل بعد التنزيل (للتصدير)
    window.addEventListener('focus', function() {
        setTimeout(() => {
            document.getElementById('loading-overlay').classList.remove('active');
        }, 1000);
    });
</script>
@endpush
