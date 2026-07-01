@extends('layouts.teacher')

@section('title', 'تعديل النشاط')

@push('styles')
<style>
    .question-item { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 15px; }
    .question-item:hover { border-color: #667eea; }
    .question-num { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); color: white; font-weight: 700; font-size: 13px; margin-left: 10px; }
    .option-item { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; }
    .option-item input[type="text"] { flex: 1; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: inherit; }
    .option-radio { width: 20px; height: 20px; cursor: pointer; accent-color: #10B981; }
    .btn-add-q { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 600; }
    .btn-remove { background: #fee2e2; color: #dc2626; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 13px; }
    .image-thumb { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 2px solid #e2e8f0; }
    .qbuilder { background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 16px; padding: 25px; margin-top: 15px; }
    /* منشئ الأسئلة المتقدم */
    .q-type-select { max-width: 200px; }
    .q-correct { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; border: 2px solid #e2e8f0; background: white; flex-shrink: 0; font-weight: 700; }
    .q-correct.selected { background: #dcfce7; border-color: #16a34a; color: #16a34a; }
    .q-opt-num { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; font-weight:700; color:#64748b; flex-shrink:0; }
    .q-label { font-weight: 700; font-size: 13px; color: #475569; margin-bottom: 6px; display:block; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.activities') }}">الأنشطة</a></li>
                <li class="breadcrumb-item active">تعديل: {{ $activity->title }}</li>
            </ol>
        </nav>
        <h2 class="mb-1">تعديل النشاط</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <form action="{{ route('teacher.activities.update', $activity->id) }}" method="POST" enctype="multipart/form-data" id="activityForm">
        @csrf
        @method('PUT')

        {{-- hidden: يحمل questions الحالية لحمايتها من الحذف --}}
        <input type="hidden" name="questions" id="questionsData"
               value="{{ $activity->questions ? json_encode($activity->questions) : '' }}">
        <input type="hidden" name="question_type" id="questionTypeData"
               value="{{ $activity->question_type ?? '' }}">

        <div class="row">
            <div class="col-lg-8">
                <div class="card glass-effect border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">المعلومات الأساسية</h5>

                        <div class="mb-3">
                            <label class="form-label">عنوان النشاط <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $activity->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">وصف النشاط</label>
                            <textarea name="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $activity->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الدرس المرتبط <span class="text-danger">*</span></label>
                            <select name="lesson_id" class="form-select @error('lesson_id') is-invalid @enderror" required>
                                <option value="">-- اختر الدرس --</option>
                                @foreach($lessons as $lesson)
                                    <option value="{{ $lesson->id }}"
                                            {{ old('lesson_id', $activity->lesson_id) == $lesson->id ? 'selected' : '' }}>
                                        {{ $lesson->concept->value->name }} &raquo;
                                        {{ $lesson->concept->name }} &raquo;
                                        {{ $lesson->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lesson_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">نوع النشاط <span class="text-danger">*</span></label>
                                <select name="type" id="activityType" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="quiz"        {{ old('type',$activity->type)=='quiz'        ?'selected':'' }}>🧪 اختبار (Quiz)</option>
                                    <option value="exercise"    {{ old('type',$activity->type)=='exercise'    ?'selected':'' }}>📋 تمرين</option>
                                    <option value="project"     {{ old('type',$activity->type)=='project'     ?'selected':'' }}>🏗️ مشروع</option>
                                    <option value="image_order" {{ old('type',$activity->type)=='image_order' ?'selected':'' }}>🖼️ ترتيب صور</option>
                                    <option value="upload"      {{ old('type',$activity->type)=='upload'      ?'selected':'' }}>📤 رفع ملف</option>
                                    <option value="practical"   {{ old('type',$activity->type)=='practical'   ?'selected':'' }}>🎯 نشاط عملي</option>
                                    <option value="discussion"  {{ old('type',$activity->type)=='discussion'  ?'selected':'' }}>💬 نقاش</option>
                                    <option value="creative"    {{ old('type',$activity->type)=='creative'    ?'selected':'' }}>✨ إبداعي</option>
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">النقاط <span class="text-danger">*</span></label>
                                <input type="number" name="points" class="form-control @error('points') is-invalid @enderror"
                                       value="{{ old('points', $activity->points) }}" min="1" max="100" required>
                                @error('points')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Quiz Settings --}}
                        <div id="quizSettings" style="display:{{ in_array($activity->type,['quiz','exercise'])?'block':'none' }};">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">درجة النجاح (%)</label>
                                    <input type="number" name="passing_score" class="form-control"
                                           value="{{ old('passing_score', $activity->passing_score) }}" min="0" max="100">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">المدة (بالدقائق)</label>
                                    <input type="number" name="duration_minutes" class="form-control"
                                           value="{{ old('duration_minutes', $activity->duration_minutes) }}" min="1">
                                </div>
                            </div>
                        </div>

                        {{-- Questions/Images Section --}}
                        <div id="questionsSection" style="display:{{ in_array($activity->type,['quiz','exercise','image_order'])?'block':'none' }};">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <span id="qSectionTitle">{{ $activity->type==='image_order' ? '🖼️ صور النشاط' : '❓ الأسئلة' }}</span>
                                    @if($activity->questions)
                                        <span class="badge bg-success ms-2">{{ count($activity->questions) }} محفوظ</span>
                                    @endif
                                </h6>
                            </div>

                            {{-- معاينة صور image_order المحفوظة (تُعرض فقط لهذا النوع) --}}
                            @if($activity->type === 'image_order' && $activity->questions && count($activity->questions) > 0)
                                <div class="alert alert-success mb-3">
                                    ✅ يوجد <strong>{{ count($activity->questions) }}</strong> صور محفوظة. إضافة صور جديدة ستستبدلها.
                                </div>
                                <div class="d-flex flex-wrap gap-3 mb-3">
                                    @foreach($activity->questions as $img)
                                        @if(isset($img['image_url']))
                                            <img src="{{ $img['image_url'] }}" class="image-thumb"
                                                 alt="{{ $img['caption'] ?? 'صورة' }}"
                                                 onerror="this.style.border='2px solid #ef4444'; this.title='صورة غير متاحة';">
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <div id="qbuilder" class="qbuilder">
                                <div id="questionsList"></div>
                                <button type="button" class="btn-add-q" onclick="addQuestion()">+ إضافة سؤال</button>
                                <p class="text-muted small mt-2 mb-0" id="qbuilderHint">
                                    عدّل الأسئلة الحالية أو أضف أسئلة جديدة. ما تراه هنا هو ما سيُحفظ.
                                </p>
                            </div>
                        </div>
                        {{-- End Questions --}}

                        {{-- Attachment --}}
                        @if($activity->attachment)
                            <div class="alert alert-info mt-3 mb-1">
                                <i class="fas fa-paperclip me-2"></i>
                                المرفق الحالي:
                                <a href="{{ asset('storage/' . $activity->attachment) }}" target="_blank" class="alert-link">
                                    {{ basename($activity->attachment) }}
                                </a>
                            </div>
                        @endif
                        <div class="mb-3 mt-2">
                            <label class="form-label">مرفق جديد (اختياري)</label>
                            <input type="file" name="attachment" class="form-control"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">رفع ملف جديد سيستبدل المرفق الحالي</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card glass-effect border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">الإعدادات</h5>

                        <div class="mb-3">
                            <label class="form-label">الفصل الدراسي</label>
                            <select name="classroom_id" class="form-select">
                                <option value="">جميع الفصول</option>
                                @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}"
                                            {{ old('classroom_id',$activity->classroom_id)==$cls->id?'selected':'' }}>
                                        {{ $cls->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_homework"
                                       id="isHomework" value="1"
                                       {{ old('is_homework',$activity->is_homework) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isHomework">واجب منزلي</label>
                            </div>
                        </div>

                        <div class="mb-3" id="dueDateField"
                             style="display:{{ old('is_homework',$activity->is_homework) ? 'block' : 'none' }};">
                            <label class="form-label">الموعد النهائي للتسليم</label>
                            <input type="datetime-local" name="due_date" class="form-control"
                                   value="{{ old('due_date', $activity->due_date ? $activity->due_date->format('Y-m-d\TH:i') : '') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="active"   {{ old('status',$activity->status)=='active'   ?'selected':'' }}>🟢 نشط</option>
                                <option value="draft"    {{ old('status',$activity->status)=='draft'    ?'selected':'' }}>📝 مسودة</option>
                                <option value="inactive" {{ old('status',$activity->status)=='inactive' ?'selected':'' }}>⏸️ غير نشط</option>
                            </select>
                        </div>

                        <div class="mb-1" style="padding:14px;background:#fffbeb;border:2px solid #f59e0b;border-radius:12px;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="manual_review" id="manualReview" value="1"
                                       style="accent-color:#f59e0b;"
                                       {{ old('manual_review', $activity->manual_review) ? 'checked' : '' }}>
                                <label class="form-check-label" for="manualReview" style="font-weight:700;color:#92400e;">
                                    👨‍🏫 يتطلب موافقة/تصحيح المعلم يدوياً
                                </label>
                            </div>
                            <small class="d-block" style="color:#a16207;margin-top:6px;">عند تفعيله لا يُصحَّح النشاط آلياً — يذهب تسليم الطالب للمعلم لاعتماد الدرجة</small>
                        </div>
                    </div>
                </div>

                <div class="card glass-effect border-0">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-2"></i>حفظ التعديلات
                        </button>
                        <a href="{{ route('teacher.activities') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('isHomework').addEventListener('change', function() {
    document.getElementById('dueDateField').style.display = this.checked ? 'block' : 'none';
});

const activityTypeEl = document.getElementById('activityType');

activityTypeEl.addEventListener('change', function() {
    const t = this.value;
    document.getElementById('quizSettings').style.display    = ['quiz','exercise'].includes(t) ? 'block' : 'none';
    document.getElementById('questionsSection').style.display = ['quiz','exercise','image_order'].includes(t) ? 'block' : 'none';
    document.getElementById('qSectionTitle').textContent = t === 'image_order' ? '🖼️ صور النشاط' : '❓ الأسئلة';
    const btn = document.querySelector('#qbuilder .btn-add-q');
    if (btn) btn.textContent = t === 'image_order' ? '+ إضافة صورة' : '+ إضافة سؤال';
    const hint = document.getElementById('qbuilderHint');
    if (hint) hint.style.display = t === 'image_order' ? 'none' : 'block';
    // إعادة رسم المنشئ بحسب النوع الجديد
    if (t === 'image_order') {
        document.getElementById('questionsList').innerHTML = '';
    } else {
        renderQuestions();
    }
});

function currentActivityType() { return activityTypeEl.value; }

// ==================================================================
// منشئ الأسئلة العام (اختبار/تمرين) — نفس عقد JSON الخاص بلوحة المشرف
// ==================================================================
let questions = [];

// تحميل الأسئلة المحفوظة إلى المحرّرات المصنّفة (مع الحفاظ على مفاتيح مثل word/hint)
(function loadExistingQuestions() {
    if (currentActivityType() === 'image_order') return;
    const raw = document.getElementById('questionsData').value;
    if (!raw) return;
    let parsed;
    try { parsed = JSON.parse(raw); } catch (e) { return; }
    if (!Array.isArray(parsed)) return;
    questions = parsed.map(q => {
        // نُبقِي كل المفاتيح الأصلية ثم نطبّعها (mirror admin edit reload)
        const nq = { ...q };
        nq.type = nq.type || (Array.isArray(nq.options) ? 'multiple_choice' : 'short_answer');
        nq.points = nq.points ?? 10;
        // دعم قيمة قديمة correct_answer (فهرس) الناتجة عن المنشئ السابق
        if (nq.correct_index === undefined && nq.correct_answer !== undefined && Array.isArray(nq.options)) {
            const idx = parseInt(nq.correct_answer);
            if (!isNaN(idx)) {
                nq.correct_index = idx;
                if (nq.answer === undefined) nq.answer = nq.options[idx];
            }
        }
        return nq;
    });
    renderQuestions();
})();

function addQuestion() {
    if (currentActivityType() === 'image_order') { addImageItem(); return; }
    questions.push({ type: 'multiple_choice', question: '', options: ['', ''], answer: '', points: 10 });
    renderQuestions();
}

function removeQuestion(index) {
    if (confirm('هل أنت متأكد من حذف هذا السؤال؟')) {
        questions.splice(index, 1);
        renderQuestions();
    }
}

function addOption(index) {
    if (!questions[index].options) questions[index].options = [];
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
    questions[qIndex].answer = questions[qIndex].options[answer];
    questions[qIndex].correct_index = answer;
    renderQuestions();
}

function updateQuestion(index, field, value) {
    const oldType = questions[index].type;
    questions[index][field] = value;

    if (field === 'type' && oldType !== value) {
        if (value === 'true_false') {
            questions[index].options = ['صح', 'خطأ'];
            questions[index].answer = '';
            delete questions[index].correct_index;
            delete questions[index].word;
        } else if (value === 'letter_choice') {
            questions[index].options = ['أ', 'ب'];
            questions[index].answer = '';
            delete questions[index].correct_index;
        } else if (value === 'word_order') {
            questions[index].options = ['كلمة', 'ثانية'];
            delete questions[index].answer;
            delete questions[index].correct_index;
            delete questions[index].word;
        } else if (value === 'sentence_order') {
            questions[index].options = ['الجملة الأولى', 'الجملة الثانية'];
            delete questions[index].answer;
            delete questions[index].correct_index;
            delete questions[index].word;
        } else if (value === 'multiple_choice') {
            if (!questions[index].options || questions[index].options.length < 2) {
                questions[index].options = ['', ''];
            }
            questions[index].answer = '';
            delete questions[index].word;
        } else if (value === 'short_answer') {
            delete questions[index].options;
            delete questions[index].correct_index;
            delete questions[index].word;
            questions[index].answer = '';
        }
        renderQuestions();
    }
    updateJson();
}

function renderQuestions() {
    const container = document.getElementById('questionsList');
    if (!container || currentActivityType() === 'image_order') return;
    container.innerHTML = '';

    questions.forEach((q, index) => {
        const card = document.createElement('div');
        card.className = 'question-item';

        const isOrderingType = ['word_order', 'sentence_order'].includes(q.type);
        let optionsHtml = '';

        if (q.options) {
            q.options.forEach((option, oIndex) => {
                const isCorrect = (q.correct_index !== undefined && q.correct_index !== null)
                    ? Number(q.correct_index) === oIndex
                    : (q.answer === option);
                optionsHtml += `
                    <div class="option-item">
                        ${!isOrderingType ? `
                            <div class="q-correct ${isCorrect ? 'selected' : ''}"
                                 onclick="setCorrectAnswer(${index}, ${oIndex})" title="اختر كإجابة صحيحة">
                                ${isCorrect ? '✓' : '○'}
                            </div>
                        ` : `<span class="q-opt-num">${oIndex + 1}</span>`}
                        <input type="text" value="${escAttr(option)}"
                               onchange="updateOption(${index}, ${oIndex}, this.value)"
                               placeholder="${q.type === 'letter_choice' ? 'الحرف' : (q.type === 'word_order' ? 'الكلمة' : (q.type === 'sentence_order' ? 'الجملة' : 'الخيار'))} ${oIndex + 1}">
                        <button type="button" onclick="removeOption(${index}, ${oIndex})"
                                style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;">×</button>
                    </div>`;
            });
        }

        card.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="question-num">${index + 1}</span>
                    <select class="form-select form-select-sm q-type-select" onchange="updateQuestion(${index}, 'type', this.value)">
                        <option value="multiple_choice" ${q.type === 'multiple_choice' ? 'selected' : ''}>اختيار متعدد</option>
                        <option value="true_false" ${q.type === 'true_false' ? 'selected' : ''}>صح / خطأ</option>
                        <option value="short_answer" ${q.type === 'short_answer' ? 'selected' : ''}>إجابة قصيرة</option>
                        <option value="letter_choice" ${q.type === 'letter_choice' ? 'selected' : ''}>اختيار حروف</option>
                        <option value="word_order" ${q.type === 'word_order' ? 'selected' : ''}>ترتيب كلمات</option>
                        <option value="sentence_order" ${q.type === 'sentence_order' ? 'selected' : ''}>ترتيب جمل</option>
                    </select>
                </div>
                <button type="button" class="btn-remove" onclick="removeQuestion(${index})">✕ حذف</button>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-9">
                    <input type="text" class="form-control" value="${escAttr(q.question)}"
                           onchange="updateQuestion(${index}, 'question', this.value)"
                           placeholder="نص السؤال...">
                </div>
                <div class="col-3">
                    <input type="number" class="form-control" value="${q.points ?? 10}"
                           onchange="updateQuestion(${index}, 'points', parseInt(this.value))"
                           placeholder="الدرجة" min="1">
                </div>
            </div>

            ${q.type === 'letter_choice' ? `
                <div class="mb-2">
                    <input type="text" class="form-control" value="${escAttr(q.word || '')}"
                           onchange="updateQuestion(${index}, 'word', this.value)"
                           placeholder="الكلمة المستهدفة (مثال: صلاة)">
                </div>
            ` : ''}

            ${(q.type === 'multiple_choice' || q.type === 'true_false' || q.type === 'letter_choice') ? `
                <label class="q-label">${q.type === 'letter_choice' ? 'الحروف (اضغط على ○ لتحديد الإجابة الصحيحة)' : 'الخيارات (اضغط على ○ لتحديد الإجابة الصحيحة)'}</label>
                <div class="options-list">${optionsHtml}</div>
                ${(q.type === 'multiple_choice' || q.type === 'letter_choice') ? `
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="addOption(${index})">+ إضافة ${q.type === 'letter_choice' ? 'حرف' : 'خيار'}</button>
                ` : ''}
            ` : ''}

            ${q.type === 'short_answer' ? `
                <label class="q-label">الإجابة الصحيحة (يقارَن بها نص الطالب بعد تطبيع المسافات والتشكيل)</label>
                <input type="text" class="form-control" value="${escAttr(q.answer || '')}"
                       onchange="updateQuestion(${index}, 'answer', this.value)"
                       placeholder="مثال: الصلاة الوسطى">
            ` : ''}

            ${(q.type === 'word_order' || q.type === 'sentence_order') ? `
                <label class="q-label">${q.type === 'word_order' ? 'الكلمات (سيتم ترتيبها عشوائياً للطالب)' : 'الجمل (سيتم ترتيبها عشوائياً للطالب)'}</label>
                <div class="options-list">${optionsHtml}</div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="addOption(${index})">+ إضافة ${q.type === 'word_order' ? 'كلمة' : 'جملة'}</button>
                <small class="text-muted d-block mt-2">الترتيب الحالي هو الترتيب الصحيح</small>
            ` : ''}
        `;
        container.appendChild(card);
    });

    updateJson();
}

function updateJson() {
    if (currentActivityType() === 'image_order') return;
    document.getElementById('questionsData').value = JSON.stringify(questions);
}

function escAttr(v) {
    return String(v == null ? '' : v)
        .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
        .replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// ================================
// وظائف image_order (كما هي)
// ================================
function addImageItem() {
    const list = document.getElementById('questionsList');
    const i = list.children.length;
    const div = document.createElement('div');
    div.className = 'question-item';
    div.dataset.index = i;
    div.innerHTML = `
        <div class="d-flex justify-content-between mb-2">
            <label class="fw-bold"><span class="question-num">${i+1}</span> صورة</label>
            <button type="button" class="btn-remove" onclick="this.closest('.question-item').remove(); updateImageData();">&#x2715; حذف</button>
        </div>
        <div class="mb-2">
            <label class="form-label small text-muted">رابط الصورة (URL)</label>
            <input type="url" class="form-control img-url" placeholder="https://example.com/image.jpg"
                   oninput="previewImg(this); updateImageData()">
            <div class="mt-2">
                <img class="img-preview" src="" alt=""
                     style="max-width:120px;max-height:100px;border-radius:8px;border:2px solid #e2e8f0;display:none;object-fit:cover;">
            </div>
        </div>
        <div>
            <label class="form-label small text-muted">عنوان الصورة (اختياري)</label>
            <input type="text" class="form-control img-caption" placeholder="وصف مختصر للصورة..." oninput="updateImageData()">
        </div>
    `;
    list.appendChild(div);
}

function previewImg(input) {
    const preview = input.closest('.question-item').querySelector('.img-preview');
    const url = input.value.trim();
    if (url) {
        preview.src = url;
        preview.style.display = 'block';
        preview.onerror = () => { preview.style.display = 'none'; };
    } else {
        preview.style.display = 'none';
    }
}

function updateImageData() {
    const items = document.querySelectorAll('#questionsList .question-item');
    const imgs = [];
    items.forEach((item, i) => {
        const url = item.querySelector('.img-url')?.value?.trim();
        const caption = item.querySelector('.img-caption')?.value?.trim() || '';
        if (url) {
            imgs.push({ image_url: url, caption: caption, order: i + 1 });
        }
    });
    // نكتب فقط عند وجود صور جديدة حتى لا نمسح الصور المحفوظة إن لم يلمسها المعلم
    if (imgs.length > 0) {
        document.getElementById('questionsData').value = JSON.stringify(imgs);
    }
}
</script>
@endpush
@endsection
