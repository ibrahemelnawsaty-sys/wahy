@if(auth()->check() && auth()->user()->hasMultipleRoles())
<div class="role-switcher-container" style="margin-top: auto; padding: 20px; border-top: 2px solid rgba(255, 255, 255, 0.1);">
    <div style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.05) 100%); border-radius: 12px; padding: 16px; backdrop-filter: blur(10px);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            <i class="fas fa-sync-alt" style="color: rgba(255, 255, 255, 0.9); font-size: 16px;"></i>
            <span style="color: rgba(255, 255, 255, 0.9); font-weight: 600; font-size: 13px;">تبديل الدور</span>
        </div>
        
        @php
            $currentRole = auth()->user()->getCurrentRole();
            $allRoles = auth()->user()->getAllRoles();
        @endphp
        
        @foreach($allRoles as $role)
            @if($role !== $currentRole)
                <form method="POST" action="{{ route('switch.role', $role) }}" style="margin:0;">
                    @csrf
                    <button type="submit"
                       class="role-switch-btn"
                       style="display: flex; align-items: center; gap: 12px; padding: 12px 14px; background: rgba(255, 255, 255, 0.95); border:none; border-radius: 10px; width:100%; cursor:pointer; margin-bottom: 8px; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);"
                       onmouseover="this.style.transform='translateX(-5px)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.15)'"
                       onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.1)'">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                            <i class="{{ App\Models\User::getRoleIcon($role) }}" style="font-size: 16px;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 13px; color: #1e293b; margin-bottom: 2px;">
                                {{ App\Models\User::getRoleNameAr($role) }}
                            </div>
                            <div style="font-size: 11px; color: #64748b;">
                                الانتقال للوحة التحكم
                            </div>
                        </div>
                        <i class="fas fa-arrow-left" style="color: #667eea; font-size: 14px;"></i>
                    </button>
                </form>
            @endif
        @endforeach
        
        <!-- الدور الحالي -->
        <div style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 10px; margin-top: 8px;">
            <i class="{{ App\Models\User::getRoleIcon($currentRole) }}" style="color: white; font-size: 14px;"></i>
            <div style="flex: 1;">
                <span style="color: white; font-weight: 600; font-size: 12px;">الدور الحالي:</span>
                <span style="color: rgba(255, 255, 255, 0.95); font-size: 12px; margin-right: 4px;">{{ App\Models\User::getRoleNameAr($currentRole) }}</span>
            </div>
            <i class="fas fa-check-circle" style="color: white; font-size: 14px;"></i>
        </div>
    </div>
</div>
@endif
