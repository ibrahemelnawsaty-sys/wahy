@extends('layouts.admin')

@section('page-title', 'إضافة نشاط جديد')

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

.info-box {
    background: #f0f9ff;
    border: 2px solid #0284c7;
    border-radius: 8px;
    padding: 16px;
    margin-top: 8px;
}

.info-box h4 {
    color: #0369a1;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
}

.info-box pre {
    background: white;
    padding: 12px;
    border-radius: 6px;
    font-size: 12px;
    overflow-x: auto;
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
    <h2 style="margin-bottom: 24px;">🎯 إضافة نشاط جديد</h2>

    <form method="POST" action="{{ route('admin.activities.store') }}">
        @csrf

        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label required">الدرس</label>
                <select name="lesson_id" class="form-select" required>
                    <option value="">اختر الدرس</option>
                    @foreach($lessons as $lesson)
                    <option value="{{ $lesson->id }}" {{ old('lesson_id', $selectedLesson) == $lesson->id ? 'selected' : '' }}>
                        {{ $lesson->concept->value->icon }} {{ $lesson->concept->name }} - {{ $lesson->title }}
                    </option>
                    @endforeach
                </select>
                @error('lesson_id')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label required">عنوان النشاط</label>
                <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="مثال: اختبار الصدق" required>
                @error('title')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label">الوصف</label>
                <textarea name="description" class="form-textarea" placeholder="وصف النشاط...">{{ old('description') }}</textarea>
                @error('description')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label required">نوع النشاط</label>
                <div class="type-selector">
                    <div class="type-option">
                        <input type="radio" name="type" value="quiz" id="type_quiz" {{ old('type', 'quiz') == 'quiz' ? 'checked' : '' }} required onchange="handleTypeChange()">
                        <label for="type_quiz" class="type-label">
                            <span class="type-icon">📋</span>
                            <span class="type-name">اختبار</span>
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="type" value="exercise" id="type_exercise" {{ old('type') == 'exercise' ? 'checked' : '' }} onchange="handleTypeChange()">
                        <label for="type_exercise" class="type-label">
                            <span class="type-icon">✍️</span>
                            <span class="type-name">تمرين</span>
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="type" value="project" id="type_project" {{ old('type') == 'project' ? 'checked' : '' }} onchange="handleTypeChange()">
                        <label for="type_project" class="type-label">
                            <span class="type-icon">🎨</span>
                            <span class="type-name">مشروع</span>
                        </label>
                    </div>
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
                            <input type="number" name="quiz_duration" class="form-input" value="{{ old('quiz_duration', 30) }}" min="1" placeholder="30">
                            <small style="color: #64748b; font-size: 13px;">اترك فارغاً لاختبار بدون وقت محدد</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">عدد المحاولات المسموحة</label>
                            <input type="number" name="max_attempts" class="form-input" value="{{ old('max_attempts', 3) }}" min="1" placeholder="3">
                            <small style="color: #64748b; font-size: 13px;">اترك فارغاً لمحاولات غير محدودة</small>
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
                            <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="allowed_file_types[]" value="document" {{ is_array(old('allowed_file_types')) && in_array('document', old('allowed_file_types')) ? 'checked' : '' }}>
                                <span>📄 مستندات (PDF, Word, Excel)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="allowed_file_types[]" value="image" {{ is_array(old('allowed_file_types')) && in_array('image', old('allowed_file_types')) ? 'checked' : '' }}>
                                <span>🖼️ صور (JPG, PNG, GIF)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="allowed_file_types[]" value="video" {{ is_array(old('allowed_file_types')) && in_array('video', old('allowed_file_types')) ? 'checked' : '' }}>
                                <span>🎥 فيديو (MP4, AVI, MOV)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="allowed_file_types[]" value="audio" {{ is_array(old('allowed_file_types')) && in_array('audio', old('allowed_file_types')) ? 'checked' : '' }}>
                                <span>🎵 صوت (MP3, WAV, AAC)</span>
                            </label>
                        </div>
                        <small style="color: #64748b; font-size: 13px; display: block; margin-top: 8px;">اختر أنواع الملفات التي يمكن للطلاب رفعها</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">الحد الأقصى لحجم الملف (MB)</label>
                        <input type="number" name="max_file_size" class="form-input" value="{{ old('max_file_size', 10) }}" min="1" max="100" placeholder="10">
                    </div>
                </div>
            </div>

            <div class="form-group full-width questions-section">
                <label class="form-label">الأسئلة</label>
                <div id="questionsBuilder">
                    <div class="questions-list" id="questionsList"></div>
                    <button type="button" class="btn btn-secondary" onclick="addQuestion()" style="margin-top: 16px;">➕ إضافة سؤال</button>
                </div>
                <input type="hidden" name="questions" id="questionsJson" value="{{ old('questions') }}">
                @error('questions')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">النقاط المكتسبة</label>
                <input type="number" name="points" class="form-input" value="{{ old('points', 20) }}" min="0" placeholder="20">
                @error('points')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">درجة النجاح (%)</label>
                <input type="number" name="passing_score" class="form-input" value="{{ old('passing_score', 70) }}" min="0" max="100" placeholder="70">
                <small style="color: #64748b; font-size: 13px;">النسبة المطلوبة لاجتياز النشاط</small>
                @error('passing_score')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">الترتيب</label>
                <input type="number" name="order" class="form-input" value="{{ old('order', 0) }}" min="0" placeholder="0">
                <small style="color: #64748b; font-size: 13px;">0 = ترتيب تلقائي</small>
                @error('order')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
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
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 حفظ النشاط</button>
            <a href="{{ route('admin.activities.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

<script>
let questions = [];

// Load old questions if exists
const oldQuestions = document.getElementById('questionsJson').value;
if (oldQuestions) {
    try {
        questions = JSON.parse(oldQuestions);
        renderQuestions();
    } catch (e) {
        console.error('Error parsing old questions:', e);
    }
}

// إظهار/إخفاء الحقول حسب نوع النشاط
function handleTypeChange() {
    const type = document.querySelector('input[name="type"]:checked').value;
    const quizFields = document.querySelector('.quiz-fields');
    const projectFields = document.querySelector('.project-fields');
    const questionsSection = document.querySelector('.questions-section');
    
    // إخفاء جميع الحقول أولاً
    if (quizFields) quizFields.style.display = 'none';
    if (projectFields) projectFields.style.display = 'none';
    if (questionsSection) questionsSection.style.display = 'none';
    
    // إظهار الحقول المناسبة
    if (type === 'quiz' || type === 'exercise') {
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
        
        // For ordering questions, don't show correct indicator
        const isOrderingType = ['word_order', 'sentence_order', 'image_order'].includes(q.type);
        
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
                               value="${option}" 
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
                    <input type="text" class="form-input" value="${q.question}" 
                           onchange="updateQuestion(${index}, 'question', this.value)"
                           placeholder="نص السؤال..." required>
                    <input type="number" class="form-input" value="${q.points}" 
                           onchange="updateQuestion(${index}, 'points', parseInt(this.value))"
                           placeholder="الدرجة" min="1" required>
                </div>
                
                ${q.type === 'letter_choice' ? `
                    <div class="field-row" style="margin-top:10px;">
                        <input type="text" class="form-input" value="${q.word || ''}"
                               onchange="updateQuestion(${index}, 'word', this.value)"
                               placeholder="الكلمة المستهدفة (مثال: صلاة)" required>
                    </div>
                ` : ''}
                ${q.type === 'multiple_choice' || q.type === 'true_false' || q.type === 'letter_choice' ? `
                    <div class="options-container">
                        <label style="font-weight: 600; font-size: 13px; color: #475569;">
                            ${q.type === 'letter_choice' ? 'الحروف (اضغط على ○ لتحديد الإجابة الصحيحة)' : 'الخيارات (اضغط على ○ لتحديد الإجابة الصحيحة)'}
                        </label>
                        ${optionsHtml}
                        ${q.type === 'multiple_choice' || q.type === 'letter_choice' ? `
                            <button type="button" class="btn-small btn-secondary" onclick="addOption(${index})">➕ إضافة ${q.type === 'letter_choice' ? 'حرف' : 'خيار'}</button>
                        ` : ''}
                    </div>
                ` : ''}
                
                ${q.type === 'short_answer' ? `
                    <div class="options-container">
                        <label style="font-weight: 600; font-size: 13px; color: #475569; margin-bottom: 6px; display:block;">الإجابة الصحيحة (يقارَن بها نص الطالب بعد تطبيع المسافات والتشكيل)</label>
                        <input type="text" class="form-input" value="${q.answer || ''}"
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
                                        <input type="url" class="form-input" value="${img.url || ''}" 
                                               onchange="updateImageUrl(${index}, ${imgIndex}, this.value)"
                                               placeholder="رابط الصورة (URL)" style="flex: 1;">
                                        <label style="padding: 10px 16px; background: #f0f9ff; border: 2px dashed #3b82f6; border-radius: 8px; cursor: pointer; color: #1e40af; font-weight: 600; font-size: 13px; white-space: nowrap; transition: all 0.2s;"
                                               onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#f0f9ff'">
                                            📤 رفع صورة
                                            <input type="file" accept="image/*" style="display:none;" 
                                                   onchange="uploadActivityImage(this, ${index}, ${imgIndex})">
                                        </label>
                                    </div>
                                    <input type="text" class="form-input" value="${img.description || ''}" 
                                           onchange="updateImageDescription(${index}, ${imgIndex}, this.value)"
                                           placeholder="وصف الصورة (اختياري)">
                                    ${img.url ? `<img src="${img.url}" style="max-width: 150px; max-height: 150px; border-radius: 8px; border: 2px solid #e2e8f0;" onerror="this.style.display='none'">` : ''}
                                </div>
                                <button type="button" class="btn-small btn-danger" onclick="removeImage(${index}, ${imgIndex})">🗑️</button>
                            </div>
                        `).join('') : '<p style="color: #64748b; padding: 12px;">لا توجد صور بعد</p>'}
                        <button type="button" class="btn-small btn-secondary" onclick="addImage(${index})">➕ إضافة صورة</button>
                        <small style="color: #64748b; font-size: 13px; margin-top: 8px; display: block;">الترتيب الحالي هو الترتيب الصحيح</small>
                    </div>
                ` : ''}
                
                ${q.type === 'short_answer' ? `
                    <input type="text" class="form-input" value="${q.answer || ''}" 
                           onchange="updateQuestion(${index}, 'answer', this.value)"
                           placeholder="الإجابة الصحيحة...">
                ` : ''}
            </div>
        `;
        
        container.appendChild(card);
    });
    
    updateJson();
}

