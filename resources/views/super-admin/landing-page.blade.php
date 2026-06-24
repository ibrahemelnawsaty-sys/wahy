@extends('layouts.admin')

@section('title', 'تخصيص الصفحة الرئيسية')
@section('page-title', '🏠 تخصيص الصفحة الرئيسية')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.css" rel="stylesheet">
<style>
.tabs-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.tabs-header {
    display: flex;
    border-bottom: 2px solid #e2e8f0;
    background: #f8fafc;
}

.tab-btn {
    flex: 1;
    padding: 16px 24px;
    border: none;
    background: transparent;
    font-weight: 600;
    font-size: 15px;
    color: #64748b;
    cursor: pointer;
    transition: all 0.3s;
    border-bottom: 3px solid transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.tab-btn:hover {
    background: white;
    color: #667eea;
}

.tab-btn.active {
    background: white;
    color: #667eea;
    border-bottom-color: #667eea;
}

.tab-content {
    display: none;
    padding: 32px;
}

.tab-content.active {
    display: block;
}

/* Page Builder Styles */
.page-builder-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 24px;
    min-height: 600px;
}

.blocks-panel {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    max-height: 80vh;
    overflow-y: auto;
}

.blocks-panel h3 {
    margin: 0 0 20px 0;
    color: #1e293b;
    font-size: 18px;
}

.block-type-item {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    cursor: move;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 12px;
}

.block-type-item:hover {
    border-color: #667eea;
    transform: translateX(-4px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.block-type-icon {
    font-size: 24px;
}

.block-type-info h4 {
    margin: 0 0 4px 0;
    color: #1e293b;
    font-size: 14px;
}

.block-type-info p {
    margin: 0;
    color: #64748b;
    font-size: 12px;
}

.page-preview {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    min-height: 600px;
    position: relative;
}

.page-preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e2e8f0;
}

.page-preview-header h3 {
    margin: 0;
    color: #1e293b;
}

.preview-actions {
    display: flex;
    gap: 12px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: #e2e8f0;
    color: #475569;
}

.btn-secondary:hover {
    background: #cbd5e1;
}

.blocks-list {
    min-height: 400px;
}

.page-block {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 16px;
    position: relative;
    cursor: move;
    transition: all 0.2s;
}

.page-block:hover {
    border-color: #667eea;
    background: #f1f5f9;
}

.page-block.dragging {
    opacity: 0.5;
    border-color: #667eea;
}

.block-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}

.block-title {
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.block-actions {
    display: flex;
    gap: 8px;
}

.block-action-btn {
    background: transparent;
    border: none;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.block-action-btn:hover {
    background: #e2e8f0;
}

.block-content {
    color: #64748b;
    font-size: 13px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;
}

.empty-state-icon {
    font-size: 64px;
    margin-bottom: 16px;
}

.empty-state-text {
    font-size: 16px;
    margin-bottom: 8px;
}

.empty-state-hint {
    font-size: 14px;
    color: #cbd5e1;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e2e8f0;
}

.modal-header h3 {
    margin: 0;
    color: #1e293b;
}

.modal-close {
    background: transparent;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #64748b;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-input, .form-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
    font-family: inherit;
}

.form-input:focus, .form-textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
}

.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    display: none;
}

.alert.success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.alert.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
}

.alert.active {
    display: block;
}

/* Settings Styles */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 24px;
}

.setting-group {
    margin-bottom: 24px;
}

.setting-label {
    display: block;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
    font-size: 14px;
}

.setting-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.setting-input:focus {
    outline: none;
    border-color: #667eea;
}

.color-picker-group {
    display: flex;
    gap: 12px;
    align-items: center;
}

.color-preview {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    cursor: pointer;
}
</style>
@endpush

