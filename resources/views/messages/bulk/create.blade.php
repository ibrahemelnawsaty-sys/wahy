@extends('layouts.admin')

@section('title', 'إرسال رسالة جماعية')

@section('content')
<style>
/* ===== Bulk Compose — طبقة بصرية فاخرة مبنيّة على متغيّرات الثيم (--w-*) لتعمل ليلاً/نهاراً.
   لكنة وظيفية خضراء (#10b981/#059669) محفوظة. لا تغييرات على البنية/الـIDs/الحقول/الـJS. ===== */
.bc-page {
    padding: 0;
    --bc-green-1: #10b981;
    --bc-green-2: #059669;
    --bc-green-3: #047857;
}

/* ===== Hero ===== */
.bc-hero {
    background: linear-gradient(135deg, #10b981 0%, #059669 48%, #047857 100%);
    border-radius: 22px;
    padding: 34px 36px;
    margin-bottom: 26px;
    color: white;
    box-shadow: 0 18px 46px rgba(16, 185, 129, 0.34);
    position: relative;
    overflow: hidden;
}
.bc-hero::before {
    content: '';
    position: absolute;
    top: -40%;
    left: -20%;
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
    border-radius: 50%;
}
.bc-hero::after {
    content: '';
    position: absolute;
    bottom: -50%;
    right: -10%;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(255,255,255,0.07) 0%, transparent 70%);
    border-radius: 50%;
}
.bc-hero-icon {
    width: 64px; height: 64px;
    background: rgba(255,255,255,0.18);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.28);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px;
    margin-bottom: 16px;
    box-shadow: 0 10px 24px rgba(0,0,0,0.14);
    position: relative; z-index: 1;
}
.bc-hero h1 { font-size: 28px; font-weight: 800; margin: 0 0 6px; position: relative; z-index: 1; letter-spacing: -0.4px; }
.bc-hero p { opacity: 0.92; font-size: 15px; margin: 0; position: relative; z-index: 1; }
.bc-hero-actions { position: relative; z-index: 1; margin-top: 20px; }
.bc-hero-btn {
    padding: 10px 24px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.25s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.16);
    color: white;
    border: 1.5px solid rgba(255,255,255,0.35);
    backdrop-filter: blur(6px);
}
.bc-hero-btn:hover { background: rgba(255,255,255,0.28); color: white; transform: translateY(-2px); }

