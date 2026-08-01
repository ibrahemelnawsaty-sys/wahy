@extends('layouts.admin')

@section('title', $page ? 'تحرير: ' . $page->title : 'صفحة جديدة')
@section('page-title', $page ? 'تحرير صفحة' : 'صفحة جديدة')

@section('content')
<div class="pb-editor" id="pbEditor">
    {{-- شريط الأدوات --}}
    <div class="pb-toolbar">
        <a href="{{ route('admin.pb.ui.index') }}" class="pb-tool-back">↩ الصفحات</a>

        <div class="pb-tabs" role="tablist">
            <button class="pb-tab is-active" data-pb-region="body">الجسم</button>
            <button class="pb-tab" data-pb-region="header">الهيدر</button>
            <button class="pb-tab" data-pb-region="footer">الفوتر</button>
        </div>

        <div class="pb-tool-actions">
            <span class="pb-status" id="pbStatusPill"></span>
            <button class="btn btn-outline-secondary btn-sm" id="pbPreview">👁 معاينة</button>
            <button class="btn btn-primary btn-sm" id="pbSave">💾 حفظ</button>
            <button class="btn btn-success btn-sm" id="pbPublish">🚀 نشر</button>
            <button class="btn btn-outline-primary btn-sm" id="pbGoLive"></button>
        </div>
    </div>

    <div class="pb-grid">
        {{-- لوحة الكتل --}}
        <aside class="pb-palette">
            <div class="pb-panel-label">أضف كتلة</div>
            <div id="pbPalette" class="pb-palette-list"></div>
        </aside>

        {{-- اللوح --}}
        <main class="pb-canvas-col">
            <div class="pb-page-settings" id="pbPageSettings">
                <div class="pb-panel-label">إعدادات الصفحة</div>
                <div class="pb-field"><label>العنوان</label><input type="text" id="pbTitle" placeholder="عنوان الصفحة"></div>
                <div class="pb-field"><label>المسار (slug)</label><input type="text" id="pbSlug" placeholder="about-us"></div>
                <div class="pb-field"><label>اللغة</label>
                    <select id="pbLocale"><option value="ar">العربيّة</option><option value="en">English</option></select>
                </div>
                <div class="pb-field"><label>عنوان SEO</label><input type="text" id="pbMetaTitle" placeholder="اختياريّ"></div>
                <div class="pb-field"><label>وصف SEO</label><textarea id="pbMetaDescription" rows="2" placeholder="اختياريّ"></textarea></div>
            </div>

            <div class="pb-canvas" id="pbCanvas"></div>
        </main>

        {{-- المفتّش (خصائص الكتلة المختارة) --}}
        <aside class="pb-inspector">
            <div class="pb-panel-label">الخصائص</div>
            <div id="pbInspector" class="pb-inspector-body"><p class="pb-hint">اختر كتلةً لتحرير خصائصها.</p></div>
        </aside>
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
            <div class="pb-media-grid" id="pbMediaGrid"></div>
        </div>
    </div>
</div>

{{-- المعاينة --}}
<div class="pb-modal" id="pbPreviewModal" hidden>
    <div class="pb-modal-box pb-modal-lg">
        <div class="pb-modal-head"><b>معاينة</b><button class="pb-modal-x" data-pb-close>✕</button></div>
        <iframe id="pbPreviewFrame" class="pb-preview-frame" title="معاينة"></iframe>
    </div>
</div>

<div class="pb-toast" id="pbToast" hidden></div>

<script>window.PB_BOOT = @json($boot);</script>
<script src="{{ asset('js/pb-editor.js') }}?v=1"></script>
@endsection

