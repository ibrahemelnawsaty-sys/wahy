{{-- أنماط أساس صفحات v2 — مصدر واحد يشاركه المستند العامّ والمعاينة (كتلها تحمل أنماطها المُقيَّدة).
     رموز التصميم (ت-١٠) تُحقَن كمتغيّرات CSS مُعقَّمة من PageDesign. --}}
@php $__pbFontUrl = \App\PageBuilder\PageDesign::googleFontUrl(); @endphp
@if($__pbFontUrl)<link href="{{ $__pbFontUrl }}" rel="stylesheet">@endif
<style>
    :root{ {!! \App\PageBuilder\PageDesign::cssVars() !!} }
    *,*::before,*::after{box-sizing:border-box}
    body{margin:0;font-family:var(--pb-font,'Tajawal',sans-serif);
        color:var(--pb-text,#1f2937);background:var(--pb-bg,#fff);line-height:1.6}
    img{max-width:100%;height:auto;display:block}
    a{color:var(--pb-primary,#667eea)}
    .pb-page{min-height:100vh;display:flex;flex-direction:column}
    .pb-page-body{flex:1}
    .pb-block{max-width:1140px;margin-inline:auto;padding:28px 20px}
    .pb-hero{text-align:center;padding:72px 20px}
    .pb-hero-title{font-size:clamp(1.8rem,4vw,2.8rem);margin:0 0 12px;font-weight:800}
    .pb-hero-subtitle{font-size:1.15rem;opacity:.72;margin:0 auto 24px;max-width:640px}
    .pb-btn{display:inline-block;padding:12px 28px;border-radius:var(--pb-radius,12px);text-decoration:none;font-weight:700}
    .pb-btn-primary{background:linear-gradient(135deg,var(--pb-primary,#667eea),var(--pb-secondary,#764ba2));color:#fff}
    .pb-btn-secondary{background:#eef2ff;color:#4338ca}
    .pb-btn-ghost{background:transparent;border:2px solid var(--pb-primary,#c7d2fe);color:var(--pb-primary,#4338ca)}
    .pb-features-grid,.pb-columns{display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))}
    .pb-columns{grid-template-columns:repeat(var(--pb-cols,2),1fr)}
    .pb-feature-card{padding:24px;border:1px solid #e5e7eb;border-radius:var(--pb-radius,16px);text-align:center}
    .pb-feature-title{margin:0 0 8px;font-size:1.15rem}
    .pb-feature-icon{font-size:2rem;margin-bottom:8px}
    .pb-spacer{padding:0}
    .pb-cta{text-align:center;background:#f9fafb;border-radius:calc(var(--pb-radius,12px) + 4px)}
    .pb-image figcaption,.pb-image-caption{text-align:center;opacity:.6;font-size:.9rem;margin-top:8px}
    /* دفعة 3: غلاف تصميم الكتلة (خلفيّة/حشو/عرض مُعقَّمة) */
    .pb-blockwrap{width:100%}
    .pb-blockwrap>.pb-block{margin-block:0}
    /* دفعة 2: كتل غنيّة */
    .pb-heading{font-weight:800;line-height:1.3}
    .pb-list{padding-inline-start:1.4em;line-height:1.9}
    .pb-list li{margin-bottom:6px}
    .pb-quote{margin:0;padding:20px 28px;border-inline-start:4px solid var(--pb-primary,#667eea);background:#f8fafc;border-radius:var(--pb-radius,12px)}
    .pb-quote blockquote{margin:0;font-size:1.2rem;font-style:italic;line-height:1.7}
    .pb-quote figcaption{margin-top:10px;opacity:.7;font-weight:700}
    .pb-separator{display:flex;align-items:center;justify-content:center}
    .pb-sep-line span{display:block;width:100%;height:1px;background:#e5e7eb}
    .pb-sep-dots span{display:block;width:100%;height:6px;background:radial-gradient(circle,#cbd5e1 1.5px,transparent 1.6px) repeat-x;background-size:16px 6px}
    .pb-sep-space{min-height:8px}
    .pb-buttons{display:flex;flex-wrap:wrap;gap:12px}
    .pb-iconlist{list-style:none;padding:0;margin:0;display:grid;gap:12px}
    .pb-iconlist li{display:flex;align-items:flex-start;gap:10px;line-height:1.6}
    .pb-iconlist-icon{flex:0 0 auto;font-size:1.2rem;line-height:1.4}
    .pb-testimonial{margin:0;padding:28px;border:1px solid #e5e7eb;border-radius:calc(var(--pb-radius,12px) + 4px);max-width:720px;text-align:center}
    .pb-testimonial-quote{margin:0 0 16px;font-size:1.15rem;font-style:italic;line-height:1.8}
    .pb-testimonial-by{display:flex;align-items:center;justify-content:center;gap:12px}
    .pb-testimonial-avatar{width:52px;height:52px;border-radius:50%;object-fit:cover}
    .pb-testimonial-meta{display:flex;flex-direction:column;text-align:start}
    .pb-testimonial-meta small{opacity:.65}
    .pb-pricing{display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))}
    .pb-price-card{padding:28px 24px;border:1px solid #e5e7eb;border-radius:calc(var(--pb-radius,12px) + 4px);text-align:center;display:flex;flex-direction:column;gap:14px}
    .pb-price-card.is-featured{border-color:var(--pb-primary,#667eea);box-shadow:0 8px 30px rgba(102,126,234,.18);transform:scale(1.03)}
    .pb-price-name{margin:0;font-size:1.2rem}
    .pb-price-amount{font-size:2.1rem;font-weight:800;color:var(--pb-primary,#4338ca)}
    .pb-price-amount small{font-size:.9rem;font-weight:500;opacity:.6}
    .pb-price-features{list-style:none;padding:0;margin:0;display:grid;gap:8px;text-align:start}
    .pb-price-features li{padding-inline-start:22px;position:relative}
    .pb-price-features li::before{content:"✔";position:absolute;inset-inline-start:0;color:#10b981;font-weight:800}
    .pb-price-card .pb-btn{margin-top:auto}
    .pb-social{display:flex;flex-wrap:wrap;gap:10px}
    .pb-social-link{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:50%;
        background:var(--pb-primary,#667eea);color:#fff;text-decoration:none;font-weight:800;transition:transform .15s}
    .pb-social-link:hover{transform:translateY(-2px)}
    .pb-table-wrap{overflow-x:auto}
    .pb-table{width:100%;border-collapse:collapse}
    .pb-table th,.pb-table td{border:1px solid #e5e7eb;padding:10px 14px;text-align:start}
    .pb-table thead th{background:#f1f5f9;font-weight:800}
    /* دفعة 4: كتل تفاعليّة + تضمينات */
    .pb-accordion{display:grid;gap:10px}
    .pb-acc-item{border:1px solid #e5e7eb;border-radius:var(--pb-radius,12px);overflow:hidden}
    .pb-acc-item summary{cursor:pointer;padding:14px 18px;font-weight:700;background:#f8fafc;list-style:none;display:flex;justify-content:space-between;align-items:center}
    .pb-acc-item summary::-webkit-details-marker{display:none}
    .pb-acc-item summary::after{content:"＋";color:var(--pb-primary,#667eea);font-weight:800}
    .pb-acc-item[open] summary::after{content:"－"}
    .pb-acc-body{padding:14px 18px;line-height:1.8;white-space:pre-line}
    .pb-tabs-nav{display:flex;flex-wrap:wrap;gap:4px;border-bottom:2px solid #e5e7eb;margin-bottom:16px}
    .pb-tab-btn{border:0;background:transparent;padding:10px 18px;font-weight:700;color:#64748b;cursor:pointer;
        border-bottom:2px solid transparent;margin-bottom:-2px}
    .pb-tab-btn.is-active{color:var(--pb-primary,#4338ca);border-bottom-color:var(--pb-primary,#4338ca)}
    .pb-tab-panel{display:none;line-height:1.8;white-space:pre-line}
    .pb-tab-panel.is-active{display:block}
    .pb-video-frame{position:relative;padding-top:56.25%;border-radius:var(--pb-radius,12px);overflow:hidden;background:#000}
    .pb-video-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
    .pb-video-cap{text-align:center;opacity:.6;font-size:.9rem;margin-top:8px}
    /* هيدر/فوتر الموقع الموحّد للصفحات الثانوية */
    .pb-site-header{background:var(--pb-bg,#fff);border-bottom:1px solid #e5e7eb;position:sticky;top:0;z-index:50}
    .pb-site-header-inner{max-width:1140px;margin-inline:auto;padding:12px 20px;display:flex;align-items:center;gap:20px}
    .pb-site-brand{display:flex;align-items:center;gap:8px;text-decoration:none;color:inherit;font-weight:800;font-size:1.15rem}
    .pb-site-logo{height:38px;width:auto}
    .pb-site-logo-icon{font-size:1.3rem}
    .pb-site-nav{display:flex;gap:18px;margin-inline-start:auto;flex-wrap:wrap}
    .pb-site-nav a{text-decoration:none;color:inherit;font-weight:700;opacity:.85}
    .pb-site-nav a:hover{opacity:1;color:var(--pb-primary,#667eea)}
    .pb-site-actions{display:flex;gap:8px}
    .pb-site-actions .pb-btn{padding:8px 18px;font-size:.9rem}
    .pb-site-footer{background:#0f172a;color:#e2e8f0;margin-top:40px}
    .pb-site-footer-inner{max-width:1140px;margin-inline:auto;padding:40px 20px;display:flex;gap:32px;flex-wrap:wrap;justify-content:space-between}
    .pb-site-footer-name{font-weight:800;font-size:1.15rem;display:flex;align-items:center;gap:8px}
    .pb-site-footer-desc{opacity:.7;margin:10px 0 0;max-width:420px;line-height:1.7}
    .pb-site-footer-links{display:flex;flex-direction:column;gap:8px}
    .pb-site-footer-links a{color:#cbd5e1;text-decoration:none}
    .pb-site-footer-links a:hover{color:#fff}
    .pb-site-footer-bottom{border-top:1px solid rgba(255,255,255,.1);padding:16px 20px;text-align:center;opacity:.7;font-size:.88rem}
    @media (max-width:640px){.pb-columns{grid-template-columns:1fr}.pb-site-header-inner{flex-wrap:wrap}.pb-site-nav{margin-inline-start:0}}
    /* زرّ تبديل الوضع (ليليّ/نهاريّ) العائم */
    .pb-theme-toggle{position:fixed;bottom:20px;inset-inline-start:20px;z-index:950;width:46px;height:46px;border-radius:50%;
        border:1px solid #e5e7eb;background:#fff;color:#334155;font-size:1.2rem;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.15)}
    /* الوضع الليليّ: تفضيل النظام ما لم يُفرض النهاريّ صراحةً */
    @media (prefers-color-scheme: dark){
        :root:not([data-theme="light"]) body{background:#0f172a;color:#e2e8f0}
        :root:not([data-theme="light"]) .pb-cta,:root:not([data-theme="light"]) .pb-quote{background:#1e293b}
        :root:not([data-theme="light"]) .pb-feature-card,:root:not([data-theme="light"]) .pb-testimonial,:root:not([data-theme="light"]) .pb-price-card{border-color:#334155}
        :root:not([data-theme="light"]) .pb-sep-line span{background:#334155}
        :root:not([data-theme="light"]) .pb-table th,:root:not([data-theme="light"]) .pb-table td{border-color:#334155}
        :root:not([data-theme="light"]) .pb-table thead th{background:#1e293b}
        :root:not([data-theme="light"]) .pb-theme-toggle{background:#1e293b;color:#e2e8f0;border-color:#334155}
    }
    /* الوضع الليليّ بالتبديل الصريح (يعمل مع مفتاح wahy-theme المشترك مع الموقع) */
    :root[data-theme="dark"] body{background:#0f172a;color:#e2e8f0}
    :root[data-theme="dark"] .pb-cta,:root[data-theme="dark"] .pb-quote{background:#1e293b}
    :root[data-theme="dark"] .pb-feature-card,:root[data-theme="dark"] .pb-testimonial,:root[data-theme="dark"] .pb-price-card{border-color:#334155}
    :root[data-theme="dark"] .pb-sep-line span{background:#334155}
    :root[data-theme="dark"] .pb-table th,:root[data-theme="dark"] .pb-table td{border-color:#334155}
    :root[data-theme="dark"] .pb-table thead th{background:#1e293b}
    :root[data-theme="dark"] .pb-theme-toggle{background:#1e293b;color:#e2e8f0;border-color:#334155}
</style>
