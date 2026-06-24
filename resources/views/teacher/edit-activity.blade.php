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

                            @if($activity->questions && count($activity->questions) > 0)
                                <div class="alert alert-success mb-3">
                                    ✅ يوجد <strong>{{ count($activity->questions) }}</strong> عناصر محفوظة وستُحفظ تلقائياً.
                                </div>

                                @if($activity->type === 'image_order')
                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                        @foreach($activity->questions as $img)
                                            @if(isset($img['image_url']))
                                                <img src="{{ $img['image_url'] }}" class="image-thumb"
                                                     alt="{{ $img['caption'] ?? 'صورة' }}"
                                                     onerror="this.style.border='2px solid #ef4444'; this.title='صورة غير متاحة';">
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    @foreach($activity->questions as $qi => $q)
                                        <div class="question-item">
                                            <div class="mb-1">
                                                <span class="question-num">{{ $qi+1 }}</span>
                                                <strong>{{ $q['question'] ?? $q['text'] ?? 'سؤال '.($qi+1) }}</strong>
                                            </div>
                                            @if(!empty($q['options']))
                                                <ul class="mb-0 mt-2 ps-4" style="font-size:14px;">
                                                    @foreach($q['options'] as $oi => $opt)
                                                        <li style="color:{{ ($q['correct_answer']??-1)===$oi ? '#16a34a' : '#64748b' }};">
                                                            {{ $opt }}
                                                            @if(($q['correct_answer']??-1)===$oi) <strong>✓</strong> @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                <button type="button" class="btn btn-outline-warning btn-sm mb-2"
                                        onclick="document.getElementById('qbuilder').style.display='block'; this.style.display='none';">
                                    ✏️ استبدال الأسئلة بأسئلة جديدة
                                </button>
                            @endif

                            <div id="qbuilder" class="qbuilder"
                                 style="{{ ($activity->questions && count($activity->questions)>0) ? 'display:none;' : '' }}">
                                @if($activity->questions && count($activity->questions) > 0)
                                    <div class="alert alert-warning mb-3 py-2">⚠️ الأسئلة أدناه ستستبدل الأسئلة الحالية عند الحفظ.</div>
                                @endif
                                <div id="questionsList"></div>
                                <button type="button" class="btn-add-q" onclick="addQuestion()">+ إضافة سؤال</button>
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

document.getElementById('activityType').addEventListener('change', function() {
    const t = this.value;
    document.getElementById('quizSettings').style.display    = ['quiz','exercise'].includes(t) ? 'block' : 'none';
    document.getElementById('questionsSection').style.display = ['quiz','exercise','image_order'].includes(t) ? 'block' : 'none';
    document.getElementById('qSectionTitle').textContent = t === 'image_order' ? '🖼️ صور النشاط' : '❓ الأسئلة';
    // تحديث زر إضافة
    const btn = document.querySelector('#qbuilder .btn-add-q');
    if (btn) btn.textContent = t === 'image_order' ? '+ إضافة صورة' : '+ إضافة سؤال';
});

function addQuestion() {
    const type = document.getElementById('activityType').value;
    if (type === 'image_order') {
        addImageItem();
        return;
    }
    const list = document.getElementById('questionsList');
    const i = list.children.length;
    const letters = ['أ','ب','ج','د'];
    const div = document.createElement('div');
    div.className = 'question-item';
    div.dataset.index = i;
    div.innerHTML = `
        <div class="d-flex justify-content-between mb-2">
            <label class="fw-bold"><span class="question-num">${i+1}</span> نص السؤال</label>
            <button type="button" class="btn-remove" onclick="this.closest('.question-item').remove(); updateQData();">✕ حذف</button>
        </div>
        <input type="text" class="form-control mb-3 q-text" placeholder="أدخل نص السؤال..." oninput="updateQData()">
        <small class="text-muted d-block mb-2">حدّد ○ بجوار الإجابة الصحيحة</small>
        <div class="options-list">
            ${letters.map((l,j)=>`
            <div class="option-item">
                <input type="radio" name="c_${i}" value="${j}" class="option-radio" onchange="updateQData()">
                <span style="font-weight:700;min-width:22px;">${l}</span>
                <input type="text" class="option-text" placeholder="خيار ${l}..." oninput="updateQData()">
                <button type="button" onclick="this.closest('.option-item').remove();updateQData();"
                        style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;">×</button>
            </div>`).join('')}
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="addOpt(this, ${i})">+ خيار إضافي</button>
    `;
    list.appendChild(div);
}

function addOpt(btn, i) {
    const opts = btn.previousElementSibling;
    const j = opts.children.length;
    const letters = ['أ','ب','ج','د','هـ','و','ز'];
    const d = document.createElement('div');
    d.className = 'option-item';
    d.innerHTML = `
        <input type="radio" name="c_${i}" value="${j}" class="option-radio" onchange="updateQData()">
        <span style="font-weight:700;min-width:22px;">${letters[j]||j+1}</span>
        <input type="text" class="option-text" placeholder="خيار جديد..." oninput="updateQData()">
        <button type="button" onclick="this.closest('.option-item').remove();updateQData();"
                style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;">×</button>
    `;
    opts.appendChild(d);
}

function updateQData() {
    const type = document.getElementById('activityType').value;
    if (type === 'image_order') {
        updateImageData();
        return;
    }
    const items = document.querySelectorAll('#questionsList .question-item');
    const qs = [];
    items.forEach(item => {
        const text = item.querySelector('.q-text')?.value?.trim();
        if (!text) return;
        const opts = [...item.querySelectorAll('.option-text')].map(o=>o.value.trim()).filter(Boolean);
        const sel  = item.querySelector('.option-radio:checked');
        qs.push({ question: text, options: opts, correct_answer: sel ? parseInt(sel.value) : 0 });
    });
    if (qs.length > 0) {
        document.getElementById('questionsData').value = JSON.stringify(qs);
    }
}

// ================================
// وظائف image_order
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
    document.getElementById('questionsData').value = imgs.length > 0 ? JSON.stringify(imgs) : '';
}
</script>
@endpush
@endsection
