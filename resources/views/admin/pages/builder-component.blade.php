{{-- Page Builder Component --}}
<div class="builder-wrapper" style="display: flex; gap: 0; height: 600px; background: #f8fafc; border-radius: 12px; overflow: hidden;">
    <!-- Sidebar -->
    <div class="builder-sidebar" style="width: 300px; background: white; border-left: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden;">
        <div class="sidebar-header" style="padding: 20px; border-bottom: 1px solid #e2e8f0;">
            <div style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">🎨 مكونات الصفحة</div>
            <div style="font-size: 12px; color: #64748b;">اسحب المكونات لبناء صفحتك</div>
        </div>
        
        <div class="sidebar-tabs" style="display: flex; border-bottom: 2px solid #e2e8f0;">
            <button type="button" class="sidebar-tab active" onclick="switchBuilderTab('sections')" style="flex: 1; padding: 10px; text-align: center; cursor: pointer; border: none; background: transparent; font-weight: 600; font-size: 13px; color: #64748b;">أقسام</button>
            <button type="button" class="sidebar-tab" onclick="switchBuilderTab('components')" style="flex: 1; padding: 10px; text-align: center; cursor: pointer; border: none; background: transparent; font-weight: 600; font-size: 13px; color: #64748b;">مكونات</button>
        </div>
        
        <div class="sidebar-content" style="flex: 1; overflow-y: auto; padding: 16px;">
            <!-- Sections Tab -->
            <div id="sectionsBuilderTab" class="tab-content">
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 10px;">📐 تخطيطات الشبكة</div>
                    
                    @foreach([1, 2, 3, 4] as $cols)
                    <div class="component-item" draggable="true" data-type="section" data-cols="{{ $cols }}" style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; padding: 10px; margin-bottom: 8px; cursor: grab; display: flex; align-items: center; gap: 10px;">
                        <div style="font-size: 20px; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; background: white; border-radius: 6px;">
                            {{ str_repeat('▬', $cols) }}
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #1e293b; font-size: 12px;">{{ $cols == 1 ? 'عمود واحد' : ($cols == 2 ? 'عمودين' : ($cols == 3 ? 'ثلاثة أعمدة' : 'أربعة أعمدة')) }}</div>
                            <div style="font-size: 10px; color: #64748b;">{{ $cols == 1 ? '100%' : (100/$cols).'%' }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Components Tab -->
            <div id="componentsBuilderTab" class="tab-content" style="display: none;">
                @php
                $components = [
                    ['type' => 'heading', 'icon' => 'H', 'name' => 'عنوان', 'desc' => 'Heading'],
                    ['type' => 'paragraph', 'icon' => '¶', 'name' => 'فقرة', 'desc' => 'Paragraph'],
                    ['type' => 'button', 'icon' => '▶', 'name' => 'زر', 'desc' => 'Button'],
                    ['type' => 'image', 'icon' => '🖼', 'name' => 'صورة', 'desc' => 'Image'],
                    ['type' => 'video', 'icon' => '▶️', 'name' => 'فيديو', 'desc' => 'Video'],
                    ['type' => 'card', 'icon' => '🎴', 'name' => 'بطاقة', 'desc' => 'Card'],
                ];
                @endphp
                
                @foreach($components as $comp)
                <div class="component-item" draggable="true" data-type="component" data-component="{{ $comp['type'] }}" style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; padding: 10px; margin-bottom: 8px; cursor: grab; display: flex; align-items: center; gap: 10px;">
                    <div style="font-size: 18px; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; background: white; border-radius: 6px;">{{ $comp['icon'] }}</div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #1e293b; font-size: 12px;">{{ $comp['name'] }}</div>
                        <div style="font-size: 10px; color: #64748b;">{{ $comp['desc'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Canvas -->
    <div class="builder-canvas" style="flex: 1; overflow-y: auto; padding: 20px;">
        <div class="canvas-inner" id="builderCanvas" style="background: white; border-radius: 12px; min-height: 500px; box-shadow: 0 2px 12px rgba(0,0,0,0.05);">
            <div class="empty-canvas" id="emptyBuilderCanvas" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 500px; padding: 40px;">
                <div style="font-size: 60px; margin-bottom: 20px; opacity: 0.3;">🎨</div>
                <div style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">ابدأ ببناء صفحتك</div>
                <div style="font-size: 14px; color: #64748b;">اسحب قسم من القائمة اليسرى لإضافته للصفحة</div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="json_data" id="jsonData">

<!-- Settings Modal -->
<div class="modal" id="builderSettingsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 99999; align-items: center; justify-content: center; padding: 24px;">
    <div class="modal-content" style="background: white; border-radius: 16px; padding: 32px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header" id="builderModalTitle" style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 20px;">⚙️ إعدادات</div>
        <div id="builderModalForm"></div>
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; padding-top: 20px; border-top: 2px solid #e2e8f0;">
            <button type="button" onclick="closeBuilderModal()" style="background: #e2e8f0; color: #64748b; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">إلغاء</button>
            <button type="button" onclick="saveBuilderSettings()" style="background: var(--color-primary); color: white; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">حفظ</button>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/page-builder.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/page-builder.js') }}"></script>
<script>
// Initialize page data
let pageData = {
    sections: @json($page->json_data['sections'] ?? [])
};

// Render on load
document.addEventListener('DOMContentLoaded', function() {
    renderPage();
    initDragDrop();
});

// ... (Include all the builder functions from edit-pro.blade.php)
// For simplicity, we'll create a separate JS file
</script>
@endpush
