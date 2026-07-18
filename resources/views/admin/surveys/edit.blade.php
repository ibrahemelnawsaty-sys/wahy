@extends('layouts.admin')

@section('page-title', 'تعديل استبيان: ' . $survey->title)

@section('content')
<style>
.form-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 900px;
    margin: 0 auto;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: #334155;
    font-size: 14px;
}

.form-label.required::after {
    content: " *";
    color: #dc2626;
}

.form-input,
.form-select,
.form-textarea {
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
}

.checkbox-group {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.questions-section {
    margin-top: 32px;
    padding-top: 32px;
    border-top: 2px solid #e2e8f0;
}

.question-item {
    background: #f8fafc;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 16px;
    border: 2px solid #e2e8f0;
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.question-number {
    font-weight: 700;
    color: var(--color-primary);
    font-size: 16px;
}

.btn-remove-question {
    background: #fee2e2;
    color: #dc2626;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
}

.options-input {
    margin-top: 12px;
}

.option-item {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
    align-items: center;
}

.option-item input {
    flex: 1;
}

.option-score-input {
    width: 70px !important;
    flex: none !important;
    text-align: center;
    font-weight: 600;
}

.btn-remove-option {
    background: #fee2e2;
    color: #dc2626;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
}

.btn-add-option {
    background: #dbeafe;
    color: #2563eb;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    margin-top: 8px;
}

.btn-add-question {
    background: var(--color-primary);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    margin-top: 16px;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 2px solid #e2e8f0;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
}

.btn-primary { background: var(--color-primary); color: white; }
.btn-secondary { background: #e2e8f0; color: #475569; }
</style>

<div class="form-card">
    <h2 style="margin-bottom: 24px;">✏️ تعديل استبيان: {{ $survey->title }}</h2>

    <form method="POST" action="{{ route('admin.surveys.update', $survey) }}" id="surveyForm">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label required">عنوان الاستبيان</label>
            <input type="text" name="title" class="form-input" value="{{ old('title', $survey->title) }}" placeholder="مثال: استبيان رضا المستخدمين" required>
            @error('title')
                <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-textarea" placeholder="وصف مختصر عن الاستبيان...">{{ old('description', $survey->description) }}</textarea>
            @error('description')
                <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
            @enderror
        </div>

        @if($survey->survey_type !== 'pre_post_assessment')
        <div class="form-group">
            <label class="form-label required">المستهدفون</label>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" name="target_type[]" value="schools" id="target_schools" {{ in_array('schools', old('target_type', $survey->target_roles ?? [])) ? 'checked' : '' }}>
                    <label for="target_schools">🏫 المدارس</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="target_type[]" value="teachers" id="target_teachers" {{ in_array('teachers', old('target_type', $survey->target_roles ?? [])) ? 'checked' : '' }}>
                    <label for="target_teachers">👨‍🏫 المعلمين</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="target_type[]" value="students" id="target_students" {{ in_array('students', old('target_type', $survey->target_roles ?? [])) ? 'checked' : '' }}>
                    <label for="target_students">🎓 الطلاب</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="target_type[]" value="parents" id="target_parents" {{ in_array('parents', old('target_type', $survey->target_roles ?? [])) ? 'checked' : '' }}>
                    <label for="target_parents">👪 أولياء الأمور</label>
                </div>
            </div>
            @error('target_type')
                <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
            @enderror
        </div>
        @else
        {{-- التقييم القبلي/البعدي يستهدف الطلاب تلقائياً --}}
        <input type="hidden" name="target_type[]" value="students">
        <div style="background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); padding: 16px 20px; border-radius: 12px; border: 2px solid #8b5cf6; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">📊</span>
                <div>
                    <div style="font-weight: 700; color: #5b21b6; font-size: 15px;">تقييم {{ $survey->assessment_phase == 'pre' ? 'قبلي' : 'بعدي' }} مرتبط بالدرس</div>
                    <div style="font-size: 13px; color: #7c3aed; margin-top: 4px;">المستهدفون: 🎓 الطلاب (تلقائياً)</div>
                </div>
            </div>
        </div>
        @endif

        <div class="form-group">
            <div class="checkbox-item" style="padding: 12px 16px; background: #f0fdf4; border-radius: 8px; border: 2px solid #bbf7d0;">
                <input type="hidden" name="is_mandatory" value="0">
                <input type="checkbox" name="is_mandatory" id="is_mandatory" value="1" {{ old('is_mandatory', $survey->is_mandatory) ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #16a34a;">
                <label for="is_mandatory" style="font-weight: 600; color: #15803d; cursor: pointer;">📌 إلزامي (يجب على المستهدفين الإجابة)</label>
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox-item" style="padding: 12px 16px; background: #eff6ff; border-radius: 8px; border: 2px solid #bfdbfe;">
                <input type="hidden" name="is_popup" value="0">
                <input type="checkbox" name="is_popup" id="is_popup" value="1" {{ old('is_popup', $survey->is_popup) ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #2563eb;">
                <label for="is_popup" style="font-weight: 600; color: #1d4ed8; cursor: pointer;">💬 يظهر كنافذة منبثقة (Popup)</label>
            </div>
        </div>

        {{-- التقييم القبلي/البعدي (درس أو قيمة) يحتفظ بمُشغِّله الأصلي — نُخفي الحقل حتى لا يُفسده التعديل --}}
        @if($survey->survey_type !== 'pre_post_assessment')
        <div class="form-group">
            <label class="form-label required">متى يظهر الاستبيان</label>
            <select name="trigger_type" class="form-select" required>
                <option value="on_platform_open" {{ old('trigger_type', $survey->trigger_type) == 'on_platform_open' ? 'selected' : '' }}>عند فتح المنصة</option>
                <option value="on_login" {{ old('trigger_type', $survey->trigger_type) == 'on_login' ? 'selected' : '' }}>عند تسجيل الدخول</option>
                <option value="on_first_login" {{ old('trigger_type', $survey->trigger_type) == 'on_first_login' ? 'selected' : '' }}>عند أول تسجيل دخول</option>
                <option value="on_lesson_start" {{ old('trigger_type', $survey->trigger_type) == 'on_lesson_start' ? 'selected' : '' }}>عند بدء الدرس</option>
                <option value="on_lesson_complete" {{ old('trigger_type', $survey->trigger_type) == 'on_lesson_complete' ? 'selected' : '' }}>عند إتمام الدرس</option>
                <option value="on_activity_complete" {{ old('trigger_type', $survey->trigger_type) == 'on_activity_complete' ? 'selected' : '' }}>عند إتمام النشاط</option>
                <option value="manual" {{ old('trigger_type', $survey->trigger_type) == 'manual' ? 'selected' : '' }}>يدوي (يظهر عند فتح الرابط مباشرة)</option>
            </select>
            @error('trigger_type')
                <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
            @enderror
        </div>
        @endif

        <div class="form-group">
            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 20px; border-radius: 12px; border: 3px solid #f59e0b; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <input type="hidden" name="requires_login" value="0">
                    <input type="checkbox" name="requires_login" id="requires_login" value="1" {{ old('requires_login', $survey->requires_login) ? 'checked' : '' }} style="width: 24px; height: 24px; cursor: pointer; accent-color: #f59e0b; margin-top: 2px;">
                    <div>
                        <label for="requires_login" style="font-weight: 700; font-size: 16px; color: #92400e; cursor: pointer; display: block; margin-bottom: 6px;">
                            🔐 يتطلب تسجيل الدخول
                        </label>
                        <p style="margin: 0; font-size: 14px; color: #b45309; line-height: 1.6;">
                            عند تفعيل هذا الخيار، يجب على المستخدم تسجيل الدخول أولاً قبل أن يتمكن من ملء الاستبيان. هذا يساعد في تتبع الإجابات ومنع التكرار.
                        </p>
                        <div style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
                            <span style="background: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; color: #92400e; font-weight: 600;">✅ تتبع دقيق للمستخدمين</span>
                            <span style="background: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; color: #92400e; font-weight: 600;">🚫 منع الإجابات المكررة</span>
                            <span style="background: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; color: #92400e; font-weight: 600;">📊 بيانات موثوقة</span>
                        </div>
                    </div>
                </div>
            </div>
            @error('requires_login')
                <span style="color: #dc2626; font-size: 13px; display: block; margin-top: 8px;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label required">الحالة</label>
            <select name="status" class="form-select" required>
                <option value="draft" {{ old('status', $survey->status) == 'draft' ? 'selected' : '' }}>مسودة</option>
                <option value="active" {{ old('status', $survey->status) == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="closed" {{ old('status', $survey->status) == 'closed' ? 'selected' : '' }}>مغلق</option>
            </select>
            @error('status')
                <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
            @enderror
        </div>

        <!-- الأسئلة -->
        <div class="questions-section">
            <h3 style="margin-bottom: 20px;">الأسئلة</h3>
            <div id="questionsContainer">
                <!-- سيتم إضافة الأسئلة هنا ديناميكياً -->
            </div>
            <button type="button" class="btn-add-question" onclick="addQuestion()">➕ إضافة سؤال</button>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 تحديث الاستبيان</button>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

<script>
let questionIndex = 0;
const existingQuestions = @json($survey->questions);

function addQuestion(questionData = null) {
    const container = document.getElementById('questionsContainer');
    const questionDiv = document.createElement('div');
    questionDiv.className = 'question-item';
    questionDiv.id = `question-${questionIndex}`;
    
    const questionText = questionData?.question_text || '';
    const questionType = questionData?.question_type || 'text';
    const options = questionData?.options || [];
    const isRequired = questionData?.is_required || false;
    const order = questionData?.order ?? questionIndex;
    const optionScores = questionData?.option_scores || [];
    
    questionDiv.innerHTML = `
        <div class="question-header">
            <span class="question-number">سؤال #${questionIndex + 1}</span>
            <button type="button" class="btn-remove-question" onclick="removeQuestion(${questionIndex})">🗑️ حذف</button>
        </div>
        
        <div class="form-group">
            <label class="form-label required">نص السؤال</label>
            <input type="text" name="questions[${questionIndex}][question_text]" class="form-input" value="${questionText}" required placeholder="اكتب السؤال هنا...">
        </div>
        
        <div class="form-group">
            <label class="form-label required">نوع السؤال</label>
            <select name="questions[${questionIndex}][question_type]" class="form-select" onchange="updateQuestionType(${questionIndex})" required>
                <option value="text" ${questionType == 'text' ? 'selected' : ''}>نص</option>
                <option value="textarea" ${questionType == 'textarea' ? 'selected' : ''}>نص طويل (توصيل)</option>
                <option value="select" ${questionType == 'select' ? 'selected' : ''}>قائمة منسدلة</option>
                <option value="radio" ${questionType == 'radio' ? 'selected' : ''}>اختيار واحد (Radio)</option>
                <option value="checkbox" ${questionType == 'checkbox' ? 'selected' : ''}>اختيار متعدد (Checkbox)</option>
                <option value="rating" ${questionType == 'rating' ? 'selected' : ''}>تقييم نجوم (1-5)</option>
                <option value="scale" ${questionType == 'scale' ? 'selected' : ''}>مقياس رقمي (1-10)</option>
            </select>
        </div>
        
        <div class="form-group" id="options-${questionIndex}" style="display: ${['select', 'radio', 'checkbox'].includes(questionType) ? 'block' : 'none'};">
            <label class="form-label">الخيارات</label>
            <div class="options-input" id="optionsContainer-${questionIndex}">
                ${options.length > 0 ? options.map((opt, idx) => {
                    const scoreVal = (optionScores && optionScores[idx] !== undefined && optionScores[idx] !== null) ? optionScores[idx] : '';
                    return `
                    <div class="option-item">
                        <input type="text" name="questions[${questionIndex}][options][]" value="${opt}" placeholder="الخيار ${idx + 1}" class="form-input">
                        <input type="number" name="questions[${questionIndex}][option_scores][]" placeholder="الدرجة" class="form-input option-score-input" min="0" value="${scoreVal}" title="درجة هذا الخيار">
                        <button type="button" class="btn-remove-option" onclick="this.parentElement.remove()">×</button>
                    </div>
                `}).join('') : `
                    <div class="option-item">
                        <input type="text" name="questions[${questionIndex}][options][]" placeholder="الخيار الأول" class="form-input">
                        <input type="number" name="questions[${questionIndex}][option_scores][]" placeholder="الدرجة" class="form-input option-score-input" min="0" title="درجة هذا الخيار">
                        <button type="button" class="btn-remove-option" onclick="this.parentElement.remove()">×</button>
                    </div>
                `}
            </div>
            <button type="button" class="btn-add-option" onclick="addOption(${questionIndex})">➕ إضافة خيار</button>
        </div>
        
        <div class="checkbox-item">
            <input type="checkbox" name="questions[${questionIndex}][is_required]" id="required-${questionIndex}" value="1" ${isRequired ? 'checked' : ''}>
            <label for="required-${questionIndex}">مطلوب</label>
        </div>
        
        <input type="hidden" name="questions[${questionIndex}][order]" value="${order}">
    `;
    
    container.appendChild(questionDiv);
    questionIndex++;
}

function removeQuestion(index) {
    const questionDiv = document.getElementById(`question-${index}`);
    if (questionDiv) {
        questionDiv.remove();
        updateQuestionNumbers();
    }
}

function updateQuestionNumbers() {
    const questions = document.querySelectorAll('.question-item');
    questions.forEach((q, index) => {
        const numberSpan = q.querySelector('.question-number');
        if (numberSpan) {
            numberSpan.textContent = `سؤال #${index + 1}`;
        }
        
        // تحديث قيمة order
        const orderInput = q.querySelector('input[name*="[order]"]');
        if (orderInput) {
            orderInput.value = index;
        }
        
        // تحديث جميع أسماء الـ inputs لتكون متسلسلة
        const oldIndex = q.dataset.index;
        
        // تحديث نص السؤال
        const questionTextInput = q.querySelector(`input[name="questions[${oldIndex}][question_text]"]`);
        if (questionTextInput) {
            questionTextInput.name = `questions[${index}][question_text]`;
        }
        
        // تحديث نوع السؤال
        const questionTypeSelect = q.querySelector(`select[name="questions[${oldIndex}][question_type]"]`);
        if (questionTypeSelect) {
            questionTypeSelect.name = `questions[${index}][question_type]`;
            questionTypeSelect.setAttribute('onchange', `updateQuestionType(${oldIndex})`);
        }
        
        // تحديث الخيارات
        const optionInputs = q.querySelectorAll(`input[name="questions[${oldIndex}][options][]"]`);
        optionInputs.forEach(optionInput => {
            optionInput.name = `questions[${index}][options][]`;
        });
        
        // تحديث درجات الخيارات
        const scoreInputs = q.querySelectorAll(`input[name="questions[${oldIndex}][option_scores][]"]`);
        scoreInputs.forEach(scoreInput => {
            scoreInput.name = `questions[${index}][option_scores][]`;
        });
        
        // تحديث is_required checkbox
        const isRequiredCheckbox = q.querySelector(`input[name="questions[${oldIndex}][is_required]"]`);
        if (isRequiredCheckbox) {
            isRequiredCheckbox.name = `questions[${index}][is_required]`;
            isRequiredCheckbox.id = `required-${index}`;
            const label = q.querySelector(`label[for="required-${oldIndex}"]`);
            if (label) {
                label.setAttribute('for', `required-${index}`);
            }
        }
        
        // تحديث order hidden input
        const orderHidden = q.querySelector(`input[name="questions[${oldIndex}][order]"]`);
        if (orderHidden) {
            orderHidden.name = `questions[${index}][order]`;
        }
        
        // تحديث dataset.index
        q.dataset.index = index;
    });
}

function updateQuestionType(index) {
    const select = document.querySelector(`#question-${index} select[name*="[question_type]"]`);
    const optionsDiv = document.getElementById(`options-${index}`);
    
    if (['select', 'radio', 'checkbox'].includes(select.value)) {
        optionsDiv.style.display = 'block';
    } else {
        optionsDiv.style.display = 'none';
    }
}

function addOption(questionIndex) {
    const container = document.getElementById(`optionsContainer-${questionIndex}`);
    const optionDiv = document.createElement('div');
    optionDiv.className = 'option-item';
    const optionCount = container.children.length;
    
    optionDiv.innerHTML = `
        <input type="text" name="questions[${questionIndex}][options][]" placeholder="الخيار ${optionCount + 1}" class="form-input">
        <input type="number" name="questions[${questionIndex}][option_scores][]" placeholder="الدرجة" class="form-input option-score-input" min="0" title="درجة هذا الخيار">
        <button type="button" class="btn-remove-option" onclick="this.parentElement.remove()">×</button>
    `;
    
    container.appendChild(optionDiv);
}

// تحميل الأسئلة الموجودة عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    if (existingQuestions && existingQuestions.length > 0) {
        existingQuestions.forEach(question => {
            addQuestion(question);
        });
    } else {
        addQuestion();
    }
});
</script>

@endsection
