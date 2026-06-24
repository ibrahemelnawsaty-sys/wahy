@extends('layouts.school-admin')
@section('title', 'إضافة ولي أمر')
@section('content')
    <div class="mb-4"><a href="{{ route('school-admin.parents') }}" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>العودة</a></div>
    <div class="row justify-content-center"><div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-header bg-white border-0 py-3"><h4 class="mb-0 fw-bold"><i class="fas fa-user-plus text-info me-2"></i>إضافة ولي أمر</h4></div><div class="card-body p-4">
        <form action="{{ route('school-admin.parents.store') }}" method="POST">@csrf
            <div class="mb-3"><label class="form-label fw-bold">الاسم <span class="text-danger">*</span></label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">البريد الإلكتروني <span class="text-danger">*</span></label><input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">رقم الهاتف</label><input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">نوع العلاقة</label><input type="text" name="relationship" class="form-control" value="{{ old('relationship', 'parent') }}" placeholder="أب / أم / وصي"></div>
            <div class="mb-4">
                <label class="form-label fw-bold d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-child text-info me-2"></i>الأبناء (الطلاب)</span>
                    <span class="badge bg-info" id="childrenCount">0 محدد</span>
                </label>
                <div class="children-selection-wrapper" style="background: #f8f9fa; border-radius: 12px; padding: 20px; max-height: 350px; overflow-y: auto;">
                    <div class="mb-3">
                        <input type="text" id="childrenSearch" class="form-control" placeholder="🔍 ابحث عن طالب..." style="border-radius: 10px; padding: 12px;">
                    </div>
                    <div id="childrenCheckboxes" class="row g-2">
                        @foreach($students as $student)
                        <div class="col-md-6 child-item" data-name="{{ $student->name }}">
                            <label class="child-checkbox-label" style="display: block; padding: 12px 15px; background: white; border-radius: 10px; cursor: pointer; transition: all 0.3s; border: 2px solid #e0e0e0;">
                                <input type="checkbox" name="children[]" value="{{ $student->id }}" 
                                    {{ in_array($student->id, old('children', [])) ? 'checked' : '' }}
                                    style="width: 20px; height: 20px; margin-left: 10px; cursor: pointer; accent-color: #17a2b8;">
                                <span style="font-weight: 500; font-size: 15px;">{{ $student->name }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i>اضغط على الطالب لاختياره</small>
                @error('children')<div class="text-danger mt-2">{{ $message }}</div>@enderror
            </div>
            <style>
            .child-checkbox-label:hover {
                border-color: #17a2b8 !important;
                box-shadow: 0 4px 12px rgba(23, 162, 184, 0.15);
                transform: translateY(-2px);
            }
            .child-checkbox-label:has(input:checked) {
                background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
                border-color: #17a2b8 !important;
                color: white !important;
            }
            .child-checkbox-label:has(input:checked) span { color: white !important; }
            </style>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('childrenSearch');
                const childItems = document.querySelectorAll('.child-item');
                const checkboxes = document.querySelectorAll('input[name="children[]"]');
                const childrenCount = document.getElementById('childrenCount');
                
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.trim().toLowerCase();
                    childItems.forEach(item => {
                        const name = item.dataset.name.toLowerCase();
                        item.style.display = name.includes(searchTerm) ? 'block' : 'none';
                    });
                });
                
                function updateCount() {
                    const count = document.querySelectorAll('input[name="children[]"]:checked').length;
                    childrenCount.textContent = count + ' محدد';
                }
                
                checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateCount));
                updateCount();
            });
            </script>
            <div class="mb-3"><label class="form-label fw-bold">كلمة المرور <span class="text-danger">*</span></label><input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">تأكيد كلمة المرور <span class="text-danger">*</span></label><input type="password" name="password_confirmation" class="form-control" required></div>
            <div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-info px-4"><i class="fas fa-save me-2"></i>حفظ</button><a href="{{ route('school-admin.parents') }}" class="btn btn-secondary px-4"><i class="fas fa-times me-2"></i>إلغاء</a></div>
        </form>
    </div></div></div></div>
</div>
@endsection


