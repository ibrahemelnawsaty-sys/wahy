{{--
    صفحة تفاصيل النشاط الموحّدة (المرحلة 4) — تُضمَّن في أغلفة رفيعة لكل دور
    (teacher/school-admin) بطبقتها الصحيحة تفاديًا لتسريب التنقّل عبر الأدوار.
    الأزرار تُحسَب من دور المستخدم: المعلّم قراءة/تعديل، مدير المدرسة اعتماد/رفض المرحلة 1.
    CSS مبدوء بـ`ad-` كي لا يصطدم بأصناف الطبقة المضيفة (.btn/.breadcrumb...).
    يتطلّب: $activity.
--}}
@php
    $u = auth()->user();
    $role = $u->role ?? null;
    $isOwnerTeacher = $role === 'teacher' && $u->id === $activity->created_by;
    $isSchoolAdmin = $role === 'school_admin';
    $schoolStatus = $activity->school_approval_status ?? 'pending';
    $adminStatus = $activity->approval_status ?? 'pending';

    $statusMeta = [
        'pending' => ['#f59e0b', '#fffbeb', '⏳ بانتظار'],
        'approved' => ['#16a34a', '#f0fdf4', '✅ معتمَد'],
        'rejected' => ['#dc2626', '#fef2f2', '❌ مرفوض'],
    ];
    $sm = $statusMeta[$schoolStatus] ?? $statusMeta['pending'];
    $am = $statusMeta[$adminStatus] ?? $statusMeta['pending'];

    // حالة النشر
    if (($activity->all_schools_mode ?? 'none') === 'direct') {
        $publishText = '🌍 منشور مباشرةً لكل المدارس';
    } elseif (($activity->all_schools_mode ?? 'none') === 'bank') {
        $publishText = '🏦 متاح في بنك كل المدارس';
    } else {
        $schoolsCount = $activity->relationLoaded('schools') ? $activity->schools->count() : $activity->schools()->count();
        $publishText = $schoolsCount > 0 ? "🏫 منشور لـ{$schoolsCount} مدرسة" : '— غير منشور بعد';
    }
@endphp

