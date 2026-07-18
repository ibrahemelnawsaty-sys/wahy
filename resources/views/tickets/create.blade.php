@php
    // اختيار لايوت الدور الحالي — الصفحة عابرة لكل الأدوار.
    $__role = auth()->user()->role;
    $__layout = $__role === 'student' ? 'layouts.student-app'
        : ($__role === 'school_admin' ? 'layouts.school-admin'
        : ($__role === 'teacher' ? 'layouts.teacher'
        : ($__role === 'parent' ? 'layouts.parent'
        : ($__role === 'technical_support' ? 'layouts.support'
        : 'layouts.admin')))); // technical_support => لوحة الدعم؛ super_admin => admin
@endphp

@extends($__layout)

@section('title', 'رفع تذكرة دعم فنيّ')
@section('page-title', 'رفع تذكرة دعم فنيّ')

@section('content')
<div class="tickets-page">
    @include('tickets.partials.styles')

    <a href="{{ route('tickets.index') }}" class="tk-back">→ العودة إلى تذاكري</a>

    <div class="tk-header">
        <div>
            <h1 class="tk-title">➕ رفع تذكرة جديدة</h1>
            <p class="tk-subtitle">صِف مشكلتك أو استفسارك بوضوح، وسيتابعها فريق الدعم في أسرع وقت.</p>
        </div>
    </div>

    <div class="tk-panel">
        <form method="POST" action="{{ route('tickets.store') }}">
            @csrf

            <div class="tk-field">
                <label class="tk-label" for="tk-subject">عنوان التذكرة <span class="req">*</span></label>
                <input type="text" id="tk-subject" name="subject" class="tk-input" maxlength="255"
                       value="{{ old('subject') }}" placeholder="مثال: لا أستطيع فتح صفحة الأنشطة" required>
                @error('subject')
                    <span class="tk-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="tk-grid2">
                <div class="tk-field">
                    <label class="tk-label" for="tk-category">التصنيف <span class="req">*</span></label>
                    <select id="tk-category" name="category" class="tk-select" required>
                        <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>مشكلة تقنية</option>
                        <option value="account" {{ old('category') == 'account' ? 'selected' : '' }}>مشكلة حساب</option>
                        <option value="content" {{ old('category') == 'content' ? 'selected' : '' }}>محتوى</option>
                        <option value="other" {{ old('category', 'other') == 'other' ? 'selected' : '' }}>أخرى</option>
                    </select>
                    @error('category')
                        <span class="tk-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="tk-field">
                    <label class="tk-label" for="tk-priority">الأولوية <span class="req">*</span></label>
                    <select id="tk-priority" name="priority" class="tk-select" required>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>منخفضة</option>
                        <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>عادية</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>عالية</option>
                    </select>
                    @error('priority')
                        <span class="tk-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="tk-field">
                <label class="tk-label" for="tk-message">تفاصيل المشكلة <span class="req">*</span></label>
                <textarea id="tk-message" name="message" class="tk-textarea"
                          placeholder="اشرح المشكلة بالتفصيل: ماذا كنت تفعل؟ وما الذي حدث؟ ومتى بدأت المشكلة؟" required>{{ old('message') }}</textarea>
                <span class="tk-help">كلما زادت التفاصيل، أسرع فريق الدعم في مساعدتك.</span>
                @error('message')
                    <span class="tk-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="tk-form-actions">
                <a href="{{ route('tickets.index') }}" class="tk-btn tk-btn-ghost">إلغاء</a>
                <button type="submit" class="tk-btn tk-btn-primary">📨 إرسال التذكرة</button>
            </div>
        </form>
    </div>
</div>
@endsection
