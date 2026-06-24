@extends('layouts.admin')

@section('title', 'إدارة الصفحات')
@section('page-title', 'بناء الصفحات')

@section('content')
    <!-- Header Actions -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 600; color: #1e293b; margin-bottom: 4px;">إدارة صفحات الموقع</h2>
            <p style="color: #64748b; font-size: 14px;">قم بإنشاء وتعديل صفحات الموقع باستخدام محرر Drag & Drop</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="admin-btn admin-btn-primary">
            <span>➕</span>
            إنشاء صفحة جديدة
        </a>
    </div>

    <!-- Pages Grid -->
    @if($pages->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
            @foreach($pages as $page)
                <div class="admin-card" style="position: relative;">
                    <!-- Active Badge -->
                    @if($page->is_active)
                        <div style="position: absolute; top: 16px; left: 16px; background: #10b981; color: white; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 600;">
                            نشط
                        </div>
                    @else
                        <div style="position: absolute; top: 16px; left: 16px; background: #ef4444; color: white; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 600;">
                            معطل
                        </div>
                    @endif

                    <div style="padding: 24px;">
                        <!-- Page Icon -->
                        <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #3CCB8A 0%, #2fb577 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                            📄
                        </div>

                        <!-- Page Info -->
                        <h3 style="font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">
                            {{ $page->page_name }}
                        </h3>
                        <p style="font-size: 14px; color: #64748b; margin-bottom: 4px;">
                            /{{ $page->slug }}
                        </p>
                        <p style="font-size: 12px; color: #94a3b8;">
                            آخر تحديث: {{ $page->updated_at->format('Y-m-d') }}
                        </p>

                        <!-- Meta Info -->
                        @if($page->meta_title)
                            <div style="margin-top: 16px; padding: 12px; background: #f8fafc; border-radius: 8px;">
                                <p style="font-size: 12px; color: #64748b; margin-bottom: 4px;">عنوان SEO:</p>
                                <p style="font-size: 13px; color: #334155; font-weight: 500;">{{ \Illuminate\Support\Str::limit($page->meta_title, 50) }}</p>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div style="display: flex; gap: 8px; margin-top: 16px;">
                            @if($page->is_active)
                                <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="admin-btn admin-btn-secondary" style="flex: 1; padding: 8px 12px; font-size: 13px;">
                                    👁️ معاينة
                                </a>
                            @endif
                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="admin-btn admin-btn-primary" style="flex: 1; padding: 8px 12px; font-size: 13px;">
                                ✏️ تعديل
                            </a>
                            <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" style="flex: 1;" onsubmit="return confirmDelete(event, '{{ $page->page_name }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-outline" style="width: 100%; padding: 8px 12px; font-size: 13px; color: #ef4444; border-color: #ef4444;">
                                    🗑️ حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="admin-card">
            <div style="text-align: center; padding: 64px 24px;">
                <div style="font-size: 64px; margin-bottom: 16px;">📄</div>
                <h3 style="font-size: 20px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">
                    لا توجد صفحات حتى الآن
                </h3>
                <p style="color: #64748b; margin-bottom: 24px;">
                    ابدأ بإنشاء صفحة جديدة باستخدام محرر Drag & Drop
                </p>
                <a href="{{ route('admin.pages.create') }}" class="admin-btn admin-btn-primary">
                    <span>➕</span>
                    إنشاء أول صفحة
                </a>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
function confirmDelete(event, pageName) {
    event.preventDefault();
    
    showConfirm(
        `هل أنت متأكد من حذف صفحة "<strong>${pageName}</strong>"؟<br><br>⚠️ هذا الإجراء لا يمكن التراجع عنه!`,
        () => {
            event.target.submit();
        },
        'تأكيد الحذف',
        'نعم، احذف الصفحة',
        'إلغاء'
    );
    
    return false;
}
</script>
@endpush

