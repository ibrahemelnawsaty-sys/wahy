<style>
/* ===== Wahy · مودال «بدء محادثة» — طبقة بصرية فاخرة (كل الوظائف/المُعرّفات محفوظة) =====
   الأسطح مبنية على متغيّرات الثيم (--w-*) فتعمل في الوضعَين تلقائياً؛
   الشارات وكتلة البثّ تبقى inline لتلتقطها dark-coverage وتحفظ لكنتها الوظيفية. */

.user-select-modal {
    position: fixed;
    inset: 0;
    background: rgba(2, 6, 23, 0.62);
    -webkit-backdrop-filter: blur(6px);
    backdrop-filter: blur(6px);
    display: none;                 /* تُبدَّل إلى flex عبر showUserSelect() */
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 24px;
    animation: usOverlayIn 0.22s ease;
}

@keyframes usOverlayIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes usModalIn {
    from { opacity: 0; transform: translateY(18px) scale(.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes usSheetIn {
    from { opacity: 0; transform: translateY(40px); }
    to   { opacity: 1; transform: translateY(0); }
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 640px;
    max-height: min(82vh, 760px);
    background: var(--w-card, #ffffff);
    color: var(--w-text, #0f172a);
    border: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(2, 6, 23, 0.38);
    animation: usModalIn 0.32s cubic-bezier(.16, 1, .3, 1);
}

/* ===== الهيدر ===== */
.us-header {
    flex: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 22px 24px;
    border-bottom: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    background: var(--w-card, #ffffff);
}
.us-header-titles { display: flex; align-items: center; gap: 14px; min-width: 0; }
.us-header-icon {
    flex: none;
    width: 48px; height: 48px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff; font-size: 20px;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.35);
}
.us-title {
    margin: 0;
    font-size: 19px;
    font-weight: 800;
    line-height: 1.2;
    color: var(--w-text, #0f172a);
}
.us-subtitle {
    margin: 3px 0 0;
    font-size: 12.5px;
    color: var(--w-text-muted, #64748b);
}
.us-close {
    flex: none;
    min-width: 44px; min-height: 44px;
    border-radius: 50%;
    border: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    background: var(--w-bg, #f1f5f9);
    color: var(--w-text-muted, #64748b);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; line-height: 1;
    transition: background .18s, color .18s, transform .18s;
}
.us-close:hover { background: #ef4444; color: #fff; transform: rotate(90deg); }
.us-close:focus-visible { outline: 2px solid #667eea; outline-offset: 2px; }

/* ===== منطقة التمرير ===== */
.us-scroll {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 0 22px 8px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: var(--w-border, rgba(15,23,42,.18)) transparent;
}
.us-scroll::-webkit-scrollbar { width: 10px; }
.us-scroll::-webkit-scrollbar-thumb {
    background: var(--w-border, rgba(15, 23, 42, 0.18));
    border-radius: 999px;
    border: 3px solid transparent;
    background-clip: padding-box;
}

/* ===== البحث (لاصق أعلى القائمة) ===== */
.us-search-wrap {
    position: sticky; top: 0; z-index: 3;
    background: var(--w-card, #fff);
    padding: 18px 0 14px;
}
.us-search-field { position: relative; }
.us-search-field > i {
    position: absolute; inset-inline-start: 15px; top: 50%;
    transform: translateY(-50%);
    color: var(--w-text-muted, #94a3b8);
    pointer-events: none;
    font-size: 14px;
}
.us-search-input {
    width: 100%;
    padding: 13px 44px 13px 16px;
    border: 1.5px solid var(--w-border, rgba(15, 23, 42, 0.12));
    border-radius: 12px;
    font-size: 14px;
    background: var(--w-bg, #f8fafc);
    color: var(--w-text, #0f172a);
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.us-search-input::placeholder { color: var(--w-text-muted, #94a3b8); }
.us-search-input:focus {
    outline: none;
    border-color: #667eea;
    background: var(--w-card, #fff);
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
}

/* ===== كتلة البثّ الجماعي (admin) — لكنة زرقاء وظيفية ===== */
.us-broadcast { margin: 4px 0; }

/* ===== رأس قائمة المستخدمين ===== */
.us-list-head {
    display: flex; align-items: center; gap: 8px;
    margin: 18px 0 12px;
    font-size: 14px; font-weight: 700;
    color: var(--w-text-muted, #475569);
}
.us-count {
    margin-inline-start: auto;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    padding: 3px 11px; border-radius: 999px;
    font-size: 12px; font-weight: 700;
}

/* ===== عناصر المستخدم ===== */
.user-list-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 14px;
    margin-bottom: 10px;
    border-radius: 14px;
    cursor: pointer;
    background: var(--w-bg, #f8fafc);
    border: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    transition: transform .18s, box-shadow .18s, border-color .18s, background .18s;
}
.user-list-item:hover,
.user-list-item:focus-visible {
    background: rgba(102, 126, 234, 0.10);
    border-color: rgba(102, 126, 234, 0.55);
    transform: translateX(-4px);
    box-shadow: 0 8px 22px rgba(102, 126, 234, 0.18);
}
.user-avatar {
    flex: none;
    width: 48px; height: 48px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff; font-weight: 700; font-size: 18px;
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.32);
    overflow: hidden;
}
.user-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
.us-user-main { flex: 1; min-width: 0; }
.us-user-row {
    display: flex; align-items: center; gap: 8px;
    flex-wrap: wrap; margin-bottom: 4px;
}
.us-user-name { font-weight: 700; font-size: 15px; color: var(--w-text, #1e293b); }
.us-user-email { font-size: 13px; color: var(--w-text-muted, #64748b); word-break: break-word; }
.us-user-school { font-size: 12px; color: var(--w-text-muted, #94a3b8); margin-top: 2px; }
.us-role-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 7px;
    font-size: 11px; font-weight: 700; white-space: nowrap;
}

/* ===== الحالة الفارغة ===== */
.us-empty {
    display: none;                 /* يتحكّم به filterUsers() */
    flex-direction: column; align-items: center; justify-content: center;
    text-align: center;
    padding: 44px 20px;
    color: var(--w-text-muted, #64748b);
}
.us-empty-icon {
    width: 74px; height: 74px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: rgba(102, 126, 234, 0.12);
    color: #667eea; font-size: 30px;
    margin-bottom: 14px;
}
.us-empty h5 { margin: 0 0 6px; font-size: 16px; font-weight: 700; color: var(--w-text, #0f172a); }
.us-empty p { margin: 0; font-size: 13px; }

/* ===== الفوتر ===== */
.us-footer {
    flex: none;
    padding: 16px 24px 20px;
    border-top: 1px solid var(--w-border, rgba(15, 23, 42, 0.08));
    background: var(--w-card, #fff);
}
.us-footer-btn {
    width: 100%;
    padding: 13px;
    border: 1.5px solid var(--w-border, rgba(15, 23, 42, 0.12));
    background: var(--w-bg, #f8fafc);
    color: var(--w-text-muted, #64748b);
    border-radius: 12px;
    cursor: pointer;
    font-weight: 700; font-size: 14px;
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    transition: background .18s, border-color .18s, color .18s;
}
.us-footer-btn:hover {
    background: rgba(148, 163, 184, 0.16);
    border-color: var(--w-text-muted, #cbd5e1);
    color: var(--w-text, #334155);
}
.us-footer-btn:focus-visible { outline: 2px solid #667eea; outline-offset: 2px; }

/* ===== الاستجابة ===== */
@media (max-width: 1024px) {
    .modal-content { max-width: 92vw; }
}
@media (max-width: 640px) {
    .user-select-modal { align-items: flex-end; padding: 0; }
    .modal-content {
        max-width: 100%;
        width: 100%;
        max-height: 92vh;
        border-radius: 22px 22px 0 0;
        animation: usSheetIn 0.30s cubic-bezier(.16, 1, .3, 1);
    }
    .us-header { padding: 18px; }
    .us-header-icon { width: 42px; height: 42px; font-size: 18px; }
    .us-title { font-size: 17px; }
    .us-scroll { padding: 0 16px 6px; }
    .us-search-wrap { padding: 14px 0 12px; }
    .us-footer { padding: 14px 16px calc(16px + env(safe-area-inset-bottom)); }
    .user-list-item { padding: 11px 12px; gap: 12px; }
    .user-avatar { width: 44px; height: 44px; font-size: 17px; }
    .us-user-email { font-size: 12.5px; }
}

/* عمق أكبر لظلّ المودال في الوضع الليلي (الأسطح نفسها تُدار عبر var(--w-*)) */
html[data-theme="dark"] .modal-content { box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6); }
</style>

<div class="user-select-modal" id="userSelectModal" role="dialog" aria-modal="true" aria-labelledby="userSelectTitle" aria-hidden="true">
    <div class="modal-content">
        <div class="us-header">
            <div class="us-header-titles">
                <div class="us-header-icon">
                    <i class="fas fa-user-plus" aria-hidden="true"></i>
                </div>
                <div>
                    <h3 id="userSelectTitle" class="us-title">بدء محادثة جديدة</h3>
                    <p class="us-subtitle">اختر مستخدماً لبدء الحوار معه</p>
                </div>
            </div>
            <button type="button" onclick="hideUserSelect()" aria-label="إغلاق" class="us-close">
                <span aria-hidden="true">×</span>
            </button>
        </div>

        <div class="us-scroll">
            <!-- حقل البحث -->
            <label for="userSearch" class="sr-only" style="position:absolute;left:-10000px;">ابحث عن مستخدم</label>
            <div class="us-search-wrap">
                <div class="us-search-field">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input
                        type="text"
                        id="userSearch"
                        class="us-search-input"
                        aria-label="ابحث عن مستخدم بالاسم أو البريد الإلكتروني"
                        placeholder="ابحث عن مستخدم بالاسم أو البريد الإلكتروني..."
                        onkeyup="filterUsers()"
                    >
                </div>
            </div>

            <!-- خيار اختيار المدرسة (للسوبر أدمن فقط) -->
            @if($role === 'admin')
                <div class="us-broadcast" style="margin-bottom: 4px; padding: 18px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 2px solid #3b82f6; border-radius: 12px;">
                    <h4 style="margin: 0 0 12px 0; color: #1e40af; display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 600;">
                        <i class="fas fa-school"></i> إرسال لجميع مستخدمي مدرسة
                    </h4>
                    <select
                        id="schoolSelect"
                        style="width: 100%; padding: 12px 14px; border: 2px solid #60a5fa; border-radius: 8px; cursor: pointer; font-size: 14px; background: white;"
                        onchange="selectSchool()"
                    >
                        <option value="">-- اختر مدرسة --</option>
                        @php
                            $schools = \App\Models\School::get();
                        @endphp
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                    <small style="color: #64748b; font-size: 12px; display: block; margin-top: 8px;">
                        <i class="fas fa-info-circle"></i> سيتم إنشاء محادثات منفصلة مع كل مستخدم في المدرسة
                    </small>
                </div>
            @endif

            <!-- قائمة المستخدمين -->
            <div class="us-list-head">
                <i class="fas fa-users" aria-hidden="true"></i>
                المستخدمون المتاحون
                <span class="us-count">{{ count($availableUsers) }}</span>
            </div>
            <div id="usersList" style="padding: 2px;">
                @foreach($availableUsers as $user)
                    <div class="user-list-item"
                         data-name="{{ strtolower($user->name) }}"
                         data-email="{{ strtolower($user->email) }}"
                         data-role="{{ $user->role }}"
                         onclick="startConversation({{ $user->id }})">
                        <div class="user-avatar">
                            @if($user->avatar)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                            @else
                                {{ mb_substr($user->name, 0, 1) }}
                            @endif
                        </div>
                        <div class="us-user-main">
                            <div class="us-user-row">
                                <span class="us-user-name">{{ $user->name }}</span>
                                @if($user->role === 'super_admin')
                                    <span class="us-role-badge" style="background: #fef3c7; color: #92400e;">
                                        <i class="fas fa-crown"></i> مدير النظام
                                    </span>
                                @elseif($user->role === 'school_admin')
                                    <span class="us-role-badge" style="background: #e0e7ff; color: #4338ca;">
                                        <i class="fas fa-user-tie"></i> مدير مدرسة
                                    </span>
                                @elseif($user->role === 'teacher')
                                    <span class="us-role-badge" style="background: #dbeafe; color: #1e40af;">
                                        <i class="fas fa-chalkboard-teacher"></i> معلم
                                    </span>
                                @elseif($user->role === 'student')
                                    <span class="us-role-badge" style="background: #dcfce7; color: #166534;">
                                        <i class="fas fa-user-graduate"></i> طالب
                                    </span>
                                @elseif($user->role === 'parent')
                                    <span class="us-role-badge" style="background: #fef3c7; color: #92400e;">
                                        <i class="fas fa-users"></i> ولي أمر
                                    </span>
                                @endif
                            </div>
                            <div class="us-user-email">{{ $user->email }}</div>
                            @if($user->school)
                                <div class="us-user-school">
                                    <i class="fas fa-school"></i> {{ $user->school->name }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <!-- حالة فارغة (لا مستخدمين / لا نتائج بحث) -->
                <div class="us-empty" id="usersEmpty">
                    <div class="us-empty-icon"><i class="fas fa-user-slash" aria-hidden="true"></i></div>
                    <h5>لا يوجد مستخدمون مطابقون</h5>
                    <p>جرّب كلمة بحث مختلفة، أو تحقّق لاحقاً.</p>
                </div>
            </div>
        </div>

        <div class="us-footer">
            <button type="button" class="us-footer-btn" onclick="hideUserSelect()">
                <i class="fas fa-times"></i> إغلاق
            </button>
        </div>
    </div>
</div>

<script>
let _userSelectPrevFocus = null;
function showUserSelect() {
    _userSelectPrevFocus = document.activeElement;
    const modal = document.getElementById('userSelectModal');
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.getElementById('userSearch').value = '';
    document.getElementById('userSearch').focus();
    filterUsers();
}

function hideUserSelect() {
    const modal = document.getElementById('userSelectModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    if (_userSelectPrevFocus && typeof _userSelectPrevFocus.focus === 'function') {
        try { _userSelectPrevFocus.focus(); } catch(_) {}
    }
}

// Escape key يُغلق modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const m = document.getElementById('userSelectModal');
        if (m && m.style.display === 'flex') {
            hideUserSelect();
        }
    }
});

function startConversation(userId) {
    hideUserSelect();

    // الحصول على اسم المستخدم
    const userItem = document.querySelector(`.user-list-item[onclick*="${userId}"]`);
    const userName = userItem ? userItem.getAttribute('data-name') : 'محادثة جديدة';

    @if($role === 'school-admin')
        // استدعاء دالة loadConversation من الصفحة الرئيسية
        if (typeof loadConversation === 'function') {
            loadConversation(userId, userName);
        } else {
            window.location.href = '/school-admin/messages/' + userId;
        }
    @elseif($role === 'admin')
        // Admin: تحميل المحادثة في نفس الصفحة
        if (typeof loadConversation === 'function') {
            loadConversation(userId, userName, null);
        } else {
            window.location.href = '/messages/' + userId;
        }
    @else
        window.location.href = '/messages/' + userId;
    @endif
}

function filterUsers() {
    const searchValue = document.getElementById('userSearch').value.toLowerCase();
    const users = document.querySelectorAll('.user-list-item');
    let visibleCount = 0;

    users.forEach(user => {
        const name = user.getAttribute('data-name');
        const email = user.getAttribute('data-email');

        if (name.includes(searchValue) || email.includes(searchValue)) {
            user.style.display = 'flex';
            visibleCount++;
        } else {
            user.style.display = 'none';
        }
    });

    // إظهار/إخفاء الحالة الفارغة (إضافة تجميلية آمنة، لا تُغيّر منطق الفلترة)
    const emptyEl = document.getElementById('usersEmpty');
    if (emptyEl) {
        emptyEl.style.display = (visibleCount === 0) ? 'flex' : 'none';
    }
}

function selectSchool() {
    const schoolId = document.getElementById('schoolSelect').value;
    if (!schoolId) {
        filterUsers();
        return;
    }

    // هنا يمكن إضافة منطق لفلترة المستخدمين حسب المدرسة
    // أو إنشاء محادثات جماعية
    alert('سيتم إنشاء محادثات منفصلة مع جميع مستخدمي المدرسة');
}

// إغلاق المودال عند الضغط على الخلفية
document.addEventListener('click', function(event) {
    const modal = document.getElementById('userSelectModal');
    if (event.target === modal) {
        hideUserSelect();
    }
});
</script>
