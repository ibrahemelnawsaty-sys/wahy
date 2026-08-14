@extends('layouts.admin')

@section('title', $page ? 'تحرير: ' . $page->title : 'صفحة جديدة')
@section('page-title', $page ? 'تحرير صفحة' : 'صفحة جديدة')

@section('content')
<div class="pb-editor" id="pbEditor" data-step="1">
    {{-- شريط الأدوات + مؤشّر الخطوات --}}
    <div class="pb-toolbar">
        <a href="{{ route('admin.pb.ui.index') }}" class="pb-tool-back">↩ الصفحات</a>

        <div class="pb-steps" role="tablist">
            <button class="pb-step-tab is-active" data-pb-step="1"><span class="pb-step-num">1</span> بيانات الصفحة</button>
            <button class="pb-step-tab" data-pb-step="2"><span class="pb-step-num">2</span> المحتوى</button>
        </div>

        <div class="pb-tool-actions">
            <span class="pb-status" id="pbStatusPill"></span>
            <button class="btn btn-primary btn-sm" id="pbSave">💾 حفظ</button>
            <button class="btn btn-success btn-sm" id="pbPublish">🚀 نشر</button>
            <button class="btn btn-outline-primary btn-sm" id="pbGoLive"></button>
            <button class="btn btn-outline-secondary btn-sm" id="pbDesign">🎨 التصميم</button>
            <button class="btn btn-outline-secondary btn-sm" id="pbHistory">🕓 السجلّ</button>
            <button class="btn btn-outline-secondary btn-sm" id="pbDuplicate">📄 نسخة</button>
            <span class="pb-lang" id="pbLang"></span>
        </div>
    </div>

    {{-- ═══ الخطوة 1: بيانات الصفحة ═══ --}}
    <div class="pb-step-panel" data-pb-panel="1">
        <div class="pb-settings-card">
            <h3 class="pb-settings-h">① بيانات الصفحة</h3>
            <p class="pb-settings-note">اضبط بيانات الصفحة أوّلاً، ثمّ انتقل إلى «المحتوى» لإضافة الكتل.</p>
            <div id="pbPageSettings">
                <div class="pb-field"><label>العنوان *</label><input type="text" id="pbTitle" placeholder="عنوان الصفحة"></div>
                <div class="pb-field"><label>المسار (slug) *</label><input type="text" id="pbSlug" placeholder="about-us"></div>
                <div class="pb-field"><label>اللغة</label>
                    <select id="pbLocale"><option value="ar">العربيّة</option><option value="en">English</option></select>
                </div>
                <div class="pb-field">
                    <label>الهيدر لهذه الصفحة</label>
                    <div class="pb-part-row"><select id="pbHeaderPart"></select><button type="button" class="pb-mini-btn" id="pbNewHeader" title="هيدر جديد">＋</button></div>
                </div>
                <div class="pb-field">
                    <label>الفوتر لهذه الصفحة</label>
                    <div class="pb-part-row"><select id="pbFooterPart"></select><button type="button" class="pb-mini-btn" id="pbNewFooter" title="فوتر جديد">＋</button></div>
                </div>
                <div class="pb-field"><label>عنوان SEO</label><input type="text" id="pbMetaTitle" placeholder="اختياريّ"></div>
                <div class="pb-field"><label>وصف SEO</label><textarea id="pbMetaDescription" rows="2" placeholder="اختياريّ"></textarea></div>
            </div>
            <button class="btn btn-primary pb-next-btn" id="pbToStep2">التالي: المحتوى ←</button>
        </div>
    </div>

    {{-- ═══ الخطوة 2: المحتوى (تحرير + معاينة حيّة دائمة) ═══ --}}
    <div class="pb-step-panel" data-pb-panel="2">
        <div class="pb-step2-bar">
            <button class="pb-back-btn" id="pbBackStep1">← رجوع للبيانات</button>
            <div class="pb-region-tabs" role="tablist">
                <button class="pb-region-tab is-active" data-pb-region="body">الجسم</button>
                <button class="pb-region-tab" data-pb-region="header">الهيدر</button>
                <button class="pb-region-tab" data-pb-region="footer">الفوتر</button>
            </div>
            <button class="pb-back-btn" id="pbRefreshPreview" title="تحديث المعاينة">🔄 تحديث</button>
            <button class="pb-back-btn" id="pbOpenPreview" title="فتح المعاينة في نافذة">↗ نافذة</button>
            <span class="pb-preview-devices" id="pbPreviewDevices">
                <button type="button" data-dev="desktop" class="is-active" title="سطح المكتب">🖥</button>
                <button type="button" data-dev="tablet" title="لوحيّ">◻</button>
                <button type="button" data-dev="mobile" title="جوّال">▯</button>
            </span>
        </div>

        <div class="pb-step2-grid">
            {{-- لوحة الكتل --}}
            <aside class="pb-palette">
                <button type="button" class="pb-patterns-btn" id="pbPatternsBtn">🧩 أنماط جاهزة</button>
                <div class="pb-panel-label">أو أضف كتلة مفردة</div>
                <div id="pbPalette" class="pb-palette-list"></div>
            </aside>

            {{-- المعاينة الحيّة الدائمة (WYSIWYG) --}}
            <main class="pb-preview-col">
                <iframe id="pbPreviewFrame" class="pb-preview-frame" title="معاينة حيّة"></iframe>
            </main>

            {{-- البنية + خصائص الكتلة المختارة --}}
            <aside class="pb-side">
                <div class="pb-side-sec">
                    <div class="pb-panel-label">بنية الصفحة</div>
                    <div class="pb-canvas" id="pbCanvas"></div>
                </div>
                <div class="pb-side-sec">
                    <div class="pb-panel-label">الخصائص</div>
                    <div id="pbInspector" class="pb-inspector-body"><p class="pb-hint">اختر كتلةً لتحرير خصائصها.</p></div>
                </div>
            </aside>
        </div>
    </div>
