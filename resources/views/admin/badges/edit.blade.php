@extends('layouts.admin')

@section('page-title', 'تعديل شارة')

@section('content')
<style>
.form-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 760px;
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
    min-height: 100px;
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

.condition-preview {
    grid-column: 1 / -1;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 14px 18px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.color-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.color-row input[type="color"] {
    width: 52px;
    height: 44px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 2px;
    cursor: pointer;
    background: white;
}

.image-upload-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.image-upload-input { display: none; }

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
    max-width: 200px;
    margin-top: 8px;
}

.image-preview-container.has-image { display: block; }

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

.image-preview-remove:hover { background: #b91c1c; transform: scale(1.1); }

.current-image { margin-bottom: 12px; }

.current-image-label {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 8px;
    display: block;
}
</style>

<div class="form-card">
    <h2 style="margin-bottom: 24px;">✏️ تعديل شارة: {{ $badge->name }}</h2>

    <form method="POST" action="{{ route('admin.badges.update', $badge) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label required">اسم الشارة</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $badge->name) }}" required>
                @error('name')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label">الوصف</label>
                <textarea name="description" class="form-textarea">{{ old('description', $badge->description) }}</textarea>
                @error('description')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">الأيقونة (Emoji)</label>
                <input type="text" name="icon" class="form-input" value="{{ old('icon', $badge->icon) }}" maxlength="10" id="iconInput">
                <small style="color: #64748b;">مثال: 🏅 🥇 ⭐ 🔥 👑</small>
                @error('icon')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">معاينة الأيقونة</label>
                <div class="icon-preview" id="iconPreview">{{ old('icon', $badge->icon) ?: '🏅' }}</div>
            </div>

            <div class="form-group">
                <label class="form-label required">نوع شرط الكسب</label>
                <select name="condition_type" class="form-select" id="conditionType" required>
                    @foreach($conditionTypes as $key => $label)
                    <option value="{{ $key }}" {{ old('condition_type', $badge->condition_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('condition_type')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label required">قيمة الشرط (الهدف)</label>
                <input type="number" name="condition_value" class="form-input" value="{{ old('condition_value', $badge->condition_value) }}" min="0" id="conditionValue" required>
                @error('condition_value')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="condition-preview">🎯 <span id="conditionPreview">—</span></div>

            <div class="form-group">
                <label class="form-label required">مكافأة العملات 🪙</label>
                <input type="number" name="coins_reward" class="form-input" value="{{ old('coins_reward', $badge->coins_reward) }}" min="0" required>
                <small style="color: #64748b;">تُمنح للطالب عند كسب الشارة</small>
                @error('coins_reward')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label required">تصنيف العرض</label>
                <select name="type" class="form-select" required>
                    <option value="achievement" {{ old('type', $badge->type) == 'achievement' ? 'selected' : '' }}>إنجاز</option>
                    <option value="streak" {{ old('type', $badge->type) == 'streak' ? 'selected' : '' }}>مواظبة</option>
                    <option value="special" {{ old('type', $badge->type) == 'special' ? 'selected' : '' }}>خاصّة</option>
                </select>
                @error('type')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">لون الشارة</label>
                <div class="color-row">
                    <input type="color" id="colorPicker" value="{{ old('color', $badge->color ?: '#f59e0b') }}">
                    <input type="text" name="color" class="form-input" style="flex:1;" value="{{ old('color', $badge->color) }}" id="colorText" placeholder="#f59e0b">
                </div>
                <small style="color: #64748b;">لون تمييز الشارة (اختياري)</small>
                @error('color')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">الترتيب</label>
                <input type="number" name="order" class="form-input" value="{{ old('order', $badge->order) }}" min="0">
                @error('order')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label required">الحالة</label>
                <select name="status" class="form-select" required>
                    <option value="active" {{ old('status', $badge->status) == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status', $badge->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
                @error('status')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label">صورة الشارة (بديل الأيقونة)</label>
                <div class="image-upload-wrapper">
                    @if($badge->image)
                    <div class="current-image">
                        <span class="current-image-label">الصورة الحالية:</span>
                        <img src="{{ asset('storage/' . $badge->image) }}" alt="صورة الشارة" class="image-preview" style="max-width: 200px;">
                    </div>
                    @endif
                    <input type="file" name="image" id="imageInput" class="image-upload-input" accept="image/*">
                    <label for="imageInput" class="image-upload-label">
                        <span>{{ $badge->image ? '🔄 تغيير الصورة' : '📷 اختر صورة' }}</span>
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
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 تحديث الشارة</button>
            <a href="{{ route('admin.badges.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

<script>
// معاينة الأيقونة
document.getElementById('iconInput').addEventListener('input', function(e) {
    document.getElementById('iconPreview').textContent = e.target.value || '🏅';
});

// معاينة نصّ الشرط «متى تظهر»
const conditionTemplates = {
    activities_completed: n => `أكمل ${n} نشاطاً`,
    level: n => `بلوغ المستوى ${n}`,
    streak: n => `سلسلة ${n} يوماً`,
    points: n => `اجمع ${n} نقطة`,
    lessons_completed: n => `أكمل ${n} درساً`,
    values_mastered: n => `أتقِن ${n} قيمة`,
};

const conditionType = document.getElementById('conditionType');
const conditionValue = document.getElementById('conditionValue');
const conditionPreview = document.getElementById('conditionPreview');

function updateConditionPreview() {
    const fn = conditionTemplates[conditionType.value];
    const n = conditionValue.value || 0;
    conditionPreview.textContent = fn ? fn(n) : '—';
}
conditionType.addEventListener('change', updateConditionPreview);
conditionValue.addEventListener('input', updateConditionPreview);
updateConditionPreview();

// مزامنة لون الشارة
const colorPicker = document.getElementById('colorPicker');
const colorText = document.getElementById('colorText');
colorPicker.addEventListener('input', () => { colorText.value = colorPicker.value; });
colorText.addEventListener('input', () => {
    if (/^#[0-9a-fA-F]{6}$/.test(colorText.value)) colorPicker.value = colorText.value;
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