@section('content')
<div class="tabs-container">
    <!-- Tabs Header -->
    <div class="tabs-header">
        <button class="tab-btn active" onclick="switchTab('theme')" id="tab-theme">
            🎨 إعدادات الثيم
        </button>
        <button class="tab-btn" onclick="switchTab('content')" id="tab-content">
            📝 محرر الصفحة
        </button>
    </div>

    <!-- Alert Messages -->
    <div id="alertSuccess" class="alert success"></div>
    <div id="alertError" class="alert error"></div>

    <!-- Tab 1: Theme Settings -->
    <div class="tab-content active" id="content-theme">
        <form id="themeForm">
            @csrf
            <div class="settings-grid">
                <div class="setting-group">
                    <label class="setting-label">اسم الموقع</label>
                    <input type="text" name="site_name" class="setting-input" value="{{ $themeSettings['site_name'] }}" placeholder="نظام القيم">
                </div>

                <div class="setting-group">
                    <label class="setting-label">شعار الموقع</label>
                    <input type="text" name="site_tagline" class="setting-input" value="{{ $themeSettings['site_tagline'] }}" placeholder="منصة تعليمية لبناء القيم">
                </div>
            </div>

            <div class="settings-grid">
                <div class="setting-group">
                    <label class="setting-label">اللون الأساسي</label>
                    <div class="color-picker-group">
                        <input type="color" name="primary_color" id="primary_color" class="color-preview" value="{{ $themeSettings['primary_color'] }}">
                        <input type="text" class="setting-input" value="{{ $themeSettings['primary_color'] }}" readonly style="flex: 1;">
                    </div>
                </div>

                <div class="setting-group">
                    <label class="setting-label">اللون الثانوي</label>
                    <div class="color-picker-group">
                        <input type="color" name="secondary_color" id="secondary_color" class="color-preview" value="{{ $themeSettings['secondary_color'] }}">
                        <input type="text" class="setting-input" value="{{ $themeSettings['secondary_color'] }}" readonly style="flex: 1;">
                    </div>
                </div>
            </div>

            <div class="setting-group">
                <label class="setting-label">نوع الخط</label>
                <select name="font_family" class="setting-input" id="font_family">
                    <option value="IBM Plex Sans Arabic" {{ $themeSettings['font_family'] == 'IBM Plex Sans Arabic' ? 'selected' : '' }}>IBM Plex Sans Arabic</option>
                    <option value="Cairo" {{ $themeSettings['font_family'] == 'Cairo' ? 'selected' : '' }}>Cairo</option>
                    <option value="Tajawal" {{ $themeSettings['font_family'] == 'Tajawal' ? 'selected' : '' }}>Tajawal</option>
                    <option value="Almarai" {{ $themeSettings['font_family'] == 'Almarai' ? 'selected' : '' }}>Almarai</option>
                    <option value="Noto Sans Arabic" {{ $themeSettings['font_family'] == 'Noto Sans Arabic' ? 'selected' : '' }}>Noto Sans Arabic</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 32px;">
                <button type="submit" class="btn btn-primary">
                    💾 حفظ الإعدادات
                </button>
            </div>
        </form>
    </div>

    <!-- Tab 2: Page Builder -->
    <div class="tab-content" id="content-content">
        <!-- Info Box -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 20px; margin-bottom: 24px; color: white;">
            <h4 style="margin: 0 0 12px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                💡 كيف تعدل الصفحة الرئيسية الحالية؟
            </h4>
            <div style="font-size: 14px; line-height: 1.8; opacity: 0.95;">
                <p style="margin: 0 0 8px 0;">
                    <strong>الخطوة 1:</strong> اضغط على زر "📥 استيراد من الصفحة الحالية" لتحميل محتوى الصفحة الحالية (landing.blade.php) إلى المحرر.
                </p>
                <p style="margin: 0 0 8px 0;">
                    <strong>الخطوة 2:</strong> بعد الاستيراد، يمكنك تعديل أي عنصر بالنقر على زر "✏️ تعديل" أو حذفه أو إعادة ترتيبه.
                </p>
                <p style="margin: 0 0 8px 0;">
                    <strong>الخطوة 3:</strong> اضغط "💾 حفظ" لحفظ التغييرات. بعد الحفظ، يمكنك معاينة الصفحة أو إضافة عناصر جديدة.
                </p>
                <p style="margin: 0; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.2);">
                    <strong>⚠️ ملاحظة:</strong> الصفحة الحالية (/) تستخدم ملف landing.blade.php. بعد التعديل والحفظ، يمكنك تغيير Route الرئيسية لاستخدام Page Builder بدلاً من الملف الثابت.
                </p>
            </div>
        </div>
        
        <div class="page-builder-container">
            <!-- Blocks Panel -->
            <div class="blocks-panel">
                <h3>📦 أنواع العناصر</h3>
                <div id="blockTypesList">
                    <div class="block-type-item" data-type="hero" onclick="addBlock('hero')">
                        <div class="block-type-icon">🎯</div>
                        <div class="block-type-info">
                            <h4>Hero Section</h4>
                            <p>قسم رئيسي مع عنوان ووصف</p>
                        </div>
                    </div>
                    <div class="block-type-item" data-type="heading" onclick="addBlock('heading')">
                        <div class="block-type-icon">📝</div>
                        <div class="block-type-info">
                            <h4>عنوان</h4>
                            <p>عنوان رئيسي أو فرعي</p>
                        </div>
                    </div>
                    <div class="block-type-item" data-type="paragraph" onclick="addBlock('paragraph')">
                        <div class="block-type-icon">📄</div>
                        <div class="block-type-info">
                            <h4>فقرة نصية</h4>
                            <p>نص عادي أو وصف</p>
                        </div>
                    </div>
                    <div class="block-type-item" data-type="button" onclick="addBlock('button')">
                        <div class="block-type-icon">🔘</div>
                        <div class="block-type-info">
                            <h4>زر</h4>
                            <p>زر قابل للنقر</p>
                        </div>
                    </div>
                    <div class="block-type-item" data-type="stats" onclick="addBlock('stats')">
                        <div class="block-type-icon">📊</div>
                        <div class="block-type-info">
                            <h4>إحصائيات</h4>
                            <p>عرض الأرقام والإحصائيات</p>
                        </div>
                    </div>
                    <div class="block-type-item" data-type="features" onclick="addBlock('features')">
                        <div class="block-type-icon">⭐</div>
                        <div class="block-type-info">
                            <h4>المميزات</h4>
                            <p>قائمة المميزات</p>
                        </div>
                    </div>
                    <div class="block-type-item" data-type="cta" onclick="addBlock('cta')">
                        <div class="block-type-icon">📢</div>
                        <div class="block-type-info">
                            <h4>دعوة للعمل</h4>
                            <p>قسم دعوة للانضمام</p>
                        </div>
                    </div>
                    <div class="block-type-item" data-type="image" onclick="addBlock('image')">
                        <div class="block-type-icon">🖼️</div>
                        <div class="block-type-info">
                            <h4>صورة</h4>
                            <p>إضافة صورة</p>
                        </div>
                    </div>
                    <div class="block-type-item" data-type="spacer" onclick="addBlock('spacer')">
                        <div class="block-type-icon">↕️</div>
                        <div class="block-type-info">
                            <h4>مسافة</h4>
                            <p>إضافة مسافة بين العناصر</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Preview -->
            <div class="page-preview">
                <div class="page-preview-header">
                    <h3>معاينة الصفحة</h3>
                    <div class="preview-actions">
                        <button class="btn btn-secondary" onclick="importCurrentLanding()" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                            📥 استيراد من الصفحة الحالية
                        </button>
                        <button class="btn btn-secondary" onclick="previewPage()">👁️ معاينة</button>
                        <button class="btn btn-primary" onclick="savePage()">💾 حفظ</button>
                    </div>
                </div>
                <div class="blocks-list" id="blocksList">
                    @if(empty($landingPage->json_data))
                        <div class="empty-state">
                            <div class="empty-state-icon">📄</div>
                            <div class="empty-state-text">لا توجد عناصر في الصفحة</div>
                            <div class="empty-state-hint">اسحب عنصراً من القائمة الجانبية لإضافته</div>
                        </div>
                    @else
                        @foreach($landingPage->json_data as $index => $block)
                            @php
                                $blockId = $block['id'] ?? 'block_' . $index . '_' . time();
                            @endphp
                            <div class="page-block" data-id="{{ $blockId }}">
                                <div class="block-header">
                                    <div class="block-title">
                                        <span>{{ getBlockIcon($block['type'] ?? 'unknown') }}</span>
                                        <span>{{ getBlockTitle($block['type'] ?? 'unknown') }}</span>
                                    </div>
                                    <div class="block-actions">
                                        <button class="block-action-btn" onclick="editBlock('{{ $blockId }}')" title="تعديل">✏️</button>
                                        <button class="block-action-btn" onclick="deleteBlock('{{ $blockId }}')" title="حذف">🗑️</button>
                                    </div>
                                </div>
                                <div class="block-content">
                                    {{ getBlockPreview($block) }}
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Block Modal -->
<div class="modal" id="editBlockModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">تعديل العنصر</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <form id="editBlockForm">
            <div id="modalBody">
                <!-- Dynamic form fields will be inserted here -->
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    </div>
</div>

