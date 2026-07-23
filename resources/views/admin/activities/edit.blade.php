@extends('layouts.admin')

@section('page-title', 'تعديل نشاط')

@section('content')
<style>
.form-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 900px;
    margin: 0 auto;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group.full-width {
    grid-column: 1 / -1;
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
    min-height: 120px;
    resize: vertical;
}

.type-selector {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 8px;
}

.type-option {
    position: relative;
}

.type-option input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.type-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.type-option input[type="radio"]:checked + .type-label {
    border-color: var(--color-primary);
    background: #f0f9ff;
}

.type-icon {
    font-size: 32px;
}

.type-name {
    font-weight: 600;
    color: #1e293b;
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

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.question-card {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.question-number {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    color: var(--color-primary);
}

.question-actions {
    display: flex;
    gap: 8px;
}

.btn-icon {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.btn-danger { background: #fee2e2; color: #991b1b; }

.question-fields {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 120px;
    gap: 12px;
}

.options-container {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.option-row {
    display: flex;
    gap: 8px;
    align-items: center;
}

.option-input {
    flex: 1;
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
}

.btn-small {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
}

.correct-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid #e2e8f0;
    background: white;
    flex-shrink: 0;
}

.correct-indicator.selected {
    background: #dcfce7;
    border-color: #16a34a;
    color: #16a34a;
    font-weight: bold;
}

.option-row img {
    margin-top: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>

<div class="form-card">
    <h2 style="margin-bottom: 24px;">✏️ تعديل نشاط: {{ $activity->title }}</h2>

    <form method="POST" action="{{ route('admin.activities.update', $activity) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label required">الدرس</label>
                <select name="lesson_id" class="form-select" required>
                    <option value="">اختر الدرس</option>
                    @foreach($lessons as $lesson)
                    <option value="{{ $lesson->id }}" {{ old('lesson_id', $activity->lesson_id) == $lesson->id ? 'selected' : '' }}>
                        {{ $lesson->concept?->value?->icon }} {{ $lesson->concept?->name }} - {{ $lesson->title }}
                    </option>
                    @endforeach
                </select>
                @error('lesson_id')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label required">عنوان النشاط</label>
                <input type="text" name="title" class="form-input" value="{{ old('title', $activity->title) }}" required>
                @error('title')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label">الوصف (يدعم تنسيقاً غنياً + إدراج صورة)</label>
                {{-- محرّر نصوص غنيّ موحّد — يُحمّل الوصف القديم تلقائياً عند التعديل --}}
                <div data-rich-editor="activityDesc" data-target="descriptionHidden" dir="rtl" hidden>{!! safe_html(old('description', $activity->description)) !!}</div>
                <textarea name="description" id="descriptionHidden" rows="6" dir="rtl" style="width:100%; min-height:150px; padding:12px 14px; border:2px solid #e2e8f0; border-radius:10px; font-family:inherit; font-size:15px; line-height:1.8; box-sizing:border-box;">{!! safe_html(old('description', $activity->description)) !!}</textarea>
                @error('description')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label required">نوع النشاط</label>
                <div class="type-selector">
                    <div class="type-option">
                        <input type="radio" name="type" value="quiz" id="type_quiz" {{ old('type', $activity->type) == 'quiz' ? 'checked' : '' }} required onchange="handleTypeChange()">
                        <label for="type_quiz" class="type-label">
                            <span class="type-icon">📋</span>
                            <span class="type-name">اختبار</span>
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="type" value="exercise" id="type_exercise" {{ old('type', $activity->type) == 'exercise' ? 'checked' : '' }} onchange="handleTypeChange()">
                        <label for="type_exercise" class="type-label">
                            <span class="type-icon">✍️</span>
                            <span class="type-name">تمرين</span>
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="type" value="project" id="type_project" {{ old('type', $activity->type) == 'project' ? 'checked' : '' }} onchange="handleTypeChange()">
                        <label for="type_project" class="type-label">
                            <span class="type-icon">🎨</span>
                            <span class="type-name">مشروع</span>
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="type" value="image_order" id="type_image_order" {{ old('type', $activity->type) == 'image_order' ? 'checked' : '' }} onchange="handleTypeChange()">
                        <label for="type_image_order" class="type-label">
                            <span class="type-icon">🖼️</span>
                            <span class="type-name">ترتيب صور</span>
                        </label>
                    </div>
                    {{-- نشاط قديم بنوع خارج الأربعة أعلاه: نعرض نوعه الحاليّ محدَّداً كي لا يمنع الحقلُ
                         المطلوب (radio) حفظَ التعديل (كان يُفتَح بلا اختيار → يُحبَط الحفظ). --}}
                    @php $__adminActivityTypes = ['quiz', 'exercise', 'project', 'image_order']; @endphp
                    @if($activity->type && ! in_array($activity->type, $__adminActivityTypes, true))
                    <div class="type-option">
                        <input type="radio" name="type" value="{{ $activity->type }}" id="type_current" {{ old('type', $activity->type) == $activity->type ? 'checked' : '' }} onchange="handleTypeChange()">
                        <label for="type_current" class="type-label">
                            <span class="type-icon">🔧</span>
                            <span class="type-name">{{ $activity->type }}</span>
                        </label>
                    </div>
                    @endif
                </div>
                @error('type')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <!-- حقول خاصة بالاختبار -->
            <div class="quiz-fields" style="display: none; grid-column: 1 / -1;">
                <div style="background: #f0f9ff; border: 2px solid #3b82f6; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <h3 style="margin: 0 0 16px 0; color: #1e40af; display: flex; align-items: center; gap: 8px;">
                        ⏱️ إعدادات الاختبار
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">مدة الاختبار (بالدقائق)</label>
                            <input type="number" name="quiz_duration" class="form-input" value="{{ old('quiz_duration', $activity->quiz_duration ?? 30) }}" min="1" placeholder="30">
                            <small style="color: #64748b; font-size: 13px;">اترك فارغاً لاختبار بدون وقت محدد</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">عدد المحاولات المسموحة</label>
                            <input type="number" name="max_attempts" class="form-input" value="{{ old('max_attempts', $activity->max_attempts ?? 3) }}" min="1" placeholder="3">
                            <small style="color: #64748b; font-size: 13px;">عدد مرّات محاولة الطالب لهذا النشاط (الافتراضي 3).</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- حقول خاصة بالمشروع -->
            <div class="project-fields" style="display: none; grid-column: 1 / -1;">
                <div style="background: #fef3c7; border: 2px solid #f59e0b; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <h3 style="margin: 0 0 16px 0; color: #92400e; display: flex; align-items: center; gap: 8px;">
                        📁 إعدادات المشروع
                    </h3>
                    <div class="form-group full-width">
                        <label class="form-label">أنواع الملفات المسموحة</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 8px;">
                            @php
                                $allowedTypes = old('allowed_file_types', $activity->allowed_file_types ?? []);
                                // تحويل إلى array إذا كان string
                                if (is_string($allowedTypes)) {
                                    $allowedTypes = json_decode($allowedTypes, true) ?? [];
                                }
                                if (!is_array($allowedTypes)) {
                                    $allowedTypes = [];
                                }
                            @endphp
                            <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="allowed_file_types[]" value="document" {{ in_array('document', $allowedTypes) ? 'checked' : '' }}>
                                <span>📄 مستندات (PDF, Word, Excel)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="allowed_file_types[]" value="image" {{ in_array('image', $allowedTypes) ? 'checked' : '' }}>
                                <span>🖼️ صور (JPG, PNG, GIF)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="allowed_file_types[]" value="video" {{ in_array('video', $allowedTypes) ? 'checked' : '' }}>
                                <span>🎥 فيديو (MP4, AVI, MOV)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="allowed_file_types[]" value="audio" {{ in_array('audio', $allowedTypes) ? 'checked' : '' }}>
                                <span>🎵 صوت (MP3, WAV, AAC)</span>
                            </label>
                        </div>
                        <small style="color: #64748b; font-size: 13px; display: block; margin-top: 8px;">اختر أنواع الملفات التي يمكن للطلاب رفعها</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">الحد الأقصى لحجم الملف (MB)</label>
                        <input type="number" name="max_file_size" class="form-input" value="{{ old('max_file_size', $activity->max_file_size ?? 10) }}" min="1" max="100" placeholder="10">
                    </div>
                </div>
            </div>

            <div class="form-group full-width questions-section">
                <label class="form-label">الأسئلة</label>
                <div id="questionsBuilder">
                    <div class="questions-list" id="questionsList"></div>
                    <button type="button" class="btn btn-secondary" onclick="addQuestion()" style="margin-top: 16px;">➕ إضافة سؤال</button>
                </div>
                <input type="hidden" name="questions" id="questionsJson" value="{{ old('questions', json_encode($activity->questions, JSON_UNESCAPED_UNICODE)) }}">
                @error('questions')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">النقاط المكتسبة</label>
                <input type="number" name="points" class="form-input" value="{{ old('points', $activity->points) }}" min="0">
                @error('points')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">درجة النجاح (%)</label>
                <input type="number" name="passing_score" class="form-input" value="{{ old('passing_score', $activity->passing_score) }}" min="0" max="100">
                @error('passing_score')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label style="display:flex;align-items:center;gap:12px;padding:16px;background:#fffbeb;border:2px solid #f59e0b;border-radius:10px;cursor:pointer;">
                    <input type="checkbox" name="manual_review" value="1" {{ old('manual_review', $activity->manual_review) ? 'checked' : '' }} style="width:20px;height:20px;cursor:pointer;accent-color:#f59e0b;">
                    <span>
                        <span style="font-weight:700;color:#92400e;">👨‍🏫 يتطلب موافقة/تصحيح المعلم يدوياً</span>
                        <small style="display:block;color:#a16207;font-size:13px;margin-top:2px;">عند تفعيله لا يُصحَّح النشاط آلياً — يذهب تسليم الطالب للمعلم لاعتماد الدرجة</small>
                    </span>
                </label>
            </div>

            <div class="form-group full-width">
                <label style="display:flex;align-items:center;gap:12px;padding:16px;background:#eef2ff;border:2px solid #6366f1;border-radius:10px;cursor:pointer;">
                    <input type="checkbox" name="requires_parent_approval" value="1" {{ old('requires_parent_approval', $activity->requires_parent_approval ?? false) ? 'checked' : '' }} style="width:20px;height:20px;cursor:pointer;accent-color:#6366f1;">
                    <span>
                        <span style="font-weight:700;color:#3730a3;">👪 يتطلب اطّلاع وموافقة وليّ الأمر</span>
                        <small style="display:block;color:#4338ca;font-size:13px;margin-top:2px;">عند تفعيله لا ينتقل التسليم للمعلّم إلا بعد موافقة وليّ الأمر (ويأخذ الوليّ نقاطاً على موافقته)</small>
                    </span>
                </label>
            </div>

            <div class="form-group">
                <label class="form-label">الترتيب</label>
                <input type="number" name="order" class="form-input" value="{{ old('order', $activity->order) }}" min="0">
                @error('order')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label required">الحالة</label>
                <select name="status" class="form-select" required>
                    <option value="active" {{ old('status', $activity->status) == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status', $activity->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
                @error('status')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- الوسائط المتعددة (اختياري) — الحالية + إضافة جديد، تظهر للطالب داخل النشاط --}}
        @include('activities.partials.media-upload-form', ['activity' => $activity])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 تحديث النشاط</button>
            <a href="{{ route('admin.activities.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

<script>
let questions = [];

// تهريب القيم داخل سمات HTML — يمنع كسر السمة وفقدان/تشويه البيانات عند إعادة الرسم
function escAttr(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
        .replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// مزامنة نهائية قبل الإرسال: أطلق change المعلّق (blur) ثم أعد بناء JSON — يعالج مسار Enter
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action*="activities"]');
    if (form) {
        form.addEventListener('submit', function () {
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
            updateJson();
        });
    }
});

// Load existing questions
const questionsData = document.getElementById('questionsJson').value;
if (questionsData) {
    try {
        let parsed = JSON.parse(questionsData);
        
        // Convert to array if it's an object
        if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
            parsed = Object.values(parsed);
        }
        
        // Ensure it's an array
        if (Array.isArray(parsed)) {
            questions = parsed.map(q => {
                const opts = Array.isArray(q.options) ? q.options : (q.options ? Object.values(q.options) : ['', '']);
                const answerText = q.answer || q.correct_answer || '';
                // استنتاج correct_index من نص الإجابة إن لم يكن مخزّناً
                let correctIdx = q.correct_index;
                if ((correctIdx === undefined || correctIdx === null) && answerText !== '') {
                    const idx = opts.findIndex(o => o === answerText);
                    if (idx >= 0) correctIdx = idx;
                }
                // نحافظ على كل مفاتيح السؤال الأصلية (word / hint / target_word / items ...)
                // ونُحدِّث الحقول القانونية فقط — كي لا تُفقَد الكلمة الهدف لأسئلة اختيار الحروف.
                const mapped = {
                    ...q,
                    type: q.type || 'multiple_choice',
                    question: q.question || '',
                    options: opts,
                    answer: answerText,
                    correct_index: correctIdx,
                    points: q.points || 10
                };
                // اختيار الحروف: الإجابة هي الكلمة (word) لا حرف مفرد — نُلغِ مؤشّر الحرف المضلِّل
                if (mapped.type === 'letter_choice') {
                    mapped.correct_index = null;
                    delete mapped.answer;
                }
                // Preserve images array for image_order questions
                if (q.type === 'image_order' && Array.isArray(q.images)) {
                    mapped.images = q.images;
                    delete mapped.options;
                }
                return mapped;
            });
            renderQuestions();
        }
    } catch (e) {
        console.error('Error parsing questions:', e);
        questions = [];
    }
}

// إظهار/إخفاء الحقول حسب نوع النشاط
function handleTypeChange() {
    const checked = document.querySelector('input[name="type"]:checked');
    if (!checked) return; // نشاط قديم بلا نوع محدَّد — لا نرمي TypeError
    const type = checked.value;
    const quizFields = document.querySelector('.quiz-fields');
    const projectFields = document.querySelector('.project-fields');
    const questionsSection = document.querySelector('.questions-section');
    
    // إخفاء جميع الحقول أولاً
    if (quizFields) quizFields.style.display = 'none';
    if (projectFields) projectFields.style.display = 'none';
    if (questionsSection) questionsSection.style.display = 'none';
    
    // إظهار الحقول المناسبة
    if (type === 'quiz' || type === 'exercise' || type === 'image_order') {
        if (questionsSection) questionsSection.style.display = 'block';
        if (type === 'quiz' && quizFields) {
            quizFields.style.display = 'block';
        }
    } else if (type === 'project') {
        if (projectFields) projectFields.style.display = 'block';
    }
}

// تنفيذ عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    handleTypeChange();
});

