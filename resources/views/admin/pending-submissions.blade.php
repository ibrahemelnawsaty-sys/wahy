@extends('layouts.admin')

@section('title', 'التقديمات المعلقة')
@section('page-title', 'التقديمات المعلقة للمراجعة')

@section('content')
<div class="space-y-6">
    <!-- إحصائيات سريعة -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold">{{ $stats['total_pending'] }}</div>
                    <div class="text-sm opacity-90">إجمالي المعلقة</div>
                </div>
                <div class="text-4xl opacity-80">📋</div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold">{{ $stats['today_pending'] }}</div>
                    <div class="text-sm opacity-90">تقديمات اليوم</div>
                </div>
                <div class="text-4xl opacity-80">📅</div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold">{{ $stats['week_pending'] }}</div>
                    <div class="text-sm opacity-90">هذا الأسبوع</div>
                </div>
                <div class="text-4xl opacity-80">📊</div>
            </div>
        </div>
    </div>

    <!-- جدول التقديمات -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <span>📝</span>
                التقديمات المعلقة
            </h2>
        </div>

        @if($submissions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">#</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">الطالب</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">المدرسة</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">النشاط</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">القيمة</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">تاريخ التقديم</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($submissions as $index => $submission)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-500">{{ $submissions->firstItem() + $index }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($submission->student?->avatar)
                                    <img src="{{ $submission->student->avatar_url }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                        {{ mb_substr($submission->student?->name ?? '?', 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $submission->student?->name ?? 'غير معروف' }}</div>
                                    <div class="text-xs text-gray-500">{{ $submission->student?->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">
                                🏫 {{ $submission->student?->school?->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="max-w-xs">
                                <div class="font-medium text-gray-800 truncate">{{ $submission->activity?->title ?? 'نشاط محذوف' }}</div>
                                <div class="text-xs text-gray-500">{{ $submission->activity?->lesson?->title ?? '-' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($submission->activity?->lesson?->meaning?->concept?->value)
                                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">
                                    💎 {{ $submission->activity->lesson->concept->value->name }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-600">{{ $submission->submitted_at?->format('Y/m/d') ?? $submission->created_at->format('Y/m/d') }}</div>
                            <div class="text-xs text-gray-400">{{ $submission->submitted_at?->format('H:i') ?? $submission->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.review-submission', $submission->id) }}" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:shadow-lg transition text-sm">
                                <span>👁️</span>
                                مراجعة
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-6 border-t border-gray-100">
            {{ $submissions->links() }}
        </div>
        @else
        <div class="p-12 text-center">
            <div class="text-6xl mb-4">✅</div>
            <h3 class="text-xl font-bold text-gray-700 mb-2">لا توجد تقديمات معلقة!</h3>
            <p class="text-gray-500">تم مراجعة كل التقديمات</p>
        </div>
        @endif
    </div>
</div>
@endsection