@php
function getBlockIcon($type) {
    $icons = [
        'hero' => '🎯',
        'heading' => '📝',
        'paragraph' => '📄',
        'button' => '🔘',
        'stats' => '📊',
        'features' => '⭐',
        'cta' => '📢',
        'image' => '🖼️',
        'spacer' => '↕️',
    ];
    return $icons[$type] ?? '📦';
}

function getBlockTitle($type) {
    $titles = [
        'hero' => 'Hero Section',
        'heading' => 'عنوان',
        'paragraph' => 'فقرة نصية',
        'button' => 'زر',
        'stats' => 'إحصائيات',
        'features' => 'المميزات',
        'cta' => 'دعوة للعمل',
        'image' => 'صورة',
        'spacer' => 'مسافة',
    ];
    return $titles[$type] ?? 'عنصر';
}

function getBlockPreview($block) {
    $type = $block['type'] ?? 'unknown';
    $content = $block['content'] ?? [];
    
    switch($type) {
        case 'hero':
            return ($content['title'] ?? 'عنوان') . ' - ' . ($content['subtitle'] ?? 'وصف');
        case 'heading':
            return $content['text'] ?? 'عنوان';
        case 'paragraph':
            return mb_substr($content['text'] ?? 'نص', 0, 100) . '...';
        case 'button':
            return 'زر: ' . ($content['text'] ?? 'نص الزر');
        case 'stats':
            return 'إحصائيات: ' . count($content['items'] ?? []) . ' عنصر';
        case 'features':
            return 'المميزات: ' . count($content['items'] ?? []) . ' عنصر';
        case 'cta':
            return ($content['title'] ?? 'دعوة للعمل');
        case 'image':
            return 'صورة: ' . ($content['url'] ?? 'لا توجد صورة');
        case 'spacer':
            return 'مسافة: ' . ($content['height'] ?? '40') . 'px';
        default:
            return 'عنصر غير معروف';
    }
}
@endphp

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
// Load blocks and ensure all have IDs
let blocks = @json($landingPage->json_data ?? []);

