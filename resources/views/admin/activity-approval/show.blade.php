@extends('layouts.admin')

@section('title', 'تفاصيل النشاط')

@section('content')
<div class="admin-page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <a href="{{ route('admin.activity-approval.index') }}" style="color: #6366f1; text-decoration: none;">← العودة للقائمة</a>
        <h1 style="margin-top: 10px;">{{ $activity->title }}</h1>
    </div>
    @if($activity->approval_status === 'pending')
    <div style="display: flex; gap: 10px;">
        <button onclick="showApproveModal()" style="background: #10b981; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px;">
            ✅ الموافقة على النشاط
        </button>
        <button onclick="showRejectModal()" style="background: #ef4444; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px;">
            ❌ رفض النشاط
        </button>
    </div>
    @endif
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
    <!-- تفاصيل النشاط -->
    <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <h3 style="margin-bottom: 20px; color: #374151;">📝 تفاصيل النشاط</h3>
        
        <div style="margin-bottom: 15px;">
            <label style="font-weight: 600; color: #6b7280;">الوصف:</label>
            {{-- safe_html (لا html_excerpt المبتور) — يعرض الوصف الغنيّ كاملاً كما لدى المعلّم --}}
            <div style="margin-top: 5px;" class="rich-content">{!! $activity->description ? safe_html($activity->description) : 'لا يوجد وصف' !!}</div>
        </div>

        @include('activities.partials.media')

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 20px;">
            <div style="background: #f9fafb; padding: 15px; border-radius: 10px;">
                <div style="font-size: 12px; color: #6b7280;">النوع</div>
                <div style="font-weight: 600; margin-top: 5px;">
                    @php
                        $typeLabels = ['quiz' => 'اختبار', 'exercise' => 'تمرين', 'project' => 'مشروع', 'creative' => 'إبداعي'];
                    @endphp
                    {{ $typeLabels[$activity->type] ?? $activity->type }}
                </div>
            </div>
            <div style="background: #f9fafb; padding: 15px; border-radius: 10px;">
                <div style="font-size: 12px; color: #6b7280;">النقاط</div>
                <div style="font-weight: 600; margin-top: 5px;">{{ $activity->points }} نقطة</div>
            </div>
            <div style="background: #f9fafb; padding: 15px; border-radius: 10px;">
                <div style="font-size: 12px; color: #6b7280;">درجة النجاح</div>
                <div style="font-weight: 600; margin-top: 5px;">{{ $activity->passing_score ?? 0 }}%</div>
            </div>
            <div style="background: #f9fafb; padding: 15px; border-radius: 10px;">
                <div style="font-size: 12px; color: #6b7280;">الحالة</div>
                <div style="font-weight: 600; margin-top: 5px;">
                    @php
                        $statusAr = match($activity->status) {
                            'active' => 'نشط',
                            'draft' => 'مسودة',
                            'inactive' => 'غير نشط',
                            default => $activity->status
                        };
                    @endphp
                    {{ $statusAr }}
                </div>
            </div>
        </div>

        @if($activity->lesson)
        <div style="margin-top: 20px; padding: 15px; background: #eff6ff; border-radius: 10px;">
            <div style="font-size: 12px; color: #3b82f6;">📚 الدرس المرتبط</div>
            <div style="font-weight: 600; margin-top: 5px;">{{ $activity->lesson->title }}</div>
            <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                {{ $activity->lesson->concept->value->name ?? '' }} > 
                {{ $activity->lesson->concept->name ?? '' }} > 
                {{ $activity->lesson->title ?? '' }}
            </div>
        </div>
        @endif

        {{-- العارض الموحّد الغنيّ (تمييز الإجابة الصحيحة، الإجابة المتوقعة لـshort_answer، الكلمة
             المستهدفة لـletter_choice) — بدل العارض الداخليّ الناقص، ليطابق آلية عرض المعلّم. --}}
        <div style="margin-top: 20px;">
            @include('activities.partials.questions')
        </div>
    </div>

    <!-- معلومات المعلم والحالة -->
    <div>
        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <h3 style="margin-bottom: 20px; color: #374151;">👨‍🏫 معلومات المعلم</h3>
            @if($activity->creator)
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    {{ mb_substr($activity->creator->name, 0, 1) }}
                </div>
                <div>
                    <div style="font-weight: 600; font-size: 18px;">{{ $activity->creator->name }}</div>
                    <div style="color: #6b7280;">{{ $activity->creator->email }}</div>
                    <div style="color: #6b7280; font-size: 13px;">{{ $activity->creator->school->name ?? '' }}</div>
                </div>
            </div>
            @endif
        </div>

        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <h3 style="margin-bottom: 20px; color: #374151;">📊 حالة الموافقة</h3>
            @php
                $statusColors = ['pending' => '#f59e0b', 'approved' => '#10b981', 'rejected' => '#ef4444'];
                $statusLabels = ['pending' => 'في الانتظار', 'approved' => 'معتمد', 'rejected' => 'مرفوض'];
            @endphp
            <div style="background: {{ $statusColors[$activity->approval_status] ?? '#6b7280' }}20; color: {{ $statusColors[$activity->approval_status] ?? '#6b7280' }}; padding: 15px; border-radius: 10px; text-align: center; font-weight: 600; font-size: 18px;">
                {{ $statusLabels[$activity->approval_status] ?? $activity->approval_status }}
            </div>

            @if($activity->approved_at)
            <div style="margin-top: 15px; font-size: 13px; color: #6b7280;">
                <div>📅 تاريخ المراجعة: {{ $activity->approved_at->format('Y/m/d H:i') }}</div>
                @if($activity->approver)
                <div>👤 بواسطة: {{ $activity->approver->name }}</div>
                @endif
            </div>
            @endif

            @if($activity->rejection_reason)
            <div style="margin-top: 15px; background: #fef2f2; padding: 15px; border-radius: 10px;">
                <div style="font-weight: 600; color: #ef4444; margin-bottom: 5px;">سبب الرفض:</div>
                <p style="color: #991b1b;">{{ $activity->rejection_reason }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal الرفض -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 15px; max-width: 500px; width: 90%;">
        <h3 style="margin-bottom: 20px;">❌ رفض النشاط</h3>
        <form action="{{ route('admin.activity-approval.reject', $activity) }}" method="POST">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">سبب الرفض:</label>
                <textarea name="rejection_reason" required rows="4" style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px;"></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeRejectModal()" style="background: #e5e7eb; color: #374151; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">إلغاء</button>
                <button type="submit" style="background: #ef4444; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">تأكيد الرفض</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal الموافقة: النطاق + وضع النشر + مدارس محدّدة -->
<div id="approveModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 15px; max-width: 560px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-bottom: 20px;">✅ اعتماد النشاط ونشره</h3>
        <form action="{{ route('admin.activity-approval.approve', $activity) }}" method="POST">
            @csrf
            <div style="margin-bottom: 18px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">نطاق النشر</label>
                <label style="display:block; padding:12px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:8px; cursor:pointer;">
                    <input type="radio" name="scope" value="all" checked onchange="toggleScope()">
                    <strong>كل المدارس</strong>
                    <span style="display:block; font-size:12px; color:#6b7280; margin-right:22px;">يُنشر لجميع المدارس (الافتراضي).</span>
                </label>
                <label style="display:block; padding:12px; border:1px solid #e5e7eb; border-radius:8px; cursor:pointer;">
                    <input type="radio" name="scope" value="specific" onchange="toggleScope()">
                    <strong>مدارس محدّدة</strong>
                    <span style="display:block; font-size:12px; color:#6b7280; margin-right:22px;">اختر المدارس المستهدفة فقط.</span>
                </label>
            </div>

            <div id="schoolsPicker" style="display:none; margin-bottom: 18px; border:1px solid #e5e7eb; border-radius:8px; padding:12px; max-height:200px; overflow-y:auto;">
                <label style="display:block; margin-bottom:8px; font-weight:600; font-size:13px;">اختر المدارس:</label>
                @forelse($schools as $school)
                    <label style="display:block; padding:6px 0; cursor:pointer;">
                        <input type="checkbox" name="school_ids[]" value="{{ $school->id }}"> {{ $school->name }}
                    </label>
                @empty
                    <div style="color:#9ca3af; font-size:13px;">لا توجد مدارس.</div>
                @endforelse
            </div>

            <div style="margin-bottom: 22px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">وضع النشر</label>
                <label style="display:block; padding:12px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:8px; cursor:pointer;">
                    <input type="radio" name="publish_mode" value="direct" checked>
                    <strong>مباشر للطلاب</strong>
                    <span style="display:block; font-size:12px; color:#6b7280; margin-right:22px;">يظهر تلقائيًّا للطلاب ضمن الدرس/الواجب.</span>
                </label>
                <label style="display:block; padding:12px; border:1px solid #e5e7eb; border-radius:8px; cursor:pointer;">
                    <input type="radio" name="publish_mode" value="bank">
                    <strong>للبنك فقط</strong>
                    <span style="display:block; font-size:12px; color:#6b7280; margin-right:22px;">يختاره المعلّمون من البنك — لا يظهر تلقائيًّا للطلاب.</span>
                </label>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeApproveModal()" style="background: #e5e7eb; color: #374151; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">إلغاء</button>
                <button type="submit" style="background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">تأكيد الاعتماد والنشر</button>
            </div>
        </form>
    </div>
</div>

<script>
function showApproveModal() { toggleScope(); document.getElementById('approveModal').style.display = 'flex'; }
function closeApproveModal() { document.getElementById('approveModal').style.display = 'none'; }
function toggleScope() {
    const specific = document.querySelector('#approveModal input[name="scope"]:checked').value === 'specific';
    document.getElementById('schoolsPicker').style.display = specific ? 'block' : 'none';
}
document.getElementById('approveModal').addEventListener('click', function(e) { if (e.target === this) closeApproveModal(); });

function showRejectModal() { document.getElementById('rejectModal').style.display = 'flex'; }
function closeRejectModal() { document.getElementById('rejectModal').style.display = 'none'; }
document.getElementById('rejectModal').addEventListener('click', function(e) { if (e.target === this) closeRejectModal(); });
</script>
@endsection