@push('styles')
<style>
    .pb-toolbar{position:sticky;top:0;z-index:20;display:flex;align-items:center;gap:14px;flex-wrap:wrap;
        padding:10px 14px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;margin-bottom:14px}
    .pb-tool-back{text-decoration:none;color:#475569;font-weight:700;font-size:.9rem}
    .pb-tabs{display:flex;gap:4px;background:#f1f5f9;border-radius:10px;padding:3px}
    .pb-tab{border:0;background:transparent;padding:6px 16px;border-radius:8px;font-weight:700;color:#64748b;cursor:pointer}
    .pb-tab.is-active{background:#fff;color:#4338ca;box-shadow:0 1px 3px rgba(0,0,0,.08)}
    .pb-tool-actions{margin-inline-start:auto;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .pb-status{font-size:.82rem;color:#94a3b8}
    .pb-grid{display:grid;grid-template-columns:210px 1fr 300px;gap:14px;align-items:start}
    .pb-palette,.pb-inspector,.pb-page-settings{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px}
    .pb-panel-label{font-weight:800;font-size:.8rem;color:#64748b;text-transform:uppercase;letter-spacing:.03em;margin-bottom:10px}
    .pb-palette-list{display:flex;flex-direction:column;gap:6px}
    .pb-add-btn{display:flex;align-items:center;gap:8px;border:1px solid #e5e7eb;background:#f8fafc;border-radius:10px;
        padding:9px 10px;cursor:pointer;font-weight:700;font-size:.9rem;color:#334155;text-align:start}
    .pb-add-btn:hover{border-color:#a5b4fc;background:#eef2ff}
    .pb-add-btn .pb-emoji{font-size:1.1rem}
    .pb-canvas-col{display:flex;flex-direction:column;gap:14px;min-width:0}
    .pb-canvas{display:flex;flex-direction:column;gap:8px;min-height:120px}
    .pb-card{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:10px 12px;cursor:pointer}
    .pb-card.is-selected{border-color:#6366f1;box-shadow:0 0 0 2px rgba(99,102,241,.2)}
    .pb-card .pb-emoji{font-size:1.25rem}
    .pb-card-main{flex:1;min-width:0}
    .pb-card-type{font-weight:800;font-size:.9rem}
    .pb-card-sum{font-size:.8rem;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .pb-card-ops{display:flex;gap:4px}
    .pb-icon-btn{border:0;background:#f1f5f9;border-radius:8px;width:28px;height:28px;cursor:pointer;font-size:.85rem;color:#475569}
    .pb-icon-btn:hover{background:#e2e8f0}
    .pb-icon-btn.danger:hover{background:#fee2e2;color:#b91c1c}
    .pb-canvas-empty{border:2px dashed #e5e7eb;border-radius:14px;padding:40px 16px;text-align:center;color:#94a3b8}
    .pb-field{margin-bottom:12px;display:flex;flex-direction:column;gap:5px}
    .pb-field label{font-weight:700;font-size:.82rem;color:#475569}
    .pb-field input,.pb-field select,.pb-field textarea{width:100%;border:1px solid #d1d5db;border-radius:9px;padding:8px 10px;font:inherit;background:#fff}
    .pb-field input:focus,.pb-field textarea:focus,.pb-field select:focus{outline:0;border-color:#818cf8;box-shadow:0 0 0 3px rgba(129,140,248,.18)}
    .pb-hint{color:#94a3b8;font-size:.88rem}
    .pb-media-field{display:flex;gap:6px}
    .pb-media-field .pb-thumb{width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid #e5e7eb;background:#f8fafc}
    .pb-rep-item{border:1px solid #e5e7eb;border-radius:10px;padding:10px;margin-bottom:8px;background:#f8fafc}
    .pb-rep-add{border:1px dashed #c7d2fe;background:#eef2ff;color:#4338ca;border-radius:9px;padding:7px;width:100%;cursor:pointer;font-weight:700}
    .pb-rep-del{border:0;background:#fee2e2;color:#b91c1c;border-radius:8px;padding:4px 10px;cursor:pointer;font-size:.8rem;font-weight:700;margin-top:4px}
    .pb-children{border-top:1px dashed #e5e7eb;margin-top:10px;padding-top:10px}
    .pb-modal{position:fixed;inset:0;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;z-index:1000;padding:20px}
    .pb-modal-box{background:#fff;border-radius:16px;width:520px;max-width:100%;max-height:86vh;display:flex;flex-direction:column;overflow:hidden}
    .pb-modal-lg{width:1000px;height:82vh}
    .pb-modal-head{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:1px solid #eef2f7}
    .pb-modal-x{border:0;background:transparent;font-size:1.1rem;cursor:pointer;color:#64748b}
    .pb-modal-body{padding:16px 18px;overflow:auto}
    .pb-upload{display:block;border:2px dashed #c7d2fe;border-radius:12px;padding:16px;text-align:center;cursor:pointer;color:#4338ca;font-weight:700;background:#eef2ff}
    .pb-media-alt{width:100%;margin:10px 0;border:1px solid #d1d5db;border-radius:9px;padding:8px 10px}
    .pb-media-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(96px,1fr));gap:8px}
    .pb-media-cell{border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;cursor:pointer;aspect-ratio:1;background:#f8fafc}
    .pb-media-cell img{width:100%;height:100%;object-fit:cover}
    .pb-media-cell:hover{border-color:#6366f1}
    .pb-preview-frame{flex:1;border:0;width:100%;background:#fff}
    .pb-toast{position:fixed;bottom:20px;inset-inline-start:50%;transform:translateX(50%);background:#111827;color:#fff;
        padding:10px 18px;border-radius:10px;z-index:1100;font-weight:700;font-size:.9rem}
    .pb-toast.err{background:#b91c1c}
    @media (max-width:980px){.pb-grid{grid-template-columns:1fr}}
    @media (prefers-color-scheme: dark){
        .pb-toolbar,.pb-palette,.pb-inspector,.pb-page-settings,.pb-card,.pb-modal-box{background:#0b1220;border-color:#1f2937;color:#e2e8f0}
        .pb-tabs{background:#111827}.pb-tab.is-active{background:#0b1220;color:#a5b4fc}
        .pb-add-btn{background:#111827;border-color:#1f2937;color:#cbd5e1}
        .pb-field input,.pb-field select,.pb-field textarea{background:#0b1220;border-color:#334155;color:#e2e8f0}
        .pb-rep-item{background:#111827;border-color:#1f2937}
    }
</style>
@endpush
