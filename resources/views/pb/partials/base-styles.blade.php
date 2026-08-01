{{-- أنماط أساس صفحات v2 — مصدر واحد يشاركه المستند العامّ والمعاينة (كتلها تحمل أنماطها المُقيَّدة). --}}
<style>
    *,*::before,*::after{box-sizing:border-box}
    body{margin:0;font-family:'Tajawal','Segoe UI',system-ui,-apple-system,sans-serif;
        color:#1f2937;background:#fff;line-height:1.6}
    img{max-width:100%;height:auto;display:block}
    a{color:#667eea}
    .pb-page{min-height:100vh;display:flex;flex-direction:column}
    .pb-page-body{flex:1}
    .pb-block{max-width:1140px;margin-inline:auto;padding:28px 20px}
    .pb-hero{text-align:center;padding:72px 20px}
    .pb-hero-title{font-size:clamp(1.8rem,4vw,2.8rem);margin:0 0 12px;font-weight:800}
    .pb-hero-subtitle{font-size:1.15rem;color:#4b5563;margin:0 auto 24px;max-width:640px}
    .pb-btn{display:inline-block;padding:12px 28px;border-radius:12px;text-decoration:none;font-weight:700}
    .pb-btn-primary{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff}
    .pb-btn-secondary{background:#eef2ff;color:#4338ca}
    .pb-btn-ghost{background:transparent;border:2px solid #c7d2fe;color:#4338ca}
    .pb-features-grid,.pb-columns{display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))}
    .pb-columns{grid-template-columns:repeat(var(--pb-cols,2),1fr)}
    .pb-feature-card{padding:24px;border:1px solid #e5e7eb;border-radius:16px;text-align:center}
    .pb-feature-title{margin:0 0 8px;font-size:1.15rem}
    .pb-feature-icon{font-size:2rem;margin-bottom:8px}
    .pb-spacer{padding:0}
    .pb-cta{text-align:center;background:#f9fafb;border-radius:18px}
    .pb-image figcaption,.pb-image-caption{text-align:center;color:#6b7280;font-size:.9rem;margin-top:8px}
    @media (max-width:640px){.pb-columns{grid-template-columns:1fr}}
    @media (prefers-color-scheme: dark){
        body{background:#0f172a;color:#e2e8f0}
        .pb-hero-subtitle{color:#94a3b8}
        .pb-cta{background:#1e293b}
        .pb-feature-card{border-color:#334155}
    }
</style>
