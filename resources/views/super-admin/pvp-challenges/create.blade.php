@extends('layouts.admin')

@section('title', 'إنشاء تحدي PvP')
@section('page-title', 'إنشاء تحدي PvP')

@section('content')
<div style="padding: 24px; max-width: 900px; margin: 0 auto;">
    <a href="{{ route('admin.pvp-challenges.index') }}"
       style="display: inline-flex; align-items: center; gap: 6px; color: #64748b; text-decoration: none; margin-bottom: 18px; font-size: 14px;">
        <i class="fas fa-arrow-right" aria-hidden="true"></i> رجوع إلى التحديات
    </a>

    <div style="background: #fff; border-radius: 14px; padding: 28px; box-shadow: 0 4px 18px rgba(0,0,0,0.06);">
        <h2 style="margin: 0 0 24px; font-size: 22px; font-weight: 700; color: #1e293b;">
            ⚔️ إنشاء تحدي PvP جديد
        </h2>

        @if($errors->any())
        <div role="alert" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 10px; margin-bottom: 18px;">
            <strong>تأكد من المدخلات:</strong>
            <ul style="margin: 8px 0 0; padding-right: 20px;">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.pvp-challenges.store') }}">
            @csrf

            <div style="margin-bottom: 20px;">
                <label for="title" style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b;">
                    عنوان التحدي *
                </label>
                <input type="text" id="title" name="title" required maxlength="255"
                       value="{{ old('title') }}"
                       style="width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="value_id" style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b;">
                    ربط التحدي بقيمة (اختياري)
                </label>
                <select id="value_id" name="value_id"
                        style="width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; background: #fff;">
                    <option value="">— تحدي عام (يظهر لكل المدارس) —</option>
                    @foreach($values as $value)
                    <option value="{{ $value->id }}" {{ old('value_id') == $value->id ? 'selected' : '' }}>
                        {{ $value->name }}
                    </option>
                    @endforeach
                </select>
                <small style="color: #94a3b8; font-size: 13px;">
                    إذا اخترت قيمة، التحدي يظهر فقط للطلاب في المدارس التي فعّلت هذه القيمة.
                </small>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label for="time_limit" style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b;">
                        الوقت الإجمالي (ثواني) *
                    </label>
                    <input type="number" id="time_limit" name="time_limit" required min="30" max="1800"
                           value="{{ old('time_limit', 600) }}"
                           style="width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px;">
                    <small style="color: #94a3b8; font-size: 13px;">30 إلى 1800 ثانية</small>
                </div>

                <div>
                    <label for="difficulty" style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b;">
                        مستوى الصعوبة
                    </label>
                    <select id="difficulty" name="difficulty"
                            style="width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; background: #fff;">
                        <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>سهل</option>
                        <option value="medium" {{ old('difficulty', 'medium') === 'medium' ? 'selected' : '' }}>متوسط</option>
                        <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>صعب</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="question_ids" style="display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b;">
                    اختر الأسئلة (يمكن اختيار عدة) *
                </label>
                @if($approvedQuestions->count() > 0)
                <select id="question_ids" name="question_ids[]" multiple required
                        aria-describedby="question_ids_help"
                        style="width: 100%; min-height: 220px; padding: 10px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                    @foreach($approvedQuestions as $q)
                    <option value="{{ $q->id }}"
                            {{ collect(old('question_ids', []))->contains($q->id) ? 'selected' : '' }}>
                        [{{ $q->question_type }}] {{ $q->title }} ({{ $q->difficulty ?? 'متوسط' }})
                    </option>
                    @endforeach
                </select>
                <small id="question_ids_help" style="color: #94a3b8; font-size: 13px;">
                    اضغط Ctrl (أو Cmd) للاختيار المتعدد. الحد الأقصى 50 سؤالًا.
                </small>
                @else
                <div role="alert" style="background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; padding: 14px; border-radius: 10px;">
                    لا توجد أسئلة مُعتمدة في بنك الأسئلة بعد.
                    <a href="{{ route('admin.question-bank.index') }}" style="color: #92400e; font-weight: 700; text-decoration: underline;">أضف أسئلة أولًا</a>
                </div>
                @endif
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           style="width: 18px; height: 18px;">
                    <span style="font-weight: 600; color: #1e293b;">تفعيل التحدي فور إنشائه</span>
                </label>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit"
                        style="background: linear-gradient(135deg, #8b5cf6, #ec4899); color: #fff; padding: 14px 32px; border: none; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer;"
                        @if($approvedQuestions->count() === 0) disabled @endif>
                    💾 حفظ التحدي
                </button>
                <a href="{{ route('admin.pvp-challenges.index') }}"
                   style="background: #f1f5f9; color: #475569; padding: 14px 24px; border-radius: 12px; font-weight: 600; text-decoration: none;">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
