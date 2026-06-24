@extends('layouts.teacher')

@section('title', 'تعديل فريق — ' . $team->name)

@section('content')
<style>
    .edit-team-page { max-width: 780px; margin: 0 auto; padding: 24px; direction: rtl; }
    .form-card { background:white; border-radius:24px; padding:28px; box-shadow:0 8px 40px rgba(0,0,0,0.06); }
    .form-card h1 { font-size:22px; font-weight:800; color:#0f172a; margin:0 0 22px; }
    .field { margin-bottom: 16px; }
    .field label { display:block; font-weight:700; color:#1e293b; margin-bottom:8px; font-size:14px; }
    .field input, .field textarea, .field select {
        width:100%; padding:12px 14px; border-radius:10px; border:2px solid #e2e8f0;
        font-size:14px; font-family:inherit; transition:.2s;
    }
    .field input:focus, .field textarea:focus, .field select:focus { border-color:#6366f1; outline:none; }
    .members-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:10px; }
    .member-tile { background:#f8fafc; border:2px solid #e2e8f0; border-radius:10px; padding:10px 14px; display:flex; align-items:center; gap:8px; cursor:pointer; transition:.2s; }
    .member-tile:hover { border-color:#6366f1; }
    .member-tile.checked { background:#ecfdf5; border-color:#10b981; }
    .member-tile input { margin:0; }
    .actions { display:flex; gap:12px; margin-top:24px; flex-wrap:wrap; }
    .btn-primary { background:linear-gradient(135deg,#6366f1,#8b5cf6); color:white; border:none; padding:12px 28px; border-radius:10px; font-weight:700; cursor:pointer; }
    .btn-secondary { background:#f1f5f9; color:#334155; padding:12px 28px; border-radius:10px; text-decoration:none; font-weight:600; border:1px solid #cbd5e1; }
</style>

<div class="edit-team-page">
    <a href="{{ route('teacher.teams.show', $team->id) }}" class="btn-secondary">← العودة للفريق</a>

    <div class="form-card" style="margin-top:14px;">
        <h1>✏️ تعديل فريق «{{ $team->name }}»</h1>

        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 14px;border-radius:10px;margin-bottom:18px;">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.teams.update', $team->id) }}">
            @csrf

            <div class="field">
                <label>اسم الفريق *</label>
                <input type="text" name="name" value="{{ old('name', $team->name) }}" required>
            </div>

            <div class="field">
                <label>الوصف</label>
                <textarea name="description" rows="3">{{ old('description', $team->description) }}</textarea>
            </div>

            <div class="field">
                <label>الفصل</label>
                <input type="text" value="{{ $team->classroom->name ?? '—' }}" disabled style="background:#f8fafc;color:#64748b;">
            </div>

            @php
                $currentMemberIds = $team->members->pluck('id')->toArray();
                $currentLeaderId  = optional($team->members->where('pivot.role', 'leader')->first())->id;
                $students = optional($team->classroom)->students ?? collect();
            @endphp

            <div class="field">
                <label>قائد الفريق *</label>
                <select name="leader_id" required>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('leader_id', $currentLeaderId) == $student->id ? 'selected' : '' }}>
                            {{ $student->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label>أعضاء الفريق *</label>
                <div class="members-grid">
                    @foreach($students as $student)
                        @php $checked = in_array($student->id, old('member_ids', $currentMemberIds)); @endphp
                        <label class="member-tile {{ $checked ? 'checked' : '' }}" data-tile>
                            <input type="checkbox" name="member_ids[]" value="{{ $student->id }}"
                                   {{ $checked ? 'checked' : '' }}
                                   onchange="this.closest('[data-tile]').classList.toggle('checked', this.checked)">
                            <span>{{ $student->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="field">
                <label>الحالة</label>
                <select name="status">
                    <option value="active"   {{ old('status', $team->status) === 'active'   ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status', $team->status) === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>

            <div class="actions">
                <button type="submit" class="btn-primary">💾 حفظ التغييرات</button>
                <a href="{{ route('teacher.teams.show', $team->id) }}" class="btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
