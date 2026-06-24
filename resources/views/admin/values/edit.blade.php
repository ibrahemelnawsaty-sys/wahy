@extends('layouts.admin')

@section('page-title', 'تعديل قيمة')

@section('content')
<style>
.form-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 700px;
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

.icon-preview {
    font-size: 48px;
    text-align: center;
    padding: 20px;
    background: #f8fafc;
    border-radius: 8px;
}

.image-upload-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.image-upload-input {
    display: none;
}

.image-upload-label {
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
}

.image-upload-label:hover {
    border-color: var(--color-primary);
    background: #f1f5f9;
    color: var(--color-primary);
}

.image-preview-container {
    position: relative;
    width: 100%;
    max-width: 300px;
    margin-top: 8px;
}

.image-preview-container.has-image {
    display: block;
}

.image-preview {
    width: 100%;
    height: auto;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    object-fit: cover;
    max-height: 200px;
}

.image-preview-remove {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    transition: all 0.2s;
}

.image-preview-remove:hover {
    background: #b91c1c;
    transform: scale(1.1);
}

.current-image {
    margin-bottom: 12px;
}

.current-image-label {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 8px;
    display: block;
}
</style>

<div class="form-card">
    <h2 style="margin-bottom: 24px;">✏️ تعديل قيمة: {{ $value->name }}</h2>

    <form method="POST" action="{{ route('admin.values.update', $value) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label required">اسم القيمة</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $value->name) }}" required>
                @error('name')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label">الوصف</label>
                <textarea name="description" class="form-textarea">{{ old('description', $value->description) }}</textarea>
                @error('description')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">الأيقونة (Emoji)</label>
                <input type="text" name="icon" class="form-input" value="{{ old('icon', $value->icon) }}" maxlength="10" id="iconInput">
                <small style="color: #64748b;">مثال: 💎 🌟 ⭐ 🎯</small>
                @error('icon')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">معاينة الأيقونة</label>
                <div class="icon-preview" id="iconPreview">{{ old('icon', $value->icon) }}</div>
            </div>

            <div class="form-group full-width">
                <label class="form-label">صورة القيمة</label>
                <div class="image-upload-wrapper">
                    @if($value->image)
                    <div class="current-image">
                        <span class="current-image-label">الصورة الحالية:</span>
                        <img src="{{ asset('storage/app/public/data/' . $value->image) }}" alt="صورة القيمة" class="image-preview" style="max-width: 300px;">
                    </div>
                    @endif
                    <input type="file" name="image" id="imageInput" class="image-upload-input" accept="image/*">
                    <label for="imageInput" class="image-upload-label">
                        <span>{{ $value->image ? '🔄 تغيير الصورة' : '📷 اختر صورة' }}</span>
                    </label>
                    <div class="image-preview-container" id="imagePreviewContainer">
                        <img id="imagePreview" class="image-preview" src="" alt="معاينة الصورة الجديدة">
                        <button type="button" class="image-preview-remove" id="removeImageBtn" title="إزالة الصورة">×</button>
                    </div>
                    <small style="color: #64748b;">الصيغ المدعومة: JPEG, PNG, JPG, GIF, SVG, WebP (حد أقصى 5MB)</small>
                    @error('image')
                        <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">الترتيب</label>
                <input type="number" name="order" class="form-input" value="{{ old('order', $value->order) }}" min="0">
                @error('order')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label required">الحالة</label>
                <select name="status" class="form-select" required>
                    <option value="active" {{ old('status', $value->status) == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status', $value->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
                @error('status')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 تحديث القيمة</button>
            <a href="{{ route('admin.values.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

<script>
document.getElementById('iconInput').addEventListener('input', function(e) {
    document.getElementById('iconPreview').textContent = e.target.value || '💎';
});

// معاينة الصورة
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');
const imagePreviewContainer = document.getElementById('imagePreviewContainer');
const removeImageBtn = document.getElementById('removeImageBtn');

imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreviewContainer.classList.add('has-image');
        };
        reader.readAsDataURL(file);
    }
});

removeImageBtn.addEventListener('click', function() {
    imageInput.value = '';
    imagePreview.src = '';
    imagePreviewContainer.classList.remove('has-image');
});
</script>

@endsection
