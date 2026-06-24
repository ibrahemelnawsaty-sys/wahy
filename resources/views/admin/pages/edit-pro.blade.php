@extends('layouts.admin')

@section('title', 'تعديل صفحة')
@section('page-title', 'تعديل: ' . $page->page_name)

@push('styles')
<style>
* { box-sizing: border-box; }

/* Glass Page Info Card */
.page-info-glass {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06),
                0 1px 3px rgba(0, 0, 0, 0.03),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
    position: relative;
    overflow: hidden;
}

.page-info-glass::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, 
        transparent, 
        rgba(60, 203, 138, 0.3) 50%, 
        transparent);
}

.page-info-glass::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(60, 203, 138, 0.03) 0%, transparent 70%);
    pointer-events: none;
}

.glass-form-group {
    position: relative;
    z-index: 1;
}

.glass-label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    font-size: 14px;
    color: #1e293b;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.glass-label::before {
    content: '';
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: var(--color-primary);
    display: inline-block;
}

.glass-input {
    width: 100%;
    padding: 14px 18px;
    border: 1.5px solid rgba(226, 232, 240, 0.8);
    border-radius: 12px;
    font-size: 15px;
    font-weight: 500;
    color: #1e293b;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02),
                inset 0 1px 2px rgba(255, 255, 255, 0.8);
}

.glass-input:focus {
    outline: none;
    border-color: var(--color-primary);
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 0 0 4px rgba(60, 203, 138, 0.1),
                0 4px 12px rgba(60, 203, 138, 0.15);
    transform: translateY(-1px);
}

.glass-input:hover:not(:focus) {
    border-color: rgba(60, 203, 138, 0.3);
}

.glass-input::placeholder {
    color: #94a3b8;
    font-weight: 400;
}

.glass-select {
    width: 100%;
    padding: 14px 18px;
    padding-left: 40px;
    border: 1.5px solid rgba(226, 232, 240, 0.8);
    border-radius: 12px;
    font-size: 15px;
    font-weight: 500;
    color: #1e293b;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%233CCB8A' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: left 16px center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02),
                inset 0 1px 2px rgba(255, 255, 255, 0.8);
}

.glass-select:focus {
    outline: none;
    border-color: var(--color-primary);
    background-color: rgba(255, 255, 255, 1);
    box-shadow: 0 0 0 4px rgba(60, 203, 138, 0.1),
                0 4px 12px rgba(60, 203, 138, 0.15);
    transform: translateY(-1px);
}

.glass-select:hover:not(:focus) {
    border-color: rgba(60, 203, 138, 0.3);
}

.info-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.info-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 1024px) {
    .info-grid-3 {
        grid-template-columns: 1fr;
    }
    .info-grid-2 {
        grid-template-columns: 1fr;
    }
}

.builder-wrapper {
    display: flex;
    gap: 0;
    height: calc(100vh - 200px);
    background: #f8fafc;
    border-radius: 12px;
    overflow: hidden;
}

