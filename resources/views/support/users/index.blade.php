@extends('layouts.support')

@section('title', 'المستخدمون')
@section('page-title', 'إدارة المستخدمين')

@section('content')
    <div style="margin-bottom: 24px;">
        <h2 style="margin: 0 0 6px; font-size: 20px; font-weight: 700;">إدارة المستخدمين</h2>
        <p style="margin: 0; color: #64748b;">عرض وبحث المستخدمين، تعديل بياناتهم، إعادة كلمات المرور، وتفعيل/تعطيل الحسابات.</p>
    </div>

    <!-- Filters -->
    <div class="support-card" style="padding: 20px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('support.users.index') }}">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 13px; font-weight: 700; color: #475569;">🔍 بحث</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="الاسم، البريد، الهاتف، QR..."
                           style="padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 13px; font-weight: 700; color: #475569;">👤 الدور</label>
                    <select name="role" style="padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                        <option value="">الكل</option>
                        <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>سوبر أدمن</option>
                        <option value="school_admin" {{ request('role') == 'school_admin' ? 'selected' : '' }}>مدير مدرسة</option>
                        <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>معلم</option>
                        <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>طالب</option>
                        <option value="parent" {{ request('role') == 'parent' ? 'selected' : '' }}>ولي أمر</option>
                        <option value="technical_support" {{ request('role') == 'technical_support' ? 'selected' : '' }}>الدعم الفنيّ</option>
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 13px; font-weight: 700; color: #475569;">⚡ الحالة</label>
                    <select name="status" style="padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                        <option value="">الكل</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>موقوف</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="submit" class="support-btn support-btn-primary">تطبيق الفلترة</button>
                <a href="{{ route('support.users.index') }}" class="support-btn support-btn-secondary" style="color:#fff;">إعادة تعيين</a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="support-card" style="overflow: hidden;">
        @if($users->count() > 0)
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="support-table">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>الدور</th>
                        <th>المدرسة</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; overflow: hidden; flex-shrink: 0;">
                                    @if($u->avatar)
                                        <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                    @else
                                        {{ mb_substr($u->name, 0, 1, 'UTF-8') }}
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight: 600;">{{ $u->name }}</div>
                                    <div style="font-size: 12px; color: #94a3b8;">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleColors = [
                                    'super_admin' => 'danger', 'school_admin' => 'info', 'teacher' => 'success',
                                    'student' => 'warning', 'parent' => 'escalate', 'technical_support' => 'secondary',
                                ];
                                $roleLabels = [
                                    'super_admin' => 'سوبر أدمن', 'school_admin' => 'مدير مدرسة', 'teacher' => 'معلم',
                                    'student' => 'طالب', 'parent' => 'ولي أمر', 'technical_support' => 'الدعم الفنيّ',
                                ];
                            @endphp
                            <span class="support-badge {{ $roleColors[$u->role] ?? 'secondary' }}">{{ $roleLabels[$u->role] ?? $u->role }}</span>
                        </td>
                        <td>
                            @if($u->school)
                                <span style="color: #64748b;">{{ $u->school->name }}</span>
                            @else
                                <span style="color: #cbd5e1;">—</span>
                            @endif
                        </td>
                        <td>
                            @php $sc = $u->status === 'active' ? 'success' : ($u->status === 'suspended' ? 'danger' : 'secondary'); @endphp
                            <span class="support-badge {{ $sc }}">
                                @switch($u->status)
                                    @case('active') نشط @break
                                    @case('inactive') غير نشط @break
                                    @case('suspended') موقوف @break
                                    @default {{ $u->status }}
                                @endswitch
                            </span>
                        </td>
                        <td>
                            @if(! $u->hasSuperAdminRole())
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('support.users.edit', $u) }}" class="support-btn support-btn-ghost">✏️ تعديل</a>

                                <button type="button" class="support-btn support-btn-ghost"
                                        onclick="openResetModal('{{ route('support.users.reset-password', $u) }}', @js($u->name))">🔑 كلمة المرور</button>

                                <form method="POST" action="{{ route('support.users.toggle-status', $u) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="support-btn {{ $u->status === 'active' ? 'support-btn-secondary' : 'support-btn-success' }}">
                                        {{ $u->status === 'active' ? '🔴 تعطيل' : '✅ تفعيل' }}
                                    </button>
                                </form>
                            </div>
                            @else
                            <span style="color: #cbd5e1; font-size: 13px;">🔒 محميّ</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 16px;">
            {{ $users->links() }}
        </div>
        @else
        <div style="text-align: center; padding: 60px 20px; color: #64748b;">
            <div style="font-size: 56px; opacity: .3; margin-bottom: 12px;">👥</div>
            <h3 style="margin: 0 0 6px;">لا يوجد مستخدمون</h3>
            <p style="margin: 0;">لم يُعثر على مستخدمين مطابقين. جرّب تغيير الفلاتر.</p>
        </div>
        @endif
    </div>

    <!-- Reset Password Modal -->
    <div id="resetModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
        <div class="support-card" style="width: 100%; max-width: 440px; padding: 0; overflow: hidden;">
            <div style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); padding: 20px 24px; color: #fff;">
                <h3 style="margin: 0; font-size: 17px; font-weight: 700;">🔑 إعادة تعيين كلمة المرور</h3>
                <div id="resetModalUser" style="font-size: 13px; opacity: .9; margin-top: 4px;"></div>
            </div>
            <form method="POST" id="resetModalForm" action="">
                @csrf
                <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">كلمة المرور الجديدة</label>
                        <input type="password" name="password" required minlength="8" placeholder="8 أحرف على الأقل"
                               style="padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" required minlength="8" placeholder="أعد كتابة كلمة المرور"
                               style="padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                    </div>
                    <label style="display: inline-flex; align-items: center; gap: 10px; font-size: 14px; color: #475569; cursor: pointer;">
                        <input type="checkbox" name="force" value="1">
                        إجبار المستخدم على تغيير كلمة المرور عند الدخول التالي
                    </label>
                </div>
                <div style="padding: 16px 24px; display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid #eef2f7;">
                    <button type="button" class="support-btn support-btn-ghost" onclick="closeResetModal()">إلغاء</button>
                    <button type="submit" class="support-btn support-btn-primary">حفظ كلمة المرور</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openResetModal(action, name) {
            var overlay = document.getElementById('resetModalOverlay');
            document.getElementById('resetModalForm').setAttribute('action', action);
            document.getElementById('resetModalUser').textContent = 'المستخدم: ' + name;
            overlay.style.display = 'flex';
        }
        function closeResetModal() {
            var overlay = document.getElementById('resetModalOverlay');
            overlay.style.display = 'none';
            document.getElementById('resetModalForm').reset();
        }
        document.getElementById('resetModalOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeResetModal();
        });
    </script>
@endsection
