// Landing Page Editor - Super Admin Only
// المحرّر المرئي المدمج: التعديلات (نص/صورة/سحب/حذف/تكرار/أنماط) تُلتقط كلقطة
// HTML كاملة لـ<main> وتُحفظ عبر /api/landing/layout (تُعقَّم خادِمياً) وتُعرض
// للزوّار بدل القالب الثابت. مصدر الحقيقة الوحيد = التخطيط المحفوظ.

function landingEditor() {
    return {
        editMode: false,
        saving: false,
        changes: {},              // تعديلات النصوص (لعدّاد الشارة)
        structuralDirty: false,    // سحب/حذف/تكرار/نمط/صورة
        draggedComponent: null,
        autoSaveInterval: null,
        lastSaved: null,
        showProperties: false,
        selectedElement: null,
        _uidCounter: 0,
        _skipUnloadGuard: false,
        _beforeUnload: null,

        init() {
            window.landingEditorInstance = this;

            // حارس المغادرة مع تغييرات غير محفوظة
            this._beforeUnload = (e) => {
                if (!this._skipUnloadGuard && this.editMode && this.hasUnsavedChanges()) {
                    e.preventDefault();
                    e.returnValue = '';
                    return '';
                }
            };
            window.addEventListener('beforeunload', this._beforeUnload);

            // تفعيل وضع التعديل تلقائياً عبر ?edit=1
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('edit') === '1') {
                setTimeout(() => this.toggleEditMode(), 100);
            }
        },

        // ==================== دورة حياة وضع التحرير ====================
        toggleEditMode() {
            // الخروج من وضع التحرير مع تغييرات غير محفوظة → تأكيد + تجاهل نظيف بإعادة تحميل
            if (this.editMode && this.hasUnsavedChanges()) {
                if (!confirm('لديك تغييرات غير محفوظة. الخروج من وضع التحرير سيتجاهلها. هل تريد المتابعة؟')) {
                    return;
                }
                this._skipUnloadGuard = true;
                location.reload();
                return;
            }

            this.editMode = !this.editMode;
            document.body.classList.toggle('edit-mode', this.editMode);
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
            document.body.classList.remove('editor-left', 'editor-right', 'editor-collapsed');
            if (this.editMode) {
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
            document.body.classList.remove('editor-left', 'editor-right');
            document.body.classList.add(`editor-${position}`);
        },

        toggleEditorCollapse(collapsed) {
            localStorage.setItem('editor-collapsed', collapsed);
            document.body.classList.toggle('editor-collapsed', !!collapsed);
        },

        enableEditing() {
            // حقن أدوات القسم على كل الأقسام (يوحّد التجربة ويعمل على اللقطة المحفوظة)
            this.injectSectionChrome();

            document.querySelectorAll('[data-editable]').forEach(el => {
                el.contentEditable = true;
                if (!el._inputHandler) {
                    const key = el.dataset.editable;
                    el._inputHandler = (e) => {
                        this.changes[key] = {
                            value: e.target.innerHTML,
                            type: 'text',
                            section: el.dataset.section || 'hero',
                        };
                    };
                    el.addEventListener('input', el._inputHandler);
                }
            });

            document.querySelectorAll('[data-editable-image]').forEach(el => {
                el.style.cursor = 'pointer';
                el.onclick = () => this.changeImage(el);
            });
        },

        disableEditing() {
            document.querySelectorAll('[data-editable]').forEach(el => {
                el.contentEditable = false;
                if (el._inputHandler) {
                    el.removeEventListener('input', el._inputHandler);
                    delete el._inputHandler;
                }
            });

            document.querySelectorAll('[data-editable-image]').forEach(el => {
                el.style.cursor = 'default';
                el.onclick = null;
            });

            document.querySelectorAll('.drop-zone').forEach(zone => zone.remove());
            // إزالة أدوات القسم المحقونة فقط (نُبقي الثابتة في القالب)
            document.querySelectorAll('#main-content [data-injected-chrome]').forEach(bar => bar.remove());
        },

        // حقن شريط أدوات لكل قسم يفتقده — يوحّد التحرير على القالب الثابت واللقطة المحفوظة
        injectSectionChrome() {
            document.querySelectorAll('#main-content > section').forEach(section => {
                if (section.querySelector(':scope > .section-actions')) return;

                const bar = document.createElement('div');
                bar.className = 'section-actions';
                bar.dataset.injectedChrome = '1';
                bar.innerHTML = `
                    <button type="button" title="نسخ القسم" aria-label="نسخ القسم">📋</button>
                    <button type="button" title="تعديل خصائص القسم" aria-label="تعديل خصائص القسم">⚙️</button>
                    <button type="button" class="danger" title="حذف القسم" aria-label="حذف القسم">🗑️</button>`;
                const btns = bar.querySelectorAll('button');
                btns[0].addEventListener('click', () => this.duplicateSection(section));
                btns[1].addEventListener('click', () => this.editSectionProperties(section));
                btns[2].addEventListener('click', () => {
                    if (confirm('حذف هذا القسم؟')) {
                        section.remove();
                        this.markStructuralChange();
                    }
                });
                section.prepend(bar);
            });
        },

        // ==================== الحفظ (لقطة التخطيط) ====================
        async saveChanges() {
            await this.saveChangesInternal(false);
        },

        async saveChangesInternal(isAutoSave = false) {
            if (this.saving) return false; // يمنع تسابق الحفظ اليدوي/التلقائي

            if (!this.hasUnsavedChanges()) {
                if (!isAutoSave) this.toast('لا توجد تغييرات للحفظ', 'info');
                return false;
            }

            const token = this.csrfToken();
            if (!token) {
                if (!isAutoSave) this.toast('انتهت الجلسة. يُعاد تحميل الصفحة…', 'error');
                this._skipUnloadGuard = true;
                setTimeout(() => location.reload(), 1500);
                return false;
            }

            this.saving = true;
            try {
                const response = await fetch('/api/landing/layout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ html: this.captureLayoutHtml() }),
                });

                if (response.status === 419) {
                    if (!isAutoSave) this.toast('انتهت صلاحية الجلسة. يُعاد تحميل الصفحة…', 'error');
                    this._skipUnloadGuard = true;
                    setTimeout(() => location.reload(), 1500);
                    return false;
                }

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                if (data.success) {
                    this.changes = {};
                    this.structuralDirty = false;
                    this.lastSaved = new Date().toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });
                    if (!isAutoSave) this.toast('تم حفظ الصفحة بنجاح ✅', 'success');
                    return true;
                }

                if (!isAutoSave) this.toast('فشل الحفظ: ' + (data.message || 'خطأ غير معروف'), 'error');
                return false;
            } catch (error) {
                console.error('Save Error:', error);
                if (!isAutoSave) this.toast('تعذّر الحفظ. تحقّق من اتصالك بالإنترنت.', 'error');
                return false;
            } finally {
                this.saving = false;
            }
        },

        // يلتقط محتوى <main> بعد إزالة أدوات التحرير و contenteditable (الخادم يعيد التعقيم)
        captureLayoutHtml() {
            const main = document.getElementById('main-content');
            if (!main) return '';

            const clone = main.cloneNode(true);
            clone.querySelectorAll('.section-actions, .element-actions, .component-actions, .drop-zone, [data-injected-chrome]')
                .forEach(node => node.remove());
            clone.querySelectorAll('[contenteditable]').forEach(el => el.removeAttribute('contenteditable'));
            clone.querySelectorAll('[style]').forEach(el => {
                if (el.style && el.style.cursor) {
                    el.style.cursor = '';
                    if (!el.getAttribute('style')) el.removeAttribute('style');
                }
            });

            return clone.innerHTML.trim();
        },

        cancelEdit() {
            if (this.hasUnsavedChanges() && !confirm('تجاهل كل التغييرات غير المحفوظة؟')) {
                return;
            }
            // تجاهل نظيف: أعِد التحميل للحالة المحفوظة على الخادم
            this._skipUnloadGuard = true;
            location.reload();
        },

        // استعادة القالب الافتراضي (حذف التخطيط المخصّص) — صمام أمان
        async resetToDefault() {
            if (!confirm('استعادة القالب الافتراضي؟ سيُحذف كل تخصيص محفوظ لهذه الصفحة.')) return;

            const token = this.csrfToken();
            if (!token) {
                this.toast('انتهت الجلسة. أعِد تحميل الصفحة.', 'error');
                return;
            }

            try {
                const response = await fetch('/api/landing/layout', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                });
                if (response.ok) {
                    this.toast('تمت استعادة القالب الافتراضي. إعادة تحميل…', 'success');
                    this._skipUnloadGuard = true;
                    setTimeout(() => location.reload(), 900);
                } else {
                    this.toast('فشلت الاستعادة', 'error');
                }
            } catch (error) {
                console.error('Reset Error:', error);
                this.toast('تعذّر الاتصال بالخادم', 'error');
            }
        },

        // ==================== الصور ====================
        changeImage(imgElement) {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';

            input.onchange = async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                // الحد الأقصى 2MB (مطابق لتحقّق الخادم image|max:2048)
                if (file.size > 2 * 1024 * 1024) {
                    this.toast('حجم الصورة كبير جداً. الحد الأقصى 2MB', 'error');
                    return;
                }

                const token = this.csrfToken();
                if (!token) {
                    this.toast('انتهت الجلسة. أعِد تحميل الصفحة.', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('image', file);
                formData.append('key', imgElement.dataset.editableImage || 'landing_image');

                const prevCursor = imgElement.style.cursor;
                imgElement.style.cursor = 'wait';
                imgElement.style.opacity = '0.6';

                try {
                    const response = await fetch('/api/landing/content/upload-image', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        body: formData,
                    });

                    if (response.status === 422) {
                        const d = await response.json().catch(() => null);
                        this.toast(d?.message || 'ملف الصورة غير صالح', 'error');
                        return;
                    }
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const data = await response.json();
                    if (data.success) {
                        imgElement.src = data.path;
                        this.markStructuralChange();
                        this.toast('تم تحديث الصورة ✅', 'success');
                    } else {
                        this.toast('فشل رفع الصورة: ' + (data.message || 'خطأ غير معروف'), 'error');
                    }
                } catch (error) {
                    console.error('Upload Error:', error);
                    this.toast('تعذّر رفع الصورة. تحقّق من اتصالك.', 'error');
                } finally {
                    imgElement.style.cursor = prevCursor || 'pointer';
                    imgElement.style.opacity = '';
                }
            };

            input.click();
        },

        // ==================== السحب والإفلات ====================
        dragStart(event, componentType) {
            this.draggedComponent = componentType;
            event.dataTransfer.effectAllowed = 'copy';
        },

        setupDropZones() {
            const sections = document.querySelectorAll('#main-content > section');
            sections.forEach((section, index) => {
                if (section.nextElementSibling?.classList.contains('drop-zone')) return;

                const dropZone = document.createElement('div');
                dropZone.className = 'drop-zone';
                dropZone.setAttribute('data-position', index + 1);
                dropZone.innerHTML = '<p style="text-align:center;color:#999;font-size:14px;">اسحب مكوّناً هنا</p>';

                dropZone.addEventListener('dragover', (ev) => {
                    ev.preventDefault();
                    ev.currentTarget.classList.add('drag-over');
                });
                dropZone.addEventListener('dragleave', (ev) => {
                    ev.currentTarget.classList.remove('drag-over');
                });
                dropZone.addEventListener('drop', (ev) => {
                    ev.preventDefault();
                    ev.currentTarget.classList.remove('drag-over');
                    this.dropComponent(ev.currentTarget);
                });

                section.after(dropZone);
            });
        },

        dropComponent(dropZone) {
            if (!this.draggedComponent) return;

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = this.getComponentTemplate(this.draggedComponent);
            const newSection = tempDiv.firstElementChild;
            if (!newSection) return;

            dropZone.replaceWith(newSection);
            this.enableEditingForElement(newSection);
            this.injectSectionChrome();
            this.markStructuralChange();

            // إعادة بناء مناطق الإفلات (تُمسح القديمة أولاً في setupDropZones عبر فحص الشقيق)
            this.refreshDropZones();

            this.toast('تمت إضافة المكوّن. عدّله ثم احفظ.', 'success');
            this.draggedComponent = null;
        },

        refreshDropZones() {
            document.querySelectorAll('.drop-zone').forEach(z => z.remove());
            this.setupDropZones();
        },

        getComponentTemplate(type) {
            const uid = this.uid();
            const templates = {
                'hero': `
                    <section class="hero section" data-component="hero">
                        <div class="container">
                            <div class="hero-content">
                                <div class="hero-text">
                                    <h1 class="hero-title" data-editable="new_hero_${uid}_title" data-section="hero">عنوان رئيسي جديد</h1>
                                    <p class="hero-description" data-editable="new_hero_${uid}_desc" data-section="hero">وصف المكوّن الجديد هنا</p>
                                    <a href="#" class="btn btn-primary btn-lg" data-editable="new_hero_${uid}_btn" data-section="hero">ابدأ الآن</a>
                                </div>
                            </div>
                        </div>
                    </section>`,
                'feature-card': `
                    <section class="section" data-component="feature-card">
                        <div class="container">
                            <article class="feature-card" style="max-width: 400px; margin: 0 auto;">
                                <div class="feature-icon">⭐</div>
                                <h3 data-editable="new_feature_${uid}_title" data-section="features">ميزة جديدة</h3>
                                <p data-editable="new_feature_${uid}_desc" data-section="features">وصف الميزة هنا</p>
                            </article>
                        </div>
                    </section>`,
                'cta': `
                    <section class="cta section" data-component="cta">
                        <div class="container">
                            <div class="cta-content">
                                <h2 data-editable="new_cta_${uid}_title" data-section="cta">عنوان الدعوة للإجراء</h2>
                                <p data-editable="new_cta_${uid}_desc" data-section="cta">نص تحفيزي</p>
                                <a href="#" class="btn btn-primary btn-lg">ابدأ الآن</a>
                            </div>
                        </div>
                    </section>`,
                'stats': `
                    <section class="section" data-component="stats" style="background:#f9fafb; padding:60px 0;">
                        <div class="container">
                            <div class="hero-stats" style="justify-content:center;">
                                <div class="stat-item"><span class="stat-number" data-editable="new_stat_${uid}_1" data-section="stats">100+</span><span class="stat-label">عنصر</span></div>
                                <div class="stat-item"><span class="stat-number" data-editable="new_stat_${uid}_2" data-section="stats">200+</span><span class="stat-label">عنصر</span></div>
                                <div class="stat-item"><span class="stat-number" data-editable="new_stat_${uid}_3" data-section="stats">300+</span><span class="stat-label">عنصر</span></div>
                            </div>
                        </div>
                    </section>`,
                'testimonial': `
                    <section class="section testimonial-section" data-component="testimonial" style="background:var(--bg-secondary); padding:80px 0;">
                        <div class="container" style="max-width:800px; text-align:center;">
                            <div class="testimonial-card" style="background:var(--bg-primary); padding:40px; border-radius:16px;">
                                <div style="font-size:48px; margin-bottom:20px;">💬</div>
                                <p data-editable="new_testimonial_${uid}_text" data-section="testimonials" style="font-size:18px; line-height:1.8; margin-bottom:24px;">شهادة رائعة من أحد العملاء تصف تجربتهم الإيجابية.</p>
                                <div style="display:flex; align-items:center; justify-content:center; gap:12px;">
                                    <div style="width:50px; height:50px; border-radius:50%; background:#ddd;"></div>
                                    <div>
                                        <strong data-editable="new_testimonial_${uid}_name" data-section="testimonials">اسم العميل</strong>
                                        <p style="font-size:14px; color:var(--text-muted);" data-editable="new_testimonial_${uid}_title" data-section="testimonials">المسمى الوظيفي</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>`,
                'image-text': `
                    <section class="section" data-component="image-text" style="padding:80px 0;">
                        <div class="container">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:60px; align-items:center;">
                                <div>
                                    <h2 data-editable="new_imgtext_${uid}_title" data-section="content" style="font-size:32px; margin-bottom:20px;">عنوان القسم</h2>
                                    <p data-editable="new_imgtext_${uid}_desc" data-section="content" style="font-size:18px; line-height:1.8; color:var(--text-secondary);">وصف تفصيلي للمحتوى يشرح الفكرة الرئيسية.</p>
                                    <a href="#" class="btn btn-primary" style="margin-top:24px;" data-editable="new_imgtext_${uid}_btn" data-section="content">اعرف المزيد</a>
                                </div>
                                <div style="background:#e5e7eb; border-radius:16px; aspect-ratio:4/3; display:flex; align-items:center; justify-content:center;">
                                    <span style="font-size:48px;">🖼️</span>
                                </div>
                            </div>
                        </div>
                    </section>`,
                'pricing': `
                    <section class="section" data-component="pricing" style="padding:80px 0; background:var(--bg-secondary);">
                        <div class="container">
                            <h2 data-editable="new_pricing_${uid}_title" data-section="pricing" style="text-align:center; font-size:36px; margin-bottom:48px;">خطط الأسعار</h2>
                            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:24px;">
                                <div style="background:var(--bg-primary); padding:32px; border-radius:16px; text-align:center;">
                                    <h3 data-editable="new_pricing_${uid}_plan1" data-section="pricing">الخطة الأساسية</h3>
                                    <div style="font-size:48px; font-weight:bold; margin:20px 0;" data-editable="new_pricing_${uid}_price1" data-section="pricing">$9</div>
                                    <p style="color:var(--text-muted);">شهرياً</p>
                                </div>
                                <div style="background:var(--primary-500); color:white; padding:32px; border-radius:16px; text-align:center; transform:scale(1.05);">
                                    <h3 data-editable="new_pricing_${uid}_plan2" data-section="pricing">الخطة المميزة</h3>
                                    <div style="font-size:48px; font-weight:bold; margin:20px 0;" data-editable="new_pricing_${uid}_price2" data-section="pricing">$29</div>
                                    <p style="opacity:0.8;">شهرياً</p>
                                </div>
                                <div style="background:var(--bg-primary); padding:32px; border-radius:16px; text-align:center;">
                                    <h3 data-editable="new_pricing_${uid}_plan3" data-section="pricing">خطة المؤسسات</h3>
                                    <div style="font-size:48px; font-weight:bold; margin:20px 0;" data-editable="new_pricing_${uid}_price3" data-section="pricing">$99</div>
                                    <p style="color:var(--text-muted);">شهرياً</p>
                                </div>
                            </div>
                        </div>
                    </section>`,
                'faq': `
                    <section class="section" data-component="faq" style="padding:80px 0;">
                        <div class="container" style="max-width:800px;">
                            <h2 data-editable="new_faq_${uid}_title" data-section="faq" style="text-align:center; font-size:36px; margin-bottom:48px;">الأسئلة الشائعة</h2>
                            <div style="display:flex; flex-direction:column; gap:16px;">
                                <details style="background:var(--bg-secondary); padding:20px; border-radius:12px;">
                                    <summary style="cursor:pointer; font-weight:600;" data-editable="new_faq_${uid}_q1" data-section="faq">ما هو السؤال الأول؟</summary>
                                    <p style="margin-top:12px; color:var(--text-secondary);" data-editable="new_faq_${uid}_a1" data-section="faq">هذه هي الإجابة على السؤال الأول.</p>
                                </details>
                                <details style="background:var(--bg-secondary); padding:20px; border-radius:12px;">
                                    <summary style="cursor:pointer; font-weight:600;" data-editable="new_faq_${uid}_q2" data-section="faq">ما هو السؤال الثاني؟</summary>
                                    <p style="margin-top:12px; color:var(--text-secondary);" data-editable="new_faq_${uid}_a2" data-section="faq">هذه هي الإجابة على السؤال الثاني.</p>
                                </details>
                            </div>
                        </div>
                    </section>`,
            };

            if (!templates[type]) {
                console.warn('Landing editor: unknown component type', type);
                return templates['feature-card'];
            }
            return templates[type];
        },

        enableEditingForElement(element) {
            element.querySelectorAll('[data-editable]').forEach(el => {
                el.contentEditable = true;
                if (!el._inputHandler) {
                    const key = el.dataset.editable;
                    el._inputHandler = (e) => {
                        this.changes[key] = {
                            value: e.target.innerHTML,
                            type: 'text',
                            section: el.dataset.section || 'custom',
                        };
                    };
                    el.addEventListener('input', el._inputHandler);
                }
            });

            element.querySelectorAll('[data-editable-image]').forEach(el => {
                el.style.cursor = 'pointer';
                el.onclick = () => this.changeImage(el);
            });
        },

        // ==================== الحفظ التلقائي ====================
        startAutoSave() {
            this.autoSaveInterval = setInterval(() => {
                if (this.hasUnsavedChanges()) this.autoSave();
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
            await this.saveChangesInternal(true);
        },

        async createSnapshot() {
            const token = this.csrfToken();
            if (!token) {
                this.toast('انتهت الجلسة. أعِد تحميل الصفحة.', 'error');
                return;
            }
            try {
                const response = await fetch('/api/landing/content/snapshot', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const data = await response.json();
                this.toast(data.success ? 'تم حفظ نسخة احتياطية ✅' : 'فشل حفظ النسخة الاحتياطية', data.success ? 'success' : 'error');
            } catch (error) {
                console.error('Snapshot Error:', error);
                this.toast('تعذّر حفظ النسخة الاحتياطية', 'error');
            }
        },

        // ==================== إدارة العناصر ====================
        editElement(element) {
            this.selectedElement = element;
            this.showProperties = true;
        },

        duplicateElement(element) {
            const clone = element.cloneNode(true);
            const originalId = element.getAttribute('data-element');
            if (originalId) {
                clone.setAttribute('data-element', originalId + '-copy-' + this.uid());
            }
            element.insertAdjacentElement('afterend', clone);
            this.enableEditingForElement(clone);
            this.markStructuralChange();
            this.toast('تم نسخ العنصر ✅', 'success');
        },

        deleteElement(element) {
            const criticalElements = ['header-logo', 'nav-link-1', 'nav-link-2', 'nav-link-3', 'nav-link-4', 'nav-link-5', 'nav-link-6', 'theme-toggle', 'login-btn', 'register-btn'];
            const elementId = element.getAttribute('data-element');

            if (criticalElements.includes(elementId)) {
                this.toast('لا يمكن حذف عنصر أساسي محمي من الهيدر.', 'error');
                return false;
            }

            element.remove();
            this.markStructuralChange();
            this.toast('تم حذف العنصر', 'info');
        },

        duplicateSection(section) {
            const clone = section.cloneNode(true);
            // مفاتيح جديدة للعناصر المنسوخة
            clone.querySelectorAll('[data-editable]').forEach(el => {
                el.dataset.editable = el.dataset.editable + '_copy_' + this.uid();
            });
            // لا نكرّر أدوات القسم المحقونة
            clone.querySelectorAll('[data-injected-chrome]').forEach(bar => bar.remove());

            section.after(clone);
            this.enableEditingForElement(clone);
            this.injectSectionChrome();
            this.refreshDropZones();
            this.markStructuralChange();
            this.toast('تم نسخ القسم ✅', 'success');
        },

        editSectionProperties(section) {
            this.selectedElement = section;
            this.showProperties = true;
        },

        updateProperty(property, value) {
            if (!this.selectedElement) return;
            const map = { background: 'background', color: 'color', padding: 'padding', fontSize: 'fontSize' };
            if (map[property]) {
                this.selectedElement.style[map[property]] = value;
                this.markStructuralChange();
            }
        },

        openIconPicker(iconElement) {
            this.selectedElement = iconElement;
            this.showProperties = true;
        },

        updateIcon(icon) {
            if (!this.selectedElement) return;
            if (this.selectedElement.dataset.editableIcon !== undefined) {
                this.selectedElement.textContent = icon;
            }
            const key = this.selectedElement.dataset.editableIcon || this.selectedElement.dataset.editable;
            if (key) {
                this.changes[key] = {
                    value: icon,
                    type: 'text',
                    section: this.selectedElement.dataset.section || 'custom',
                };
            }
            this.markStructuralChange();
        },

        closeProperties() {
            this.showProperties = false;
            this.selectedElement = null;
        },

        // ==================== أدوات مساعدة ====================
        markStructuralChange() {
            this.structuralDirty = true;
        },

        hasUnsavedChanges() {
            return this.structuralDirty || Object.keys(this.changes).length > 0;
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },

        uid() {
            return Date.now().toString(36) + (++this._uidCounter).toString(36);
        },

        toast(message, type = 'info') {
            let host = document.getElementById('wahy-toast-host');
            if (!host) {
                host = document.createElement('div');
                host.id = 'wahy-toast-host';
                host.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:100000;display:flex;flex-direction:column;gap:8px;align-items:center;pointer-events:none;';
                document.body.appendChild(host);
            }
            const colors = { success: '#16a34a', error: '#dc2626', info: '#2563eb' };
            const t = document.createElement('div');
            t.textContent = message;
            t.setAttribute('role', 'status');
            t.style.cssText = `background:${colors[type] || colors.info};color:#fff;padding:12px 20px;border-radius:12px;font:600 14px/1.5 'IBM Plex Sans Arabic',system-ui,sans-serif;box-shadow:0 8px 24px rgba(0,0,0,.22);opacity:0;transform:translateY(10px);transition:opacity .25s,transform .25s;max-width:90vw;text-align:center;`;
            host.appendChild(t);
            requestAnimationFrame(() => { t.style.opacity = '1'; t.style.transform = 'translateY(0)'; });
            setTimeout(() => {
                t.style.opacity = '0';
                t.style.transform = 'translateY(10px)';
                setTimeout(() => t.remove(), 300);
            }, 3000);
        },
    };
}
