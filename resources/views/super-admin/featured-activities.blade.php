@extends('layouts.admin')

@section('title', 'الأنشطة المميزة')

@section('content')
<style>
.fa-page { padding: 0; }

.fa-hero {
    background: linear-gradient(135deg, #f59e0b 0%, #f97316 50%, #ef4444 100%);
    border-radius: 18px;
    padding: 32px;
    margin-bottom: 28px;
    color: white;
    box-shadow: 0 10px 30px rgba(245, 158, 11, 0.35);
    position: relative;
    overflow: hidden;
}
.fa-hero::before {
    content: '';
    position: absolute;
    top: -40%;
    left: -20%;
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}
.fa-hero::after {
    content: '';
    position: absolute;
    bottom: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
    border-radius: 50%;
}
.fa-hero-icon {
    width: 60px; height: 60px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    margin-bottom: 14px;
}
.fa-hero h1 { font-size: 28px; font-weight: 800; margin: 0 0 6px; position: relative; z-index: 1; }
.fa-hero p { opacity: 0.9; font-size: 15px; margin: 0; position: relative; z-index: 1; }

/* Stats Grid */
.fa-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 18px;
    margin-bottom: 28px;
}
.fa-stat {
    background: white;
    border-radius: 16px;
    padding: 24px;
    border: 2px solid #f1f5f9;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 18px;
}
.fa-stat:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.08);
    border-color: #e2e8f0;
}
.fa-stat-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.fa-stat-icon i { font-size: 24px; color: white; }
.fa-stat-value {
    font-size: 30px;
    font-weight: 800;
    color: #1e293b;
    line-height: 1;
}
.fa-stat-label {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
    margin-top: 4px;
}

/* Card */
.fa-card {
    background: white;
    border-radius: 18px;
    border: 2px solid #f1f5f9;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    overflow: hidden;
}
.fa-card-header {
    padding: 20px 24px;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, rgba(245,158,11,0.03) 0%, rgba(239,68,68,0.03) 100%);
}
.fa-card-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

/* Table */
.fa-table {
    width: 100%;
    border-collapse: collapse;
}
.fa-table thead th {
    padding: 14px 16px;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    text-align: right;
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}
.fa-table tbody tr {
    transition: all 0.2s;
}
.fa-table tbody tr:hover {
    background: linear-gradient(135deg, rgba(245,158,11,0.03) 0%, rgba(239,68,68,0.03) 100%);
}
.fa-table tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
    vertical-align: middle;
}
.fa-activity-title {
    font-weight: 700;
    color: #1e293b;
    font-size: 14px;
}
.fa-activity-desc {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 3px;
}

/* User Avatar */
.fa-user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
.fa-user-avatar {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    display: flex; align-items: center; justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}

/* Value Badge */
.fa-value-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    background: linear-gradient(135deg, #dbeafe, #ede9fe);
    color: #4338ca;
}
.fa-value-badge.empty {
    background: #f1f5f9;
    color: #94a3b8;
}

