@extends('layouts.admin')

@section('title', 'المتصلين الآن')
@section('page-title', 'المتصلين الآن')

@section('content')
<div id="onlineUsersContainer">
    {{-- شريط الإحصائيات --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 16px; padding: 20px; color: white; text-align: center;">
            <div style="font-size: 32px; font-weight: 800;" id="stat-total">{{ $totalOnline }}</div>
            <div style="font-size: 14px; opacity: 0.9;">إجمالي المتصلين</div>
        </div>
        @foreach($stats as $role => $stat)
        <div style="background: var(--card-bg, white); border-radius: 16px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
            <div style="font-size: 28px; font-weight: 800; color: var(--color-primary, #667eea);" id="stat-{{ $role }}">{{ $stat['count'] }}</div>
            <div style="font-size: 13px; color: var(--text-secondary, #64748b);">{{ $stat['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- أزرار التحديث والفلترة --}}
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
        <button onclick="refreshOnlineUsers()" id="refreshBtn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 12px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s ease; font-family: inherit;">
            <i class="fas fa-sync-alt" id="refreshIcon"></i>
            تحديث الآن
        </button>

        <div style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: rgba(16, 185, 129, 0.1); border-radius: 10px; color: #10b981; font-size: 13px; font-weight: 600;">
            <span class="pulse-dot" style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; display: inline-block; animation: pulse 2s infinite;"></span>
            تحديث تلقائي كل 30 ثانية
        </div>

        <div style="margin-right: auto; display: flex; gap: 8px;">
            <button class="role-filter-btn active" data-role="all" onclick="filterByRole('all', this)" style="padding: 8px 16px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: var(--color-primary, #667eea); color: white; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.2s;">الكل</button>
            @foreach($stats as $role => $stat)
            <button class="role-filter-btn" data-role="{{ $role }}" onclick="filterByRole('{{ $role }}', this)" style="padding: 8px 16px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: var(--card-bg, white); color: var(--text-secondary, #64748b); font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.2s;">{{ $stat['label'] }}</button>
            @endforeach
        </div>
    </div>

    {{-- جدول المستخدمين --}}
    <div style="background: var(--card-bg, white); border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;" id="onlineUsersTable">
                <thead>
                    <tr style="background: rgba(102, 126, 234, 0.05);">
                        <th style="padding: 14px 20px; text-align: right; font-weight: 700; font-size: 13px; color: var(--text-secondary, #64748b); white-space: nowrap;">#</th>
                        <th style="padding: 14px 20px; text-align: right; font-weight: 700; font-size: 13px; color: var(--text-secondary, #64748b); white-space: nowrap;">المستخدم</th>
                        <th style="padding: 14px 20px; text-align: right; font-weight: 700; font-size: 13px; color: var(--text-secondary, #64748b); white-space: nowrap;">الدور</th>
                        <th style="padding: 14px 20px; text-align: right; font-weight: 700; font-size: 13px; color: var(--text-secondary, #64748b); white-space: nowrap;">المدرسة</th>
                        <th style="padding: 14px 20px; text-align: right; font-weight: 700; font-size: 13px; color: var(--text-secondary, #64748b); white-space: nowrap;">آخر نشاط</th>
                        <th style="padding: 14px 20px; text-align: right; font-weight: 700; font-size: 13px; color: var(--text-secondary, #64748b); white-space: nowrap;">الحالة</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @forelse($onlineUsers as $index => $user)
                    <tr class="user-row" data-role="{{ $user->role }}" style="border-bottom: 1px solid rgba(0,0,0,0.05); transition: background 0.2s;" onmouseover="this.style.background='rgba(102,126,234,0.03)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 14px 20px; font-size: 14px; color: var(--text-secondary, #64748b);">{{ $index + 1 }}</td>
                        <td style="padding: 14px 20px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 12px; overflow: hidden; border: 2px solid #10b981; flex-shrink: 0;">
                                    <img src="{{ $user->avatar ? asset('storage/app/public/data/' . $user->avatar) : asset('storage/app/public/data/avatars/default-avatar.webp') }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div>
                                    <div style="font-weight: 600; font-size: 14px; color: var(--text-primary, #1e293b);">{{ $user->name }}</div>
                                    <div style="font-size: 12px; color: var(--text-secondary, #94a3b8);">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px 20px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 8px; background: rgba(102,126,234,0.1); color: #667eea; font-size: 13px; font-weight: 600;">
                                <i class="{{ $user->role_icon }}"></i>
                                {{ $user->role_ar }}
                            </span>
                        </td>
                        <td style="padding: 14px 20px; font-size: 14px; color: var(--text-primary, #475569);">{{ $user->school_name ?? '—' }}</td>
                        <td style="padding: 14px 20px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #10b981; font-weight: 600;">
                                <span style="width: 6px; height: 6px; background: #10b981; border-radius: 50; display: inline-block;"></span>
                                {{ $user->online_since }}
                            </span>
                        </td>
                        <td style="padding: 14px 20px;">
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 8px; background: rgba(16,185,129,0.1); color: #10b981; font-size: 12px; font-weight: 600;">
                                🟢 متصل
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="6" style="padding: 60px 20px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 16px;">😴</div>
                            <div style="font-size: 16px; font-weight: 600; color: var(--text-secondary, #64748b);">لا يوجد مستخدمين متصلين حالياً</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .role-filter-btn:hover {
        border-color: var(--color-primary, #667eea) !important;
        color: var(--color-primary, #667eea) !important;
    }
    .role-filter-btn.active {
        background: var(--color-primary, #667eea) !important;
        color: white !important;
        border-color: var(--color-primary, #667eea) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let autoRefreshInterval;
    let currentFilter = 'all';

    // التحديث التلقائي كل 30 ثانية
    function startAutoRefresh() {
        autoRefreshInterval = setInterval(refreshOnlineUsers, 30000);
    }

    // تحديث البيانات
    async function refreshOnlineUsers() {
        const icon = document.getElementById('refreshIcon');
        const btn = document.getElementById('refreshBtn');
        
        icon.style.animation = 'spin 1s linear infinite';
        btn.style.opacity = '0.7';
        btn.disabled = true;

        try {
            const response = await fetch('{{ route("admin.online-users.api") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            const data = await response.json();

            // تحديث الإحصائيات
            document.getElementById('stat-total').textContent = data.totalOnline;
            for (const [role, stat] of Object.entries(data.stats)) {
                const el = document.getElementById('stat-' + role);
                if (el) el.textContent = stat.count;
            }

            // تحديث جدول المستخدمين
            const tbody = document.getElementById('usersTableBody');
            if (data.onlineUsers.length === 0) {
                tbody.innerHTML = `
                    <tr id="emptyRow">
                        <td colspan="6" style="padding: 60px 20px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 16px;">😴</div>
                            <div style="font-size: 16px; font-weight: 600; color: var(--text-secondary, #64748b);">لا يوجد مستخدمين متصلين حالياً</div>
                        </td>
                    </tr>`;
            } else {
                tbody.innerHTML = data.onlineUsers.map((user, index) => `
                    <tr class="user-row" data-role="${user.role}" style="border-bottom: 1px solid rgba(0,0,0,0.05); transition: background 0.2s; ${currentFilter !== 'all' && user.role !== currentFilter ? 'display:none;' : ''}" onmouseover="this.style.background='rgba(102,126,234,0.03)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 14px 20px; font-size: 14px; color: var(--text-secondary, #64748b);">${index + 1}</td>
                        <td style="padding: 14px 20px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 12px; overflow: hidden; border: 2px solid #10b981; flex-shrink: 0;">
                                    <img src="${user.avatar ? '/storage/app/public/data/' + user.avatar : '/storage/app/public/data/avatars/default-avatar.webp'}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div>
                                    <div style="font-weight: 600; font-size: 14px; color: var(--text-primary, #1e293b);">${user.name}</div>
                                    <div style="font-size: 12px; color: var(--text-secondary, #94a3b8);">${user.email}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px 20px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 8px; background: rgba(102,126,234,0.1); color: #667eea; font-size: 13px; font-weight: 600;">
                                <i class="${user.role_icon}"></i>
                                ${user.role_ar}
                            </span>
                        </td>
                        <td style="padding: 14px 20px; font-size: 14px; color: var(--text-primary, #475569);">${user.school_name || '—'}</td>
                        <td style="padding: 14px 20px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #10b981; font-weight: 600;">
                                <span style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; display: inline-block;"></span>
                                ${user.online_since}
                            </span>
                        </td>
                        <td style="padding: 14px 20px;">
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 8px; background: rgba(16,185,129,0.1); color: #10b981; font-size: 12px; font-weight: 600;">
                                🟢 متصل
                            </span>
                        </td>
                    </tr>
                `).join('');
            }
        } catch (err) {
            console.error('فشل تحديث المستخدمين:', err);
        } finally {
            icon.style.animation = '';
            btn.style.opacity = '1';
            btn.disabled = false;
        }
    }

    // فلترة حسب الدور
    function filterByRole(role, btn) {
        currentFilter = role;
        
        // تحديث زر الفلتر
        document.querySelectorAll('.role-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // فلترة الصفوف
        document.querySelectorAll('.user-row').forEach(row => {
            if (role === 'all' || row.getAttribute('data-role') === role) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // بدء التحديث التلقائي
    startAutoRefresh();
</script>
@endpush
@endsection
