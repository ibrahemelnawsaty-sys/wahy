@extends('layouts.admin')

@section('title', 'الموافقة على الأنشطة')

@section('content')
<div class="admin-page-header">
    <h1>🎯 الموافقة على أنشطة بنك الأنشطة</h1>
    <p>مراجعة الأنشطة المقدمة من المعلمين للموافقة عليها أو رفضها</p>
</div>

<!-- إحصائيات -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; padding: 20px; border-radius: 15px;">
        <div style="font-size: 32px; font-weight: bold;">{{ $stats['pending'] }}</div>
        <div style="opacity: 0.9;">في انتظار الموافقة</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px; border-radius: 15px;">
        <div style="font-size: 32px; font-weight: bold;">{{ $stats['approved'] }}</div>
        <div style="opacity: 0.9;">تمت الموافقة</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 20px; border-radius: 15px;">
        <div style="font-size: 32px; font-weight: bold;">{{ $stats['rejected'] }}</div>
        <div style="opacity: 0.9;">مرفوض</div>
    </div>
</div>

<!-- فلاتر -->
<div class="filters-section" style="background: white; padding: 20px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
    <form method="GET" action="{{ route('admin.activity-approval.index') }}" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 200px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث عن نشاط أو معلم..." 
                   style="width: 100%; padding: 10px 15px; border: 1px solid #e5e7eb; border-radius: 8px;">
        </div>
        <div>
            <select name="status" style="padding: 10px 15px; border: 1px solid #e5e7eb; border-radius: 8px;">
                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>في انتظار الموافقة</option>
                <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>تمت الموافقة</option>
                <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>الكل</option>
            </select>
        </div>
        <button type="submit" style="background: #6366f1; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">
            🔍 بحث
        </button>
    </form>
</div>

<!-- قائمة الأنشطة -->
<div class="activities-list" style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
    @if($activities->isEmpty())
        <div style="padding: 60px; text-align: center; color: #6b7280;">
            <div style="font-size: 48px; margin-bottom: 15px;">📭</div>
            <p>لا توجد أنشطة {{ $status == 'pending' ? 'في انتظار الموافقة' : '' }}</p>
        </div>
    @else
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <th style="padding: 15px; text-align: right;">النشاط</th>
                    <th style="padding: 15px; text-align: right;">المعلم</th>
                    <th style="padding: 15px; text-align: right;">الدرس</th>
                    <th style="padding: 15px; text-align: center;">النوع</th>
                    <th style="padding: 15px; text-align: center;">الحالة</th>
                    <th style="padding: 15px; text-align: center;">التاريخ</th>
                    <th style="padding: 15px; text-align: center;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities as $activity)
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 15px;">
                        <div style="font-weight: 600; color: #1f2937;">{{ $activity->title }}</div>
                        @if($activity->description)
                            <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">{{ html_excerpt($activity->description, 80) }}</div>
                        @endif
                    </td>
                    <td style="padding: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 35px; height: 35px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center;">
                                {{ mb_substr($activity->creator->name ?? '?', 0, 1) }}
                            </div>
                            <div>
                                <div style="font-weight: 500;">{{ $activity->creator->name ?? 'غير معروف' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $activity->creator->school->name ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 15px;">
                        @if($activity->lesson)
                            <div style="font-size: 13px;">{{ $activity->lesson->title }}</div>
                            <div style="font-size: 11px; color: #6b7280;">
                                {{ $activity->lesson->concept->value->name ?? '' }} > 
                                {{ $activity->lesson->concept->name ?? '' }}
                            </div>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        @php
                            $typeColors = [
                                'quiz' => '#8b5cf6',
                                'exercise' => '#3b82f6',
                                'project' => '#10b981',
                                'creative' => '#f59e0b',
                            ];
                            $typeLabels = [
                                'quiz' => 'اختبار',
                                'exercise' => 'تمرين',
                                'project' => 'مشروع',
                                'creative' => 'إبداعي',
                            ];
                        @endphp
                        <span style="background: {{ $typeColors[$activity->type] ?? '#6b7280' }}20; color: {{ $typeColors[$activity->type] ?? '#6b7280' }}; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                            {{ $typeLabels[$activity->type] ?? $activity->type }}
                        </span>
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        @php
                            $statusColors = [
                                'pending' => '#f59e0b',
                                'approved' => '#10b981',
                                'rejected' => '#ef4444',
                            ];
                            $statusLabels = [
                                'pending' => 'في الانتظار',
                                'approved' => 'معتمد',
                                'rejected' => 'مرفوض',
                            ];
                        @endphp
                        <span style="background: {{ $statusColors[$activity->approval_status] ?? '#6b7280' }}20; color: {{ $statusColors[$activity->approval_status] ?? '#6b7280' }}; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                            {{ $statusLabels[$activity->approval_status] ?? $activity->approval_status }}
                        </span>
                    </td>
                    <td style="padding: 15px; text-align: center; font-size: 13px; color: #6b7280;">
                        {{ $activity->created_at->format('Y/m/d') }}
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <a href="{{ route('admin.activity-approval.show', $activity) }}" 
                               style="background: #6366f1; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px;">
                                👁️ عرض
                            </a>
                            @if($activity->approval_status === 'pending')
                                <form action="{{ route('admin.activity-approval.approve', $activity) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" style="background: #10b981; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px;">
                                        ✅ موافقة
                                    </button>
                                </form>
                                <button onclick="showRejectModal({{ $activity->id }})" 
                                        style="background: #ef4444; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px;">
                                    ❌ رفض
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="padding: 20px;">
            {{ $activities->withQueryString()->links() }}
        </div>
    @endif
</div>

<!-- Modal الرفض -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 15px; max-width: 500px; width: 90%;">
        <h3 style="margin-bottom: 20px;">❌ رفض النشاط</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">سبب الرفض:</label>
                <textarea name="rejection_reason" required rows="4" 
                          style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; resize: vertical;"
                          placeholder="اكتب سبب رفض هذا النشاط..."></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeRejectModal()" 
                        style="background: #e5e7eb; color: #374151; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">
                    إلغاء
                </button>
                <button type="submit" 
                        style="background: #ef4444; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">
                    تأكيد الرفض
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(activityId) {
    document.getElementById('rejectForm').action = '/admin/activity-approval/' + activityId + '/reject';
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endsection