/* Reason */
.fa-reason {
    font-size: 13px;
    color: #64748b;
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Date */
.fa-date {
    font-size: 13px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Action Buttons */
.fa-actions { display: flex; gap: 6px; }
.fa-action-btn {
    width: 36px; height: 36px;
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    background: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    color: #475569;
}
.fa-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.fa-action-btn.view:hover { border-color: #667eea; color: #667eea; background: #f0f0ff; }
.fa-action-btn.remove:hover { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
.fa-action-btn i { font-size: 13px; }

/* Empty State */
.fa-empty {
    text-align: center;
    padding: 70px 20px;
}
.fa-empty-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    font-size: 40px;
    margin: 0 auto 18px;
    box-shadow: 0 6px 20px rgba(245,158,11,0.15);
}

/* Alert */
.fa-alert {
    padding: 14px 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #dcfce7, #d1fae5);
    color: #166534;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 2px solid #bbf7d0;
}

/* Modal Overrides */
.fa-modal-header {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 20px 24px;
    border-radius: 16px 16px 0 0;
}
.fa-modal-header h5 { font-weight: 700; margin: 0; }
.fa-modal-body { padding: 24px; }
.fa-modal-footer {
    padding: 16px 24px;
    border-top: 2px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.fa-modal-btn {
    padding: 10px 22px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.fa-modal-btn-cancel { background: #f1f5f9; color: #475569; }
.fa-modal-btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; box-shadow: 0 4px 14px rgba(239,68,68,0.3); }
.fa-modal-btn-danger:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(239,68,68,0.4); }
</style>

<div class="fa-page">
    <!-- Hero Header -->
    <div class="fa-hero">
        <div class="fa-hero-icon">⭐</div>
        <h1>الأنشطة المميزة</h1>
        <p>الأنشطة التي تم تمييزها من قبل المعلمين</p>
    </div>

    @if(session('success'))
        <div class="fa-alert">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="fa-stats">
        <div class="fa-stat">
            <div class="fa-stat-icon" style="background: linear-gradient(135deg, #f59e0b, #f97316); box-shadow: 0 6px 16px rgba(245,158,11,0.3);">
                <i class="fas fa-star"></i>
            </div>
            <div>
                <div class="fa-stat-value">{{ $stats['total_featured'] }}</div>
                <div class="fa-stat-label">إجمالي الأنشطة المميزة</div>
            </div>
        </div>
        <div class="fa-stat">
            <div class="fa-stat-icon" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 16px rgba(16,185,129,0.3);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <div class="fa-stat-value">{{ $stats['this_month'] }}</div>
                <div class="fa-stat-label">هذا الشهر</div>
            </div>
        </div>
        <div class="fa-stat">
            <div class="fa-stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2); box-shadow: 0 6px 16px rgba(102,126,234,0.3);">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div>
                <div class="fa-stat-value">{{ $stats['by_teachers'] }}</div>
                <div class="fa-stat-label">عدد المعلمين المشاركين</div>
            </div>
        </div>
    </div>

    <!-- Activities List -->
    <div class="fa-card">
        <div class="fa-card-header">
            <span style="font-size: 20px;">📋</span>
            <h3>قائمة الأنشطة المميزة</h3>
        </div>
        <div>
            @if($activities->count() > 0)
                <div style="overflow-x: auto;">
                    <table class="fa-table">
                        <thead>
                            <tr>
                                <th>النشاط</th>
                                <th>المعلم</th>
                                <th>القيمة</th>
                                <th>سبب التمييز</th>
                                <th>تاريخ التمييز</th>
                                <th style="width: 100px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                                <tr>
                                    <td>
                                        <div class="fa-activity-title">{{ $activity->title }}</div>
                                        <div class="fa-activity-desc">{{ Str::limit($activity->description, 50) }}</div>
                                    </td>
                                    <td>
                                        <div class="fa-user-cell">
                                            <div class="fa-user-avatar">
                                                {{ mb_substr($activity->featuredBy->name ?? 'N', 0, 1) }}
                                            </div>
                                            <div>
                                                <span style="font-weight: 600; color: #1e293b; font-size: 13px;">{{ $activity->featuredBy->name ?? 'غير محدد' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($activity->lesson?->concept?->value)
                                            <span class="fa-value-badge">
                                                {{ $activity->lesson->concept->value->name }}
                                            </span>
                                        @else
                                            <span class="fa-value-badge empty">غير محدد</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fa-reason" title="{{ $activity->featured_reason }}">
                                            {{ Str::limit($activity->featured_reason, 40) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fa-date">
                                            🕐 {{ $activity->featured_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fa-actions">
                                            <a href="{{ route('admin.featured-activities.show', $activity->id) }}" class="fa-action-btn view" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="fa-action-btn remove" data-bs-toggle="modal" data-bs-target="#unfeatureModal{{ $activity->id }}" title="إلغاء التمييز">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Unfeature Modal -->
                                <div class="modal fade" id="unfeatureModal{{ $activity->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content" style="border-radius: 18px; overflow: hidden; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.15);">
                                            <div class="fa-modal-header">
                                                <h5>⚠️ إلغاء تمييز النشاط</h5>
                                            </div>
                                            <div class="fa-modal-body">
                                                <p style="color: #475569; font-size: 15px;">هل أنت متأكد من إلغاء تمييز هذا النشاط؟</p>
                                                <div style="padding: 14px 18px; background: #fef3c7; border-radius: 12px; border: 2px solid #fde68a; margin-top: 12px;">
                                                    <strong style="color: #92400e;">{{ $activity->title }}</strong>
                                                </div>
                                            </div>
                                            <div class="fa-modal-footer">
                                                <button type="button" class="fa-modal-btn fa-modal-btn-cancel" data-bs-dismiss="modal">إلغاء</button>
                                                <form action="{{ route('admin.featured-activities.unfeature', $activity->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="fa-modal-btn fa-modal-btn-danger">
                                                        ✕ إلغاء التمييز
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div style="padding: 20px; border-top: 2px solid #f1f5f9;">
                    {{ $activities->links() }}
                </div>
            @else
                <div class="fa-empty">
                    <div class="fa-empty-icon">⭐</div>
                    <h3 style="font-size: 20px; font-weight: 700; color: #475569; margin: 0 0 6px;">لا توجد أنشطة مميزة حالياً</h3>
                    <p style="color: #94a3b8; margin: 0; font-size: 14px;">لم يقم أي معلم بتمييز أنشطة بعد</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