/* ===== Card ===== */
.bc-card {
    background: var(--w-card, #ffffff);
    border-radius: 20px;
    border: 1px solid var(--w-border, #eef2f7);
    box-shadow: 0 10px 40px rgba(2, 6, 23, 0.08);
    overflow: hidden;
}
.bc-card-header {
    padding: 20px 26px;
    border-bottom: 1px solid var(--w-border, #eef2f7);
    display: flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, rgba(16,185,129,0.06) 0%, rgba(5,150,105,0.03) 100%);
}
.bc-card-header h3 {
    font-size: 18px;
    font-weight: 800;
    color: var(--w-text, #1e293b);
    margin: 0;
}
.bc-card-body { padding: 28px; }

/* ===== Section Title ===== */
.bc-section-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--w-text, #1e293b);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.bc-section-title i { color: #10b981; }

/* ===== Recipient Type Cards ===== */
.bc-recipient-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.bc-recipient-card {
    position: relative;
    cursor: pointer;
}
.bc-recipient-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.bc-recipient-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 22px 16px;
    border-radius: 16px;
    border: 1.5px solid var(--w-border, #e2e8f0);
    background: var(--w-card, #ffffff);
    color: var(--w-text, #1e293b);
    transition: all 0.25s ease;
    cursor: pointer;
    height: 100%;
}
.bc-recipient-label:hover {
    border-color: #6ee7b7;
    background: rgba(16,185,129,0.05);
    transform: translateY(-3px);
    box-shadow: 0 10px 26px rgba(16,185,129,0.14);
}
.bc-recipient-card input[type="radio"]:checked + .bc-recipient-label {
    border-color: #10b981;
    background: linear-gradient(135deg, rgba(16,185,129,0.10), rgba(5,150,105,0.14));
    box-shadow: 0 10px 26px rgba(16,185,129,0.22);
    transform: translateY(-3px);
}
.bc-recipient-card input[type="radio"]:checked + .bc-recipient-label .bc-recipient-icon {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 6px 18px rgba(16,185,129,0.45);
}
.bc-recipient-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    margin-bottom: 10px;
    transition: all 0.25s ease;
    background: #f1f5f9;
    color: #64748b;
}
.bc-recipient-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--w-text, #1e293b);
    margin-bottom: 2px;
}
.bc-recipient-count {
    font-size: 12px;
    color: #94a3b8;
    font-weight: 500;
}

/* ===== Divider ===== */
.bc-divider {
    display: flex;
    align-items: center;
    margin: 28px 0;
    gap: 16px;
}
.bc-divider-line {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--w-border, #e2e8f0), transparent);
}
.bc-divider-text {
    font-size: 12px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 1px;
    white-space: nowrap;
}

/* ===== School Section ===== */
.bc-school-section {
    display: none;
    animation: fadeSlideIn 0.3s ease;
}
.bc-school-section.active {
    display: block;
}
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.bc-school-select {
    width: 100%;
    padding: 14px 18px;
    border: 1.5px solid var(--w-border, #e2e8f0);
    border-radius: 14px;
    font-size: 15px;
    font-weight: 500;
    color: var(--w-text, #1e293b);
    background-color: var(--w-card, #ffffff);
    transition: all 0.25s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: left 16px center;
}
.bc-school-select:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16,185,129,0.12);
    outline: none;
}

/* ===== Count Display ===== */
.bc-recipient-total {
    display: none;
    padding: 16px 20px;
    background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(5,150,105,0.12));
    border: 1.5px solid rgba(16,185,129,0.28);
    border-radius: 14px;
    margin-top: 16px;
    text-align: center;
    animation: fadeSlideIn 0.3s ease;
}
.bc-recipient-total.active { display: block; }
.bc-recipient-total-number {
    font-size: 26px;
    font-weight: 800;
    color: #059669;
}
.bc-recipient-total-label {
    font-size: 13px;
    color: #047857;
    font-weight: 600;
}

/* ===== Form Fields ===== */
.bc-form-group { margin-bottom: 24px; }
.bc-form-label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: var(--w-text, #1e293b);
    margin-bottom: 8px;
}
.bc-form-label .required { color: #ef4444; }
.bc-form-input, .bc-form-textarea {
    width: 100%;
    padding: 14px 18px;
    border: 1.5px solid var(--w-border, #e2e8f0);
    border-radius: 14px;
    font-size: 15px;
    font-weight: 500;
    color: var(--w-text, #1e293b);
    background: var(--w-card, #ffffff);
    transition: all 0.25s ease;
    font-family: inherit;
}
.bc-form-input:focus, .bc-form-textarea:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16,185,129,0.12);
    outline: none;
}
.bc-form-input::placeholder, .bc-form-textarea::placeholder {
    color: #cbd5e1;
}
.bc-form-textarea { resize: vertical; min-height: 160px; line-height: 1.8; }
.bc-form-help {
    font-size: 13px;
    color: #94a3b8;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ===== Preview ===== */
.bc-preview {
    padding: 20px;
    border-radius: 16px;
    background: linear-gradient(135deg, #eff6ff, #eef2ff);
    border: 1.5px solid #c7d2fe;
    margin-bottom: 24px;
}
.bc-preview-title {
    font-size: 15px;
    font-weight: 700;
    color: #4338ca;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.bc-preview-content {
    padding: 16px;
    background: var(--w-card, #ffffff);
    border-radius: 12px;
    border: 1px solid #e0e7ff;
}
.bc-preview-subject {
    font-weight: 700;
    color: var(--w-text, #1e293b);
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--w-border, #f1f5f9);
}
.bc-preview-body {
    color: #475569;
    line-height: 1.8;
    white-space: pre-wrap;
}
.bc-preview-empty {
    color: #94a3b8;
    text-align: center;
    padding: 20px;
}

/* ===== Buttons ===== */
.bc-btn-group {
    display: flex;
    gap: 12px;
    margin-top: 8px;
}
.bc-btn {
    padding: 14px 32px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 15px;
    border: none;
    cursor: pointer;
    transition: all 0.25s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.bc-btn-primary {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 10px 26px rgba(16,185,129,0.32);
}
.bc-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 34px rgba(16,185,129,0.42);
    color: white;
}
.bc-btn-secondary {
    background: #f1f5f9;
    color: #475569;
}
.bc-btn-secondary:hover {
    background: #e2e8f0;
    color: #334155;
    transform: translateY(-2px);
}

/* ===== Error ===== */
.bc-error {
    font-size: 13px;
    color: #ef4444;
    margin-top: 6px;
    font-weight: 500;
}

/* ===== Alert ===== */
.bc-alert {
    padding: 14px 20px;
    border-radius: 14px;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.bc-alert-danger { background: linear-gradient(135deg, #fef2f2, #fecdd3); color: #991b1b; border: 1.5px solid #fca5a5; }

/* ===== الوضع الليلي — تغطية صريحة لكل سطح/نص لا تلتقطه dark-coverage (أصناف بيضاء) ===== */
html[data-theme="dark"] .bc-card {
    background: var(--w-card) !important;
    border-color: var(--w-border) !important;
    box-shadow: 0 14px 44px rgba(0,0,0,0.45) !important;
}
html[data-theme="dark"] .bc-card-header {
    background: linear-gradient(135deg, rgba(16,185,129,0.14), rgba(5,150,105,0.05)) !important;
    border-bottom-color: var(--w-border) !important;
}
html[data-theme="dark"] .bc-recipient-card { background: transparent !important; }
html[data-theme="dark"] .bc-recipient-label {
    background: rgba(255,255,255,0.03) !important;
    border-color: var(--w-border) !important;
    color: var(--w-text) !important;
}
html[data-theme="dark"] .bc-recipient-label:hover {
    background: rgba(16,185,129,0.10) !important;
    border-color: rgba(52,211,153,0.5) !important;
}
html[data-theme="dark"] .bc-recipient-card input[type="radio"]:checked + .bc-recipient-label {
    background: linear-gradient(135deg, rgba(16,185,129,0.22), rgba(5,150,105,0.12)) !important;
    border-color: #10b981 !important;
}
html[data-theme="dark"] .bc-recipient-icon {
    background: rgba(255,255,255,0.06);
    color: #94a3b8;
}
html[data-theme="dark"] .bc-recipient-name { color: var(--w-text) !important; }
html[data-theme="dark"] .bc-recipient-count { color: var(--w-text-muted) !important; }
html[data-theme="dark"] .bc-section-title { color: var(--w-text) !important; }
html[data-theme="dark"] .bc-form-label { color: var(--w-text) !important; }
html[data-theme="dark"] .bc-form-help { color: var(--w-text-muted) !important; }
html[data-theme="dark"] .bc-form-input,
html[data-theme="dark"] .bc-form-textarea {
    background: rgba(255,255,255,0.04) !important;
    border-color: var(--w-border) !important;
    color: var(--w-text) !important;
}
html[data-theme="dark"] .bc-form-input::placeholder,
html[data-theme="dark"] .bc-form-textarea::placeholder { color: var(--w-text-muted) !important; }
html[data-theme="dark"] .bc-school-select {
    background-color: rgba(255,255,255,0.04) !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: left 16px center !important;
    color: var(--w-text) !important;
    border-color: var(--w-border) !important;
}
html[data-theme="dark"] .bc-recipient-total {
    background: linear-gradient(135deg, rgba(16,185,129,0.16), rgba(5,150,105,0.10)) !important;
    border-color: rgba(16,185,129,0.35) !important;
}
html[data-theme="dark"] .bc-recipient-total-number { color: #6ee7b7 !important; }
html[data-theme="dark"] .bc-recipient-total-label { color: #6ee7b7 !important; }
html[data-theme="dark"] .bc-preview {
    background: linear-gradient(135deg, rgba(79,70,229,0.16), rgba(99,102,241,0.08)) !important;
    border-color: rgba(129,140,248,0.30) !important;
}
html[data-theme="dark"] .bc-preview-title { color: #a5b4fc !important; }
html[data-theme="dark"] .bc-preview-content {
    background: rgba(2,6,23,0.35) !important;
    border-color: var(--w-border) !important;
}
html[data-theme="dark"] .bc-preview-subject {
    color: var(--w-text) !important;
    border-bottom-color: var(--w-border) !important;
}
html[data-theme="dark"] .bc-preview-body { color: #cbd5e1 !important; }
html[data-theme="dark"] .bc-preview-empty { color: var(--w-text-muted) !important; }
html[data-theme="dark"] .bc-btn-secondary {
    background: rgba(255,255,255,0.06) !important;
    color: var(--w-text) !important;
}
html[data-theme="dark"] .bc-btn-secondary:hover {
    background: rgba(255,255,255,0.12) !important;
    color: var(--w-text) !important;
}
html[data-theme="dark"] .bc-alert-danger {
    background: linear-gradient(135deg, rgba(239,68,68,0.16), rgba(220,38,38,0.10)) !important;
    color: #fca5a5 !important;
    border-color: rgba(248,113,113,0.35) !important;
}

/* ===== الاستجابة — لابتوب / تابلت / جوال ===== */
@media (max-width: 1024px) {
    .bc-recipient-grid { grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); }
}
@media (max-width: 991.98px) {
    /* عند تكدّس العمودين (col-lg) عمودياً، أوقِف لصق المعاينة */
    .bc-card[style*="sticky"] { position: static !important; top: auto !important; }
}
@media (max-width: 640px) {
    .bc-hero { padding: 24px 20px; border-radius: 18px; margin-bottom: 20px; }
    .bc-hero-icon { width: 54px; height: 54px; font-size: 26px; margin-bottom: 12px; }
    .bc-hero h1 { font-size: 22px; }
    .bc-hero p { font-size: 13.5px; }
    .bc-card-header { padding: 16px 18px; }
    .bc-card-body { padding: 20px 16px; }
    .bc-recipient-grid { grid-template-columns: repeat(auto-fit, minmax(138px, 1fr)); gap: 10px; margin-bottom: 18px; }
    .bc-recipient-label { padding: 16px 10px; }
    .bc-recipient-icon { width: 48px; height: 48px; font-size: 20px; }
    .bc-divider { margin: 20px 0; gap: 10px; }
    .bc-btn-group { flex-direction: column; }
    .bc-btn { width: 100%; justify-content: center; }
}
</style>

<div class="bc-page">
    <!-- Hero Header -->
    <div class="bc-hero">
        <div class="bc-hero-icon">✉️</div>
        <h1>إرسال رسالة جماعية</h1>
        <p>إرسال رسالة إلى مجموعة محددة من المستخدمين</p>
        <div class="bc-hero-actions">
            <a href="{{ route('messages.bulk.index') }}" class="bc-hero-btn">
                <i class="fas fa-arrow-right"></i> العودة للرسائل
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="bc-alert bc-alert-danger">
            ❌ {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('messages.bulk.send') }}" method="POST" id="bulkMessageForm">
        @csrf

        <div class="row g-4">
            <!-- Right Column: Form -->
            <div class="col-lg-8">
                <div class="bc-card">
                    <div class="bc-card-header">
                        <span style="font-size: 20px;">👥</span>
                        <h3>اختيار المستلمين</h3>
                    </div>
                    <div class="bc-card-body">
                        <!-- Main Recipient Types -->
                        <div class="bc-section-title">
                            <i class="fas fa-globe"></i>
                            إرسال لجميع المستخدمين
                        </div>
                        <div class="bc-recipient-grid">
                            <div class="bc-recipient-card">
                                <input type="radio" name="recipient_type" id="typeAll" value="all" {{ old('recipient_type') == 'all' ? 'checked' : '' }} required>
                                <label for="typeAll" class="bc-recipient-label">
                                    <div class="bc-recipient-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="bc-recipient-name">الجميع</div>
                                    <div class="bc-recipient-count">{{ $recipientCounts['all'] }} مستخدم</div>
                                </label>
                            </div>
                            <div class="bc-recipient-card">
                                <input type="radio" name="recipient_type" id="typeTeacher" value="teacher" {{ old('recipient_type') == 'teacher' ? 'checked' : '' }}>
                                <label for="typeTeacher" class="bc-recipient-label">
                                    <div class="bc-recipient-icon">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="bc-recipient-name">جميع المعلمين</div>
                                    <div class="bc-recipient-count">{{ $recipientCounts['teachers'] }} معلم</div>
                                </label>
                            </div>
                            <div class="bc-recipient-card">
                                <input type="radio" name="recipient_type" id="typeStudent" value="student" {{ old('recipient_type') == 'student' ? 'checked' : '' }}>
                                <label for="typeStudent" class="bc-recipient-label">
                                    <div class="bc-recipient-icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="bc-recipient-name">جميع الطلاب</div>
                                    <div class="bc-recipient-count">{{ $recipientCounts['students'] }} طالب</div>
                                </label>
                            </div>
                            <div class="bc-recipient-card">
                                <input type="radio" name="recipient_type" id="typeParent" value="parent" {{ old('recipient_type') == 'parent' ? 'checked' : '' }}>
                                <label for="typeParent" class="bc-recipient-label">
                                    <div class="bc-recipient-icon">
                                        <i class="fas fa-user-friends"></i>
                                    </div>
                                    <div class="bc-recipient-name">جميع أولياء الأمور</div>
                                    <div class="bc-recipient-count">{{ $recipientCounts['parents'] }} ولي أمر</div>
                                </label>
                            </div>
                            <div class="bc-recipient-card">
                                <input type="radio" name="recipient_type" id="typeSchoolAdmin" value="school_admin" {{ old('recipient_type') == 'school_admin' ? 'checked' : '' }}>
                                <label for="typeSchoolAdmin" class="bc-recipient-label">
                                    <div class="bc-recipient-icon">
                                        <i class="fas fa-school"></i>
                                    </div>
                                    <div class="bc-recipient-name">مدراء المدارس</div>
                                    <div class="bc-recipient-count">{{ $recipientCounts['school_admins'] }} مدير</div>
                                </label>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="bc-divider">
                            <div class="bc-divider-line"></div>
                            <div class="bc-divider-text">أو إرسال لمدرسة محددة</div>
                            <div class="bc-divider-line"></div>
                        </div>

                        <!-- School-Specific Types -->
                        <div class="bc-section-title">
                            <i class="fas fa-building"></i>
                            إرسال لمستخدمي مدرسة محددة
                        </div>
                        <div class="bc-recipient-grid">
                            <div class="bc-recipient-card">
                                <input type="radio" name="recipient_type" id="typeSchoolAll" value="school_all" {{ old('recipient_type') == 'school_all' ? 'checked' : '' }}>
                                <label for="typeSchoolAll" class="bc-recipient-label">
                                    <div class="bc-recipient-icon" style="background: #fef3c7; color: #d97706;">
                                        <i class="fas fa-school"></i>
                                    </div>
                                    <div class="bc-recipient-name">جميع منسوبي المدرسة</div>
                                    <div class="bc-recipient-count">اختر المدرسة</div>
                                </label>
                            </div>
                            <div class="bc-recipient-card">
                                <input type="radio" name="recipient_type" id="typeSchoolTeachers" value="school_teachers" {{ old('recipient_type') == 'school_teachers' ? 'checked' : '' }}>
                                <label for="typeSchoolTeachers" class="bc-recipient-label">
                                    <div class="bc-recipient-icon" style="background: #dbeafe; color: #2563eb;">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="bc-recipient-name">معلمو مدرسة محددة</div>
                                    <div class="bc-recipient-count">اختر المدرسة</div>
                                </label>
                            </div>
                            <div class="bc-recipient-card">
                                <input type="radio" name="recipient_type" id="typeSchoolStudents" value="school_students" {{ old('recipient_type') == 'school_students' ? 'checked' : '' }}>
                                <label for="typeSchoolStudents" class="bc-recipient-label">
                                    <div class="bc-recipient-icon" style="background: #fce7f3; color: #db2777;">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="bc-recipient-name">طلاب مدرسة محددة</div>
                                    <div class="bc-recipient-count">اختر المدرسة</div>
                                </label>
                            </div>
                            <div class="bc-recipient-card">
                                <input type="radio" name="recipient_type" id="typeSchoolParents" value="school_parents" {{ old('recipient_type') == 'school_parents' ? 'checked' : '' }}>
                                <label for="typeSchoolParents" class="bc-recipient-label">
                                    <div class="bc-recipient-icon" style="background: #dcfce7; color: #16a34a;">
                                        <i class="fas fa-user-friends"></i>
                                    </div>
                                    <div class="bc-recipient-name">أولياء أمور مدرسة محددة</div>
                                    <div class="bc-recipient-count">اختر المدرسة</div>
                                </label>
                            </div>
                        </div>

                        @error('recipient_type')
                            <div class="bc-error">{{ $message }}</div>
                        @enderror

                        <!-- School Selection -->
                        <div class="bc-school-section" id="schoolSection">
                            <div class="bc-section-title">
                                <i class="fas fa-map-marker-alt"></i>
                                اختيار المدرسة
                            </div>
                            <select name="school_id" id="schoolSelect" class="bc-school-select">
                                <option value="">-- اختر المدرسة --</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" 
                                            data-teachers="{{ $school->teachers_count }}"
                                            data-students="{{ $school->students_count }}"
                                            data-users="{{ $school->users_count }}"
                                            {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_id')
                                <div class="bc-error">{{ $message }}</div>
                            @enderror

                            <div class="bc-recipient-total" id="recipientTotal">
                                <div class="bc-recipient-total-number" id="recipientCount">0</div>
                                <div class="bc-recipient-total-label">سيتم إرسال الرسالة لهؤلاء المستلمين</div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="bc-divider">
                            <div class="bc-divider-line"></div>
                            <div class="bc-divider-text">محتوى الرسالة</div>
                            <div class="bc-divider-line"></div>
                        </div>

                        <!-- Subject -->
                        <div class="bc-form-group">
                            <label class="bc-form-label">
                                <i class="fas fa-heading" style="color: #10b981; margin-left: 6px;"></i>
                                الموضوع <span class="required">*</span>
                            </label>
                            <input type="text" name="subject" id="subject" class="bc-form-input" 
                                   value="{{ old('subject') }}" placeholder="أدخل موضوع الرسالة" required>
                            @error('subject')
                                <div class="bc-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div class="bc-form-group">
                            <label class="bc-form-label">
                                <i class="fas fa-align-right" style="color: #10b981; margin-left: 6px;"></i>
                                نص الرسالة <span class="required">*</span>
                            </label>
                            <textarea name="message" id="messageText" class="bc-form-textarea" 
                                      placeholder="اكتب رسالتك هنا..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="bc-error">{{ $message }}</div>
                            @enderror
                            <div class="bc-form-help">
                                <i class="fas fa-info-circle"></i>
                                يمكنك كتابة رسالة تفصيلية ومتعددة الأسطر
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="bc-btn-group">
                            <button type="submit" class="bc-btn bc-btn-primary">
                                <i class="fas fa-paper-plane"></i> إرسال الرسالة
                            </button>
                            <a href="{{ route('messages.bulk.index') }}" class="bc-btn bc-btn-secondary">
                                إلغاء
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Left Column: Preview -->
            <div class="col-lg-4">
                <div class="bc-card" style="position: sticky; top: 90px;">
                    <div class="bc-card-header">
                        <span style="font-size: 20px;">👁️</span>
                        <h3>معاينة الرسالة</h3>
                    </div>
                    <div class="bc-card-body">
                        <div class="bc-preview">
                            <div class="bc-preview-title">
                                <i class="fas fa-envelope"></i> معاينة مباشرة
                            </div>
                            <div class="bc-preview-content" id="previewContent">
                                <div class="bc-preview-empty">
                                    ستظهر معاينة الرسالة هنا عند الكتابة...
                                </div>
                            </div>
                        </div>

                        <!-- Selected Type Info -->
                        <div id="selectedTypeInfo" style="display: none;">
                            <div style="padding: 14px 18px; background: #f8fafc; border-radius: 12px; font-size: 13px;">
                                <div style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                                    <i class="fas fa-bullseye" style="color: #10b981;"></i>
                                    المستلمون المحددون
                                </div>
                                <div id="selectedTypeText" style="color: #64748b;">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bulkMessageForm');
    const schoolSection = document.getElementById('schoolSection');
    const schoolSelect = document.getElementById('schoolSelect');
    const recipientTotal = document.getElementById('recipientTotal');
    const recipientCount = document.getElementById('recipientCount');
    const selectedTypeInfo = document.getElementById('selectedTypeInfo');
    const selectedTypeText = document.getElementById('selectedTypeText');
    const previewContent = document.getElementById('previewContent');
    const subjectInput = document.getElementById('subject');
    const messageText = document.getElementById('messageText');

    const schoolTypes = ['school_all', 'school_teachers', 'school_students', 'school_parents'];
    
    const typeLabels = {
        'all': 'جميع المستخدمين',
        'teacher': 'جميع المعلمين',
        'student': 'جميع الطلاب',
        'parent': 'جميع أولياء الأمور',
        'school_admin': 'جميع مدراء المدارس',
        'school_all': 'جميع منسوبي المدرسة',
        'school_teachers': 'معلمو المدرسة',
        'school_students': 'طلاب المدرسة',
        'school_parents': 'أولياء أمور المدرسة'
    };

    // Handle recipient type selection
    document.querySelectorAll('input[name="recipient_type"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const isSchoolType = schoolTypes.includes(this.value);
            
            if (isSchoolType) {
                schoolSection.classList.add('active');
                schoolSelect.required = true;
            } else {
                schoolSection.classList.remove('active');
                schoolSelect.required = false;
                schoolSelect.value = '';
                recipientTotal.classList.remove('active');
            }

            // Update selected type info
            selectedTypeInfo.style.display = 'block';
            selectedTypeText.textContent = typeLabels[this.value] || this.value;

            // Update count for non-school types
            if (!isSchoolType) {
                updateRecipientCountDirect(this.value);
            }
        });
    });

    // Handle school selection
    schoolSelect.addEventListener('change', function() {
        const selectedType = document.querySelector('input[name="recipient_type"]:checked');
        if (!selectedType || !this.value) {
            recipientTotal.classList.remove('active');
            return;
        }

        const option = this.options[this.selectedIndex];
        const type = selectedType.value;
        let count = 0;

        if (type === 'school_teachers') {
            count = option.dataset.teachers || 0;
        } else if (type === 'school_students') {
            count = option.dataset.students || 0;
        } else if (type === 'school_all') {
            count = option.dataset.users || 0;
        } else {
            // For school_parents, fetch from server
            fetchRecipientCount(type, this.value);
            return;
        }

        recipientCount.textContent = count;
        recipientTotal.classList.add('active');

        selectedTypeText.textContent = typeLabels[type] + ' - ' + option.text + ' (' + count + ' مستلم)';
    });

    function updateRecipientCountDirect(type) {
        const counts = {
            'all': {{ $recipientCounts['all'] }},
            'teacher': {{ $recipientCounts['teachers'] }},
            'student': {{ $recipientCounts['students'] }},
            'parent': {{ $recipientCounts['parents'] }},
            'school_admin': {{ $recipientCounts['school_admins'] }},
        };
        
        const count = counts[type] || 0;
        selectedTypeText.textContent = typeLabels[type] + ' (' + count + ' مستلم)';
    }

    function fetchRecipientCount(type, schoolId) {
        fetch(`{{ route('messages.bulk.recipient-count') }}?type=${type}&school_id=${schoolId}`)
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                recipientCount.textContent = data.count;
                recipientTotal.classList.add('active');

                const schoolName = schoolSelect.options[schoolSelect.selectedIndex].text;
                selectedTypeText.textContent = typeLabels[type] + ' - ' + schoolName + ' (' + data.count + ' مستلم)';
            })
            .catch(() => {
                recipientCount.textContent = '?';
                recipientTotal.classList.add('active');
            });
    }

    // Live preview
    function updatePreview() {
        const subject = subjectInput.value.trim();
        const message = messageText.value.trim();

        if (!subject && !message) {
            previewContent.innerHTML = '<div class="bc-preview-empty">ستظهر معاينة الرسالة هنا عند الكتابة...</div>';
            return;
        }

        let html = '';
        if (subject) {
            html += `<div class="bc-preview-subject">${escapeHtml(subject)}</div>`;
        }
        if (message) {
            html += `<div class="bc-preview-body">${escapeHtml(message).replace(/\n/g, '<br>')}</div>`;
        }
        previewContent.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    subjectInput.addEventListener('input', updatePreview);
    messageText.addEventListener('input', updatePreview);
    
    // Initial state on page load (for old() values)
    updatePreview();
    const checkedRadio = document.querySelector('input[name="recipient_type"]:checked');
    if (checkedRadio) {
        checkedRadio.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
