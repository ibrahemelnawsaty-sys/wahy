@extends('layouts.admin')

@section('title', 'محرّر الصفحات')
@section('page-title', 'محرّر الصفحات')

@section('content')
<div class="pb-admin">
    <div class="pb-admin-head">
        <div>
            <h2 class="pb-admin-title">صفحات المحرّر الاحترافيّ</h2>
            <p class="pb-admin-sub">أنشئ صفحاتٍ بالكتل، وحرّر الهيدر والفوتر والجسم كلًّا على حدة، ثمّ انشرها وفعّلها على المسار العامّ.</p>
        </div>
        <a href="{{ route('admin.pb.ui.create') }}" class="btn btn-primary">＋ صفحة جديدة</a>
    </div>

    @if($pages->isEmpty())
        <div class="pb-empty">
            <div class="pb-empty-emoji">📄</div>
            <p>لا صفحات بعد. ابدأ بإنشاء صفحة، أو استعمل <code>php artisan pb:scaffold-home</code> لتوليد رئيسيّة مبدئيّة.</p>
            <a href="{{ route('admin.pb.ui.create') }}" class="btn btn-primary">أنشئ أوّل صفحة</a>
        </div>
    @else
        <div class="pb-table-wrap">
            <table class="pb-table">
                <thead>
                    <tr>
                        <th>العنوان</th><th>المسار</th><th>اللغة</th><th>الحالة</th><th>مباشر؟</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pages as $p)
                        <tr>
                            <td class="pb-td-title">{{ $p->title }}</td>
                            <td><code>/{{ $p->slug }}</code></td>
                            <td>{{ $p->locale }}</td>
                            <td>
                                @if($p->status === 'published')
                                    <span class="pb-badge pb-badge-green">منشورة</span>
                                @else
                                    <span class="pb-badge pb-badge-gray">مسودّة</span>
                                @endif
                            </td>
                            <td>
                                @if(in_array($p->slug, $liveSlugs, true))
                                    <span class="pb-badge pb-badge-blue">مباشر ✓</span>
                                @else
                                    <span class="pb-badge pb-badge-gray">—</span>
                                @endif
                            </td>
                            <td class="pb-td-actions">
                                <a href="{{ route('admin.pb.ui.edit', $p) }}" class="btn btn-sm btn-outline-primary">تحرير</a>
                                <button class="btn btn-sm btn-outline-danger" data-pb-delete="{{ $p->id }}" data-pb-title="{{ $p->title }}">حذف</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .pb-admin-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:20px;flex-wrap:wrap}
    .pb-admin-title{margin:0 0 4px;font-size:1.4rem;font-weight:800}
    .pb-admin-sub{margin:0;color:#6b7280;font-size:.92rem;max-width:640px}
    .pb-table-wrap{overflow-x:auto;border:1px solid #e5e7eb;border-radius:14px;background:#fff}
    .pb-table{width:100%;border-collapse:collapse;font-size:.95rem}
    .pb-table th,.pb-table td{padding:12px 16px;text-align:start;border-bottom:1px solid #f1f5f9}
    .pb-table th{background:#f8fafc;font-weight:700;color:#475569;font-size:.85rem}
    .pb-table tr:last-child td{border-bottom:0}
    .pb-td-title{font-weight:700}
    .pb-td-actions{display:flex;gap:8px;justify-content:flex-end;white-space:nowrap}
    .pb-badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:.78rem;font-weight:700}
    .pb-badge-green{background:#dcfce7;color:#166534}
    .pb-badge-blue{background:#dbeafe;color:#1e40af}
    .pb-badge-gray{background:#f1f5f9;color:#94a3b8}
    .pb-empty{text-align:center;padding:60px 20px;border:2px dashed #e5e7eb;border-radius:16px;color:#6b7280}
    .pb-empty-emoji{font-size:2.5rem;margin-bottom:8px}
    .pb-empty .btn{margin-top:12px}
    @media (prefers-color-scheme: dark){
        .pb-table-wrap{background:#0b1220;border-color:#1f2937}
        .pb-table th{background:#111827;color:#94a3b8}
        .pb-table th,.pb-table td{border-color:#1f2937}
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-pb-delete]');
    if (!btn) return;
    const id = btn.getAttribute('data-pb-delete');
    const title = btn.getAttribute('data-pb-title') || '';
    if (!confirm('حذف الصفحة «' + title + '»؟ لا يمكن التراجع.')) return;
    const token = document.querySelector('meta[name="csrf-token"]').content;
    fetch('{{ url('admin/pb/pages') }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
    }).then(r => {
        if (r.ok) { btn.closest('tr').remove(); }
        else { alert('تعذّر الحذف.'); }
    }).catch(() => alert('تعذّر الاتّصال.'));
});
</script>
@endpush
