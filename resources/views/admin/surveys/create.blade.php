@extends('layouts.admin')

@section('page-title', 'إنشاء استبيان جديد')

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
    transition: all 0.2s;
}

.btn-remove-option:hover {
    background: #fca5a5;
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
    transition: all 0.2s;
}

.btn-add-option:hover {
    background: #93c5fd;
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
    transition: all 0.2s;
}

.btn-add-question:hover {
    opacity: 0.9;
}

.error-message {
    color: #dc2626;
    font-size: 13px;
    margin-top: 4px;
    display: block;
}

.form-input.error,
.form-select.error,
.form-textarea.error {
    border-color: #dc2626;
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
    transition: all 0.2s;
}

.btn-primary { background: var(--color-primary); color: white; }
.btn-primary:hover { opacity: 0.9; }
.btn-secondary { background: #e2e8f0; color: #475569; }
.btn-secondary:hover { background: #cbd5e1; }
</style>

<div class="form-card">
    <h2 style="margin-bottom: 24px;">📋 إنشاء استبيان جديد</h2>

    @if ($errors->any())
        <div style="background: #fee2e2; border: 2px solid #dc2626; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
            <h4 style="color: #dc2626; margin-bottom: 8px;">❌ يوجد أخطاء في النموذج:</h4>
            <ul style="margin: 0; padding-right: 20px; color: #dc2626;">
                @foreach ($errors->all() as $error)
                    {{-- إخفاء أخطاء الخيارات لأنها ستظهر تحت كل سؤال --}}
                    @if (!str_contains($error, 'الخيارات') && !str_contains($error, 'خيار'))
                        <li>{{ $error }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fee2e2; border: 2px solid #dc2626; border-radius: 8px; padding: 16px; margin-bottom: 24px; color: #dc2626;">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.surveys.store') }}" id="surveyForm">
        @csrf

        <div class="form-group">
            <label class="form-label required">عنوان الاستبيان</label>
            <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="مثال: استبيان رضا المستخدمين" required>
            @error('title')
                <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-textarea" placeholder="وصف مختصر عن الاستبيان...">{{ old('description') }}</textarea>
            @error('description')
                <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
            @enderror
        </div>

        <!-- نوع الاستبيان -->
        <div class="form-group">
            <label class="form-label required">نوع الاستبيان</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 8px;">
                <label style="display: flex; align-items: center; gap: 12px; padding: 16px; border: 3px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.3s;" id="type_general_label">
                    <input type="radio" name="survey_type" value="general" id="type_general" {{ old('survey_type', 'general') == 'general' ? 'checked' : '' }} onchange="toggleSurveyType()" style="width: 20px; height: 20px; accent-color: var(--color-primary);">
                    <div>
                        <div style="font-weight: 700; font-size: 15px; color: #1e293b;">📋 استبيان عام</div>
                        <div style="font-size: 13px; color: #64748b; margin-top: 4px;">استبيان تقليدي يُعرض مرة واحدة</div>
                    </div>
                </label>
                <label style="display: flex; align-items: center; gap: 12px; padding: 16px; border: 3px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.3s;" id="type_assessment_label">
                    <input type="radio" name="survey_type" value="pre_post_assessment" id="type_assessment" {{ old('survey_type') == 'pre_post_assessment' ? 'checked' : '' }} onchange="toggleSurveyType()" style="width: 20px; height: 20px; accent-color: #8b5cf6;">
                    <div>
                        <div style="font-weight: 700; font-size: 15px; color: #1e293b;">📊 تقييم قبلي وبعدي</div>
                        <div style="font-size: 13px; color: #64748b; margin-top: 4px;">يقيّم الطالب قبل وبعد الدرس مع مقارنة</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- مبدّل هدف التقييم: درس أو قيمة (يظهر فقط عند اختيار التقييم) -->
        <div class="form-group" id="assessmentTargetSection" style="display: none;">
            <label class="form-label required">ربط التقييم بـ</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 8px;">
                <label style="display: flex; align-items: center; gap: 12px; padding: 16px; border: 3px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.3s;" id="target_lesson_label">
                    <input type="radio" name="assessment_target" value="lesson" id="target_lesson" {{ old('assessment_target', 'lesson') == 'lesson' ? 'checked' : '' }} onchange="toggleAssessmentTarget()" style="width: 20px; height: 20px; accent-color: #8b5cf6;">
                    <div>
                        <div style="font-weight: 700; font-size: 15px; color: #1e293b;">📚 درس</div>
                        <div style="font-size: 13px; color: #64748b; margin-top: 4px;">يرتبط التقييم بدرس محدد</div>
                    </div>
                </label>
                <label style="display: flex; align-items: center; gap: 12px; padding: 16px; border: 3px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.3s;" id="target_value_label">
                    <input type="radio" name="assessment_target" value="value" id="target_value" {{ old('assessment_target') == 'value' ? 'checked' : '' }} onchange="toggleAssessmentTarget()" style="width: 20px; height: 20px; accent-color: #8b5cf6;">
                    <div>
                        <div style="font-weight: 700; font-size: 15px; color: #1e293b;">⭐ قيمة</div>
                        <div style="font-size: 13px; color: #64748b; margin-top: 4px;">يرتبط التقييم بقيمة كاملة (كل دروسها)</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- اختيار الدرس (يظهر فقط عند اختيار التقييم بدرس) -->
        <div class="form-group" id="lessonSection" style="display: none;">
            <div style="background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); padding: 20px; border-radius: 12px; border: 3px solid #8b5cf6; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15);">
                <label class="form-label required" style="color: #5b21b6; font-size: 16px; margin-bottom: 12px; display: block;">📚 الدرس المرتبط بالتقييم</label>
                <select name="lesson_id" id="lesson_id" class="form-select" style="width: 100%; padding: 14px 16px; border: 2px solid #c4b5fd; border-radius: 10px; font-size: 14px; background: white;">
                    <option value="">-- اختر الدرس --</option>
                    @foreach($lessons as $lesson)
                        <option value="{{ $lesson->id }}" {{ old('lesson_id') == $lesson->id ? 'selected' : '' }}>
                            {{ $lesson->title }}
                            @if($lesson->concept)
                                - {{ $lesson->concept->name }}
                                @if($lesson->concept->value)
                                    ({{ $lesson->concept->value->name }})
                                @endif
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('lesson_id')
                    <span style="color: #dc2626; font-size: 13px; display: block; margin-top: 8px;">{{ $message }}</span>
                @enderror
                <div style="margin-top: 14px; padding: 12px; background: rgba(255,255,255,0.7); border-radius: 8px;">
                    <p style="margin: 0; font-size: 13px; color: #6d28d9; line-height: 1.8;">
                        <strong>💡 كيف يعمل التقييم القبلي والبعدي:</strong><br>
                        ✅ سيتم إنشاء <strong>استبيانين تلقائياً</strong> (قبلي + بعدي) بنفس الأسئلة<br>
                        ✅ الاستبيان القبلي يظهر <strong>قبل بدء الدرس</strong><br>
                        ✅ الاستبيان البعدي يظهر <strong>بعد إتمام الدرس</strong><br>
                        ✅ يمكنك عرض <strong>تقرير المقارنة</strong> لمعرفة مدى تحسن الطلاب
                    </p>
                </div>
            </div>
        </div>

        <!-- اختيار القيمة (يظهر فقط عند اختيار التقييم بقيمة) -->
        <div class="form-group" id="valueSection" style="display: none;">
            <div style="background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); padding: 20px; border-radius: 12px; border: 3px solid #8b5cf6; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15);">
                <label class="form-label required" style="color: #5b21b6; font-size: 16px; margin-bottom: 12px; display: block;">⭐ القيمة المرتبطة بالتقييم</label>
                <select name="value_id" id="value_id" class="form-select" style="width: 100%; padding: 14px 16px; border: 2px solid #c4b5fd; border-radius: 10px; font-size: 14px; background: white;">
                    <option value="">-- اختر القيمة --</option>
                    @foreach($values as $value)
                        <option value="{{ $value->id }}" {{ old('value_id') == $value->id ? 'selected' : '' }}>
                            {{ $value->icon }} {{ $value->name }}
                        </option>
                    @endforeach
                </select>
                @error('value_id')
                    <span style="color: #dc2626; font-size: 13px; display: block; margin-top: 8px;">{{ $message }}</span>
                @enderror
                <div style="margin-top: 14px; padding: 12px; background: rgba(255,255,255,0.7); border-radius: 8px;">
                    <p style="margin: 0; font-size: 13px; color: #6d28d9; line-height: 1.8;">
                        <strong>💡 كيف يعمل التقييم القبلي والبعدي للقيمة:</strong><br>
                        ✅ سيتم إنشاء <strong>استبيانين تلقائياً</strong> (قبلي + بعدي) بنفس الأسئلة<br>
                        ✅ الاستبيان القبلي يظهر <strong>عند بدء الطالب أوّل درس في القيمة</strong><br>
                        ✅ الاستبيان البعدي يظهر <strong>عند إتقان الطالب كل دروس القيمة</strong><br>
                        ✅ يمكنك عرض <strong>تقرير المقارنة</strong> لمعرفة مدى تحسن الطلاب
                    </p>
                </div>
            </div>
        </div>

        <div class="form-group" id="targetSection">
            <label class="form-label required">المستهدفون</label>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" name="target_type[]" value="schools" id="target_schools" {{ in_array('schools', old('target_type', [])) ? 'checked' : '' }}>
                    <label for="target_schools">🏫 المدارس</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="target_type[]" value="teachers" id="target_teachers" {{ in_array('teachers', old('target_type', [])) ? 'checked' : '' }}>
                    <label for="target_teachers">👨‍🏫 المعلمين</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="target_type[]" value="students" id="target_students" {{ in_array('students', old('target_type', [])) ? 'checked' : '' }}>
                    <label for="target_students">🎓 الطلاب</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="target_type[]" value="parents" id="target_parents" {{ in_array('parents', old('target_type', [])) ? 'checked' : '' }}>
                    <label for="target_parents">👪 أولياء الأمور</label>
                </div>
            </div>
            @error('target_type')
                <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
            @enderror
            <span id="target_type_error" style="color: #dc2626; font-size: 13px; display: none;">يجب اختيار مستهدف واحد على الأقل</span>
        </div>

        <div class="form-group">
            <label class="form-label required">متى يظهر الاستبيان</label>
            <select name="trigger_type" class="form-select" required>
                <option value="on_platform_open" {{ old('trigger_type') == 'on_platform_open' ? 'selected' : '' }}>عند فتح المنصة</option>
                <option value="on_login" {{ old('trigger_type') == 'on_login' ? 'selected' : '' }}>عند تسجيل الدخول</option>
                <option value="on_first_login" {{ old('trigger_type') == 'on_first_login' ? 'selected' : '' }}>عند أول تسجيل دخول</option>
                <option value="on_lesson_start" {{ old('trigger_type') == 'on_lesson_start' ? 'selected' : '' }}>عند بدء الدرس</option>
                <option value="on_lesson_complete" {{ old('trigger_type') == 'on_lesson_complete' ? 'selected' : '' }}>عند إتمام الدرس</option>
                <option value="on_activity_complete" {{ old('trigger_type') == 'on_activity_complete' ? 'selected' : '' }}>عند إتمام النشاط</option>
                <option value="manual" {{ old('trigger_type') == 'manual' ? 'selected' : '' }}>يدوي (يظهر عند فتح الرابط مباشرة)</option>
            </select>
            @error('trigger_type')
                <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 20px; border-radius: 12px; border: 3px solid #f59e0b; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <input type="hidden" name="requires_login" value="0">
                    <input type="checkbox" name="requires_login" id="requires_login" value="1" {{ old('requires_login', 1) ? 'checked' : '' }} style="width: 24px; height: 24px; cursor: pointer; accent-color: #f59e0b; margin-top: 2px;">
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
                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
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
            <span id="questions_error" style="color: #dc2626; font-size: 13px; display: none;">يجب إضافة سؤال واحد على الأقل</span>
            <button type="button" class="btn-add-question" onclick="addQuestion()">➕ إضافة سؤال</button>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="submitBtn">💾 حفظ الاستبيان</button>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

<script>
let questionIndex = 0;

function addQuestion() {
    const container = document.getElementById('questionsContainer');
    const questionDiv = document.createElement('div');
    questionDiv.className = 'question-item';
    questionDiv.id = `question-${questionIndex}`;
    questionDiv.dataset.index = questionIndex;
    
    questionDiv.innerHTML = `
        <div class="question-header">
            <span class="question-number">سؤال #${getQuestionCount() + 1}</span>
            <button type="button" class="btn-remove-question" onclick="removeQuestion(${questionIndex})">🗑️ حذف</button>
        </div>
        
        <div class="form-group">
            <label class="form-label required">نص السؤال</label>
            <input type="text" name="questions[${questionIndex}][question_text]" class="form-input" required placeholder="اكتب السؤال هنا...">
        </div>
        
        <div class="form-group">
            <label class="form-label required">نوع السؤال</label>
            <select name="questions[${questionIndex}][question_type]" class="form-select" onchange="updateQuestionType(${questionIndex})" required>
                <option value="text">نص</option>
                <option value="textarea">نص طويل (توصيل)</option>
                <option value="email">بريد إلكتروني</option>
                <option value="phone">رقم جوال</option>
                <option value="select">قائمة منسدلة</option>
                <option value="radio">اختيار واحد (Radio)</option>
                <option value="checkbox">اختيار متعدد (Checkbox)</option>
                <option value="rating">تقييم نجوم (1-5)</option>
                <option value="scale">مقياس رقمي (1-10)</option>
            </select>
        </div>
        
        <div class="form-group" id="options-${questionIndex}" style="display: none;">
            <label class="form-label">الخيارات</label>
            <div class="options-input" id="optionsContainer-${questionIndex}">
                <div class="option-item">
                    <input type="text" name="questions[${questionIndex}][options][]" placeholder="الخيار الأول" class="form-input">
                    <input type="number" name="questions[${questionIndex}][option_scores][]" placeholder="الدرجة" class="form-input option-score-input" min="0" title="درجة هذا الخيار">
                    <button type="button" class="btn-remove-option" onclick="this.parentElement.remove()">×</button>
                </div>
            </div>
            <button type="button" class="btn-add-option" onclick="addOption(${questionIndex})">➕ إضافة خيار</button>
            <div class="error-message" id="options-error-${questionIndex}" style="display: none;"></div>
        </div>
        
        <div class="checkbox-item">
            <input type="checkbox" name="questions[${questionIndex}][is_required]" id="required-${questionIndex}" value="1">
            <label for="required-${questionIndex}">مطلوب</label>
        </div>
        
        <input type="hidden" name="questions[${questionIndex}][order]" value="${questionIndex}">
    `;
    
    container.appendChild(questionDiv);
    questionIndex++;
    
    // إخفاء رسالة الخطأ إذا تم إضافة سؤال
    document.getElementById('questions_error').style.display = 'none';
}

function addQuestionWithData(questionData, index) {
    const container = document.getElementById('questionsContainer');
    const questionDiv = document.createElement('div');
    questionDiv.className = 'question-item';
    questionDiv.id = `question-${index}`;
    questionDiv.dataset.index = index;
    
    const optionsHtml = (questionData.options && questionData.options.length > 0) 
        ? questionData.options.map((opt, optIdx) => {
            const scoreVal = (questionData.option_scores && questionData.option_scores[optIdx]) ? questionData.option_scores[optIdx] : '';
            return `
            <div class="option-item">
                <input type="text" name="questions[${index}][options][]" placeholder="خيار" class="form-input" value="${opt || ''}">
                <input type="number" name="questions[${index}][option_scores][]" placeholder="الدرجة" class="form-input option-score-input" min="0" value="${scoreVal}" title="درجة هذا الخيار">
                <button type="button" class="btn-remove-option" onclick="this.parentElement.remove()">×</button>
            </div>
        `}).join('')
        : `<div class="option-item">
            <input type="text" name="questions[${index}][options][]" placeholder="الخيار الأول" class="form-input">
            <input type="number" name="questions[${index}][option_scores][]" placeholder="الدرجة" class="form-input option-score-input" min="0" title="درجة هذا الخيار">
            <button type="button" class="btn-remove-option" onclick="this.parentElement.remove()">×</button>
        </div>`;
    
    const showOptions = ['select', 'radio', 'checkbox'].includes(questionData.question_type || '');
    
    questionDiv.innerHTML = `
        <div class="question-header">
            <span class="question-number">سؤال #${container.children.length + 1}</span>
            <button type="button" class="btn-remove-question" onclick="removeQuestion(${index})">🗑️ حذف</button>
        </div>
        
        <div class="form-group">
            <label class="form-label required">نص السؤال</label>
            <input type="text" name="questions[${index}][question_text]" class="form-input" required placeholder="اكتب السؤال هنا..." value="${questionData.question_text || ''}">
        </div>
        
        <div class="form-group">
            <label class="form-label required">نوع السؤال</label>
            <select name="questions[${index}][question_type]" class="form-select" onchange="updateQuestionType(${index})" required>
                <option value="text" ${questionData.question_type === 'text' ? 'selected' : ''}>نص</option>
                <option value="textarea" ${questionData.question_type === 'textarea' ? 'selected' : ''}>نص طويل (توصيل)</option>
                <option value="email" ${questionData.question_type === 'email' ? 'selected' : ''}>بريد إلكتروني</option>
                <option value="phone" ${questionData.question_type === 'phone' ? 'selected' : ''}>رقم جوال</option>
                <option value="select" ${questionData.question_type === 'select' ? 'selected' : ''}>قائمة منسدلة</option>
                <option value="radio" ${questionData.question_type === 'radio' ? 'selected' : ''}>اختيار واحد (Radio)</option>
                <option value="checkbox" ${questionData.question_type === 'checkbox' ? 'selected' : ''}>اختيار متعدد (Checkbox)</option>
                <option value="rating" ${questionData.question_type === 'rating' ? 'selected' : ''}>تقييم نجوم (1-5)</option>
                <option value="scale" ${questionData.question_type === 'scale' ? 'selected' : ''}>مقياس رقمي (1-10)</option>
            </select>
        </div>
        
        <div class="form-group" id="options-${index}" style="display: ${showOptions ? 'block' : 'none'};">
            <label class="form-label">الخيارات</label>
            <div class="options-input" id="optionsContainer-${index}">
                ${optionsHtml}
            </div>
            <button type="button" class="btn-add-option" onclick="addOption(${index})">➕ إضافة خيار</button>
            <div class="error-message" id="options-error-${index}" style="display: none;"></div>
        </div>
        
        <div class="checkbox-item">
            <input type="checkbox" name="questions[${index}][is_required]" id="required-${index}" value="1" ${questionData.is_required ? 'checked' : ''}>
            <label for="required-${index}">مطلوب</label>
        </div>
        
        <input type="hidden" name="questions[${index}][order]" value="${questionData.order || index}">
    `;
    
    container.appendChild(questionDiv);
    if (index >= questionIndex) {
        questionIndex = index + 1;
    }
    
    // إخفاء رسالة الخطأ إذا تم إضافة سؤال
    document.getElementById('questions_error').style.display = 'none';
}

function getQuestionCount() {
    return document.querySelectorAll('.question-item').length;
}

function removeQuestion(index) {
    const questionDiv = document.getElementById(`question-${index}`);
    if (questionDiv) {
        // تأكيد الحذف إذا كان هناك سؤال واحد فقط
        if (getQuestionCount() <= 1) {
            if (!confirm('هل أنت متأكد من حذف هذا السؤال؟ سيتوجب عليك إضافة سؤال جديد.')) {
                return;
            }
        }
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

// التحقق من النموذج قبل الإرسال
document.addEventListener('DOMContentLoaded', function() {
    // إضافة سؤال افتراضي أو إعادة تحميل الأسئلة القديمة
    @if(old('questions'))
        // إعادة تحميل الأسئلة من old() values
        const oldQuestions = @json(old('questions'));
        console.log('Loading old questions:', oldQuestions);
        
        Object.keys(oldQuestions).forEach((key, index) => {
            const question = oldQuestions[key];
            addQuestionWithData(question, parseInt(key));
        });
        
        // عرض أخطاء الخيارات تحت كل سؤال
        const errors = @json($errors->messages());
        console.log('Errors:', errors);
        
        Object.keys(errors).forEach(errorKey => {
            // التحقق من أخطاء الخيارات (مثل: questions.0.options)
            const match = errorKey.match(/questions\.(\d+)\.options/);
            if (match) {
                const questionIndex = match[1];
                const errorDiv = document.getElementById(`options-error-${questionIndex}`);
                if (errorDiv) {
                    errorDiv.textContent = errors[errorKey][0];
                    errorDiv.style.display = 'block';
                    errorDiv.style.color = '#dc2626';
                    errorDiv.style.marginTop = '8px';
                    errorDiv.style.fontSize = '13px';
                }
            }
        });
    @else
        // إضافة سؤال افتراضي
        addQuestion();
    @endif
    
    const form = document.getElementById('surveyForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        let hasError = false;
        
        // التحقق من المستهدفين (checkboxes + hidden inputs)
        const targetChecked = document.querySelectorAll('input[name="target_type[]"]:checked');
        const targetHidden = document.querySelectorAll('input[type="hidden"][name="target_type[]"]');
        const targetError = document.getElementById('target_type_error');
        if (targetChecked.length === 0 && targetHidden.length === 0) {
            targetError.style.display = 'block';
            hasError = true;
        } else {
            targetError.style.display = 'none';
        }
        
        // التحقق من الأسئلة
        const questionsError = document.getElementById('questions_error');
        if (getQuestionCount() === 0) {
            questionsError.style.display = 'block';
            hasError = true;
        } else {
            questionsError.style.display = 'none';
            
            // التحقق من أن جميع الأسئلة لها نص
            const questionTexts = document.querySelectorAll('input[name*="[question_text]"]');
            questionTexts.forEach(input => {
                if (input.value.trim() === '') {
                    hasError = true;
                    input.style.borderColor = '#dc2626';
                } else {
                    input.style.borderColor = '#e2e8f0';
                }
            });
            
            // التحقق من الخيارات للأسئلة التي تتطلب خيارات
            const questions = document.querySelectorAll('.question-item');
            questions.forEach(question => {
                const questionIndex = question.dataset.index;
                const typeSelect = question.querySelector('select[name*="[question_type]"]');
                const errorDiv = question.querySelector(`#options-error-${questionIndex}`);
                
                // إخفاء رسالة الخطأ السابقة
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                }
                
                if (typeSelect && ['select', 'radio', 'checkbox'].includes(typeSelect.value)) {
                    const optionInputs = question.querySelectorAll(`input[name="questions[${questionIndex}][options][]"]`);
                    let hasValidOption = false;
                    let emptyOptionsCount = 0;
                    
                    // حذف الخيارات الفارغة قبل التحقق
                    optionInputs.forEach(optionInput => {
                        if (optionInput.value.trim() === '') {
                            emptyOptionsCount++;
                            optionInput.parentElement.remove();
                        } else {
                            hasValidOption = true;
                        }
                    });
                    
                    // إعادة التحقق من الخيارات بعد الحذف
                    const remainingOptions = question.querySelectorAll(`input[name="questions[${questionIndex}][options][]"]`);
                    
                    if (!hasValidOption || remainingOptions.length === 0) {
                        const questionNumber = question.querySelector('.question-number').textContent;
                        const errorMessage = `⚠️ ${questionNumber} يحتاج إلى خيار واحد على الأقل (من نوع ${typeSelect.selectedOptions[0].text})`;
                        
                        if (errorDiv) {
                            errorDiv.textContent = errorMessage;
                            errorDiv.style.display = 'block';
                        } else {
                            alert(errorMessage);
                        }
                        hasError = true;
                        
                        // التمرير إلى السؤال المعني
                        question.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        }
        
        if (hasError) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            // طباعة البيانات للتأكد (يمكن إزالة هذا السطر في الإنتاج)
            console.log('Form data before submit:', new FormData(form));
            
            // إذا لم يكن هناك أخطاء، نعطل الزر لمنع الإرسال المتكرر
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ جاري الحفظ...';
            submitBtn.style.opacity = '0.6';
        }
    });
});

// Toggle survey type (general vs assessment)
function toggleSurveyType() {
    const isAssessment = document.getElementById('type_assessment').checked;
    const assessmentTargetSection = document.getElementById('assessmentTargetSection');
    const lessonSection = document.getElementById('lessonSection');
    const valueSection = document.getElementById('valueSection');
    const triggerField = document.querySelector('select[name="trigger_type"]');
    const generalLabel = document.getElementById('type_general_label');
    const assessmentLabel = document.getElementById('type_assessment_label');
    const targetSection = document.getElementById('targetSection');

    // إزالة أو إضافة hidden input للمستهدفين
    let hiddenTarget = document.getElementById('hidden_target_students');

    if (isAssessment) {
        assessmentTargetSection.style.display = 'block';
        // إظهار قسم الدرس أو القيمة حسب المبدّل
        toggleAssessmentTarget();
        targetSection.style.display = 'none';
        assessmentLabel.style.borderColor = '#8b5cf6';
        assessmentLabel.style.background = '#f5f3ff';
        generalLabel.style.borderColor = '#e2e8f0';
        generalLabel.style.background = 'transparent';
        if (triggerField) {
            triggerField.closest('.form-group').style.display = 'none';
        }
        // إضافة hidden input للطلاب تلقائياً
        if (!hiddenTarget) {
            hiddenTarget = document.createElement('input');
            hiddenTarget.type = 'hidden';
            hiddenTarget.name = 'target_type[]';
            hiddenTarget.value = 'students';
            hiddenTarget.id = 'hidden_target_students';
            document.querySelector('form').appendChild(hiddenTarget);
        }
    } else {
        assessmentTargetSection.style.display = 'none';
        lessonSection.style.display = 'none';
        valueSection.style.display = 'none';
        targetSection.style.display = 'block';
        generalLabel.style.borderColor = 'var(--color-primary)';
        generalLabel.style.background = '#f0fdf4';
        assessmentLabel.style.borderColor = '#e2e8f0';
        assessmentLabel.style.background = 'transparent';
        if (triggerField) {
            triggerField.closest('.form-group').style.display = 'block';
        }
        // إزالة hidden input
        if (hiddenTarget) {
            hiddenTarget.remove();
        }
    }
    
    // إضافة/إزالة hidden trigger_type للتقييم
    let hiddenTrigger = document.getElementById('hidden_trigger_type');
    if (isAssessment) {
        if (!hiddenTrigger) {
            hiddenTrigger = document.createElement('input');
            hiddenTrigger.type = 'hidden';
            hiddenTrigger.name = 'trigger_type';
            hiddenTrigger.value = 'manual';
            hiddenTrigger.id = 'hidden_trigger_type';
            document.querySelector('form').appendChild(hiddenTrigger);
        }
    } else {
        if (hiddenTrigger) {
            hiddenTrigger.remove();
        }
    }
}

// Toggle assessment target (lesson vs value) — يعمل فقط داخل قسم التقييم
function toggleAssessmentTarget() {
    const valueRadio = document.getElementById('target_value');
    const isValue = valueRadio && valueRadio.checked;
    const lessonSection = document.getElementById('lessonSection');
    const valueSection = document.getElementById('valueSection');
    const lessonLabel = document.getElementById('target_lesson_label');
    const valueLabel = document.getElementById('target_value_label');

    if (isValue) {
        lessonSection.style.display = 'none';
        valueSection.style.display = 'block';
        valueLabel.style.borderColor = '#8b5cf6';
        valueLabel.style.background = '#f5f3ff';
        lessonLabel.style.borderColor = '#e2e8f0';
        lessonLabel.style.background = 'transparent';
    } else {
        lessonSection.style.display = 'block';
        valueSection.style.display = 'none';
        lessonLabel.style.borderColor = '#8b5cf6';
        lessonLabel.style.background = '#f5f3ff';
        valueLabel.style.borderColor = '#e2e8f0';
        valueLabel.style.background = 'transparent';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleSurveyType();
});
</script>

@endsection
