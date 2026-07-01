@extends('layouts.school-admin')

@section('page-title', 'الإحصائيات والتصنيف')
@section('breadcrumb')
    <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a> / الإحصائيات والتصنيف
@endsection

@section('content')
<!-- Header -->
<div class="row mb-4">
    <div class="col-12">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 24px; padding: 40px; box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3); position: relative; overflow: hidden;">
            <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
            <div style="position: relative; z-index: 1;">
                <h1 style="font-size: 32px; font-weight: 800; color: white; margin-bottom: 8px;">
                    <i class="fas fa-chart-bar me-2"></i>
                    الإحصائيات والتصنيف
                </h1>
                <p style="color: rgba(255,255,255,0.9); font-size: 16px; margin: 0;">
                    تعرف على تصنيف مدرستك ومعلميك وطلابك على مستوى المنصة والدولة والمدينة
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Badges Row -->
@php $allBadges = array_merge($schoolStats['badges'] ?? [], $teacherStats['badges'] ?? []); @endphp
@if(!empty($allBadges))
<div class="row mb-4 g-3">
    @foreach($allBadges as $badge)
    <div class="col-md-4">
        <div class="badge-card-white" style="background: white; border-radius: 16px; padding: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border-right: 5px solid {{ $badge['color'] }}; display: flex; align-items: center; gap: 15px; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 35px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)'">
            <div style="font-size: 40px; line-height: 1;">{{ $badge['icon'] }}</div>
            <div>
                <div style="font-weight: 700; color: #1a202c; font-size: 15px;">{{ $badge['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<!-- Tabs -->
<ul class="nav nav-pills mb-4 stats-tabs-bar" id="statsTabs" role="tablist" style="background: white; padding: 8px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
    <li class="nav-item" role="presentation" style="flex: 1;">
        <button class="nav-link active w-100" id="school-tab" data-bs-toggle="pill" data-bs-target="#school-pane" type="button" role="tab" style="border-radius: 12px; font-weight: 700; font-size: 16px; padding: 14px;">
            <i class="fas fa-school me-2"></i>المدرسة
        </button>
    </li>
    <li class="nav-item" role="presentation" style="flex: 1;">
        <button class="nav-link w-100" id="teachers-tab" data-bs-toggle="pill" data-bs-target="#teachers-pane" type="button" role="tab" style="border-radius: 12px; font-weight: 700; font-size: 16px; padding: 14px;">
            <i class="fas fa-chalkboard-teacher me-2"></i>المعلمين
        </button>
    </li>
    <li class="nav-item" role="presentation" style="flex: 1;">
        <button class="nav-link w-100" id="students-tab" data-bs-toggle="pill" data-bs-target="#students-pane" type="button" role="tab" style="border-radius: 12px; font-weight: 700; font-size: 16px; padding: 14px;">
            <i class="fas fa-user-graduate me-2"></i>الطلاب
        </button>
    </li>
</ul>

<div class="tab-content" id="statsTabContent">
    <!-- ========== TAB: المدرسة ========== -->
    <div class="tab-pane fade show active" id="school-pane" role="tabpanel">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="stat-icon"><i class="fas fa-star"></i></div>
                    <div class="stat-value">{{ number_format($schoolStats['total_points']) }}</div>
                    <div class="stat-label">إجمالي النقاط</div>
                    @if($schoolStats['trend'] !== 'same')
                    <div class="stat-trend {{ $schoolStats['trend'] }}">
                        <i class="fas fa-arrow-{{ $schoolStats['trend'] === 'up' ? 'up' : 'down' }}"></i>
                        {{ $schoolStats['rank_change'] }} مركز
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="stat-icon"><i class="fas fa-fire"></i></div>
                    <div class="stat-value">{{ number_format($schoolStats['monthly_points']) }}</div>
                    <div class="stat-label">نقاط هذا الشهر</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="stat-icon"><i class="fas fa-globe"></i></div>
                    <div class="stat-value">#{{ $schoolStats['platform_rank'] }}</div>
                    <div class="stat-label">من {{ $schoolStats['platform_total'] }} مدرسة</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <div class="stat-icon"><i class="fas fa-city"></i></div>
                    <div class="stat-value">#{{ $schoolStats['city_rank'] }}</div>
                    <div class="stat-label">من {{ $schoolStats['city_total'] }} في {{ $school->city ?? 'المدينة' }}</div>
                </div>
            </div>
        </div>

        <!-- Ranking Cards -->
        <div class="row g-4">
            @foreach([
                ['title' => 'التصنيف العالمي (المنصة)', 'icon' => 'fa-globe-americas', 'color' => '#667eea', 'rank' => $schoolStats['platform_rank'], 'total' => $schoolStats['platform_total'], 'data' => $schoolStats['top_schools_platform']],
                ['title' => 'التصنيف على مستوى الدولة', 'icon' => 'fa-flag', 'color' => '#f5576c', 'rank' => $schoolStats['country_rank'], 'total' => $schoolStats['country_total'], 'data' => $schoolStats['top_schools_country']],
                ['title' => 'التصنيف على مستوى المدينة', 'icon' => 'fa-city', 'color' => '#43e97b', 'rank' => $schoolStats['city_rank'], 'total' => $schoolStats['city_total'], 'data' => $schoolStats['top_schools_city']],
            ] as $ranking)
            <div class="col-md-4">
                <div class="ranking-card">
                    <div class="ranking-header" style="background: {{ $ranking['color'] }};">
                        <i class="fas {{ $ranking['icon'] }} me-2"></i>
                        {{ $ranking['title'] }}
                    </div>
                    <div class="ranking-position">
                        <span class="position-badge" style="background: {{ $ranking['color'] }};">#{{ $ranking['rank'] }}</span>
                        <span class="position-total">من {{ $ranking['total'] }}</span>
                        @php $pct = $ranking['total'] > 0 ? round((1 - $ranking['rank']/$ranking['total']) * 100) : 0; @endphp
                        <div class="progress mt-2" style="height: 8px; border-radius: 10px;">
                            <div class="progress-bar" style="width: {{ $pct }}%; background: {{ $ranking['color'] }}; border-radius: 10px;"></div>
                        </div>
                        <small class="text-muted">أفضل من {{ $pct }}% من المدارس</small>
                    </div>
                    <div class="ranking-list">
                        @foreach($ranking['data'] as $i => $s)
                        <div class="ranking-item {{ $s->id == $school->id ? 'current' : '' }}">
                            <span class="rank-num {{ $i < 3 ? 'top' : '' }}">{{ $i + 1 }}</span>
                            <span class="rank-name">{{ $s->name }}</span>
                            <span class="rank-points">{{ number_format($s->total_points) }} <small>نقطة</small></span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- ========== TAB: المعلمين ========== -->
    <div class="tab-pane fade" id="teachers-pane" role="tabpanel">
        @if(!empty($teacherStats['badges']))
        <div class="row mb-4">
            @foreach($teacherStats['badges'] as $badge)
            <div class="col-md-6 mx-auto">
                <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 16px; padding: 20px; text-align: center; box-shadow: 0 8px 25px rgba(245,158,11,0.15);">
                    <div style="font-size: 48px;">{{ $badge['icon'] }}</div>
                    <div style="font-weight: 700; color: #92400e; font-size: 18px; margin-top: 8px;">{{ $badge['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="row g-4">
            @foreach([
                ['title' => 'معلمين المدرسة', 'icon' => 'fa-school', 'color' => '#10b981', 'data' => $teacherStats['school_teachers']],
                ['title' => 'معلمين المدينة', 'icon' => 'fa-city', 'color' => '#3b82f6', 'data' => $teacherStats['city_teachers']],
                ['title' => 'معلمين الدولة', 'icon' => 'fa-flag', 'color' => '#f59e0b', 'data' => $teacherStats['country_teachers']],
                ['title' => 'معلمين المنصة', 'icon' => 'fa-globe', 'color' => '#8b5cf6', 'data' => $teacherStats['platform_teachers']],
            ] as $section)
            <div class="col-md-6">
                <div class="ranking-card">
                    <div class="ranking-header" style="background: {{ $section['color'] }};">
                        <i class="fas {{ $section['icon'] }} me-2"></i>
                        {{ $section['title'] }}
                    </div>
                    <div class="ranking-list">
                        @forelse($section['data'] as $i => $t)
                        <div class="ranking-item {{ $t->school_id == $school->id ? 'current' : '' }}">
                            <span class="rank-num {{ $i < 3 ? 'top' : '' }}">{{ $i + 1 }}</span>
                            <span class="rank-name">
                                {{ $t->name }}
                                @if($t->school_id == $school->id)
                                    <small class="badge bg-success ms-1" style="font-size: 10px;">مدرستك</small>
                                @endif
                            </span>
                            <span class="rank-points">{{ number_format($t->total_points) }} <small>نقطة</small></span>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users fa-2x mb-2 opacity-50 d-block"></i>
                            <small>لا توجد بيانات</small>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- ========== TAB: الطلاب ========== -->
    <div class="tab-pane fade" id="students-pane" role="tabpanel">
        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="stat-card" style="background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);">
                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="stat-value">{{ $studentStats['total_school'] }}</div>
                    <div class="stat-label">طالب في مدرستك</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="stat-icon"><i class="fas fa-globe"></i></div>
                    <div class="stat-value">{{ number_format($studentStats['total_platform']) }}</div>
                    <div class="stat-label">طالب على المنصة</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @foreach([
                ['title' => 'أفضل طلاب المدرسة', 'icon' => 'fa-school', 'color' => '#10b981', 'data' => $studentStats['school_students']],
                ['title' => 'أفضل طلاب المدينة', 'icon' => 'fa-city', 'color' => '#3b82f6', 'data' => $studentStats['city_students']],
                ['title' => 'أفضل طلاب الدولة', 'icon' => 'fa-flag', 'color' => '#f59e0b', 'data' => $studentStats['country_students']],
                ['title' => 'أفضل طلاب المنصة', 'icon' => 'fa-globe', 'color' => '#8b5cf6', 'data' => $studentStats['platform_students']],
            ] as $section)
            <div class="col-md-6">
                <div class="ranking-card">
                    <div class="ranking-header" style="background: {{ $section['color'] }};">
                        <i class="fas {{ $section['icon'] }} me-2"></i>
                        {{ $section['title'] }}
                    </div>
                    <div class="ranking-list">
                        @forelse($section['data'] as $i => $s)
                        <div class="ranking-item {{ $s->school_id == $school->id ? 'current' : '' }}">
                            <span class="rank-num {{ $i < 3 ? 'top' : '' }}">{{ $i + 1 }}</span>
                            <span class="rank-name">
                                {{ $s->name }}
                                @if($s->school_id == $school->id)
                                    <small class="badge bg-success ms-1" style="font-size: 10px;">مدرستك</small>
                                @endif
                            </span>
                            <span class="rank-points">{{ number_format($s->total_points ?? 0) }} <small>نقطة</small></span>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users fa-2x mb-2 opacity-50 d-block"></i>
                            <small>لا توجد بيانات</small>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Grade Level Rankings -->
        @if(!empty($studentStats['grade_rankings']))
        <h4 class="fw-bold mt-5 mb-3">
            <i class="fas fa-layer-group me-2 text-primary"></i>
            التصنيف حسب المرحلة الدراسية
        </h4>
        <div class="row g-4">
            @foreach($studentStats['grade_rankings'] as $grade => $students)
            <div class="col-md-6">
                <div class="ranking-card">
                    <div class="ranking-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-graduation-cap me-2"></i>
                        {{ $grade }}
                    </div>
                    <div class="ranking-list">
                        @forelse($students as $i => $s)
                        <div class="ranking-item {{ $s->school_id == $school->id ? 'current' : '' }}">
                            <span class="rank-num {{ $i < 3 ? 'top' : '' }}">{{ $i + 1 }}</span>
                            <span class="rank-name">
                                {{ $s->name }}
                                @if($s->school_id == $school->id)
                                    <small class="badge bg-success ms-1" style="font-size: 10px;">مدرستك</small>
                                @endif
                            </span>
                            <span class="rank-points">{{ number_format($s->total_points ?? 0) }} <small>نقطة</small></span>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <small>لا توجد بيانات لهذه المرحلة</small>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Wahy dark-mode coverage — بطاقات الشارات والتبويبات ذات الخلفية البيضاء inline
       (بطاقات .ranking-card و.rank-name و#statsTabs مُغطّاة مركزياً في لايوت الدور). */
    html[data-theme="dark"] .badge-card-white,
    html[data-theme="dark"] .stats-tabs-bar { background: var(--w-card) !important; box-shadow: var(--w-shadow); }
    html[data-theme="dark"] .badge-card-white [style*="color: #1a202c"] { color: var(--w-text) !important; }

    #statsTabs {
        --bs-nav-link-color: #334155;
        --bs-nav-link-hover-color: #1e293b;
        --bs-nav-pills-link-active-color: #fff;
        --bs-nav-pills-link-active-bg: transparent;
    }
    #statsTabs .nav-link {
        color: #334155 !important;
        background: #f1f5f9 !important;
        transition: all 0.3s;
    }
    #statsTabs .nav-link i {
        color: #667eea !important;
    }
    #statsTabs .nav-link.active,
    #statsTabs .nav-link.active:focus {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    #statsTabs .nav-link.active i {
        color: #ffffff !important;
    }
    #statsTabs .nav-link:hover:not(.active) {
        background: #e2e8f0 !important;
        color: #1e293b !important;
    }

    .stat-card {
        border-radius: 20px;
        padding: 28px;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        transition: transform 0.3s;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-icon {
        font-size: 24px;
        opacity: 0.8;
        margin-bottom: 10px;
    }
    .stat-value {
        font-size: 32px;
        font-weight: 800;
        line-height: 1.1;
    }
    .stat-label {
        font-size: 14px;
        opacity: 0.9;
        margin-top: 5px;
    }
    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        margin-top: 8px;
    }
    .stat-trend.up { background: rgba(255,255,255,0.3); color: #dcfce7; }
    .stat-trend.down { background: rgba(255,255,255,0.3); color: #fecaca; }

    .ranking-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.3s;
        border: 1px solid rgba(0,0,0,0.04);
    }
    .ranking-card:hover {
        box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        transform: translateY(-3px);
    }
    .ranking-header {
        color: white;
        padding: 18px 24px;
        font-weight: 700;
        font-size: 16px;
    }
    .ranking-position {
        padding: 20px 24px;
        text-align: center;
        border-bottom: 1px solid #f1f5f9;
    }
    .position-badge {
        display: inline-block;
        color: white;
        font-size: 28px;
        font-weight: 800;
        padding: 10px 24px;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .position-total {
        display: block;
        color: #94a3b8;
        font-size: 14px;
        margin-top: 8px;
    }
    .ranking-list { padding: 12px; }
    .ranking-item {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        border-radius: 12px;
        transition: background 0.2s;
        gap: 12px;
    }
    .ranking-item:hover { background: #f8fafc; }
    .ranking-item.current {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #93c5fd;
    }
    .rank-num {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-weight: 800;
        font-size: 14px;
        background: #f1f5f9;
        color: #64748b;
        flex-shrink: 0;
    }
    .rank-num.top {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        box-shadow: 0 4px 10px rgba(245,158,11,0.3);
    }
    .rank-name {
        flex: 1;
        font-weight: 600;
        color: #1e293b;
        font-size: 14px;
    }
    .rank-points {
        font-weight: 700;
        color: #667eea;
        font-size: 14px;
        white-space: nowrap;
    }
    .rank-points small {
        font-weight: 400;
        color: #94a3b8;
        font-size: 11px;
    }

    /* Wahy dark-mode round2: صفّ المستخدم الحالي .ranking-item.current خلفيته تدرّج فاتح
       (#eff6ff→#dbeafe) بينما .rank-name يُفتَّح في اللايوت => فاتح-على-فاتح ويختفي.
       نعتّم الخلفية بـ!important (يتغلّب على linear-gradient) ونثبّت نصّاً فاتحاً واضحاً. */
    html[data-theme="dark"] .ranking-item.current {
        background: rgba(96, 165, 250, 0.16) !important;
        border-color: rgba(96, 165, 250, 0.45) !important;
    }
    html[data-theme="dark"] .ranking-item.current .rank-name { color: var(--w-text) !important; }
    html[data-theme="dark"] .ranking-item.current .rank-points { color: #93c5fd !important; }
</style>
@endpush