</div>

{{-- مُنتقي الوسائط --}}
<div class="pb-modal" id="pbMediaModal" hidden>
    <div class="pb-modal-box">
        <div class="pb-modal-head"><b>مكتبة الوسائط</b><button class="pb-modal-x" data-pb-close>✕</button></div>
        <div class="pb-modal-body">
            <label class="pb-upload">
                <input type="file" id="pbMediaFile" accept="image/*" hidden>
                <span>⬆ رفع صورة (تحتاج نصّاً بديلاً)</span>
            </label>
            <input type="text" id="pbMediaAlt" placeholder="النصّ البديل للصورة المرفوعة" class="pb-media-alt">
            <input type="text" id="pbMediaSearch" placeholder="🔎 ابحث في الوسائط بالنصّ البديل…" class="pb-media-alt" autocomplete="off">
            <div class="pb-media-grid" id="pbMediaGrid"></div>
        </div>
    </div>
</div>

{{-- رموز التصميم (ت-١٠) --}}
<div class="pb-modal" id="pbDesignModal" hidden>
    <div class="pb-modal-box">
        <div class="pb-modal-head"><b>رموز التصميم (تُطبَّق على كلّ الصفحات)</b><button class="pb-modal-x" data-pb-close>✕</button></div>
        <div class="pb-modal-body">
            <div class="pb-field"><label>اللون الأساسيّ</label><input type="color" id="pbTkPrimary"></div>
            <div class="pb-field"><label>اللون الثانويّ</label><input type="color" id="pbTkSecondary"></div>
            <div class="pb-field"><label>لون النصّ</label><input type="color" id="pbTkText"></div>
            <div class="pb-field"><label>لون الخلفيّة</label><input type="color" id="pbTkBg"></div>
            <div class="pb-field"><label>الخطّ</label><select id="pbTkFont"></select></div>
            <div class="pb-field"><label>الاستدارة (بكسل)</label><input type="number" id="pbTkRadius" min="0" max="40"></div>
            <button class="btn btn-primary" id="pbTkSave">حفظ التصميم</button>
        </div>
    </div>
</div>

{{-- سجلّ النُّسخ (تراجع) --}}
<div class="pb-modal" id="pbHistoryModal" hidden>
    <div class="pb-modal-box">
        <div class="pb-modal-head"><b>🕓 سجلّ النُّسخ — استرجاع نسخة سابقة من الجسم</b><button class="pb-modal-x" data-pb-close>✕</button></div>
        <div class="pb-modal-body"><div id="pbRevList" class="pb-rev-list"></div></div>
    </div>
</div>

{{-- أنماط/أقسام جاهزة (كالووردبريس) --}}
<div class="pb-modal" id="pbPatternsModal" hidden>
    <div class="pb-modal-box pb-modal-wide">
        <div class="pb-modal-head"><b>🧩 أنماط جاهزة — أدرِج قسماً كاملاً ثمّ عدّل محتواه</b><button class="pb-modal-x" data-pb-close>✕</button></div>
        <div class="pb-modal-body"><div class="pb-patterns-grid" id="pbPatternsGrid"></div></div>
    </div>
</div>

