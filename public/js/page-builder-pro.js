/**
 * Page Builder Pro - نظام بناء الصفحات الاحترافي
 * Version: 2.0
 */

(function() {
    'use strict';

    // ========================================
    // Global State Management
    // ========================================
    
    const PageBuilder = {
        // البيانات الرئيسية
        data: { sections: [] },
        
        // العنصر المحدد حالياً
        currentTarget: null,
        
        // تاريخ التغييرات (Undo/Redo)
        history: [],
        historyIndex: -1,
        
        // Auto-save timer
        autoSaveTimer: null,
        
        // Drag & Drop state
        draggedElement: null,
        draggedType: null,
        
        // Initialize
        init() {
            this.loadFromInput();
            this.setupEventListeners();
            this.renderPage();
            this.initDragDrop();
            this.setupAutoSave();
            this.loadDraft();
        },
        
        // ========================================
        // Data Management
        // ========================================
        
        loadFromInput() {
            const jsonInput = document.getElementById('jsonData');
            if (jsonInput && jsonInput.value) {
                try {
                    this.data = JSON.parse(jsonInput.value);
                } catch (e) {
                    // Invalid JSON, initialize empty data
                    this.data = { sections: [] };
                }
            }
        },
        
        updateJsonInput() {
            const jsonInput = document.getElementById('jsonData');
            if (jsonInput) {
                jsonInput.value = JSON.stringify(this.data);
            }
        },
        
        saveToHistory() {
            // حذف أي تاريخ بعد الموضع الحالي
            this.history = this.history.slice(0, this.historyIndex + 1);
            
            // إضافة الحالة الحالية
            this.history.push(JSON.parse(JSON.stringify(this.data)));
            this.historyIndex++;
            
            // الحد الأقصى 50 خطوة
            if (this.history.length > 50) {
                this.history.shift();
                this.historyIndex--;
            }
            
            this.updateUndoRedoButtons();
        },
        
        undo() {
            if (this.historyIndex > 0) {
                this.historyIndex--;
                this.data = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
                this.renderPage();
                this.updateJsonInput();
                this.updateUndoRedoButtons();
            }
        },
        
        redo() {
            if (this.historyIndex < this.history.length - 1) {
                this.historyIndex++;
                this.data = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
                this.renderPage();
                this.updateJsonInput();
                this.updateUndoRedoButtons();
            }
        },
        
        updateUndoRedoButtons() {
            const undoBtn = document.getElementById('undoBtn');
            const redoBtn = document.getElementById('redoBtn');
            
            if (undoBtn) {
                undoBtn.disabled = this.historyIndex <= 0;
            }
            
            if (redoBtn) {
                redoBtn.disabled = this.historyIndex >= this.history.length - 1;
            }
        },
        
        // ========================================
        // Auto-Save System
        // ========================================
        
        setupAutoSave() {
            // حفظ تلقائي كل 30 ثانية
            this.autoSaveTimer = setInterval(() => {
                this.saveDraft();
            }, 30000);
        },
        
        saveDraft() {
            try {
                localStorage.setItem('pageBuilderDraft', JSON.stringify(this.data));
                localStorage.setItem('pageBuilderDraftTime', new Date().toISOString());
                this.showToast('success', 'تم الحفظ التلقائي', 1000);
            } catch (e) {
                // Failed to save draft silently
            }
        },
        
        loadDraft() {
            const draft = localStorage.getItem('pageBuilderDraft');
            const draftTime = localStorage.getItem('pageBuilderDraftTime');
            
            if (draft && draftTime && !document.getElementById('jsonData').value) {
                const timeDiff = new Date() - new Date(draftTime);
                const hoursDiff = timeDiff / (1000 * 60 * 60);
                
                // إذا كان المسودة أحدث من 24 ساعة
                if (hoursDiff < 24) {
                    showConfirm(
                        `تم العثور على مسودة محفوظة من ${this.formatTimeAgo(draftTime)}.<br>هل تريد استعادتها؟`,
                        () => {
                            this.data = JSON.parse(draft);
                            this.renderPage();
                            this.updateJsonInput();
                            this.showToast('success', 'تم استعادة المسودة!');
                        },
                        'استعادة المسودة',
                        'نعم، استعادة',
                        'لا، ابدأ من جديد'
                    );
                }
            }
        },
        
        clearDraft() {
            localStorage.removeItem('pageBuilderDraft');
            localStorage.removeItem('pageBuilderDraftTime');
        },
        
        formatTimeAgo(dateString) {
            const now = new Date();
            const past = new Date(dateString);
            const diffMs = now - past;
            const diffMins = Math.floor(diffMs / 60000);
            
            if (diffMins < 60) return `منذ ${diffMins} دقيقة`;
            const diffHours = Math.floor(diffMins / 60);
            if (diffHours < 24) return `منذ ${diffHours} ساعة`;
            const diffDays = Math.floor(diffHours / 24);
            return `منذ ${diffDays} يوم`;
        },
        
        // ========================================
        // Event Listeners
        // ========================================
        
        setupEventListeners() {
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey || e.metaKey) {
                    if (e.key === 'z' && !e.shiftKey) {
                        e.preventDefault();
                        this.undo();
                    } else if (e.key === 'z' && e.shiftKey || e.key === 'y') {
                        e.preventDefault();
                        this.redo();
                    } else if (e.key === 's') {
                        e.preventDefault();
                        document.getElementById('pageForm').submit();
                    }
                }
            });
            
            // Form submit - clear draft
            const form = document.getElementById('pageForm');
            if (form) {
                form.addEventListener('submit', () => {
                    this.clearDraft();
                });
            }
        },
        
        // ========================================
        // Drag & Drop System (Enhanced)
        // ========================================
        
        initDragDrop() {
            this.initSidebarDraggables();
            this.initCanvasDropzones();
        },
        
        initSidebarDraggables() {
            document.querySelectorAll('.component-item[draggable="true"], .section-item[draggable="true"]').forEach(item => {
                item.addEventListener('dragstart', (e) => {
                    this.draggedType = item.dataset.type || item.dataset.cols;
                    this.draggedElement = item;
                    e.dataTransfer.effectAllowed = 'copy';
                    item.classList.add('dragging');
                });
                
                item.addEventListener('dragend', (e) => {
                    item.classList.remove('dragging');
                    this.draggedElement = null;
                    this.draggedType = null;
                });
            });
        },
        
        initCanvasDropzones() {
            const canvas = document.getElementById('pageCanvas');
            
            // Drop على Canvas مباشرة (لإضافة Sections)
            canvas.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
            });
            
            canvas.addEventListener('drop', (e) => {
                e.preventDefault();
                
                if (this.draggedType && !isNaN(this.draggedType)) {
                    // إضافة Section جديد
                    this.addSection(parseInt(this.draggedType));
                }
            });
            
            // Drop على Columns
            this.initColumnDropzones();
        },
        
        initColumnDropzones() {
            document.querySelectorAll('.grid-column').forEach(col => {
                col.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    col.classList.add('drag-over');
                });
                
                col.addEventListener('dragleave', (e) => {
                    if (e.target === col) {
                        col.classList.remove('drag-over');
                    }
                });
                
                col.addEventListener('drop', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    col.classList.remove('drag-over');
                    
                    const sectionIndex = parseInt(col.dataset.sectionIndex);
                    const columnIndex = parseInt(col.dataset.columnIndex);
                    
                    if (!isNaN(sectionIndex) && !isNaN(columnIndex) && this.draggedType) {
                        // إضافة Component للعمود
                        this.addComponent(sectionIndex, columnIndex, this.draggedType);
                    }
                });
            });
        },
        
        // ========================================
        // Section Management
        // ========================================
        
        addSection(columns) {
            const newSection = {
                type: `grid-${columns}`,
                columns: Array(columns).fill(null).map(() => ({ components: [] })),
                settings: {
                    backgroundColor: '#ffffff',
                    padding: '40px',
                    fullWidth: false
                }
            };
            
            this.data.sections.push(newSection);
            this.saveToHistory();
            this.renderPage();
            this.updateJsonInput();
            this.showToast('success', `تم إضافة قسم ${columns} أعمدة`);
        },
        
        duplicateSection(index) {
            const section = JSON.parse(JSON.stringify(this.data.sections[index]));
            this.data.sections.splice(index + 1, 0, section);
            this.saveToHistory();
            this.renderPage();
            this.updateJsonInput();
            this.showToast('success', 'تم نسخ القسم');
        },
        
        moveSection(index, direction) {
            if (direction === 'up' && index > 0) {
                [this.data.sections[index], this.data.sections[index - 1]] = 
                [this.data.sections[index - 1], this.data.sections[index]];
            } else if (direction === 'down' && index < this.data.sections.length - 1) {
                [this.data.sections[index], this.data.sections[index + 1]] = 
                [this.data.sections[index + 1], this.data.sections[index]];
            }
            
            this.saveToHistory();
            this.renderPage();
            this.updateJsonInput();
        },
        
        deleteSection(index) {
            showConfirm(
                'هل أنت متأكد من حذف هذا القسم؟<br><small>سيتم حذف جميع المكونات بداخله.</small>',
                () => {
                    this.data.sections.splice(index, 1);
                    this.saveToHistory();
                    this.renderPage();
                    this.updateJsonInput();
                    this.showToast('success', 'تم حذف القسم');
                },
                'حذف القسم'
            );
        },
        
        // ========================================
        // Component Management
        // ========================================
        
        addComponent(sectionIndex, columnIndex, type) {
            const component = this.createComponent(type);
            
            if (!this.data.sections[sectionIndex].columns[columnIndex].components) {
                this.data.sections[sectionIndex].columns[columnIndex].components = [];
            }
            
            this.data.sections[sectionIndex].columns[columnIndex].components.push(component);
            this.saveToHistory();
            this.renderPage();
            this.updateJsonInput();
            this.showToast('success', `تم إضافة ${this.getComponentName(type)}`);
        },
        
        createComponent(type) {
            const templates = {
                heading: { type: 'heading', text: 'عنوان جديد', level: 'h2', alignment: 'right' },
                paragraph: { type: 'paragraph', text: 'نص تجريبي هنا...', alignment: 'right' },
                button: { type: 'button', text: 'اضغط هنا', link: '#', style: 'primary', alignment: 'right' },
                image: { type: 'image', src: '', alt: '', mode: 'url', url: '' },
                video: { type: 'video', src: '', mode: 'url', url: '' },
                spacer: { type: 'spacer', height: '40px' },
                divider: { type: 'divider', style: 'solid', color: '#e2e8f0' },
                list: { type: 'list', items: ['عنصر 1', 'عنصر 2', 'عنصر 3'], style: 'bullet' },
                icon: { type: 'icon', icon: '⭐', size: '48px', color: '#667eea', mode: 'emoji' }
            };
            
            return templates[type] || templates.paragraph;
        },
        
        getComponentName(type) {
            const names = {
                heading: 'عنوان',
                paragraph: 'فقرة نصية',
                button: 'زر',
                image: 'صورة',
                video: 'فيديو',
                spacer: 'مسافة',
                divider: 'فاصل',
                list: 'قائمة',
                icon: 'أيقونة'
            };
            return names[type] || 'مكون';
        },
        
        deleteComponent(sectionIndex, columnIndex, componentIndex) {
            this.data.sections[sectionIndex].columns[columnIndex].components.splice(componentIndex, 1);
            this.saveToHistory();
            this.renderPage();
            this.updateJsonInput();
            this.showToast('success', 'تم حذف المكون');
        },
        
        // ========================================
        // Render System
        // ========================================
        
        renderPage() {
            const canvas = document.getElementById('pageCanvas');
            const emptyState = document.getElementById('emptyCanvas');
            
            if (!this.data.sections || this.data.sections.length === 0) {
                canvas.innerHTML = '';
                if (emptyState) emptyState.style.display = 'flex';
                return;
            }
            
            if (emptyState) emptyState.style.display = 'none';
            
            canvas.innerHTML = this.data.sections.map((section, sIndex) => 
                this.renderSection(section, sIndex)
            ).join('');
            
            // Re-init drag drop للعناصر الجديدة
            this.initColumnDropzones();
        },
        
        renderSection(section, sIndex) {
            const cols = section.columns.length;
            const settings = section.settings || {};
            
            return `
                <div class="page-section" style="background: ${settings.backgroundColor || '#fff'}; padding: ${settings.padding || '40px'};">
                    <div class="section-toolbar">
                        <button type="button" class="toolbar-btn btn-settings" onclick="PageBuilder.editSection(${sIndex})" title="إعدادات">⚙️</button>
                        <button type="button" class="toolbar-btn btn-duplicate" onclick="PageBuilder.duplicateSection(${sIndex})" title="نسخ">📋</button>
                        ${sIndex > 0 ? `<button type="button" class="toolbar-btn btn-move-up" onclick="PageBuilder.moveSection(${sIndex}, 'up')" title="للأعلى">⬆️</button>` : ''}
                        ${sIndex < this.data.sections.length - 1 ? `<button type="button" class="toolbar-btn btn-move-down" onclick="PageBuilder.moveSection(${sIndex}, 'down')" title="للأسفل">⬇️</button>` : ''}
                        <button type="button" class="toolbar-btn btn-delete" onclick="PageBuilder.deleteSection(${sIndex})" title="حذف">🗑️</button>
                    </div>
                    <div class="section-grid grid-cols-${cols}">
                        ${section.columns.map((col, cIndex) => this.renderColumn(col, sIndex, cIndex)).join('')}
                    </div>
                </div>
            `;
        },
        
        renderColumn(column, sIndex, cIndex) {
            const components = column.components || [];
            
            return `
                <div class="grid-column" data-section-index="${sIndex}" data-column-index="${cIndex}">
                    ${components.length === 0 ? 
                        '<div class="column-empty">اسحب مكوناً هنا</div>' : 
                        components.map((comp, compIndex) => this.renderComponent(comp, sIndex, cIndex, compIndex)).join('')
                    }
                </div>
            `;
        },
        
        renderComponent(component, sIndex, cIndex, compIndex) {
            let html = '';
            
            switch(component.type) {
                case 'heading':
                    html = `<${component.level} style="text-align: ${component.alignment};">${component.text}</${component.level}>`;
                    break;
                case 'paragraph':
                    html = `<p style="text-align: ${component.alignment};">${component.text}</p>`;
                    break;
                case 'button':
                    html = `<div style="text-align: ${component.alignment};"><a href="${component.link}" class="btn btn-${component.style}">${component.text}</a></div>`;
                    break;
                case 'image':
                    const imgSrc = component.mode === 'upload' ? `/storage/app/public/data/${component.src}` : component.url;
                    html = `<img src="${imgSrc}" alt="${component.alt}" style="max-width: 100%; height: auto;">`;
                    break;
                case 'video':
                    const videoSrc = component.mode === 'upload' ? `/storage/app/public/data/${component.src}` : component.url;
                    html = `<video src="${videoSrc}" controls style="max-width: 100%; height: auto;"></video>`;
                    break;
                case 'spacer':
                    html = `<div style="height: ${component.height};"></div>`;
                    break;
                case 'divider':
                    html = `<hr style="border-top: 2px ${component.style} ${component.color};">`;
                    break;
                case 'list':
                    const listTag = component.style === 'bullet' ? 'ul' : 'ol';
                    html = `<${listTag}>${component.items.map(item => `<li>${item}</li>`).join('')}</${listTag}>`;
                    break;
                case 'icon':
                    const iconSrc = component.mode === 'emoji' ? component.icon : `/storage/app/public/data/${component.src}`;
                    const iconHtml = component.mode === 'emoji' ? iconSrc : `<img src="${iconSrc}" style="width: ${component.size}; height: ${component.size};">`;
                    html = `<div style="font-size: ${component.size}; color: ${component.color}; text-align: center;">${iconHtml}</div>`;
                    break;
            }
            
            return `
                <div class="grid-component">
                    <div class="component-actions">
                        <button type="button" class="action-btn" onclick="PageBuilder.editComponent(${sIndex}, ${cIndex}, ${compIndex})" title="تعديل">✏️</button>
                        <button type="button" class="action-btn" onclick="PageBuilder.deleteComponent(${sIndex}, ${cIndex}, ${compIndex})" title="حذف">🗑️</button>
                    </div>
                    ${html}
                </div>
            `;
        },
        
        // ========================================
        // Toast Notifications
        // ========================================
        
        showToast(type, message, duration = 2000) {
            // استخدام نظام Toast بدلاً من showSuccess/showError
            const toast = document.createElement('div');
            toast.className = `builder-toast builder-toast-${type}`;
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                background: ${type === 'success' ? '#10b981' : '#ef4444'};
                color: white;
                padding: 12px 24px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 100000;
                animation: slideInUp 0.3s ease;
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOutDown 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
    };
    
    // ========================================
    // Expose to Global Scope
    // ========================================
    
    window.PageBuilder = PageBuilder;
    
    // Auto-initialize when DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => PageBuilder.init());
    } else {
        PageBuilder.init();
    }
    
})();

// ========================================
// Animation Keyframes (inject to head)
// ========================================

const style = document.createElement('style');
style.textContent = `
    @keyframes slideInUp {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    @keyframes slideOutDown {
        from { transform: translateY(0); opacity: 1; }
        to { transform: translateY(100%); opacity: 0; }
    }
    
    .dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }
`;
document.head.appendChild(style);
