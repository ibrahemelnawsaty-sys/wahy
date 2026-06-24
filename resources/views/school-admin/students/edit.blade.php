@extends('layouts.school-admin')

@section('title', 'تعديل طالب')

@section('content')
    <div class="mb-4">
        <a href="{{ route('school-admin.students') }}" class="text-decoration-none">
            <i class="fas fa-arrow-right me-2"></i> العودة إلى قائمة الطلاب
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h4 class="mb-0 fw-bold">
                        <i class="fas fa-edit text-success me-2"></i>
                        تعديل طالب: {{ $student->name }}
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('school-admin.students.update', $student->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">الاسم <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $student->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $student->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">تاريخ الميلاد</label>
                            <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" 
                                   value="{{ old('birth_date', $student->birth_date ? $student->birth_date->format('Y-m-d') : '') }}" 
                                   max="{{ date('Y-m-d') }}">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                @if($student->age)
                                    العمر الحالي: <strong>{{ $student->age }} سنة</strong>
                                @else
                                    سيتم حساب العمر تلقائياً من تاريخ الميلاد
                                @endif
                            </small>
                            @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">الحالة <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $student->status) === 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ old('status', $student->status) === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">الفصول الدراسية</label>
                            <select name="classrooms[]" class="form-select @error('classrooms') is-invalid @enderror" multiple size="5">
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" 
                                        {{ in_array($classroom->id, old('classrooms', $student->classrooms->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $classroom->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">اضغط Ctrl للاختيار المتعدد</small>
                            @error('classrooms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            اترك حقول كلمة المرور فارغة إذا لم ترغب في تغييرها
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">كلمة المرور الجديدة</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-save me-2"></i> حفظ التعديلات
                            </button>
                            <a href="{{ route('school-admin.students') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-2"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