{{-- مُنتقي الكتل (بديل prompt) --}}
<div class="pb-modal" id="pbInserterModal" hidden>
    <div class="pb-modal-box">
        <div class="pb-modal-head"><b>إضافة كتلة</b><button class="pb-modal-x" data-pb-close>✕</button></div>
        <div class="pb-modal-body">
            <input type="text" id="pbInserterSearch" placeholder="🔎 ابحث عن كتلة…" class="pb-media-alt" autocomplete="off">
            <div class="pb-inserter-grid" id="pbInserterGrid"></div>
        </div>
    </div>
</div>

<div class="pb-toast" id="pbToast" hidden></div>

<script>window.PB_BOOT = @json($boot);</script>
<script src="{{ asset('js/pb-editor.js') }}?v={{ @filemtime(public_path('js/pb-editor.js')) ?: '5' }}"></script>
@endsection

@push('styles')
<style>
    .pb-toolbar{position:sticky;top:0;z-index:20;display:flex;align-items:center;gap:14px;flex-wrap:wrap;
        padding:10px 14px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;margin-bottom:14px}
    .pb-tool-back{text-decoration:none;color:#475569;font-weight:700;font-size:.9rem}
    .pb-steps{display:flex;gap:4px;background:#f1f5f9;border-radius:10px;padding:3px}
    .pb-step-tab{border:0;background:transparent;padding:6px 16px;border-radius:8px;font-weight:700;color:#64748b;cursor:pointer;display:flex;align-items:center;gap:7px}
    .pb-step-tab.is-active{background:#fff;color:#4338ca;box-shadow:0 1px 3px rgba(0,0,0,.08)}
    .pb-step-num{display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#e2e8f0;color:#475569;font-size:.78rem}
    .pb-step-tab.is-active .pb-step-num{background:#4338ca;color:#fff}
    .pb-tool-actions{margin-inline-start:auto;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .pb-status{font-size:.82rem;color:#94a3b8}
    .pb-lang{display:inline-flex;gap:4px;align-items:center}
    .pb-lang a,.pb-lang button{font-size:.76rem;font-weight:700;border:1px solid #e5e7eb;border-radius:8px;padding:3px 9px;background:#f8fafc;color:#475569;cursor:pointer;text-decoration:none}
    .pb-lang a.is-current{background:#eef2ff;color:#4338ca;border-color:#c7d2fe}

    /* الخطوات */
    .pb-step-panel{display:none}
    .pb-editor[data-step="1"] .pb-step-panel[data-pb-panel="1"]{display:block}
    .pb-editor[data-step="2"] .pb-step-panel[data-pb-panel="2"]{display:block}
    .pb-settings-card{max-width:620px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:26px}
    .pb-settings-h{margin:0 0 4px;font-size:1.15rem;font-weight:800}
    .pb-settings-note{margin:0 0 18px;color:#64748b;font-size:.9rem}
    .pb-next-btn{width:100%;margin-top:8px;padding:12px}

    .pb-step2-bar{display:flex;align-items:center;gap:12px;margin-bottom:10px;flex-wrap:wrap}
    .pb-back-btn{border:1px solid #e5e7eb;background:#fff;border-radius:9px;padding:7px 14px;font-weight:700;color:#475569;cursor:pointer}
    .pb-region-tabs{display:flex;gap:3px;background:#f1f5f9;border-radius:8px;padding:3px}
    .pb-region-tab{border:0;background:transparent;padding:5px 14px;border-radius:6px;font-weight:700;color:#64748b;cursor:pointer;font-size:.85rem}
    .pb-region-tab.is-active{background:#fff;color:#4338ca;box-shadow:0 1px 3px rgba(0,0,0,.08)}
    .pb-preview-devices{margin-inline-start:auto;display:flex;gap:3px;background:#f1f5f9;border-radius:8px;padding:3px}
    .pb-preview-devices button{border:0;background:transparent;padding:4px 10px;border-radius:6px;cursor:pointer;font-size:.95rem}
    .pb-preview-devices button.is-active{background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.1)}

    /* أعمدة بارتفاع صريح (لا نعتمد على تمدّد الشبكة كي لا ينهار ارتفاع الـiframe) */
    .pb-step2-grid{display:grid;grid-template-columns:200px 1fr 320px;gap:12px;align-items:start}
    .pb-palette,.pb-side-sec{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:12px}
    .pb-palette{overflow:auto;height:calc(100vh - 230px);min-height:460px}
    .pb-panel-label{font-weight:800;font-size:.78rem;color:#64748b;text-transform:uppercase;letter-spacing:.03em;margin-bottom:8px}
    .pb-palette-list{display:flex;flex-direction:column;gap:5px}
    .pb-pal-cat{font-size:.68rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;margin:8px 2px 2px}
    .pb-pal-cat:first-child{margin-top:0}
    .pb-add-btn{display:flex;flex-direction:column;gap:2px;border:1px solid #e5e7eb;background:#f8fafc;border-radius:10px;padding:8px 10px;cursor:pointer;text-align:start}
    .pb-add-btn:hover{border-color:#a5b4fc;background:#eef2ff}
    .pb-add-btn .pb-add-top{display:flex;align-items:center;gap:8px;font-weight:700;font-size:.88rem;color:#334155}
    .pb-add-btn .pb-emoji{font-size:1.05rem}
    .pb-add-btn .pb-add-desc{font-size:.72rem;color:#94a3b8;line-height:1.4}
    .pb-patterns-btn{width:100%;border:1px solid #c7d2fe;background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:#4338ca;
        border-radius:10px;padding:11px;font-weight:800;font-size:.9rem;cursor:pointer;margin-bottom:10px}
    .pb-patterns-btn:hover{background:#e0e7ff}
    .pb-modal-wide{width:760px}
    .pb-patterns-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin-top:6px}
    .pb-pattern-card{border:1px solid #e5e7eb;background:#f8fafc;border-radius:12px;padding:16px 10px;cursor:pointer;text-align:center}
    .pb-pattern-card:hover{border-color:#a5b4fc;background:#eef2ff;transform:translateY(-2px)}
    .pb-pattern-icon{font-size:1.8rem;display:block;margin-bottom:6px}
    .pb-pattern-label{font-weight:700;font-size:.85rem;color:#334155}
    .pb-pattern-cat{font-size:.68rem;color:#94a3b8;margin-top:2px}
    /* سحب وإفلات بطاقات البنية */
    .pb-card[draggable="true"]{cursor:grab}
    .pb-card.pb-dragging{opacity:.45}
    .pb-card.pb-drop-before{box-shadow:0 -3px 0 #6366f1}
    .pb-card.pb-drop-after{box-shadow:0 3px 0 #6366f1}
    .pb-card-grip{color:#cbd5e1;cursor:grab;font-size:.9rem;padding:0 2px}
    .pb-rev-list{display:flex;flex-direction:column;gap:8px}
    .pb-rev-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border:1px solid #e5e7eb;border-radius:10px;background:#f8fafc;font-size:.85rem}
    .pb-rev-row small{color:#94a3b8}

    .pb-preview-col{height:calc(100vh - 230px);min-height:460px;background:#f1f5f9;border:1px solid #cbd5e1;border-radius:14px;overflow:hidden;display:flex;justify-content:center}
    .pb-preview-frame{width:100%;height:100%;border:0;background:#fff;transition:width .2s;max-width:100%}
    .pb-editor[data-dev="tablet"] .pb-preview-frame{width:768px}
    .pb-editor[data-dev="mobile"] .pb-preview-frame{width:390px}

    .pb-side{display:flex;flex-direction:column;gap:12px;height:calc(100vh - 230px);min-height:460px;overflow:auto}
    .pb-canvas{display:flex;flex-direction:column;gap:6px;min-height:40px}
    .pb-card{display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:8px 10px;cursor:pointer}
    .pb-card.is-selected{border-color:#6366f1;box-shadow:0 0 0 2px rgba(99,102,241,.2)}
    .pb-card .pb-emoji{font-size:1.1rem}
    .pb-card-main{flex:1;min-width:0}
    .pb-card-type{font-weight:800;font-size:.85rem}
    .pb-card-sum{font-size:.76rem;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .pb-card-ops{display:flex;gap:3px}
    .pb-icon-btn{border:0;background:#f1f5f9;border-radius:7px;width:26px;height:26px;cursor:pointer;font-size:.82rem;color:#475569}
    .pb-icon-btn:hover{background:#e2e8f0}
    .pb-icon-btn.danger:hover{background:#fee2e2;color:#b91c1c}
    .pb-canvas-empty{border:2px dashed #e5e7eb;border-radius:12px;padding:24px 12px;text-align:center;color:#94a3b8;font-size:.85rem}

    .pb-field{margin-bottom:12px;display:flex;flex-direction:column;gap:5px}
    .pb-field label{font-weight:700;font-size:.82rem;color:#475569}
    .pb-field input,.pb-field select,.pb-field textarea{width:100%;border:1px solid #d1d5db;border-radius:9px;padding:8px 10px;font:inherit;background:#fff}
    .pb-field input:focus,.pb-field textarea:focus,.pb-field select:focus{outline:0;border-color:#818cf8;box-shadow:0 0 0 3px rgba(129,140,248,.18)}
    .pb-hint{color:#94a3b8;font-size:.88rem}
    .pb-part-row{display:flex;gap:6px;align-items:center}
    .pb-part-row select{flex:1}
    .pb-mini-btn{border:1px solid #c7d2fe;background:#eef2ff;color:#4338ca;border-radius:9px;width:34px;height:34px;flex:0 0 auto;cursor:pointer;font-weight:800}
    .pb-media-field{display:flex;gap:6px}
    .pb-media-field .pb-thumb{width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid #e5e7eb;background:#f8fafc}
    .pb-rep-item{border:1px solid #e5e7eb;border-radius:10px;padding:10px;margin-bottom:8px;background:#f8fafc}
    .pb-rep-add{border:1px dashed #c7d2fe;background:#eef2ff;color:#4338ca;border-radius:9px;padding:7px;width:100%;cursor:pointer;font-weight:700}
    .pb-rep-del{border:0;background:#fee2e2;color:#b91c1c;border-radius:8px;padding:4px 10px;cursor:pointer;font-size:.8rem;font-weight:700;margin-top:4px}
    .pb-children{border-top:1px dashed #e5e7eb;margin-top:10px;padding-top:10px}
    .pb-style-sec{border-top:2px solid #eef2ff;margin-top:14px;padding-top:12px}
    .pb-style-title{font-weight:800;font-size:.82rem;color:#6366f1;margin-bottom:10px}
    .pb-field-toggle{flex-direction:row;align-items:center;gap:8px}
    .pb-field-toggle input{width:auto}

    .pb-modal{position:fixed;inset:0;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;z-index:1000;padding:20px}
    .pb-modal-box{background:#fff;border-radius:16px;width:560px;max-width:100%;max-height:86vh;display:flex;flex-direction:column;overflow:hidden}
    .pb-modal-head{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:1px solid #eef2f7}
    .pb-modal-x{border:0;background:transparent;font-size:1.1rem;cursor:pointer;color:#64748b}
    .pb-modal-body{padding:16px 18px;overflow:auto}
    .pb-upload{display:block;border:2px dashed #c7d2fe;border-radius:12px;padding:16px;text-align:center;cursor:pointer;color:#4338ca;font-weight:700;background:#eef2ff}
    .pb-media-alt{width:100%;margin:10px 0;border:1px solid #d1d5db;border-radius:9px;padding:8px 10px}
    .pb-media-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(96px,1fr));gap:8px}
    .pb-media-cell{position:relative;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;cursor:pointer;aspect-ratio:1;background:#f8fafc}
    .pb-media-cell img{width:100%;height:100%;object-fit:cover}
    .pb-media-del{position:absolute;top:3px;inset-inline-end:3px;border:0;background:rgba(220,38,38,.92);color:#fff;border-radius:6px;width:24px;height:24px;cursor:pointer;font-size:.72rem;opacity:0;transition:opacity .15s}
    .pb-media-cell:hover .pb-media-del{opacity:1}
    .pb-inserter-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin-top:12px}
    .pb-ins-btn{display:flex;flex-direction:column;align-items:center;gap:6px;border:1px solid #e5e7eb;background:#f8fafc;border-radius:12px;padding:14px 8px;cursor:pointer;font-weight:700;font-size:.82rem;color:#334155;text-align:center}
    .pb-ins-btn:hover{border-color:#a5b4fc;background:#eef2ff}
    .pb-ins-btn .pb-emoji{font-size:1.5rem}
    .pb-ins-cat{grid-column:1/-1;font-size:.72rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;margin-top:6px}
    .pb-toast{position:fixed;bottom:20px;inset-inline-start:50%;transform:translateX(50%);background:#111827;color:#fff;padding:10px 18px;border-radius:10px;z-index:1100;font-weight:700;font-size:.9rem}
    .pb-toast.err{background:#b91c1c}
    @media (max-width:1100px){.pb-step2-grid{grid-template-columns:1fr;height:auto}.pb-preview-col{height:60vh}}
    @media (prefers-color-scheme: dark){
        .pb-toolbar,.pb-palette,.pb-side-sec,.pb-settings-card,.pb-card,.pb-modal-box{background:#0b1220;border-color:#1f2937;color:#e2e8f0}
        .pb-steps{background:#111827}.pb-step-tab.is-active{background:#0b1220;color:#a5b4fc}
        .pb-add-btn{background:#111827;border-color:#1f2937}
        .pb-field input,.pb-field select,.pb-field textarea{background:#0b1220;border-color:#334155;color:#e2e8f0}
        .pb-rep-item{background:#111827;border-color:#1f2937}
        .pb-preview-col{background:#111827;border-color:#334155}
    }
</style>
@endpush