function updateJson() {
    document.getElementById('questionsJson').value = JSON.stringify(questions);
}

// Initialize if type is true_false
document.addEventListener('DOMContentLoaded', function() {
    // Set default options for true/false questions
    const typeSelects = document.querySelectorAll('select[onchange*="type"]');
    typeSelects.forEach(select => {
        select.addEventListener('change', function() {
            const index = parseInt(this.getAttribute('onchange').match(/\d+/)[0]);
            if (this.value === 'true_false') {
                questions[index].options = ['صح', 'خطأ'];
                renderQuestions();
            }
        });
    });
});

// New functions for ordering questions
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
            questions[index].options = ['أ', 'ب'];
            questions[index].answer = '';
        } else if (value === 'word_order') {
            questions[index].options = ['كلمة', 'ثانية'];
            delete questions[index].answer; // Answer is the order itself
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

    // Validate file type
    if (!file.type.startsWith('image/')) {
        alert('يرجى اختيار ملف صورة فقط');
        return;
    }

    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('حجم الصورة يجب أن يكون أقل من 5 ميجابايت');
        return;
    }

    // Show loading state
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
        questions[qIndex].images[imgIndex].url = data.url;
        renderQuestions();
    })
    .catch(function(error) {
        alert('حدث خطأ أثناء رفع الصورة: ' + error.message);
        label.innerHTML = originalText;
        label.style.pointerEvents = '';
    });
}
</script>

@endsection
