@extends('layouts.admin')

@section('page-title', 'إضافة درس جديد')

@section('content')
<style>
.form-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 800px;
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
    min-height: 150px;
    resize: vertical;
}

.type-selector {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
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

.conditional-field {
    display: none;
}

.conditional-field.active {
    display: flex;
}

.file-upload-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.file-upload-input {
    display: none;
}

.file-upload-label {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px 16px;
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s;
    color: #475569;
    font-weight: 500;
    gap: 8px;
}

.file-upload-label:hover {
    border-color: var(--color-primary);
    background: #f1f5f9;
    color: var(--color-primary);
}

.file-preview-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
    margin-top: 8px;
}

.file-preview-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.file-preview-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
}

.file-preview-item video,
.file-preview-item audio {
    width: 100%;
    max-height: 120px;
    display: block;
}

.file-preview-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    transition: all 0.2s;
}

.file-preview-remove:hover {
    background: #b91c1c;
    transform: scale(1.1);
}

.media-section {
    background: #f8fafc;
    padding: 20px;
    border-radius: 8px;
    margin-top: 8px;
}

.media-section-title {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 12px;
    font-size: 14px;
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
.rte-btn {
    padding: 4px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    font-size: 13px;
    color: #334155;
    transition: all 0.15s;
    line-height: 1.2;
}

.rte-btn:hover {
    background: #e2e8f0;
    border-color: #94a3b8;
}

#contentEditor:empty::before {
    content: 'محتوى الدرس...';
    color: #94a3b8;
    pointer-events: none;
}
</style>