function addQuestion() {
    questions.push({
        type: 'multiple_choice',
        question: '',
        options: ['', ''],
        answer: '',
        points: 10
    });
    renderQuestions();
}

function removeQuestion(index) {
    if (confirm('هل أنت متأكد من حذف هذا السؤال؟')) {
        questions.splice(index, 1);
        renderQuestions();
    }
}

function addOption(index) {
    if (!questions[index].options) {
        questions[index].options = [];
    }
    questions[index].options.push('');
    renderQuestions();
}

function removeOption(qIndex, oIndex) {
    if (questions[qIndex].options.length > 2) {
        questions[qIndex].options.splice(oIndex, 1);
        renderQuestions();
    } else {
        alert('يجب أن يكون هناك خيارين على الأقل');
    }
}

function updateOption(qIndex, oIndex, value) {
    questions[qIndex].options[oIndex] = value;
    updateJson();
}

function setCorrectAnswer(qIndex, answer) {
    const currentAnswer = questions[qIndex].options[answer];
    // نخزّن الاثنين: نص الخيار (للتوافق الخلفي) + الدليل (المعتمد في التصحيح)
    questions[qIndex].answer = currentAnswer;
    questions[qIndex].correct_index = answer;
    renderQuestions();
}

function renderQuestions() {
    const container = document.getElementById('questionsList');
    container.innerHTML = '';
    
    questions.forEach((q, index) => {
        const card = document.createElement('div');
        card.className = 'question-card';
        
        let optionsHtml = '';
        
        // أنواع الترتيب لا تُظهر مؤشّر «إجابة صحيحة» — اختيار الحروف منها (ترتيب حروف)
        const isOrderingType = ['word_order', 'sentence_order', 'image_order', 'letter_choice'].includes(q.type);
        
        if (q.type !== 'image_order' && q.options) {
            q.options.forEach((option, oIndex) => {
                const isCorrect = (q.correct_index !== undefined && q.correct_index !== null)
                    ? Number(q.correct_index) === oIndex
                    : (q.answer === option);
                optionsHtml += `
                    <div class="option-row">
                        ${!isOrderingType ? `
                            <div class="correct-indicator ${isCorrect ? 'selected' : ''}" 
                                 onclick="setCorrectAnswer(${index}, ${oIndex})"
                                 title="اختر كإجابة صحيحة">
                                ${isCorrect ? '✓' : '○'}
                            </div>
                        ` : `
                            <span style="width: 32px; text-align: center; font-weight: 600; color: #64748b;">${oIndex + 1}</span>
                        `}
                        <input type="text" class="option-input"
                               value="${escAttr(option)}"
                               onchange="updateOption(${index}, ${oIndex}, this.value)"
                               placeholder="${q.type === 'letter_choice' ? 'الحرف' : (q.type === 'word_order' ? 'الكلمة' : (q.type === 'sentence_order' ? 'الجملة' : 'الخيار'))} ${oIndex + 1}">
                        <button type="button" class="btn-small btn-danger" onclick="removeOption(${index}, ${oIndex})">🗑️</button>
                    </div>
                `;
            });
        }
        
        card.innerHTML = `
            <div class="question-header">
                <div class="question-number">
                    <span style="background: var(--color-primary); color: white; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 14px;">
                        ${index + 1}
                    </span>
                    <select onchange="updateQuestion(${index}, 'type', this.value)" style="padding: 8px; border: 2px solid #e2e8f0; border-radius: 6px;">
                        <option value="multiple_choice" ${q.type === 'multiple_choice' ? 'selected' : ''}>اختيار متعدد</option>
                        <option value="true_false" ${q.type === 'true_false' ? 'selected' : ''}>صح / خطأ</option>
                        <option value="short_answer" ${q.type === 'short_answer' ? 'selected' : ''}>إجابة قصيرة</option>
                        <option value="letter_choice" ${q.type === 'letter_choice' ? 'selected' : ''}>اختيار حروف</option>
                        <option value="word_order" ${q.type === 'word_order' ? 'selected' : ''}>ترتيب كلمات</option>
                        <option value="sentence_order" ${q.type === 'sentence_order' ? 'selected' : ''}>ترتيب جمل</option>
                        <option value="image_order" ${q.type === 'image_order' ? 'selected' : ''}>ترتيب صور</option>
                    </select>
                </div>
                <div class="question-actions">
                    <button type="button" class="btn-icon btn-danger" onclick="removeQuestion(${index})">🗑️ حذف</button>
                </div>
            </div>
            
            <div class="question-fields">
                <div class="field-row">
                    <input type="text" class="form-input" value="${escAttr(q.question)}"
                           onchange="updateQuestion(${index}, 'question', this.value)"
                           placeholder="نص السؤال..." required>
                    <input type="number" class="form-input" value="${q.points}"
                           onchange="updateQuestion(${index}, 'points', parseInt(this.value))"
                           placeholder="الدرجة" min="1" required>
                </div>

                ${mediaRowHtml(q, index)}

                ${q.type === 'letter_choice' ? `
                    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:8px 12px;margin-top:8px;font-size:13px;color:#0369a1;">
                        🔤 أدخل حروف الكلمة <b>بالترتيب الصحيح</b> — كل الحروف صحيحة والطالب يعيد ترتيبها لتكوين الكلمة. الكلمة المُكوَّنة: <b>${escAttr((q.options || []).map(o => (typeof o === 'string' ? o : '')).join(''))}</b>
                    </div>
                ` : ''}
                ${q.type === 'multiple_choice' || q.type === 'true_false' || q.type === 'letter_choice' ? `
                    <div class="options-container">
                        <label style="font-weight: 600; font-size: 13px; color: #475569;">
                            ${q.type === 'letter_choice' ? 'الحروف بالترتيب الصحيح' : 'الخيارات (اضغط على ○ لتحديد الإجابة الصحيحة)'}
                        </label>
                        ${optionsHtml}
                        ${q.type === 'multiple_choice' || q.type === 'letter_choice' ? `
                            <button type="button" class="btn-small btn-secondary" onclick="addOption(${index})">➕ إضافة ${q.type === 'letter_choice' ? 'حرف' : 'خيار'}</button>
                        ` : ''}
                    </div>
                ` : ''}
                
                ${q.type === 'short_answer' ? `
                    <div class="options-container">
                        <label style="font-weight: 600; font-size: 13px; color: #475569; margin-bottom: 6px; display:block;">الإجابة الصحيحة (يقارَن بها نص الطالب بعد تطبيع المسافات والتشكيل والمحارف الخفية)</label>
                        <input type="text" class="form-input" value="${escAttr(q.answer || '')}"
                               onchange="updateQuestion(${index}, 'answer', this.value)"
                               placeholder="مثال: الصلاة الوسطى" required>
                    </div>
                ` : ''}
                ${q.type === 'word_order' || q.type === 'sentence_order' ? `
                    <div class="options-container">
                        <label style="font-weight: 600; font-size: 13px; color: #475569;">
                            ${q.type === 'word_order' ? 'الكلمات (سيتم ترتيبها عشوائياً للطالب)' : 'الجمل (سيتم ترتيبها عشوائياً للطالب)'}
                        </label>
                        ${optionsHtml}
                        <button type="button" class="btn-small btn-secondary" onclick="addOption(${index})">➕ إضافة ${q.type === 'word_order' ? 'كلمة' : 'جملة'}</button>
                        <small style="color: #64748b; font-size: 13px; margin-top: 8px; display: block;">الترتيب الحالي هو الترتيب الصحيح</small>
                    </div>
                ` : ''}
                
                ${q.type === 'image_order' ? `
                    <div class="options-container">
                        <label style="font-weight: 600; font-size: 13px; color: #475569;">الصور (سيتم ترتيبها عشوائياً للطالب)</label>
                        ${q.images && q.images.length > 0 ? q.images.map((img, imgIndex) => `
                            <div class="option-row" style="align-items: start;">
                                <span style="width: 32px; text-align: center; font-weight: 600; color: #64748b;">${imgIndex + 1}</span>
                                <div style="flex: 1; display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input type="url" class="form-input" value="${escAttr(img.url || '')}"
                                               onchange="updateImageUrl(${index}, ${imgIndex}, this.value)"
                                               placeholder="رابط الصورة (URL)" style="flex: 1;">
                                        <label style="padding: 10px 16px; background: #f0f9ff; border: 2px dashed #3b82f6; border-radius: 8px; cursor: pointer; color: #1e40af; font-weight: 600; font-size: 13px; white-space: nowrap; transition: all 0.2s;"
                                               onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#f0f9ff'">
                                            📤 رفع صورة
                                            <input type="file" accept="image/*" style="display:none;" 
                                                   onchange="uploadActivityImage(this, ${index}, ${imgIndex})">
                                        </label>
                                    </div>
                                    <input type="text" class="form-input" value="${escAttr(img.description || '')}"
                                           onchange="updateImageDescription(${index}, ${imgIndex}, this.value)"
                                           placeholder="وصف الصورة (اختياري)">
                                    ${img.url ? `<img src="${escAttr(img.url)}" style="max-width: 150px; max-height: 150px; border-radius: 8px; border: 2px solid #e2e8f0;" onerror="this.style.display='none'">` : ''}
                                </div>
                                <button type="button" class="btn-small btn-danger" onclick="removeImage(${index}, ${imgIndex})">🗑️</button>
                            </div>
                        `).join('') : '<p style="color: #64748b; padding: 12px;">لا توجد صور بعد</p>'}
                        <button type="button" class="btn-small btn-secondary" onclick="addImage(${index})">➕ إضافة صورة</button>
                        <small style="color: #64748b; font-size: 13px; margin-top: 8px; display: block;">الترتيب الحالي هو الترتيب الصحيح</small>
                    </div>
                ` : ''}
                
            </div>
        `;
        
        container.appendChild(card);
    });
    
    updateJson();
}