// Ensure all blocks have IDs
blocks = blocks.map((block, index) => {
    if (!block.id) {
        block.id = 'block_' + Date.now() + '_' + index;
    }
    return block;
});

let currentEditingBlock = null;

// Switch Tabs
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    document.getElementById('content-' + tab).classList.add('active');
}

// Color Picker Updates
document.getElementById('primary_color')?.addEventListener('input', function() {
    this.nextElementSibling.value = this.value;
});

document.getElementById('secondary_color')?.addEventListener('input', function() {
    this.nextElementSibling.value = this.value;
});

// Save Theme Settings
document.getElementById('themeForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('{{ route("admin.landing-page.theme") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess(result.message);
        } else {
            showError(result.message || 'حدث خطأ أثناء الحفظ');
        }
    } catch (error) {
        showError('حدث خطأ في الاتصال');
        console.error(error);
    }
});

// Add Block
function addBlock(type) {
    const defaultContent = getDefaultContent(type);
    
    const newBlock = {
        id: 'block_' + Date.now(),
        type: type,
        content: defaultContent
    };
    
    blocks.push(newBlock);
    renderBlocks();
    editBlock(newBlock.id);
}

function getDefaultContent(type) {
    const defaults = {
        hero: { title: 'عنوان رئيسي', subtitle: 'وصف قصير', buttonText: 'ابدأ الآن', buttonLink: '#' },
        heading: { text: 'عنوان جديد', level: 'h2' },
        paragraph: { text: 'نص الفقرة هنا...' },
        button: { text: 'زر', link: '#', style: 'primary' },
        stats: { items: [{ label: 'إحصائية', value: '0' }] },
        features: { items: [{ title: 'ميزة', description: 'وصف' }] },
        cta: { title: 'دعوة للعمل', description: 'وصف', buttonText: 'انضم الآن', buttonLink: '#' },
        image: { url: '', alt: 'صورة' },
        spacer: { height: '40' }
    };
    return defaults[type] || {};
}

