// Landing Page Editor - Super Admin Only
// فصل JavaScript الخاص بوضع التحرير لتحسين الأداء

function landingEditor() {
    return {
        editMode: false,
        saving: false,
        changes: {},
        originalContent: {},
        draggedComponent: null,
        autoSaveInterval: null,
        lastSaved: null,
        showProperties: false,
        selectedElement: null,
        
        init() {
            window.landingEditorInstance = this;
            
            // تفعيل وضع التعديل تلقائياً إذا كان هناك query parameter
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('edit') === '1') {
                // تفعيل وضع التعديل بعد تحميل الصفحة
                setTimeout(() => {
                    this.toggleEditMode();
                }, 100);
            }
        },
        
        toggleEditMode() {
            this.editMode = !this.editMode;
            document.body.classList.toggle('edit-mode', this.editMode);
            
            // تحديث كلاسات موقع اللوحة على body
            this.updateBodyEditorClasses();
            
            if (this.editMode) {
                this.enableEditing();
                this.setupDropZones();
                this.startAutoSave();
            } else {
                this.disableEditing();
                this.stopAutoSave();
            }
        },
        
        updateBodyEditorClasses() {
            // إزالة الكلاسات القديمة
            document.body.classList.remove('editor-left', 'editor-right', 'editor-collapsed');
            
            if (this.editMode) {
                // إضافة الكلاس حسب موقع اللوحة
                const position = localStorage.getItem('editor-position') || 'right';
                const collapsed = localStorage.getItem('editor-collapsed') === 'true';
                
                document.body.classList.add(`editor-${position}`);
                
                if (collapsed) {
                    document.body.classList.add('editor-collapsed');
                }
            }
        },
        
        setEditorPosition(position) {
            localStorage.setItem('editor-position', position);
            // تحديث كلاسات body
            document.body.classList.remove('editor-left', 'editor-right');
            document.body.classList.add(`editor-${position}`);
        },
        
        toggleEditorCollapse(collapsed) {
            localStorage.setItem('editor-collapsed', collapsed);
            // تحديث كلاسات body
            if (collapsed) {
                document.body.classList.add('editor-collapsed');
            } else {
                document.body.classList.remove('editor-collapsed');
            }
        },
        
        enableEditing() {
            // تفعيل تحرير النصوص
            document.querySelectorAll('[data-editable]').forEach(el => {
                const key = el.dataset.editable;
                this.originalContent[key] = el.innerHTML;
                
                el.contentEditable = true;
                
                // حفظ reference للـ handler لنتمكن من إزالته لاحقاً
                el._inputHandler = (e) => {
                    this.changes[key] = {
                        value: e.target.innerHTML,
                        type: 'text',
                        section: el.dataset.section || 'hero'
                    };
                };
                el.addEventListener('input', el._inputHandler);
            });
            
            // تفعيل تغيير الصور
            document.querySelectorAll('[data-editable-image]').forEach(el => {
                el.style.cursor = 'pointer';
                el.onclick = () => this.changeImage(el);
            });
        },
        
        disableEditing() {
            document.querySelectorAll('[data-editable]').forEach(el => {
                el.contentEditable = false;
                // إزالة الـ handler المحفوظ
                if (el._inputHandler) {
                    el.removeEventListener('input', el._inputHandler);
                    delete el._inputHandler;
                }
            });
            
            document.querySelectorAll('[data-editable-image]').forEach(el => {
                el.style.cursor = 'default';
                el.onclick = null;
            });
            
            // إزالة drop zones
            document.querySelectorAll('.drop-zone').forEach(zone => zone.remove());
        },
        
        async saveChanges() {
            await this.saveChangesInternal(false);
        },
        
        async saveChangesInternal(isAutoSave = false) {
            if (Object.keys(this.changes).length === 0) {
                if (!isAutoSave) alert('لا توجد تغييرات للحفظ');
                return false;
            }
            
            this.saving = true;
            
            try {
                const contents = Object.entries(this.changes).map(([key, data]) => ({
                    key,
                    value: data.value,
                    type: data.type,
                    section: data.section
                }));
                
                const response = await fetch('/api/landing/content/bulk-update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ contents })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    if (!isAutoSave) {
                        alert('✅ تم الحفظ بنجاح!');
                        this.changes = {};
                        this.originalContent = {};
                        this.toggleEditMode();
                        location.reload();
                    } else {
                        this.changes = {};
                    }
                    return true;
                } else {
                    if (!isAutoSave) alert('❌ فشل الحفظ: ' + (data.message || 'خطأ غير معروف'));
                    return false;
                }
            } catch (error) {
                console.error('Save Error:', error);
                if (!isAutoSave) {
                    alert('❌ حدث خطأ أثناء الحفظ. تحقق من الاتصال بالإنترنت.');
                }
                return false;
            } finally {
                this.saving = false;
            }
        },
        
        cancelEdit() {
            // استرجاع المحتوى الأصلي
            Object.entries(this.originalContent).forEach(([key, content]) => {
                const el = document.querySelector(`[data-editable="${key}"]`);
                if (el) el.innerHTML = content;
            });
            
            this.changes = {};
            this.originalContent = {};
            this.toggleEditMode();
        },
        
        changeImage(imgElement) {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            
            input.onchange = async (e) => {
                const file = e.target.files[0];
                if (!file) return;
                
                // التحقق من حجم الملف (أقل من 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('❌ حجم الصورة كبير جداً. الحد الأقصى 5MB');
                    return;
                }
                
                const formData = new FormData();
                formData.append('image', file);
                formData.append('key', imgElement.dataset.editableImage);
                
                try {
                    const response = await fetch('/api/landing/content/upload-image', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        imgElement.src = data.path;
                        alert('✅ تم تحديث الصورة!');
                    } else {
                        alert('❌ فشل رفع الصورة: ' + (data.message || 'خطأ غير معروف'));
                    }
                } catch (error) {
                    console.error('Upload Error:', error);
                    alert('❌ فشل رفع الصورة. تحقق من الاتصال بالإنترنت.');
                }
            };
            
            input.click();
        },
        
        // Drag & Drop Functions
        dragStart(event, componentType) {
            this.draggedComponent = componentType;
            event.dataTransfer.effectAllowed = 'copy';
        },
        
        setupDropZones() {
            // إضافة drop zones بين الأقسام الموجودة
            const sections = document.querySelectorAll('main section');
            sections.forEach((section, index) => {
                if (!section.nextElementSibling?.classList.contains('drop-zone')) {
                    const dropZone = document.createElement('div');
                    dropZone.className = 'drop-zone';
                    dropZone.setAttribute('data-position', index + 1);
                    dropZone.innerHTML = '<p style="text-align:center;color:#999;font-size:14px;">اسحب مكون هنا</p>';
                    
                    dropZone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        e.currentTarget.classList.add('drag-over');
                    });
                    
                    dropZone.addEventListener('dragleave', (e) => {
                        e.currentTarget.classList.remove('drag-over');
                    });
                    
                    dropZone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        e.currentTarget.classList.remove('drag-over');
                        this.dropComponent(e.currentTarget);
                    });
                    
                    section.after(dropZone);
                }
            });
        },
        
        dropComponent(dropZone) {
            if (!this.draggedComponent) return;
            
            const componentHTML = this.getComponentTemplate(this.draggedComponent);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = componentHTML;
            const newSection = tempDiv.firstElementChild;
            
            dropZone.replaceWith(newSection);
            
            // إعادة setup drop zones
            setTimeout(() => this.setupDropZones(), 100);
            
            // تفعيل editing على المكون الجديد
            this.enableEditingForElement(newSection);
            
            alert('✅ تم إضافة المكون! عدّل المحتوى ثم احفظ');
            this.draggedComponent = null;
        },
        
        getComponentTemplate(type) {
            const templates = {
                'hero': `
                    <section class="hero section" data-component="hero">
                        <div class="component-actions">
                            <button onclick="this.closest('[data-component]').remove()">🗑️</button>
                        </div>
                        <div class="container">
                            <div class="hero-content">
                                <div class="hero-text">
                                    <h1 class="hero-title" data-editable="new_hero_${Date.now()}_title" data-section="hero">عنوان رئيسي جديد</h1>
                                    <p class="hero-description" data-editable="new_hero_${Date.now()}_desc" data-section="hero">وصف المكون الجديد هنا</p>
                                    <a href="#" class="btn btn-primary btn-lg" data-editable="new_hero_${Date.now()}_btn" data-section="hero">ابدأ الآن</a>
                                </div>
                            </div>
                        </div>
                    </section>
                `,
                'feature-card': `
                    <section class="section" data-component="feature-card">
                        <div class="component-actions">
                            <button onclick="this.closest('[data-component]').remove()">🗑️</button>
                        </div>
                        <div class="container">
                            <article class="feature-card" style="max-width: 400px; margin: 0 auto;">
                                <div class="feature-icon">⭐</div>
                                <h3 data-editable="new_feature_${Date.now()}_title" data-section="features">ميزة جديدة</h3>
                                <p data-editable="new_feature_${Date.now()}_desc" data-section="features">وصف الميزة هنا</p>
                            </article>
                        </div>
                    </section>
                `,
                'cta': `
                    <section class="cta section" data-component="cta">
                        <div class="component-actions">
                            <button onclick="this.closest('[data-component]').remove()">🗑️</button>
                        </div>
                        <div class="container">
                            <div class="cta-content">
                                <h2 data-editable="new_cta_${Date.now()}_title" data-section="cta">عنوان الدعوة للإجراء</h2>
                                <p data-editable="new_cta_${Date.now()}_desc" data-section="cta">نص تحفيزي</p>
                                <a href="#" class="btn btn-primary btn-lg">ابدأ الآن</a>
                            </div>
                        </div>
                    </section>
                `,
                'stats': `
                    <section class="section" data-component="stats" style="background:#f9fafb; padding:60px 0;">
                        <div class="component-actions">
                            <button onclick="this.closest('[data-component]').remove()">🗑️</button>
                        </div>
                        <div class="container">
                            <div class="hero-stats" style="justify-content:center;">
                                <div class="stat-item"><span class="stat-number" data-editable="new_stat_${Date.now()}_1" data-section="stats">100+</span><span class="stat-label">عنصر</span></div>
                                <div class="stat-item"><span class="stat-number" data-editable="new_stat_${Date.now()}_2" data-section="stats">200+</span><span class="stat-label">عنصر</span></div>
                                <div class="stat-item"><span class="stat-number" data-editable="new_stat_${Date.now()}_3" data-section="stats">300+</span><span class="stat-label">عنصر</span></div>
                            </div>
                        </div>
                    </section>
                `,
                'testimonial': `
                    <section class="section testimonial-section" data-component="testimonial" style="background:var(--bg-secondary); padding:80px 0;">
                        <div class="component-actions">
                            <button onclick="this.closest('[data-component]').remove()">🗑️</button>
                        </div>
                        <div class="container" style="max-width:800px; text-align:center;">
                            <div class="testimonial-card" style="background:var(--bg-primary); padding:40px; border-radius:16px;">
                                <div style="font-size:48px; margin-bottom:20px;">💬</div>
                                <p data-editable="new_testimonial_${Date.now()}_text" data-section="testimonials" style="font-size:18px; line-height:1.8; margin-bottom:24px;">شهادة رائعة من أحد العملاء تصف تجربتهم الإيجابية مع الخدمة أو المنتج.</p>
                                <div style="display:flex; align-items:center; justify-content:center; gap:12px;">
                                    <div style="width:50px; height:50px; border-radius:50%; background:#ddd;"></div>
                                    <div>
                                        <strong data-editable="new_testimonial_${Date.now()}_name" data-section="testimonials">اسم العميل</strong>
                                        <p style="font-size:14px; color:var(--text-muted);" data-editable="new_testimonial_${Date.now()}_title" data-section="testimonials">المسمى الوظيفي</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                `,
                'image-text': `
                    <section class="section" data-component="image-text" style="padding:80px 0;">
                        <div class="component-actions">
                            <button onclick="this.closest('[data-component]').remove()">🗑️</button>
                        </div>
                        <div class="container">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:60px; align-items:center;">
                                <div>
                                    <h2 data-editable="new_imgtext_${Date.now()}_title" data-section="content" style="font-size:32px; margin-bottom:20px;">عنوان القسم</h2>
                                    <p data-editable="new_imgtext_${Date.now()}_desc" data-section="content" style="font-size:18px; line-height:1.8; color:var(--text-secondary);">وصف تفصيلي للمحتوى يشرح الفكرة الرئيسية ويجذب القارئ لمعرفة المزيد.</p>
                                    <a href="#" class="btn btn-primary" style="margin-top:24px;" data-editable="new_imgtext_${Date.now()}_btn" data-section="content">اعرف المزيد</a>
                                </div>
                                <div style="background:#e5e7eb; border-radius:16px; aspect-ratio:4/3; display:flex; align-items:center; justify-content:center;">
                                    <span style="font-size:48px;">🖼️</span>
                                </div>
                            </div>
                        </div>
                    </section>
                `,
                'pricing': `
                    <section class="section" data-component="pricing" style="padding:80px 0; background:var(--bg-secondary);">
                        <div class="component-actions">
                            <button onclick="this.closest('[data-component]').remove()">🗑️</button>
                        </div>
                        <div class="container">
                            <h2 data-editable="new_pricing_${Date.now()}_title" data-section="pricing" style="text-align:center; font-size:36px; margin-bottom:48px;">خطط الأسعار</h2>
                            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:24px;">
                                <div style="background:var(--bg-primary); padding:32px; border-radius:16px; text-align:center;">
                                    <h3 data-editable="new_pricing_${Date.now()}_plan1" data-section="pricing">الخطة الأساسية</h3>
                                    <div style="font-size:48px; font-weight:bold; margin:20px 0;" data-editable="new_pricing_${Date.now()}_price1" data-section="pricing">$9</div>
                                    <p style="color:var(--text-muted);">شهرياً</p>
                                </div>
                                <div style="background:var(--primary-500); color:white; padding:32px; border-radius:16px; text-align:center; transform:scale(1.05);">
                                    <h3 data-editable="new_pricing_${Date.now()}_plan2" data-section="pricing">الخطة المميزة</h3>
                                    <div style="font-size:48px; font-weight:bold; margin:20px 0;" data-editable="new_pricing_${Date.now()}_price2" data-section="pricing">$29</div>
                                    <p style="opacity:0.8;">شهرياً</p>
                                </div>
                                <div style="background:var(--bg-primary); padding:32px; border-radius:16px; text-align:center;">
                                    <h3 data-editable="new_pricing_${Date.now()}_plan3" data-section="pricing">خطة المؤسسات</h3>
                                    <div style="font-size:48px; font-weight:bold; margin:20px 0;" data-editable="new_pricing_${Date.now()}_price3" data-section="pricing">$99</div>
                                    <p style="color:var(--text-muted);">شهرياً</p>
                                </div>
                            </div>
                        </div>
                    </section>
                `,
                'faq': `
                    <section class="section" data-component="faq" style="padding:80px 0;">
                        <div class="component-actions">
                            <button onclick="this.closest('[data-component]').remove()">🗑️</button>
                        </div>
                        <div class="container" style="max-width:800px;">
                            <h2 data-editable="new_faq_${Date.now()}_title" data-section="faq" style="text-align:center; font-size:36px; margin-bottom:48px;">الأسئلة الشائعة</h2>
                            <div style="display:flex; flex-direction:column; gap:16px;">
                                <details style="background:var(--bg-secondary); padding:20px; border-radius:12px;">
                                    <summary style="cursor:pointer; font-weight:600;" data-editable="new_faq_${Date.now()}_q1" data-section="faq">ما هو السؤال الأول؟</summary>
                                    <p style="margin-top:12px; color:var(--text-secondary);" data-editable="new_faq_${Date.now()}_a1" data-section="faq">هذه هي الإجابة على السؤال الأول.</p>
                                </details>
                                <details style="background:var(--bg-secondary); padding:20px; border-radius:12px;">
                                    <summary style="cursor:pointer; font-weight:600;" data-editable="new_faq_${Date.now()}_q2" data-section="faq">ما هو السؤال الثاني؟</summary>
                                    <p style="margin-top:12px; color:var(--text-secondary);" data-editable="new_faq_${Date.now()}_a2" data-section="faq">هذه هي الإجابة على السؤال الثاني.</p>
                                </details>
                            </div>
                        </div>
                    </section>
                `
            };
            
            return templates[type] || templates['feature-card'];
        },
        
        enableEditingForElement(element) {
            element.querySelectorAll('[data-editable]').forEach(el => {
                const key = el.dataset.editable;
                el.contentEditable = true;
                el.addEventListener('input', (e) => {
                    this.changes[key] = {
                        value: e.target.innerHTML,
                        type: 'text',
                        section: el.dataset.section || 'custom'
                    };
                });
            });
            
            element.querySelectorAll('[data-editable-image]').forEach(el => {
                el.style.cursor = 'pointer';
                el.onclick = () => this.changeImage(el);
            });
        },
        
        // Auto-save Functions
        startAutoSave() {
            // حفظ تلقائي كل 30 ثانية
            this.autoSaveInterval = setInterval(() => {
                if (Object.keys(this.changes).length > 0) {
                    this.autoSave();
                }
            }, 30000);
        },
        
        stopAutoSave() {
            if (this.autoSaveInterval) {
                clearInterval(this.autoSaveInterval);
                this.autoSaveInterval = null;
            }
        },
        
        async autoSave() {
            if (this.saving) return;
            
            console.log('🔄 Auto-saving...');
            const success = await this.saveChangesInternal(true);
            
            if (success) {
                const now = new Date();
                this.lastSaved = now.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });
            }
        },
        
        async createSnapshot() {
            try {
                const response = await fetch('/api/landing/content/snapshot', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ تم حفظ نسخة احتياطية!');
                } else {
                    alert('❌ فشل حفظ النسخة الاحتياطية');
                }
            } catch (error) {
                console.error('Snapshot Error:', error);
                alert('❌ حدث خطأ أثناء حفظ النسخة الاحتياطية');
            }
        },
        
        // Element Management
        editElement(element) {
            this.selectedElement = element;
            this.showProperties = true;
        },

        duplicateElement(element) {
            const clone = element.cloneNode(true);
            const originalId = element.getAttribute('data-element');
            if (originalId) {
                const newId = originalId + '-copy-' + Date.now();
                clone.setAttribute('data-element', newId);
            }
            
            element.insertAdjacentElement('afterend', clone);
            alert('✅ تم نسخ العنصر بنجاح');
        },

        deleteElement(element) {
            // Critical elements that cannot be deleted
            const criticalElements = ['header-logo', 'nav-link-1', 'nav-link-2', 'nav-link-3', 'nav-link-4', 'nav-link-5', 'nav-link-6', 'theme-toggle', 'login-btn', 'register-btn'];
            const elementId = element.getAttribute('data-element');
            
            if (criticalElements.includes(elementId)) {
                alert('⚠️ لا يمكن حذف هذا العنصر الأساسي من الـ Header. العناصر الأساسية محمية للحفاظ على سلامة الموقع.');
                return false;
            }
            
            element.remove();
            alert('✅ تم حذف العنصر');
        },
        
        duplicateSection(section) {
            const clone = section.cloneNode(true);
            
            // تحديث IDs في العناصر المنسوخة
            clone.querySelectorAll('[data-editable]').forEach(el => {
                const oldKey = el.dataset.editable;
                const newKey = oldKey + '_copy_' + Date.now();
                el.dataset.editable = newKey;
            });
            
            section.after(clone);
            this.enableEditingForElement(clone);
            
            setTimeout(() => this.setupDropZones(), 100);
            alert('✅ تم نسخ القسم بنجاح!');
        },
        
        editSectionProperties(section) {
            this.selectedElement = section;
            this.showProperties = true;
        },
        
        updateProperty(property, value) {
            if (!this.selectedElement) return;
            
            if (property === 'background') {
                this.selectedElement.style.background = value;
            } else if (property === 'color') {
                this.selectedElement.style.color = value;
            } else if (property === 'padding') {
                this.selectedElement.style.padding = value;
            } else if (property === 'fontSize') {
                this.selectedElement.style.fontSize = value;
            }
        },
        
        openIconPicker(iconElement) {
            this.selectedElement = iconElement;
            this.showProperties = true;
        },
        
        updateIcon(icon) {
            if (!this.selectedElement) return;
            
            if (this.selectedElement.dataset.editableIcon) {
                this.selectedElement.textContent = icon;
            }
            
            const key = this.selectedElement.dataset.editableIcon || this.selectedElement.dataset.editable;
            if (key) {
                this.changes[key] = {
                    value: icon,
                    type: 'text',
                    section: this.selectedElement.dataset.section || 'custom'
                };
            }
        },
        
        closeProperties() {
            this.showProperties = false;
            this.selectedElement = null;
        }
    };
}
