@extends('layouts.admin')

@section('title', 'مراجعة التقديم')
@section('page-title', 'مراجعة نشاط الطالب')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- زر العودة -->
    <a href="{{ route('admin.pending-submissions') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition">
        <span>←</span>
        التقديمات المعلقة
    </a>

    <!-- معلومات الطالب والنشاط -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- بطاقة الطالب -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span>👨‍🎓</span>
                معلومات الطالب
            </h3>
            <div class="flex items-center gap-4">
                @if($submission->student?->avatar)
                    <img src="{{ $submission->student->avatar_url }}" class="w-16 h-16 rounded-xl object-cover">
                @else
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                        {{ mb_substr($submission->student?->name ?? '?', 0, 1) }}
                    </div>
                @endif
                <div>
                    <div class="text-xl font-bold text-gray-800">{{ $submission->student?->name ?? 'غير معروف' }}</div>
                    <div class="text-gray-500">{{ $submission->student?->email ?? '-' }}</div>
                    <div class="text-sm text-gray-400 mt-1">
                        🏫 {{ $submission->student?->school?->name ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- بطاقة النشاط -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span>📋</span>
                معلومات النشاط
            </h3>
            <div class="space-y-3">
                <div>
                    <div class="text-sm text-gray-500">اسم النشاط</div>
                    <div class="font-semibold text-gray-800">{{ $submission->activity?->title ?? 'نشاط محذوف' }}</div>
                </div>
                @if($submission->activity?->lesson)
                <div>
                    <div class="text-sm text-gray-500">الدرس</div>
                    <div class="text-gray-700">{{ $submission->activity->lesson->title }}</div>
                </div>
                @endif
                @if($submission->activity?->lesson?->meaning?->concept?->value)
                <div>
                    <div class="text-sm text-gray-500">القيمة</div>
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">
                        💎 {{ $submission->activity->lesson->concept->value->name }}
                    </span>
                </div>
                @endif
                <div>
                    <div class="text-sm text-gray-500">تاريخ التقديم</div>
                    <div class="text-gray-700">{{ $submission->submitted_at?->format('Y/m/d H:i') ?? $submission->created_at->format('Y/m/d H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- إجابة الطالب -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span>📝</span>
            إجابة الطالب
        </h3>

        @if($submission->answer)
            @php
                $answer = is_string($submission->answer) ? json_decode($submission->answer, true) : $submission->answer;
            @endphp

            @if(is_array($answer))
                <div class="space-y-4">
                    @foreach($answer as $key => $value)
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-sm text-gray-500 mb-1">الإجابة {{ $loop->iteration }}</div>
                            <div class="text-gray-800">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="text-gray-800 whitespace-pre-wrap">{{ html_excerpt($submission->answer, 2000) }}</div>
                </div>
            @endif
        @else
            <div class="text-gray-400 text-center py-4">لا توجد إجابة نصية</div>
        @endif

        @if($submission->file_path)
            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <div class="text-sm text-gray-500 mb-2">مرفقات الطالب</div>
                <a href="{{ asset('storage/app/public/data/' . $submission->file_path) }}" target="_blank" 
                   class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800">
                    <span>📎</span>
                    فتح الملف
                </a>
            </div>
        @endif
    </div>

    <!-- نموذج المراجعة -->
    <form action="{{ route('admin.save-review', $submission->id) }}" method="POST" class="bg-white rounded-xl shadow-sm p-6">
        @csrf
        
        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span>⚖️</span>
            التقييم والمراجعة
        </h3>

        <div class="space-y-6">
            <!-- حالة المراجعة -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">حالة التقديم</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-500 transition has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                        <input type="radio" name="status" value="approved" class="w-5 h-5 text-green-500" required>
                        <span class="text-2xl">✅</span>
                        <div>
                            <div class="font-semibold text-gray-800">قبول</div>
                            <div class="text-sm text-gray-500">الإجابة صحيحة ومقبولة</div>
                        </div>
                    </label>
                    
                    <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-red-500 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                        <input type="radio" name="status" value="rejected" class="w-5 h-5 text-red-500">
                        <span class="text-2xl">❌</span>
                        <div>
                            <div class="font-semibold text-gray-800">رفض</div>
                            <div class="text-sm text-gray-500">الإجابة غير مقبولة</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- الدرجة -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الدرجة (من 100)</label>
                <input type="number" name="score" min="0" max="100" value="{{ $submission->activity?->points ?? 10 }}"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <p class="text-sm text-gray-500 mt-1">الدرجة الافتراضية للنشاط: {{ $submission->activity?->points ?? 10 }}</p>
            </div>

            <!-- الملاحظات -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات للطالب (اختياري)</label>
                <textarea name="feedback" rows="4" 
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                          placeholder="اكتب ملاحظاتك هنا...">{{ $submission->feedback }}</textarea>
            </div>

            <!-- أزرار الإرسال -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 py-3 px-6 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-semibold hover:shadow-lg transition">
                    ✅ حفظ المراجعة
                </button>
                <a href="{{ route('admin.pending-submissions') }}" class="py-3 px-6 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">
                    إلغاء
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