/* Sidebar */
.builder-sidebar {
    width: 320px;
    background: white;
    border-left: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.sidebar-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.sidebar-desc {
    font-size: 13px;
    color: #64748b;
}

.sidebar-tabs {
    display: flex;
    border-bottom: 2px solid #e2e8f0;
}

.sidebar-tab {
    flex: 1;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    border: none;
    background: transparent;
    font-weight: 600;
    font-size: 14px;
    color: #64748b;
    transition: all 0.2s;
}

.sidebar-tab.active {
    color: var(--color-primary);
    border-bottom: 2px solid var(--color-primary);
    margin-bottom: -2px;
}

.sidebar-content {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
}

.component-category {
    margin-bottom: 24px;
}

.category-title {
    font-size: 12px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.component-item {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    cursor: grab;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 12px;
}

.component-item:hover {
    background: var(--color-primary-light);
    border-color: var(--color-primary);
    transform: translateX(-4px);
}

.component-item:active {
    cursor: grabbing;
}

.component-icon {
    font-size: 24px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 8px;
}

.component-info {
    flex: 1;
}

.component-name {
    font-weight: 600;
    color: #1e293b;
    font-size: 13px;
    margin-bottom: 2px;
}

.component-desc {
    font-size: 11px;
    color: #64748b;
}

/* Canvas */
.builder-canvas {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}

.canvas-inner {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    border-radius: 12px;
    min-height: 600px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.empty-canvas {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 600px;
    padding: 60px 24px;
    text-align: center;
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 24px;
    opacity: 0.3;
}

.empty-title {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 12px;
}

.empty-desc {
    font-size: 16px;
    color: #64748b;
    max-width: 400px;
}

/* Section */
.page-section {
    position: relative;
    border: 2px dashed transparent;
    transition: all 0.2s;
    min-height: 100px;
}

.page-section:hover {
    border-color: var(--color-primary);
    background: var(--color-primary-light);
}

.page-section:hover .section-toolbar {
    opacity: 1;
}

.section-toolbar {
    position: absolute;
    top: 8px;
    left: 8px;
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s;
    z-index: 10;
}

.toolbar-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s;
}

.toolbar-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.btn-settings { color: #3b82f6; }
.btn-duplicate { color: #8b5cf6; }
.btn-paste { color: #10b981; }
.btn-move-up { color: #10b981; }
.btn-move-down { color: #10b981; }
.btn-delete { color: #ef4444; }

/* Grid System */
.section-grid {
    display: grid;
    gap: 16px;
    padding: 16px;
}

.grid-cols-1 { grid-template-columns: 1fr; }
.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
.grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
.grid-cols-12 { grid-template-columns: repeat(12, 1fr); }

.grid-column {
    border: 2px dashed #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    min-height: 80px;
    position: relative;
    transition: all 0.2s;
}

.grid-column:hover {
    border-color: var(--color-secondary);
    background: rgba(59, 130, 246, 0.05);
}

.grid-column.drag-over {
    border-color: var(--color-primary);
    background: var(--color-primary-light);
}

.column-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 13px;
    min-height: 80px;
}

/* Component in Grid */
.grid-component {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    position: relative;
}

.grid-component:hover {
    border-color: var(--color-primary);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.grid-component:hover .component-actions {
    opacity: 1;
}

.component-actions {
    position: absolute;
    top: 8px;
    left: 8px;
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s;
}

.action-btn {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 6px;
    background: white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    cursor: pointer;
    font-size: 12px;
}

/* Full Width Sections */
.section-full {
    padding: 60px 24px;
    margin: 0;
}

.section-hero {
    background: linear-gradient(135deg, var(--color-primary) 0%, #2fb577 100%);
    color: white;
    text-align: center;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 32px;
    max-width: 700px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--color-primary);
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 2px solid #e2e8f0;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
}

.btn-secondary {
    background: #e2e8f0;
    color: #64748b;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
}

/* Column Span Selector */
.span-selector {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-top: 8px;
}

.span-option {
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    text-align: center;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}

.span-option:hover {
    border-color: var(--color-primary);
    background: var(--color-primary-light);
}

.span-option.active {
    border-color: var(--color-primary);
    background: var(--color-primary);
    color: white;
}

/* Utility Classes */
.col-span-1 { grid-column: span 1; }
.col-span-2 { grid-column: span 2; }
.col-span-3 { grid-column: span 3; }
.col-span-4 { grid-column: span 4; }
.col-span-6 { grid-column: span 6; }
.col-span-12 { grid-column: span 12; }

.text-center { text-align: center; }
.text-right { text-align: right; }
.text-left { text-align: left; }
</style>
@endpush

@section('content')
<form action="{{ route('admin.pages.update', $page->id) }}" method="POST" id="pageForm">
    @csrf
    @method('PUT')
    
    <!-- Page Info Card -->
    <div class="page-info-glass">
        <div class="info-grid-3">
            <div class="glass-form-group">
                <label class="glass-label">اسم الصفحة</label>
                <input type="text" name="page_name" class="glass-input" value="{{ $page->page_name }}" placeholder="أدخل اسم الصفحة..." required>
            </div>
            <div class="glass-form-group">
                <label class="glass-label">Slug</label>
                <input type="text" name="slug" class="glass-input" value="{{ $page->slug }}" placeholder="معرف الصفحة..." required>
            </div>
            <div class="glass-form-group">
                <label class="glass-label">الحالة</label>
                <select name="is_active" class="glass-select">
                    <option value="1" {{ $page->is_active ? 'selected' : '' }}>✓ نشط</option>
                    <option value="0" {{ !$page->is_active ? 'selected' : '' }}>✕ معطل</option>
                </select>
            </div>
        </div>
        <div class="info-grid-2">
            <div class="glass-form-group">
                <label class="glass-label">عنوان SEO (Meta Title)</label>
                <input type="text" name="meta_title" class="glass-input" value="{{ $page->meta_title }}" placeholder="عنوان SEO...">
            </div>
            <div class="glass-form-group">
                <label class="glass-label">وصف SEO (Meta Description)</label>
                <input type="text" name="meta_description" class="glass-input" value="{{ $page->meta_description }}" placeholder="وصف SEO...">
            </div>
        </div>
    </div>

    <!-- Builder -->
    <div class="builder-wrapper">
        <!-- Sidebar -->
        <div class="builder-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">🎨 مكونات الصفحة</div>
                <div class="sidebar-desc">اسحب المكونات لبناء صفحتك</div>
            </div>
            
            <div class="sidebar-tabs">
                <button type="button" class="sidebar-tab active" onclick="switchTab('sections')">أقسام</button>
                <button type="button" class="sidebar-tab" onclick="switchTab('components')">مكونات</button>
            </div>
            
            <div class="sidebar-content">
                <!-- Sections Tab -->
                <div id="sectionsTab" class="tab-content">
                    <div class="component-category">
                        <div class="category-title">📐 تخطيطات الشبكة</div>
                        
                        <div class="component-item" draggable="true" data-type="section" data-cols="1">
                            <div class="component-icon">▬</div>
                            <div class="component-info">
                                <div class="component-name">عمود واحد</div>
                                <div class="component-desc">عرض كامل 100%</div>
                            </div>
                        </div>
                        
                        <div class="component-item" draggable="true" data-type="section" data-cols="2">
                            <div class="component-icon">▬▬</div>
                            <div class="component-info">
                                <div class="component-name">عمودين</div>
                                <div class="component-desc">50% + 50%</div>
                            </div>
                        </div>
                        
                        <div class="component-item" draggable="true" data-type="section" data-cols="3">
                            <div class="component-icon">▬▬▬</div>
                            <div class="component-info">
                                <div class="component-name">ثلاثة أعمدة</div>
                                <div class="component-desc">33% + 33% + 33%</div>
                            </div>
                        </div>
                        
                        <div class="component-item" draggable="true" data-type="section" data-cols="4">
                            <div class="component-icon">▬▬▬▬</div>
                            <div class="component-info">
                                <div class="component-name">أربعة أعمدة</div>
                                <div class="component-desc">25% + 25% + 25% + 25%</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Components Tab -->
                <div id="componentsTab" class="tab-content" style="display: none;">
                    <div class="component-category">
                        <div class="category-title">📝 نصوص</div>
                        
                        <div class="component-item" draggable="true" data-type="component" data-component="heading">
                            <div class="component-icon">H</div>
                            <div class="component-info">
                                <div class="component-name">عنوان</div>
                                <div class="component-desc">عنوان من H1 إلى H6</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="paragraph">
                            <div class="component-icon">¶</div>
                            <div class="component-info">
                                <div class="component-name">فقرة</div>
                                <div class="component-desc">فقرة نصية</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="list">
                            <div class="component-icon">☰</div>
                            <div class="component-info">
                                <div class="component-name">قائمة</div>
                                <div class="component-desc">قائمة نقطية أو مرقّمة</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="quote">
                            <div class="component-icon">❝</div>
                            <div class="component-info">
                                <div class="component-name">اقتباس</div>
                                <div class="component-desc">كتلة اقتباس</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="divider">
                            <div class="component-icon">─</div>
                            <div class="component-info">
                                <div class="component-name">فاصل</div>
                                <div class="component-desc">خط فاصل</div>
                            </div>
                        </div>
                    </div>

                    <div class="component-category">
                        <div class="category-title">🎯 تفاعلي</div>

                        <div class="component-item" draggable="true" data-type="component" data-component="button">
                            <div class="component-icon">▶</div>
                            <div class="component-info">
                                <div class="component-name">زر</div>
                                <div class="component-desc">زر إجراء أو رابط</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="link">
                            <div class="component-icon">🔗</div>
                            <div class="component-info">
                                <div class="component-name">رابط نصي</div>
                                <div class="component-desc">رابط نصي</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="badge">
                            <div class="component-icon">🏷️</div>
                            <div class="component-info">
                                <div class="component-name">وسم</div>
                                <div class="component-desc">شارة أو وسم ملوّن</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="component-category">
                        <div class="category-title">🖼️ وسائط</div>
                        
                        <div class="component-item" draggable="true" data-type="component" data-component="image">
                            <div class="component-icon">🖼</div>
                            <div class="component-info">
                                <div class="component-name">صورة</div>
                                <div class="component-desc">صورة مفردة</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="video">
                            <div class="component-icon">▶️</div>
                            <div class="component-info">
                                <div class="component-name">فيديو</div>
                                <div class="component-desc">فيديو يوتيوب</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="gallery">
                            <div class="component-icon">🖼️</div>
                            <div class="component-info">
                                <div class="component-name">معرض صور</div>
                                <div class="component-desc">معرض صور متعدد</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="icon">
                            <div class="component-icon">⭐</div>
                            <div class="component-info">
                                <div class="component-name">أيقونة</div>
                                <div class="component-desc">أيقونة أو رمز تعبيري</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="component-category">
                        <div class="category-title">🎴 محتوى</div>
                        
                        <div class="component-item" draggable="true" data-type="component" data-component="card">
                            <div class="component-icon">🎴</div>
                            <div class="component-info">
                                <div class="component-name">بطاقة</div>
                                <div class="component-desc">بطاقة عرض محتوى</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="alert">
                            <div class="component-icon">⚠️</div>
                            <div class="component-info">
                                <div class="component-name">تنبيه</div>
                                <div class="component-desc">صندوق تنبيه أو ملاحظة</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="accordion">
                            <div class="component-icon">▼</div>
                            <div class="component-info">
                                <div class="component-name">أكورديون</div>
                                <div class="component-desc">محتوى قابل للطي</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="tabs">
                            <div class="component-icon">▭</div>
                            <div class="component-info">
                                <div class="component-name">تبويبات</div>
                                <div class="component-desc">تبويبات تنقّل بين أقسام</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="spacer">
                            <div class="component-icon">↕️</div>
                            <div class="component-info">
                                <div class="component-name">مسافة</div>
                                <div class="component-desc">فاصل بين الأقسام</div>
                            </div>
                        </div>

                        <div class="component-item" draggable="true" data-type="component" data-component="html">
                            <div class="component-icon">&lt;/&gt;</div>
                            <div class="component-info">
                                <div class="component-name">HTML مخصص</div>
                                <div class="component-desc">كود HTML مخصص</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Canvas -->
        <div class="builder-canvas">
            <div class="canvas-inner" id="canvas">
                <div class="empty-canvas" id="emptyCanvas">
                    <div class="empty-icon">🎨</div>
                    <div class="empty-title">ابدأ ببناء صفحتك</div>
                    <div class="empty-desc">اسحب قسم من القائمة اليسرى لإضافته للصفحة</div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="json_data" id="jsonData">

    <div class="admin-form-actions" style="margin-top: 24px; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; gap: 8px;">
            <button type="button" id="undoBtn" onclick="undo()" class="admin-btn admin-btn-outline" title="تراجع (Ctrl+Z)" disabled>
                ⟲ تراجع
            </button>
            <button type="button" id="redoBtn" onclick="redo()" class="admin-btn admin-btn-outline" title="إعادة (Ctrl+Shift+Z)" disabled>
                ⟳ إعادة
            </button>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-secondary">إلغاء</a>
            <button type="button" onclick="preview()" class="admin-btn admin-btn-outline">👁️ معاينة</button>
            <button type="submit" class="admin-btn admin-btn-primary">💾 حفظ (Ctrl+S)</button>
        </div>
    </div>
</form>

<!-- Settings Modal -->
<div class="modal" id="settingsModal">
    <div class="modal-content">
        <div class="modal-header" id="modalTitle">⚙️ إعدادات</div>
        <div id="modalForm"></div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeModal()">إلغاء</button>
            <button type="button" class="btn-primary" onclick="saveSettings()">حفظ</button>
        </div>
    </div>
</div>

<script>
let pageData = {
    sections: @json($page->json_data['sections'] ?? [])
};
let currentEditTarget = null;
let clipboardComponent = null;
let historyStack = [];
let historyIndex = -1;
const MAX_HISTORY = 50;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    renderPage();
    initDragDrop();
    saveHistory();
});

// Switch tabs
function switchTab(tab) {
    document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    
    document.getElementById('sectionsTab').style.display = tab === 'sections' ? 'block' : 'none';
    document.getElementById('componentsTab').style.display = tab === 'components' ? 'block' : 'none';
}

// Render page
function renderPage() {
    const canvas = document.getElementById('canvas');
    const emptyCanvas = document.getElementById('emptyCanvas');
    
    if (pageData.sections.length === 0) {
        if (emptyCanvas) emptyCanvas.style.display = 'flex';
        canvas.querySelectorAll('.page-section').forEach(s => s.remove());
        return;
    }
    
    if (emptyCanvas) emptyCanvas.style.display = 'none';
    
    // Clear existing sections
    canvas.querySelectorAll('.page-section').forEach(s => s.remove());
    
    pageData.sections.forEach((section, sIndex) => {
        const sectionEl = createSectionElement(section, sIndex);
        canvas.appendChild(sectionEl);
    });
    
    updateJsonData();
}

// Create section element
function createSectionElement(section, sIndex) {
    const div = document.createElement('div');
    div.className = 'page-section';
    div.dataset.sectionIndex = sIndex;
    
    div.innerHTML = `
        <div class="section-toolbar">
            <button type="button" class="toolbar-btn btn-settings" onclick="editSection(${sIndex})" title="إعدادات">⚙️</button>
            ${sIndex > 0 ? `<button type="button" class="toolbar-btn btn-move-up" onclick="moveSection(${sIndex}, -1)" title="للأعلى">⬆️</button>` : ''}
            ${sIndex < pageData.sections.length - 1 ? `<button type="button" class="toolbar-btn btn-move-down" onclick="moveSection(${sIndex}, 1)" title="للأسفل">⬇️</button>` : ''}
            <button type="button" class="toolbar-btn btn-duplicate" onclick="duplicateSection(${sIndex})" title="تكرار القسم">⎘</button>
            ${clipboardComponent ? `<button type="button" class="toolbar-btn btn-paste" onclick="pasteComponent(${sIndex}, 0)" title="لصق المكون">📋</button>` : ''}
            <button type="button" class="toolbar-btn btn-delete" onclick="deleteSection(${sIndex})" title="حذف">🗑️</button>
        </div>
        <div class="section-grid grid-cols-${section.columns || 1}">
            ${createGridColumns(section, sIndex)}
        </div>
    `;
    
    // Add drop listeners to columns
    div.querySelectorAll('.grid-column').forEach((col, cIndex) => {
        col.addEventListener('dragover', (e) => {
            e.preventDefault();
            col.classList.add('drag-over');
        });
        
        col.addEventListener('dragleave', () => {
            col.classList.remove('drag-over');
        });
        
        col.addEventListener('drop', (e) => {
            e.preventDefault();
            col.classList.remove('drag-over');
            const componentType = e.dataTransfer.getData('component');
            if (componentType) {
                addComponent(sIndex, cIndex, componentType);
            }
        });
    });
    
    return div;
}

// Create grid columns
function createGridColumns(section, sIndex) {
    const columns = section.columns || 1;
    let html = '';
    
    for (let i = 0; i < columns; i++) {
        const components = (section.grid && section.grid[i]) || [];
        html += `
            <div class="grid-column" data-section="${sIndex}" data-column="${i}">
                ${components.length === 0 ? '<div class="column-empty">اسحب مكون هنا</div>' : ''}
                ${components.map((comp, cIndex) => createComponentHTML(comp, sIndex, i, cIndex)).join('')}
            </div>
        `;
    }
    
    return html;
}

// Create component HTML
function createComponentHTML(comp, sIndex, colIndex, cIndex) {
    let preview = '';
    const c = comp.content || {};
    
    switch(comp.type) {
        case 'heading':
            preview = `<h3 style="margin:0;font-size:${c.fontSize||'32px'};color:${c.color||'#1e293b'};text-align:${c.align||'right'};font-weight:${c.fontWeight||'700'}">${c.text || 'عنوان'}</h3>`;
            break;
        case 'paragraph':
            preview = `<p style="margin:0;color:${c.color||'#64748b'};text-align:${c.align||'right'};font-size:${c.fontSize||'16px'};line-height:${c.lineHeight||'1.8'}">${c.text || 'نص...'}</p>`;
            break;
        case 'list':
            const listItems = (c.items || ['عنصر 1', 'عنصر 2']).map(item => `<li>${item}</li>`).join('');
            preview = `<${c.type||'ul'} style="margin:0;padding-right:20px;color:${c.color||'#334155'};font-size:${c.fontSize||'16px'}">${listItems}</${c.type||'ul'}>`;
            break;
        case 'quote':
            preview = `<blockquote style="margin:0;padding:16px;border-right:4px solid #3b82f6;background:#f8fafc;border-radius:8px;font-size:${c.fontSize||'18px'};color:${c.color||'#64748b'}"><p style="margin:0 0 8px">"${c.text || 'اقتباس...'}"</p><footer style="font-size:14px;color:#94a3b8">— ${c.author || 'المؤلف'}</footer></blockquote>`;
            break;
        case 'divider':
            preview = `<hr style="border:none;border-top:${c.thickness||'2px'} ${c.style||'solid'} ${c.color||'#e2e8f0'};width:${c.width||'100%'};margin:20px 0">`;
            break;
        case 'button':
            preview = `<button style="padding:10px 20px;background:var(--color-primary);color:white;border:none;border-radius:6px;font-size:${c.fontSize||'16px'};font-weight:${c.fontWeight||'600'}">${c.text || 'زر'}</button>`;
            break;
        case 'link':
            preview = `<a href="#" style="color:${c.color||'#3b82f6'};font-size:${c.fontSize||'16px'};text-decoration:${c.underline?'underline':'none'}">${c.text || 'رابط'}</a>`;
            break;
        case 'badge':
            preview = `<span style="display:inline-block;padding:4px 12px;background:${c.bgColor||'#d1fae5'};color:${c.color||'#10b981'};border-radius:16px;font-size:${c.fontSize||'12px'};font-weight:${c.fontWeight||'600'}">${c.text || 'وسم'}</span>`;
            break;
        case 'image':
            preview = `<img src="${c.url || 'https://via.placeholder.com/400x200'}" alt="${c.alt||'صورة'}" style="width:${c.width||'100%'};border-radius:${c.rounded||'8px'}">`;
            break;
        case 'video':
            preview = `<div style="background:#f1f5f9;padding:40px;text-align:center;border-radius:8px;">🎥 فيديو</div>`;
            break;
        case 'gallery':
            const galleryImages = (c.images || []).slice(0, 3).map(img => `<img src="${img}" style="width:100%;border-radius:8px">`).join('');
            preview = `<div style="display:grid;grid-template-columns:repeat(${c.columns||3},1fr);gap:${c.gap||'16px'}">${galleryImages || '<div style="background:#f1f5f9;padding:40px;text-align:center">معرض صور</div>'}</div>`;
            break;
        case 'icon':
            preview = `<div style="font-size:${c.size||'48px'};color:${c.color||'#fbbf24'};text-align:center">${c.icon || '⭐'}</div>`;
            break;
        case 'card':
            preview = `
                <div style="text-align:center;padding:24px;background:#f8fafc;border-radius:12px;">
                    <div style="font-size:40px;margin-bottom:12px;">${c.icon || '⭐'}</div>
                    <h4 style="margin:0 0 8px;font-size:${c.titleSize||'20px'}">${c.title || 'عنوان'}</h4>
                    <p style="margin:0;color:#64748b;font-size:${c.textSize||'14px'}">${c.text || 'نص...'}</p>
                </div>
            `;
            break;
        case 'alert':
            const alertColors = {info: '#3b82f6', success: '#10b981', warning: '#f59e0b', error: '#ef4444'};
            const bgColors = {info: '#dbeafe', success: '#d1fae5', warning: '#fef3c7', error: '#fee2e2'};
            preview = `<div style="padding:16px;background:${bgColors[c.type||'info']};border-right:4px solid ${alertColors[c.type||'info']};border-radius:8px;display:flex;align-items:center;gap:12px"><span style="font-size:24px">${c.icon||'ℹ️'}</span><span>${c.text || 'رسالة تنبيه'}</span></div>`;
            break;
        case 'accordion':
            preview = `<div style="border:1px solid #e2e8f0;border-radius:8px;padding:16px">▼ أكورديون (${(c.items||[]).length} عناصر)</div>`;
            break;
        case 'tabs':
            preview = `<div style="border-bottom:2px solid #e2e8f0;padding-bottom:8px">▭ تبويبات (${(c.tabs||[]).length} تبويبات)</div>`;
            break;
        case 'spacer':
            preview = `<div style="height:${c.height||'40px'};background:repeating-linear-gradient(90deg,#e2e8f0 0,#e2e8f0 10px,transparent 10px,transparent 20px);border-radius:4px"></div>`;
            break;
        case 'html':
            preview = `<div style="padding:16px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;font-family:monospace;font-size:12px">&lt;/&gt; HTML مخصص</div>`;
            break;
        default:
            preview = `<div style="padding:20px;background:#f8fafc;border-radius:8px;text-align:center;color:#94a3b8">مكون غير معروف</div>`;
    }
    
    return `
        <div class="grid-component" data-section="${sIndex}" data-column="${colIndex}" data-component="${cIndex}">
            <div class="component-actions">
                <button type="button" class="action-btn" onclick="editComponent(${sIndex},${colIndex},${cIndex})" style="color:#3b82f6;" title="تعديل">✏️</button>
                <button type="button" class="action-btn" onclick="copyComponent(${sIndex},${colIndex},${cIndex})" style="color:#10b981;" title="نسخ">📋</button>
                <button type="button" class="action-btn" onclick="duplicateComponent(${sIndex},${colIndex},${cIndex})" style="color:#f59e0b;" title="تكرار">⎘</button>
                <button type="button" class="action-btn" onclick="deleteComponent(${sIndex},${colIndex},${cIndex})" style="color:#ef4444;" title="حذف">🗑️</button>
            </div>
            ${preview}
        </div>
    `;
}

// Init drag & drop
function initDragDrop() {
    // Sidebar items
    document.querySelectorAll('[draggable="true"]').forEach(item => {
        item.addEventListener('dragstart', (e) => {
            if (item.dataset.type === 'section') {
                e.dataTransfer.setData('sectionCols', item.dataset.cols);
            } else if (item.dataset.type === 'component') {
                e.dataTransfer.setData('component', item.dataset.component);
            }
        });
    });
    
    // Canvas drop
    const canvas = document.getElementById('canvas');
    canvas.addEventListener('dragover', (e) => {
        e.preventDefault();
    });
    
    canvas.addEventListener('drop', (e) => {
        e.preventDefault();
        const cols = e.dataTransfer.getData('sectionCols');
        if (cols) {
            addSection(parseInt(cols));
        }
    });
}

// Add section
function addSection(columns) {
    const newSection = {
        columns: columns,
        grid: Array.from({ length: columns }, () => [])
    };
    
    pageData.sections.push(newSection);
    renderPage();
}

// Add component
function addComponent(sIndex, colIndex, componentType) {
    if (!pageData.sections[sIndex].grid) {
        pageData.sections[sIndex].grid = Array.from({ length: pageData.sections[sIndex].columns }, () => []);
    }
    
    if (!Array.isArray(pageData.sections[sIndex].grid[colIndex])) {
        pageData.sections[sIndex].grid[colIndex] = [];
    }
    
    const newComponent = {
        type: componentType,
        content: getDefaultContent(componentType)
    };
    
    pageData.sections[sIndex].grid[colIndex].push(newComponent);
    renderPage();
}

// Get default content
function getDefaultContent(type) {
    const defaults = {
        heading: { level: 'h2', text: 'عنوان جديد', align: 'right', fontSize: '32px', color: '#1e293b', fontFamily: 'Cairo', fontWeight: '700' },
        paragraph: { text: 'اكتب النص هنا...', align: 'right', fontSize: '16px', color: '#334155', fontFamily: 'Cairo', lineHeight: '1.8' },
        list: { items: ['عنصر 1', 'عنصر 2', 'عنصر 3'], type: 'ul', fontSize: '16px', color: '#334155' },
        quote: { text: 'اقتباس ملهم هنا...', author: 'المؤلف', fontSize: '18px', color: '#64748b', style: 'border' },
        divider: { color: '#e2e8f0', thickness: '2px', style: 'solid', width: '100%' },
        button: { text: 'انقر هنا', link: '#', style: 'primary', fontSize: '16px', fontWeight: '600' },
        link: { text: 'رابط نصي', url: '#', color: '#3b82f6', fontSize: '16px', underline: true },
        badge: { text: 'جديد', color: '#10b981', bgColor: '#d1fae5', fontSize: '12px', fontWeight: '600' },
        image: { url: 'https://via.placeholder.com/800x400', alt: 'صورة', width: '100%', rounded: '8px' },
        video: { url: 'https://www.youtube.com/embed/VIDEO_ID', width: '100%', height: '400px' },
        gallery: { images: ['https://via.placeholder.com/400', 'https://via.placeholder.com/400', 'https://via.placeholder.com/400'], columns: '3', gap: '16px' },
        icon: { icon: '⭐', size: '48px', color: '#fbbf24' },
        card: { icon: '⭐', title: 'عنوان البطاقة', text: 'نص البطاقة', titleSize: '20px', textSize: '14px' },
        alert: { text: 'رسالة تنبيه هنا', type: 'info', icon: 'ℹ️' },
        accordion: { items: [{title: 'عنوان 1', content: 'محتوى 1'}, {title: 'عنوان 2', content: 'محتوى 2'}] },
        tabs: { tabs: [{title: 'تبويب 1', content: 'محتوى 1'}, {title: 'تبويب 2', content: 'محتوى 2'}] },
        spacer: { height: '40px' },
        html: { code: '<div>HTML مخصص</div>' }
    };
    return defaults[type] || {};
}

// Edit component
function editComponent(sIndex, colIndex, cIndex) {
    const component = pageData.sections[sIndex].grid[colIndex][cIndex];
    currentEditTarget = { sIndex, colIndex, cIndex, type: 'component' };
    
    const modal = document.getElementById('settingsModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalForm = document.getElementById('modalForm');
    
    modalTitle.textContent = '✏️ تعديل ' + getComponentName(component.type);
    modalForm.innerHTML = getComponentForm(component);
    modal.classList.add('active');
}

// Get component name
function getComponentName(type) {
    const names = {
        heading: 'العنوان', paragraph: 'الفقرة', list: 'القائمة', quote: 'الاقتباس', divider: 'الفاصل',
        button: 'الزر', link: 'الرابط', badge: 'الوسم',
        image: 'الصورة', video: 'الفيديو', gallery: 'المعرض', icon: 'الأيقونة',
        card: 'البطاقة', alert: 'التنبيه', accordion: 'الأكورديون', tabs: 'التبويبات',
        spacer: 'المسافة', html: 'HTML'
    };
    return names[type] || type;
}

// Get component form
function getComponentForm(comp) {
    switch(comp.type) {
        case 'heading':
            return `
                <div class="form-row">
                    <div class="form-group">
                        <label>المستوى</label>
                        <select id="edit_level" class="admin-input">
                            ${['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].map(h => 
                                `<option value="${h}" ${comp.content.level === h ? 'selected' : ''}>${h.toUpperCase()}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>المحاذاة</label>
                        <select id="edit_align" class="admin-input">
                            <option value="right" ${comp.content.align === 'right' ? 'selected' : ''}>يمين</option>
                            <option value="center" ${comp.content.align === 'center' ? 'selected' : ''}>وسط</option>
                            <option value="left" ${comp.content.align === 'left' ? 'selected' : ''}>يسار</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>النص</label>
                    <input type="text" id="edit_text" class="admin-input" value="${comp.content.text || ''}">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>حجم الخط</label>
                        <input type="text" id="edit_fontSize" class="admin-input" value="${comp.content.fontSize || '32px'}" placeholder="32px">
                    </div>
                    <div class="form-group">
                        <label>اللون</label>
                        <input type="color" id="edit_color" class="admin-input" value="${comp.content.color || '#1e293b'}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>نوع الخط</label>
                        <select id="edit_fontFamily" class="admin-input">
                            <option value="Cairo" ${comp.content.fontFamily === 'Cairo' ? 'selected' : ''}>Cairo</option>
                            <option value="IBM Plex Sans Arabic" ${comp.content.fontFamily === 'IBM Plex Sans Arabic' ? 'selected' : ''}>IBM Plex Sans Arabic</option>
                            <option value="Tajawal" ${comp.content.fontFamily === 'Tajawal' ? 'selected' : ''}>Tajawal</option>
                            <option value="Almarai" ${comp.content.fontFamily === 'Almarai' ? 'selected' : ''}>Almarai</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>سُمك الخط</label>
                        <select id="edit_fontWeight" class="admin-input">
                            <option value="300" ${comp.content.fontWeight === '300' ? 'selected' : ''}>خفيف</option>
                            <option value="400" ${comp.content.fontWeight === '400' ? 'selected' : ''}>عادي</option>
                            <option value="600" ${comp.content.fontWeight === '600' ? 'selected' : ''}>متوسط</option>
                            <option value="700" ${comp.content.fontWeight === '700' ? 'selected' : ''}>سميك</option>
                            <option value="900" ${comp.content.fontWeight === '900' ? 'selected' : ''}>سميك جداً</option>
                        </select>
                    </div>
                </div>
            `;
        case 'paragraph':
            return `
                <div class="form-group">
                    <label>المحاذاة</label>
                    <select id="edit_align" class="admin-input">
                        <option value="right" ${comp.content.align === 'right' ? 'selected' : ''}>يمين</option>
                        <option value="center" ${comp.content.align === 'center' ? 'selected' : ''}>وسط</option>
                        <option value="left" ${comp.content.align === 'left' ? 'selected' : ''}>يسار</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>النص</label>
                    <textarea id="edit_text" class="admin-input" rows="6">${comp.content.text || ''}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>حجم الخط</label>
                        <input type="text" id="edit_fontSize" class="admin-input" value="${comp.content.fontSize || '16px'}" placeholder="16px">
                    </div>
                    <div class="form-group">
                        <label>اللون</label>
                        <input type="color" id="edit_color" class="admin-input" value="${comp.content.color || '#334155'}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>نوع الخط</label>
                        <select id="edit_fontFamily" class="admin-input">
                            <option value="Cairo" ${comp.content.fontFamily === 'Cairo' ? 'selected' : ''}>Cairo</option>
                            <option value="IBM Plex Sans Arabic" ${comp.content.fontFamily === 'IBM Plex Sans Arabic' ? 'selected' : ''}>IBM Plex Sans Arabic</option>
                            <option value="Tajawal" ${comp.content.fontFamily === 'Tajawal' ? 'selected' : ''}>Tajawal</option>
                            <option value="Almarai" ${comp.content.fontFamily === 'Almarai' ? 'selected' : ''}>Almarai</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ارتفاع السطر</label>
                        <input type="text" id="edit_lineHeight" class="admin-input" value="${comp.content.lineHeight || '1.8'}" placeholder="1.8">
                    </div>
                </div>
            `;
        case 'button':
            return `
                <div class="form-row">
                    <div class="form-group">
                        <label>نص الزر</label>
                        <input type="text" id="edit_text" class="admin-input" value="${comp.content.text || ''}">
                    </div>
                    <div class="form-group">
                        <label>النمط</label>
                        <select id="edit_style" class="admin-input">
                            <option value="primary" ${comp.content.style === 'primary' ? 'selected' : ''}>رئيسي</option>
                            <option value="secondary" ${comp.content.style === 'secondary' ? 'selected' : ''}>ثانوي</option>
                            <option value="outline" ${comp.content.style === 'outline' ? 'selected' : ''}>محدد</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>الرابط</label>
                    <input type="text" id="edit_link" class="admin-input" value="${comp.content.link || '#'}">
                </div>
            `;
        case 'image':
            return `
                <div class="form-group">
                    <label>طريقة إضافة الصورة</label>
                    <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                        <button type="button" onclick="switchImageMode('url')" id="imgModeUrl" class="span-option active" style="flex: 1;">رابط URL</button>
                        <button type="button" onclick="switchImageMode('upload')" id="imgModeUpload" class="span-option" style="flex: 1;">رفع ملف</button>
                    </div>
                </div>
                <div id="imageUrlMode">
                    <div class="form-group">
                        <label>رابط الصورة</label>
                        <input type="text" id="edit_url" class="admin-input" value="${comp.content.url || ''}" placeholder="مثال: https://cdn.qiyamm.sa/image.jpg">
                    </div>
                </div>
                <div id="imageUploadMode" style="display: none;">
                    <div class="form-group">
                        <label>رفع صورة</label>
                        <input type="file" id="edit_upload" class="admin-input" accept="image/*" onchange="handleImageUpload(event)">
                        <div id="uploadPreview" style="margin-top: 12px;"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>النص البديل</label>
                    <input type="text" id="edit_alt" class="admin-input" value="${comp.content.alt || ''}" placeholder="وصف الصورة">
                </div>
            `;
        case 'video':
            return `
                <div class="form-group">
                    <label>طريقة إضافة الفيديو</label>
                    <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                        <button type="button" onclick="switchVideoMode('youtube')" id="vidModeYoutube" class="span-option active" style="flex: 1;">يوتيوب</button>
                        <button type="button" onclick="switchVideoMode('upload')" id="vidModeUpload" class="span-option" style="flex: 1;">رفع ملف</button>
                    </div>
                </div>
                <div id="videoYoutubeMode">
                    <div class="form-group">
                        <label>رابط فيديو يوتيوب (Embed)</label>
                        <input type="text" id="edit_url" class="admin-input" value="${comp.content.url || ''}" placeholder="https://www.youtube.com/embed/VIDEO_ID">
                        <small style="color:#64748b;display:block;margin-top:8px;">مثال: https://www.youtube.com/embed/VIDEO_ID</small>
                    </div>
                </div>
                <div id="videoUploadMode" style="display: none;">
                    <div class="form-group">
                        <label>رفع فيديو</label>
                        <input type="file" id="edit_video_upload" class="admin-input" accept="video/*" onchange="handleVideoUpload(event)">
                        <div id="videoUploadPreview" style="margin-top: 12px;"></div>
                        <small style="color:#64748b;display:block;margin-top:8px;">الحد الأقصى: 50 ميجابايت</small>
                    </div>
                </div>
            `;
        case 'card':
            return `
                <div class="form-group">
                    <label>الأيقونة</label>
                    <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                        <button type="button" onclick="switchIconMode('emoji')" id="iconModeEmoji" class="span-option active" style="flex: 1;">رمز تعبيري</button>
                        <button type="button" onclick="switchIconMode('image')" id="iconModeImage" class="span-option" style="flex: 1;">صورة</button>
                    </div>
                </div>
                <div id="iconEmojiMode">
                    <div class="form-group">
                        <label>الرمز التعبيري</label>
                        <input type="text" id="edit_icon" class="admin-input" value="${comp.content.icon || '⭐'}" placeholder="⭐">
                        <div style="margin-top: 8px; display: flex; gap: 8px; flex-wrap: wrap;">
                            ${['⭐', '🎯', '🚀', '💡', '🏆', '📊', '✨', '🎨', '📱', '💻', '🔥', '❤️'].map(emoji => 
                                `<button type="button" onclick="document.getElementById('edit_icon').value='${emoji}'" style="padding:8px 12px;border:2px solid #e2e8f0;border-radius:6px;background:white;cursor:pointer;font-size:20px;">${emoji}</button>`
                            ).join('')}
                        </div>
                    </div>
                </div>
                <div id="iconImageMode" style="display: none;">
                    <div class="form-group">
                        <label>رفع صورة الأيقونة</label>
                        <input type="file" id="edit_icon_upload" class="admin-input" accept="image/*" onchange="handleIconUpload(event)">
                        <div id="iconUploadPreview" style="margin-top: 12px;"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>العنوان</label>
                    <input type="text" id="edit_title" class="admin-input" value="${comp.content.title || ''}" placeholder="عنوان البطاقة">
                </div>
                <div class="form-group">
                    <label>النص</label>
                    <textarea id="edit_text" class="admin-input" rows="4" placeholder="نص البطاقة">${comp.content.text || ''}</textarea>
                </div>
            `;
        case 'list':
            return `
                <div class="form-group">
                    <label>نوع القائمة</label>
                    <select id="edit_type" class="admin-input">
                        <option value="ul" ${comp.content.type === 'ul' ? 'selected' : ''}>نقاط (Bullets)</option>
                        <option value="ol" ${comp.content.type === 'ol' ? 'selected' : ''}>أرقام (Numbers)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>العناصر (سطر لكل عنصر)</label>
                    <textarea id="edit_items" class="admin-input" rows="6" placeholder="عنصر 1\nعنصر 2\nعنصر 3">${(comp.content.items || []).join('\n')}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>حجم الخط</label>
                        <input type="text" id="edit_fontSize" class="admin-input" value="${comp.content.fontSize || '16px'}" placeholder="16px">
                    </div>
                    <div class="form-group">
                        <label>اللون</label>
                        <input type="color" id="edit_color" class="admin-input" value="${comp.content.color || '#334155'}">
                    </div>
                </div>
            `;
        case 'quote':
            return `
                <div class="form-group">
                    <label>نص الاقتباس</label>
                    <textarea id="edit_text" class="admin-input" rows="4" placeholder="اكتب الاقتباس هنا...">${comp.content.text || ''}</textarea>
                </div>
                <div class="form-group">
                    <label>المؤلف</label>
                    <input type="text" id="edit_author" class="admin-input" value="${comp.content.author || ''}" placeholder="اسم المؤلف">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>حجم الخط</label>
                        <input type="text" id="edit_fontSize" class="admin-input" value="${comp.content.fontSize || '18px'}" placeholder="18px">
                    </div>
                    <div class="form-group">
                        <label>اللون</label>
                        <input type="color" id="edit_color" class="admin-input" value="${comp.content.color || '#64748b'}">
                    </div>
                </div>
                <div class="form-group">
                    <label>نمط الحدود</label>
                    <select id="edit_style" class="admin-input">
                        <option value="border" ${comp.content.style === 'border' ? 'selected' : ''}>حد جانبي</option>
                        <option value="background" ${comp.content.style === 'background' ? 'selected' : ''}>خلفية ملونة</option>
                        <option value="minimal" ${comp.content.style === 'minimal' ? 'selected' : ''}>بسيط</option>
                    </select>
                </div>
            `;
        case 'divider':
            return `
                <div class="form-row">
                    <div class="form-group">
                        <label>نمط الخط</label>
                        <select id="edit_style" class="admin-input">
                            <option value="solid" ${comp.content.style === 'solid' ? 'selected' : ''}>متصل</option>
                            <option value="dashed" ${comp.content.style === 'dashed' ? 'selected' : ''}>متقطع</option>
                            <option value="dotted" ${comp.content.style === 'dotted' ? 'selected' : ''}>نقاط</option>
                            <option value="double" ${comp.content.style === 'double' ? 'selected' : ''}>مزدوج</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>السُمك</label>
                        <input type="text" id="edit_thickness" class="admin-input" value="${comp.content.thickness || '2px'}" placeholder="2px">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>اللون</label>
                        <input type="color" id="edit_color" class="admin-input" value="${comp.content.color || '#e2e8f0'}">
                    </div>
                    <div class="form-group">
                        <label>العرض</label>
                        <input type="text" id="edit_width" class="admin-input" value="${comp.content.width || '100%'}" placeholder="100%">
                    </div>
                </div>
            `;
        case 'link':
            return `
                <div class="form-group">
                    <label>نص الرابط</label>
                    <input type="text" id="edit_text" class="admin-input" value="${comp.content.text || ''}" placeholder="انقر هنا">
                </div>
                <div class="form-group">
                    <label>الرابط (URL)</label>
                    <input type="text" id="edit_url" class="admin-input" value="${comp.content.url || '#'}" placeholder="مثال: https://qiyamm.sa">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>حجم الخط</label>
                        <input type="text" id="edit_fontSize" class="admin-input" value="${comp.content.fontSize || '16px'}" placeholder="16px">
                    </div>
                    <div class="form-group">
                        <label>اللون</label>
                        <input type="color" id="edit_color" class="admin-input" value="${comp.content.color || '#3b82f6'}">
                    </div>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="edit_underline" ${comp.content.underline ? 'checked' : ''}>
                        <span>خط تحت النص</span>
                    </label>
                </div>
            `;
        case 'badge':
            return `
                <div class="form-group">
                    <label>النص</label>
                    <input type="text" id="edit_text" class="admin-input" value="${comp.content.text || ''}" placeholder="جديد">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>لون النص</label>
                        <input type="color" id="edit_color" class="admin-input" value="${comp.content.color || '#10b981'}">
                    </div>
                    <div class="form-group">
                        <label>لون الخلفية</label>
                        <input type="color" id="edit_bgColor" class="admin-input" value="${comp.content.bgColor || '#d1fae5'}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>حجم الخط</label>
                        <input type="text" id="edit_fontSize" class="admin-input" value="${comp.content.fontSize || '12px'}" placeholder="12px">
                    </div>
                    <div class="form-group">
                        <label>سُمك الخط</label>
                        <select id="edit_fontWeight" class="admin-input">
                            <option value="400" ${comp.content.fontWeight === '400' ? 'selected' : ''}>عادي</option>
                            <option value="600" ${comp.content.fontWeight === '600' ? 'selected' : ''}>متوسط</option>
                            <option value="700" ${comp.content.fontWeight === '700' ? 'selected' : ''}>سميك</option>
                        </select>
                    </div>
                </div>
            `;
        case 'gallery':
            return `
                <div class="form-group">
                    <label>روابط الصور (سطر لكل رابط)</label>
                    <textarea id="edit_images" class="admin-input" rows="6" placeholder="مثال:
https://cdn.qiyamm.sa/image1.jpg
https://cdn.qiyamm.sa/image2.jpg">${(comp.content.images || []).join('\n')}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>عدد الأعمدة</label>
                        <select id="edit_columns" class="admin-input">
                            <option value="2" ${comp.content.columns === '2' ? 'selected' : ''}>2</option>
                            <option value="3" ${comp.content.columns === '3' ? 'selected' : ''}>3</option>
                            <option value="4" ${comp.content.columns === '4' ? 'selected' : ''}>4</option>
                            <option value="5" ${comp.content.columns === '5' ? 'selected' : ''}>5</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>المسافة بين الصور</label>
                        <input type="text" id="edit_gap" class="admin-input" value="${comp.content.gap || '16px'}" placeholder="16px">
                    </div>
                </div>
            `;
        case 'icon':
            return `
                <div class="form-group">
                    <label>الأيقونة (رمز تعبيري)</label>
                    <input type="text" id="edit_icon" class="admin-input" value="${comp.content.icon || '⭐'}" placeholder="⭐">
                    <div style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
                        ${['⭐', '🎯', '🚀', '💡', '🏆', '📊', '✨', '🎨', '📱', '💻', '🔥', '❤️', '✅', '🌟', '💎', '🎁'].map(emoji => 
                            `<button type="button" onclick="document.getElementById('edit_icon').value='${emoji}'" style="padding:8px 12px;border:2px solid #e2e8f0;border-radius:6px;background:white;cursor:pointer;font-size:20px;">${emoji}</button>`
                        ).join('')}
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>الحجم</label>
                        <input type="text" id="edit_size" class="admin-input" value="${comp.content.size || '48px'}" placeholder="48px">
                    </div>
                    <div class="form-group">
                        <label>اللون</label>
                        <input type="color" id="edit_color" class="admin-input" value="${comp.content.color || '#fbbf24'}">
                    </div>
                </div>
            `;
        case 'alert':
            return `
                <div class="form-group">
                    <label>نص التنبيه</label>
                    <textarea id="edit_text" class="admin-input" rows="3" placeholder="رسالة التنبيه هنا...">${comp.content.text || ''}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>نوع التنبيه</label>
                        <select id="edit_type" class="admin-input">
                            <option value="info" ${comp.content.type === 'info' ? 'selected' : ''}>ℹ️ معلومة</option>
                            <option value="success" ${comp.content.type === 'success' ? 'selected' : ''}>✅ نجاح</option>
                            <option value="warning" ${comp.content.type === 'warning' ? 'selected' : ''}>⚠️ تحذير</option>
                            <option value="error" ${comp.content.type === 'error' ? 'selected' : ''}>❌ خطأ</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>الأيقونة</label>
                        <input type="text" id="edit_icon" class="admin-input" value="${comp.content.icon || 'ℹ️'}" placeholder="ℹ️">
                    </div>
                </div>
            `;
        case 'accordion':
            return `
                <div class="form-group">
                    <label>عدد العناصر: ${(comp.content.items || []).length}</label>
                    <button type="button" onclick="addAccordionItem()" class="admin-btn admin-btn-outline" style="width: 100%; margin-top: 8px;">➕ إضافة عنصر</button>
                </div>
                <div id="accordion_items" style="max-height: 400px; overflow-y: auto;">
                    ${(comp.content.items || []).map((item, i) => `
                        <div style="padding: 16px; background: #f8fafc; border-radius: 8px; margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong>عنصر ${i + 1}</strong>
                                <button type="button" onclick="removeAccordionItem(${i})" style="color: #ef4444; border: none; background: none; cursor: pointer; font-size: 18px;">🗑️</button>
                            </div>
                            <input type="text" class="admin-input accordion-title" data-index="${i}" value="${item.title || ''}" placeholder="العنوان" style="margin-bottom: 8px;">
                            <textarea class="admin-input accordion-content" data-index="${i}" rows="2" placeholder="المحتوى">${item.content || ''}</textarea>
                        </div>
                    `).join('')}
                </div>
            `;
        case 'tabs':
            return `
                <div class="form-group">
                    <label>عدد التبويبات: ${(comp.content.tabs || []).length}</label>
                    <button type="button" onclick="addTabItem()" class="admin-btn admin-btn-outline" style="width: 100%; margin-top: 8px;">➕ إضافة تبويب</button>
                </div>
                <div id="tabs_items" style="max-height: 400px; overflow-y: auto;">
                    ${(comp.content.tabs || []).map((tab, i) => `
                        <div style="padding: 16px; background: #f8fafc; border-radius: 8px; margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong>تبويب ${i + 1}</strong>
                                <button type="button" onclick="removeTabItem(${i})" style="color: #ef4444; border: none; background: none; cursor: pointer; font-size: 18px;">🗑️</button>
                            </div>
                            <input type="text" class="admin-input tab-title" data-index="${i}" value="${tab.title || ''}" placeholder="العنوان" style="margin-bottom: 8px;">
                            <textarea class="admin-input tab-content" data-index="${i}" rows="2" placeholder="المحتوى">${tab.content || ''}</textarea>
                        </div>
                    `).join('')}
                </div>
            `;
        case 'spacer':
            return `
                <div class="form-group">
                    <label>الارتفاع</label>
                    <input type="text" id="edit_height" class="admin-input" value="${comp.content.height || '40px'}" placeholder="40px">
                    <small style="display: block; margin-top: 8px; color: #64748b;">يمكنك استخدام px أو rem أو vh</small>
                </div>
                <div class="form-group">
                    <label>معاينة:</label>
                    <div style="height: ${comp.content.height || '40px'}; background: repeating-linear-gradient(90deg, #e2e8f0 0, #e2e8f0 10px, transparent 10px, transparent 20px); border-radius: 4px;"></div>
                </div>
            `;
        case 'html':
            return `
                <div class="form-group">
                    <label>كود HTML</label>
                    <textarea id="edit_code" class="admin-input" rows="10" placeholder="<div>HTML مخصص هنا...</div>" style="font-family: monospace; font-size: 13px;">${comp.content.code || ''}</textarea>
                    <small style="display: block; margin-top: 8px; color: #64748b;">⚠️ تأكد من صحة الكود لتجنب المشاكل</small>
                </div>
            `;
    }
}

// Save settings
function saveSettings() {
    if (!currentEditTarget) return;
    
    const { sIndex, colIndex, cIndex, type } = currentEditTarget;
    
    if (type === 'component') {
        const component = pageData.sections[sIndex].grid[colIndex][cIndex];
        
        switch(component.type) {
            case 'heading':
                component.content = {
                    level: document.getElementById('edit_level').value,
                    text: document.getElementById('edit_text').value,
                    align: document.getElementById('edit_align').value,
                    fontSize: document.getElementById('edit_fontSize')?.value || '32px',
                    color: document.getElementById('edit_color')?.value || '#1e293b',
                    fontFamily: document.getElementById('edit_fontFamily')?.value || 'Cairo',
                    fontWeight: document.getElementById('edit_fontWeight')?.value || '700'
                };
                break;
            case 'paragraph':
                component.content = {
                    text: document.getElementById('edit_text').value,
                    align: document.getElementById('edit_align').value,
                    fontSize: document.getElementById('edit_fontSize')?.value || '16px',
                    color: document.getElementById('edit_color')?.value || '#334155',
                    fontFamily: document.getElementById('edit_fontFamily')?.value || 'Cairo',
                    lineHeight: document.getElementById('edit_lineHeight')?.value || '1.8'
                };
                break;
            case 'list':
                component.content = {
                    type: document.getElementById('edit_type').value,
                    items: document.getElementById('edit_items').value.split('\n').filter(i => i.trim()),
                    fontSize: document.getElementById('edit_fontSize')?.value || '16px',
                    color: document.getElementById('edit_color')?.value || '#334155'
                };
                break;
            case 'quote':
                component.content = {
                    text: document.getElementById('edit_text').value,
                    author: document.getElementById('edit_author').value,
                    fontSize: document.getElementById('edit_fontSize')?.value || '18px',
                    color: document.getElementById('edit_color')?.value || '#64748b',
                    style: document.getElementById('edit_style').value
                };
                break;
            case 'divider':
                component.content = {
                    style: document.getElementById('edit_style').value,
                    thickness: document.getElementById('edit_thickness').value,
                    color: document.getElementById('edit_color').value,
                    width: document.getElementById('edit_width').value
                };
                break;
            case 'button':
                component.content = {
                    text: document.getElementById('edit_text').value,
                    link: document.getElementById('edit_link').value,
                    style: document.getElementById('edit_style').value
                };
                break;
            case 'link':
                component.content = {
                    text: document.getElementById('edit_text').value,
                    url: document.getElementById('edit_url').value,
                    fontSize: document.getElementById('edit_fontSize')?.value || '16px',
                    color: document.getElementById('edit_color')?.value || '#3b82f6',
                    underline: document.getElementById('edit_underline')?.checked || false
                };
                break;
            case 'badge':
                component.content = {
                    text: document.getElementById('edit_text').value,
                    color: document.getElementById('edit_color').value,
                    bgColor: document.getElementById('edit_bgColor').value,
                    fontSize: document.getElementById('edit_fontSize')?.value || '12px',
                    fontWeight: document.getElementById('edit_fontWeight')?.value || '600'
                };
                break;
            case 'image':
                component.content = {
                    url: document.getElementById('edit_url').value,
                    alt: document.getElementById('edit_alt')?.value || 'صورة'
                };
                break;
            case 'video':
                component.content = {
                    url: document.getElementById('edit_url').value
                };
                break;
            case 'gallery':
                component.content = {
                    images: document.getElementById('edit_images').value.split('\n').filter(i => i.trim()),
                    columns: document.getElementById('edit_columns').value,
                    gap: document.getElementById('edit_gap').value
                };
                break;
            case 'icon':
                component.content = {
                    icon: document.getElementById('edit_icon').value,
                    size: document.getElementById('edit_size').value,
                    color: document.getElementById('edit_color').value
                };
                break;
            case 'card':
                component.content = {
                    icon: document.getElementById('edit_icon').value,
                    title: document.getElementById('edit_title').value,
                    text: document.getElementById('edit_text').value
                };
                break;
            case 'alert':
                component.content = {
                    text: document.getElementById('edit_text').value,
                    type: document.getElementById('edit_type').value,
                    icon: document.getElementById('edit_icon').value
                };
                break;
            case 'accordion':
                const accordionItems = [];
                document.querySelectorAll('.accordion-title').forEach((titleEl, i) => {
                    const contentEl = document.querySelector(`.accordion-content[data-index="${i}"]`);
                    if (titleEl && contentEl) {
                        accordionItems.push({
                            title: titleEl.value,
                            content: contentEl.value
                        });
                    }
                });
                component.content = { items: accordionItems };
                break;
            case 'tabs':
                const tabItems = [];
                document.querySelectorAll('.tab-title').forEach((titleEl, i) => {
                    const contentEl = document.querySelector(`.tab-content[data-index="${i}"]`);
                    if (titleEl && contentEl) {
                        tabItems.push({
                            title: titleEl.value,
                            content: contentEl.value
                        });
                    }
                });
                component.content = { tabs: tabItems };
                break;
            case 'spacer':
                component.content = {
                    height: document.getElementById('edit_height').value
                };
                break;
            case 'html':
                component.content = {
                    code: document.getElementById('edit_code').value
                };
                break;
        }
        
        renderPage();
    }
    
    closeModal();
}

// Move section
function moveSection(index, direction) {
    const newIndex = index + direction;
    if (newIndex >= 0 && newIndex < pageData.sections.length) {
        [pageData.sections[index], pageData.sections[newIndex]] = [pageData.sections[newIndex], pageData.sections[index]];
        renderPage();
    }
}

// Delete section
function deleteSection(index) {
    if (confirm('هل تريد حذف هذا القسم؟')) {
        pageData.sections.splice(index, 1);
        renderPage();
    }
}

// Delete component
function deleteComponent(sIndex, colIndex, cIndex) {
    if (confirm('هل تريد حذف هذا المكون؟')) {
        pageData.sections[sIndex].grid[colIndex].splice(cIndex, 1);
        renderPage();
    }
}

// Close modal
function closeModal() {
    document.getElementById('settingsModal').classList.remove('active');
    currentEditTarget = null;
}

// Switch Image Mode
function switchImageMode(mode) {
    document.getElementById('imgModeUrl').classList.toggle('active', mode === 'url');
    document.getElementById('imgModeUpload').classList.toggle('active', mode === 'upload');
    document.getElementById('imageUrlMode').style.display = mode === 'url' ? 'block' : 'none';
    document.getElementById('imageUploadMode').style.display = mode === 'upload' ? 'block' : 'none';
}

// Switch Video Mode
function switchVideoMode(mode) {
    document.getElementById('vidModeYoutube').classList.toggle('active', mode === 'youtube');
    document.getElementById('vidModeUpload').classList.toggle('active', mode === 'upload');
    document.getElementById('videoYoutubeMode').style.display = mode === 'youtube' ? 'block' : 'none';
    document.getElementById('videoUploadMode').style.display = mode === 'upload' ? 'block' : 'none';
}

// Switch Icon Mode
function switchIconMode(mode) {
    document.getElementById('iconModeEmoji').classList.toggle('active', mode === 'emoji');
    document.getElementById('iconModeImage').classList.toggle('active', mode === 'image');
    document.getElementById('iconEmojiMode').style.display = mode === 'emoji' ? 'block' : 'none';
    document.getElementById('iconImageMode').style.display = mode === 'image' ? 'block' : 'none';
}

// Handle Image Upload
async function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'image');
    
    try {
        const response = await fetch('{{ route("admin.theme.upload") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('edit_url').value = data.url;
            document.getElementById('uploadPreview').innerHTML = `
                <img src="${data.url}" style="max-width: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <p style="color: #10b981; margin-top: 8px;">✓ تم الرفع بنجاح</p>
            `;
        } else {
            alert('فشل رفع الصورة: ' + data.message);
        }
    } catch (error) {
        alert('حدث خطأ أثناء رفع الصورة');
        console.error(error);
    }
}

// Handle Video Upload
async function handleVideoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (file.size > 50 * 1024 * 1024) {
        alert('حجم الفيديو يجب أن يكون أقل من 50 ميجابايت');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'video');
    
    document.getElementById('videoUploadPreview').innerHTML = '<p style="color:#3b82f6;">⏳ جاري الرفع...</p>';
    
    try {
        const response = await fetch('{{ route("admin.theme.upload") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('edit_url').value = data.url;
            document.getElementById('videoUploadPreview').innerHTML = `
                <video src="${data.url}" controls style="max-width: 300px; border-radius: 8px;"></video>
                <p style="color: #10b981; margin-top: 8px;">✓ تم الرفع بنجاح</p>
            `;
        } else {
            document.getElementById('videoUploadPreview').innerHTML = `<p style="color:#ef4444;">✗ فشل الرفع: ${data.message}</p>`;
        }
    } catch (error) {
        document.getElementById('videoUploadPreview').innerHTML = '<p style="color:#ef4444;">✗ حدث خطأ أثناء الرفع</p>';
        console.error(error);
    }
}

// Handle Icon Upload
async function handleIconUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'icon');
    
    try {
        const response = await fetch('{{ route("admin.theme.upload") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('edit_icon').value = data.url;
            document.getElementById('iconUploadPreview').innerHTML = `
                <img src="${data.url}" style="max-width: 100px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <p style="color: #10b981; margin-top: 8px;">✓ تم الرفع بنجاح</p>
            `;
        } else {
            alert('فشل رفع الصورة: ' + data.message);
        }
    } catch (error) {
        alert('حدث خطأ أثناء رفع الصورة');
        console.error(error);
    }
}

// Update JSON data
function updateJsonData() {
    document.getElementById('jsonData').value = JSON.stringify(pageData);
}

// Accordion helpers
function addAccordionItem() {
    const container = document.getElementById('accordion_items');
    const index = document.querySelectorAll('.accordion-title').length;
    const itemHTML = `
        <div style="padding: 16px; background: #f8fafc; border-radius: 8px; margin-bottom: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <strong>عنصر ${index + 1}</strong>
                <button type="button" onclick="removeAccordionItem(${index})" style="color: #ef4444; border: none; background: none; cursor: pointer; font-size: 18px;">🗑️</button>
            </div>
            <input type="text" class="admin-input accordion-title" data-index="${index}" value="" placeholder="العنوان" style="margin-bottom: 8px;">
            <textarea class="admin-input accordion-content" data-index="${index}" rows="2" placeholder="المحتوى"></textarea>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHTML);
}

function removeAccordionItem(index) {
    const items = document.querySelectorAll('#accordion_items > div');
    if (items.length > 1 && items[index]) {
        items[index].remove();
        // Reindex
        document.querySelectorAll('.accordion-title').forEach((el, i) => {
            el.dataset.index = i;
            el.parentElement.querySelector('strong').textContent = `عنصر ${i + 1}`;
        });
    }
}

// Tabs helpers
function addTabItem() {
    const container = document.getElementById('tabs_items');
    const index = document.querySelectorAll('.tab-title').length;
    const itemHTML = `
        <div style="padding: 16px; background: #f8fafc; border-radius: 8px; margin-bottom: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <strong>تبويب ${index + 1}</strong>
                <button type="button" onclick="removeTabItem(${index})" style="color: #ef4444; border: none; background: none; cursor: pointer; font-size: 18px;">🗑️</button>
            </div>
            <input type="text" class="admin-input tab-title" data-index="${index}" value="" placeholder="العنوان" style="margin-bottom: 8px;">
            <textarea class="admin-input tab-content" data-index="${index}" rows="2" placeholder="المحتوى"></textarea>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHTML);
}

function removeTabItem(index) {
    const items = document.querySelectorAll('#tabs_items > div');
    if (items.length > 1 && items[index]) {
        items[index].remove();
        // Reindex
        document.querySelectorAll('.tab-title').forEach((el, i) => {
            el.dataset.index = i;
            el.parentElement.querySelector('strong').textContent = `تبويب ${i + 1}`;
        });
    }
}

// Undo/Redo system
function saveHistory() {
    // Remove future states if we're not at the end
    if (historyIndex < historyStack.length - 1) {
        historyStack = historyStack.slice(0, historyIndex + 1);
    }
    
    // Add current state
    historyStack.push(JSON.parse(JSON.stringify(pageData)));
    
    // Limit history size
    if (historyStack.length > MAX_HISTORY) {
        historyStack.shift();
    } else {
        historyIndex++;
    }
    
    updateUndoRedoButtons();
}

function undo() {
    if (historyIndex > 0) {
        historyIndex--;
        pageData = JSON.parse(JSON.stringify(historyStack[historyIndex]));
        renderPage();
        updateUndoRedoButtons();
    }
}

function redo() {
    if (historyIndex < historyStack.length - 1) {
        historyIndex++;
        pageData = JSON.parse(JSON.stringify(historyStack[historyIndex]));
        renderPage();
        updateUndoRedoButtons();
    }
}

function updateUndoRedoButtons() {
    const undoBtn = document.getElementById('undoBtn');
    const redoBtn = document.getElementById('redoBtn');
    if (undoBtn) undoBtn.disabled = historyIndex <= 0;
    if (redoBtn) redoBtn.disabled = historyIndex >= historyStack.length - 1;
}

// Copy/Paste system
function copyComponent(sIndex, colIndex, cIndex) {
    clipboardComponent = {
        type: 'component',
        data: JSON.parse(JSON.stringify(pageData.sections[sIndex].grid[colIndex][cIndex]))
    };
    showToast('✅ تم النسخ!');
}

function pasteComponent(sIndex, colIndex) {
    if (!clipboardComponent || clipboardComponent.type !== 'component') {
        showToast('⚠️ لا يوجد مكون منسوخ!');
        return;
    }
    
    const component = JSON.parse(JSON.stringify(clipboardComponent.data));
    pageData.sections[sIndex].grid[colIndex].push(component);
    saveHistory();
    renderPage();
    showToast('✅ تم اللصق!');
}

function duplicateComponent(sIndex, colIndex, cIndex) {
    const component = JSON.parse(JSON.stringify(pageData.sections[sIndex].grid[colIndex][cIndex]));
    pageData.sections[sIndex].grid[colIndex].splice(cIndex + 1, 0, component);
    saveHistory();
    renderPage();
    showToast('✅ تم التكرار!');
}

function duplicateSection(index) {
    const section = JSON.parse(JSON.stringify(pageData.sections[index]));
    pageData.sections.splice(index + 1, 0, section);
    saveHistory();
    renderPage();
    showToast('✅ تم تكرار القسم!');
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1e293b;color:white;padding:12px 24px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.3);z-index:10000;animation:slideUp 0.3s ease;';
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideDown 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + Z = Undo
    if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
        e.preventDefault();
        undo();
    }
    // Ctrl/Cmd + Shift + Z = Redo
    if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) {
        e.preventDefault();
        redo();
    }
    // Ctrl/Cmd + S = Save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.getElementById('pageForm').requestSubmit();
    }
});

// Form submit
document.getElementById('pageForm').addEventListener('submit', function(e) {
    if (pageData.sections.length === 0) {
        e.preventDefault();
        alert('يجب إضافة قسم واحد على الأقل!');
        return;
    }
    updateJsonData();
});

// Preview
function preview() {
    updateJsonData();
    const slug = document.querySelector('[name="slug"]').value;
    window.open('/pages/' + slug, '_blank');
}
</script>
@endsection