// Render Blocks
function renderBlocks() {
    const container = document.getElementById('blocksList');
    
    if (blocks.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📄</div>
                <div class="empty-state-text">لا توجد عناصر في الصفحة</div>
                <div class="empty-state-hint">اسحب عنصراً من القائمة الجانبية لإضافته</div>
            </div>
        `;
        return;
    }
    
    // Ensure all blocks have IDs before rendering
    blocks = blocks.map((block, index) => {
        if (!block.id) {
            block.id = 'block_' + Date.now() + '_' + index;
        }
        return block;
    });
    
    container.innerHTML = blocks.map(block => `
        <div class="page-block" data-id="${block.id}">
            <div class="block-header">
                <div class="block-title">
                    <span>${getBlockIcon(block.type)}</span>
                    <span>${getBlockTitle(block.type)}</span>
                </div>
                <div class="block-actions">
                    <button class="block-action-btn" onclick="editBlock('${block.id}')" title="تعديل">✏️</button>
                    <button class="block-action-btn" onclick="deleteBlock('${block.id}')" title="حذف">🗑️</button>
                </div>
            </div>
            <div class="block-content">
                ${getBlockPreview(block)}
            </div>
        </div>
    `).join('');
    
    // Initialize Sortable
    new Sortable(container, {
        animation: 150,
        handle: '.page-block',
        onEnd: function(evt) {
            const oldIndex = evt.oldIndex;
            const newIndex = evt.newIndex;
            const movedBlock = blocks.splice(oldIndex, 1)[0];
            blocks.splice(newIndex, 0, movedBlock);
            savePage();
        }
    });
}

function getBlockIcon(type) {
    const icons = {
        hero: '🎯', heading: '📝', paragraph: '📄', button: '🔘',
        stats: '📊', features: '⭐', cta: '📢', image: '🖼️', spacer: '↕️'
    };
    return icons[type] || '📦';
}

function getBlockTitle(type) {
    const titles = {
        hero: 'Hero Section', heading: 'عنوان', paragraph: 'فقرة نصية',
        button: 'زر', stats: 'إحصائيات', features: 'المميزات',
        cta: 'دعوة للعمل', image: 'صورة', spacer: 'مسافة'
    };
    return titles[type] || 'عنصر';
}

function getBlockPreview(block) {
    const content = block.content || {};
    switch(block.type) {
        case 'hero':
            return (content.title || 'عنوان') + ' - ' + (content.subtitle || 'وصف');
        case 'heading':
            return content.text || 'عنوان';
        case 'paragraph':
            return (content.text || 'نص').substring(0, 100) + '...';
        case 'button':
            return 'زر: ' + (content.text || 'نص الزر');
        case 'stats':
            return 'إحصائيات: ' + (content.items?.length || 0) + ' عنصر';
        case 'features':
            return 'المميزات: ' + (content.items?.length || 0) + ' عنصر';
        case 'cta':
            return content.title || 'دعوة للعمل';
        case 'image':
            return 'صورة: ' + (content.url || 'لا توجد صورة');
        case 'spacer':
            return 'مسافة: ' + (content.height || '40') + 'px';
        default:
            return 'عنصر غير معروف';
    }
}

// Edit Block
function editBlock(id) {
    const block = blocks.find(b => b.id === id);
    if (!block) return;
    
    currentEditingBlock = block;
    const modal = document.getElementById('editBlockModal');
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalTitle');
    
    modalTitle.textContent = `تعديل: ${getBlockTitle(block.type)}`;
    modalBody.innerHTML = getBlockForm(block);
    modal.classList.add('active');
}

function getBlockForm(block) {
    const content = block.content || {};
    const type = block.type;
    
    switch(type) {
        case 'hero':
            return `
                <div class="form-group">
                    <label class="form-label">العنوان</label>
                    <input type="text" name="title" class="form-input" value="${content.title || ''}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">الوصف</label>
                    <textarea name="subtitle" class="form-textarea" required>${content.subtitle || ''}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">نص الزر</label>
                    <input type="text" name="buttonText" class="form-input" value="${content.buttonText || ''}">
                </div>
                <div class="form-group">
                    <label class="form-label">رابط الزر</label>
                    <input type="text" name="buttonLink" class="form-input" value="${content.buttonLink || '#'}">
                </div>
            `;
        case 'heading':
            return `
                <div class="form-group">
                    <label class="form-label">النص</label>
                    <input type="text" name="text" class="form-input" value="${content.text || ''}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">المستوى</label>
                    <select name="level" class="form-input">
                        <option value="h1" ${content.level === 'h1' ? 'selected' : ''}>H1</option>
                        <option value="h2" ${content.level === 'h2' ? 'selected' : ''}>H2</option>
                        <option value="h3" ${content.level === 'h3' ? 'selected' : ''}>H3</option>
                    </select>
                </div>
            `;
        case 'paragraph':
            return `
                <div class="form-group">
                    <label class="form-label">النص</label>
                    <textarea name="text" class="form-textarea" required>${content.text || ''}</textarea>
                </div>
            `;
        case 'button':
            return `
                <div class="form-group">
                    <label class="form-label">النص</label>
                    <input type="text" name="text" class="form-input" value="${content.text || ''}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">الرابط</label>
                    <input type="text" name="link" class="form-input" value="${content.link || '#'}">
                </div>
                <div class="form-group">
                    <label class="form-label">النمط</label>
                    <select name="style" class="form-input">
                        <option value="primary" ${content.style === 'primary' ? 'selected' : ''}>أساسي</option>
                        <option value="secondary" ${content.style === 'secondary' ? 'selected' : ''}>ثانوي</option>
                    </select>
                </div>
            `;
        case 'stats':
            const statsItems = content.items || [{ label: '', value: '' }];
            return `
                <div id="statsItems">
                    ${statsItems.map((item, index) => `
                        <div class="form-group" style="border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; margin-bottom: 12px;">
                            <label class="form-label">الإحصائية ${index + 1}</label>
                            <input type="text" name="items[${index}][label]" class="form-input" value="${item.label || ''}" placeholder="التسمية" style="margin-bottom: 8px;">
                            <input type="text" name="items[${index}][value]" class="form-input" value="${item.value || ''}" placeholder="القيمة">
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-secondary" onclick="addStatsItem()">+ إضافة إحصائية</button>
            `;
        case 'features':
            const featuresItems = content.items || [{ title: '', description: '' }];
            return `
                <div id="featuresItems">
                    ${featuresItems.map((item, index) => `
                        <div class="form-group" style="border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; margin-bottom: 12px;">
                            <label class="form-label">الميزة ${index + 1}</label>
                            <input type="text" name="items[${index}][title]" class="form-input" value="${item.title || ''}" placeholder="العنوان" style="margin-bottom: 8px;">
                            <textarea name="items[${index}][description]" class="form-textarea" placeholder="الوصف">${item.description || ''}</textarea>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-secondary" onclick="addFeatureItem()">+ إضافة ميزة</button>
            `;
        case 'cta':
            return `
                <div class="form-group">
                    <label class="form-label">العنوان</label>
                    <input type="text" name="title" class="form-input" value="${content.title || ''}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-textarea">${content.description || ''}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">نص الزر</label>
                    <input type="text" name="buttonText" class="form-input" value="${content.buttonText || ''}">
                </div>
                <div class="form-group">
                    <label class="form-label">رابط الزر</label>
                    <input type="text" name="buttonLink" class="form-input" value="${content.buttonLink || '#'}">
                </div>
            `;
        case 'image':
            return `
                <div class="form-group">
                    <label class="form-label">رابط الصورة</label>
                    <input type="text" name="url" class="form-input" value="${content.url || ''}" placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group">
                    <label class="form-label">النص البديل</label>
                    <input type="text" name="alt" class="form-input" value="${content.alt || ''}">
                </div>
            `;
        case 'spacer':
            return `
                <div class="form-group">
                    <label class="form-label">الارتفاع (px)</label>
                    <input type="number" name="height" class="form-input" value="${content.height || '40'}" min="0">
                </div>
            `;
        default:
            return '<p>نموذج غير متوفر</p>';
    }
}

// Save Block
document.getElementById('editBlockForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentEditingBlock) return;
    
    const formData = new FormData(this);
    const content = {};
    
    // Handle different block types
    if (currentEditingBlock.type === 'stats' || currentEditingBlock.type === 'features') {
        const items = [];
        formData.forEach((value, key) => {
            const match = key.match(/items\[(\d+)\]\[(\w+)\]/);
            if (match) {
                const index = parseInt(match[1]);
                const field = match[2];
                if (!items[index]) items[index] = {};
                items[index][field] = value;
            }
        });
        content.items = items.filter(item => item.label || item.title);
    } else {
        formData.forEach((value, key) => {
            content[key] = value;
        });
    }
    
    currentEditingBlock.content = content;
    renderBlocks();
    closeModal();
    showSuccess('تم تحديث العنصر بنجاح!');
});

// Delete Block
function deleteBlock(id) {
    if (!confirm('هل أنت متأكد من حذف هذا العنصر؟')) return;
    
    blocks = blocks.filter(b => b.id !== id);
    renderBlocks();
    savePageAuto(); // حفظ تلقائي
    showSuccess('تم حذف العنصر بنجاح!');
}

// Auto Save (no alert)
async function savePageAuto() {
    try {
        await fetch('{{ route("admin.landing-page.content") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                json_data: blocks
            })
        });
    } catch (error) {
        console.error('حفظ تلقائي فشل:', error);
    }
}

// Save Page
async function savePage() {
    try {
        const response = await fetch('{{ route("admin.landing-page.content") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                json_data: blocks
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess('تم حفظ الصفحة بنجاح!');
        } else {
            showError(result.message || 'حدث خطأ أثناء الحفظ');
        }
    } catch (error) {
        showError('حدث خطأ في الاتصال');
        console.error(error);
    }
}

// Preview Page
function previewPage() {
    window.open('/', '_blank');
}

// Import Current Landing Page
async function importCurrentLanding() {
    if (!confirm('هل تريد استيراد المحتوى من الصفحة الحالية (landing.blade.php)؟\n\nسيتم استبدال جميع العناصر الحالية بالمحتوى المستورد.')) {
        return;
    }
    
    try {
        const response = await fetch('{{ route("admin.landing-page.import-current") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            blocks = result.blocks;
            renderBlocks();
            showSuccess('تم استيراد المحتوى من الصفحة الحالية بنجاح! يمكنك الآن تعديل العناصر.');
        } else {
            showError(result.message || 'حدث خطأ أثناء الاستيراد');
        }
    } catch (error) {
        showError('حدث خطأ في الاتصال');
        console.error(error);
    }
}

// Close Modal
function closeModal() {
    document.getElementById('editBlockModal').classList.remove('active');
    currentEditingBlock = null;
}

// Alert Functions
function showSuccess(message) {
    const alert = document.getElementById('alertSuccess');
    alert.textContent = message;
    alert.classList.add('active', 'success');
    alert.classList.remove('error');
    setTimeout(() => alert.classList.remove('active'), 5000);
}

function showError(message) {
    const alert = document.getElementById('alertError');
    alert.textContent = message;
    alert.classList.add('active', 'error');
    alert.classList.remove('success');
    setTimeout(() => alert.classList.remove('active'), 5000);
}

// Add Stats Item
function addStatsItem() {
    const container = document.getElementById('statsItems');
    const index = container.children.length;
    const newItem = document.createElement('div');
    newItem.className = 'form-group';
    newItem.style.cssText = 'border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; margin-bottom: 12px;';
    newItem.innerHTML = `
        <label class="form-label">الإحصائية ${index + 1}</label>
        <input type="text" name="items[${index}][label]" class="form-input" placeholder="التسمية" style="margin-bottom: 8px;">
        <input type="text" name="items[${index}][value]" class="form-input" placeholder="القيمة">
    `;
    container.appendChild(newItem);
}

// Add Feature Item
function addFeatureItem() {
    const container = document.getElementById('featuresItems');
    const index = container.children.length;
    const newItem = document.createElement('div');
    newItem.className = 'form-group';
    newItem.style.cssText = 'border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; margin-bottom: 12px;';
    newItem.innerHTML = `
        <label class="form-label">الميزة ${index + 1}</label>
        <input type="text" name="items[${index}][title]" class="form-input" placeholder="العنوان" style="margin-bottom: 8px;">
        <textarea name="items[${index}][description]" class="form-textarea" placeholder="الوصف"></textarea>
    `;
    container.appendChild(newItem);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    renderBlocks();
});
</script>
@endsection
