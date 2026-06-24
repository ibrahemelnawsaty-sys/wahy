@extends('layouts.admin')

@section('title', 'إنشاء صفحة جديدة')
@section('page-title', 'إنشاء صفحة جديدة')

@push('styles')
<style>
.page-builder-container {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 24px;
    min-height: 600px;
}

.blocks-sidebar {
    background: white;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e2e8f0;
    height: fit-content;
    position: sticky;
    top: 24px;
}

.sidebar-title {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 16px;
}

.block-item {
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: grab;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    user-select: none;
}

.block-item:hover {
    background: var(--color-primary-light);
    border-color: var(--color-primary);
    transform: translateY(-2px);
}

.block-item:active {
    cursor: grabbing;
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
    margin-bottom: 2px;
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
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 60px 24px;
    text-align: center;
    color: #94a3b8;
    transition: all 0.3s ease;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.drop-zone.drag-over {
    border-color: var(--color-primary);
    background: var(--color-primary-light);
    transform: scale(1.02);
}

.drop-zone-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.dropped-block {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    position: relative;
    transition: all 0.3s ease;
}

.dropped-block:hover {
    border-color: var(--color-primary);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.block-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}

.block-type-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}

.block-controls {
    display: flex;
    gap: 8px;
}

.block-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 14px;
}

.block-btn:hover {
    transform: scale(1.1);
}

.btn-edit {
    background: #3b82f6;
    color: white;
}

.btn-move-up {
    background: #8b5cf6;
    color: white;
}

.btn-move-down {
    background: #8b5cf6;
    color: white;
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.block-content-preview {
    padding: 16px;
    background: #f8fafc;
    border-radius: 8px;
    font-size: 13px;
    color: #64748b;
    max-height: 200px;
    overflow-y: auto;
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
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 32px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    font-size: 20px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #334155;
    margin-bottom: 8px;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.btn-save {
    background: var(--color-primary);
    color: white;
    padding: 10px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.btn-cancel {
    background: #e2e8f0;
    color: #64748b;
    padding: 10px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.cards-editor {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin-top: 8px;
}

.card-item {
    background: #f8fafc;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 12px;
}

.btn-add-card {
    background: var(--color-primary);
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
}
</style>
@endpush

@section('content')
<form action="{{ route('admin.pages.store') }}" method="POST" id="pageBuilderForm">
    @csrf
    
    <!-- Page Info -->
    <div class="admin-card" style="margin-bottom: 24px;">
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
            <textarea id="meta_description" name="meta_description" class="admin-input" rows="2"></textarea>
        </div>

        <div class="admin-form-group">
            <label>
                <input type="checkbox" name="is_active" checked>
                نشط
            </label>
        </div>
    </div>

    <!-- Page Builder -->
    <div class="page-builder-container">
        <!-- Sidebar -->
        <div class="blocks-sidebar">
            <div class="sidebar-title">📦 البلوكات المتاحة</div>
            
            <div class="block-item" draggable="true" data-block-type="hero">
                <div class="block-icon">🎯</div>
                <div class="block-info">
                    <div class="block-title">قسم رئيسي</div>
                    <div class="block-desc">صورة كبيرة + عنوان رئيسي</div>
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
            <div class="drop-zone" id="dropZone" style="display: none;">
                <div class="drop-zone-icon">📦</div>
                <div>اسحب البلوكات هنا لبناء الصفحة</div>
            </div>
            <div id="pageBlocks"></div>
        </div>
    </div>

    <input type="hidden" name="json_data" id="jsonData">

    <div class="admin-form-actions" style="margin-top: 24px;">
        <a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-secondary">إلغاء</a>
        <button type="submit" class="admin-btn admin-btn-primary">💾 إنشاء الصفحة</button>
    </div>
</form>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">✏️ تعديل البلوك</div>
        <div id="modalForm"></div>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal()">إلغاء</button>
            <button type="button" class="btn-save" onclick="saveBlockEdit()">حفظ</button>
        </div>
    </div>
</div>

<script>
let pageBlocks = [];
let currentEditIndex = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    renderBlocks();
    initDragAndDrop();
});

// Render blocks
function renderBlocks() {
    const container = document.getElementById('pageBlocks');
    const dropZone = document.getElementById('dropZone');
    
    if (pageBlocks.length === 0) {
        dropZone.style.display = 'flex';
        container.innerHTML = '';
        return;
    }
    
    dropZone.style.display = 'none';
    container.innerHTML = '';
    
    pageBlocks.forEach((block, index) => {
        const blockEl = createBlockElement(block, index);
        container.appendChild(blockEl);
    });
    
    updateJsonData();
}

// Create block element
function createBlockElement(block, index) {
    const div = document.createElement('div');
    div.className = 'dropped-block';
    div.innerHTML = `
        <div class="block-header">
            <div class="block-type-label">
                ${getBlockIcon(block.type)} ${getBlockName(block.type)}
            </div>
            <div class="block-controls">
                ${index > 0 ? '<button type="button" class="block-btn btn-move-up" onclick="moveBlock(' + index + ', -1)">⬆️</button>' : ''}
                ${index < pageBlocks.length - 1 ? '<button type="button" class="block-btn btn-move-down" onclick="moveBlock(' + index + ', 1)">⬇️</button>' : ''}
                <button type="button" class="block-btn btn-edit" onclick="editBlock(${index})">✏️</button>
                <button type="button" class="block-btn btn-delete" onclick="deleteBlock(${index})">🗑️</button>
            </div>
        </div>
        <div class="block-content-preview">
            ${getBlockPreview(block)}
        </div>
    `;
    return div;
}

// Get block icon
function getBlockIcon(type) {
    const icons = {
        hero: '🎯',
        heading: '📝',
        paragraph: '📄',
        button: '🔘',
        image: '🖼️',
        cards: '🎴',
        video: '🎥',
        spacer: '📏'
    };
    return icons[type] || '📦';
}

// Get block name
function getBlockName(type) {
    const names = {
        hero: 'Hero Section',
        heading: 'Heading',
        paragraph: 'Paragraph',
        button: 'Button',
        image: 'Image',
        cards: 'Cards',
        video: 'Video',
        spacer: 'Spacer'
    };
    return names[type] || type;
}

// Get block preview
function getBlockPreview(block) {
    switch(block.type) {
        case 'hero':
            return `<strong>${block.content.title || 'عنوان'}</strong><br>${block.content.subtitle || 'وصف'}`;
        case 'heading':
            return `<${block.content.level}>${block.content.text || 'عنوان'}</${block.content.level}>`;
        case 'paragraph':
            return block.content.text || 'نص الفقرة';
        case 'button':
            return `زر: ${block.content.text || 'انقر هنا'}`;
        case 'image':
            return `صورة: ${block.content.url || 'رابط الصورة'}`;
        case 'cards':
            return `${(block.content.items || []).length} بطاقة`;
        case 'video':
            return `فيديو: ${block.content.url || 'رابط الفيديو'}`;
        case 'spacer':
            return `مسافة: ${block.content.height || '40px'}`;
        default:
            return JSON.stringify(block.content);
    }
}

// Drag and Drop
function initDragAndDrop() {
    const blockItems = document.querySelectorAll('.block-item[draggable="true"]');
    const dropZone = document.getElementById('dropZone');
    const pageBlocks = document.getElementById('pageBlocks');
    
    blockItems.forEach(item => {
        item.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('blockType', item.dataset.blockType);
        });
    });
    
    [dropZone, pageBlocks].forEach(zone => {
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });
        
        zone.addEventListener('dragleave', () => {
            zone.classList.remove('drag-over');
        });
        
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            const blockType = e.dataTransfer.getData('blockType');
            if (blockType) {
                addBlock(blockType);
            }
        });
    });
}

