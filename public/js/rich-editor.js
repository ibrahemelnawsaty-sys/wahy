/**
 * Wahy Rich Editor — محرر نصوص خفيف موحّد لكل النماذج.
 *
 * الاستخدام في الـ Blade:
 *   <div data-rich-editor="lessonContent" data-target="contentHidden" dir="rtl">{!! old('content', $lesson->content) !!}</div>
 *   <textarea name="content" id="contentHidden" hidden>{!! old('content', $lesson->content) !!}</textarea>
 *
 * المتطلبات:
 *   - meta[name=csrf-token] في الـ <head>
 *   - route('editor.upload-image') لرفع الصور
 *
 * خصائص:
 *   - تلوين النص والخلفية (يُكتب عبر inline styles في selection — موثوق في كل المتصفحات الحديثة)
 *   - رفع صورة من جهاز المستخدم (multipart) بدلاً من URL فقط
 *   - تنظيف Paste (إزالة styles مسربة من Word)
 *   - مزامنة تلقائية مع textarea مخفي عند submit النموذج
 */
(function () {
    'use strict';

    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
        document.querySelector('input[name="_token"]')?.value ||
        '';

    const uploadUrl = window.WAHY_EDITOR_UPLOAD_URL || '/editor/upload-image';

    function $(sel, root = document) { return root.querySelector(sel); }

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
        // execCommand لا يزال يعمل في كل المتصفحات الحديثة لمحرر contenteditable.
        // لكن للتلوين نستخدم styleWithCSS=true ليكتب inline styles.
        try {
            document.execCommand('styleWithCSS', false, true);
            document.execCommand(cmd, false, value);
        } catch (_) {
            // تجاهل — fallback عبر Selection API يأتي لاحقاً عند الحاجة
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

    function openLinkPrompt() {
        const url = window.prompt('أدخل رابط URL:', 'https://');
        if (!url || url === 'https://') return;
        const text = window.getSelection().toString();
        if (text) {
            execCmd('createLink', url);
        } else {
            const html = `<a href="${escapeAttr(url)}" target="_blank" rel="noopener noreferrer">${escapeHtml(url)}</a>`;
            execCmd('insertHTML', html);
        }
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

            // عرض placeholder فوري
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
                        placeholder.src = data.url;
                        placeholder.style.opacity = '1';
                        placeholder.removeAttribute('alt');
                        // رفع الصورة لا يُطلق حدث input تلقائياً — نُطلقه يدوياً لتزامن الـtextarea فوراً
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

    function escapeHtml(s) { return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
    function escapeAttr(s) { return escapeHtml(s); }

    function attachToolbarHandlers(toolbar, editor) {
        toolbar.addEventListener('mousedown', (e) => {
            // منع فقدان الـ selection
            if (e.target.matches('button[data-cmd], select[data-cmd]')) {
                e.preventDefault();
            }
        });

        toolbar.querySelectorAll('button[data-cmd]').forEach(btn => {
            btn.addEventListener('click', () => {
                const cmd = btn.dataset.cmd;
                editor.focus();
                if (cmd === 'link') return openLinkPrompt();
                if (cmd === 'image') return uploadAndInsertImage(editor);
                if (cmd === 'clear') return clearFormatting();
                execCmd(cmd);
            });
        });

        toolbar.querySelectorAll('select[data-cmd]').forEach(sel => {
            sel.addEventListener('change', () => {
                const cmd = sel.dataset.cmd;
                if (cmd === 'heading') applyHeading(editor, sel.value);
                sel.value = '';
            });
        });

        toolbar.querySelectorAll('input[type=color][data-cmd]').forEach(inp => {
            inp.addEventListener('input', () => {
                editor.focus();
                execCmd(inp.dataset.cmd, inp.value);
            });
        });
    }

    function attachPasteSanitizer(editor) {
        editor.addEventListener('paste', (e) => {
            const text = (e.clipboardData || window.clipboardData).getData('text/html')
                || (e.clipboardData || window.clipboardData).getData('text/plain');
            if (!text) return;
            e.preventDefault();
            // إزالة style سمين من Word مع الإبقاء على bold/italic/color inline
            const cleaned = String(text)
                .replace(/<\!--[\s\S]*?-->/g, '')
                .replace(/<\/?(o:p|w:[^>]*|m:[^>]*|xml|meta|link|style|script)[^>]*>/gi, '')
                .replace(/\sclass="[^"]*"/gi, '')
                .replace(/\smso-[^:;"]*:[^;"]*;?/gi, '');
            execCmd('insertHTML', cleaned);
        });
    }

    function syncWithHidden(editor, target) {
        const hidden = target ? document.getElementById(target) : null;
        if (!hidden) return;
        // مزامنة مستمرّة: كل تعديل ينعكس فوراً على الحقل المُرسَل (textarea) — كي يصل الوصف
        // دائماً حتى مع الإرسال البرمجيّ (.submit() الذي يتجاوز مستمعي submit) أو اعتراض
        // حدث submit في اللايوت. الاعتماد على submit وحده كان يفقد المحتوى.
        const sync = () => { hidden.value = editor.innerHTML; };
        editor.addEventListener('input', sync);
        editor.addEventListener('blur', sync);
        const form = editor.closest('form');
        if (form) form.addEventListener('submit', sync);
        sync(); // مزامنة أوّليّة
    }

    function ensureStyles() {
        if (document.getElementById('wahy-rte-styles')) return;
        const css = document.createElement('style');
        css.id = 'wahy-rte-styles';
        css.textContent = `
            .wahy-rte-wrapper { border: 2px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fff; }
            .wahy-rte-toolbar { display: flex; flex-wrap: wrap; gap: 4px; padding: 8px 10px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; align-items: center; }
            .wahy-rte-toolbar button[data-cmd], .wahy-rte-toolbar select[data-cmd] { background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 10px; cursor: pointer; font-size: 14px; min-width: 32px; transition: .15s; }
            .wahy-rte-toolbar button[data-cmd]:hover { background: #eff6ff; border-color: #3b82f6; }
            .wahy-rte-toolbar select[data-cmd] { font-size: 13px; }
            .wahy-rte-toolbar .rte-sep { width: 1px; height: 22px; background: #cbd5e1; margin: 0 4px; }
            .wahy-rte-toolbar .rte-color { display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; cursor: pointer; }
            .wahy-rte-toolbar .rte-color input[type=color] { width: 22px; height: 22px; padding: 0; border: 0; background: transparent; cursor: pointer; }
            [data-rich-editor] { min-height: 200px; padding: 14px 18px; outline: none; line-height: 1.8; font-size: 15px; }
            [data-rich-editor] img { max-width: 100%; height: auto; }
            [data-rich-editor]:focus { background: #fffef9; }
            [data-rich-editor] h2 { font-size: 22px; font-weight: 800; margin: 14px 0 8px; }
            [data-rich-editor] h3 { font-size: 18px; font-weight: 700; margin: 12px 0 6px; }
            [data-rich-editor] h4 { font-size: 16px; font-weight: 700; margin: 10px 0 6px; }
            [data-rich-editor] ul, [data-rich-editor] ol { padding-right: 24px; margin: 8px 0; }
        `;
        document.head.appendChild(css);
    }

    function init(el) {
        if (el.dataset.rteReady === '1') return;
        el.dataset.rteReady = '1';
        el.setAttribute('contenteditable', 'true');
        el.setAttribute('spellcheck', 'true');

        ensureStyles();

        const wrapper = document.createElement('div');
        wrapper.className = 'wahy-rte-wrapper';
        const toolbar = buildToolbar();

        // Wrap editor element with our wrapper
        el.parentNode.insertBefore(wrapper, el);
        wrapper.appendChild(toolbar);
        wrapper.appendChild(el);

        attachToolbarHandlers(toolbar, el);
        attachPasteSanitizer(el);
        syncWithHidden(el, el.dataset.target);

        // تعزيز تدريجيّ (progressive enhancement): المحرّر يبدأ مخفياً والـtextarea البديلة ظاهرة
        // كي يستطيع المستخدم الكتابة والحفظ حتى لو لم يُنفَّذ JS. الآن وقد نجحت التهيئة وارتبطت
        // المزامنة، نُظهر المحرّر ونُخفي الـtextarea. يتمّ أخيراً فإن فشل شيء قبله بقيت البديلة صالحة.
        el.hidden = false;
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

    // كشف API للاستخدام البرمجي
    window.WahyRichEditor = { init, initAll };
})();
