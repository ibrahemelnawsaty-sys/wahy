@extends('layouts.school-admin')

@section('title', 'إضافة طالب جديد')

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
                        <i class="fas fa-user-plus text-success me-2"></i>
                        إضافة طالب جديد
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('school-admin.students.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">الاسم <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">تاريخ الميلاد</label>
                            <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" 
                                   value="{{ old('birth_date') }}" max="{{ date('Y-m-d') }}">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                سيتم حساب العمر تلقائياً من تاريخ الميلاد
                            </small>
                            @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">الفصول الدراسية</label>
                            <select name="classrooms[]" class="form-select @error('classrooms') is-invalid @enderror" multiple size="5">
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" 
                                        {{ in_array($classroom->id, old('classrooms', [])) ? 'selected' : '' }}>
                                        {{ $classroom->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">اضغط Ctrl للاختيار المتعدد</small>
                            @error('classrooms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">كلمة المرور <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-save me-2"></i> حفظ
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


