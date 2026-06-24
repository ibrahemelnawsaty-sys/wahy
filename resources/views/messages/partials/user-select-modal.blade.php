<style>
.user-select-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 28px;
    max-width: 650px;
    width: 90%;
    max-height: 75vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { 
        opacity: 0;
        transform: translateY(20px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

.user-list-item {
    padding: 14px;
    border-radius: 10px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-list-item:hover {
    background: white;
    border-color: #667eea;
    transform: translateX(-4px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}
</style>

<div class="user-select-modal" id="userSelectModal" role="dialog" aria-modal="true" aria-labelledby="userSelectTitle" aria-hidden="true">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #f1f5f9;">
            <h3 id="userSelectTitle" style="margin: 0; font-size: 20px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-user-plus" aria-hidden="true"></i>
                بدء محادثة جديدة
            </h3>
            <button type="button" onclick="hideUserSelect()" aria-label="إغلاق"
                    style="background: #ef4444; color: white; border: none; min-width: 44px; min-height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 22px; transition: all 0.2s;">
                <span aria-hidden="true">×</span>
            </button>
        </div>

        <!-- حقل البحث -->
        <label for="userSearch" class="sr-only" style="position:absolute;left:-10000px;">ابحث عن مستخدم</label>
        <div style="position: relative; margin-bottom: 20px;">
            <i class="fas fa-search" aria-hidden="true" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
            <input
                type="text"
                id="userSearch"
                aria-label="ابحث عن مستخدم بالاسم أو البريد الإلكتروني"
                placeholder="ابحث عن مستخدم بالاسم أو البريد الإلكتروني..."
                style="width: 100%; padding: 12px 16px 12px 42px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.2s;"
                onkeyup="filterUsers()"
                onfocus="this.style.borderColor='#667eea'"
                onblur="this.style.borderColor='#e2e8f0'"
            >
        </div>
        
        <!-- خيار اختيار المدرسة (للسوبر أدمن فقط) -->
        @if($role === 'admin')
            <div style="margin-bottom: 20px; padding: 18px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 2px solid #3b82f6; border-radius: 12px;">
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
        <div style="margin-top: 20px;">
            <h4 style="margin: 0 0 14px 0; color: #475569; font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-users"></i>
                المستخدمون المتاحون
                <span style="margin-right: auto; background: #667eea; color: white; padding: 3px 10px; border-radius: 7px; font-size: 12px;">{{ count($availableUsers) }}</span>
            </h4>
            <div id="usersList" style="max-height: 380px; overflow-y: auto; padding: 2px;">
                @foreach($availableUsers as $user)
                    <div class="user-list-item" 
                         data-name="{{ strtolower($user->name) }}" 
                         data-email="{{ strtolower($user->email) }}"
                         data-role="{{ $user->role }}"
                         onclick="startConversation({{ $user->id }})">
                        <div class="user-avatar" style="width: 46px; height: 46px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 17px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); overflow: hidden;">
                            @if($user->avatar)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            @else
                                {{ mb_substr($user->name, 0, 1) }}
                            @endif
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px;">
                                <span style="font-weight: 600; font-size: 15px; color: #1e293b;">{{ $user->name }}</span>
                                @if($user->role === 'super_admin')
                                    <span style="background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                        <i class="fas fa-crown"></i> مدير النظام
                                    </span>
                                @elseif($user->role === 'school_admin')
                                    <span style="background: #e0e7ff; color: #4338ca; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                        <i class="fas fa-user-tie"></i> مدير مدرسة
                                    </span>
                                @elseif($user->role === 'teacher')
                                    <span style="background: #dbeafe; color: #1e40af; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                        <i class="fas fa-chalkboard-teacher"></i> معلم
                                    </span>
                                @elseif($user->role === 'student')
                                    <span style="background: #dcfce7; color: #166534; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                        <i class="fas fa-user-graduate"></i> طالب
                                    </span>
                                @elseif($user->role === 'parent')
                                    <span style="background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                        <i class="fas fa-users"></i> ولي أمر
                                    </span>
                                @endif
                            </div>
                            <div style="font-size: 13px; color: #64748b;">{{ $user->email }}</div>
                            @if($user->school)
                                <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                                    <i class="fas fa-school"></i> {{ $user->school->name }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <button style="width: 100%; padding: 12px; margin-top: 20px; border: 2px solid #e2e8f0; background: white; border-radius: 10px; cursor: pointer; font-weight: 600; color: #64748b; transition: all 0.2s;" 
                onclick="hideUserSelect()"
                onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1';"
                onmouseout="this.style.background='white'; this.style.borderColor='#e2e8f0';">
            <i class="fas fa-times"></i> إغلاق
        </button>
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
    
    users.forEach(user => {
        const name = user.getAttribute('data-name');
        const email = user.getAttribute('data-email');
        
        if (name.includes(searchValue) || email.includes(searchValue)) {
            user.style.display = 'flex';
        } else {
            user.style.display = 'none';
        }
    });
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
