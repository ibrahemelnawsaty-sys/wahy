@extends('layouts.admin')

@section('title', 'إدارة المتجر')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🛍️ إدارة المتجر</h1>
            <p class="text-gray-600">إدارة منتجات المتجر والعناصر القابلة للشراء</p>
        </div>
        <a href="{{ route('admin.shop.create') }}" class="btn btn-primary">
            <i class="fas fa-plus ml-2"></i>
            إضافة منتج جديد
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">إجمالي المنتجات</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['total_items'] }}</h3>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-box text-blue-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">المنتجات النشطة</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['active_items'] }}</h3>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">نفذت من المخزون</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['sold_out'] }}</h3>
                </div>
                <div class="bg-red-100 p-4 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-r-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">إجمالي المبيعات</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['total_purchases'] }}</h3>
                </div>
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas fa-shopping-cart text-purple-500 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">قائمة المنتجات</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">النوع</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">السعر</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">الندرة</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">المخزون</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($items as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($item->image)
                                    <img src="{{ asset('storage/app/public/data/' . $item->image) }}" alt="{{ $item->name }}" class="w-12 h-12 rounded-lg object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-2xl">
                                        {{ $item->icon }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $item->name }}</p>
                                    <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($item->description, 60) }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $typeColors = [
                                    'avatar' => 'bg-blue-100 text-blue-800',
                                    'theme' => 'bg-purple-100 text-purple-800',
                                    'badge' => 'bg-yellow-100 text-yellow-800',
                                    'power_up' => 'bg-red-100 text-red-800',
                                    'special' => 'bg-pink-100 text-pink-800',
                                ];
                                $typeNames = [
                                    'avatar' => 'صورة رمزية',
                                    'theme' => 'ثيم',
                                    'badge' => 'شارة',
                                    'power_up' => 'تعزيز',
                                    'special' => 'خاص',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $typeColors[$item->type] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $typeNames[$item->type] ?? $item->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-yellow-600">{{ number_format($item->price) }} 🪙</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $rarityColors = [
                                    'common' => 'bg-gray-100 text-gray-800',
                                    'rare' => 'bg-blue-100 text-blue-800',
                                    'epic' => 'bg-purple-100 text-purple-800',
                                    'legendary' => 'bg-yellow-100 text-yellow-800',
                                ];
                                $rarityNames = [
                                    'common' => 'عادي',
                                    'rare' => 'نادر',
                                    'epic' => 'أسطوري',
                                    'legendary' => 'خرافي',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $rarityColors[$item->rarity] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $rarityNames[$item->rarity] ?? $item->rarity }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($item->stock === null)
                                <span class="text-green-600 font-semibold">∞ غير محدود</span>
                            @else
                                <span class="font-semibold {{ $item->stock > 10 ? 'text-green-600' : ($item->stock > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $item->stock }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($item->status === 'active')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">نشط</span>
                            @elseif($item->status === 'sold_out')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">نفذ</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">غير نشط</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.shop.edit', $item->id) }}" class="text-blue-600 hover:text-blue-800 transition-colors" title="تعديل">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                                <button onclick="deleteItem({{ $item->id }})" class="text-red-600 hover:text-red-800 transition-colors" title="حذف">
                                    <i class="fas fa-trash text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 text-lg mb-4">لا توجد منتجات في المتجر</p>
                                <a href="{{ route('admin.shop.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus ml-2"></i>
                                    إضافة أول منتج
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
        <div class="p-6 border-t border-gray-200">
            {{ $items->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function deleteItem(id) {
    if (confirm('هل أنت متأكد من حذف هذا المنتج؟')) {
        fetch(`/admin/shop/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ أثناء الحذف');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء الحذف');
        });
    }
}
</script>
@endpush
@endsection
