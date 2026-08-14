/*
 * محرّر الصفحات الخفيف (المرحلة 2 — بلا React). خطوتان واضحتان: (1) بيانات الصفحة، (2) المحتوى
 * مع **معاينة حيّة دائمة** (iframe مُصيَّر خادميّاً يتحدّث تلقائيّاً) — WYSIWYG آمن.
 * يحرّر ثلاث مناطق (الجسم/الهيدر/الفوتر) ويحفظ عبر نقاط JSON الآمنة. الرندرة النهائيّة دائماً Blade خادميّاً.
 */
(function () {
    'use strict';
    var B = window.PB_BOOT;
    if (!B) return;

    var PAGE_DEFAULTS = {
        id: null, title: '', slug: '', locale: 'ar', status: 'draft',
        meta_title: '', meta_description: '', blocks: [],
        header_part_id: null, footer_part_id: null, hide_header: false, hide_footer: false,
        use_site_header: false, use_site_footer: false, has_unpublished: false,
    };

    var state = {
        page: Object.assign({}, PAGE_DEFAULTS, B.page || {}),
        step: 1,
        region: 'body',
        parts: { header: null, footer: null },
        selected: null,
        isLive: !!B.isLive,
        preview: { device: 'desktop', timer: null },
    };
    if (!Array.isArray(state.page.blocks)) state.page.blocks = [];

    /* ============ سجلّ التراجع/الإعادة (Undo/Redo) ============ */
    var hist = { stack: [], ptr: -1, timer: null };
    function snap() {
        return JSON.stringify({
            b: state.page.blocks,
            h: state.parts.header ? state.parts.header.blocks : null,
            f: state.parts.footer ? state.parts.footer.blocks : null,
        });
    }
    function pushHistory() {
        var s = snap();
        if (hist.ptr >= 0 && hist.stack[hist.ptr] === s) return; // لا تغيير ⟶ تجاهل (يمنع قيود التحديد)
        hist.stack = hist.stack.slice(0, hist.ptr + 1);
        hist.stack.push(s);
        if (hist.stack.length > 80) hist.stack.shift();
        hist.ptr = hist.stack.length - 1;
        updateHistBtns();
    }
    function scheduleHistory() { clearTimeout(hist.timer); hist.timer = setTimeout(pushHistory, 500); }
    function applyHistory(s) {
        var d = JSON.parse(s);
        state.page.blocks = d.b || [];
        if (state.parts.header) state.parts.header.blocks = d.h || [];
        if (state.parts.footer) state.parts.footer.blocks = d.f || [];
        state.selected = null; renderAll();
    }
    function undo() { clearTimeout(hist.timer); if (hist.ptr > 0) { hist.ptr--; applyHistory(hist.stack[hist.ptr]); updateHistBtns(); toast('↶ تراجع'); } }
    function redo() { clearTimeout(hist.timer); if (hist.ptr < hist.stack.length - 1) { hist.ptr++; applyHistory(hist.stack[hist.ptr]); updateHistBtns(); toast('↷ إعادة'); } }
    function updateHistBtns() {
        var u = $('pbUndo'), r = $('pbRedo');
        if (u) u.disabled = hist.ptr <= 0;
        if (r) r.disabled = hist.ptr >= hist.stack.length - 1;
    }

    var $ = function (id) { return document.getElementById(id); };
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
    var uid = 0;

    /* ============ الوصول لكتل المنطقة الحاليّة ============ */
    function rootArray() {
        if (state.region === 'body') return state.page.blocks;
        var part = state.parts[state.region];
        return part ? part.blocks : [];
    }
    function containerAndIndex(path) {
        var arr = rootArray();
        for (var k = 0; k < path.length - 1; k++) {
            var b = arr[path[k]];
            if (!b.children) b.children = [];
            arr = b.children;
        }
        return [arr, path[path.length - 1]];
    }
    function blockAt(path) {
        if (!path || !path.length) return null;
        var ci = containerAndIndex(path); return ci[0][ci[1]];
    }
    function samePath(a, b) { return a && b && a.join('.') === b.join('.'); }

    /* ============ عمليّات الكتل ============ */
    function newBlock(type) {
        var d = (B.defaults && B.defaults[type]) || {};
        var blk = { type: type, v: 1, props: JSON.parse(JSON.stringify(d)) };
        if (B.schema[type] && B.schema[type].children) {
            // الأعمدة تأتي بعمودين جاهزين كي تظهر فوراً ويسهل إضافة المزيد
            blk.children = (type === 'columns') ? [
                { type: 'richtext', v: 1, props: { html: '<p>العمود الأوّل — اكتب هنا…</p>' } },
                { type: 'richtext', v: 1, props: { html: '<p>العمود الثاني — اكتب هنا…</p>' } },
            ] : [];
        }
        return blk;
    }
    function addBlock(type, containerPath) {
        var arr;
        if (containerPath) { var b = blockAt(containerPath); b.children = b.children || []; arr = b.children; }
        else arr = rootArray();
        arr.push(newBlock(type));
        state.selected = (containerPath || []).concat([arr.length - 1]);
        renderAll();
    }
    function duplicateBlock(path) {
        var ci = containerAndIndex(path), arr = ci[0], i = ci[1];
        arr.splice(i + 1, 0, JSON.parse(JSON.stringify(arr[i])));
        state.selected = path.slice(0, -1).concat([i + 1]);
        renderAll();
    }
    function moveBlock(path, dir) {
        var ci = containerAndIndex(path), arr = ci[0], i = ci[1], j = i + dir;
        if (j < 0 || j >= arr.length) return;
        var t = arr[i]; arr[i] = arr[j]; arr[j] = t;
        if (samePath(state.selected, path)) state.selected = path.slice(0, -1).concat([j]);
        renderAll();
    }
    function deleteBlock(path) {
        var ci = containerAndIndex(path); ci[0].splice(ci[1], 1);
        if (state.selected && state.selected.join('.').indexOf(path.join('.')) === 0) state.selected = null;
        renderAll();
    }
    // سحب وإفلات: إعادة ترتيب داخل نفس الحاوية (نفس الأب)
    var dragState = { path: null };
    function clearDropIndicators() {
        document.querySelectorAll('.pb-drop-before,.pb-drop-after').forEach(function (c) { c.classList.remove('pb-drop-before', 'pb-drop-after'); });
    }
    function moveBlockTo(srcPath, targetPath, after) {
        var ci = containerAndIndex(srcPath), arr = ci[0], from = ci[1];
        var to = targetPath[targetPath.length - 1];
        var item = arr.splice(from, 1)[0];
        if (from < to) to--;
        var at = Math.max(0, Math.min(arr.length, after ? to + 1 : to));
        arr.splice(at, 0, item);
        state.selected = srcPath.slice(0, -1).concat([at]);
        renderAll();
    }

    /* ============ لوحة الكتل (بوصف مختصر) ============ */
    function renderPalette() {
        var host = $('pbPalette'); host.innerHTML = '';
        var cats = {};
        Object.keys(B.schema).forEach(function (type) {
            var cat = B.schema[type].category || 'عامّ';
            (cats[cat] = cats[cat] || []).push(type);
        });
        Object.keys(cats).forEach(function (cat) {
            var h = document.createElement('div'); h.className = 'pb-pal-cat'; h.textContent = cat; host.appendChild(h);
            cats[cat].forEach(function (type) {
                var s = B.schema[type];
                var btn = document.createElement('button'); btn.className = 'pb-add-btn';
                var top = '<div class="pb-add-top"><span class="pb-emoji">' + esc(s.icon || '▪') + '</span>' + esc(s.label || type) + '</div>';
                var desc = s.desc ? '<div class="pb-add-desc">' + esc(s.desc) + '</div>' : '';
                btn.innerHTML = top + desc;
                btn.onclick = function () { addBlock(type, null); };
                host.appendChild(btn);
            });
        });
    }

    /* ============ بنية الصفحة (بطاقات) ============ */
    function summaryOf(block) {
        var p = block.props || {};
        var s = p.title || p.heading || p.text || p.html || p.alt || p.caption || p.quote || '';
        return String(s).replace(/<[^>]*>/g, '').slice(0, 60);
    }
    function renderList(arr, basePath, host) {
        arr.forEach(function (block, i) {
            var path = basePath.concat([i]);
            var s = B.schema[block.type] || { icon: '▪', label: block.type };
            var card = document.createElement('div');
            card.className = 'pb-card' + (samePath(state.selected, path) ? ' is-selected' : '');
            card.innerHTML =
                '<span class="pb-card-grip" title="اسحب لإعادة الترتيب">⋮⋮</span>' +
                '<span class="pb-emoji">' + esc(s.icon || '▪') + '</span>' +
                '<div class="pb-card-main"><div class="pb-card-type">' + esc(s.label || block.type) + '</div>' +
                '<div class="pb-card-sum">' + esc(summaryOf(block)) + '</div></div>' +
                '<div class="pb-card-ops">' +
                '<button class="pb-icon-btn" data-op="up" title="أعلى">▲</button>' +
                '<button class="pb-icon-btn" data-op="down" title="أسفل">▼</button>' +
                '<button class="pb-icon-btn" data-op="dup" title="استنساخ">⧉</button>' +
                '<button class="pb-icon-btn danger" data-op="del" title="حذف">🗑</button></div>';
            card.addEventListener('click', function (e) {
                var op = e.target.getAttribute && e.target.getAttribute('data-op');
                if (op === 'up') { e.stopPropagation(); moveBlock(path, -1); }
                else if (op === 'down') { e.stopPropagation(); moveBlock(path, 1); }
                else if (op === 'dup') { e.stopPropagation(); duplicateBlock(path); }
                else if (op === 'del') { e.stopPropagation(); deleteBlock(path); }
                else { state.selected = path; renderAll(); }
            });
            // سحب وإفلات (نفس الحاوية)
            card.setAttribute('draggable', 'true');
            card.addEventListener('dragstart', function (e) { dragState.path = path; card.classList.add('pb-dragging'); e.dataTransfer.effectAllowed = 'move'; try { e.dataTransfer.setData('text/plain', 'b'); } catch (_) {} });
            card.addEventListener('dragend', function () { card.classList.remove('pb-dragging'); clearDropIndicators(); dragState.path = null; });
            card.addEventListener('dragover', function (e) {
                if (!dragState.path || dragState.path.slice(0, -1).join('.') !== path.slice(0, -1).join('.')) return;
                e.preventDefault(); clearDropIndicators();
                var rect = card.getBoundingClientRect(); card._after = (e.clientY - rect.top) > rect.height / 2;
                card.classList.add(card._after ? 'pb-drop-after' : 'pb-drop-before');
            });
            card.addEventListener('drop', function (e) {
                if (!dragState.path || dragState.path.slice(0, -1).join('.') !== path.slice(0, -1).join('.')) return;
                e.preventDefault(); moveBlockTo(dragState.path, path, !!card._after);
            });
            host.appendChild(card);

            if (s.children) {
                var wrap = document.createElement('div'); wrap.className = 'pb-children';
                renderList(block.children || [], path, wrap);
                var add = document.createElement('button'); add.className = 'pb-rep-add'; add.textContent = '＋ كتلة داخل الأعمدة';
                add.onclick = function () { openInserter(function (t) { addBlock(t, path); }, { noContainers: true }); };
                wrap.appendChild(add); host.appendChild(wrap);
            }
        });
    }
    function renderCanvas() {
        var host = $('pbCanvas'); host.innerHTML = '';
        var arr = rootArray();
        if (!arr.length) { host.innerHTML = '<div class="pb-canvas-empty">لا كتل بعد — أضِف كتلة من «أضف كتلة».</div>'; return; }
        renderList(arr, [], host);
    }

    /* ============ مُنتقي الكتل ============ */
    function openInserter(cb, opts) {
        opts = opts || {};
        openInserter._cb = cb;
        var grid = $('pbInserterGrid'), search = $('pbInserterSearch');
        search.value = '';
        function draw(filter) {
            grid.innerHTML = '';
            var cats = {};
            Object.keys(B.schema).forEach(function (type) {
                var s = B.schema[type];
                if (opts.noContainers && s.children) return;
                var label = s.label || type;
                if (filter && (label + ' ' + type).toLowerCase().indexOf(filter) === -1) return;
                var cat = s.category || 'عامّ';
                (cats[cat] = cats[cat] || []).push(type);
            });
            var names = Object.keys(cats);
            if (!names.length) { grid.innerHTML = '<p class="pb-hint">لا نتائج.</p>'; return; }
            names.forEach(function (cat) {
                var h = document.createElement('div'); h.className = 'pb-ins-cat'; h.textContent = cat; grid.appendChild(h);
                cats[cat].forEach(function (type) {
                    var s = B.schema[type];
                    var btn = document.createElement('button'); btn.className = 'pb-ins-btn';
                    btn.innerHTML = '<span class="pb-emoji">' + esc(s.icon || '▪') + '</span>' + esc(s.label || type);
                    btn.onclick = function () { $('pbInserterModal').hidden = true; if (openInserter._cb) openInserter._cb(type); };
                    grid.appendChild(btn);
                });
            });
        }
        search.oninput = function () { draw(search.value.trim().toLowerCase()); };
        draw('');
        $('pbInserterModal').hidden = false;
        setTimeout(function () { search.focus(); }, 30);
    }

    /* ============ الأنماط الجاهزة + أنماط المستخدم ============ */
    function patternCard(label, icon, cat, onClick, onDelete) {
        var card = document.createElement('button'); card.type = 'button'; card.className = 'pb-pattern-card';
        var html = '<span class="pb-pattern-icon">' + esc(icon || '🧩') + '</span><div class="pb-pattern-label">' + esc(label) + '</div>';
        if (cat) html += '<div class="pb-pattern-cat">' + esc(cat) + '</div>';
        card.innerHTML = html; card.onclick = onClick;
        if (onDelete) {
            var del = document.createElement('span'); del.className = 'pb-pat-del'; del.textContent = '🗑'; del.title = 'حذف';
            del.onclick = function (e) { e.stopPropagation(); onDelete(); };
            card.appendChild(del);
        }
        return card;
    }
    function openPatterns() {
        var grid = $('pbPatternsGrid'); grid.innerHTML = '';
        (B.patterns || []).forEach(function (pat) {
            grid.appendChild(patternCard(pat.label, pat.icon, pat.category, function () { insertPattern(pat); $('pbPatternsModal').hidden = true; }));
        });
        var ups = B.userPatterns || [];
        if (ups.length) {
            var h = document.createElement('div'); h.className = 'pb-ins-cat'; h.textContent = '💾 أنماطك المحفوظة'; grid.appendChild(h);
            ups.forEach(function (up) {
                grid.appendChild(patternCard(up.name, '💾', '', function () { insertUserPattern(up.id); }, function () { deleteUserPattern(up.id); }));
            });
        }
        $('pbPatternsModal').hidden = false;
    }
    function insertBlocks(blocks) {
        var arr = rootArray(); var start = arr.length;
        (blocks || []).forEach(function (b) { arr.push(b); });
        if ((blocks || []).length) state.selected = [start];
        renderAll();
    }
    function insertPattern(pat) {
        insertBlocks(JSON.parse(JSON.stringify(pat.blocks || [])));
        toast('أُدرِج «' + (pat.label || 'النمط') + '» — عدّل محتواه من «بنية الصفحة».');
    }
    function insertUserPattern(id) {
        api(B.urls.userPatternBase + '/' + id, 'GET').then(function (res) {
            if (!res.ok) { toast('تعذّر جلب النمط.', true); return; }
            insertBlocks(JSON.parse(JSON.stringify((res.data.pattern && res.data.pattern.blocks) || [])));
            $('pbPatternsModal').hidden = true; toast('أُدرِج نمطك المحفوظ.');
        });
    }
    function saveAsPattern() {
        var blocks = rootArray();
        if (!blocks.length) { toast('لا كتل لحفظها — أضِف كتلاً أوّلاً.', true); return; }
        var name = prompt('اسم النمط (لإعادة استخدامه لاحقاً):');
        if (!name) return;
        api(B.urls.userPatternBase, 'POST', { name: name, blocks: blocks }).then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر الحفظ.', true); return; }
            B.userPatterns = (B.userPatterns || []).concat([{ id: res.data.pattern.id, name: res.data.pattern.name }]);
            toast('حُفِظ النمط «' + name + '» — تجده في «🧩 أنماط جاهزة».');
        });
    }
    function deleteUserPattern(id) {
        if (!confirm('حذف هذا النمط المحفوظ؟')) return;
        api(B.urls.userPatternBase + '/' + id, 'DELETE').then(function (res) {
            if (!res.ok) { toast('تعذّر الحذف.', true); return; }
            B.userPatterns = (B.userPatterns || []).filter(function (p) { return p.id !== id; });
            openPatterns();
        });
    }

    /* ============ المفتّش ============ */
    function fieldControl(field, value, onChange) {
        var wrap = document.createElement('div'); wrap.className = 'pb-field';
        var lab = document.createElement('label'); lab.textContent = field.label; wrap.appendChild(lab);
        if (field.type === 'richtext') {
            var rid = 'pbrte' + (++uid);
            var ta = document.createElement('textarea'); ta.id = rid; ta.rows = 6; ta.value = value || '';
            ta.oninput = function () { onChange(ta.value); };
            var ed = document.createElement('div');
            ed.setAttribute('data-rich-editor', rid + '_e'); ed.setAttribute('data-target', rid);
            ed.setAttribute('dir', 'rtl'); ed.setAttribute('hidden', 'hidden'); ed.innerHTML = value || '';
            var syncRich = function () { onChange(ed.innerHTML); };
            ed.addEventListener('input', syncRich); ed.addEventListener('blur', syncRich);
            wrap.appendChild(ed); wrap.appendChild(ta);
            setTimeout(function () { if (window.WahyRichEditor) window.WahyRichEditor.init(ed); }, 0);
            return wrap;
        }
        var el;
        if (field.type === 'textarea') {
            el = document.createElement('textarea'); el.rows = 3; el.value = value || '';
            el.oninput = function () { onChange(el.value); };
        } else if (field.type === 'select') {
            el = document.createElement('select');
            Object.keys(field.options).forEach(function (k) {
                var o = document.createElement('option'); o.value = k; o.textContent = field.options[k];
                if (k === value) o.selected = true; el.appendChild(o);
            });
            el.onchange = function () { onChange(el.value); };
        } else if (field.type === 'number') {
            el = document.createElement('input'); el.type = 'number';
            if (field.min != null) el.min = field.min; if (field.max != null) el.max = field.max;
            el.value = value == null ? '' : value;
            el.oninput = function () { onChange(el.value === '' ? null : parseInt(el.value, 10)); };
        } else if (field.type === 'media') {
            var row = document.createElement('div'); row.className = 'pb-media-field';
            var thumb = document.createElement('img'); thumb.className = 'pb-thumb';
            var showThumb = function (url) { if (url) { thumb.src = url; thumb.style.display = ''; } else { thumb.style.display = 'none'; } };
            showThumb(/^https?:\/\//.test(value || '') ? value : '');
            var inp = document.createElement('input'); inp.type = 'text'; inp.value = value || ''; inp.placeholder = 'مسار الصورة';
            inp.oninput = function () { onChange(inp.value); };
            var pick = document.createElement('button'); pick.type = 'button'; pick.className = 'btn btn-sm btn-outline-primary'; pick.textContent = 'اختر';
            pick.onclick = function () { openMedia(function (asset) { inp.value = asset.path; showThumb(asset.url); onChange(asset.path); }); };
            row.appendChild(thumb); row.appendChild(inp); row.appendChild(pick);
            wrap.appendChild(row); return wrap;
        } else if (field.type === 'color') {
            el = document.createElement('input'); el.type = 'color';
            el.value = /^#[0-9a-fA-F]{6}$/.test(value || '') ? value : '#000000';
            el.oninput = function () { onChange(el.value); };
        } else if (field.type === 'toggle') {
            el = document.createElement('input'); el.type = 'checkbox'; el.checked = !!value;
            el.onchange = function () { onChange(el.checked); };
            wrap.classList.add('pb-field-toggle');
        } else {
            el = document.createElement('input'); el.type = 'text'; el.value = value || '';
            el.oninput = function () { onChange(el.value); };
        }
        wrap.appendChild(el); return wrap;
    }
    function repeaterControl(field, block) {
        var wrap = document.createElement('div'); wrap.className = 'pb-field';
        var lab = document.createElement('label'); lab.textContent = field.label; wrap.appendChild(lab);
        block.props[field.key] = Array.isArray(block.props[field.key]) ? block.props[field.key] : [];
        var items = block.props[field.key];
        items.forEach(function (item, idx) {
            var box = document.createElement('div'); box.className = 'pb-rep-item';
            field.fields.forEach(function (sub) {
                box.appendChild(fieldControl(sub, item[sub.key], function (v) { item[sub.key] = v; renderCanvas(); schedulePreview(); }));
            });
            var del = document.createElement('button'); del.className = 'pb-rep-del'; del.textContent = 'حذف العنصر';
            del.onclick = function () { items.splice(idx, 1); renderInspector(); renderCanvas(); schedulePreview(); };
            box.appendChild(del); wrap.appendChild(box);
        });
        var add = document.createElement('button'); add.className = 'pb-rep-add'; add.textContent = '＋ عنصر';
        add.onclick = function () { items.push({}); renderInspector(); };
        wrap.appendChild(add); return wrap;
    }
    function styleControls(block) {
        block.props._style = (block.props._style && typeof block.props._style === 'object') ? block.props._style : {};
        var st = block.props._style;
        var wrap = document.createElement('div'); wrap.className = 'pb-style-sec';
        var h = document.createElement('div'); h.className = 'pb-style-title'; h.textContent = '🎨 تصميم الكتلة'; wrap.appendChild(h);
        function on(key, v) { if (v === '' || v == null) delete st[key]; else st[key] = v; renderCanvas(); schedulePreview(); }
        wrap.appendChild(fieldControl({ label: 'لون الخلفيّة', type: 'color' }, st.bg, function (v) { on('bg', v); }));
        wrap.appendChild(fieldControl({ label: 'لون النصّ', type: 'color' }, st.color, function (v) { on('color', v); }));
        wrap.appendChild(fieldControl({ label: 'المحاذاة', type: 'select', options: { '': '—', 'start': 'بداية', 'center': 'وسط', 'end': 'نهاية' } }, st.align || '', function (v) { on('align', v); }));
        wrap.appendChild(fieldControl({ label: 'حشو علويّ (px)', type: 'number', min: 0, max: 200 }, st.pt, function (v) { on('pt', v); }));
        wrap.appendChild(fieldControl({ label: 'حشو سفليّ (px)', type: 'number', min: 0, max: 200 }, st.pb, function (v) { on('pb', v); }));
        wrap.appendChild(fieldControl({ label: 'أقصى عرض (px)', type: 'number', min: 0, max: 1600 }, st.maxw, function (v) { on('maxw', v); }));
        var vh = document.createElement('div'); vh.className = 'pb-style-title'; vh.textContent = '📱 الإظهار حسب الجهاز'; wrap.appendChild(vh);
        wrap.appendChild(fieldControl({ label: 'إخفاء على الجوّال', type: 'toggle' }, st.hide_mobile, function (v) { on('hide_mobile', v || undefined); }));
        wrap.appendChild(fieldControl({ label: 'إخفاء على اللوحيّ', type: 'toggle' }, st.hide_tablet, function (v) { on('hide_tablet', v || undefined); }));
        wrap.appendChild(fieldControl({ label: 'إخفاء على سطح المكتب', type: 'toggle' }, st.hide_desktop, function (v) { on('hide_desktop', v || undefined); }));
        var clr = document.createElement('button'); clr.className = 'pb-rep-del'; clr.textContent = 'مسح التنسيق';
        clr.onclick = function () { block.props._style = {}; renderInspector(); renderCanvas(); schedulePreview(); };
        wrap.appendChild(clr); return wrap;
    }
    function renderInspector() {
        var host = $('pbInspector'); host.innerHTML = '';
        var block = blockAt(state.selected);
        if (!block) { host.innerHTML = '<p class="pb-hint">اختر كتلةً من «بنية الصفحة» لتحرير خصائصها.</p>'; return; }
        var s = B.schema[block.type];
        if (!s) { host.innerHTML = '<p class="pb-hint">نوع غير معروف.</p>'; return; }
        block.props = block.props || {};
        (s.fields || []).forEach(function (field) {
            if (field.type === 'repeater') host.appendChild(repeaterControl(field, block));
            else host.appendChild(fieldControl(field, block.props[field.key], function (v) { block.props[field.key] = v; renderCanvas(); schedulePreview(); }));
        });
        host.appendChild(styleControls(block));
    }

    /* ============ إعدادات الصفحة (الخطوة 1) ============ */
    function partSelectValue(kind) {
        var idKey = kind === 'header' ? 'header_part_id' : 'footer_part_id';
        var hideKey = kind === 'header' ? 'hide_header' : 'hide_footer';
        var siteKey = kind === 'header' ? 'use_site_header' : 'use_site_footer';
        if (state.page[siteKey]) return '__site__';
        if (state.page[hideKey]) return '__none__';
        return state.page[idKey] ? String(state.page[idKey]) : '';
    }
    function renderPartSelect(kind) {
        var sel = kind === 'header' ? $('pbHeaderPart') : $('pbFooterPart');
        var list = (B.parts && B.parts[kind]) || [];
        var word = kind === 'header' ? 'هيدر' : 'فوتر';
        sel.innerHTML = '';
        function opt(v, label) { var o = document.createElement('option'); o.value = v; o.textContent = label; sel.appendChild(o); }
        opt('', 'الافتراضيّ العامّ');
        opt('__site__', word + ' الموقع الرئيسيّ (يُعدَّل من محتوى الرئيسية)');
        opt('__none__', 'بلا ' + word);
        list.forEach(function (p) { opt(String(p.id), p.name + (p.is_active ? ' — الافتراضيّ' : '')); });
        sel.value = partSelectValue(kind);
        sel.onchange = function () {
            var idKey = kind === 'header' ? 'header_part_id' : 'footer_part_id';
            var hideKey = kind === 'header' ? 'hide_header' : 'hide_footer';
            var siteKey = kind === 'header' ? 'use_site_header' : 'use_site_footer';
            var v = sel.value;
            state.page[siteKey] = false; state.page[hideKey] = false; state.page[idKey] = null;
            if (v === '__site__') state.page[siteKey] = true;
            else if (v === '__none__') state.page[hideKey] = true;
            else if (v !== '') state.page[idKey] = parseInt(v, 10);
            if (state.region === kind && v !== '__site__') { state.parts[kind] = null; switchRegion(kind); }
            else schedulePreview();
        };
    }
    function renderPageSettings() {
        $('pbTitle').value = state.page.title || '';
        $('pbSlug').value = state.page.slug || '';
        $('pbLocale').value = state.page.locale || 'ar';
        $('pbMetaTitle').value = state.page.meta_title || '';
        $('pbMetaDescription').value = state.page.meta_description || '';
        renderPartSelect('header'); renderPartSelect('footer');
    }
    function renderStatus() {
        var label = state.page.status === 'published' ? 'منشورة' : 'مسودّة';
        if (state.page.status === 'published' && state.page.has_unpublished) label += ' • ✎ تعديلات غير منشورة';
        $('pbStatusPill').textContent = label + (state.page.id ? '' : ' (غير محفوظة)');
        var gl = $('pbGoLive');
        gl.textContent = state.isLive ? '● إيقاف البثّ' : '○ بثّ مباشر';
        gl.disabled = !state.page.id || state.page.status !== 'published';
    }
    function renderAll() { renderCanvas(); renderInspector(); renderStatus(); schedulePreview(); scheduleHistory(); }

    /* ============ الخطوات ============ */
    function showStep(n) {
        if (n === 2) {
            syncPageFields();
            if (!state.page.title || !state.page.slug) { toast('أدخِل العنوان والمسار أوّلاً.', true); return; }
        }
        state.step = n;
        $('pbEditor').setAttribute('data-step', String(n));
        document.querySelectorAll('.pb-step-tab').forEach(function (t) { t.classList.toggle('is-active', t.getAttribute('data-pb-step') === String(n)); });
        if (n === 2) {
            $('pbPreviewFrame').srcdoc = '<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;padding:24px;color:#64748b">جارِ تحميل المعاينة…</body>';
            renderAll(); refreshPreview();
        }
    }

    /* ============ الشبكة ============ */
    function api(url, method, body, isForm) {
        var headers = { 'X-CSRF-TOKEN': B.csrf, 'Accept': 'application/json' };
        var opts = { method: method, headers: headers };
        if (isForm) { opts.body = body; }
        else if (body) { headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
        return fetch(url, opts).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, status: r.status, data: j }; });
        });
    }
    function toast(msg, isErr) {
        var t = $('pbToast'); t.textContent = msg; t.className = 'pb-toast' + (isErr ? ' err' : ''); t.hidden = false;
        clearTimeout(toast._t); toast._t = setTimeout(function () { t.hidden = true; }, 2600);
    }
    function syncPageFields() {
        state.page.title = $('pbTitle').value.trim();
        state.page.slug = $('pbSlug').value.trim();
        state.page.locale = $('pbLocale').value;
        state.page.meta_title = $('pbMetaTitle').value.trim();
        state.page.meta_description = $('pbMetaDescription').value.trim();
    }

    function saveBody() {
        syncPageFields();
        if (!state.page.title || !state.page.slug) { toast('العنوان والمسار مطلوبان.', true); return Promise.resolve(false); }
        var payload = {
            title: state.page.title, slug: state.page.slug, locale: state.page.locale,
            meta_title: state.page.meta_title, meta_description: state.page.meta_description,
            blocks: state.page.blocks,
            header_part_id: state.page.header_part_id, footer_part_id: state.page.footer_part_id,
            hide_header: state.page.hide_header, hide_footer: state.page.hide_footer,
            use_site_header: state.page.use_site_header, use_site_footer: state.page.use_site_footer,
        };
        var isNew = !state.page.id;
        var url = isNew ? B.urls.store : (B.urls.update + '/' + state.page.id);
        return api(url, isNew ? 'POST' : 'PUT', payload).then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر الحفظ.', true); return false; }
            state.page.id = res.data.page.id;
            state.page.status = res.data.page.status;
            if (state.page.status === 'published') state.page.has_unpublished = true; // حُفِظت مسودّة على صفحة منشورة
            if (isNew) history.replaceState({}, '', B.urls.indexUi.replace(/\/?$/, '/') + 'editor/' + state.page.id);
            renderStatus(); renderLang(); toast('حُفِظت المسودّة — لن تظهر للجمهور حتى تضغط «نشر».'); return true;
        });
    }
    function savePart() {
        var part = state.parts[state.region];
        if (!part) return Promise.resolve(false);
        return api(B.urls.updatePart + '/' + part.id, 'PUT', { name: part.name, blocks: part.blocks }).then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر الحفظ.', true); return false; }
            toast('حُفِظ ' + (state.region === 'header' ? 'الهيدر' : 'الفوتر') + '.'); return true;
        });
    }
    function save() { return state.region === 'body' ? saveBody() : savePart(); }
    function publish() {
        var chain = state.page.id ? Promise.resolve(true) : saveBody();
        chain.then(function (ok) {
            if (!ok && !state.page.id) return;
            api(B.urls.publish + '/' + state.page.id + '/publish', 'POST').then(function (res) {
                if (!res.ok) { toast(res.data.message || 'تعذّر النشر.', true); return; }
                state.page.status = 'published'; state.page.has_unpublished = false; renderStatus(); toast('نُشِرت الصفحة — صار المحتوى مرئيّاً للجمهور.');
            });
        });
    }
    function toggleLive() {
        var action = state.isLive ? 'take-down' : 'go-live';
        api(B.urls.goLive + '/' + state.page.id + '/' + action, 'POST').then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر التغيير.', true); return; }
            state.isLive = !state.isLive; renderStatus();
            toast(state.isLive ? 'الصفحة الآن مباشرة على /' + state.page.slug : 'أُوقِف البثّ.');
        });
    }

    /* ============ المعاينة الحيّة (دائمة في الخطوة 2) ============ */
    function buildPreviewPayload() {
        var p = { locale: state.page.locale || 'ar', body: state.page.blocks,
            hide_header: !!state.page.hide_header, hide_footer: !!state.page.hide_footer,
            use_site_header: !!state.page.use_site_header, use_site_footer: !!state.page.use_site_footer };
        if (state.parts.header) p.header = state.parts.header.blocks;
        else if (state.page.header_part_id) p.header_part_id = state.page.header_part_id;
        if (state.parts.footer) p.footer = state.parts.footer.blocks;
        else if (state.page.footer_part_id) p.footer_part_id = state.page.footer_part_id;
        return p;
    }
    function fetchPreview() {
        return fetch(B.urls.preview, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': B.csrf, 'Content-Type': 'application/json' },
            body: JSON.stringify(buildPreviewPayload()),
        }).then(function (r) { return r.text(); });
    }
    function refreshPreview() {
        if (state.step !== 2) return;
        var frame = $('pbPreviewFrame');
        fetchPreview().then(function (html) { frame.srcdoc = html; })
            .catch(function () { frame.srcdoc = '<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;padding:24px;color:#b91c1c">تعذّر تحميل المعاينة — تأكّد من الاتصال ثمّ اضغط «🔄 تحديث».</body>'; });
    }
    function openPreviewWindow() {
        var w = window.open('', '_blank');
        if (!w) { toast('اسمح بالنوافذ المنبثقة لهذا الموقع.', true); return; }
        w.document.write('<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;padding:24px">جارِ تحميل المعاينة…</body>');
        fetchPreview().then(function (html) { w.document.open(); w.document.write(html); w.document.close(); })
            .catch(function () { toast('تعذّرت المعاينة.', true); });
    }
    function schedulePreview() {
        if (state.step !== 2) return;
        clearTimeout(state.preview.timer);
        state.preview.timer = setTimeout(refreshPreview, 300);
    }
    function setPreviewDevice(dev) {
        state.preview.device = dev;
        $('pbEditor').setAttribute('data-dev', dev);
        document.querySelectorAll('#pbPreviewDevices button').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-dev') === dev); });
    }

    /* ============ الوسائط ============ */
    function loadMedia(search) {
        var url = B.urls.mediaIndex + (search ? ('?search=' + encodeURIComponent(search)) : '');
        api(url, 'GET').then(function (res) {
            var grid = $('pbMediaGrid'); grid.innerHTML = '';
            var list = (res.data && res.data.data) || [];
            if (!list.length) { grid.innerHTML = '<p class="pb-hint">لا نتائج.</p>'; return; }
            list.forEach(function (a) {
                var cell = document.createElement('div'); cell.className = 'pb-media-cell';
                var img = document.createElement('img'); img.src = a.url; img.alt = a.alt || ''; img.title = a.alt || '';
                img.onclick = function () { if (openMedia._cb) openMedia._cb(a); $('pbMediaModal').hidden = true; };
                var del = document.createElement('button'); del.className = 'pb-media-del'; del.textContent = '🗑'; del.title = 'حذف';
                del.onclick = function (e) { e.stopPropagation(); deleteMedia(a.id); };
                cell.appendChild(img); cell.appendChild(del);
                grid.appendChild(cell);
            });
        });
    }
    function deleteMedia(id) {
        if (!confirm('حذف هذه الصورة نهائيّاً؟')) return;
        api(B.urls.mediaStore + '/' + id, 'DELETE').then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر الحذف.', true); return; }
            toast('حُذِفت الصورة.'); loadMedia($('pbMediaSearch').value.trim());
        });
    }
    function openMedia(onPick) {
        openMedia._cb = onPick;
        $('pbMediaSearch').value = '';
        $('pbMediaModal').hidden = false;
        loadMedia('');
    }
    function uploadMedia(file) {
        var alt = $('pbMediaAlt').value.trim();
        if (!alt) { toast('أدخِل النصّ البديل قبل الرفع.', true); return; }
        var fd = new FormData(); fd.append('file', file); fd.append('alt', alt);
        api(B.urls.mediaStore, 'POST', fd, true).then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر الرفع.', true); return; }
            $('pbMediaAlt').value = ''; toast('رُفِعت الصورة.'); loadMedia('');
        });
    }

    /* ============ رموز التصميم ============ */
    function openDesign() {
        api(B.urls.design, 'GET').then(function (res) {
            var t = (res.data && res.data.tokens) || {}, fonts = (res.data && res.data.fonts) || ['Tajawal'];
            if (/^#[0-9a-fA-F]{6}$/.test(t.primary)) $('pbTkPrimary').value = t.primary;
            if (/^#[0-9a-fA-F]{6}$/.test(t.secondary)) $('pbTkSecondary').value = t.secondary;
            if (/^#[0-9a-fA-F]{6}$/.test(t.text)) $('pbTkText').value = t.text;
            if (/^#[0-9a-fA-F]{6}$/.test(t.bg)) $('pbTkBg').value = t.bg;
            $('pbTkRadius').value = t.radius != null ? t.radius : 12;
            var sel = $('pbTkFont'); sel.innerHTML = '';
            fonts.forEach(function (f) { var o = document.createElement('option'); o.value = f; o.textContent = f; if (f === t.font) o.selected = true; sel.appendChild(o); });
            // لوحات الألوان الجاهزة
            var pals = (res.data && res.data.palettes) || {};
            var host = $('pbTkPalettes'); host.innerHTML = '';
            Object.keys(pals).forEach(function (name) {
                var p = pals[name];
                var sw = document.createElement('button'); sw.type = 'button'; sw.className = 'pb-swatch'; sw.title = name;
                sw.style.background = 'linear-gradient(135deg,' + p.primary + ',' + p.secondary + ')';
                sw.onclick = function () {
                    $('pbTkPrimary').value = p.primary; $('pbTkSecondary').value = p.secondary;
                    $('pbTkText').value = p.text; $('pbTkBg').value = p.bg;
                };
                host.appendChild(sw);
            });
            $('pbDesignModal').hidden = false;
        });
    }
    function saveDesign() {
        var payload = { primary: $('pbTkPrimary').value, secondary: $('pbTkSecondary').value, text: $('pbTkText').value, bg: $('pbTkBg').value, font: $('pbTkFont').value, radius: parseInt($('pbTkRadius').value, 10) || 0 };
        api(B.urls.design, 'PUT', payload).then(function (res) {
            if (!res.ok) { toast('تعذّر حفظ التصميم.', true); return; }
            $('pbDesignModal').hidden = true; toast('حُفِظت رموز التصميم.'); refreshPreview();
        });
    }

    /* ============ سجلّ النُّسخ (تراجع) ============ */
    function openRevisions() {
        var host = $('pbRevList'); host.innerHTML = '';
        if (!state.page.id) { host.innerHTML = '<p class="pb-hint">احفظ الصفحة أوّلاً لتظهر النُّسخ.</p>'; }
        else {
            var revs = B.revisions || [];
            if (!revs.length) host.innerHTML = '<p class="pb-hint">لا نُسخ بعد — تُحفَظ لقطة تلقائيّاً قبل كل حفظ.</p>';
            revs.forEach(function (r) {
                var row = document.createElement('div'); row.className = 'pb-rev-row';
                var span = document.createElement('span'); span.innerHTML = '📸 ' + esc(r.at || '') + ' <small>(' + esc(r.label || '') + ')</small>';
                var btn = document.createElement('button'); btn.className = 'pb-back-btn'; btn.textContent = 'استرجاع';
                btn.onclick = function () { if (confirm('استرجاع هذه النسخة سيستبدل محتوى الجسم الحاليّ. متابعة؟')) restoreRevision(r.id); };
                row.appendChild(span); row.appendChild(btn); host.appendChild(row);
            });
        }
        $('pbHistoryModal').hidden = false;
    }
    function duplicatePage() {
        if (!state.page.id) { toast('احفظ الصفحة أوّلاً قبل النسخ.', true); return; }
        api(B.urls.update + '/' + state.page.id + '/duplicate', 'POST').then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر النسخ.', true); return; }
            toast('أُنشئت نسخة — يجري فتحها…');
            window.location.href = B.urls.indexUi.replace(/\/?$/, '/') + 'editor/' + res.data.page.id;
        });
    }
    function restoreRevision(id) {
        api(B.urls.update + '/' + state.page.id + '/restore/' + id, 'POST').then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر الاسترجاع.', true); return; }
            state.page.blocks = (res.data.page && res.data.page.blocks) || [];
            state.region = 'body'; state.selected = null;
            $('pbHistoryModal').hidden = true;
            switchRegion('body'); toast('تمّ استرجاع النسخة. احفظ لتثبيتها.');
        });
    }

    /* ============ اللغات ============ */
    function renderLang() {
        var host = $('pbLang'); host.innerHTML = '';
        if (!state.page.id) return;
        var cur = document.createElement('a'); cur.className = 'is-current'; cur.textContent = (state.page.locale || 'ar').toUpperCase(); cur.href = 'javascript:void(0)'; host.appendChild(cur);
        (B.translations || []).forEach(function (tr) {
            var a = document.createElement('a'); a.textContent = (tr.locale || '').toUpperCase();
            a.href = B.urls.indexUi.replace(/\/?$/, '/') + 'editor/' + tr.id; host.appendChild(a);
        });
        var have = [state.page.locale].concat((B.translations || []).map(function (t) { return t.locale; }));
        ['ar', 'en'].forEach(function (loc) {
            if (have.indexOf(loc) !== -1) return;
            var b = document.createElement('button'); b.textContent = '＋ ' + loc.toUpperCase(); b.onclick = function () { addTranslation(loc); }; host.appendChild(b);
        });
    }
    function addTranslation(locale) {
        if (!state.page.id) { toast('احفظ الصفحة أوّلاً.', true); return; }
        api(B.urls.update + '/' + state.page.id + '/translate', 'POST', { locale: locale }).then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر إنشاء اللغة.', true); return; }
            window.location.href = B.urls.indexUi.replace(/\/?$/, '/') + 'editor/' + res.data.page.id;
        });
    }

    /* ============ المناطق + أجزاء القالب ============ */
    function switchRegion(region) {
        state.region = region; state.selected = null;
        document.querySelectorAll('.pb-region-tab').forEach(function (t) { t.classList.toggle('is-active', t.getAttribute('data-pb-region') === region); });
        if (region === 'body') { renderAll(); return; }
        var idKey = region === 'header' ? 'header_part_id' : 'footer_part_id';
        var pagePartId = state.page[idKey];
        var loaded = state.parts[region];
        if (loaded && (!pagePartId || loaded.id === pagePartId)) { renderAll(); return; }
        var url = pagePartId ? B.urls.partBase + '/' + pagePartId : B.urls.activePart + '/' + region + '?locale=' + encodeURIComponent(state.page.locale || 'ar');
        api(url, 'GET').then(function (res) { state.parts[region] = res.data.part; renderAll(); });
    }
    function createNewPart(kind) {
        var name = prompt('اسم ال' + (kind === 'header' ? 'هيدر' : 'فوتر') + ' الجديد:');
        if (!name) return;
        api(B.urls.partCreate, 'POST', { kind: kind, name: name, locale: state.page.locale || 'ar' }).then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر الإنشاء.', true); return; }
            var p = res.data.part;
            B.parts[kind] = (B.parts[kind] || []).concat([{ id: p.id, name: p.name, is_active: false }]);
            var idKey = kind === 'header' ? 'header_part_id' : 'footer_part_id';
            var hideKey = kind === 'header' ? 'hide_header' : 'hide_footer';
            state.page[idKey] = p.id; state.page[hideKey] = false;
            state.parts[kind] = { id: p.id, name: p.name, blocks: [] };
            renderPageSettings();
            showStep(2); switchRegion(kind);
            toast('أُنشئ الجزء — حرّره ثمّ احفظ.');
        });
    }

    /* ============ الربط ============ */
    function bind() {
        document.querySelectorAll('.pb-step-tab').forEach(function (t) { t.onclick = function () { showStep(Number(t.getAttribute('data-pb-step'))); }; });
        $('pbToStep2').onclick = function () { showStep(2); };
        $('pbBackStep1').onclick = function () { showStep(1); };
        document.querySelectorAll('.pb-region-tab').forEach(function (t) { t.onclick = function () { switchRegion(t.getAttribute('data-pb-region')); }; });
        $('pbSave').onclick = function () { save(); };
        $('pbPublish').onclick = function () { publish(); };
        $('pbGoLive').onclick = function () { toggleLive(); };
        $('pbDesign').onclick = function () { openDesign(); };
        $('pbTkSave').onclick = function () { saveDesign(); };
        $('pbHistory').onclick = function () { openRevisions(); };
        $('pbDuplicate').onclick = function () { duplicatePage(); };
        $('pbRefreshPreview').onclick = function () { refreshPreview(); };
        $('pbOpenPreview').onclick = function () { openPreviewWindow(); };
        $('pbPatternsBtn').onclick = function () { openPatterns(); };
        $('pbSavePatternBtn').onclick = function () { saveAsPattern(); };
        document.querySelectorAll('#pbPreviewDevices button').forEach(function (b) { b.onclick = function () { setPreviewDevice(b.getAttribute('data-dev')); }; });
        $('pbNewHeader').onclick = function () { createNewPart('header'); };
        $('pbNewFooter').onclick = function () { createNewPart('footer'); };
        ['pbTitle', 'pbSlug', 'pbLocale', 'pbMetaTitle', 'pbMetaDescription'].forEach(function (id) { $(id).addEventListener('change', function () { syncPageFields(); schedulePreview(); }); });
        document.querySelectorAll('[data-pb-close]').forEach(function (x) { x.onclick = function () { x.closest('.pb-modal').hidden = true; }; });
        $('pbMediaFile').addEventListener('change', function (e) { if (e.target.files[0]) uploadMedia(e.target.files[0]); });
        // رسائل إطار المعاينة: تحديد الكتلة (نقر) + تحرير النصّ في المكان
        window.addEventListener('message', function (e) {
            if (!e.data) return;
            if (e.data.pbSelect !== undefined) {
                var idx = parseInt(e.data.pbSelect, 10); if (isNaN(idx)) return;
                state.region = 'body';
                document.querySelectorAll('.pb-region-tab').forEach(function (t) { t.classList.toggle('is-active', t.getAttribute('data-pb-region') === 'body'); });
                state.selected = [idx]; renderCanvas(); renderInspector();
                var insp = $('pbInspector'); if (insp && insp.scrollIntoView) insp.scrollIntoView({ block: 'nearest' });
            } else if (e.data.pbEdit) {
                var d = e.data.pbEdit; var i = parseInt(d.path, 10);
                if (isNaN(i) || state.region !== 'body') return;
                var blk = state.page.blocks[i];
                if (blk) {
                    blk.props = blk.props || {}; blk.props[d.key] = d.value;
                    renderCanvas(); if (samePath(state.selected, [i])) renderInspector();
                    // لا نُحدّث المعاينة الآن (الإطار يعرض النصّ المكتوب) — يتزامن عند أيّ تغيير لاحق
                }
            }
        });
        var mediaTimer;
        $('pbMediaSearch').addEventListener('input', function () { clearTimeout(mediaTimer); mediaTimer = setTimeout(function () { loadMedia($('pbMediaSearch').value.trim()); }, 300); });
    }

    /* ============ اختصارات لوحة المفاتيح ============ */
    function onKey(e) {
        var mod = e.ctrlKey || e.metaKey;
        var t = (e.target.tagName || '').toLowerCase();
        var typing = t === 'input' || t === 'textarea' || t === 'select' || e.target.isContentEditable;
        var k = (e.key || '').toLowerCase();
        if (mod && k === 'z') { e.preventDefault(); e.shiftKey ? redo() : undo(); return; }
        if (mod && k === 'y') { e.preventDefault(); redo(); return; }
        if (mod && k === 's') { e.preventDefault(); save(); return; }
        if (typing) return;
        if (mod && k === 'd') { e.preventDefault(); if (state.selected) duplicateBlock(state.selected); return; }
        if ((e.key === 'Delete' || e.key === 'Backspace') && state.selected) { e.preventDefault(); deleteBlock(state.selected); }
    }

    /* ============ الإقلاع ============ */
    renderPalette(); bind(); renderPageSettings(); renderStatus(); renderLang();
    $('pbUndo').onclick = function () { undo(); };
    $('pbRedo').onclick = function () { redo(); };
    document.addEventListener('keydown', onKey);
    pushHistory(); // لقطة أوليّة
    showStep(1);
})();
