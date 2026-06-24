@extends('layouts.admin')

@section('title', 'إنشاء صفحة جديدة')
@section('page-title', 'إنشاء صفحة جديدة')

@push('styles')
<style>
.page-builder-container {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 24px;
    min-height: 600px;
}

.blocks-sidebar {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e2e8f0;
    height: fit-content;
}

.block-item {
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: move;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    border: 2px dashed transparent;
}

.block-item:hover {
    background: #e8f9f2;
    border-color: #3CCB8A;
}

.block-icon {
    font-size: 24px;
}

.block-info {
    flex: 1;
}

.block-title {
    font-weight: 600;
    color: #1e293b;
    font-size: 13px;
}

.block-desc {
    font-size: 11px;
    color: #64748b;
}

.canvas-area {
    background: white;
    border-radius: 12px;
    padding: 24px;
    border: 1px solid #e2e8f0;
    min-height: 600px;
}

.drop-zone {
    border: 2px dashed #e2e8f0;
    border-radius: 8px;
    padding: 40px 24px;
    text-align: center;
    color: #94a3b8;
    transition: all 0.3s ease;
}

.drop-zone.drag-over {
    border-color: #3CCB8A;
    background: #e8f9f2;
}

.dropped-block {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
    position: relative;
    cursor: move;
}

.dropped-block:hover {
    border-color: #3CCB8A;
}

.block-controls {
    position: absolute;
    top: 8px;
    left: 8px;
    display: flex;
    gap: 4px;
}

.block-control-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    font-size: 14px;
    transition: all 0.3s ease;
}

.block-control-btn:hover {
    transform: scale(1.1);
}

.block-content-editable {
    min-height: 40px;
    padding: 12px;
    border: 1px dashed transparent;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.block-content-editable:hover {
    border-color: #cbd5e1;
    background: white;
}
</style>
@endpush

@section('content')
    <form action="{{ route('admin.pages.store') }}" method="POST" id="pageBuilderForm">
        @csrf

        <!-- Page Settings -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">إعدادات الصفحة</h3>
            </div>
            <div class="admin-card-body" style="padding: 24px;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">اسم الصفحة *</label>
                        <input type="text" name="page_name" class="admin-form-input" required placeholder="مثال: من نحن">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">الرابط (Slug) *</label>
                        <input type="text" name="slug" class="admin-form-input" required placeholder="مثال: about-us">
                        <div class="admin-form-help">سيكون الرابط: example.com/<strong>slug</strong></div>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">عنوان SEO</label>
                        <input type="text" name="meta_title" class="admin-form-input" placeholder="عنوان الصفحة لمحركات البحث">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">حالة الصفحة</label>
                        <select name="is_active" class="admin-form-select">
                            <option value="1">نشط</option>
                            <option value="0">معطل</option>
                        </select>
                    </div>

                    <div class="admin-form-group" style="grid-column: 1 / -1;">
                        <label class="admin-form-label">وصف SEO</label>
                        <textarea name="meta_description" class="admin-form-textarea" rows="3" placeholder="وصف الصفحة لمحركات البحث"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Builder -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">محرر الصفحة</h3>
            </div>
            <div class="admin-card-body" style="padding: 24px;">
                <div class="page-builder-container">
                    <!-- Blocks Sidebar -->
                    <div class="blocks-sidebar">
                        <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 16px; color: #1e293b;">البلوكات المتاحة</h4>
                        
                        <div class="block-item" draggable="true" data-block-type="hero">
                            <div class="block-icon">🏠</div>
                            <div class="block-info">
                                <div class="block-title">قسم رئيسي</div>
                                <div class="block-desc">صورة كبيرة + عنوان</div>
                            </div>
                        </div>

                        <div class="block-item" draggable="true" data-block-type="heading">
                            <div class="block-icon">📝</div>
                            <div class="block-info">
                                <div class="block-title">عنوان</div>
                                <div class="block-desc">عنوان فرعي للقسم</div>
                            </div>
                        </div>

                        <div class="block-item" draggable="true" data-block-type="paragraph">
                            <div class="block-icon">📄</div>
                            <div class="block-info">
                                <div class="block-title">فقرة نصية</div>
                                <div class="block-desc">نص متعدد الأسطر</div>
                            </div>
                        </div>

                        <div class="block-item" draggable="true" data-block-type="button">
                            <div class="block-icon">🔘</div>
                            <div class="block-info">
                                <div class="block-title">زر</div>
                                <div class="block-desc">زر إجراء أو رابط</div>
                            </div>
                        </div>

                        <div class="block-item" draggable="true" data-block-type="image">
                            <div class="block-icon">🖼️</div>
                            <div class="block-info">
                                <div class="block-title">صورة</div>
                                <div class="block-desc">صورة مفردة</div>
                            </div>
                        </div>

                        <div class="block-item" draggable="true" data-block-type="cards">
                            <div class="block-icon">🎴</div>
                            <div class="block-info">
                                <div class="block-title">بطاقات</div>
                                <div class="block-desc">شبكة من البطاقات</div>
                            </div>
                        </div>

                        <div class="block-item" draggable="true" data-block-type="video">
                            <div class="block-icon">🎥</div>
                            <div class="block-info">
                                <div class="block-title">فيديو</div>
                                <div class="block-desc">فيديو يوتيوب أو ملف</div>
                            </div>
                        </div>

                        <div class="block-item" draggable="true" data-block-type="spacer">
                            <div class="block-icon">📏</div>
                            <div class="block-info">
                                <div class="block-title">فاصل</div>
                                <div class="block-desc">مساحة بين الأقسام</div>
                            </div>
                        </div>
                    </div>

                    <!-- Canvas -->
                    <div class="canvas-area">
                        <div id="dropZone" class="drop-zone">
                            <div style="font-size: 48px; margin-bottom: 16px;">🎨</div>
                            <h3 style="color: #1e293b; font-size: 18px; margin-bottom: 8px;">ابدأ بسحب البلوكات هنا</h3>
                            <p>اسحب أي بلوك من القائمة اليمنى وأفلته هنا لبناء الصفحة</p>
                        </div>
                        <div id="pageBlocks"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden Input for JSON Data -->
        <input type="hidden" name="json_data" id="jsonData">

        <!-- Save Button -->
        <div style="display: flex; justify-content: flex-end; gap: 16px;">
            <a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-outline">
                إلغاء
            </a>
            <button type="submit" class="admin-btn admin-btn-primary">
                💾 حفظ الصفحة
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
let pageBlocks = [];
let draggedElement = null;
let blockIdCounter = 0;

// Drag & Drop من Sidebar
document.querySelectorAll('.block-item').forEach(block => {
    block.addEventListener('dragstart', function(e) {
        const blockType = this.getAttribute('data-block-type');
        e.dataTransfer.setData('blockType', blockType);
        this.style.opacity = '0.5';
    });

    block.addEventListener('dragend', function() {
        this.style.opacity = '1';
    });
});

// Drop Zone Events
const dropZone = document.getElementById('dropZone');
const pageBlocksContainer = document.getElementById('pageBlocks');

[dropZone, pageBlocksContainer].forEach(zone => {
    zone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });

    zone.addEventListener('dragleave', function() {
        this.classList.remove('drag-over');
    });

    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        
        const blockType = e.dataTransfer.getData('blockType');
        if (blockType) {
            addBlock(blockType);
            dropZone.style.display = 'none';
        }
    });
});

