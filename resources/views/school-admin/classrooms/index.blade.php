@extends('layouts.school-admin')

@section('page-title', 'إدارة الفصول')
@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / إدارة الفصول
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-door-open text-warning me-2"></i>إدارة الفصول الدراسية</h2>
        <a href="{{ route('school-admin.classrooms.create') }}" class="btn btn-warning"><i class="fas fa-plus me-2"></i>إنشاء فصل جديد</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>اسم الفصل</th>
                            <th>المعلم</th>
                            <th>المستوى</th>
                            <th>عدد الطلاب</th>
                            <th>السنة الدراسية</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classrooms as $classroom)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $classroom->name }}</strong></td>
                                <td>{{ $classroom->teacher->name ?? '-' }}</td>
                                <td>{{ $classroom->grade_level ?? '-' }}</td>
                                <td><span class="badge bg-primary">{{ $classroom->students_count }} طالب</span></td>
                                <td>{{ $classroom->academic_year ?? '-' }}</td>
                                <td>
                                    @if($classroom->status === 'active')
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('school-admin.classrooms.edit', $classroom->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form id="delete-classroom-{{ $classroom->id }}" action="{{ route('school-admin.classrooms.delete', $classroom->id) }}" method="POST" class="d-inline">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteClassroom({{ $classroom->id }}, '{{ $classroom->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-door-closed fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">لا توجد فصول</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $classrooms->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function confirmDeleteClassroom(classroomId, classroomName) {
    glassNotify.confirm(
        'حذف الفصل',
        `هل أنت متأكد من حذف فصل "${classroomName}"؟`,
        function() {
            document.getElementById('delete-classroom-' + classroomId).submit();
        },
        {
            confirmText: 'حذف',
            cancelText: 'إلغاء',
            type: 'error'
        }
    );
}
</script>
@endpush

