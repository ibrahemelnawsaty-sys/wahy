@extends('layouts.teacher')

@section('title', 'إدارة الفرق')

@section('content')
<style>
    .teams-page { max-width: 1200px; margin: 0 auto; padding: 24px; direction: rtl; }

    /* Header */
    .teams-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
        border-radius: 24px;
        padding: 40px;
        color: white;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(99, 102, 241, 0.3);
    }
    .teams-header::before {
        content: '';
        position: absolute;
        top: -60%;
        left: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
        border-radius: 50%;
    }
    .teams-header::after {
        content: '';
        position: absolute;
        bottom: -40%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
        border-radius: 50%;
    }
    .header-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
    .header-text h1 { font-size: 28px; font-weight: 800; margin: 0 0 8px; }
    .header-text p { opacity: 0.85; font-size: 15px; margin: 0; }
    .btn-create {
        display: inline-flex; align-items: center; gap: 10px;
        background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);
        padding: 14px 28px; border-radius: 16px; color: white;
        text-decoration: none; font-size: 15px; font-weight: 700;
        border: 1px solid rgba(255,255,255,0.3);
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .btn-create:hover {
        background: rgba(255,255,255,0.35);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }
    .stat-mini {
        background: white;
        border-radius: 18px;
        padding: 24px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.04);
        transition: all 0.3s;
    }
    .stat-mini:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
    .stat-mini .icon { font-size: 32px; margin-bottom: 8px; display: block; }
    .stat-mini .value { font-size: 28px; font-weight: 800; color: #1e293b; }
    .stat-mini .label { font-size: 12px; color: #94a3b8; font-weight: 500; margin-top: 4px; }

    /* Success Alert */
    .success-alert {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        border: 1px solid #6ee7b7;
        border-radius: 16px;
        padding: 16px 24px;
        color: #065f46;
        font-weight: 600;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.4s ease;
    }
    @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Teams Grid */
    .teams-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 24px;
    }

    /* Team Card */
    .team-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .team-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 50px rgba(99, 102, 241, 0.15);
    }
    .team-card-accent {
        height: 6px;
        background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899);
    }
    .team-card-body { padding: 28px; }
    .team-card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    .team-avatar {
        width: 56px; height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, #ede9fe, #e0e7ff);
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
    }
    .team-badge {
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .badge-active { background: #d1fae5; color: #059669; }
    .badge-archived { background: #f1f5f9; color: #64748b; }

    .team-name { font-size: 20px; font-weight: 800; color: #1e293b; margin: 16px 0 6px; }
    .team-classroom {
        font-size: 13px; color: #8b5cf6; font-weight: 600;
        display: inline-flex; align-items: center; gap: 6px;
        background: #f5f3ff; padding: 4px 12px; border-radius: 8px;
    }

    /* Info chips */
    .team-chips { display: flex; gap: 12px; margin: 20px 0; flex-wrap: wrap; }
    .chip {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 16px;
        background: #f8fafc;
        border-radius: 12px;
        font-size: 13px;
        color: #475569;
        font-weight: 600;
        border: 1px solid #f1f5f9;
        flex: 1;
        min-width: 120px;
    }
    .chip .chip-icon {
        width: 32px; height: 32px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    .chip-leader .chip-icon { background: #fef3c7; }
    .chip-members .chip-icon { background: #dbeafe; }

    /* Description */
    .team-desc {
        font-size: 13px; color: #64748b; line-height: 1.7;
        padding: 14px 16px;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 12px;
        margin-bottom: 16px;
        border-right: 3px solid #8b5cf6;
    }

    /* Actions */
    .team-actions {
        display: flex; gap: 8px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
    }
    .btn-action {
        flex: 1;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        cursor: pointer;
        border: none;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .btn-view { background: #ede9fe; color: #7c3aed; }
    .btn-view:hover { background: #ddd6fe; transform: translateY(-1px); }
    .btn-delete-team { background: #fff1f2; color: #e11d48; }
    .btn-delete-team:hover { background: #ffe4e6; transform: translateY(-1px); }

    /* Empty State */
    .empty-state {
        grid-column: 1 / -1;
        background: white;
        border-radius: 24px;
        padding: 80px 40px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 2px dashed #e2e8f0;
    }
    .empty-state .empty-icon {
        width: 100px; height: 100px;
        margin: 0 auto 24px;
        background: linear-gradient(135deg, #ede9fe, #e0e7ff);
        border-radius: 28px;
        display: flex; align-items: center; justify-content: center;
        font-size: 48px;
        box-shadow: 0 8px 30px rgba(99, 102, 241, 0.15);
    }
    .empty-state h3 { font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
    .empty-state p { color: #64748b; font-size: 15px; margin-bottom: 28px; }
    .btn-empty-create {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 14px 32px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        border-radius: 14px;
        text-decoration: none;
        font-weight: 700;
        font-size: 15px;
        transition: all 0.3s;
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
    }
    .btn-empty-create:hover { transform: translateY(-3px); box-shadow: 0 12px 35px rgba(99, 102, 241, 0.4); }

    /* Delete Confirm Modal */
    .modal-overlay {
        display: none;
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center; justify-content: center;
    }
    .modal-overlay.show { display: flex; }
    .modal-box {
        background: white;
        border-radius: 24px;
        padding: 40px;
        max-width: 420px;
        width: 90%;
        text-align: center;
        box-shadow: 0 25px 60px rgba(0,0,0,0.2);
        animation: modalIn 0.3s ease;
    }
    @keyframes modalIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    .modal-icon { font-size: 56px; margin-bottom: 16px; display: block; }
    .modal-title { font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
    .modal-text { color: #64748b; font-size: 14px; margin-bottom: 28px; }
    .modal-btns { display: flex; gap: 12px; }
    .modal-btn {
        flex: 1; padding: 12px; border-radius: 14px; font-weight: 700;
        font-size: 14px; border: none; cursor: pointer; transition: all 0.3s;
    }
    .modal-btn-cancel { background: #f1f5f9; color: #475569; }
    .modal-btn-cancel:hover { background: #e2e8f0; }
    .modal-btn-delete { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; box-shadow: 0 4px 15px rgba(239,68,68,0.3); }
    .modal-btn-delete:hover { box-shadow: 0 8px 25px rgba(239,68,68,0.4); transform: translateY(-1px); }

    /* Pagination */
    .pagination-wrap { margin-top: 32px; display: flex; justify-content: center; }

    @media (max-width: 768px) {
        .teams-header { padding: 28px; }
        .header-content { flex-direction: column; text-align: center; }
        .teams-grid { grid-template-columns: 1fr; }
        .stats-row { grid-template-columns: 1fr 1fr; }
    }
</style>

<div class="teams-page">
    <!-- Header -->
    <div class="teams-header">
        <div class="header-content">
            <div class="header-text">
                <h1>👥 إدارة الفرق</h1>
                <p>إنشاء وإدارة فرق الطلاب للأنشطة الجماعية والتعاونية</p>
            </div>
            <a href="{{ route('teacher.teams.create') }}" class="btn-create">
                <span style="font-size: 20px;">✨</span>
                إنشاء فريق جديد
            </a>
        </div>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
        <div class="success-alert">
            <span style="font-size: 20px;">✅</span>
            {{ session('success') }}
        </div>
    @endif

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-mini">
            <span class="icon">🏆</span>
            <div class="value">{{ $teams->total() }}</div>
            <div class="label">إجمالي الفرق</div>
        </div>
        <div class="stat-mini">
            <span class="icon">👥</span>
            <div class="value">{{ $teams->sum('members_count') }}</div>
            <div class="label">إجمالي الأعضاء</div>
        </div>
        <div class="stat-mini">
            <span class="icon">✅</span>
            <div class="value">{{ $teams->where('status', 'active')->count() }}</div>
            <div class="label">فرق نشطة</div>
        </div>
    </div>

    <!-- Teams Grid -->
    <div class="teams-grid">
        @forelse($teams as $team)
            <div class="team-card">
                <div class="team-card-accent"></div>
                <div class="team-card-body">
                    <div class="team-card-top">
                        <div class="team-avatar">👥</div>
                        <span class="team-badge {{ $team->status == 'active' ? 'badge-active' : 'badge-archived' }}">
                            {{ $team->status == 'active' ? '● نشط' : '○ مؤرشف' }}
                        </span>
                    </div>

                    <div class="team-name">{{ $team->name }}</div>
                    <div class="team-classroom">
                        📚 {{ $team->classroom->name ?? 'بدون فصل' }}
                    </div>

                    <div class="team-chips">
                        <div class="chip chip-leader">
                            <div class="chip-icon">👑</div>
                            <div>
                                <div style="font-size: 11px; color: #94a3b8; font-weight: 500;">القائد</div>
                                <div>{{ $team->leader->first()?->name ?? 'غير محدد' }}</div>
                            </div>
                        </div>
                        <div class="chip chip-members">
                            <div class="chip-icon">👤</div>
                            <div>
                                <div style="font-size: 11px; color: #94a3b8; font-weight: 500;">الأعضاء</div>
                                <div>{{ $team->members_count }} طالب</div>
                            </div>
                        </div>
                    </div>

                    @if($team->description)
                        <div class="team-desc">{{ \Illuminate\Support\Str::limit($team->description, 100) }}</div>
                    @endif

                    <div class="team-actions">
                        <a href="{{ route('teacher.teams.show', $team->id) }}" class="btn-action btn-view">
                            👁️ عرض
                        </a>
                        <button class="btn-action btn-delete-team" onclick="confirmDelete({{ $team->id }}, '{{ $team->name }}')">
                            🗑️ حذف
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <h3>لا توجد فرق بعد</h3>
                <p>ابدأ بإنشاء أول فريق لطلابك للعمل الجماعي والتعاوني</p>
                <a href="{{ route('teacher.teams.create') }}" class="btn-empty-create">
                    ✨ إنشاء أول فريق
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($teams->hasPages())
        <div class="pagination-wrap">
            {{ $teams->links() }}
        </div>
    @endif
</div>

<!-- Delete Confirm Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <span class="modal-icon">⚠️</span>
        <div class="modal-title">حذف الفريق</div>
        <div class="modal-text">
            هل أنت متأكد من حذف فريق <strong id="deleteTeamName"></strong>؟
            <br>لا يمكن التراجع عن هذا الإجراء.
        </div>
        <div class="modal-btns">
            <button class="modal-btn modal-btn-cancel" onclick="closeModal()">إلغاء</button>
            <button class="modal-btn modal-btn-delete" id="confirmDeleteBtn" onclick="performDelete()">🗑️ حذف</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let deleteTeamId = null;

function confirmDelete(teamId, teamName) {
    deleteTeamId = teamId;
    document.getElementById('deleteTeamName').textContent = teamName;
    document.getElementById('deleteModal').classList.add('show');
}

function closeModal() {
    document.getElementById('deleteModal').classList.remove('show');
    deleteTeamId = null;
}

function performDelete() {
    if (!deleteTeamId) return;
    const btn = document.getElementById('confirmDeleteBtn');
    btn.textContent = 'جاري الحذف...';
    btn.disabled = true;

    fetch(`/teacher/teams/${deleteTeamId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'حدث خطأ');
            btn.textContent = '🗑️ حذف';
            btn.disabled = false;
            closeModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء الحذف');
        btn.textContent = '🗑️ حذف';
        btn.disabled = false;
        closeModal();
    });
}

// Close modal on overlay click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
@endsection
