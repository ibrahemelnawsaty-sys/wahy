@extends('layouts.admin')

@section('title', 'إنشاء صفحة جديدة')
@section('page-title', 'إنشاء صفحة جديدة')

@section('content')
<div class="admin-card">
    <form action="{{ route('admin.pages.store') }}" method="POST">
        @csrf
        
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label for="page_name">اسم الصفحة</label>
                <input type="text" id="page_name" name="page_name" class="admin-input" required>
            </div>

            <div class="admin-form-group">
                <label for="slug">الرابط (Slug)</label>
                <input type="text" id="slug" name="slug" class="admin-input" required>
            </div>
        </div>

        <div class="admin-form-group">
            <label for="meta_title">عنوان الصفحة (Meta Title)</label>
            <input type="text" id="meta_title" name="meta_title" class="admin-input">
        </div>

        <div class="admin-form-group">
            <label for="meta_description">وصف الصفحة (Meta Description)</label>
            <textarea id="meta_description" name="meta_description" class="admin-input" rows="3"></textarea>
        </div>

        <div class="admin-form-group">
            <label>
                <input type="checkbox" name="is_active" checked>
                نشط
            </label>
        </div>

        <hr style="margin: 32px 0;">

        <h3>محتوى الصفحة</h3>
        <p style="color: #64748b; margin-bottom: 24px;">أضف أقسام الصفحة باستخدام JSON</p>

        <div class="admin-form-group">
            <label for="json_data">JSON Data</label>
            <textarea id="json_data" name="json_data" class="admin-input" rows="15" required style="font-family: monospace; direction: ltr;">[
  {
    "type": "hero",
    "content": {
      "title": "عنوان رئيسي",
      "subtitle": "وصف قصير",
      "buttonText": "ابدأ الآن",
      "buttonUrl": "#"
    }
  },
  {
    "type": "paragraph",
    "content": {
      "text": "نص الفقرة هنا"
    }
  }
]</textarea>
            <small style="color: #64748b;">
                الأنواع المتاحة: hero, heading, paragraph, button, image, cards, video, spacer
            </small>
        </div>

        <div class="admin-form-actions">
            <a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-secondary">إلغاء</a>
            <button type="submit" class="admin-btn admin-btn-primary">حفظ الصفحة</button>
        </div>
    </form>
</div>

<script>
// Auto-generate slug from page name
document.getElementById('page_name').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\u0600-\u06FFa-z0-9-]/g, '');
    document.getElementById('slug').value = slug;
});

// Validate JSON before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const jsonData = document.getElementById('json_data').value;
    try {
        const parsed = JSON.parse(jsonData);
        if (!Array.isArray(parsed)) {
            throw new Error('JSON يجب أن يكون Array');
        }
    } catch(error) {
        e.preventDefault();
        alert('خطأ في صيغة JSON: ' + error.message);
    }
});
</script>
@endsection
