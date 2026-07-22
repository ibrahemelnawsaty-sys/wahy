/**
 * Wahy Rich Editor — محرر نصوص خفيف موحّد لكل النماذج.
 *
 * الاستخدام في الـ Blade:
 *   <div data-rich-editor="lessonContent" data-target="contentHidden" dir="rtl" hidden>{!! old('content', $lesson->content) !!}</div>
 *   <textarea name="content" id="contentHidden">{!! old('content', $lesson->content) !!}</textarea>
 *
 * المتطلبات:
 *   - meta[name=csrf-token] في الـ <head>
 *   - route('editor.upload-image') لرفع الصور
 *
 * خصائص:
 *   - تلوين النص والخلفية (مع حفظ/استعادة التحديد كي لا يضيع عند فتح منتقي اللون)
 *   - إضافة رابط عبر نافذة منبثقة احترافية (بدل prompt المتصفّح)
 *   - رفع صورة من جهاز المستخدم (multipart) بدلاً من URL فقط
 *   - تنظيف Paste (إزالة styles مسربة من Word)
 *   - مزامنة مستمرّة مع textarea مُرسَل، وتعزيز تدريجيّ (المحرّر يبدأ مخفياً والـtextarea بديلة)
 */
(function () {
    'use strict';

    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
        document.querySelector('input[name="_token"]')?.value ||
        '';

    const uploadUrl = window.WAHY_EDITOR_UPLOAD_URL || '/editor/upload-image';

    // ===== حفظ/استعادة التحديد =====
    // فتح منتقي الألوان الأصليّ أو النافذة المنبثقة يسحب التركيز من المحرّر فيضيع التحديد،
    // فلا يُطبَّق اللون/الرابط على النصّ المختار. نحفظ المدى قبل ذلك ونستعيده قبل التنفيذ.
    let savedRange = null;
    let activeEditor = null;

    function saveSelection(editor) {
        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            // تأكّد أنّ التحديد داخل هذا المحرّر
            if (editor.contains(range.commonAncestorContainer)) {
                savedRange = range.cloneRange();
                activeEditor = editor;
            }
        }
    }

    function restoreSelection() {
        if (!savedRange || !activeEditor) return false;
        activeEditor.focus();
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(savedRange);
        return true;
    }

    function buildToolbar() {
        const tpl = document.createElement('div');
        tpl.className = 'wahy-rte-toolbar';
        tpl.innerHTML = `
            <button type="button" data-cmd="bold" title="غامق"><b>B</b></button>
            <button type="button" data-cmd="italic" title="مائل"><i>I</i></button>
            <button type="button" data-cmd="underline" title="تسطير"><u>U</u></button>
            <span class="rte-sep"></span>
            <select data-cmd="heading" title="تنسيق الفقرة">
                <option value="">— نمط —</option>
                <option value="h2">عنوان كبير</option>
                <option value="h3">عنوان متوسط</option>
                <option value="h4">عنوان صغير</option>
                <option value="p">فقرة عادية</option>
            </select>
            <span class="rte-sep"></span>
            <label title="لون النص" class="rte-color">
                <span>🎨</span>
                <input type="color" data-cmd="foreColor" value="#1f2937">
            </label>
            <label title="لون الخلفية" class="rte-color">
                <span>🖌️</span>
                <input type="color" data-cmd="hiliteColor" value="#fde68a">
            </label>
            <span class="rte-sep"></span>
            <button type="button" data-cmd="justifyRight" title="يمين">⇶</button>
            <button type="button" data-cmd="justifyCenter" title="وسط">≡</button>
            <button type="button" data-cmd="justifyLeft" title="يسار">⇷</button>
            <span class="rte-sep"></span>
            <button type="button" data-cmd="insertUnorderedList" title="نقاط">•</button>
            <button type="button" data-cmd="insertOrderedList" title="ترقيم">1.</button>
            <span class="rte-sep"></span>
            <button type="button" data-cmd="link" title="رابط">🔗</button>
            <button type="button" data-cmd="image" title="صورة">🖼️</button>
            <button type="button" data-cmd="clear" title="مسح التنسيق">⌫</button>
        `;
        return tpl;
    }

    function execCmd(cmd, value = null) {
        try {
            // styleWithCSS=true يجعل التلوين يُكتب inline style بدل وسوم <font> القديمة.
            document.execCommand('styleWithCSS', false, true);
            const ok = document.execCommand(cmd, false, value);
            // بعض المتصفّحات لا تدعم hiliteColor لخلفية النص → backColor بديلاً.
            if (!ok && cmd === 'hiliteColor') {
                document.execCommand('backColor', false, value);
            }
        } catch (_) {
            if (cmd === 'hiliteColor') {
                try { document.execCommand('backColor', false, value); } catch (__) {}
            }
        }
    }

    function applyHeading(editor, tag) {
        if (!tag) return;
        editor.focus();
        execCmd('formatBlock', tag);
    }

    function clearFormatting() {
        execCmd('removeFormat');
        execCmd('unlink');
    }

    // ===== نافذة الرابط المنبثقة (احترافية، بدل prompt) =====
    let linkModal = null;

    function ensureLinkModal() {
        if (linkModal) return linkModal;
        const overlay = document.createElement('div');
        overlay.className = 'wahy-rte-modal-overlay';
        overlay.innerHTML = `
            <div class="wahy-rte-modal" role="dialog" aria-modal="true" aria-label="إضافة رابط">
                <div class="wahy-rte-modal-head"><span>🔗</span> إضافة رابط</div>
                <label class="wahy-rte-modal-label">النص الظاهر</label>
                <input type="text" class="wahy-rte-modal-input" data-field="text" placeholder="مثال: اضغط هنا">
                <label class="wahy-rte-modal-label">الرابط (URL)</label>
                <input type="url" class="wahy-rte-modal-input" data-field="url" placeholder="https://example.com" dir="ltr">
                <div class="wahy-rte-modal-err" data-field="err"></div>
                <div class="wahy-rte-modal-actions">
                    <button type="button" class="wahy-rte-btn-cancel" data-field="cancel">إلغاء</button>
                    <button type="button" class="wahy-rte-btn-insert" data-field="insert">إدراج الرابط</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        linkModal = overlay;
        return overlay;
    }

    function normalizeUrl(url) {
        url = (url || '').trim();
        if (!url) return '';
        // بريد؟ اتركه كما هو مع mailto عند اللزوم
        if (/^mailto:/i.test(url) || /^tel:/i.test(url)) return url;
        if (/^https?:\/\//i.test(url)) return url;
        if (/^[\w.+-]+@[\w.-]+\.\w+$/.test(url)) return 'mailto:' + url;
        return 'https://' + url.replace(/^\/+/, '');
    }

    function openLinkModal(editor) {
        saveSelection(editor);
        const selectedText = (window.getSelection().toString() || '').trim();

        const overlay = ensureLinkModal();
        const textInput = overlay.querySelector('[data-field="text"]');
        const urlInput = overlay.querySelector('[data-field="url"]');
        const errBox = overlay.querySelector('[data-field="err"]');
        const cancelBtn = overlay.querySelector('[data-field="cancel"]');
        const insertBtn = overlay.querySelector('[data-field="insert"]');

        textInput.value = selectedText;
        urlInput.value = '';
        errBox.textContent = '';
        overlay.classList.add('active');
        setTimeout(() => urlInput.focus(), 40);

        function close() {
            overlay.classList.remove('active');
            cancelBtn.onclick = insertBtn.onclick = overlay.onclick = null;
            urlInput.onkeydown = textInput.onkeydown = null;
        }

        function submit() {
            const url = normalizeUrl(urlInput.value);
            if (!url) { errBox.textContent = 'الرجاء إدخال رابط صحيح'; urlInput.focus(); return; }
            const text = (textInput.value || '').trim() || url;
            const html = `<a href="${escapeAttr(url)}" target="_blank" rel="noopener noreferrer">${escapeHtml(text)}</a>`;
            close();
            restoreSelection();
            execCmd('insertHTML', html);
            editor.dispatchEvent(new Event('input'));
        }

        cancelBtn.onclick = close;
        insertBtn.onclick = submit;
        overlay.onclick = (e) => { if (e.target === overlay) close(); };
        urlInput.onkeydown = (e) => { if (e.key === 'Enter') { e.preventDefault(); submit(); } if (e.key === 'Escape') close(); };
        textInput.onkeydown = (e) => { if (e.key === 'Enter') { e.preventDefault(); urlInput.focus(); } if (e.key === 'Escape') close(); };
    }

    function uploadAndInsertImage(editor) {
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/jpeg,image/png,image/gif,image/webp,image/svg+xml';
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);

        fileInput.addEventListener('change', async () => {
            const file = fileInput.files?.[0];
            fileInput.remove();
            if (!file) return;

            editor.focus();
            const placeholderId = 'rte_img_' + Date.now();
            const placeholderHtml = `<img id="${placeholderId}" alt="جارِ الرفع..." style="max-width:100%;height:auto;border-radius:8px;margin:8px 0;opacity:.4;">`;
            execCmd('insertHTML', placeholderHtml);

            const fd = new FormData();
            fd.append('image', file);
            fd.append('_token', csrfToken);

            try {
                const res = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: fd,
                });
                const data = await res.json();
                const placeholder = document.getElementById(placeholderId);
                if (data.success && data.url) {
                    if (placeholder) {
                        // رابط مطلق: كي يُخزَّن المحتوى بـsrc http(s) يعمل في كل مكان (لا نسبيّ)
                        placeholder.src = new URL(data.url, window.location.origin).href;
                        placeholder.style.opacity = '1';
                        placeholder.removeAttribute('alt');
                        const ed = placeholder.closest('[data-rich-editor]');
                        if (ed) ed.dispatchEvent(new Event('input'));
                    }
                } else {
                    if (placeholder) placeholder.remove();
                    alert(data.message || 'فشل رفع الصورة');
                }
            } catch (e) {
                const placeholder = document.getElementById(placeholderId);
                if (placeholder) placeholder.remove();
                alert('فشل الاتصال بالخادم لرفع الصورة');
            }
        });

        fileInput.click();
    }

    function escapeHtml(s) { return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
    function escapeAttr(s) { return escapeHtml(s); }

    function attachToolbarHandlers(toolbar, editor) {
        toolbar.addEventListener('mousedown', (e) => {
            // منع فقدان الـ selection عند ضغط الأزرار والقوائم (لا ينطبق على منتقي اللون).
            if (e.target.matches('button[data-cmd], select[data-cmd]')) {
                e.preventDefault();
            }
        });

        toolbar.querySelectorAll('button[data-cmd]').forEach(btn => {
            btn.addEventListener('click', () => {
                const cmd = btn.dataset.cmd;
                editor.focus();
                if (cmd === 'link') return openLinkModal(editor);
                if (cmd === 'image') return uploadAndInsertImage(editor);
                if (cmd === 'clear') return clearFormatting();
                execCmd(cmd);
                editor.dispatchEvent(new Event('input'));
            });
        });

        toolbar.querySelectorAll('select[data-cmd]').forEach(sel => {
            sel.addEventListener('change', () => {
                const cmd = sel.dataset.cmd;
                if (cmd === 'heading') applyHeading(editor, sel.value);
                sel.value = '';
                editor.dispatchEvent(new Event('input'));
            });
        });

        toolbar.querySelectorAll('input[type=color][data-cmd]').forEach(inp => {
            // احفظ التحديد قبل أن يسرقه منتقي اللون الأصليّ (mousedown يسبق فتح المنتقي).
            inp.addEventListener('mousedown', () => saveSelection(editor));
            inp.addEventListener('focus', () => saveSelection(editor));
            inp.addEventListener('input', () => {
                restoreSelection();               // أعِد التحديد المحفوظ ثم لوّنه
                execCmd(inp.dataset.cmd, inp.value);
                saveSelection(editor);            // احفظ الجديد لتلوينات متتابعة
                editor.dispatchEvent(new Event('input'));
            });
        });

        // حدّث التحديد المحفوظ أثناء تحرير المستخدم داخل المحرّر
        editor.addEventListener('keyup', () => saveSelection(editor));
        editor.addEventListener('mouseup', () => saveSelection(editor));
    }

    function attachPasteSanitizer(editor) {
        editor.addEventListener('paste', (e) => {
            const text = (e.clipboardData || window.clipboardData).getData('text/html')
                || (e.clipboardData || window.clipboardData).getData('text/plain');
            if (!text) return;
            e.preventDefault();
            const cleaned = String(text)
                .replace(/<\!--[\s\S]*?-->/g, '')
                .replace(/<\/?(o:p|w:[^>]*|m:[^>]*|xml|meta|link|style|script)[^>]*>/gi, '')
                .replace(/\sclass="[^"]*"/gi, '')
                .replace(/\smso-[^:;"]*:[^;"]*;?/gi, '');
            execCmd('insertHTML', cleaned);
            editor.dispatchEvent(new Event('input'));
        });
    }

    function syncWithHidden(editor, target) {
        const hidden = target ? document.getElementById(target) : null;
        if (!hidden) return;
        // مزامنة مستمرّة: كل تعديل ينعكس فوراً على الحقل المُرسَل (textarea) — كي يصل الوصف
        // دائماً حتى مع الإرسال البرمجيّ (.submit()) أو اعتراض حدث submit في اللايوت.
        const sync = () => { hidden.value = editor.innerHTML; };
        editor.addEventListener('input', sync);
        editor.addEventListener('blur', sync);
        const form = editor.closest('form');
        if (form) form.addEventListener('submit', sync);
        sync();
    }

    function ensureStyles() {
        if (document.getElementById('wahy-rte-styles')) return;
        const css = document.createElement('style');
        css.id = 'wahy-rte-styles';
        css.textContent = `
            .wahy-rte-wrapper { border: 2px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fff; }
            .wahy-rte-toolbar { display: flex; flex-wrap: wrap; gap: 4px; padding: 8px 10px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; align-items: center; }
            .wahy-rte-toolbar button[data-cmd], .wahy-rte-toolbar select[data-cmd] { background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 10px; cursor: pointer; font-size: 14px; min-width: 32px; transition: .15s; color: #1f2937; }
            .wahy-rte-toolbar button[data-cmd]:hover { background: #eff6ff; border-color: #3b82f6; }
            .wahy-rte-toolbar select[data-cmd] { font-size: 13px; }
            .wahy-rte-toolbar .rte-sep { width: 1px; height: 22px; background: #cbd5e1; margin: 0 4px; }
            .wahy-rte-toolbar .rte-color { display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; cursor: pointer; }
            .wahy-rte-toolbar .rte-color input[type=color] { width: 22px; height: 22px; padding: 0; border: 0; background: transparent; cursor: pointer; }
            [data-rich-editor] { min-height: 200px; padding: 14px 18px; outline: none; line-height: 1.8; font-size: 15px; background: #fff; color: #1f2937; }
            [data-rich-editor] img { max-width: 100%; height: auto; }
            [data-rich-editor]:focus { background: #fffef9; }
            [data-rich-editor] h2 { font-size: 22px; font-weight: 800; margin: 14px 0 8px; }
            [data-rich-editor] h3 { font-size: 18px; font-weight: 700; margin: 12px 0 6px; }
            [data-rich-editor] h4 { font-size: 16px; font-weight: 700; margin: 10px 0 6px; }
            [data-rich-editor] ul, [data-rich-editor] ol { padding-right: 24px; margin: 8px 0; }
            [data-rich-editor] a { color: #2563eb; text-decoration: underline; }

            /* نافذة الرابط المنبثقة */
            .wahy-rte-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.55); backdrop-filter: blur(3px); z-index: 100000; display: none; align-items: center; justify-content: center; padding: 20px; }
            .wahy-rte-modal-overlay.active { display: flex; }
            .wahy-rte-modal { width: 100%; max-width: 440px; background: #fff; border-radius: 18px; padding: 24px; box-shadow: 0 24px 70px rgba(0,0,0,0.35); font-family: inherit; animation: wahyRteModalIn .18s ease-out; }
            @keyframes wahyRteModalIn { from { opacity: 0; transform: translateY(14px) scale(.97); } to { opacity: 1; transform: none; } }
            .wahy-rte-modal-head { font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
            .wahy-rte-modal-label { display: block; font-size: 13px; font-weight: 700; color: #475569; margin: 12px 0 6px; }
            .wahy-rte-modal-input { width: 100%; box-sizing: border-box; padding: 11px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; font-family: inherit; outline: none; transition: border-color .15s; }
            .wahy-rte-modal-input:focus { border-color: #667eea; }
            .wahy-rte-modal-err { color: #dc2626; font-size: 13px; font-weight: 600; min-height: 18px; margin-top: 8px; }
            .wahy-rte-modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px; }
            .wahy-rte-modal-actions button { padding: 10px 22px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; font-family: inherit; border: none; }
            .wahy-rte-btn-cancel { background: #f1f5f9; color: #475569; }
            .wahy-rte-btn-cancel:hover { background: #e2e8f0; }
            .wahy-rte-btn-insert { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; }
            .wahy-rte-btn-insert:hover { filter: brightness(1.06); }
        `;
        document.head.appendChild(css);
    }

    function init(el) {
        if (el.dataset.rteReady === '1') return;

        ensureStyles();
        el.setAttribute('contenteditable', 'true');
        el.setAttribute('spellcheck', 'true');

        const wrapper = document.createElement('div');
        wrapper.className = 'wahy-rte-wrapper';
        const toolbar = buildToolbar();

        el.parentNode.insertBefore(wrapper, el);
        wrapper.appendChild(toolbar);
        wrapper.appendChild(el);

        attachToolbarHandlers(toolbar, el);
        attachPasteSanitizer(el);
        syncWithHidden(el, el.dataset.target);

        el.dataset.rteReady = '1';

        // تعزيز تدريجيّ: المحرّر يبدأ مخفياً والـtextarea البديلة ظاهرة (تعمل بلا JS).
        // بعد نجاح التهيئة: أظهِر المحرّر وأخفِ البديلة. (يجري أخيراً فإن فشل شيء بقيت البديلة صالحة.)
        el.hidden = false;
        el.removeAttribute('hidden');
        const hiddenTa = el.dataset.target ? document.getElementById(el.dataset.target) : null;
        if (hiddenTa) { hiddenTa.hidden = true; hiddenTa.style.display = 'none'; }
    }

    function initAll() {
        document.querySelectorAll('[data-rich-editor]').forEach(init);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    window.WahyRichEditor = { init, initAll };
})();
