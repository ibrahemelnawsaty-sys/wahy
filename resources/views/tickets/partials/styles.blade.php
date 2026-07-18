{{-- أنماط صفحات تذاكر الدعم الفنيّ (نهاية المستخدم) — تعمل تحت أيّ لايوت دور،
     ومتوافقة مع الوضع الليلي عبر تخصيص مقصور على .tickets-page (لا يعتمد على تغطية اللايوت). --}}
<style>
.tickets-page{
    --tk-card:#ffffff;
    --tk-text:#1e293b;
    --tk-muted:#64748b;
    --tk-border:#e8ecf3;
    --tk-soft:#f6f8fc;
    --tk-primary:var(--color-primary, #667eea);
    --tk-secondary:var(--color-secondary, #764ba2);
    max-width:960px;
    margin:0 auto;
    color:var(--tk-text);
    font-family:inherit;
}
html[data-theme="dark"] .tickets-page{
    --tk-card:#1e293b;
    --tk-text:#f1f5f9;
    --tk-muted:#94a3b8;
    --tk-border:rgba(255,255,255,.10);
    --tk-soft:rgba(255,255,255,.045);
}
.tickets-page *{box-sizing:border-box;}
.tickets-page a{text-decoration:none;}

/* ===== الرأس ===== */
.tk-header{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:24px;}
.tk-title{font-size:26px;font-weight:800;margin:0 0 6px;color:var(--tk-text);display:flex;align-items:center;gap:10px;line-height:1.2;}
.tk-subtitle{margin:0;color:var(--tk-muted);font-size:14px;}

/* ===== الأزرار ===== */
.tk-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border-radius:12px;font-weight:700;font-size:14px;cursor:pointer;border:none;text-decoration:none;transition:transform .2s, box-shadow .2s, background .2s;font-family:inherit;}
.tk-btn-primary{background:linear-gradient(135deg,var(--tk-primary),var(--tk-secondary));color:#fff;box-shadow:0 6px 18px rgba(102,126,234,.32);}
.tk-btn-primary:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(102,126,234,.42);}
.tk-btn-ghost{background:var(--tk-soft);color:var(--tk-text);border:1px solid var(--tk-border);}
.tk-btn-ghost:hover{background:var(--tk-border);}
.tk-btn-danger{background:#fee2e2;color:#dc2626;}
.tk-btn-danger:hover{background:#fecaca;}
html[data-theme="dark"] .tk-btn-danger{background:rgba(239,68,68,.16);color:#fca5a5;}
.tk-btn-block{width:100%;}

/* ===== إحصاءات موجزة ===== */
.tk-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;}
.tk-stat{background:var(--tk-card);border:1px solid var(--tk-border);border-radius:16px;padding:18px 20px;box-shadow:0 4px 14px rgba(15,23,42,.05);display:flex;align-items:center;gap:14px;}
.tk-stat-icon{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;}
.tk-stat-icon.all{background:rgba(102,126,234,.12);}
.tk-stat-icon.open{background:rgba(245,158,11,.14);}
.tk-stat-icon.done{background:rgba(22,163,74,.14);}
.tk-stat-val{font-size:26px;font-weight:800;line-height:1;color:var(--tk-text);}
.tk-stat-lbl{font-size:13px;color:var(--tk-muted);margin-top:5px;}

/* ===== قائمة التذاكر ===== */
.tk-list{display:flex;flex-direction:column;gap:14px;}
.tk-card{display:block;background:var(--tk-card);border:1px solid var(--tk-border);border-radius:16px;padding:18px 20px;color:inherit;box-shadow:0 4px 14px rgba(15,23,42,.05);transition:transform .2s, box-shadow .2s, border-color .2s;}
.tk-card:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(15,23,42,.10);border-color:var(--tk-primary);}
.tk-card-top{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;}
.tk-card-subj{font-size:17px;font-weight:700;margin:0;color:var(--tk-text);word-break:break-word;}
.tk-card-meta{display:flex;flex-wrap:wrap;gap:14px;margin-top:12px;font-size:13px;color:var(--tk-muted);align-items:center;}
.tk-meta-chip{display:inline-flex;align-items:center;gap:5px;}

/* ===== الشارات ===== */
.tk-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;font-size:12px;font-weight:700;white-space:nowrap;}
.tk-badge-warning{background:#fef3c7;color:#b45309;}
.tk-badge-info{background:#dbeafe;color:#1d4ed8;}
.tk-badge-success{background:#dcfce7;color:#15803d;}
.tk-badge-secondary{background:#eef2f7;color:#475569;}
html[data-theme="dark"] .tk-badge-warning{background:rgba(245,158,11,.18);color:#fcd34d;}
html[data-theme="dark"] .tk-badge-info{background:rgba(59,130,246,.20);color:#93c5fd;}
html[data-theme="dark"] .tk-badge-success{background:rgba(22,163,74,.20);color:#86efac;}
html[data-theme="dark"] .tk-badge-secondary{background:rgba(255,255,255,.08);color:#cbd5e1;}
.tk-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;background:var(--tk-soft);border:1px solid var(--tk-border);color:var(--tk-muted);}
.tk-pill.high{background:rgba(239,68,68,.12);color:#dc2626;border-color:transparent;}
html[data-theme="dark"] .tk-pill.high{background:rgba(239,68,68,.18);color:#fca5a5;}

/* ===== لوحة/نموذج ===== */
.tk-panel{background:var(--tk-card);border:1px solid var(--tk-border);border-radius:18px;padding:26px;box-shadow:0 4px 14px rgba(15,23,42,.05);}
.tk-field{margin-bottom:18px;}
.tk-label{display:block;font-weight:700;font-size:14px;margin-bottom:8px;color:var(--tk-text);}
.tk-label .req{color:#ef4444;}
.tk-input,.tk-select,.tk-textarea{width:100%;padding:12px 14px;border:2px solid var(--tk-border);border-radius:12px;font-size:14px;font-family:inherit;background:var(--tk-soft);color:var(--tk-text);transition:border-color .2s, box-shadow .2s;}
.tk-input:focus,.tk-select:focus,.tk-textarea:focus{outline:none;border-color:var(--tk-primary);box-shadow:0 0 0 3px rgba(102,126,234,.12);}
.tk-select{cursor:pointer;}
.tk-textarea{min-height:150px;resize:vertical;line-height:1.7;}
.tk-help{font-size:12px;color:var(--tk-muted);margin-top:6px;}
.tk-error{color:#ef4444;font-size:13px;margin-top:6px;display:block;font-weight:600;}
.tk-grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.tk-form-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:24px;flex-wrap:wrap;}

/* ===== تفاصيل التذكرة ===== */
.tk-back{display:inline-flex;align-items:center;gap:6px;color:var(--tk-muted);font-weight:600;font-size:14px;margin-bottom:16px;}
.tk-back:hover{color:var(--tk-primary);}
.tk-detail-head{background:var(--tk-card);border:1px solid var(--tk-border);border-radius:18px;padding:22px 24px;box-shadow:0 4px 14px rgba(15,23,42,.05);margin-bottom:22px;}
.tk-detail-head-top{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;flex-wrap:wrap;}
.tk-detail-subj{font-size:22px;font-weight:800;margin:0;color:var(--tk-text);word-break:break-word;line-height:1.3;}
.tk-detail-meta{display:flex;flex-wrap:wrap;gap:12px 18px;margin-top:16px;font-size:13px;color:var(--tk-muted);}
.tk-detail-meta b{color:var(--tk-text);font-weight:700;}

/* ===== سلسلة الردود ===== */
.tk-section-title{font-size:15px;font-weight:800;color:var(--tk-text);margin:0 0 14px;display:flex;align-items:center;gap:8px;}
.tk-thread{display:flex;flex-direction:column;gap:16px;margin-bottom:24px;}
.tk-msg{background:var(--tk-card);border:1px solid var(--tk-border);border-radius:16px;padding:16px 18px;box-shadow:0 3px 10px rgba(15,23,42,.04);border-inline-start:4px solid var(--tk-primary);}
.tk-msg.staff{border-inline-start-color:#2563eb;}
html[data-theme="dark"] .tk-msg.staff{background:rgba(37,99,235,.08);}
.tk-msg-head{display:flex;align-items:center;gap:10px;margin-bottom:10px;flex-wrap:wrap;}
.tk-msg-avatar{width:38px;height:38px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:15px;background:linear-gradient(135deg,var(--tk-primary),var(--tk-secondary));}
.tk-msg-avatar.staff{background:linear-gradient(135deg,#0ea5e9,#2563eb);}
.tk-msg-author{font-weight:700;font-size:14px;color:var(--tk-text);}
.tk-msg-role{font-size:11px;padding:2px 9px;border-radius:999px;font-weight:700;background:rgba(37,99,235,.14);color:#1d4ed8;}
html[data-theme="dark"] .tk-msg-role{background:rgba(59,130,246,.22);color:#93c5fd;}
.tk-msg-time{margin-inline-start:auto;font-size:12px;color:var(--tk-muted);}
.tk-msg-body{font-size:14px;line-height:1.85;color:var(--tk-text);word-wrap:break-word;overflow-wrap:anywhere;}
.tk-msg-body img{max-width:100%;height:auto;border-radius:10px;}
.tk-msg-body a{color:var(--tk-primary);text-decoration:underline;}
.tk-msg-body p{margin:0 0 8px;}
.tk-msg-body p:last-child{margin-bottom:0;}

/* ===== إشعار الإغلاق ===== */
.tk-closed-note{display:flex;align-items:center;gap:10px;background:var(--tk-soft);border:1px dashed var(--tk-border);border-radius:14px;padding:16px 18px;color:var(--tk-muted);font-weight:600;font-size:14px;}

/* ===== حالة فارغة ===== */
.tk-empty{text-align:center;padding:60px 24px;background:var(--tk-card);border:1px solid var(--tk-border);border-radius:18px;box-shadow:0 4px 14px rgba(15,23,42,.05);}
.tk-empty-icon{font-size:56px;margin-bottom:14px;opacity:.7;}
.tk-empty-title{font-size:19px;font-weight:800;color:var(--tk-text);margin:0 0 8px;}
.tk-empty-text{color:var(--tk-muted);font-size:14px;margin:0 0 22px;}

/* ===== ترقيم الصفحات ===== */
.tk-pagination{margin-top:24px;display:flex;justify-content:center;}
.tk-pagination nav{display:flex;justify-content:center;}
.tk-pagination svg{width:18px;height:18px;}

@media(max-width:640px){
    .tk-grid2{grid-template-columns:1fr;}
    .tk-stats{grid-template-columns:1fr;}
    .tk-title{font-size:22px;}
    .tk-detail-subj{font-size:19px;}
}
</style>
