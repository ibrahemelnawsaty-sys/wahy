@extends('layouts.admin')

@section('title', 'النسخ الاحتياطي')

@section('content')
<div class="admin-content-header">
    <h1 class="admin-content-title">
        <i class="fas fa-database ml-2"></i>
        النسخ الاحتياطي والاسترداد
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

            <!-- Actions Section -->
            <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-tools text-blue-600 ml-2"></i>
                    الإجراءات
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Create Full Backup -->
                    <form action="{{ route('admin.backups.create') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="type" value="full">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center gap-2">
                            <i class="fas fa-plus-circle"></i>
                            <span>نسخة كاملة</span>
                        </button>
                    </form>

                    <!-- Create Database Backup -->
                    <form action="{{ route('admin.backups.create') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="type" value="database-only">
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center gap-2">
                            <i class="fas fa-database"></i>
                            <span>قاعدة البيانات فقط</span>
                        </button>
                    </form>

                    <!-- Create Files Backup -->
                    <form action="{{ route('admin.backups.create') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="type" value="files-only">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center gap-2">
                            <i class="fas fa-folder"></i>
                            <span>الملفات فقط</span>
                        </button>
                    </form>

                    <!-- Cleanup Old Backups -->
                    <form action="{{ route('admin.backups.cleanup') }}" method="POST" class="w-full" 
                          onsubmit="return confirm('سيتم حذف النسخ القديمة وفقاً للإعدادات. هل تريد المتابعة؟')">
                        @csrf
                        <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center gap-2">
                            <i class="fas fa-broom"></i>
                            <span>تنظيف النسخ القديمة</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Restore Section -->
            <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-undo text-green-600 ml-2"></i>
                    استرداد نسخة احتياطية
                </h2>
                
                <form action="{{ route('admin.backups.restore') }}" method="POST" enctype="multipart/form-data" 
                      onsubmit="return confirm('⚠️ تحذير: سيتم استبدال جميع البيانات الحالية. هل أنت متأكد؟')">
                    @csrf
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <input type="file" name="backup_file" accept=".zip" required
                                   class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                        </div>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center gap-2">
                            <i class="fas fa-upload"></i>
                            <span>استرداد النسخة</span>
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">
                        <i class="fas fa-info-circle ml-1"></i>
                        قم برفع ملف ZIP للنسخة الاحتياطية. سيتم نسخ احتياطي للبيانات الحالية تلقائياً قبل الاسترداد.
                    </p>
                </form>
            </div>

            <!-- Backups List -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-list text-blue-600 ml-2"></i>
                    النسخ الاحتياطية المتوفرة ({{ count($backups) }})
                </h2>

                @if(count($backups) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        اسم الملف
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        الحجم
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        التاريخ
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        الإجراءات
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($backups as $backup)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <i class="fas fa-file-archive text-blue-600 ml-2"></i>
                                                <span class="text-sm font-medium text-gray-900">{{ $backup['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-600">{{ $backup['size'] }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-600">{{ $backup['date'] }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <!-- Download -->
                                                <a href="{{ route('admin.backups.download', $backup['name']) }}" 
                                                   class="text-blue-600 hover:text-blue-900 flex items-center gap-1"
                                                   title="تحميل">
                                                    <i class="fas fa-download"></i>
                                                    <span>تحميل</span>
                                                </a>

                                                <!-- Delete -->
                                                <form action="{{ route('admin.backups.delete', $backup['name']) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه النسخة؟')"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 flex items-center gap-1"
                                                            title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                        <span>حذف</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-600 text-lg">لا توجد نسخ احتياطية حالياً</p>
                        <p class="text-gray-500 text-sm mt-2">قم بإنشاء نسخة احتياطية أولاً</p>
                    </div>
                @endif
            </div>

            <!-- Info Section -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-6">
                <h3 class="text-lg font-bold text-blue-900 mb-3">
                    <i class="fas fa-info-circle ml-2"></i>
                    معلومات النسخ الاحتياطي
                </h3>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-blue-600 mt-1"></i>
                        <span><strong>نسخة كاملة:</strong> تشمل قاعدة البيانات + جميع ملفات المشروع (باستثناء vendor, node_modules)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-blue-600 mt-1"></i>
                        <span><strong>قاعدة البيانات فقط:</strong> نسخة احتياطية لقاعدة البيانات SQLite فقط</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-blue-600 mt-1"></i>
                        <span><strong>الملفات فقط:</strong> نسخة من الملفات (app, config, resources, public) بدون قاعدة البيانات</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-blue-600 mt-1"></i>
                        <span><strong>سياسة الحفظ:</strong> 7 أيام (كل النسخ) → 16 يوم (نسخة يومية) → 8 أسابيع (نسخة أسبوعية) → 4 أشهر (نسخة شهرية) → سنتين (نسخة سنوية)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-blue-600 mt-1"></i>
                        <span><strong>التنظيف التلقائي:</strong> يحذف النسخ القديمة عند تجاوز الحد الأقصى (5GB)</span>
                    </li>
                </ul>
            </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"]');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
</script>
@endpush
