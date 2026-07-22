@extends('layouts.teacher')

@section('title', isset($exercise) ? 'تعديل التمرين' : 'إنشاء تمرين جديد')

@section('content')
<div class="container">
    <div class="page-header" style="margin-bottom: 30px;">
        <h1 class="page-title">{{ isset($exercise) ? '✏️ تعديل التمرين' : '➕ إنشاء تمرين جديد' }}</h1>
    </div>

    <form action="{{ isset($exercise) ? route('teacher.exercises.update', $exercise->id) : route('teacher.exercises.store') }}" method="POST" style="background: white; border-radius: 20px; padding: 35px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
        @csrf
        @if(isset($exercise)) @method('PUT') @endif

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
            <div>
                <label style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">عنوان التمرين *</label>
                <input type="text" name="title" value="{{ old('title', $exercise->title ?? '') }}" required style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; font-family: inherit; transition: border 0.3s;" onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e2e8f0'">
                @error('title') <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">الفصل الدراسي</label>
                <select name="classroom_id" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; font-family: inherit;">
                    <option value="">كل الفصول</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c->id }}" {{ old('classroom_id', $exercise->classroom_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">الوصف</label>
            <textarea name="description" rows="3" style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; font-family: inherit; resize: vertical;">{{ old('description', $exercise->description ?? '') }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; margin-bottom: 25px;">
            <div>
                <label style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">نوع التمرين *</label>
                <select name="type" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-family: inherit;">
                    <option value="quiz" {{ old('type', $exercise->type ?? '') == 'quiz' ? 'selected' : '' }}>اختبار قصير</option>
                    <option value="review" {{ old('type', $exercise->type ?? '') == 'review' ? 'selected' : '' }}>مراجعة</option>
                    <option value="challenge" {{ old('type', $exercise->type ?? '') == 'challenge' ? 'selected' : '' }}>تحدي</option>
                </select>
            </div>
            <div>
                <label style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">الصعوبة *</label>
                <select name="difficulty" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-family: inherit;">
                    <option value="easy" {{ old('difficulty', $exercise->difficulty ?? '') == 'easy' ? 'selected' : '' }}>سهل</option>
                    <option value="medium" {{ old('difficulty', $exercise->difficulty ?? 'medium') == 'medium' ? 'selected' : '' }}>متوسط</option>
                    <option value="hard" {{ old('difficulty', $exercise->difficulty ?? '') == 'hard' ? 'selected' : '' }}>صعب</option>
                </select>
            </div>
            <div>
                <label style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">المدة (دقائق)</label>
                <input type="number" name="time_limit" value="{{ old('time_limit', $exercise->time_limit ?? '') }}" min="1" max="120" placeholder="بدون وقت" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-family: inherit;">
            </div>
            <div>
                <label style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">عدد المحاولات *</label>
                <input type="number" name="max_attempts" value="{{ old('max_attempts', $exercise->max_attempts ?? 3) }}" required min="1" max="10" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-family: inherit;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
            <div>
                <label style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">تاريخ البدء</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($exercise) && $exercise->starts_at ? $exercise->starts_at->format('Y-m-d\TH:i') : '') }}" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-family: inherit;">
            </div>
            <div>
                <label style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">تاريخ الانتهاء</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($exercise) && $exercise->ends_at ? $exercise->ends_at->format('Y-m-d\TH:i') : '') }}" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-family: inherit;">
            </div>
        </div>

        {{-- اختيار الأسئلة --}}
        <div style="margin-bottom: 30px;">
            <label style="font-weight: 700; color: #334155; margin-bottom: 12px; display: block; font-size: 18px;">📋 اختر الأسئلة من بنك الأسئلة *</label>
            <div id="selectedCount" style="margin-bottom: 12px; padding: 10px 16px; background: #eff6ff; border-radius: 8px; color: #2563eb; font-weight: 600;">0 سؤال محدد</div>

            @if($questions->count() > 0)
            <div style="max-height: 400px; overflow-y: auto; border: 2px solid #e2e8f0; border-radius: 12px; padding: 10px;">
                @php $selectedIds = isset($exercise) ? ($exercise->questions ?? []) : old('question_ids', []); @endphp
                @foreach($questions as $q)
                <label style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; cursor: pointer; transition: background 0.2s; margin-bottom: 4px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                    <input type="checkbox" name="question_ids[]" value="{{ $q->id }}" {{ in_array($q->id, (array)$selectedIds) ? 'checked' : '' }} onchange="updateCount()" style="width: 20px; height: 20px; accent-color: #667eea;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #1e293b; margin-bottom: 3px;">{{ $q->title }}</div>
                        <div style="font-size: 13px; color: #64748b;">{{ Str::limit($q->question_text, 80) }}</div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <span style="background: {{ $q->difficulty == 'easy' ? '#dcfce7' : ($q->difficulty == 'medium' ? '#fef3c7' : '#fecaca') }}; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                            {{ $q->difficulty == 'easy' ? 'سهل' : ($q->difficulty == 'medium' ? 'متوسط' : 'صعب') }}
                        </span>
                        <span style="background: #f1f5f9; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; color: #64748b;">{{ $q->points }} نقطة</span>
                    </div>
                </label>
                @endforeach
            </div>
            @else
            <div style="text-align: center; padding: 40px; background: #f8fafc; border-radius: 12px;">
                <p style="color: #94a3b8;">لا توجد أسئلة معتمَدة في البنك بعد. تُضاف الأسئلة وتُعتمَد من الإدارة، ثم تظهر هنا لاختيارها.</p>
            </div>
            @endif
        </div>

        <div style="display: flex; gap: 15px; justify-content: flex-end;">
            <a href="{{ route('teacher.exercises') }}" style="padding: 14px 30px; border-radius: 12px; background: #f1f5f9; color: #64748b; text-decoration: none; font-weight: 600;">إلغاء</a>
            <button type="submit" style="padding: 14px 40px; border-radius: 12px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; font-weight: 700; font-size: 16px; cursor: pointer; box-shadow: 0 4px 15px rgba(102,126,234,0.4);">
                {{ isset($exercise) ? '💾 حفظ التعديلات' : '✅ إنشاء التمرين' }}
            </button>
        </div>
    </form>
</div>

<script>
function updateCount() {
    const checked = document.querySelectorAll('input[name="question_ids[]"]:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' سؤال محدد';
}
document.addEventListener('DOMContentLoaded', updateCount);
</script>
@endsection