// Add new block
function addBlock(type) {
    const newBlock = {
        type: type,
        content: getDefaultContent(type)
    };
    pageBlocks.push(newBlock);
    renderBlocks();
}

// Get default content
function getDefaultContent(type) {
    const defaults = {
        hero: { title: 'عنوان رئيسي', subtitle: 'وصف قصير', buttonText: 'ابدأ الآن', buttonLink: '#' },
        heading: { level: 'h2', text: 'عنوان' },
        paragraph: { text: 'اكتب النص هنا...' },
        button: { text: 'انقر هنا', link: '#' },
        image: { url: 'https://via.placeholder.com/800x400', alt: 'صورة' },
        cards: { items: [{ icon: '⭐', title: 'عنوان', text: 'نص' }] },
        video: { url: 'https://www.youtube.com/embed/VIDEO_ID' },
        spacer: { height: '40px' }
    };
    return defaults[type] || {};
}

// Move block
function moveBlock(index, direction) {
    const newIndex = index + direction;
    if (newIndex >= 0 && newIndex < pageBlocks.length) {
        [pageBlocks[index], pageBlocks[newIndex]] = [pageBlocks[newIndex], pageBlocks[index]];
        renderBlocks();
    }
}

// Delete block
function deleteBlock(index) {
    if (confirm('هل تريد حذف هذا البلوك؟')) {
        pageBlocks.splice(index, 1);
        renderBlocks();
    }
}

// Edit block
function editBlock(index) {
    currentEditIndex = index;
    const block = pageBlocks[index];
    const modalForm = document.getElementById('modalForm');
    
    modalForm.innerHTML = getEditForm(block);
    document.getElementById('editModal').classList.add('active');
}