<div class="form-card">
    <h2 style="margin-bottom: 24px;">📚 إضافة درس جديد</h2>

    <form method="POST" action="{{ route('admin.lessons.store') }}" id="lessonForm" enctype="multipart/form-data">
        @csrf

        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label required">المفهوم</label>
                <select name="concept_id" class="form-select" required>
                    <option value="">اختر المفهوم</option>
                    @foreach($concepts as $concept)
                    <option value="{{ $concept->id }}" {{ old('concept_id', $selectedConcept) == $concept->id ? 'selected' : '' }}>
                        {{ $concept->value->icon }} {{ $concept->name }}
                    </option>
                    @endforeach
                </select>
                @error('concept_id')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label required">عنوان الدرس</label>
                <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="مثال: درس عن أهمية الصدق" required>
                @error('title')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label required">نوع الدرس</label>
                <div class="type-selector">
                    <div class="type-option">
                        <input type="radio" name="type" value="text" id="type_text" {{ old('type', 'mixed') == 'text' ? 'checked' : '' }} required>
                        <label for="type_text" class="type-label">
                            <span class="type-icon">📝</span>
                            <span class="type-name">نص</span>
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="type" value="video" id="type_video" {{ old('type') == 'video' ? 'checked' : '' }}>
                        <label for="type_video" class="type-label">
                            <span class="type-icon">🎥</span>
                            <span class="type-name">فيديو</span>
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="type" value="audio" id="type_audio" {{ old('type') == 'audio' ? 'checked' : '' }}>
                        <label for="type_audio" class="type-label">
                            <span class="type-icon">🎵</span>
                            <span class="type-name">صوت</span>
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="type" value="mixed" id="type_mixed" {{ old('type', 'mixed') == 'mixed' ? 'checked' : '' }}>
                        <label for="type_mixed" class="type-label">
                            <span class="type-icon">🎬</span>
                            <span class="type-name">مختلط</span>
                        </label>
                    </div>
                </div>
                <small style="color: #64748b; font-size: 13px; margin-top: 8px; display: block;">اختر "مختلط" لإضافة نص وصور وفيديو وصوت معاً</small>
                @error('type')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <!-- المحتوى النصي -->
            <div class="form-group full-width conditional-field" id="content_field">
                <label class="form-label">المحتوى النصي</label>
                {{-- Quill Rich Text Editor (N2 — يعمل بشكل موثوق على جميع المتصفحات بدلاً من execCommand المُهمل) --}}
                <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
                <style>
                    #quillEditor { min-height: 280px; font-size: 15px; line-height: 1.9; direction: rtl; text-align: right; background: white; border-bottom-right-radius: 8px; border-bottom-left-radius: 8px; }
                    .ql-toolbar { border-color: #e2e8f0 !important; border-top-right-radius: 8px; border-top-left-radius: 8px; background: #f8fafc; }
                    .ql-container { border-color: #e2e8f0 !important; }
                    .ql-editor { direction: rtl !important; text-align: right !important; min-height: 280px; font-family: 'IBM Plex Sans Arabic', Tahoma, sans-serif; }
                    .ql-editor.ql-blank::before { right: 15px; left: auto; font-style: normal; }
                </style>
                <div id="quillEditor">{!! safe_html(old('content')) !!}</div>
                <textarea name="content" id="contentHidden" style="display:none;">{{ old('content') }}</textarea>
                <small style="color: #64748b; font-size: 13px; margin-top: 8px; display: block;">💡 استخدم أزرار التنسيق أعلاه. التلوين يعمل عبر اختيار النص ثم النقر على لوحة الألوان.</small>
                @error('content')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror

                <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Quill === 'undefined' || !document.getElementById('quillEditor')) return;

                    const quill = new Quill('#quillEditor', {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'align': [] }],
                                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                ['link', 'image', 'blockquote'],
                                ['clean']
                            ]
                        },
                        placeholder: 'اكتب محتوى الدرس هنا...'
                    });

                    // مزامنة الـ HTML إلى textarea مخفي قبل الإرسال
                    const form = document.getElementById('quillEditor').closest('form');
                    if (form) {
                        form.addEventListener('submit', function() {
                            document.getElementById('contentHidden').value = quill.root.innerHTML;
                        });
                    }

                    // معالج خاص لرفع الصور: استخدم endpoint لاحقاً
                    const toolbar = quill.getModule('toolbar');
                    toolbar.addHandler('image', function() {
                        const input = document.createElement('input');
                        input.type = 'file';
                        input.accept = 'image/*';
                        input.onchange = async () => {
                            const file = input.files[0];
                            if (!file) return;
                            const formData = new FormData();
                            formData.append('image', file);
                            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                            try {
                                const r = await fetch('{{ route("editor.upload-image") }}', { method: 'POST', body: formData });
                                const json = await r.json();
                                if (json.url) {
                                    const range = quill.getSelection(true);
                                    quill.insertEmbed(range.index, 'image', json.url, 'user');
                                    quill.setSelection(range.index + 1);
                                } else {
                                    alert('فشل رفع الصورة: ' + (json.message || ''));
                                }
                            } catch (e) {
                                alert('خطأ في رفع الصورة');
                            }
                        };
                        input.click();
                    });
                });
                </script>
            </div>

            <!-- الصور -->
            <div class="form-group full-width conditional-field" id="images_field">
                <label class="form-label">صور الدرس</label>
                <div class="file-upload-wrapper">
                    <input type="file" name="images[]" id="imagesInput" class="file-upload-input" accept="image/*" multiple>
                    <label for="imagesInput" class="file-upload-label">
                        <span>📷</span>
                        <span>اختر صور (يمكن اختيار عدة صور)</span>
                    </label>
                    <div class="file-preview-container" id="imagesPreview"></div>
                    <small style="color: #64748b; font-size: 13px;">الصيغ المدعومة: JPEG, PNG, JPG, GIF, SVG, WebP (حد أقصى 5MB لكل صورة)</small>
                    @error('images')
                        <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- الفيديو -->
            <div class="form-group full-width conditional-field" id="video_field">
                <div class="media-section">
                    <div class="media-section-title">🎥 الفيديو</div>
                    
                    <div style="margin-bottom: 16px;">
                        <label class="form-label">رابط الفيديو (اختياري)</label>
                        <input type="url" name="video_url" class="form-input" value="{{ old('video_url') }}" placeholder="مثال: https://youtube.com/watch?v=abc123">
                        <small style="color: #64748b; font-size: 13px;">رابط YouTube أو Vimeo أو رابط مباشر للفيديو</small>
                        @error('video_url')
                            <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">أو ارفع ملف فيديو (اختياري)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="video_file" id="videoFileInput" class="file-upload-input" accept="video/*">
                            <label for="videoFileInput" class="file-upload-label">
                                <span>🎬</span>
                                <span>اختر ملف فيديو</span>
                            </label>
                            <div class="file-preview-container" id="videoPreview"></div>
                            <small style="color: #64748b; font-size: 13px;">الصيغ المدعومة: MP4, MOV, AVI, WMV, WebM (حد أقصى 50MB)</small>
                            @error('video_file')
                                <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- الصوت -->
            <div class="form-group full-width conditional-field" id="audio_field">
                <div class="media-section">
                    <div class="media-section-title">🎵 الصوت</div>
                    
                    <div style="margin-bottom: 16px;">
                        <label class="form-label">رابط الصوت (اختياري)</label>
                        <input type="url" name="audio_url" class="form-input" value="{{ old('audio_url') }}" placeholder="مثال: https://cdn.qiyamm.sa/audio.mp3">
                        <small style="color: #64748b; font-size: 13px;">رابط مباشر لملف الصوت</small>
                        @error('audio_url')
                            <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">أو ارفع ملف صوت (اختياري)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="audio_file" id="audioFileInput" class="file-upload-input" accept="audio/*">
                            <label for="audioFileInput" class="file-upload-label">
                                <span>🎧</span>
                                <span>اختر ملف صوت</span>
                            </label>
                            <div class="file-preview-container" id="audioPreview"></div>
                            <small style="color: #64748b; font-size: 13px;">الصيغ المدعومة: MP3, WAV, OGG, M4A (حد أقصى 10MB)</small>
                            @error('audio_file')
                                <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">المدة (بالدقائق)</label>
                <input type="number" name="duration" class="form-input" value="{{ old('duration') }}" min="0" placeholder="15">
                @error('duration')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">النقاط المكتسبة</label>
                <input type="number" name="points" class="form-input" value="{{ old('points', 10) }}" min="0" placeholder="10">
                @error('points')
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
                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                </select>
                @error('status')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <!-- نظام مكافأة الالتزام (Streak) -->
            <div class="form-group full-width" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 20px; border-radius: 12px; margin-top: 20px; border: 2px solid #f59e0b;">
                <h3 style="margin: 0 0 16px 0; color: #92400e; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    <span>🔥</span> نظام مكافأة الالتزام اليومي
                </h3>
                <p style="color: #92400e; font-size: 13px; margin-bottom: 16px;">
                    عند تفعيل هذا النظام، يحصل الطالب على نقاط إضافية إذا أكمل أنشطة في عدد معين من الأيام
                </p>
                
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                    <input type="checkbox" name="streak_enabled" id="streak_enabled" value="1" {{ old('streak_enabled') ? 'checked' : '' }} style="width: 20px; height: 20px; cursor: pointer;">
                    <label for="streak_enabled" style="font-weight: 600; color: #92400e; cursor: pointer;">تفعيل نظام المكافأة</label>
                </div>

                <div id="streak_fields" style="display: {{ old('streak_enabled') ? 'grid' : 'none' }}; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                    <div>
                        <label class="form-label" style="color: #92400e;">الحد الأدنى من الأيام</label>
                        <input type="number" name="streak_min_days" class="form-input" value="{{ old('streak_min_days', 5) }}" min="1" max="30" placeholder="5">
                        <small style="color: #92400e; font-size: 11px;">أقل عدد أيام للحصول على المكافأة</small>
                    </div>
                    <div>
                        <label class="form-label" style="color: #92400e;">الحد الأعلى من الأيام</label>
                        <input type="number" name="streak_max_days" class="form-input" value="{{ old('streak_max_days', 10) }}" min="1" max="60" placeholder="10">
                        <small style="color: #92400e; font-size: 11px;">أقصى مدة متوقعة للدرس</small>
                    </div>
                    <div>
                        <label class="form-label" style="color: #92400e;">نقاط المكافأة</label>
                        <input type="number" name="streak_bonus_points" class="form-input" value="{{ old('streak_bonus_points', 50) }}" min="0" placeholder="50">
                        <small style="color: #92400e; font-size: 11px;">النقاط الإضافية عند التأهل</small>
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.5); padding: 12px; border-radius: 8px; margin-top: 12px;" id="streak_example">
                    <strong style="color: #92400e;">📝 مثال:</strong>
                    <span style="color: #78350f;" id="streak_example_text">إذا أكمل الطالب أنشطة في 5 أيام أو أكثر، سيحصل على 50 نقطة إضافية</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 حفظ الدرس</button>
            <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

<!-- RTE Link Popup -->
<div id="rteLinkPopup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); backdrop-filter:blur(8px); z-index:10000; justify-content:center; align-items:center;">
    <div style="background:rgba(255,255,255,0.95); backdrop-filter:blur(20px); border-radius:16px; padding:32px; width:90%; max-width:460px; box-shadow:0 25px 60px rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.6); animation:rtePopIn 0.3s ease;">
        <h3 style="margin:0 0 20px; font-size:20px; color:#1e293b; display:flex; align-items:center; gap:10px;">🔗 إدراج رابط</h3>
        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:600; color:#334155; font-size:14px; margin-bottom:6px;">نص الرابط</label>
            <input type="text" id="rteLinkText" placeholder="أدخل النص الذي سيظهر..." style="width:100%; padding:12px 16px; border:2px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; transition:border 0.2s; box-sizing:border-box;" onfocus="this.style.borderColor='var(--color-primary)'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <div style="margin-bottom:24px;">
            <label style="display:block; font-weight:600; color:#334155; font-size:14px; margin-bottom:6px;">رابط URL</label>
            <input type="url" id="rteLinkUrl" placeholder="https://example.com" dir="ltr" style="width:100%; padding:12px 16px; border:2px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; transition:border 0.2s; box-sizing:border-box;" onfocus="this.style.borderColor='var(--color-primary)'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <button type="button" onclick="closeRtePopup('rteLinkPopup')" style="padding:10px 24px; border-radius:8px; border:2px solid #e2e8f0; background:white; color:#475569; font-weight:600; cursor:pointer; font-size:14px; transition:all 0.2s;">إلغاء</button>
            <button type="button" onclick="confirmRteLink()" style="padding:10px 24px; border-radius:8px; border:none; background:var(--color-primary); color:white; font-weight:600; cursor:pointer; font-size:14px; transition:all 0.2s; box-shadow:0 4px 12px rgba(60,203,138,0.3);">✅ إدراج</button>
        </div>
    </div>
</div>

<!-- RTE Image Popup -->
<div id="rteImagePopup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); backdrop-filter:blur(8px); z-index:10000; justify-content:center; align-items:center;">
    <div style="background:rgba(255,255,255,0.95); backdrop-filter:blur(20px); border-radius:16px; padding:32px; width:90%; max-width:460px; box-shadow:0 25px 60px rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.6); animation:rtePopIn 0.3s ease;">
        <h3 style="margin:0 0 20px; font-size:20px; color:#1e293b; display:flex; align-items:center; gap:10px;">🖼️ إدراج صورة</h3>
        <div style="margin-bottom:14px; padding:14px; background:#f0f9ff; border-radius:8px; border:2px dashed #3b82f6;">
            <label for="rteImageFile" style="display:flex; align-items:center; gap:10px; cursor:pointer; color:#1e40af; font-weight:600; font-size:14px;">
                <span style="font-size:22px;">📤</span>
                <span>ارفع صورة من جهازك</span>
            </label>
            <input type="file" id="rteImageFile" accept="image/*" style="display:none;" onchange="uploadRteImage(this)">
            <div id="rteImageUploadStatus" style="margin-top:8px; font-size:13px; color:#64748b; display:none;"></div>
        </div>
        <div style="text-align:center; margin:8px 0; font-size:12px; color:#94a3b8;">أو</div>
        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:600; color:#334155; font-size:14px; margin-bottom:6px;">رابط الصورة من الإنترنت</label>
            <input type="url" id="rteImageUrl" placeholder="https://example.com/image.jpg" dir="ltr" style="width:100%; padding:12px 16px; border:2px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; transition:border 0.2s; box-sizing:border-box;" onfocus="this.style.borderColor='var(--color-primary)'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <div style="margin-bottom:24px;">
            <label style="display:block; font-weight:600; color:#334155; font-size:14px; margin-bottom:6px;">النص البديل (اختياري)</label>
            <input type="text" id="rteImageAlt" placeholder="وصف الصورة..." style="width:100%; padding:12px 16px; border:2px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; transition:border 0.2s; box-sizing:border-box;" onfocus="this.style.borderColor='var(--color-primary)'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <div id="rteImagePreviewBox" style="display:none; margin-bottom:16px; text-align:center; background:#f8fafc; border-radius:8px; padding:12px; border:2px dashed #e2e8f0;">
            <img id="rteImagePreviewImg" src="" alt="معاينة" style="max-width:100%; max-height:150px; border-radius:8px;">
        </div>
        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <button type="button" onclick="closeRtePopup('rteImagePopup')" style="padding:10px 24px; border-radius:8px; border:2px solid #e2e8f0; background:white; color:#475569; font-weight:600; cursor:pointer; font-size:14px; transition:all 0.2s;">إلغاء</button>
            <button type="button" onclick="confirmRteImage()" style="padding:10px 24px; border-radius:8px; border:none; background:var(--color-primary); color:white; font-weight:600; cursor:pointer; font-size:14px; transition:all 0.2s; box-shadow:0 4px 12px rgba(60,203,138,0.3);">✅ إدراج</button>
        </div>
    </div>
</div>

<style>
@keyframes rtePopIn {
    from { opacity: 0; transform: scale(0.9) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const contentField = document.getElementById('content_field');
    const imagesField = document.getElementById('images_field');
    const videoField = document.getElementById('video_field');
    const audioField = document.getElementById('audio_field');

    function updateFields() {
        const selectedType = document.querySelector('input[name="type"]:checked')?.value;
        
        contentField.classList.remove('active');
        imagesField.classList.remove('active');
        videoField.classList.remove('active');
        audioField.classList.remove('active');

        if (selectedType === 'text') {
            contentField.classList.add('active');
            imagesField.classList.add('active');
        } else if (selectedType === 'video') {
            videoField.classList.add('active');
        } else if (selectedType === 'audio') {
            audioField.classList.add('active');
        } else if (selectedType === 'mixed') {
            // إظهار جميع الحقول في حالة mixed
            contentField.classList.add('active');
            imagesField.classList.add('active');
            videoField.classList.add('active');
            audioField.classList.add('active');
        }
    }

    typeRadios.forEach(radio => {
        radio.addEventListener('change', updateFields);
    });

    // معاينة الصور
    const imagesInput = document.getElementById('imagesInput');
    const imagesPreview = document.getElementById('imagesPreview');
    
    imagesInput.addEventListener('change', function(e) {
        imagesPreview.innerHTML = '';
        Array.from(e.target.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'file-preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <button type="button" class="file-preview-remove" onclick="removeImagePreview(${index})">×</button>
                    `;
                    imagesPreview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // معاينة الفيديو
    const videoFileInput = document.getElementById('videoFileInput');
    const videoPreview = document.getElementById('videoPreview');
    
    videoFileInput.addEventListener('change', function(e) {
        videoPreview.innerHTML = '';
        const file = e.target.files[0];
        if (file && file.type.startsWith('video/')) {
            const url = URL.createObjectURL(file);
            const div = document.createElement('div');
            div.className = 'file-preview-item';
            div.innerHTML = `
                <video src="${url}" controls></video>
                <button type="button" class="file-preview-remove" onclick="removeVideoPreview()">×</button>
            `;
            videoPreview.appendChild(div);
        }
    });

    // معاينة الصوت
    const audioFileInput = document.getElementById('audioFileInput');
    const audioPreview = document.getElementById('audioPreview');
    
    audioFileInput.addEventListener('change', function(e) {
        audioPreview.innerHTML = '';
        const file = e.target.files[0];
        if (file && file.type.startsWith('audio/')) {
            const url = URL.createObjectURL(file);
            const div = document.createElement('div');
            div.className = 'file-preview-item';
            div.innerHTML = `
                <audio src="${url}" controls style="width: 100%;"></audio>
                <button type="button" class="file-preview-remove" onclick="removeAudioPreview()">×</button>
            `;
            audioPreview.appendChild(div);
        }
    });

    // Rich Text Editor functions (Issue #19/#45 — تلوين الخطوط)
    window._rteSavedSelection = null;
    function _rteSaveSelection() {
        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0) {
            window._rteSavedSelection = sel.getRangeAt(0).cloneRange();
        }
    }
    function _rteRestoreSelection() {
        const editor = document.getElementById('contentEditor');
        if (!editor) return;
        editor.focus();
        if (window._rteSavedSelection) {
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(window._rteSavedSelection);
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const editor = document.getElementById('contentEditor');
        if (editor) {
            editor.addEventListener('keyup', _rteSaveSelection);
            editor.addEventListener('mouseup', _rteSaveSelection);
            editor.addEventListener('blur', _rteSaveSelection);
        }
    });
    window.rteExec = function(command, value) {
        _rteRestoreSelection();
        try {
            document.execCommand(command, false, value || null);
        } catch (e) {
            console.error('Editor command failed:', command, e);
        }
        _rteSaveSelection();
    };

    window.rteInsertLink = function() {
        document.getElementById('rteLinkText').value = '';
        document.getElementById('rteLinkUrl').value = '';
        var popup = document.getElementById('rteLinkPopup');
        popup.style.display = 'flex';
        setTimeout(function() { document.getElementById('rteLinkText').focus(); }, 100);
    };

    window.rteInsertImage = function() {
        _rteSaveSelection();
        document.getElementById('rteImageUrl').value = '';
        document.getElementById('rteImageAlt').value = '';
        document.getElementById('rteImagePreviewBox').style.display = 'none';
        const fileInput = document.getElementById('rteImageFile');
        if (fileInput) fileInput.value = '';
        const statusEl = document.getElementById('rteImageUploadStatus');
        if (statusEl) statusEl.style.display = 'none';
        var popup = document.getElementById('rteImagePopup');
        popup.style.display = 'flex';
        setTimeout(function() { document.getElementById('rteImageUrl').focus(); }, 100);
    };

    // Issue #11: رفع صورة من الجهاز للمحرر
    window.uploadRteImage = async function(input) {
        const file = input.files && input.files[0];
        if (!file) return;
        const statusEl = document.getElementById('rteImageUploadStatus');
        statusEl.style.display = 'block';
        statusEl.style.color = '#64748b';
        statusEl.textContent = 'جاري الرفع... ⏳';
        try {
            const fd = new FormData();
            fd.append('image', file);
            const res = await fetch('{{ route("editor.upload-image") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: fd,
            });
            const data = await res.json();
            if (data.url) {
                document.getElementById('rteImageUrl').value = data.url;
                const preview = document.getElementById('rteImagePreviewImg');
                preview.src = data.url;
                document.getElementById('rteImagePreviewBox').style.display = 'block';
                statusEl.style.color = '#16a34a';
                statusEl.textContent = '✓ تم الرفع — اضغط "إدراج" لإضافة الصورة للدرس';
            } else {
                throw new Error(data.message || 'فشل الرفع');
            }
        } catch (e) {
            statusEl.style.color = '#dc2626';
            statusEl.textContent = '✗ تعذّر الرفع: ' + e.message;
        }
    };

    window.closeRtePopup = function(id) {
        document.getElementById(id).style.display = 'none';
        document.getElementById('contentEditor').focus();
    };

    window.confirmRteLink = function() {
        var text = document.getElementById('rteLinkText').value.trim();
        var url = document.getElementById('rteLinkUrl').value.trim();
        if (!url) return;
        var editor = document.getElementById('contentEditor');
        editor.focus();
        if (text) {
            var link = '<a href="' + url + '" target="_blank" style="color:#3b82f6;text-decoration:underline;">' + text + '</a>';
            document.execCommand('insertHTML', false, link);
        } else {
            document.execCommand('createLink', false, url);
        }
        closeRtePopup('rteLinkPopup');
    };

    window.confirmRteImage = function() {
        var url = document.getElementById('rteImageUrl').value.trim();
        var alt = document.getElementById('rteImageAlt').value.trim();
        if (!url) return;
        var editor = document.getElementById('contentEditor');
        editor.focus();
        var img = '<img src="' + url + '" alt="' + (alt || '') + '" style="max-width:100%;height:auto;border-radius:8px;margin:8px 0;">';
        document.execCommand('insertHTML', false, img);
        closeRtePopup('rteImagePopup');
    };

    // Image URL preview
    document.getElementById('rteImageUrl').addEventListener('input', function() {
        var url = this.value.trim();
        var previewBox = document.getElementById('rteImagePreviewBox');
        var previewImg = document.getElementById('rteImagePreviewImg');
        if (url && (url.match(/\.(jpg|jpeg|png|gif|svg|webp|bmp)$/i) || url.startsWith('http'))) {
            previewImg.src = url;
            previewImg.onload = function() { previewBox.style.display = 'block'; };
            previewImg.onerror = function() { previewBox.style.display = 'none'; };
        } else {
            previewBox.style.display = 'none';
        }
    });

    // Sync rich text editor to hidden textarea on form submit
    document.getElementById('lessonForm').addEventListener('submit', function() {
        var editor = document.getElementById('contentEditor');
        var hidden = document.getElementById('contentHidden');
        if (editor && hidden) {
            hidden.value = editor.innerHTML;
        }
    });

    // Initial state
    updateFields();
});

function removeImagePreview(index) {
    const imagesInput = document.getElementById('imagesInput');
    const dt = new DataTransfer();
    Array.from(imagesInput.files).forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    imagesInput.files = dt.files;
    imagesInput.dispatchEvent(new Event('change'));
}

function removeVideoPreview() {
    document.getElementById('videoFileInput').value = '';
    document.getElementById('videoPreview').innerHTML = '';
}

function removeAudioPreview() {
    document.getElementById('audioFileInput').value = '';
    document.getElementById('audioPreview').innerHTML = '';
}

// نظام Streak
document.addEventListener('DOMContentLoaded', function() {
    const streakCheckbox = document.getElementById('streak_enabled');
    const streakFields = document.getElementById('streak_fields');
    const streakExample = document.getElementById('streak_example_text');
    const minDaysInput = document.querySelector('input[name="streak_min_days"]');
    const maxDaysInput = document.querySelector('input[name="streak_max_days"]');
    const bonusInput = document.querySelector('input[name="streak_bonus_points"]');

    if (streakCheckbox) {
        streakCheckbox.addEventListener('change', function() {
            streakFields.style.display = this.checked ? 'grid' : 'none';
        });
    }

    function updateExample() {
        if (streakExample && minDaysInput && bonusInput) {
            const minDays = minDaysInput.value || 5;
            const bonus = bonusInput.value || 50;
            streakExample.textContent = `إذا أكمل الطالب أنشطة في ${minDays} أيام أو أكثر، سيحصل على ${bonus} نقطة إضافية`;
        }
    }

    if (minDaysInput) minDaysInput.addEventListener('input', updateExample);
    if (bonusInput) bonusInput.addEventListener('input', updateExample);
});
</script>

@endsection
