@php
    // السوبر أدمن يدير التذاكر من داخل لوحته (منفصلة تماماً عن لوحة موظّف الدعم الفنيّ)،
    // فيما يراها موظّف الدعم داخل لوحة الدعم. البيانات والمنطق مشتركان، والقشرة تختلف بالدور.
    $__ticketLayout = auth()->user()->role === 'technical_support' ? 'layouts.support' : 'layouts.admin';
@endphp
@extends($__ticketLayout)

@section('title', 'تذاكر الدعم الفنيّ')
@section('page-title', 'تذاكر الدعم الفنيّ')

@section('content')
    {{-- تحت لايوت الأدمن نُدرِج أصناف الدعم (لايوت الدعم يُدرِجها أصلاً) --}}
    @if(auth()->user()->role !== 'technical_support')
        @include('support.partials.styles')
    @endif

    {{-- زرّ سريع للتذاكر المُصعّدة داخل الصفحة --}}
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
        @if(request('escalated'))
            <a href="{{ route('support.tickets.index') }}" class="support-btn support-btn-secondary" style="color:#fff;">← عرض كل التذاكر</a>
            <span class="support-badge escalate">🚨 تعرض المُصعّدة فقط</span>
        @else
            <a href="{{ route('support.tickets.index', ['escalated' => 1]) }}" class="support-btn support-btn-escalate">
                🚨 التذاكر المُصعّدة ({{ number_format($counts['escalated']) }})
            </a>
        @endif
    </div>

    <!-- Counters (منها «حُلّت») -->
    <div class="admin-stats-grid" style="margin-bottom: 24px;">
        <a href="{{ route('support.tickets.index') }}" class="admin-stat-card" style="text-decoration: none;">
            <div class="admin-stat-icon primary">🎫</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">الكل</div>
                <div class="admin-stat-value">{{ number_format($counts['all']) }}</div>
            </div>
        </a>
        <a href="{{ route('support.tickets.index', ['status' => 'open']) }}" class="admin-stat-card" style="text-decoration: none;">
            <div class="admin-stat-icon warning">📂</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">مفتوحة</div>
                <div class="admin-stat-value">{{ number_format($counts['open']) }}</div>
            </div>
        </a>
        <a href="{{ route('support.tickets.index', ['status' => 'answered']) }}" class="admin-stat-card" style="text-decoration: none;">
            <div class="admin-stat-icon info">💬</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">تم الرد</div>
                <div class="admin-stat-value">{{ number_format($counts['answered']) }}</div>
            </div>
        </a>
        <a href="{{ route('support.tickets.index', ['status' => 'resolved']) }}" class="admin-stat-card" style="text-decoration: none;">
            <div class="admin-stat-icon success">✅</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">حُلّت</div>
                <div class="admin-stat-value">{{ number_format($counts['resolved']) }}</div>
            </div>
        </a>
        <a href="{{ route('support.tickets.index', ['status' => 'closed']) }}" class="admin-stat-card" style="text-decoration: none;">
            <div class="admin-stat-icon secondary">🔒</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">مغلقة</div>
                <div class="admin-stat-value">{{ number_format($counts['closed']) }}</div>
            </div>
        </a>
        <a href="{{ route('support.tickets.index', ['escalated' => 1]) }}" class="admin-stat-card" style="text-decoration: none;">
            <div class="admin-stat-icon accent">⬆️</div>
            <div class="admin-stat-content">
                <div class="admin-stat-label">مُصعّدة</div>
                <div class="admin-stat-value">{{ number_format($counts['escalated']) }}</div>
            </div>
        </a>
    </div>

    <!-- Filters -->
    <div class="support-card" style="padding: 20px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('support.tickets.index') }}">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 13px; font-weight: 700; color: #475569;">🔍 بحث</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="الموضوع أو اسم/بريد المستخدم..."
                           style="padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 13px; font-weight: 700; color: #475569;">⚡ الحالة</label>
                    <select name="status" style="padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                        <option value="">الكل</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>مفتوحة</option>
                        <option value="answered" {{ request('status') == 'answered' ? 'selected' : '' }}>تم الرد</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>محلولة</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>مغلقة</option>
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 13px; font-weight: 700; color: #475569;">🎚️ الأولوية</label>
                    <select name="priority" style="padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                        <option value="">الكل</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>عالية</option>
                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>عادية</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>منخفضة</option>
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 13px; font-weight: 700; color: #475569;">🏷️ التصنيف</label>
                    <select name="category" style="padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                        <option value="">الكل</option>
                        <option value="technical" {{ request('category') == 'technical' ? 'selected' : '' }}>مشكلة تقنية</option>
                        <option value="account" {{ request('category') == 'account' ? 'selected' : '' }}>مشكلة حساب</option>
                        <option value="content" {{ request('category') == 'content' ? 'selected' : '' }}>محتوى</option>
                        <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>أخرى</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #475569; margin-left: auto;">
                    <input type="checkbox" name="escalated" value="1" {{ request('escalated') ? 'checked' : '' }}>
                    عرض المُصعّدة فقط
                </label>
                <button type="submit" class="support-btn support-btn-primary">تطبيق الفلترة</button>
                <a href="{{ route('support.tickets.index') }}" class="support-btn support-btn-secondary" style="color:#fff;">إعادة تعيين</a>
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="support-card" style="overflow: hidden;">
        @if($tickets->count() > 0)
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="support-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الموضوع</th>
                        <th>صاحب التذكرة</th>
                        <th>التصنيف</th>
                        <th>الأولوية</th>
                        <th>الحالة</th>
                        <th>مُسنَدة إلى</th>
                        <th>آخر تحديث</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr>
                        <td style="color: #94a3b8; font-weight: 700;">#{{ $ticket->id }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $ticket->subject }}</div>
                            @if($ticket->escalated)
                            <span class="support-badge escalate" style="margin-top: 4px;">⬆️ مُصعّدة</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 500;">{{ $ticket->user->name ?? 'غير معروف' }}</div>
                            <div style="font-size: 12px; color: #94a3b8;">{{ $ticket->user->email ?? '' }}</div>
                        </td>
                        <td><span style="font-size: 13px; color: #64748b;">{{ $ticket->categoryLabel() }}</span></td>
                        <td>
                            @php $pc = $ticket->priority === 'high' ? 'danger' : ($ticket->priority === 'low' ? 'secondary' : 'info'); @endphp
                            <span class="support-badge {{ $pc }}">{{ $ticket->priorityLabel() }}</span>
                        </td>
                        <td><span class="support-badge {{ $ticket->statusColor() }}">{{ $ticket->statusLabel() }}</span></td>
                        <td>
                            @if($ticket->assignee)
                                {{ $ticket->assignee->name }}
                            @else
                                <span style="color: #cbd5e1;">—</span>
                            @endif
                        </td>
                        <td style="color: #64748b; font-size: 13px; white-space: nowrap;">{{ $ticket->updated_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('support.tickets.show', $ticket) }}" class="support-btn support-btn-primary">فتح</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 16px;">
            {{ $tickets->links() }}
        </div>
        @else
        <div style="text-align: center; padding: 60px 20px; color: #64748b;">
            <div style="font-size: 56px; opacity: .3; margin-bottom: 12px;">🎫</div>
            <h3 style="margin: 0 0 6px;">لا توجد تذاكر</h3>
            <p style="margin: 0;">لم يُعثر على تذاكر مطابقة. جرّب تغيير الفلاتر.</p>
        </div>
        @endif
    </div>
@endsection
