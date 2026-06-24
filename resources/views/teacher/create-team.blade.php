@extends('layouts.teacher')

@section('title', 'إنشاء فريق جديد')

@section('content')
<style>
    .create-team-page { max-width: 780px; margin: 0 auto; padding: 24px; direction: rtl; }

    /* Back Link */
    .back-link {
        display: inline-flex; align-items: center; gap: 8px;
        background: white; padding: 10px 20px; border-radius: 14px;
        color: #6366f1; text-decoration: none; font-size: 14px; font-weight: 600;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        transition: all 0.3s;
        margin-bottom: 24px;
        border: 1px solid #ede9fe;
    }
    .back-link:hover { background: #f5f3ff; transform: translateX(4px); box-shadow: 0 4px 15px rgba(0,0,0,0.08); }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 8px 40px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.04);
    }
    .form-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
        padding: 40px;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .form-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }
    .form-header-icon {
        width: 80px; height: 80px;
        margin: 0 auto 16px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        border-radius: 22px;
        display: flex; align-items: center; justify-content: center;
        font-size: 40px;
        border: 1px solid rgba(255,255,255,0.3);
        position: relative;
        z-index: 1;
    }
    .form-header h1 { font-size: 26px; font-weight: 800; margin: 0 0 6px; position: relative; z-index: 1; }
    .form-header p { opacity: 0.85; font-size: 14px; margin: 0; position: relative; z-index: 1; }

    .form-body { padding: 40px; }

    /* Form Groups */
    .fg { margin-bottom: 28px; }
    .fg-label {
        display: block; font-weight: 700; color: #1e293b; margin-bottom: 10px; font-size: 14px;
    }
    .fg-label .required { color: #ef4444; margin-right: 4px; }
    .fg-label .optional { color: #94a3b8; font-weight: 400; font-size: 12px; margin-right: 6px; }

    .fg-input {
        width: 100%; padding: 14px 18px;
        border: 2px solid #e2e8f0; border-radius: 14px;
        font-size: 15px; font-family: inherit; color: #1e293b;
        background: #fafbfc;
        transition: all 0.3s;
        box-sizing: border-box;
    }
    .fg-input:focus {
        outline: none; border-color: #8b5cf6;
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        background: white;
    }
    .fg-input::placeholder { color: #94a3b8; }
    .fg-error { color: #ef4444; font-size: 12px; margin-top: 6px; display: flex; align-items: center; gap: 4px; }

    /* Select styled */
    .fg-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 16px center;
        padding-left: 44px;
    }

    /* Members Section */
    .members-box {
        background: #fafbfc;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s;
    }
    .members-box:focus-within { border-color: #8b5cf6; box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }
    .members-search {
        padding: 14px 18px;
        border-bottom: 1px solid #e2e8f0;
    }
    .members-search input {
        width: 100%; padding: 10px 14px;
        border: none; border-radius: 10px;
        background: white; font-size: 14px; font-family: inherit;
        box-sizing: border-box;
    }
    .members-search input:focus { outline: none; }
    .members-list { max-height: 240px; overflow-y: auto; padding: 8px; }
    .member-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 14px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .member-item:hover { background: #ede9fe; }
    .member-item.selected { background: #f5f3ff; }
    .member-item input[type="checkbox"] {
        width: 20px; height: 20px;
        accent-color: #8b5cf6;
        cursor: pointer;
    }
    .member-avatar {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; font-weight: 700; color: #6366f1;
    }
    .member-name { font-size: 14px; font-weight: 600; color: #334155; }
    .members-count {
        padding: 10px 18px;
        background: #f1f5f9;
        border-top: 1px solid #e2e8f0;
        font-size: 12px; color: #64748b;
        display: flex; align-items: center; gap: 6px;
    }
    .count-badge {
        background: #8b5cf6; color: white;
        padding: 2px 8px; border-radius: 8px;
        font-weight: 700; font-size: 11px;
    }

    /* Hint */
    .fg-hint { font-size: 12px; color: #94a3b8; margin-top: 8px; display: flex; align-items: center; gap: 6px; }

    /* Buttons */
    .form-actions {
        display: flex; gap: 12px;
        padding-top: 8px;
    }
    .btn-submit {
        flex: 1; padding: 16px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white; border: none; border-radius: 16px;
        font-size: 16px; font-weight: 700; font-family: inherit;
        cursor: pointer; transition: all 0.3s;
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 35px rgba(99, 102, 241, 0.4); }
    .btn-cancel {
        padding: 16px 28px;
        background: #f1f5f9; color: #475569; border: none; border-radius: 16px;
        font-size: 15px; font-weight: 600; font-family: inherit;
        cursor: pointer; transition: all 0.3s;
        text-decoration: none;
        display: flex; align-items: center; justify-content: center;
    }
    .btn-cancel:hover { background: #e2e8f0; }

    @media (max-width: 768px) {
        .form-body { padding: 24px; }
        .form-header { padding: 28px; }
    }
</style>

<div class="create-team-page">
    <!-- Back -->
    <a href="{{ route('teacher.teams') }}" class="back-link">
        <span>→</span> العودة للفرق
    </a>

    <!-- Form Card -->
    <div class="form-card">
        <div class="form-header">
            <div class="form-header-icon">👥</div>
            <h1>إنشاء فريق جديد</h1>
            <p>أنشئ فريقاً للطلاب للعمل على الأنشطة الجماعية والتعاونية</p>
        </div>

        <div class="form-body">
            <form action="{{ route('teacher.teams.store') }}" method="POST" id="createTeamForm">
                @csrf

                <!-- Team Name -->
                <div class="fg">
                    <label class="fg-label">
                        اسم الفريق <span class="required">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="fg-input" placeholder="مثال: فريق النجوم ⭐"
                           required maxlength="255">
                    @error('name')
                        <div class="fg-error">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <!-- Classroom -->
                <div class="fg">
                    <label class="fg-label">
                        الفصل الدراسي <span class="required">*</span>
                    </label>
                    <select name="classroom_id" class="fg-input fg-select" required id="classroomSelect">
                        <option value="">اختر الفصل الدراسي...</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                📚 {{ $classroom->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('classroom_id')
                        <div class="fg-error">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <!-- Leader -->
                <div class="fg">
                    <label class="fg-label">
                        قائد الفريق <span class="required">*</span>
                    </label>
                    <select name="leader_id" class="fg-input fg-select" required>
                        <option value="">اختر قائد الفريق...</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('leader_id') == $student->id ? 'selected' : '' }}>
                                👑 {{ $student->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('leader_id')
                        <div class="fg-error">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <!-- Members -->
                <div class="fg">
                    <label class="fg-label">
                        أعضاء الفريق <span class="required">*</span>
                    </label>
                    <div class="members-box">
                        <div class="members-search">
                            <input type="text" placeholder="🔍 ابحث عن طالب..." id="memberSearch" oninput="filterMembers()">
                        </div>
                        <div class="members-list" id="membersList">
                            @foreach($students as $student)
                                <label class="member-item" data-name="{{ $student->name }}">
                                    <input type="checkbox" name="member_ids[]" value="{{ $student->id }}"
                                           {{ in_array($student->id, old('member_ids', [])) ? 'checked' : '' }}
                                           onchange="updateCount()">
                                    <div class="member-avatar">{{ mb_substr($student->name, 0, 1) }}</div>
                                    <span class="member-name">{{ $student->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="members-count">
                            💡 تم اختيار <span class="count-badge" id="selectedCount">0</span> طالب — القائد سيُضاف تلقائياً
                        </div>
                    </div>
                    @error('member_ids')
                        <div class="fg-error">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <!-- Description -->
                <div class="fg">
                    <label class="fg-label">
                        وصف الفريق <span class="optional">(اختياري)</span>
                    </label>
                    <textarea name="description" rows="3" class="fg-input"
                              placeholder="مثال: فريق متخصص في مشاريع القيم ويعمل على الأنشطة الجماعية"
                              style="resize: vertical;">{{ old('description') }}</textarea>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        ✨ إنشاء الفريق
                    </button>
                    <a href="{{ route('teacher.teams') }}" class="btn-cancel">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterMembers() {
    const search = document.getElementById('memberSearch').value.toLowerCase();
    const items = document.querySelectorAll('.member-item');
    items.forEach(item => {
        const name = item.getAttribute('data-name').toLowerCase();
        item.style.display = name.includes(search) ? 'flex' : 'none';
    });
}

function updateCount() {
    const checked = document.querySelectorAll('.member-item input[type="checkbox"]:checked').length;
    document.getElementById('selectedCount').textContent = checked;
}

// Initialize count on page load
document.addEventListener('DOMContentLoaded', updateCount);
</script>
@endpush
@endsection
