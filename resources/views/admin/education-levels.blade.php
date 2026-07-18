@extends('layouts.admin')

@section('title', 'المراحل الدراسية')
@section('page-title', 'المراحل الدراسية')

@section('content')
<div>
    {{-- زر إضافة مرحلة جديدة --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <p style="color: var(--text-secondary, #64748b); font-size: 14px; margin: 0;">إدارة المراحل الدراسية والسنوات الدراسية وربطها بالمدارس</p>
        <button onclick="showAddLevelModal()" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 12px; font-weight: 600; font-size: 14px; cursor: pointer; font-family: inherit;">
            <i class="fas fa-plus"></i>
            إضافة مرحلة دراسية
        </button>
    </div>

    {{-- المراحل الدراسية --}}
    <div id="levelsContainer">
        @forelse($levels as $level)
        <div class="level-card" data-level-id="{{ $level->id }}" style="background: var(--card-bg, white); border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); margin-bottom: 20px; overflow: hidden;">
            {{-- رأس المرحلة --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.06);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 24px;">🎓</span>
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-primary, #1e293b);">{{ $level->name }}</h3>
                        <span style="font-size: 12px; color: var(--text-secondary, #94a3b8);">{{ $level->academic_years_count ?? $level->academicYears->count() }} سنة دراسية • {{ $level->schools_count }} مدرسة</span>
                    </div>
                    <span style="padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; {{ $level->status ? 'background: rgba(16,185,129,0.1); color: #10b981;' : 'background: rgba(239,68,68,0.1); color: #ef4444;' }}">
                        {{ $level->status ? 'نشط' : 'معطّل' }}
                    </span>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button onclick="showAddYearModal({{ $level->id }}, '{{ $level->name }}')" style="padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(16,185,129,0.3); background: rgba(16,185,129,0.1); color: #10b981; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit;">
                        <i class="fas fa-plus"></i> إضافة سنة
                    </button>
                    <button onclick="showLinkSchoolModal({{ $level->id }}, '{{ $level->name }}')" style="padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(59,130,246,0.3); background: rgba(59,130,246,0.1); color: #3b82f6; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit;">
                        <i class="fas fa-link"></i> ربط بمدرسة
                    </button>
                    <button onclick="editLevel({{ $level->id }}, '{{ $level->name }}')" style="padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(245,158,11,0.3); background: rgba(245,158,11,0.1); color: #f59e0b; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit;">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteLevel({{ $level->id }}, '{{ $level->name }}')" style="padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(239,68,68,0.3); background: rgba(239,68,68,0.1); color: #ef4444; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            {{-- السنوات الدراسية --}}
            @if($level->academicYears->count() > 0)
            <div style="padding: 16px 24px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px;">
                    @foreach($level->academicYears as $year)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: rgba(102,126,234,0.05); border-radius: 10px; border: 1px solid rgba(102,126,234,0.1);">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 16px;">📖</span>
                            <span style="font-weight: 600; font-size: 14px; color: var(--text-primary, #334155);">{{ $year->name }}</span>
                        </div>
                        <div style="display: flex; gap: 4px;">
                            <button onclick="editYear({{ $year->id }}, '{{ $year->name }}')" style="padding: 4px 8px; border: none; background: transparent; color: #f59e0b; cursor: pointer; font-size: 12px;" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteYear({{ $year->id }}, '{{ $year->name }}')" style="padding: 4px 8px; border: none; background: transparent; color: #ef4444; cursor: pointer; font-size: 12px;" title="حذف">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div style="padding: 30px 24px; text-align: center; color: var(--text-secondary, #94a3b8); font-size: 14px;">
                لا توجد سنوات دراسية مضافة بعد
            </div>
            @endif
        </div>
        @empty
        <div style="background: var(--card-bg, white); border-radius: 16px; padding: 60px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
            <div style="font-size: 48px; margin-bottom: 16px;">🎓</div>
            <div style="font-size: 16px; font-weight: 600; color: var(--text-secondary, #64748b); margin-bottom: 8px;">لا توجد مراحل دراسية</div>
            <p style="font-size: 14px; color: var(--text-secondary, #94a3b8);">أضف المراحل الدراسية مثل: ابتدائي، متوسط، ثانوي</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Modal إضافة/تعديل --}}
<div id="levelModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(8px); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: var(--card-bg, white); border-radius: 20px; padding: 32px; max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
        <h3 id="modalTitle" style="margin: 0 0 20px; font-size: 18px; font-weight: 700; color: var(--text-primary, #1e293b);"></h3>
        <input type="text" id="modalInput" placeholder="اسم المرحلة أو السنة الدراسية" style="width: 100%; padding: 12px 16px; border: 2px solid rgba(0,0,0,0.1); border-radius: 12px; font-size: 15px; font-family: inherit; outline: none; box-sizing: border-box; background: var(--card-bg, white); color: var(--text-primary, #1e293b);" autofocus>
        <div style="display: flex; gap: 12px; margin-top: 20px; justify-content: flex-end;">
            <button onclick="closeModal()" style="padding: 10px 24px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: transparent; color: var(--text-secondary, #64748b); font-weight: 600; cursor: pointer; font-family: inherit;">إلغاء</button>
            <button id="modalSubmitBtn" onclick="submitModal()" style="padding: 10px 24px; border-radius: 10px; border: none; background: linear-gradient(135deg, #667eea, #764ba2); color: white; font-weight: 600; cursor: pointer; font-family: inherit;">حفظ</button>
        </div>
    </div>
</div>

{{-- Modal ربط مدرسة --}}
<div id="linkSchoolModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(8px); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: var(--card-bg, white); border-radius: 20px; padding: 32px; max-width: 500px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-height: 80vh; overflow-y: auto;">
        <h3 id="linkSchoolTitle" style="margin: 0 0 20px; font-size: 18px; font-weight: 700; color: var(--text-primary, #1e293b);"></h3>
        <div id="schoolCheckboxes" style="display: grid; gap: 8px; max-height: 400px; overflow-y: auto; padding: 4px;">
            @foreach($schools as $school)
            <label class="school-option" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; cursor: pointer; border: 1px solid rgba(0,0,0,0.08); transition: background 0.2s;" onmouseover="this.style.background='rgba(102,126,234,0.05)'" onmouseout="this.style.background='transparent'">
                <input type="checkbox" class="school-checkbox" value="{{ $school->id }}" style="width: 18px; height: 18px; accent-color: #667eea;">
                <span style="font-weight: 600; font-size: 14px; color: var(--text-primary, #334155);">{{ $school->name }}</span>
                <span class="linked-badge" style="display: none; margin-inline-start: auto; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: rgba(16,185,129,0.12); color: #10b981;">مرتبطة</span>
            </label>
            @endforeach
        </div>
        <div id="linkSchoolEmpty" style="display: none; text-align: center; padding: 20px; color: var(--text-secondary, #94a3b8); font-size: 14px;">كل المدارس مرتبطة بهذه المرحلة بالفعل</div>
        <div style="display: flex; gap: 12px; margin-top: 20px; justify-content: flex-end;">
            <button onclick="closeLinkSchoolModal()" style="padding: 10px 24px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: transparent; color: var(--text-secondary, #64748b); font-weight: 600; cursor: pointer; font-family: inherit;">إلغاء</button>
            <button onclick="submitLinkSchool()" style="padding: 10px 24px; border-radius: 10px; border: none; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; font-weight: 600; cursor: pointer; font-family: inherit;">حفظ الربط</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let modalAction = null; // { type: 'add_level' | 'edit_level' | 'add_year' | 'edit_year', id: null, levelId: null }
    let linkSchoolLevelId = null;

    // خريطة: معرّف المرحلة → معرّفات المدارس المرتبطة بها بالفعل (لإخفاء المكرَّر عند الربط)
    window.levelSchoolMap = @json($levels->mapWithKeys(fn ($l) => [$l->id => $l->schools->pluck('id')->all()]));

    // ============ المراحل ============

    function showAddLevelModal() {
        modalAction = { type: 'add_level' };
        document.getElementById('modalTitle').textContent = 'إضافة مرحلة دراسية جديدة';
        document.getElementById('modalInput').value = '';
        document.getElementById('modalInput').placeholder = 'مثال: ثانوي، متوسط، ابتدائي';
        showModal();
    }

    function editLevel(id, name) {
        modalAction = { type: 'edit_level', id };
        document.getElementById('modalTitle').textContent = 'تعديل المرحلة الدراسية';
        document.getElementById('modalInput').value = name;
        showModal();
    }

    async function deleteLevel(id, name) {
        if (!confirm(`هل تريد حذف المرحلة "${name}" وجميع السنوات المرتبطة بها؟`)) return;

        try {
            const res = await fetch(`{{ url('/admin/education-levels') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.success) { location.reload(); }
        } catch (e) { console.error(e); }
    }

    // ============ السنوات ============

    function showAddYearModal(levelId, levelName) {
        modalAction = { type: 'add_year', levelId };
        document.getElementById('modalTitle').textContent = `إضافة سنة دراسية - ${levelName}`;
        document.getElementById('modalInput').value = '';
        document.getElementById('modalInput').placeholder = 'مثال: الأول ثانوي، الثاني ثانوي';
        showModal();
    }

    function editYear(id, name) {
        modalAction = { type: 'edit_year', id };
        document.getElementById('modalTitle').textContent = 'تعديل السنة الدراسية';
        document.getElementById('modalInput').value = name;
        showModal();
    }

    async function deleteYear(id, name) {
        if (!confirm(`هل تريد حذف السنة "${name}"؟`)) return;

        try {
            const res = await fetch(`{{ url('/admin/academic-years') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.success) { location.reload(); }
        } catch (e) { console.error(e); }
    }

    // ============ ربط المدرسة ============

    function showLinkSchoolModal(levelId, levelName) {
        linkSchoolLevelId = levelId;
        document.getElementById('linkSchoolTitle').textContent = `ربط المدارس بمرحلة: ${levelName}`;
        // إخفاء/تعطيل المدارس المرتبطة بهذه المرحلة مسبقاً حتى لا تظهر مرة أخرى
        const linkedIds = (window.levelSchoolMap[levelId] || []).map(String);
        let availableCount = 0;
        document.querySelectorAll('#schoolCheckboxes .school-option').forEach(label => {
            const cb = label.querySelector('.school-checkbox');
            const badge = label.querySelector('.linked-badge');
            const isLinked = linkedIds.includes(String(cb.value));
            cb.checked = false;
            cb.disabled = isLinked;                 // المُعطَّل يُستبعَد تلقائياً من :checked
            label.style.display = isLinked ? 'none' : 'flex';
            if (badge) badge.style.display = 'none';
            if (!isLinked) availableCount++;
        });
        document.getElementById('linkSchoolEmpty').style.display = availableCount === 0 ? 'block' : 'none';
        document.getElementById('linkSchoolModal').style.display = 'flex';
    }

    function closeLinkSchoolModal() {
        document.getElementById('linkSchoolModal').style.display = 'none';
    }

    async function submitLinkSchool() {
        const selectedIds = [...document.querySelectorAll('#schoolCheckboxes .school-checkbox:checked')].map(cb => cb.value);
        if (selectedIds.length === 0) { alert('اختر مدرسة واحدة على الأقل'); return; }

        try {
            const res = await fetch('{{ route("admin.education-levels.link-school") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ school_ids: selectedIds, education_level_id: linkSchoolLevelId }),
            });
            const data = await res.json();
            if (data.success) {
                closeLinkSchoolModal();
                location.reload();
            } else if (data.errors) {
                alert(Object.values(data.errors).flat().join('\n'));
            }
        } catch (e) { console.error(e); }
    }

    // ============ Modal عام ============

    function showModal() {
        document.getElementById('levelModal').style.display = 'flex';
        setTimeout(() => document.getElementById('modalInput').focus(), 100);
    }

    function closeModal() {
        document.getElementById('levelModal').style.display = 'none';
    }

    async function submitModal() {
        const name = document.getElementById('modalInput').value.trim();
        if (!name) { alert('الرجاء إدخال الاسم'); return; }

        let url, method, body;

        switch (modalAction.type) {
            case 'add_level':
                url = '{{ route("admin.education-levels.store") }}';
                method = 'POST';
                body = { name };
                break;
            case 'edit_level':
                url = `{{ url('/admin/education-levels') }}/${modalAction.id}`;
                method = 'PUT';
                body = { name };
                break;
            case 'add_year':
                url = '{{ route("admin.academic-years.store") }}';
                method = 'POST';
                body = { name, education_level_id: modalAction.levelId };
                break;
            case 'edit_year':
                url = `{{ url('/admin/academic-years') }}/${modalAction.id}`;
                method = 'PUT';
                body = { name };
                break;
        }

        try {
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await res.json();
            if (data.success) {
                closeModal();
                location.reload();
            } else if (data.errors) {
                alert(Object.values(data.errors).flat().join('\n'));
            }
        } catch (e) { console.error(e); }
    }

    // Enter key
    document.getElementById('modalInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') submitModal();
    });
</script>
@endpush
@endsection