// إضافة Block
function addBlock(type) {
    const blockId = ++blockIdCounter;
    const block = {
        id: blockId,
        type: type,
        content: getDefaultContent(type)
    };
    
    pageBlocks.push(block);
    renderBlocks();
}

// محتوى افتراضي للبلوكات
function getDefaultContent(type) {
    const defaults = {
        hero: {
            title: 'عنوان رئيسي',
            subtitle: 'وصف قصير هنا',
            buttonText: 'ابدأ الآن',
            buttonLink: '#'
        },
        heading: {
            text: 'عنوان هنا',
            level: 'h2'
        },
        paragraph: {
            text: 'هذه فقرة نصية. انقر للتعديل.'
        },
        button: {
            text: 'انقر هنا',
            link: '#',
            style: 'primary'
        },
        image: {
            url: 'https://via.placeholder.com/800x400',
            alt: 'صورة'
        },
        cards: {
            items: [
                { icon: '⭐', title: 'بطاقة 1', text: 'وصف البطاقة' },
                { icon: '🎯', title: 'بطاقة 2', text: 'وصف البطاقة' },
                { icon: '💎', title: 'بطاقة 3', text: 'وصف البطاقة' }
            ]
        },
        video: {
            url: 'https://www.youtube.com/embed/dQw4w9WgXcQ'
        },
        spacer: {
            height: '40px'
        }
    };
    
    return defaults[type] || {};
}

