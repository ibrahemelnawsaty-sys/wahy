{{-- Element Actions Component - For Super Admin Edit Mode --}}
{{-- يظهر فقط عندما يكون وضع التحرير نشط (body.edit-mode) --}}
@auth
@if(auth()->user()->role === 'super_admin')
<div class="element-actions" role="toolbar" aria-label="أدوات تحرير العنصر">
    <button type="button" 
            onclick="window.landingEditorInstance?.editElement(this.closest('.editable-element'))" 
            title="تعديل المحتوى"
            aria-label="تعديل">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
        </svg>
    </button>
    <button type="button" 
            onclick="window.landingEditorInstance?.duplicateElement(this.closest('.editable-element'))" 
            title="نسخ العنصر"
            aria-label="نسخ">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
        </svg>
    </button>
    <button type="button" 
            onclick="if(confirm('{{ $confirmMessage ?? 'حذف هذا العنصر؟' }}')) window.landingEditorInstance?.deleteElement(this.closest('.editable-element'))" 
            title="حذف العنصر"
            aria-label="حذف"
            class="danger">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
        </svg>
    </button>
</div>
@endif
@endauth
