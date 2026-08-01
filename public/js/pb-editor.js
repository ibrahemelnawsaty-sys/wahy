/*
 * محرّر الصفحات الخفيف (المرحلة 2 — بلا React). واجهة كتل مُولَّدة من مخطّط BlockRegistry،
 * تحرّر ثلاث مناطق مستقلّة (الجسم/الهيدر/الفوتر)، وتحفظ عبر نقاط JSON الآمنة.
 * لا يبني HTML خامّاً للموقع — الرندرة النهائيّة دائماً عبر مكوّنات Blade على الخادم.
 */
(function () {
    'use strict';
    var B = window.PB_BOOT;
    if (!B) return;

    var state = {
        page: B.page ? Object.assign({ blocks: [] }, B.page) : {
            id: null, title: '', slug: '', locale: 'ar', status: 'draft',
            meta_title: '', meta_description: '', blocks: [],
        },
        region: 'body',
        parts: { header: null, footer: null }, // {id, name, blocks} تُحمّل عند فتح التبويب
        selected: null,                        // مسار الكتلة المختارة داخل منطقةٍ ما
        isLive: !!B.isLive,
    };

    var $ = function (id) { return document.getElementById(id); };
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };

    /* ============ الوصول لكتل المنطقة الحاليّة عبر مسار ============ */
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

    /* ============ عمليّات ============ */
    function newBlock(type) {
        var blk = { type: type, v: 1, props: {} };
        if (B.schema[type] && B.schema[type].children) blk.children = [];
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

    /* ============ رندرة اللوحة (الكتل المتاحة) ============ */
    function renderPalette() {
        var host = $('pbPalette'); host.innerHTML = '';
        Object.keys(B.schema).forEach(function (type) {
            var s = B.schema[type];
            var btn = document.createElement('button');
            btn.className = 'pb-add-btn';
            btn.innerHTML = '<span class="pb-emoji">' + esc(s.icon || '▪') + '</span>' + esc(s.label || type);
            btn.onclick = function () { addBlock(type, null); };
            host.appendChild(btn);
        });
    }

    /* ============ رندرة اللوح (قائمة الكتل) ============ */
    function summaryOf(block) {
        var p = block.props || {};
        return p.title || p.heading || p.text || p.html || p.alt || p.caption || '';
    }
    function renderList(arr, basePath, host) {
        arr.forEach(function (block, i) {
            var path = basePath.concat([i]);
            var s = B.schema[block.type] || { icon: '▪', label: block.type };
            var card = document.createElement('div');
            card.className = 'pb-card' + (samePath(state.selected, path) ? ' is-selected' : '');
            card.innerHTML =
                '<span class="pb-emoji">' + esc(s.icon || '▪') + '</span>' +
                '<div class="pb-card-main"><div class="pb-card-type">' + esc(s.label || block.type) + '</div>' +
                '<div class="pb-card-sum">' + esc(summaryOf(block)) + '</div></div>' +
                '<div class="pb-card-ops">' +
                '<button class="pb-icon-btn" data-op="up" title="أعلى">▲</button>' +
                '<button class="pb-icon-btn" data-op="down" title="أسفل">▼</button>' +
                '<button class="pb-icon-btn danger" data-op="del" title="حذف">🗑</button></div>';
            card.addEventListener('click', function (e) {
                var op = e.target.getAttribute && e.target.getAttribute('data-op');
                if (op === 'up') { e.stopPropagation(); moveBlock(path, -1); }
                else if (op === 'down') { e.stopPropagation(); moveBlock(path, 1); }
                else if (op === 'del') { e.stopPropagation(); deleteBlock(path); }
                else { state.selected = path; renderAll(); }
            });
            host.appendChild(card);

            if (s.children) {
                var wrap = document.createElement('div');
                wrap.className = 'pb-children';
                renderList(block.children || [], path, wrap);
                var add = document.createElement('button');
                add.className = 'pb-rep-add';
                add.textContent = '＋ كتلة داخل الأعمدة';
                add.onclick = function () { openTypeMenu(function (t) { addBlock(t, path); }); };
                wrap.appendChild(add);
                host.appendChild(wrap);
            }
        });
    }
    function renderCanvas() {
        var host = $('pbCanvas'); host.innerHTML = '';
        var arr = rootArray();
        if (!arr.length) {
            host.innerHTML = '<div class="pb-canvas-empty">لا كتل بعد — أضِف كتلة من اللوحة اليمنى.</div>';
            return;
        }
        renderList(arr, [], host);
    }

    /* قائمة أنواع سريعة (لإضافة ابن) */
    function openTypeMenu(cb) {
        var types = Object.keys(B.schema).filter(function (t) { return !(B.schema[t].children); }); // لا تداخل عميق
        var label = types.map(function (t, i) { return (i + 1) + ') ' + B.schema[t].label; }).join('\n');
        var pick = prompt('اختر نوع الكتلة برقمه:\n' + label);
        var idx = parseInt(pick, 10) - 1;
        if (idx >= 0 && types[idx]) cb(types[idx]);
    }

    /* ============ المفتّش (خصائص الكتلة المختارة) ============ */
    function fieldControl(field, value, onChange) {
        var wrap = document.createElement('div'); wrap.className = 'pb-field';
        var lab = document.createElement('label'); lab.textContent = field.label; wrap.appendChild(lab);
        var el;
        if (field.type === 'textarea' || field.type === 'richtext') {
            el = document.createElement('textarea'); el.rows = field.type === 'richtext' ? 6 : 3; el.value = value || '';
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
            // لا نخمّن رابط القرص العامّ (جذره غير قياسيّ) — نعرض مصغّرةً فقط حين نملك رابطاً حقيقيّاً.
            var showThumb = function (url) { if (url) { thumb.src = url; thumb.style.display = ''; } else { thumb.style.display = 'none'; } };
            showThumb(/^https?:\/\//.test(value || '') ? value : '');
            var inp = document.createElement('input'); inp.type = 'text'; inp.value = value || ''; inp.placeholder = 'مسار الصورة';
            inp.oninput = function () { onChange(inp.value); };
            var pick = document.createElement('button'); pick.type = 'button'; pick.className = 'btn btn-sm btn-outline-primary'; pick.textContent = 'اختر';
            pick.onclick = function () { openMedia(function (asset) { inp.value = asset.path; showThumb(asset.url); onChange(asset.path); }); };
            row.appendChild(thumb); row.appendChild(inp); row.appendChild(pick);
            wrap.appendChild(row); return wrap;
        } else {
            el = document.createElement('input'); el.type = field.type === 'url' ? 'text' : 'text'; el.value = value || '';
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
                box.appendChild(fieldControl(sub, item[sub.key], function (v) { item[sub.key] = v; renderCanvas(); }));
            });
            var del = document.createElement('button'); del.className = 'pb-rep-del'; del.textContent = 'حذف العنصر';
            del.onclick = function () { items.splice(idx, 1); renderInspector(); renderCanvas(); };
            box.appendChild(del); wrap.appendChild(box);
        });
        var add = document.createElement('button'); add.className = 'pb-rep-add'; add.textContent = '＋ عنصر';
        add.onclick = function () { items.push({}); renderInspector(); };
        wrap.appendChild(add); return wrap;
    }
    function renderInspector() {
        var host = $('pbInspector'); host.innerHTML = '';
        var block = blockAt(state.selected);
        if (!block) { host.innerHTML = '<p class="pb-hint">اختر كتلةً لتحرير خصائصها.</p>'; return; }
        var s = B.schema[block.type];
        if (!s) { host.innerHTML = '<p class="pb-hint">نوع غير معروف.</p>'; return; }
        block.props = block.props || {};
        (s.fields || []).forEach(function (field) {
            if (field.type === 'repeater') host.appendChild(repeaterControl(field, block));
            else host.appendChild(fieldControl(field, block.props[field.key], function (v) {
                block.props[field.key] = v; renderCanvas();
            }));
        });
    }

    /* ============ رندرة شاملة ============ */
    function renderPageSettings() {
        $('pbPageSettings').style.display = state.region === 'body' ? '' : 'none';
        $('pbTitle').value = state.page.title || '';
        $('pbSlug').value = state.page.slug || '';
        $('pbLocale').value = state.page.locale || 'ar';
        $('pbMetaTitle').value = state.page.meta_title || '';
        $('pbMetaDescription').value = state.page.meta_description || '';
    }
    function renderStatus() {
        var pill = $('pbStatusPill');
        pill.textContent = (state.page.status === 'published' ? 'منشورة' : 'مسودّة') + (state.page.id ? '' : ' (غير محفوظة)');
        var gl = $('pbGoLive');
        gl.textContent = state.isLive ? '● إيقاف البثّ' : '○ بثّ مباشر';
        gl.disabled = !state.page.id || state.page.status !== 'published';
    }
    function renderAll() { renderCanvas(); renderInspector(); renderStatus(); }

    /* ============ نداءات الشبكة ============ */
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
        };
        var isNew = !state.page.id;
        var url = isNew ? B.urls.store : (B.urls.update + '/' + state.page.id);
        return api(url, isNew ? 'POST' : 'PUT', payload).then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر الحفظ.', true); return false; }
            state.page.id = res.data.page.id;
            state.page.status = res.data.page.status;
            if (isNew) history.replaceState({}, '', B.urls.indexUi.replace(/\/?$/, '/') + 'editor/' + state.page.id);
            renderStatus(); toast('حُفِظت المسودّة.'); return true;
        });
    }
    function savePart() {
        var part = state.parts[state.region];
        if (!part) return Promise.resolve(false);
        return api(B.urls.updatePart + '/' + part.id, 'PUT', { name: part.name, blocks: part.blocks })
            .then(function (res) {
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
                state.page.status = 'published'; renderStatus(); toast('نُشِرت الصفحة.');
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

    function preview() {
        // المعاينة تُرجِع HTML لا JSON؛ نجلبها نصّاً ونعرضها في iframe.
        fetch(B.urls.preview, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': B.csrf, 'Content-Type': 'application/json' },
            body: JSON.stringify({ blocks: rootArray() }),
        }).then(function (r) { return r.text(); }).then(function (html) {
            $('pbPreviewFrame').srcdoc = html;
            $('pbPreviewModal').hidden = false;
        }).catch(function () { toast('تعذّرت المعاينة.', true); });
    }

    /* ============ الوسائط ============ */
    function openMedia(onPick) {
        openMedia._cb = onPick;
        $('pbMediaModal').hidden = false;
        api(B.urls.mediaIndex, 'GET').then(function (res) {
            var grid = $('pbMediaGrid'); grid.innerHTML = '';
            var list = (res.data && res.data.data) || [];
            if (!list.length) { grid.innerHTML = '<p class="pb-hint">لا وسائط بعد — ارفع صورة.</p>'; }
            list.forEach(function (a) {
                var cell = document.createElement('div'); cell.className = 'pb-media-cell';
                cell.innerHTML = '<img src="' + esc(a.url) + '" alt="' + esc(a.alt || '') + '">';
                cell.onclick = function () { if (openMedia._cb) openMedia._cb(a); $('pbMediaModal').hidden = true; };
                grid.appendChild(cell);
            });
        });
    }
    function uploadMedia(file) {
        var alt = $('pbMediaAlt').value.trim();
        if (!alt) { toast('أدخِل النصّ البديل قبل الرفع.', true); return; }
        var fd = new FormData(); fd.append('file', file); fd.append('alt', alt);
        api(B.urls.mediaStore, 'POST', fd, true).then(function (res) {
            if (!res.ok) { toast(res.data.message || 'تعذّر الرفع.', true); return; }
            toast('رُفِعت الصورة.'); openMedia(openMedia._cb); // إعادة التحميل
        });
    }

    /* ============ المناطق (التبويبات) ============ */
    function switchRegion(region) {
        state.region = region; state.selected = null;
        document.querySelectorAll('.pb-tab').forEach(function (t) {
            t.classList.toggle('is-active', t.getAttribute('data-pb-region') === region);
        });
        renderPageSettings();
        if (region !== 'body' && !state.parts[region]) {
            api(B.urls.activePart + '/' + region + '?locale=' + encodeURIComponent(state.page.locale || 'ar'), 'GET')
                .then(function (res) {
                    state.parts[region] = res.data.part; renderAll();
                });
        } else { renderAll(); }
    }

    /* ============ الربط ============ */
    function bind() {
        document.querySelectorAll('.pb-tab').forEach(function (t) {
            t.onclick = function () { switchRegion(t.getAttribute('data-pb-region')); };
        });
        $('pbSave').onclick = function () { save(); };
        $('pbPublish').onclick = function () { publish(); };
        $('pbPreview').onclick = function () { preview(); };
        $('pbGoLive').onclick = function () { toggleLive(); };
        ['pbTitle', 'pbSlug', 'pbLocale', 'pbMetaTitle', 'pbMetaDescription'].forEach(function (id) {
            $(id).addEventListener('change', syncPageFields);
        });
        document.querySelectorAll('[data-pb-close]').forEach(function (x) {
            x.onclick = function () { x.closest('.pb-modal').hidden = true; };
        });
        $('pbMediaFile').addEventListener('change', function (e) {
            if (e.target.files[0]) uploadMedia(e.target.files[0]);
        });
    }

    /* ============ الإقلاع ============ */
    renderPalette(); bind(); renderPageSettings(); renderAll();
})();
