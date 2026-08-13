{{-- خطوط المحتوى الغنيّ (الدروس/الأنشطة): الافتراضيّ = خطّ الموقع، + 8 خطوط عربيّة للاختيار.
     يُضمَّن حيث يُعرَض .rich-content. أيّ font-family مضمّن (اختاره المؤلّف من منتقي الخط) يطغى على الافتراضيّ.
     نفس قائمة الخطوط في public/js/rich-editor.js (منتقي المحرّر) — أبقِهما متطابقتَين. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&family=Cairo:wght@400;600;700&family=Almarai:wght@400;700&family=Amiri:wght@400;700&family=Reem+Kufi:wght@400;700&family=El+Messiri:wght@400;700&family=Changa:wght@400;700&family=Markazi+Text:wght@400;700&display=swap" rel="stylesheet">
<style>
    /* الافتراضيّ = خطّ الموقع؛ النصوص التي اختار لها المؤلّف خطًّا (font-family مضمّن) تطغى تلقائيًّا. */
    .rich-content { font-family: 'IBM Plex Sans Arabic', 'Tajawal', 'Segoe UI', Tahoma, sans-serif; }
</style>
