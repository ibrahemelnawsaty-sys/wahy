@extends('layouts.teacher')

@section('title', 'إضافة سؤال جديد')

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
        border-color: #10b981;
        outline: none;
        box-shadow: 0 0 0 4px rgba(16,185,129,0.1);
        background: white;
    }
    .form-textarea { resize: vertical; min-height: 120px; }
    .form-hint { font-size: 13px; color: #94a3b8; margin-top: 6px; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
    @media (max-width: 768px) {
        .grid-2, .grid-3 { grid-template-columns: 1fr; }
    }

    .option-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #f8fafc;
        border-radius: 12px;
        margin-bottom: 10px;
        border: 2px solid #e2e8f0;
        transition: all 0.3s;
    }
    .option-row:hover { border-color: #10b981; }
    .option-row input[type="text"] {
        flex: 1;
        padding: 10px 14px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        background: white;
    }
    .option-row input[type="text"]:focus {
        border-color: #10b981;
        outline: none;
    }
    .option-correct-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #475569;
        white-space: nowrap;
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 8px;
        background: #f1f5f9;
        transition: all 0.3s;
    }
    .option-correct-label:has(input:checked) {
        background: #dcfce7;
        color: #166534;
    }
    .btn-remove {
        background: #fee2e2;
        color: #991b1b;
        border: none;
        padding: 8px 14px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
    }
    .btn-remove:hover { background: #fecaca; }
    .btn-add-option {
        background: #dcfce7;
        color: #166534;
        border: 2px dashed #86efac;
        padding: 12px 20px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 600;
        width: 100%;
        font-size: 14px;
        transition: all 0.3s;
    }
    .btn-add-option:hover { background: #bbf7d0; border-color: #4ade80; }

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
    .media-upload:hover { border-color: #10b981; background: #f0fdf4; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,185,129,0.12); }
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

    .btn-submit {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 16px 40px;
        border: none;
        border-radius: 14px;
        font-size: 17px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 8px 25px rgba(16,185,129,0.3);
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 35px rgba(16,185,129,0.4); }
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
<div class="fade-in" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 25px; padding: 35px; margin-bottom: 30px; box-shadow: 0 15px 50px rgba(16, 185, 129, 0.3); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: white; margin-bottom: 8px;">➕ إضافة سؤال جديد</h1>
            <p style="color: rgba(255,255,255,0.95); font-size: 16px;">أنشئ سؤالاً احترافياً مع دعم الوسائط المتعددة</p>
        </div>
        <a href="{{ route('teacher.question-bank.index') }}" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: white; padding: 12px 24px; border-radius: 12px; border: 2px solid rgba(255,255,255,0.3); font-weight: 700; text-decoration: none;">
            ← العودة لبنك الأسئلة
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

<form method="POST" action="{{ route('teacher.question-bank.store') }}" enctype="multipart/form-data">
    @csrf

    <!-- بيانات السؤال الأساسية -->
    <div class="form-card fade-in">
        <h3>📝 بيانات السؤال الأساسية</h3>
        
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> عنوان السؤال</label>
            <input type="text" name="title" class="form-input" placeholder="مثال: ما هي أركان الإسلام الخمسة؟" required value="{{ old('title') }}">
        </div>
        
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> نص السؤال</label>
            <textarea name="question_text" class="form-textarea" placeholder="اكتب نص السؤال الكامل هنا... يمكنك كتابة سؤال مطول مع التفاصيل" required>{{ old('question_text') }}</textarea>
        </div>
        
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> نوع السؤال</label>
                <select name="question_type" id="questionType" class="form-select" required onchange="toggleQuestionType()">
                    <option value="multiple_choice" {{ old('question_type') == 'multiple_choice' ? 'selected' : '' }}>اختيار متعدد</option>
                    <option value="true_false" {{ old('question_type') == 'true_false' ? 'selected' : '' }}>صح / خطأ</option>
                    <option value="short_answer" {{ old('question_type') == 'short_answer' ? 'selected' : '' }}>إجابة قصيرة</option>
                    <option value="essay" {{ old('question_type') == 'essay' ? 'selected' : '' }}>مقال</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> مستوى الصعوبة</label>
                <select name="difficulty" class="form-select" required>
                    <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>🟢 سهل</option>
                    <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>🟡 متوسط</option>
                    <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>🔴 صعب</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> النقاط</label>
                <input type="number" name="points" class="form-input" value="{{ old('points', 10) }}" min="1" max="50" required>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">الدرس المرتبط</label>
            <select name="lesson_id" class="form-select">
                <option value="">اختر درس (اختياري)</option>
                @foreach($lessons as $lesson)
                <option value="{{ $lesson->id }}" {{ old('lesson_id') == $lesson->id ? 'selected' : '' }}>{{ $lesson->title }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- خيارات الاختيار المتعدد -->
    <div class="form-card fade-in" id="optionsSection">
        <h3>📋 خيارات الإجابة</h3>
        <p class="form-hint" style="margin-bottom: 20px;">أضف الخيارات وحدد الإجابة/الإجابات الصحيحة</p>
        
        <div id="optionsContainer">
            <div class="option-row">
                <span style="font-weight: 700; color: #10b981; min-width: 24px;">1</span>
                <input type="text" name="options[0][text]" placeholder="نص الخيار الأول" required value="{{ old('options.0.text') }}">
                <label class="option-correct-label"><input type="checkbox" name="options[0][is_correct]" value="1" {{ old('options.0.is_correct') ? 'checked' : '' }}> صحيح</label>
                <button type="button" onclick="removeOption(this)" class="btn-remove">✕</button>
            </div>
            <div class="option-row">
                <span style="font-weight: 700; color: #10b981; min-width: 24px;">2</span>
                <input type="text" name="options[1][text]" placeholder="نص الخيار الثاني" required value="{{ old('options.1.text') }}">
                <label class="option-correct-label"><input type="checkbox" name="options[1][is_correct]" value="1" {{ old('options.1.is_correct') ? 'checked' : '' }}> صحيح</label>
                <button type="button" onclick="removeOption(this)" class="btn-remove">✕</button>
            </div>
            <div class="option-row">
                <span style="font-weight: 700; color: #10b981; min-width: 24px;">3</span>
                <input type="text" name="options[2][text]" placeholder="نص الخيار الثالث" value="{{ old('options.2.text') }}">
                <label class="option-correct-label"><input type="checkbox" name="options[2][is_correct]" value="1" {{ old('options.2.is_correct') ? 'checked' : '' }}> صحيح</label>
                <button type="button" onclick="removeOption(this)" class="btn-remove">✕</button>
            </div>
            <div class="option-row">
                <span style="font-weight: 700; color: #10b981; min-width: 24px;">4</span>
                <input type="text" name="options[3][text]" placeholder="نص الخيار الرابع" value="{{ old('options.3.text') }}">
                <label class="option-correct-label"><input type="checkbox" name="options[3][is_correct]" value="1" {{ old('options.3.is_correct') ? 'checked' : '' }}> صحيح</label>
                <button type="button" onclick="removeOption(this)" class="btn-remove">✕</button>
            </div>
        </div>
        <button type="button" onclick="addOption()" class="btn-add-option">➕ إضافة خيار جديد</button>
    </div>

    <!-- إجابة قصيرة / صح وخطأ -->
    <div class="form-card fade-in" id="correctAnswerSection" style="display: none;">
        <h3>✅ الإجابة الصحيحة</h3>
        <div class="form-group">
            <label class="form-label">اكتب الإجابة الصحيحة</label>
            <input type="text" name="correct_answer" class="form-input" placeholder="اكتب الإجابة الصحيحة هنا" value="{{ old('correct_answer') }}">
        </div>
    </div>

    <!-- الوسائط المتعددة -->
    <div class="form-card fade-in">
        <h3>📎 الوسائط المتعددة (اختياري)</h3>
        <p class="form-hint" style="margin-bottom: 25px;">أرفق صوراً أو مقاطع صوتية أو فيديو لتعزيز السؤال</p>
        
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
    </div>

    <!-- شرح الإجابة -->
    <div class="form-card fade-in">
        <h3>💡 شرح الإجابة (اختياري)</h3>
        <div class="form-group">
            <textarea name="explanation" class="form-textarea" placeholder="اكتب شرحاً توضيحياً للإجابة الصحيحة يظهر للطالب بعد حل السؤال...">{{ old('explanation') }}</textarea>
        </div>
    </div>

    <!-- أزرار -->
    <div class="fade-in" style="display: flex; gap: 15px; justify-content: center; padding: 20px 0;">
        <button type="submit" class="btn-submit">💾 حفظ السؤال</button>
        <a href="{{ route('teacher.question-bank.index') }}" class="btn-cancel">إلغاء</a>
    </div>
</form>

<script>
let optionIndex = 4;

function toggleQuestionType() {
    const type = document.getElementById('questionType').value;
    const optionsSection = document.getElementById('optionsSection');
    const correctAnswerSection = document.getElementById('correctAnswerSection');
    
    if (type === 'multiple_choice') {
        optionsSection.style.display = 'block';
        correctAnswerSection.style.display = 'none';
        document.querySelectorAll('#optionsContainer .option-row:nth-child(-n+2) input[type="text"]').forEach(i => i.required = true);
    } else if (type === 'true_false' || type === 'short_answer') {
        optionsSection.style.display = 'none';
        correctAnswerSection.style.display = 'block';
        document.querySelectorAll('#optionsContainer input[type="text"]').forEach(i => i.required = false);
    } else {
        optionsSection.style.display = 'none';
        correctAnswerSection.style.display = 'none';
        document.querySelectorAll('#optionsContainer input[type="text"]').forEach(i => i.required = false);
    }
}

function addOption() {
    optionIndex++;
    const container = document.getElementById('optionsContainer');
    const div = document.createElement('div');
    div.className = 'option-row';
    div.innerHTML = `
        <span style="font-weight: 700; color: #10b981; min-width: 24px;">${optionIndex}</span>
        <input type="text" name="options[${optionIndex - 1}][text]" placeholder="نص الخيار ${optionIndex}" style="flex: 1; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; background: white;">
        <label class="option-correct-label"><input type="checkbox" name="options[${optionIndex - 1}][is_correct]" value="1"> صحيح</label>
        <button type="button" onclick="removeOption(this)" class="btn-remove">✕</button>
    `;
    container.appendChild(div);
}

function removeOption(btn) {
    const container = document.getElementById('optionsContainer');
    if (container.querySelectorAll('.option-row').length > 2) {
        btn.closest('.option-row').remove();
        // Re-number
        container.querySelectorAll('.option-row').forEach((row, i) => {
            row.querySelector('span').textContent = i + 1;
        });
    } else {
        alert('يجب أن يكون هناك خياران على الأقل');
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
        }
        
        preview.innerHTML += `<div style="margin-top: 8px; font-size: 13px; color: #64748b;">${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</div>`;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', toggleQuestionType);
</script>

@endsection
