@extends('layouts.admin')

@section('title', 'إضافة منتج جديد')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.shop.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-right text-xl"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-800">إضافة منتج جديد</h1>
        </div>
        <p class="text-gray-600">أضف منتج جديد إلى المتجر</p>
    </div>

    <form action="{{ route('admin.shop.store') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl">
        @csrf

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Basic Information -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 mb-4">المعلومات الأساسية</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            اسم المنتج <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="مثال: صورة رمزية - الأسد الشجاع" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            الوصف
                        </label>
                        <textarea name="description" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="وصف مختصر للمنتج...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            نوع المنتج <span class="text-red-500">*</span>
                        </label>
                        <select name="type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            <option value="">اختر النوع</option>
                            <option value="avatar" {{ old('type') == 'avatar' ? 'selected' : '' }}>صورة رمزية</option>
                            <option value="theme" {{ old('type') == 'theme' ? 'selected' : '' }}>ثيم</option>
                            <option value="badge" {{ old('type') == 'badge' ? 'selected' : '' }}>شارة</option>
                            <option value="power_up" {{ old('type') == 'power_up' ? 'selected' : '' }}>تعزيز</option>
                            <option value="special" {{ old('type') == 'special' ? 'selected' : '' }}>خاص</option>
                        </select>
                        @error('type')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rarity -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            الندرة <span class="text-red-500">*</span>
                        </label>
                        <select name="rarity" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            <option value="">اختر الندرة</option>
                            <option value="common" {{ old('rarity') == 'common' ? 'selected' : '' }}>⚪ عادي</option>
                            <option value="rare" {{ old('rarity') == 'rare' ? 'selected' : '' }}>🔵 نادر</option>
                            <option value="epic" {{ old('rarity') == 'epic' ? 'selected' : '' }}>🟣 أسطوري</option>
                            <option value="legendary" {{ old('rarity') == 'legendary' ? 'selected' : '' }}>🟡 خرافي</option>
                        </select>
                        @error('rarity')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            السعر (بالعملات) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="price" value="{{ old('price') }}" min="1"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="100" required>
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-2xl">🪙</span>
                        </div>
                        @error('price')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Icon -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            الأيقونة (إيموجي) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="icon" value="{{ old('icon', '🎁') }}" maxlength="10"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center text-3xl"
                               placeholder="🎁" required>
                        @error('icon')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 mb-4">صورة المنتج</h2>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        رفع صورة (اختياري)
                    </label>
                    <input type="file" name="image" accept="image/*" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           onchange="previewImage(event)">
                    <p class="text-sm text-gray-500 mt-1">الحد الأقصى: 2MB - الصيغ المدعومة: JPG, PNG, GIF</p>
                    @error('image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-4 hidden">
                        <img src="" alt="Preview" class="w-32 h-32 rounded-lg object-cover border-2 border-gray-300">
                    </div>
                </div>
            </div>

            <!-- Stock & Availability -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 mb-4">المخزون والتوفر</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Stock -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            المخزون
                        </label>
                        <input type="number" name="stock" value="{{ old('stock') }}" min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="اتركه فارغاً للمخزون غير المحدود">
                        <p class="text-sm text-gray-500 mt-1">اتركه فارغاً إذا كان المنتج غير محدود</p>
                        @error('stock')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Order -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            ترتيب العرض
                        </label>
                        <input type="number" name="order" value="{{ old('order', 0) }}" min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="0">
                        <p class="text-sm text-gray-500 mt-1">الأقل يظهر أولاً</p>
                        @error('order')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Is Limited -->
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_limited" value="1" {{ old('is_limited') ? 'checked' : '' }}
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                                   onchange="toggleLimitedDate(this)">
                            <span class="text-sm font-semibold text-gray-700">منتج محدود الوقت</span>
                        </label>
                    </div>

                    <!-- Available Until -->
                    <div id="limitedDateContainer" class="md:col-span-2 hidden">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            متاح حتى تاريخ
                        </label>
                        <input type="datetime-local" name="available_until" value="{{ old('available_until') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('available_until')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">الحالة</h2>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        حالة المنتج <span class="text-red-500">*</span>
                    </label>
                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="p-6 bg-gray-50 border-t border-gray-200 flex items-center justify-between gap-4">
                <a href="{{ route('admin.shop.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    إلغاء
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save ml-2"></i>
                    حفظ المنتج
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}

function toggleLimitedDate(checkbox) {
    const container = document.getElementById('limitedDateContainer');
    if (checkbox.checked) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

// Check on page load
document.addEventListener('DOMContentLoaded', function() {
    const limitedCheckbox = document.querySelector('input[name="is_limited"]');
    if (limitedCheckbox && limitedCheckbox.checked) {
        document.getElementById('limitedDateContainer').classList.remove('hidden');
    }
});
</script>
@endpush
@endsection
