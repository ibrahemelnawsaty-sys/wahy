@extends('layouts.teacher')

@section('title', 'إضافة نشاط جديد')

@push('styles')
<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .fade-in { animation: fadeIn 0.5s ease-out; }
    
    .form-card {
        background: white;
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }
    .form-card h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-group { margin-bottom: 20px; }
    .form-label {
        display: block;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
        font-size: 15px;
    }
    .form-label .required { color: #ef4444; margin-right: 4px; }
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 15px;
        font-family: inherit;
        transition: all 0.3s;
        background: #fafbfc;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
        background: white;
    }
    .form-textarea { resize: vertical; min-height: 120px; }
    .form-hint { font-size: 13px; color: #94a3b8; margin-top: 6px; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
    @media (max-width: 768px) {
        .grid-2, .grid-3 { grid-template-columns: 1fr; }
    }

    .type-card {
        padding: 20px;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: white;
    }
    .type-card:hover { border-color: #667eea; }
    .type-card.active { border-color: #667eea; background: #eff6ff; box-shadow: 0 4px 15px rgba(102,126,234,0.15); }
    .type-card .icon { font-size: 36px; margin-bottom: 8px; }
    .type-card .title { font-weight: 700; color: #1a202c; font-size: 15px; }
    .type-card .desc { font-size: 12px; color: #94a3b8; margin-top: 4px; }

    .media-upload {
        display: flex;               /* الوسم <label> افتراضياً inline داخل الـwrapper — نجعله عموداً يملأ الخلية */
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        min-height: 160px;
        box-sizing: border-box;
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 24px 16px;
        text-align: center;
        cursor: pointer;
        transition: border-color .25s, background .25s, transform .15s, box-shadow .25s;
        background: #fafbfc;
    }
    .media-upload:hover { border-color: #667eea; background: #f0f4ff; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102,126,234,0.12); }
    .media-upload input[type="file"] { display: none; }
    .media-upload .icon { font-size: 38px; line-height: 1; margin: 0; }
    .media-upload .text { color: #334155; font-size: 14px; font-weight: 700; }
    .media-upload .hint { color: #94a3b8; font-size: 12px; margin: 0; }
    .media-preview {
        display: none;
        margin-top: 15px;
        padding: 15px;
        border-radius: 12px;
        background: #f1f5f9;
    }
    .media-preview img, .media-preview video, .media-preview audio {
        max-width: 100%;
        border-radius: 10px;
    }

    .creative-toggle {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 16px;
        margin-bottom: 20px;
    }
    .creative-toggle input[type="checkbox"] {
        width: 22px;
        height: 22px;
        cursor: pointer;
        accent-color: #f59e0b;
    }

    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 16px 40px;
        border: none;
        border-radius: 14px;
        font-size: 17px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 8px 25px rgba(102,126,234,0.3);
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 35px rgba(102,126,234,0.4); }
    .btn-cancel {
        background: #f1f5f9;
        color: #475569;
        padding: 16px 40px;
        border: none;
        border-radius: 14px;
        font-size: 17px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s;
    }
    .btn-cancel:hover { background: #e2e8f0; }

    /* ===== منشئ الأسئلة ===== */
    .q-card { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 16px; }
    .q-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 16px; }
    .q-num { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; }
    .q-type-select { padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-family: inherit; font-size: 14px; background: white; }
    .q-fields { display: flex; flex-direction: column; gap: 12px; }
    .q-field-row { display: grid; grid-template-columns: 1fr 120px; gap: 12px; }
    @media (max-width: 768px) { .q-field-row { grid-template-columns: 1fr; } }
    .q-options { display: flex; flex-direction: column; gap: 8px; }
    .q-option-row { display: flex; gap: 8px; align-items: center; }
    .q-option-input { flex: 1; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-family: inherit; font-size: 14px; }
    .q-correct { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; border: 2px solid #e2e8f0; background: white; flex-shrink: 0; font-weight: 700; }
    .q-correct.selected { background: #dcfce7; border-color: #16a34a; color: #16a34a; }
    .q-btn-sm { padding: 8px 12px; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; }
    .q-btn-add { background: #eef2ff; color: #4338ca; }
    .q-btn-del { background: #fee2e2; color: #dc2626; }
    .q-label { font-weight: 700; font-size: 13px; color: #475569; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="fade-in" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 25px; padding: 35px; margin-bottom: 30px; box-shadow: 0 15px 50px rgba(102, 126, 234, 0.3); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: white; margin-bottom: 8px;">➕ إضافة نشاط جديد</h1>
            <p style="color: rgba(255,255,255,0.95); font-size: 16px;">أنشئ نشاطاً تعليمياً احترافياً مع مرفقات وسائط متعددة</p>
        </div>
        <a href="{{ route('teacher.activity-bank.index') }}" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: white; padding: 12px 24px; border-radius: 12px; border: 2px solid rgba(255,255,255,0.3); font-weight: 700; text-decoration: none;">
            ← العودة لبنك الأنشطة
        </a>
    </div>
</div>

@if($errors->any())
<div class="fade-in" style="background: #fee2e2; border: 2px solid #fca5a5; border-radius: 16px; padding: 18px 25px; margin-bottom: 20px;">
    <div style="font-weight: 700; color: #991b1b; margin-bottom: 8px;">⚠️ يرجى تصحيح الأخطاء التالية:</div>
    @foreach($errors->all() as $error)
    <div style="color: #991b1b; font-size: 14px; padding: 3px 0;">• {{ $error }}</div>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('teacher.activity-bank.store') }}" enctype="multipart/form-data">
    @csrf

    <!-- بيانات النشاط الأساسية -->
    <div class="form-card fade-in">
        <h3>📝 بيانات النشاط الأساسية</h3>
        
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> عنوان النشاط</label>
            <input type="text" name="title" class="form-input" placeholder="مثال: مشروع البحث عن القيم الإسلامية" required value="{{ old('title') }}">
        </div>
        
        <div class="form-group">
            <label class="form-label">وصف النشاط</label>
            {{-- محرّر نصوص غنيّ موحّد (ذاتيّ الاستضافة — يعمل بدون إنترنت) --}}
            <div data-rich-editor="activityDesc" data-target="descriptionHidden" dir="rtl" hidden>{!! safe_html(old('description')) !!}</div>
            <textarea name="description" id="descriptionHidden" rows="6" dir="rtl" style="width:100%; min-height:150px; padding:12px 14px; border:2px solid #e2e8f0; border-radius:10px; font-family:inherit; font-size:15px; line-height:1.8; box-sizing:border-box;">{!! safe_html(old('description')) !!}</textarea>
        </div>
    </div>

    <!-- نوع النشاط -->
    <div class="form-card fade-in">
        <h3>📂 نوع النشاط</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <label class="type-card {{ old('type', 'quiz') == 'quiz' ? 'active' : '' }}" onclick="selectType(this, 'quiz')">
                <div class="icon">📝</div>
                <div class="title">اختبار</div>
                <div class="desc">أسئلة وأجوبة</div>
                <input type="radio" name="type" value="quiz" {{ old('type', 'quiz') == 'quiz' ? 'checked' : '' }} style="display: none;">
            </label>
            <label class="type-card {{ old('type') == 'exercise' ? 'active' : '' }}" onclick="selectType(this, 'exercise')">
                <div class="icon">📋</div>
                <div class="title">تمرين</div>
                <div class="desc">تطبيق عملي</div>
                <input type="radio" name="type" value="exercise" {{ old('type') == 'exercise' ? 'checked' : '' }} style="display: none;">
            </label>
            <label class="type-card {{ old('type') == 'project' ? 'active' : '' }}" onclick="selectType(this, 'project')">
                <div class="icon">🏗️</div>
                <div class="title">مشروع</div>
                <div class="desc">مشروع طويل الأمد</div>
                <input type="radio" name="type" value="project" {{ old('type') == 'project' ? 'checked' : '' }} style="display: none;">
            </label>
            <label class="type-card {{ old('type') == 'creative' ? 'active' : '' }}" onclick="selectType(this, 'creative')">
                <div class="icon">✨</div>
                <div class="title">إبداعي</div>
                <div class="desc">نشاط إبداعي مميز</div>
                <input type="radio" name="type" value="creative" {{ old('type') == 'creative' ? 'checked' : '' }} style="display: none;">
            </label>
            <label class="type-card {{ old('type') == 'image_order' ? 'active' : '' }}" onclick="selectType(this, 'image_order')">
                <div class="icon">🖼️</div>
                <div class="title">ترتيب صور</div>
                <div class="desc">رتّب الصور بالترتيب الصحيح</div>
                <input type="radio" name="type" value="image_order" {{ old('type') == 'image_order' ? 'checked' : '' }} style="display: none;">
            </label>
        </div>
    </div>

    <!-- إعدادات الاختبار (اختبار فقط) -->
    <div class="form-card fade-in" id="quizSettingsSection" style="display: {{ old('type', 'quiz') == 'quiz' ? 'block' : 'none' }};">
        <h3>⏱️ إعدادات الاختبار</h3>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">مدة الاختبار (بالدقائق)</label>
                <input type="number" name="quiz_duration" class="form-input" value="{{ old('quiz_duration', 30) }}" min="1" placeholder="30">
                <div class="form-hint">اترك فارغاً لاختبار بدون وقت محدد</div>
            </div>
            <div class="form-group">
                <label class="form-label">عدد المحاولات المسموحة</label>
                <input type="number" name="max_attempts" class="form-input" value="{{ old('max_attempts', 3) }}" min="1" placeholder="3">
                <div class="form-hint">عدد مرّات محاولة الطالب لهذا النشاط (الافتراضي 3).</div>
            </div>
        </div>
    </div>

    <!-- إعدادات رفع الملفّات — لكل نوع يقبل تسليم ملفّ (مشروع/رفع/إبداعي/عمليّ) -->
    <div class="form-card fade-in" id="projectSettingsSection" style="display: {{ in_array(old('type'), ['project', 'upload', 'creative', 'practical']) ? 'block' : 'none' }};">
        <h3>📁 إعدادات المشروع</h3>
        <div class="form-group">
            <label class="form-label">أنواع الملفات المسموحة</label>
            <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 8px;">
                <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer;">
                    <input type="checkbox" name="allowed_file_types[]" value="document" {{ is_array(old('allowed_file_types')) && in_array('document', old('allowed_file_types')) ? 'checked' : '' }}>
                    <span>📄 مستندات (PDF, Word, Excel)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer;">
                    <input type="checkbox" name="allowed_file_types[]" value="image" {{ is_array(old('allowed_file_types')) && in_array('image', old('allowed_file_types')) ? 'checked' : '' }}>
                    <span>🖼️ صور (JPG, PNG, GIF)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer;">
                    <input type="checkbox" name="allowed_file_types[]" value="video" {{ is_array(old('allowed_file_types')) && in_array('video', old('allowed_file_types')) ? 'checked' : '' }}>
                    <span>🎥 فيديو (MP4, AVI, MOV)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer;">
                    <input type="checkbox" name="allowed_file_types[]" value="audio" {{ is_array(old('allowed_file_types')) && in_array('audio', old('allowed_file_types')) ? 'checked' : '' }}>
                    <span>🎵 صوت (MP3, WAV, AAC)</span>
                </label>
            </div>
            <div class="form-hint" style="margin-top: 8px;">اختر أنواع الملفات التي يمكن للطلاب رفعها</div>
        </div>
        <div class="form-group" style="margin-top: 16px;">
            <label class="form-label">الحد الأقصى لحجم الملف (MB)</label>
            <input type="number" name="max_file_size" class="form-input" value="{{ old('max_file_size', 10) }}" min="1" max="100" placeholder="10">
        </div>
    </div>

    <!-- إعدادات النشاط -->
    <div class="form-card fade-in">
        <h3>⚙️ إعدادات النشاط</h3>
        
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">الدرس المرتبط</label>
                <select name="lesson_id" class="form-select">
                    <option value="">اختر درس (اختياري)</option>
                    @foreach($lessons as $lesson)
                    <option value="{{ $lesson->id }}" {{ old('lesson_id') == $lesson->id ? 'selected' : '' }}>{{ $lesson->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">الفصل الدراسي</label>
                <select name="classroom_id" id="classroomSelect" class="form-select">
                    <option value="">اختر فصل (اختياري)</option>
                    @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> النقاط</label>
                <input type="number" name="points" class="form-input" value="{{ old('points', 20) }}" min="1" max="100" required>
                <div class="form-hint">النقاط الأساسية للنشاط</div>
            </div>
            <div class="form-group">
                <label class="form-label">نقاط إضافية</label>
                <input type="number" name="bonus_points" class="form-input" value="{{ old('bonus_points', 0) }}" min="0" max="50">
                <div class="form-hint">مكافأة إضافية للأداء المتميز</div>
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> الحالة</label>
                <select name="status" class="form-select" required>
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>🟢 نشط</option>
                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>📝 مسودة</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>⏸️ غير نشط</option>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">درجة النجاح (%)</label>
                <input type="number" name="passing_score" class="form-input" value="{{ old('passing_score', 70) }}" min="0" max="100">
                <div class="form-hint">النسبة المطلوبة لاجتياز النشاط</div>
            </div>
            <div class="form-group">
                <label class="form-label">الترتيب</label>
                <input type="number" name="order" class="form-input" value="{{ old('order', 0) }}" min="0" placeholder="0">
                <div class="form-hint">0 = ترتيب تلقائي</div>
            </div>
        </div>

        <div class="creative-toggle">
            <input type="checkbox" name="is_creative" id="isCreative" value="1" {{ old('is_creative') ? 'checked' : '' }}>
            <div>
                <div style="font-weight: 700; color: #92400e; font-size: 16px;">✨ نشاط إبداعي (جماعي للفصل)</div>
                <div style="font-size: 13px; color: #a16207; margin-top: 4px;">يتطلب تحديد فصل دراسي</div>
            </div>
        </div>

        <label style="display:flex;align-items:center;gap:15px;padding:20px;background:linear-gradient(135deg,#fef3c7 0%,#fde68a 100%);border-radius:16px;margin-top:20px;cursor:pointer;">
            <input type="checkbox" name="manual_review" value="1" {{ old('manual_review') ? 'checked' : '' }} style="width:22px;height:22px;cursor:pointer;accent-color:#f59e0b;flex-shrink:0;">
            <div>
                <div style="font-weight:700;color:#92400e;font-size:16px;">👨‍🏫 يتطلب موافقة/تصحيح المعلم يدوياً</div>
                <div style="font-size:13px;color:#a16207;margin-top:4px;">عند تفعيله لا يُصحَّح النشاط آلياً — يذهب تسليم الطالب للمعلم لاعتماد الدرجة</div>
            </div>
        </label>

        <label style="display:flex;align-items:center;gap:15px;padding:20px;background:linear-gradient(135deg,#e0e7ff 0%,#c7d2fe 100%);border-radius:16px;margin-top:16px;cursor:pointer;">
            <input type="checkbox" name="requires_parent_approval" value="1" {{ old('requires_parent_approval') ? 'checked' : '' }} style="width:22px;height:22px;cursor:pointer;accent-color:#6366f1;flex-shrink:0;">
            <div>
                <div style="font-weight:700;color:#3730a3;font-size:16px;">👪 يتطلب اطّلاع وموافقة وليّ الأمر</div>
                <div style="font-size:13px;color:#4338ca;margin-top:4px;">عند تفعيله لا ينتقل تسليم الطالب للمعلّم إلا بعد موافقة وليّ الأمر (ويأخذ الوليّ نقاطاً على موافقته)</div>
            </div>
        </label>
    </div>

    <!-- قسم بناء الصور (لنشاط ترتيب الصور) -->
    <input type="hidden" name="questions" id="questionsData" value="{{ old('questions', '') }}">
    <div class="form-card fade-in" id="imageBuilderSection" style="display: {{ old('type') == 'image_order' ? 'block' : 'none' }};">
        <h3>🖼️ صور النشاط</h3>
        <p class="form-hint" style="margin-bottom: 20px;">أضف صور النشاط بالترتيب الصحيح. الطالب سيراها مبعثرة ويرتبها.</p>
        
        <div id="imagesList"></div>
        
        <button type="button" onclick="addImageItem()" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; padding: 12px 24px; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 10px;">
            + إضافة صورة
        </button>
    </div>

    <!-- قسم بناء الأسئلة (لاختبار/تمرين) -->
    <div class="form-card fade-in" id="questionsBuilderSection" style="display: {{ in_array(old('type', 'quiz'), ['quiz','exercise']) ? 'block' : 'none' }};">
        <h3>❓ الأسئلة</h3>
        <p class="form-hint" style="margin-bottom: 20px;">أضف أسئلة النشاط. يمكنك اختيار نوع كل سؤال (اختيار متعدد، صح/خطأ، إجابة قصيرة، اختيار حروف، ترتيب كلمات/جمل).</p>

        <div id="questionsList"></div>

        <button type="button" onclick="addQuestion()" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 12px 24px; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 10px;">
            ➕ إضافة سؤال
        </button>
    </div>

    <!-- الوسائط المتعددة -->
    <div class="form-card fade-in">
        <h3>📎 الوسائط المتعددة (اختياري)</h3>
        <p class="form-hint" style="margin-bottom: 25px;">أرفق ملفات وسائط لتعزيز النشاط التعليمي</p>
        
        <div class="grid-3">
            <div>
                <label class="media-upload" onclick="this.querySelector('input').click()">
                    <div class="icon">🖼️</div>
                    <div class="text">إرفاق صورة</div>
                    <div class="hint">JPG, PNG — عدّة صور ممكن</div>
                    <input type="file" name="image[]" accept="image/*" multiple onchange="previewMedia(this, 'imagePreview')">
                </label>
                <div class="media-preview" id="imagePreview"></div>
            </div>
            <div>
                <label class="media-upload" onclick="this.querySelector('input').click()">
                    <div class="icon">🎵</div>
                    <div class="text">إرفاق مقطع صوتي</div>
                    <div class="hint">MP3, WAV — عدّة مقاطع ممكن</div>
                    <input type="file" name="audio[]" accept="audio/*" multiple onchange="previewMedia(this, 'audioPreview')">
                </label>
                <div class="media-preview" id="audioPreview"></div>
            </div>
            <div>
                <label class="media-upload" onclick="this.querySelector('input').click()">
                    <div class="icon">🎬</div>
                    <div class="text">إرفاق فيديو</div>
                    <div class="hint">MP4, WebM — عدّة مقاطع ممكن (حتى 100MB)</div>
                    <input type="file" name="video[]" accept="video/*" multiple onchange="previewMedia(this, 'videoPreview')">
                </label>
                <div class="media-preview" id="videoPreview"></div>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <label class="media-upload" onclick="this.querySelector('input').click()">
                <div class="icon">📄</div>
                <div class="text">إرفاق مستند أو ملف</div>
                <div class="hint">PDF, DOCX, PPTX — عدّة ملفّات ممكن</div>
                <input type="file" name="document[]" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx" multiple onchange="previewMedia(this, 'docPreview')">
            </label>
            <div class="media-preview" id="docPreview"></div>
        </div>
    </div>

    <!-- أزرار -->
    <div class="fade-in" style="display: flex; gap: 15px; justify-content: center; padding: 20px 0;">
        <button type="submit" class="btn-submit">💾 حفظ النشاط</button>
        <a href="{{ route('teacher.activity-bank.index') }}" class="btn-cancel">إلغاء</a>
    </div>
</form>

<script>
let currentType = '{{ old('type', 'quiz') }}';

function selectType(card, type) {
    document.querySelectorAll('.type-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
    currentType = type;

    // Show/hide image builder section
    const imageSection = document.getElementById('imageBuilderSection');
    if (imageSection) {
        imageSection.style.display = type === 'image_order' ? 'block' : 'none';
    }

    // Show/hide general questions builder (quiz/exercise)
    const qSection = document.getElementById('questionsBuilderSection');
    if (qSection) {
        qSection.style.display = (type === 'quiz' || type === 'exercise') ? 'block' : 'none';
    }

    // Show/hide quiz settings (اختبار فقط) و project settings (مشروع فقط)
    const quizSettings = document.getElementById('quizSettingsSection');
    if (quizSettings) {
        quizSettings.style.display = type === 'quiz' ? 'block' : 'none';
    }
    const projectSettings = document.getElementById('projectSettingsSection');
    if (projectSettings) {
        projectSettings.style.display = ['project', 'upload', 'creative', 'practical'].includes(type) ? 'block' : 'none';
    }

    // Rewrite hidden field with the correct shape for the selected type
    serializeQuestions();
}

// موزّع تسلسل موحّد: يكتب الشكل الصحيح إلى #questionsData حسب نوع النشاط
function serializeQuestions() {
    if (currentType === 'image_order') {
        updateImageData();
    } else if (currentType === 'quiz' || currentType === 'exercise') {
        updateJson();
    } else {
        document.getElementById('questionsData').value = '';
    }
}

function previewMedia(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!input.files || !input.files.length) { preview.style.display = 'none'; preview.innerHTML = ''; return; }
    preview.style.display = 'block';
    preview.innerHTML = '';
    // معاينة كل الملفّات المختارة (يدعم الاختيار المتعدّد)
    Array.from(input.files).forEach(function (file) {
        const url = URL.createObjectURL(file);
        const box = document.createElement('div');
        box.style.marginBottom = '10px';
        if (file.type.startsWith('image/')) {
            box.innerHTML = `<img src="${url}" alt="معاينة" style="max-height: 200px; max-width: 100%;">`;
        } else if (file.type.startsWith('audio/')) {
            box.innerHTML = `<audio controls src="${url}" style="width: 100%;"></audio>`;
        } else if (file.type.startsWith('video/')) {
            box.innerHTML = `<video controls src="${url}" style="max-height: 250px; width: 100%;"></video>`;
        } else {
            box.innerHTML = `<div style="display: flex; align-items: center; gap: 10px;"><span style="font-size: 28px;">📄</span><span>${file.name}</span></div>`;
        }
        box.innerHTML += `<div style="margin-top: 6px; font-size: 13px; color: #64748b;">${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</div>`;
        preview.appendChild(box);
    });
}

document.getElementById('isCreative').addEventListener('change', function() {
    const classroomSelect = document.getElementById('classroomSelect');
    if (this.checked) {
        classroomSelect.required = true;
        classroomSelect.style.borderColor = '#f59e0b';
    } else {
        classroomSelect.required = false;
        classroomSelect.style.borderColor = '#e2e8f0';
    }
});

// ================================
// وظائف بناء صور ترتيب الصور
// ================================
function addImageItem() {
    const list = document.getElementById('imagesList');
    const i = list.children.length;
    const div = document.createElement('div');
    div.className = 'form-card';
    div.style.cssText = 'padding: 20px; margin-bottom: 15px; border: 2px solid #e2e8f0;';
    div.dataset.index = i;
    div.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <label style="font-weight: 700; color: #334155;"><span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; font-weight: 700; font-size: 13px; margin-left: 8px;">${i+1}</span> صورة</label>
            <button type="button" onclick="this.closest('.form-card').remove(); updateImageData();" style="background: #fee2e2; color: #dc2626; border: none; padding: 6px 14px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600;">✕ حذف</button>
        </div>
        <div style="margin-bottom: 12px;">
            <label style="display: block; font-weight: 600; color: #64748b; margin-bottom: 6px; font-size: 13px;">رابط الصورة (URL) أو ارفع صورة</label>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <input type="url" class="form-input img-url" placeholder="https://example.com/image.jpg" oninput="previewImg(this); updateImageData()" style="flex:1;min-width:200px;">
                <label style="padding:10px 16px;background:#fef3c7;border:2px dashed #f59e0b;border-radius:10px;cursor:pointer;color:#92400e;font-weight:600;font-size:13px;white-space:nowrap;">
                    📤 رفع صورة
                    <input type="file" accept="image/*" style="display:none;" onchange="uploadOrderImage(this)">
                </label>
            </div>
            <div style="margin-top: 8px;">
                <img class="img-preview" src="" alt="" style="max-width:120px;max-height:100px;border-radius:8px;border:2px solid #e2e8f0;display:none;object-fit:cover;">
            </div>
        </div>
        <div>
            <label style="display: block; font-weight: 600; color: #64748b; margin-bottom: 6px; font-size: 13px;">عنوان الصورة (اختياري)</label>
            <input type="text" class="form-input img-caption" placeholder="وصف مختصر للصورة..." oninput="updateImageData()">
        </div>
    `;
    list.appendChild(div);
}

function previewImg(input) {
    const preview = input.closest('.form-card').querySelector('.img-preview');
    const url = input.value.trim();
    if (url) {
        preview.src = url;
        preview.style.display = 'block';
        preview.onerror = () => { preview.style.display = 'none'; };
    } else {
        preview.style.display = 'none';
    }
}

// رفع صورة عنصر ترتيب الصور عبر endpoint المحرّر العام
function uploadOrderImage(input) {
    var file = input.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) { alert('يرجى اختيار ملف صورة فقط'); return; }
    if (file.size > 5 * 1024 * 1024) { alert('حجم الصورة يجب أن يكون أقل من 5 ميجابايت'); return; }
    var card = input.closest('.form-card');
    var urlInput = card.querySelector('.img-url');
    var fd = new FormData();
    fd.append('image', file);
    fd.append('_token', '{{ csrf_token() }}');
    fetch('{{ route("editor.upload-image") }}', {
        method: 'POST',
        body: fd,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(function (r) { if (!r.ok) throw new Error('فشل رفع الصورة'); return r.json(); })
    .then(function (d) {
        if (d.url) {
            // الرابط المُعاد قد يكون نسبياً (/storage/...)؛ حقل type=url يتطلّب رابطاً مطلقاً،
            // ونحتاجه مطلقاً أيضاً كي يظهر في واجهة الطالب وصفحة المراجعة (http(s) فقط).
            urlInput.value = new URL(d.url, window.location.origin).href;
            previewImg(urlInput);
            updateImageData();
        } else { alert('فشل رفع الصورة'); }
    })
    .catch(function (e) { alert('حدث خطأ أثناء الرفع: ' + e.message); });
}

function updateImageData() {
    if (currentType !== 'image_order') return;
    const items = document.querySelectorAll('#imagesList .form-card');
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

// ==================================================================
// منشئ الأسئلة العام (اختبار/تمرين) — نفس عقد JSON الخاص بلوحة المشرف
// ==================================================================
let questions = [];

// تحميل الأسئلة القديمة (عند فشل التحقق) إن كان النشاط اختباراً/تمريناً
(function loadOldQuestions() {
    if (currentType !== 'quiz' && currentType !== 'exercise') return;
    const raw = document.getElementById('questionsData').value;
    if (!raw) return;
    try {
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) { questions = parsed; renderQuestions(); }
    } catch (e) { /* ليست أسئلة صالحة — تجاهل */ }
})();

function addQuestion() {
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
    // نخزّن الاثنين: نص الخيار (للتوافق الخلفي) + الفهرس (المعتمد في التصحيح)
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
    if (!container) return;
    container.innerHTML = '';

    questions.forEach((q, index) => {
        const card = document.createElement('div');
        card.className = 'q-card';

        const isOrderingType = ['word_order', 'sentence_order'].includes(q.type);
        let optionsHtml = '';

        if (q.options) {
            q.options.forEach((option, oIndex) => {
                const isCorrect = (q.correct_index !== undefined && q.correct_index !== null)
                    ? Number(q.correct_index) === oIndex
                    : (q.answer === option);
                optionsHtml += `
                    <div class="q-option-row">
                        ${!isOrderingType ? `
                            <div class="q-correct ${isCorrect ? 'selected' : ''}"
                                 onclick="setCorrectAnswer(${index}, ${oIndex})" title="اختر كإجابة صحيحة">
                                ${isCorrect ? '✓' : '○'}
                            </div>
                        ` : `
                            <span style="width:34px;text-align:center;font-weight:700;color:#64748b;">${oIndex + 1}</span>
                        `}
                        <input type="text" class="q-option-input" value="${escAttr(option)}"
                               onchange="updateOption(${index}, ${oIndex}, this.value)"
                               placeholder="${q.type === 'letter_choice' ? 'الحرف' : (q.type === 'word_order' ? 'الكلمة' : (q.type === 'sentence_order' ? 'الجملة' : 'الخيار'))} ${oIndex + 1}">
                        <button type="button" class="q-btn-sm q-btn-del" onclick="removeOption(${index}, ${oIndex})">🗑️</button>
                    </div>`;
            });
        }

        card.innerHTML = `
            <div class="q-head">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span class="q-num">${index + 1}</span>
                    <select class="q-type-select" onchange="updateQuestion(${index}, 'type', this.value)">
                        <option value="multiple_choice" ${q.type === 'multiple_choice' ? 'selected' : ''}>اختيار متعدد</option>
                        <option value="true_false" ${q.type === 'true_false' ? 'selected' : ''}>صح / خطأ</option>
                        <option value="short_answer" ${q.type === 'short_answer' ? 'selected' : ''}>إجابة قصيرة</option>
                        <option value="letter_choice" ${q.type === 'letter_choice' ? 'selected' : ''}>اختيار حروف</option>
                        <option value="word_order" ${q.type === 'word_order' ? 'selected' : ''}>ترتيب كلمات</option>
                        <option value="sentence_order" ${q.type === 'sentence_order' ? 'selected' : ''}>ترتيب جمل</option>
                    </select>
                </div>
                <button type="button" class="q-btn-sm q-btn-del" onclick="removeQuestion(${index})">🗑️ حذف</button>
            </div>

            <div class="q-fields">
                <div class="q-field-row">
                    <input type="text" class="form-input" value="${escAttr(q.question)}"
                           onchange="updateQuestion(${index}, 'question', this.value)"
                           placeholder="نص السؤال...">
                    <input type="number" class="form-input" value="${q.points ?? 10}"
                           onchange="updateQuestion(${index}, 'points', parseInt(this.value))"
                           placeholder="الدرجة" min="1">
                </div>

                ${mediaRowHtml(q, index)}

                ${q.type === 'letter_choice' ? `
                    <div class="q-field-row">
                        <input type="text" class="form-input" value="${escAttr(q.word || '')}"
                               onchange="updateQuestion(${index}, 'word', this.value)"
                               placeholder="الكلمة المستهدفة (مثال: صلاة)">
                    </div>
                ` : ''}

                ${(q.type === 'multiple_choice' || q.type === 'true_false' || q.type === 'letter_choice') ? `
                    <div class="q-options">
                        <label class="q-label">${q.type === 'letter_choice' ? 'الحروف (اضغط على ○ لتحديد الإجابة الصحيحة)' : 'الخيارات (اضغط على ○ لتحديد الإجابة الصحيحة)'}</label>
                        ${optionsHtml}
                        ${(q.type === 'multiple_choice' || q.type === 'letter_choice') ? `
                            <button type="button" class="q-btn-sm q-btn-add" onclick="addOption(${index})">➕ إضافة ${q.type === 'letter_choice' ? 'حرف' : 'خيار'}</button>
                        ` : ''}
                    </div>
                ` : ''}

                ${q.type === 'short_answer' ? `
                    <div class="q-options">
                        <label class="q-label" style="margin-bottom:6px;display:block;">الإجابة الصحيحة (يقارَن بها نص الطالب بعد تطبيع المسافات والتشكيل)</label>
                        <input type="text" class="form-input" value="${escAttr(q.answer || '')}"
                               onchange="updateQuestion(${index}, 'answer', this.value)"
                               placeholder="مثال: الصلاة الوسطى">
                    </div>
                ` : ''}

                ${(q.type === 'word_order' || q.type === 'sentence_order') ? `
                    <div class="q-options">
                        <label class="q-label">${q.type === 'word_order' ? 'الكلمات (سيتم ترتيبها عشوائياً للطالب)' : 'الجمل (سيتم ترتيبها عشوائياً للطالب)'}</label>
                        ${optionsHtml}
                        <button type="button" class="q-btn-sm q-btn-add" onclick="addOption(${index})">➕ إضافة ${q.type === 'word_order' ? 'كلمة' : 'جملة'}</button>
                        <small class="form-hint">الترتيب الحالي هو الترتيب الصحيح</small>
                    </div>
                ` : ''}
            </div>
        `;
        container.appendChild(card);
    });

    updateJson();
}

function updateJson() {
    if (currentType !== 'quiz' && currentType !== 'exercise') return;
    document.getElementById('questionsData').value = JSON.stringify(questions);
}

// تهريب القيم داخل سمات HTML
function escAttr(v) {
    return String(v == null ? '' : v)
        .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
        .replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// ===== وسائط لكل سؤال (صورة/فيديو/صوت) — مطابق للوحة المشرف، رفع عبر editor.upload-image =====
function mediaRowHtml(q, index) {
    const mtype = q.media_type || '';
    const label = mtype === 'image' ? 'الصورة' : (mtype === 'video' ? 'الفيديو' : 'الصوت');
    return `
        <div class="q-options" style="margin-top:10px;background:#fafbff;border:1px dashed #cbd5e1;border-radius:10px;padding:10px 12px;">
            <label class="q-label" style="display:block;margin-bottom:6px;">🎬 وسائط للسؤال (اختياري)</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <select class="q-option-input" style="max-width:150px;flex:0 0 auto;" onchange="updateQuestionMedia(${index}, 'media_type', this.value)">
                    <option value="" ${mtype === '' ? 'selected' : ''}>لا شيء</option>
                    <option value="image" ${mtype === 'image' ? 'selected' : ''}>🖼️ صورة</option>
                    <option value="video" ${mtype === 'video' ? 'selected' : ''}>🎬 فيديو</option>
                    <option value="audio" ${mtype === 'audio' ? 'selected' : ''}>🎵 صوت</option>
                </select>
                ${mtype ? `
                    <input type="url" class="q-option-input" style="flex:1;min-width:200px;" value="${escAttr(q.media_url || '')}"
                           onchange="updateQuestionMedia(${index}, 'media_url', this.value)"
                           placeholder="رابط ${label} (URL)">
                    ${mtype === 'image' ? `
                        <label style="padding:8px 12px;background:#eef2ff;border:2px dashed #667eea;border-radius:8px;cursor:pointer;color:#4338ca;font-weight:600;font-size:12px;white-space:nowrap;">
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
    fetch('{{ route("editor.upload-image") }}', {
        method: 'POST',
        body: fd,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(function (r) { if (!r.ok) throw new Error('فشل رفع الصورة'); return r.json(); })
    .then(function (d) { if (d.url) { questions[index].media_url = new URL(d.url, window.location.origin).href; renderQuestions(); } else { alert('فشل رفع الصورة'); } })
    .catch(function (e) { alert('حدث خطأ أثناء الرفع: ' + e.message); });
}
</script>

@endsection