// رسم البلوكات
function renderBlocks() {
    pageBlocksContainer.innerHTML = '';
    
    pageBlocks.forEach((block, index) => {
        const blockElement = document.createElement('div');
        blockElement.className = 'dropped-block';
        blockElement.setAttribute('data-block-id', block.id);
        blockElement.innerHTML = `
            <div class="block-controls">
                <button type="button" class="block-control-btn" onclick="moveBlock(${index}, -1)" ${index === 0 ? 'disabled' : ''}>⬆️</button>
                <button type="button" class="block-control-btn" onclick="moveBlock(${index}, 1)" ${index === pageBlocks.length - 1 ? 'disabled' : ''}>⬇️</button>
                <button type="button" class="block-control-btn" onclick="editBlock(${index})">✏️</button>
                <button type="button" class="block-control-btn" onclick="deleteBlock(${index})">🗑️</button>
            </div>
            ${renderBlockContent(block)}
        `;
        
        pageBlocksContainer.appendChild(blockElement);
    });
    
    updateJsonData();
}

// رسم محتوى Block
function renderBlockContent(block) {
    switch(block.type) {
        case 'hero':
            return `
                <div style="text-align: center; padding: 40px 24px; background: linear-gradient(135deg, #3CCB8A 0%, #2fb577 100%); border-radius: 8px; color: white;">
                    <h1 style="font-size: 32px; margin-bottom: 16px;">${block.content.title}</h1>
                    <p style="font-size: 18px; margin-bottom: 24px;">${block.content.subtitle}</p>
                    <button style="background: white; color: #3CCB8A; padding: 12px 32px; border-radius: 8px; border: none; font-weight: 600;">
                        ${block.content.buttonText}
                    </button>
                </div>
            `;
        case 'heading':
            return `<${block.content.level} style="color: #1e293b; margin: 16px 0;">${block.content.text}</${block.content.level}>`;
        case 'paragraph':
            return `<p style="color: #64748b; line-height: 1.8;">${block.content.text}</p>`;
        case 'button':
            return `<button style="background: #3CCB8A; color: white; padding: 12px 24px; border-radius: 8px; border: none; font-weight: 600;">${block.content.text}</button>`;
        case 'image':
            return `<img src="${block.content.url}" alt="${block.content.alt}" style="max-width: 100%; border-radius: 8px;">`;
        case 'cards':
            return `
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                    ${block.content.items.map(item => `
                        <div style="padding: 24px; background: #f8fafc; border-radius: 8px; text-align: center;">
                            <div style="font-size: 32px; margin-bottom: 12px;">${item.icon}</div>
                            <h3 style="font-size: 18px; margin-bottom: 8px; color: #1e293b;">${item.title}</h3>
                            <p style="color: #64748b;">${item.text}</p>
                        </div>
                    `).join('')}
                </div>
            `;
        case 'video':
            return `<iframe width="100%" height="400" src="${block.content.url}" frameborder="0" allowfullscreen style="border-radius: 8px;"></iframe>`;
        case 'spacer':
            return `<div style="height: ${block.content.height};"></div>`;
        default:
            return `<p>Unknown block type: ${block.type}</p>`;
    }
}

// نقل Block
function moveBlock(index, direction) {
    const newIndex = index + direction;
    if (newIndex >= 0 && newIndex < pageBlocks.length) {
        [pageBlocks[index], pageBlocks[newIndex]] = [pageBlocks[newIndex], pageBlocks[index]];
        renderBlocks();
    }
}

// تعديل Block
function editBlock(index) {
    const block = pageBlocks[index];
    const newContent = prompt('أدخل المحتوى الجديد (JSON):', JSON.stringify(block.content, null, 2));
    if (newContent) {
        try {
            block.content = JSON.parse(newContent);
            renderBlocks();
        } catch(e) {
            alert('خطأ في صيغة JSON!');
        }
    }
}

// حذف Block
function deleteBlock(index) {
    if (confirm('هل تريد حذف هذا البلوك؟')) {
        pageBlocks.splice(index, 1);
        if (pageBlocks.length === 0) {
            dropZone.style.display = 'block';
        }
        renderBlocks();
    }
}

// تحديث JSON Data
function updateJsonData() {
    document.getElementById('jsonData').value = JSON.stringify(pageBlocks);
}

// Auto-generate slug from page name
document.querySelector('input[name="page_name"]').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .replace(/[^\u0600-\u06FFa-z0-9\s-]/g, '')
        .replace(/\s+/g, '-');
    document.querySelector('input[name="slug"]').value = slug;
});

// Form Submit
document.getElementById('pageBuilderForm').addEventListener('submit', function(e) {
    if (pageBlocks.length === 0) {
        e.preventDefault();
        alert('يجب إضافة بلوك واحد على الأقل!');
    }
});
</script>
@endpush