<style>
.ad-header { background: white; border-radius: 12px; padding: 32px; margin-bottom: 24px; }
.ad-breadcrumb { display: flex; gap: 8px; align-items: center; margin-bottom: 16px; font-size: 14px; flex-wrap: wrap; }
.ad-crumb { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #f1f5f9; border-radius: 6px; color: #475569; font-weight: 600; }
.ad-sep { color: #cbd5e1; }
.ad-title-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; }
.ad-badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; }
.ad-type-quiz { background: #e0e7ff; color: #4338ca; }
.ad-type-exercise { background: #dbeafe; color: #1e40af; }
.ad-type-project { background: #fce7f3; color: #9f1239; }
.ad-title { font-size: 28px; font-weight: 700; color: #1e293b; margin: 4px 0; }
.ad-desc { color: #64748b; font-size: 16px; line-height: 1.8; margin-bottom: 24px; }
.ad-meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; padding: 24px 0; border-top: 2px solid #f1f5f9; border-bottom: 2px solid #f1f5f9; margin-bottom: 24px; }
.ad-meta-item { display: flex; flex-direction: column; gap: 4px; }
.ad-meta-label { font-size: 13px; color: #94a3b8; font-weight: 600; }
.ad-meta-value { font-size: 18px; color: #1e293b; font-weight: 600; }
.ad-actions { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
.ad-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 22px; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; border: none; font-size: 15px; }
.ad-btn-primary { background: #667eea; color: white; }
.ad-btn-approve { background: #10b981; color: white; }
.ad-btn-reject { background: #ef4444; color: white; }
.ad-btn-secondary { background: #e2e8f0; color: #475569; }
.ad-select { padding: 11px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-weight: 600; color: #1e293b; background: white; }
.ad-note { color: #94a3b8; font-size: 13px; margin-top: 8px; }
</style>

<div class="ad-header">
    <div class="ad-breadcrumb">
        @if($activity->lesson)
            <span class="ad-crumb">{{ $activity->lesson->concept?->value?->icon }} {{ $activity->lesson->concept?->value?->name }}</span>
            <span class="ad-sep">←</span>
            <span class="ad-crumb">💡 {{ $activity->lesson->concept?->name }}</span>
            <span class="ad-sep">←</span>
            <span class="ad-crumb">📚 {{ $activity->lesson->title }}</span>
        @else
            <span class="ad-crumb">📝 نشاط مستقل (غير مرتبط بدرس)</span>
        @endif
    </div>

    <div class="ad-title-row">
        @if($activity->type == 'quiz')
            <span class="ad-badge ad-type-quiz">📋 اختبار</span>
        @elseif($activity->type == 'exercise')
            <span class="ad-badge ad-type-exercise">✍️ تمرين</span>
        @elseif($activity->type == 'project')
            <span class="ad-badge ad-type-project">🎨 مشروع</span>
        @else
            <span class="ad-badge ad-type-exercise">{{ $activity->type }}</span>
        @endif
        @if($activity->is_activity_bank)
            <span class="ad-badge" style="background:#f3f4f6;color:#374151;">🏦 بنك</span>
        @endif
        <span class="ad-badge" style="background: {{ $sm[1] }}; color: {{ $sm[0] }};">مدير المدرسة: {{ $sm[2] }}</span>
        <span class="ad-badge" style="background: {{ $am[1] }}; color: {{ $am[0] }};">الإدارة: {{ $am[2] }}</span>
    </div>

    <h1 class="ad-title">🎯 {{ $activity->title }}</h1>

    @if($activity->description)
        <div class="ad-desc">{!! safe_html($activity->description) !!}</div>
    @endif

    <div class="ad-meta">
        <div class="ad-meta-item"><span class="ad-meta-label">المعلّم</span><span class="ad-meta-value" style="font-size:15px;">{{ $activity->creator->name ?? '—' }}</span></div>
        @if($activity->points)
            <div class="ad-meta-item"><span class="ad-meta-label">النقاط</span><span class="ad-meta-value">{{ $activity->points }} 🪙</span></div>
        @endif
        @if($activity->passing_score)
            <div class="ad-meta-item"><span class="ad-meta-label">درجة النجاح</span><span class="ad-meta-value">{{ $activity->passing_score }}%</span></div>
        @endif
        <div class="ad-meta-item"><span class="ad-meta-label">عدد الأسئلة</span><span class="ad-meta-value">{{ is_array($activity->questions) ? count($activity->questions) : 0 }}</span></div>
        <div class="ad-meta-item"><span class="ad-meta-label">التصحيح</span><span class="ad-meta-value" style="font-size:15px;">{{ $activity->manual_review ? 'يدوي (المعلّم)' : 'تلقائي' }}</span></div>
        <div class="ad-meta-item"><span class="ad-meta-label">النشر</span><span class="ad-meta-value" style="font-size:14px;">{{ $publishText }}</span></div>
        <div class="ad-meta-item"><span class="ad-meta-label">تاريخ الإضافة</span><span class="ad-meta-value" style="font-size:15px;">{{ $activity->created_at?->format('Y-m-d') }}</span></div>
    </div>

    {{-- سبب الرفض (إن وُجد) --}}
    @if($schoolStatus === 'rejected' && $activity->school_rejection_reason)
        <div style="margin-bottom:16px; padding:12px 16px; background:#fef2f2; border-right:4px solid #ef4444; border-radius:8px; color:#991b1b;">
            <strong>سبب رفض مدير المدرسة:</strong> {{ $activity->school_rejection_reason }}
        </div>
    @endif
    @if($adminStatus === 'rejected' && $activity->rejection_reason)
        <div style="margin-bottom:16px; padding:12px 16px; background:#fef2f2; border-right:4px solid #ef4444; border-radius:8px; color:#991b1b;">
            <strong>سبب رفض الإدارة:</strong> {{ $activity->rejection_reason }}
        </div>
    @endif

    {{-- شريط الإجراءات حسب الدور --}}
    <div class="ad-actions">
        @if($isOwnerTeacher)
            <a href="{{ route('teacher.activities.edit', $activity->id) }}" class="ad-btn ad-btn-primary">✏️ تعديل النشاط</a>
            <a href="{{ route('teacher.activities') }}" class="ad-btn ad-btn-secondary">⬅️ العودة</a>
            <div class="ad-note" style="width:100%;">أيّ تعديل يُعيد النشاط للاعتماد (مدير المدرسة ثم الإدارة) قبل ظهوره للطلاب.</div>
        @elseif($isSchoolAdmin && $schoolStatus === 'pending')
            <form method="POST" action="{{ route('school-admin.activity-approvals.approve', $activity->id) }}" style="display:flex; gap:10px; align-items:center;">
                @csrf
                <select name="publish_mode" class="ad-select">
                    <option value="direct">مباشر للطلاب</option>
                    <option value="bank">للبنك فقط</option>
                </select>
                <button type="submit" class="ad-btn ad-btn-approve">✅ اعتماد لمدرستي</button>
            </form>
            <button type="button" class="ad-btn ad-btn-reject" onclick="adOpenReject()">❌ رفض</button>
            <a href="{{ route('school-admin.activity-approvals') }}" class="ad-btn ad-btn-secondary">⬅️ العودة</a>
        @elseif($isSchoolAdmin)
            <a href="{{ route('school-admin.activity-approvals') }}" class="ad-btn ad-btn-secondary">⬅️ العودة لقائمة الاعتماد</a>
        @endif
    </div>
</div>

@include('activities.partials.questions')

@if($isSchoolAdmin && $schoolStatus === 'pending')
<div id="adRejectModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1050; align-items:center; justify-content:center;">
    <div style="background:white; padding:28px; border-radius:15px; max-width:480px; width:90%;">
        <h3 style="margin-bottom:16px;">❌ رفض النشاط</h3>
        <form method="POST" action="{{ route('school-admin.activity-approvals.reject', $activity->id) }}">
            @csrf
            <label style="display:block; margin-bottom:8px; font-weight:600;">سبب الرفض:</label>
            <textarea name="rejection_reason" required rows="4" style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; resize:vertical;" placeholder="اكتب سبب الرفض ليطّلع عليه المعلّم ويعدّل نشاطه"></textarea>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:16px;">
                <button type="button" onclick="adCloseReject()" style="background:#e5e7eb; color:#374151; padding:10px 20px; border:none; border-radius:8px; cursor:pointer;">إلغاء</button>
                <button type="submit" style="background:#ef4444; color:white; padding:10px 20px; border:none; border-radius:8px; cursor:pointer;">تأكيد الرفض</button>
            </div>
        </form>
    </div>
</div>
<script>
function adOpenReject() { document.getElementById('adRejectModal').style.display = 'flex'; }
function adCloseReject() { document.getElementById('adRejectModal').style.display = 'none'; }
document.getElementById('adRejectModal').addEventListener('click', function (e) { if (e.target === this) adCloseReject(); });
</script>
@endif