// Get edit form
function getEditForm(block) {
    switch(block.type) {
        case 'hero':
            return `
                <div class="form-group">
                    <label>العنوان</label>
                    <input type="text" id="edit_title" value="${block.content.title || ''}" class="admin-input">
                </div>
                <div class="form-group">
                    <label>الوصف</label>
                    <input type="text" id="edit_subtitle" value="${block.content.subtitle || ''}" class="admin-input">
                </div>
                <div class="form-group">
                    <label>نص الزر</label>
                    <input type="text" id="edit_buttonText" value="${block.content.buttonText || ''}" class="admin-input">
                </div>
                <div class="form-group">
                    <label>رابط الزر</label>
                    <input type="text" id="edit_buttonLink" value="${block.content.buttonLink || ''}" class="admin-input">
                </div>
            `;
        case 'heading':
            return `
                <div class="form-group">
                    <label>المستوى</label>
                    <select id="edit_level" class="admin-input">
                        <option value="h1" ${block.content.level === 'h1' ? 'selected' : ''}>H1</option>
                        <option value="h2" ${block.content.level === 'h2' ? 'selected' : ''}>H2</option>
                        <option value="h3" ${block.content.level === 'h3' ? 'selected' : ''}>H3</option>
                        <option value="h4" ${block.content.level === 'h4' ? 'selected' : ''}>H4</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>النص</label>
                    <input type="text" id="edit_text" value="${block.content.text || ''}" class="admin-input">
                </div>
            `;
        case 'paragraph':
            return `
                <div class="form-group">
                    <label>النص</label>
                    <textarea id="edit_text" class="admin-input" rows="6">${block.content.text || ''}</textarea>
                </div>
            `;
        case 'button':
            return `
                <div class="form-group">
                    <label>نص الزر</label>
                    <input type="text" id="edit_text" value="${block.content.text || ''}" class="admin-input">
                </div>
                <div class="form-group">
                    <label>الرابط</label>
                    <input type="text" id="edit_link" value="${block.content.link || ''}" class="admin-input">
                </div>
            `;
        case 'image':
            return `
                <div class="form-group">
                    <label>رابط الصورة</label>
                    <input type="text" id="edit_url" value="${block.content.url || ''}" class="admin-input">
                </div>
                <div class="form-group">
                    <label>النص البديل</label>
                    <input type="text" id="edit_alt" value="${block.content.alt || ''}" class="admin-input">
                </div>
            `;
        case 'video':
            return `
                <div class="form-group">
                    <label>رابط الفيديو (YouTube Embed)</label>
                    <input type="text" id="edit_url" value="${block.content.url || ''}" class="admin-input">
                </div>
            `;
        case 'spacer':
            return `
                <div class="form-group">
                    <label>الارتفاع (مثال: 40px)</label>
                    <input type="text" id="edit_height" value="${block.content.height || '40px'}" class="admin-input">
                </div>
            `;
        case 'cards':
            return `
                <div class="form-group">
                    <label>البطاقات (JSON)</label>
                    <textarea id="edit_items" class="admin-input" rows="10">${JSON.stringify(block.content.items || [], null, 2)}</textarea>
                    <small style="color: #64748b;">مثال: [{"icon":"⭐","title":"عنوان","text":"نص"}]</small>
                </div>
            `;
        default:
            return '<p>نوع غير معروف</p>';
    }
}

// Save block edit
function saveBlockEdit() {
    if (currentEditIndex === null) return;
    
    const block = pageBlocks[currentEditIndex];
    
    switch(block.type) {
        case 'hero':
            block.content = {
                title: document.getElementById('edit_title').value,
                subtitle: document.getElementById('edit_subtitle').value,
                buttonText: document.getElementById('edit_buttonText').value,
                buttonLink: document.getElementById('edit_buttonLink').value
            };
            break;
        case 'heading':
            block.content = {
                level: document.getElementById('edit_level').value,
                text: document.getElementById('edit_text').value
            };
            break;
        case 'paragraph':
            block.content = { text: document.getElementById('edit_text').value };
            break;
        case 'button':
            block.content = {
                text: document.getElementById('edit_text').value,
                link: document.getElementById('edit_link').value
            };
            break;
        case 'image':
            block.content = {
                url: document.getElementById('edit_url').value,
                alt: document.getElementById('edit_alt').value
            };
            break;
        case 'video':
            block.content = { url: document.getElementById('edit_url').value };
            break;
        case 'spacer':
            block.content = { height: document.getElementById('edit_height').value };
            break;
        case 'cards':
            try {
                block.content = { items: JSON.parse(document.getElementById('edit_items').value) };
            } catch(e) {
                alert('خطأ في صيغة JSON');
                return;
            }
            break;
    }
    
    renderBlocks();
    closeModal();
}

// Close modal
function closeModal() {
    document.getElementById('editModal').classList.remove('active');
    currentEditIndex = null;
}

// Update JSON data
function updateJsonData() {
    document.getElementById('jsonData').value = JSON.stringify(pageBlocks);
}

// Auto-generate slug
document.getElementById('page_name').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\u0600-\u06FFa-z0-9-]/g, '');
    document.getElementById('slug').value = slug;
});

// Form submit
document.getElementById('pageBuilderForm').addEventListener('submit', function(e) {
    if (pageBlocks.length === 0) {
        e.preventDefault();
        alert('يجب إضافة بلوك واحد على الأقل!');
    }
    updateJsonData();
});
</script>
@endsection
