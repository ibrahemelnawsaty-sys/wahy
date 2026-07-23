@extends('layouts.admin')

@section('title', 'بنك الأنشطة')

@push('styles')
<style>
    .bank-tabs { display: flex; gap: 8px; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 0; }
    .bank-tab { padding: 14px 28px; border-radius: 12px 12px 0 0; font-weight: 700; font-size: 15px; cursor: pointer; border: none; background: transparent; color: #64748b; transition: all 0.3s; border-bottom: 3px solid transparent; margin-bottom: -2px; }
    .bank-tab.active { background: white; color: #667eea; border-bottom-color: #667eea; box-shadow: 0 -4px 12px rgba(102,126,234,0.1); }
    .bank-tab:hover:not(.active) { background: #f8fafc; color: #374151; }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .stat-cards { display: grid; grid-template-columns: repeat(auto-fit,minmax(150px,1fr)); gap: 16px; margin-bottom: 24px; }
    .stat-card { background: white; border-radius: 16px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
    .stat-card .num { font-size: 28px; font-weight: 800; margin-bottom: 4px; }
    .stat-card .lbl { font-size: 12px; color: #94a3b8; font-weight: 600; }
    .item-card { background: white; border-radius: 14px; padding: 20px; border: 1px solid #e2e8f0; margin-bottom: 14px; display: flex; align-items: center; gap: 16px; transition: box-shadow 0.2s; }
    .item-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
    .item-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 8px; font-size: 12px; font-weight: 700; }
    .badge-pending  { background: #fef3c7; color: #d97706; }
    .badge-approved { background: #dcfce7; color: #15803d; }
    .badge-rejected { background: #fee2e2; color: #dc2626; }
    .badge-type     { background: #e0e7ff; color: #4338ca; }
    .action-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; }
    .btn-approve { background: #dcfce7; color: #15803d; }
    .btn-approve:hover { background: #bbf7d0; }
    .btn-reject  { background: #fee2e2; color: #dc2626; }
    .btn-reject:hover  { background: #fecaca; }
    .add-btn { background: linear-gradient(135deg,#667eea,#764ba2); color: white; padding: 12px 24px; border-radius: 12px; border: none; font-weight: 700; font-size: 15px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.3s; }
    .add-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102,126,234,0.35); }
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: flex-start; justify-content: center; padding: 40px 20px; overflow-y: auto; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: white; border-radius: 20px; padding: 36px; width: 100%; max-width: 640px; position: relative; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-weight: 700; color: #1e293b; margin-bottom: 6px; font-size: 14px; }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; outline: none; transition: border-color 0.2s;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #667eea; }
    .filter-bar { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
    .filter-bar select { padding: 8px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; outline: none; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div style="background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); border-radius: 20px; padding: 28px 32px; margin-bottom: 28px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 30px rgba(102,126,234,0.3);">
    <div>
        <h1 style="font-size: 26px; font-weight: 800; color: white; margin-bottom: 4px;">📚 بنك الأنشطة</h1>
        <p style="color: rgba(255,255,255,0.85); font-size: 14px;">إدارة الأنشطة والأسئلة — موافقة، رفض، وإضافة جديدة</p>
    </div>
    <a href="{{ route('admin.activities.create') }}" class="add-btn" style="text-decoration:none;">➕ إضافة نشاط جديد</a>
</div>

{{-- Alerts --}}
@if(session('success'))
<div style="background:#dcfce7;border:1.5px solid #86efac;border-radius:12px;padding:14px 20px;margin-bottom:20px;color:#15803d;font-weight:600;display:flex;align-items:center;gap:10px;">
    ✅ {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fee2e2;border:1.5px solid #fca5a5;border-radius:12px;padding:14px 20px;margin-bottom:20px;color:#dc2626;font-weight:600;display:flex;align-items:center;gap:10px;">
    ❌ {{ session('error') }}
</div>
@endif

{{-- Tabs --}}
<div class="bank-tabs">
    <button class="bank-tab {{ $activeTab === 'activities' ? 'active' : '' }}" onclick="switchTab('activities')">
        📚 الأنشطة ({{ $activityStats['total'] }})
    </button>
    <button class="bank-tab {{ $activeTab === 'questions' ? 'active' : '' }}" onclick="switchTab('questions')">
        ❓ الأسئلة ({{ $questionStats['total'] }})
    </button>
</div>

{{-- ═══════════════════════════════════════════ TAB: الأنشطة --}}
<div class="tab-panel {{ $activeTab === 'activities' ? 'active' : '' }}" id="panel-activities">

    {{-- Stats --}}
    <div class="stat-cards">
        <div class="stat-card">
            <div class="num" style="color:#667eea;">{{ $activityStats['total'] }}</div>
            <div class="lbl">إجمالي الأنشطة</div>
        </div>
        <div class="stat-card">
            <div class="num" style="color:#d97706;">{{ $activityStats['pending'] }}</div>
            <div class="lbl">بانتظار الموافقة</div>
        </div>
        <div class="stat-card">
            <div class="num" style="color:#15803d;">{{ $activityStats['approved'] }}</div>
            <div class="lbl">معتمدة</div>
        </div>
        <div class="stat-card">
            <div class="num" style="color:#dc2626;">{{ $activityStats['rejected'] }}</div>
            <div class="lbl">مرفوضة</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.activity-bank.index') }}" id="activityFilterForm" style="display:flex;gap:12px;flex-wrap:wrap;">
            <input type="hidden" name="tab" value="activities">
            <select name="activity_status" onchange="document.getElementById('activityFilterForm').submit()">
                <option value="">جميع الحالات</option>
                <option value="pending"  {{ request('activity_status')=='pending'  ? 'selected' : '' }}>بانتظار الموافقة</option>
                <option value="approved" {{ request('activity_status')=='approved' ? 'selected' : '' }}>معتمدة</option>
                <option value="rejected" {{ request('activity_status')=='rejected' ? 'selected' : '' }}>مرفوضة</option>
            </select>
        </form>
    </div>

    {{-- Activities List --}}
    @forelse($activities as $activity)
    <div class="item-card" id="activity-{{ $activity->id }}">
        <div class="item-icon" style="background: linear-gradient(135deg,#667eea,#764ba2);">
            {{ $activity->type === 'quiz' ? '📝' : ($activity->type === 'creative' ? '✨' : '📚') }}
        </div>
        <div style="flex:1; min-width:0;">
            <div style="font-size:16px;font-weight:700;color:#1e293b;margin-bottom:6px;">{{ $activity->title }}</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px;">
                <span class="badge badge-type">{{ match($activity->type) { 'quiz' => 'اختبار', 'exercise' => 'تمرين', 'project' => 'مشروع', 'creative' => 'إبداعي', 'homework' => 'واجب', 'practice' => 'تطبيق', default => $activity->type } }}</span>
                <span class="badge {{ 'badge-'.($activity->approval_status ?? 'pending') }}">
                    {{ $activity->approval_status === 'approved' ? '✅ معتمد' : ($activity->approval_status === 'rejected' ? '❌ مرفوض' : '⏳ بانتظار الموافقة') }}
                </span>
                <span class="badge" style="background:#f1f5f9;color:#475569;">⭐ {{ $activity->points }} نقطة</span>
                <span class="badge" style="background:#f1f5f9;color:#475569;">👤 {{ $activity->creator->name ?? 'الأدمن' }}</span>
            </div>
            @if($activity->description)
            <div style="font-size:13px;color:#64748b;">{{ html_excerpt($activity->description, 120) }}</div>
            @endif
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0;">
            @if(($activity->approval_status ?? 'pending') === 'pending')
            <button onclick="approveActivity({{ $activity->id }})" class="action-btn btn-approve">✅ موافقة</button>
            <button onclick="rejectActivity({{ $activity->id }})" class="action-btn btn-reject">❌ رفض</button>
            @endif
        </div>
    </div>
    @empty
    <div style="text-align:center;padding:60px;background:white;border-radius:16px;color:#94a3b8;">
        <div style="font-size:60px;margin-bottom:16px;">📭</div>
        <p style="font-size:16px;font-weight:600;">لا توجد أنشطة في البنك</p>
        <a href="{{ route('admin.activities.create') }}" class="add-btn" style="margin:16px auto 0;text-decoration:none;display:inline-flex;">➕ أضف أول نشاط</a>
    </div>
    @endforelse

    <div style="margin-top:24px;">{{ $activities->appends(request()->except('activities_page'))->links() }}</div>
</div>

{{-- ═══════════════════════════════════════════ TAB: الأسئلة --}}
<div class="tab-panel {{ $activeTab === 'questions' ? 'active' : '' }}" id="panel-questions">

    {{-- Stats --}}
    <div class="stat-cards">
        <div class="stat-card">
            <div class="num" style="color:#667eea;">{{ $questionStats['total'] }}</div>
            <div class="lbl">إجمالي الأسئلة</div>
        </div>
        <div class="stat-card">
            <div class="num" style="color:#d97706;">{{ $questionStats['pending'] }}</div>
            <div class="lbl">بانتظار الموافقة</div>
        </div>
        <div class="stat-card">
            <div class="num" style="color:#15803d;">{{ $questionStats['approved'] }}</div>
            <div class="lbl">معتمدة</div>
        </div>
        <div class="stat-card">
            <div class="num" style="color:#dc2626;">{{ $questionStats['rejected'] }}</div>
            <div class="lbl">مرفوضة</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.activity-bank.index') }}" id="questionFilterForm" style="display:flex;gap:12px;flex-wrap:wrap;">
            <input type="hidden" name="tab" value="questions">
            <select name="question_status" onchange="document.getElementById('questionFilterForm').submit()">
                <option value="">جميع الحالات</option>
                <option value="pending"  {{ request('question_status')=='pending'  ? 'selected' : '' }}>بانتظار الموافقة</option>
                <option value="approved" {{ request('question_status')=='approved' ? 'selected' : '' }}>معتمدة</option>
                <option value="rejected" {{ request('question_status')=='rejected' ? 'selected' : '' }}>مرفوضة</option>
            </select>
        </form>
    </div>

    {{-- Questions List --}}
    @forelse($questions as $question)
    <div class="item-card" id="question-{{ $question->id }}">
        <div class="item-icon" style="background: linear-gradient(135deg,#f093fb,#f5576c);">❓</div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:16px;font-weight:700;color:#1e293b;margin-bottom:6px;">{{ $question->title }}</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px;">
                <span class="badge badge-type">{{ $question->question_type }}</span>
                <span class="badge {{ 'badge-'.($question->status ?? 'pending') }}">
                    {{ $question->status === 'approved' ? '✅ معتمد' : ($question->status === 'rejected' ? '❌ مرفوض' : '⏳ بانتظار الموافقة') }}
                </span>
                <span class="badge" style="background:#fef3c7;color:#d97706;">{{ match($question->difficulty ?? 'medium') { 'easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', default => $question->difficulty ?? 'متوسط' } }}</span>
                <span class="badge" style="background:#f1f5f9;color:#475569;">👤 {{ $question->creator->name ?? 'الأدمن' }}</span>
            </div>
            @if($question->question_text)
            <div style="font-size:13px;color:#64748b;">{{ Str::limit($question->question_text, 120) }}</div>
            @endif
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0;">
            @if(($question->status ?? 'pending') === 'pending')
            <button onclick="approveQuestion({{ $question->id }})" class="action-btn btn-approve">✅ موافقة</button>
            <button onclick="rejectQuestion({{ $question->id }})" class="action-btn btn-reject">❌ رفض</button>
            @endif
        </div>
    </div>
    @empty
    <div style="text-align:center;padding:60px;background:white;border-radius:16px;color:#94a3b8;">
        <div style="font-size:60px;margin-bottom:16px;">📭</div>
        <p style="font-size:16px;font-weight:600;">لا توجد أسئلة في البنك</p>
    </div>
    @endforelse

    <div style="margin-top:24px;">{{ $questions->appends(request()->except('questions_page'))->links() }}</div>
</div>

{{-- ═══════════════════════════════════════════ MODAL: إضافة نشاط --}}
<div class="modal-overlay" id="addModal" onclick="if(event.target===this)closeAddModal()">
    <div class="modal-box">
        <button onclick="closeAddModal()" style="position:absolute;top:16px;left:16px;background:#f1f5f9;border:none;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:18px;color:#64748b;">✕</button>
        <h2 style="font-size:22px;font-weight:800;color:#1e293b;margin-bottom:24px;text-align:center;">➕ إضافة نشاط جديد للبنك</h2>

        <form method="POST" action="{{ route('admin.activity-bank.store') }}">
            @csrf
            <div class="form-group">
                <label>عنوان النشاط *</label>
                <input type="text" name="title" required placeholder="أدخل عنوان النشاط">
            </div>
            <div class="form-group">
                <label>وصف النشاط</label>
                <textarea name="description" rows="3" placeholder="وصف مختصر للنشاط"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>نوع النشاط *</label>
                    <select name="type" required>
                        <option value="quiz">اختبار (Quiz)</option>
                        <option value="exercise">تمرين</option>
                        <option value="project">مشروع</option>
                        <option value="creative">إبداعي</option>
                        <option value="homework">واجب منزلي</option>
                        <option value="practice">تطبيق</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>المستوى *</label>
                    <select name="difficulty" required>
                        <option value="easy">سهل</option>
                        <option value="medium" selected>متوسط</option>
                        <option value="hard">صعب</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>النقاط *</label>
                    <input type="number" name="points" value="20" min="1" max="500" required>
                </div>
                <div class="form-group">
                    <label>العملات</label>
                    <input type="number" name="coins" value="10" min="0" max="500">
                </div>
            </div>
            <div class="form-group">
                <label>الدرس (اختياري)</label>
                <select name="lesson_id">
                    <option value="">— بدون درس محدد —</option>
                    @foreach($lessons as $lesson)
                    <option value="{{ $lesson->id }}">{{ $lesson->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>الحالة *</label>
                <select name="status" required>
                    <option value="active">نشط</option>
                    <option value="draft">مسودة</option>
                    <option value="inactive">غير نشط</option>
                </select>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" style="flex:1;background:linear-gradient(135deg,#667eea,#764ba2);color:white;padding:13px;border-radius:12px;border:none;font-weight:700;font-size:15px;cursor:pointer;">
                    💾 حفظ النشاط
                </button>
                <button type="button" onclick="closeAddModal()" style="flex:1;background:#f1f5f9;color:#475569;padding:13px;border-radius:12px;border:none;font-weight:700;font-size:15px;cursor:pointer;">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════ MODAL: سبب الرفض --}}
<div class="modal-overlay" id="rejectModal" onclick="if(event.target===this)closeRejectModal()">
    <div class="modal-box" style="max-width:480px;">
        <h2 style="font-size:20px;font-weight:800;color:#1e293b;margin-bottom:20px;text-align:center;">❌ سبب الرفض</h2>
        <div class="form-group">
            <label>سبب الرفض (اختياري)</label>
            <textarea id="rejectReason" rows="4" placeholder="اكتب سبب الرفض للمعلم..." style="width:100%;padding:10px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;"></textarea>
        </div>
        <div style="display:flex;gap:12px;">
            <button onclick="confirmReject()" style="flex:1;background:#fee2e2;color:#dc2626;padding:12px;border-radius:10px;border:none;font-weight:700;cursor:pointer;">
                تأكيد الرفض
            </button>
            <button onclick="closeRejectModal()" style="flex:1;background:#f1f5f9;color:#475569;padding:12px;border-radius:10px;border:none;font-weight:700;cursor:pointer;">
                إلغاء
            </button>
        </div>
    </div>
</div>

{{-- نافذة اختيار وضع النشر عند الموافقة — كان مُثبَّتاً «مباشر لكل الطلاب» بلا خيار --}}
<div class="modal-overlay" id="approveModal" onclick="if(event.target===this)closeApproveModal()">
    <div class="modal-box" style="max-width:520px;">
        <h2 style="font-size:20px;font-weight:800;color:#1e293b;margin-bottom:8px;text-align:center;">✅ الموافقة على النشاط</h2>
        <p style="color:#64748b;font-size:13px;text-align:center;margin-bottom:20px;">اختر كيف يُنشَر لكل المدارس.</p>
        <div class="form-group" style="display:flex;flex-direction:column;gap:10px;">
            <label style="display:flex;gap:10px;align-items:flex-start;padding:14px;border:2px solid #e2e8f0;border-radius:12px;cursor:pointer;">
                <input type="radio" name="approveMode" value="bank" checked style="margin-top:4px;">
                <span><strong>🏦 للبنك فقط</strong><br><span style="color:#64748b;font-size:13px;">يُتاح للمعلّمين لاستنساخه/إسناده — لا يظهر مباشرةً للطلاب.</span></span>
            </label>
            <label style="display:flex;gap:10px;align-items:flex-start;padding:14px;border:2px solid #e2e8f0;border-radius:12px;cursor:pointer;">
                <input type="radio" name="approveMode" value="direct" style="margin-top:4px;">
                <span><strong>🎯 مباشر للطلاب</strong><br><span style="color:#64748b;font-size:13px;">يظهر فوراً لكل طلاب كل المدارس.</span></span>
            </label>
        </div>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button onclick="confirmApprove()" style="flex:1;background:#dcfce7;color:#15803d;padding:12px;border-radius:10px;border:none;font-weight:700;cursor:pointer;">
                تأكيد الموافقة
            </button>
            <button onclick="closeApproveModal()" style="flex:1;background:#f1f5f9;color:#475569;padding:12px;border-radius:10px;border:none;font-weight:700;cursor:pointer;">
                إلغاء
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ─── Tabs ─────────────────────────
function switchTab(tab) {
    document.querySelectorAll('.bank-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelector(`.bank-tab[onclick="switchTab('${tab}')"]`).classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
}

// ─── Add Modal ─────────────────────
function openAddModal()  { document.getElementById('addModal').classList.add('open'); }
function closeAddModal() { document.getElementById('addModal').classList.remove('open'); }

// ─── Reject Modal ──────────────────
let _rejectType = null, _rejectId = null;

function rejectActivity(id) { _rejectType = 'activity'; _rejectId = id; document.getElementById('rejectReason').value=''; document.getElementById('rejectModal').classList.add('open'); }
function rejectQuestion(id) { _rejectType = 'question'; _rejectId = id; document.getElementById('rejectReason').value=''; document.getElementById('rejectModal').classList.add('open'); }
function closeRejectModal() { document.getElementById('rejectModal').classList.remove('open'); }

async function confirmReject() {
    const reason = document.getElementById('rejectReason').value;
    const url = _rejectType === 'activity'
        ? `/admin/activity-bank/${_rejectId}/reject-activity`
        : `/admin/activity-bank/${_rejectId}/reject-question`;
    await postAction(url, { reason }, _rejectType === 'activity' ? `activity-${_rejectId}` : `question-${_rejectId}`, 'مرفوض', 'badge-rejected');
    closeRejectModal();
}

// ─── Approve ───────────────────────
let _approveId = null;
function approveActivity(id) {
    _approveId = id;
    document.querySelector('#approveModal input[name="approveMode"][value="bank"]').checked = true;
    document.getElementById('approveModal').classList.add('open');
}
function closeApproveModal() { document.getElementById('approveModal').classList.remove('open'); }
async function confirmApprove() {
    const mode = document.querySelector('#approveModal input[name="approveMode"]:checked').value;
    closeApproveModal();
    await postAction(`/admin/activity-bank/${_approveId}/approve-activity`, { publish_mode: mode }, `activity-${_approveId}`, '✅ معتمد', 'badge-approved');
}
async function approveQuestion(id) {
    await postAction(`/admin/activity-bank/${id}/approve-question`, {}, `question-${id}`, '✅ معتمد', 'badge-approved');
}

async function postAction(url, data, cardId, newLabel, badgeClass) {
    const resp = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(data)
    });
    const json = await resp.json();
    if (json.success) {
        const card = document.getElementById(cardId);
        if (card) {
            // تحديث الـ badge
            const badges = card.querySelectorAll('.badge-pending, .badge-approved, .badge-rejected');
            badges.forEach(b => { b.className = `badge ${badgeClass}`; b.textContent = newLabel; });
            // إخفاء أزرار الموافقة/الرفض
            card.querySelectorAll('.action-btn').forEach(b => b.style.display='none');
        }
    } else {
        alert('❌ ' + (json.message || 'حدث خطأ'));
    }
}
</script>
@endpush

@endsection
