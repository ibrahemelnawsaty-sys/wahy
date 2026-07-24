@extends('layouts.school-admin')
@section('title', 'تعديل فصل')
@section('content')
    <div class="mb-4"><a href="{{ route('school-admin.classrooms') }}" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>العودة</a></div>
    <div class="row justify-content-center"><div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-header bg-white border-0 py-3"><h4 class="mb-0 fw-bold"><i class="fas fa-edit text-warning me-2"></i>تعديل: {{ $classroom->name }}</h4></div><div class="card-body p-4">
        <form action="{{ route('school-admin.classrooms.update', $classroom->id) }}" method="POST">@csrf @method('PUT')
            <div class="mb-3"><label class="form-label fw-bold">اسم الفصل <span class="text-danger">*</span></label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $classroom->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">المعلم <span class="text-danger">*</span></label><select name="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required><option value="">-- اختر --</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" {{ old('teacher_id', $classroom->teacher_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>@endforeach</select>@error('teacher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">المستوى</label><input type="text" name="grade_level" class="form-control @error('grade_level') is-invalid @enderror" value="{{ old('grade_level', $classroom->grade_level) }}">@error('grade_level')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">السنة الدراسية</label><input type="text" name="academic_year" class="form-control @error('academic_year') is-invalid @enderror" value="{{ old('academic_year', $classroom->academic_year) }}">@error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">السعة</label><input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity', $classroom->capacity) }}">@error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">الحالة <span class="text-danger">*</span></label><select name="status" class="form-select @error('status') is-invalid @enderror" required><option value="active" {{ old('status', $classroom->status) === 'active' ? 'selected' : '' }}>نشط</option><option value="archived" {{ old('status', $classroom->status) === 'archived' ? 'selected' : '' }}>مؤرشَف</option></select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label fw-bold">الوصف</label><textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $classroom->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            
            <!-- Students Selection with Modern Checkboxes -->
            <div class="mb-4">
                <label class="form-label fw-bold d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-users text-primary me-2"></i>إضافة طلاب للفصل</span>
                    <span class="badge bg-primary" id="selectedCount">{{ $classroom->students->count() }} محدد</span>
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
                                    {{ $classroom->students->contains($student->id) ? 'checked' : '' }}
                                    style="width: 20px; height: 20px; margin-left: 10px; cursor: pointer; accent-color: #667eea;">
                                <span style="font-weight: 500; font-size: 15px;">{{ $student->name }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i>اضغط على الطالب لاختياره أو إلغاء اختياره</small>
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

.student-checkbox-label:has(input:checked) span {
    color: white !important;
}

.students-selection-wrapper::-webkit-scrollbar {
    width: 8px;
}

.students-selection-wrapper::-webkit-scrollbar-track {
    background: #e0e0e0;
    border-radius: 10px;
}

.students-selection-wrapper::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

.students-selection-wrapper::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('studentSearch');
    const studentItems = document.querySelectorAll('.student-item');
    const checkboxes = document.querySelectorAll('input[name="students[]"]');
    const selectedCount = document.getElementById('selectedCount');
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        
        studentItems.forEach(item => {
            const studentName = item.dataset.name.toLowerCase();
            if (studentName.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Update count
    function updateCount() {
        const count = document.querySelectorAll('input[name="students[]"]:checked').length;
        selectedCount.textContent = count + ' محدد';
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCount);
    });
});
</script>
@endsection



