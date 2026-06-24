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
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #fafbfc;
    }
    .media-upload:hover { border-color: #667eea; background: #f0f4ff; }
    .media-upload input[type="file"] { display: none; }
    .media-upload .icon { font-size: 40px; margin-bottom: 10px; }
    .media-upload .text { color: #64748b; font-size: 14px; font-weight: 600; }
    .media-upload .hint { color: #94a3b8; font-size: 12px; margin-top: 6px; }
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
            <textarea name="description" class="form-textarea" placeholder="اكتب وصفاً تفصيلياً للنشاط... ماذا يتوقع من الطالب؟ ما هي التعليمات؟">{{ old('description') }}</textarea>
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

        <div class="creative-toggle">
            <input type="checkbox" name="is_creative" id="isCreative" value="1" {{ old('is_creative') ? 'checked' : '' }}>
            <div>
                <div style="font-weight: 700; color: #92400e; font-size: 16px;">✨ نشاط إبداعي (جماعي للفصل)</div>
                <div style="font-size: 13px; color: #a16207; margin-top: 4px;">يتطلب تحديد فصل دراسي</div>
            </div>
        </div>
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

    <!-- الوسائط المتعددة -->
    <div class="form-card fade-in">
        <h3>📎 الوسائط المتعددة (اختياري)</h3>
        <p class="form-hint" style="margin-bottom: 25px;">أرفق ملفات وسائط لتعزيز النشاط التعليمي</p>
        
        <div class="grid-3">
            <div>
                <label class="media-upload" onclick="this.querySelector('input').click()">
                    <div class="icon">🖼️</div>
                    <div class="text">إرفاق صورة</div>
                    <div class="hint">JPG, PNG - حتى 5MB</div>
                    <input type="file" name="image" accept="image/*" onchange="previewMedia(this, 'imagePreview')">
                </label>
                <div class="media-preview" id="imagePreview"></div>
            </div>
            <div>
                <label class="media-upload" onclick="this.querySelector('input').click()">
                    <div class="icon">🎵</div>
                    <div class="text">إرفاق مقطع صوتي</div>
                    <div class="hint">MP3, WAV - حتى 10MB</div>
                    <input type="file" name="audio" accept="audio/*" onchange="previewMedia(this, 'audioPreview')">
                </label>
                <div class="media-preview" id="audioPreview"></div>
            </div>
            <div>
                <label class="media-upload" onclick="this.querySelector('input').click()">
                    <div class="icon">🎬</div>
                    <div class="text">إرفاق فيديو</div>
                    <div class="hint">MP4, WebM - حتى 50MB</div>
                    <input type="file" name="video" accept="video/*" onchange="previewMedia(this, 'videoPreview')">
                </label>
                <div class="media-preview" id="videoPreview"></div>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <label class="media-upload" onclick="this.querySelector('input').click()">
                <div class="icon">📄</div>
                <div class="text">إرفاق مستند أو ملف</div>
                <div class="hint">PDF, DOCX, PPTX - حتى 20MB</div>
                <input type="file" name="document" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx" onchange="previewMedia(this, 'docPreview')">
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
}

function previewMedia(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const url = URL.createObjectURL(file);
        preview.style.display = 'block';
        
        if (file.type.startsWith('image/')) {
            preview.innerHTML = `<img src="${url}" alt="معاينة" style="max-height: 200px;">`;
        } else if (file.type.startsWith('audio/')) {
            preview.innerHTML = `<audio controls src="${url}" style="width: 100%;"></audio>`;
        } else if (file.type.startsWith('video/')) {
            preview.innerHTML = `<video controls src="${url}" style="max-height: 250px; width: 100%;"></video>`;
        } else {
            preview.innerHTML = `<div style="display: flex; align-items: center; gap: 10px;"><span style="font-size: 28px;">📄</span><span>${file.name}</span></div>`;
        }
        
        preview.innerHTML += `<div style="margin-top: 8px; font-size: 13px; color: #64748b;">${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</div>`;
    }
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
            <label style="display: block; font-weight: 600; color: #64748b; margin-bottom: 6px; font-size: 13px;">رابط الصورة (URL)</label>
            <input type="url" class="form-input img-url" placeholder="https://example.com/image.jpg" oninput="previewImg(this); updateImageData()">
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

function updateImageData() {
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
</script>

@endsection