function updateJson() {
    // اختيار الحروف: الكلمة المستهدفة = الحروف بترتيبها الحالي (كلها صحيحة، الترتيب هو المطلوب)
    questions.forEach(q => {
        if (q.type === 'letter_choice') {
            q.word = (q.options || []).map(o => (typeof o === 'string' ? o : '')).join('');
        }
    });
    document.getElementById('questionsJson').value = JSON.stringify(questions);
}

// ===== وسائط لكل سؤال (صورة/فيديو/صوت) =====
function mediaRowHtml(q, index) {
    const mtype = q.media_type || '';
    const label = mtype === 'image' ? 'الصورة' : (mtype === 'video' ? 'الفيديو' : 'الصوت');
    return `
        <div class="options-container" style="margin-top:10px;background:#fafbff;border:1px dashed #cbd5e1;border-radius:10px;padding:10px 12px;">
            <label style="font-weight:600;font-size:13px;color:#475569;display:block;margin-bottom:6px;">🎬 وسائط للسؤال (اختياري)</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <select class="form-input" style="max-width:150px;" onchange="updateQuestionMedia(${index}, 'media_type', this.value)">
                    <option value="" ${mtype === '' ? 'selected' : ''}>لا شيء</option>
                    <option value="image" ${mtype === 'image' ? 'selected' : ''}>🖼️ صورة</option>
                    <option value="video" ${mtype === 'video' ? 'selected' : ''}>🎬 فيديو</option>
                    <option value="audio" ${mtype === 'audio' ? 'selected' : ''}>🎵 صوت</option>
                </select>
                ${mtype ? `
                    <input type="url" class="form-input" style="flex:1;min-width:200px;" value="${escAttr(q.media_url || '')}"
                           onchange="updateQuestionMedia(${index}, 'media_url', this.value)"
                           placeholder="رابط ${label} (URL)">
                    ${mtype === 'image' ? `
                        <label style="padding:8px 12px;background:#f0f9ff;border:2px dashed #3b82f6;border-radius:8px;cursor:pointer;color:#1e40af;font-weight:600;font-size:12px;white-space:nowrap;">
                            📤 رفع
                            <input type="file" accept="image/*" style="display:none;" onchange="uploadQuestionImage(this, ${index})">
                        </label>` : ''}
                ` : ''}
            </div>
            ${mtype === 'image' && q.media_url ? `<img src="${escAttr(q.media_url)}" style="max-width:160px;max-height:120px;border-radius:8px;margin-top:8px;border:2px solid #e2e8f0;" onerror="this.style.display='none'">` : ''}
            ${mtype === 'video' && q.media_url ? `<video src="${escAttr(q.media_url)}" controls style="max-width:220px;max-height:130px;border-radius:8px;margin-top:8px;"></video>` : ''}
            ${mtype === 'audio' && q.media_url ? `<audio src="${escAttr(q.media_url)}" controls style="margin-top:8px;width:100%;"></audio>` : ''}
        </div>
    `;
}

