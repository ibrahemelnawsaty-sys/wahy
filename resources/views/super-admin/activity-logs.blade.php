@extends('layouts.admin')

@section('title', 'سجل الأنشطة')

@push('styles')
<style>
    .badge-created { @apply bg-green-100 text-green-800; }
    .badge-updated { @apply bg-blue-100 text-blue-800; }
    .badge-deleted { @apply bg-red-100 text-red-800; }
</style>
@endpush

@section('content')
<div class="admin-content-header">
    <h1 class="admin-content-title">
        <i class="fas fa-history ml-2"></i>
        سجل الأنشطة والتغييرات
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

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-filter text-blue-600 ml-2"></i>
                    تصفية السجلات
                </h2>

                <form method="GET" action="{{ route('admin.activity-logs') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Model Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">نوع السجل</label>
                        <select name="model" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">جميع الأنواع</option>
                            @foreach($models as $model)
                                <option value="{{ $model['value'] }}" {{ request('model') == $model['value'] ? 'selected' : '' }}>
                                    {{ $model['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Event Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">نوع الإجراء</label>
                        <select name="event" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">جميع الإجراءات</option>
                            <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>إنشاء</option>
                            <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>تحديث</option>
                            <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>حذف</option>
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المستخدم</label>
                        <select name="user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">جميع المستخدمين</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Submit -->
                    <div class="col-span-full flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-search ml-2"></i>
                            بحث
                        </button>
                        <a href="{{ route('admin.activity-logs') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-redo ml-2"></i>
                            إعادة تعيين
                        </a>
                    </div>
                </form>
            </div>

            <!-- Clean Old Logs -->
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-6 flex items-center justify-between">
                <div class="flex items-center gap-2 text-orange-800">
                    <i class="fas fa-info-circle"></i>
                    <span>يمكنك حذف السجلات القديمة لتوفير مساحة التخزين</span>
                </div>
                <form method="POST" action="{{ route('admin.activity-logs.clean') }}" class="flex items-center gap-2"
                      onsubmit="return confirm('هل تريد حذف جميع السجلات الأقدم من 30 يوم؟')">
                    @csrf
                    <input type="hidden" name="days" value="30">
                    <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition">
                        <i class="fas fa-trash ml-2"></i>
                        حذف السجلات الأقدم من 30 يوم
                    </button>
                </form>
            </div>

            <!-- Activity Logs Table -->
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-bold text-gray-900">
                        السجلات ({{ $logs->total() }})
                    </h2>
                </div>

                @if($logs->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المستخدم</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراء</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">النوع</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التفاصيل</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($logs as $log)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $log->created_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($log->causer)
                                                <div class="text-sm">
                                                    <div class="font-medium text-gray-900">{{ $log->causer->name }}</div>
                                                    <div class="text-gray-500">{{ $log->causer->role }}</div>
                                                </div>
                                            @else
                                                <span class="text-gray-400">نظام</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $eventNames = [
                                                    'created' => 'إنشاء',
                                                    'updated' => 'تحديث',
                                                    'deleted' => 'حذف'
                                                ];
                                            @endphp
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full badge-{{ $log->event }}">
                                                {{ $eventNames[$log->event] ?? $log->event }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ class_basename($log->subject_type) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <div class="max-w-md">
                                                @if($log->description)
                                                    <div class="mb-2 font-medium">{{ $log->description }}</div>
                                                @endif
                                                
                                                @if($log->properties && count($log->properties) > 0)
                                                    <details class="cursor-pointer">
                                                        <summary class="text-blue-600 hover:text-blue-800">عرض التفاصيل</summary>
                                                        <div class="mt-2 p-3 bg-gray-50 rounded text-xs">
                                                            <pre class="whitespace-pre-wrap">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    </details>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t">
                        {{ $logs->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-600 text-lg">لا توجد سجلات</p>
                        <p class="text-gray-500 text-sm mt-2">جرب تغيير خيارات التصفية</p>
                    </div>
                @endif
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
