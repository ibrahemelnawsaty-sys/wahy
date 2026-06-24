@extends('layouts.school-admin')
@section('title', 'إنشاء فصل')
@section('content')
    <div class="mb-4"><a href="{{ route('school-admin.classrooms') }}" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>العودة</a></div>
    <div class="row justify-content-center"><div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-header bg-white border-0 py-3"><h4 class="mb-0 fw-bold"><i class="fas fa-door-open text-warning me-2"></i>إنشاء فصل جديد</h4></div><div class="card-body p-4">
        <form action="{{ route('school-admin.classrooms.store') }}" method="POST">@csrf
            @if($errors->any())
                <div class="alert alert-danger mb-4" style="border-radius: 12px; border: none; background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); color: white; padding: 18px 24px; box-shadow: 0 8px 20px rgba(245, 101, 101, 0.3);">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-exclamation-circle me-2" style="font-size: 20px;"></i>
                        <strong>يرجى تصحيح الأخطاء التالية:</strong>
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="mb-3"><label class="form-label fw-bold">اسم الفصل <span class="text-danger">*</span></label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="مثال: الصف الأول أ">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">المعلم المسؤول <span class="text-danger">*</span></label><select name="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required><option value="">-- اختر المعلم --</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>@endforeach</select>@error('teacher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3">
                <label class="form-label fw-bold">المستوى الدراسي</label>
                @if(isset($educationLevels) && $educationLevels->count() > 0)
                    {{-- Issue #38: dropdown مبني على المراحل المرتبطة بالمدرسة + سنواتها --}}
                    <select name="grade_level" class="form-select @error('grade_level') is-invalid @enderror">
                        <option value="">-- اختر المستوى --</option>
                        @foreach($educationLevels as $level)
                            <optgroup label="{{ $level->name }}">
                                @foreach($level->academicYears as $year)
                                    <option value="{{ $year->name }}" {{ old('grade_level') === $year->name ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                                @if($level->academicYears->isEmpty())
                                    <option value="{{ $level->name }}" {{ old('grade_level') === $level->name ? 'selected' : '' }}>{{ $level->name }}</option>
                                @endif
                            </optgroup>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="grade_level" class="form-control @error('grade_level') is-invalid @enderror" value="{{ old('grade_level') }}" placeholder="مثال: الأول الابتدائي">
                    <small class="text-muted d-block mt-1">لم يتم ربط أي مرحلة دراسية بمدرستك بعد. تواصل مع إدارة الموقع.</small>
                @endif
                @error('grade_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3"><label class="form-label fw-bold">السنة الدراسية</label><input type="text" name="academic_year" class="form-control @error('academic_year') is-invalid @enderror" value="{{ old('academic_year', '2025-2026') }}" placeholder="مثال: 2025-2026">@error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">السعة القصوى</label><input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity') }}" placeholder="عدد الطلاب الأقصى">@error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">الوصف</label><textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            
            <!-- Students Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-users text-primary me-2"></i>إضافة طلاب للفصل</span>
                    <span class="badge bg-primary" id="selectedCount">0 محدد</span>
                </label>
                <div class="students-selection-wrapper" style="background: #f8f9fa; border-radius: 12px; padding: 20px; max-height: 450px; overflow-y: auto;">
                    <div class="mb-3">
                        <input type="text" id="studentSearch" class="form-control" placeholder="🔍 ابحث عن طالب..." style="border-radius: 10px; padding: 12px;">
                    </div>
                    <div id="studentsCheckboxes" class="row g-2">
                        @foreach($students as $student)
                        <div class="col-md-6 student-item" data-name="{{ $student->name }}">
                            <label class="student-checkbox-label" style="display: block; padding: 12px 15px; background: white; border-radius: 10px; cursor: pointer; transition: all 0.3s; border: 2px solid #e0e0e0;">
                                <input type="checkbox" name="students[]" value="{{ $student->id }}" 
                                    {{ in_array($student->id, old('students', [])) ? 'checked' : '' }}
                                    style="width: 20px; height: 20px; margin-left: 10px; cursor: pointer; accent-color: #667eea;">
                                <span style="font-weight: 500; font-size: 15px;">{{ $student->name }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i>اضغط على الطالب لاختياره</small>
            </div>
            
            <div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-warning px-4"><i class="fas fa-save me-2"></i>حفظ</button><a href="{{ route('school-admin.classrooms') }}" class="btn btn-secondary px-4"><i class="fas fa-times me-2"></i>إلغاء</a></div>
        </form>
    </div></div></div></div>
</div>

<style>
.student-checkbox-label:hover {
    border-color: #667eea !important;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
}
.student-checkbox-label:has(input:checked) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-color: #667eea !important;
    color: white !important;
}
.student-checkbox-label:has(input:checked) span { color: white !important; }
.students-selection-wrapper::-webkit-scrollbar { width: 8px; }
.students-selection-wrapper::-webkit-scrollbar-track { background: #e0e0e0; border-radius: 10px; }
.students-selection-wrapper::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('studentSearch');
    const studentItems = document.querySelectorAll('.student-item');
    const checkboxes = document.querySelectorAll('input[name="students[]"]');
    const selectedCount = document.getElementById('selectedCount');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        studentItems.forEach(item => {
            const studentName = item.dataset.name.toLowerCase();
            item.style.display = studentName.includes(searchTerm) ? 'block' : 'none';
        });
    });
    
    function updateCount() {
        const count = document.querySelectorAll('input[name="students[]"]:checked').length;
        selectedCount.textContent = count + ' محدد';
    }
    
    checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateCount));
    updateCount();
});
</script>
@endsection