function updateQuestionMedia(index, field, value) {
    questions[index][field] = value;
    if (field === 'media_type' && !value) {
        delete questions[index].media_url;
    }
    renderQuestions();
}

function uploadQuestionImage(input, index) {
    var file = input.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) { alert('يرجى اختيار ملف صورة فقط'); return; }
    if (file.size > 5 * 1024 * 1024) { alert('حجم الصورة يجب أن يكون أقل من 5 ميجابايت'); return; }
    var fd = new FormData();
    fd.append('image', file);
    fd.append('_token', '{{ csrf_token() }}');
    fetch('{{ route("admin.activities.upload-image") }}', {
        method: 'POST',
        body: fd,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(function (r) { if (!r.ok) throw new Error('فشل رفع الصورة'); return r.json(); })
    .then(function (d) { questions[index].media_url = d.url ? new URL(d.url, window.location.origin).href : ''; renderQuestions(); })
    .catch(function (e) { alert('حدث خطأ أثناء الرفع: ' + e.message); });
}

// Handle type changes
function updateQuestion(index, field, value) {
    const oldType = questions[index].type;
    questions[index][field] = value;
    
    // Handle type change
    if (field === 'type' && oldType !== value) {
        // Initialize based on new type
        if (value === 'true_false') {
            questions[index].options = ['صح', 'خطأ'];
            questions[index].answer = '';
        } else if (value === 'letter_choice') {
            questions[index].options = ['ص', 'ل', 'ا', 'ة'];
            delete questions[index].answer;
            delete questions[index].correct_index;
        } else if (value === 'word_order') {
            questions[index].options = ['كلمة', 'ثانية'];
            delete questions[index].answer;
        } else if (value === 'sentence_order') {
            questions[index].options = ['الجملة الأولى', 'الجملة الثانية'];
            delete questions[index].answer;
        } else if (value === 'image_order') {
            questions[index].images = [{url: '', description: ''}, {url: '', description: ''}];
            delete questions[index].options;
            delete questions[index].answer;
        } else if (value === 'multiple_choice') {
            if (!questions[index].options || questions[index].options.length < 2) {
                questions[index].options = ['', ''];
            }
            questions[index].answer = '';
        } else if (value === 'short_answer') {
            delete questions[index].options;
            questions[index].answer = '';
        }
        renderQuestions();
    }
    
    updateJson();
}

// Image handling functions
function addImage(qIndex) {
    if (!questions[qIndex].images) {
        questions[qIndex].images = [];
    }
    questions[qIndex].images.push({url: '', description: ''});
    renderQuestions();
}

function removeImage(qIndex, imgIndex) {
    if (questions[qIndex].images.length > 2) {
        questions[qIndex].images.splice(imgIndex, 1);
        renderQuestions();
    } else {
        alert('يجب أن يكون هناك صورتين على الأقل');
    }
}

function updateImageUrl(qIndex, imgIndex, value) {
    questions[qIndex].images[imgIndex].url = value;
    renderQuestions();
}

function updateImageDescription(qIndex, imgIndex, value) {
    questions[qIndex].images[imgIndex].description = value;
    updateJson();
}

function uploadActivityImage(input, qIndex, imgIndex) {
    var file = input.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('يرجى اختيار ملف صورة فقط');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        alert('حجم الصورة يجب أن يكون أقل من 5 ميجابايت');
        return;
    }

    var label = input.parentElement;
    var originalText = label.innerHTML;
    label.innerHTML = '<span style="display:flex;align-items:center;gap:6px;">⏳ جاري الرفع...</span>';
    label.style.pointerEvents = 'none';

    var formData = new FormData();
    formData.append('image', file);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route("admin.activities.upload-image") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(function(response) {
        if (!response.ok) throw new Error('فشل في رفع الصورة');
        return response.json();
    })
    .then(function(data) {
        // رابط مطلق: حقل type=url يرفض النسبيّ، ونحتاجه http(s) لواجهة الطالب/المراجعة
        questions[qIndex].images[imgIndex].url = data.url ? new URL(data.url, window.location.origin).href : '';
        renderQuestions();
    })
    .catch(function(error) {
        alert('حدث خطأ أثناء رفع الصورة: ' + error.message);
        label.innerHTML = originalText;
        label.style.pointerEvents = '';
    });
}

// وصف النشاط يُدار الآن عبر المحرّر الموحّد rich-editor.js (data-rich-editor)

</script>

@endsection
