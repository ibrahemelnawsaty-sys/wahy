@extends('layouts.teacher')

@section('title', 'الإعدادات')

@push('styles')
<style>
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide { animation: slideIn 0.5s ease-out; }
    .hover-lift { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-3px); }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="animate-slide" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 25px; padding: 35px; margin-bottom: 30px; box-shadow: 0 15px 50px rgba(102, 126, 234, 0.3);">
    <div style="display: flex; align-items: center; gap: 15px;">
        <div style="font-size: 56px;">⚙️</div>
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: white; margin-bottom: 8px;">الإعدادات</h1>
            <p style="color: rgba(255,255,255,0.95); font-size: 16px;">إدارة حسابك وتفضيلاتك</p>
        </div>
    </div>
</div>

@if(session('success'))
<div class="animate-slide" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 20px 30px; border-radius: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3);">
    <span style="font-size: 28px;">✅</span>
    <span style="font-weight: 600; font-size: 16px;">{{ session('success') }}</span>
</div>
@endif

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
    
    <!-- Profile Card -->
    <div class="animate-slide hover-lift" style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); height: fit-content;">
        <div style="text-align: center;">
            <div style="width: 120px; height: 120px; margin: 0 auto 20px; border-radius: 50%; overflow: hidden; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h2 style="font-size: 24px; font-weight: 700; color: #1a202c; margin-bottom: 8px;">{{ $user->name }}</h2>
            <p style="color: #718096; font-size: 14px; margin-bottom: 4px;">{{ $user->email }}</p>
            <div style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-top: 12px;">
                👨‍🏫 معلم
            </div>
        </div>
        
        @if($school)
        <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e2e8f0;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                <div style="width: 45px; height: 45px; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px;">🏫</div>
                <div>
                    <div style="font-size: 12px; color: #718096; margin-bottom: 2px;">المدرسة</div>
                    <div style="font-size: 16px; font-weight: 700; color: #1a202c;">{{ $school->name }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Settings Form -->
    <div class="animate-slide" style="background: white; border-radius: 20px; padding: 35px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
        <h3 style="font-size: 22px; font-weight: 700; color: #1a202c; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 28px;">📝</span> المعلومات الشخصية
        </h3>
        
        <form method="POST" action="{{ route('teacher.settings.update') }}" enctype="multipart/form-data">
            @csrf
            
            <div style="display: grid; gap: 25px;">
                
                <!-- Name -->
                <div>
                    <label style="display: block; font-weight: 600; color: #2d3748; margin-bottom: 8px; font-size: 14px;">الاسم الكامل</label>
                    <input type="text" name="name" value="{{ $user->name }}" required
                           style="width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.3s;"
                           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                           onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                </div>
                
                <!-- Email -->
                <div>
                    <label style="display: block; font-weight: 600; color: #2d3748; margin-bottom: 8px; font-size: 14px;">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ $user->email }}" required
                           style="width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.3s;"
                           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                           onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                </div>
                
                <!-- كلمة المرور الحالية (مطلوبة لتغيير البريد) -->
                <div>
                    <label style="display: block; font-weight: 600; color: #2d3748; margin-bottom: 8px; font-size: 14px;">كلمة المرور الحالية <span style="color:#94a3b8;font-weight:400;font-size:12px;">(مطلوبة فقط عند تغيير البريد)</span></label>
                    <input type="password" name="current_password" autocomplete="current-password" placeholder="••••••••"
                           style="width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.3s;"
                           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                           onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    @error('current_password')<div style="color:#dc2626;font-size:13px;margin-top:6px;">{{ $message }}</div>@enderror
                </div>

                <!-- Phone -->
                <div>
                    <label style="display: block; font-weight: 600; color: #2d3748; margin-bottom: 8px; font-size: 14px;">رقم الجوال</label>
                    <input type="text" name="phone" value="{{ $user->phone ?? '' }}" placeholder="05xxxxxxxx"
                           style="width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.3s;"
                           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                           onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                </div>
                
                <!-- Bio -->
                <div>
                    <label style="display: block; font-weight: 600; color: #2d3748; margin-bottom: 8px; font-size: 14px;">نبذة تعريفية</label>
                    <textarea name="bio" rows="4" placeholder="اكتب نبذة قصيرة عنك..."
                              style="width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; resize: vertical; transition: all 0.3s; font-family: 'IBM Plex Sans Arabic', sans-serif;"
                              onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                              onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">{{ $user->bio ?? '' }}</textarea>
                </div>
                
                <!-- Avatar -->
                <div>
                    <label style="display: block; font-weight: 600; color: #2d3748; margin-bottom: 8px; font-size: 14px;">الصورة الشخصية</label>
                    <input type="file" name="avatar" accept="image/*"
                           style="width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 14px; cursor: pointer;"
                           onchange="previewImage(event)">
                    <div id="imagePreview" style="margin-top: 15px; display: none;">
                        <img id="preview" style="max-width: 200px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                </div>
                
                <!-- Notifications -->
                <div style="background: #f7fafc; padding: 20px; border-radius: 12px; border: 2px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 700; color: #2d3748; margin-bottom: 4px; font-size: 15px;">🔔 الإشعارات</div>
                            <div style="color: #718096; font-size: 13px;">استلام إشعارات عند ورود أنشطة جديدة</div>
                        </div>
                        <label style="position: relative; display: inline-block; width: 60px; height: 30px;">
                            <input type="checkbox" name="notifications_enabled" value="1" {{ ($user->notifications_enabled ?? true) ? 'checked' : '' }}
                                   style="opacity: 0; width: 0; height: 0;"
                                   onchange="this.nextElementSibling.style.background = this.checked ? '#48bb78' : '#cbd5e0'">
                            <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: {{ ($user->notifications_enabled ?? true) ? '#48bb78' : '#cbd5e0' }}; transition: 0.4s; border-radius: 30px;">
                                <span style="position: absolute; content: ''; height: 22px; width: 22px; left: 4px; bottom: 4px; background: white; transition: 0.4s; border-radius: 50%;"></span>
                            </span>
                        </label>
                    </div>
                </div>
                
            </div>
            
            <!-- Submit Button -->
            <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e2e8f0;">
                <button type="submit" class="hover-lift"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px 40px; border-radius: 15px; border: none; font-weight: 700; font-size: 16px; cursor: pointer; box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3); transition: all 0.3s;"
                        onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 30px rgba(102, 126, 234, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(102, 126, 234, 0.3)'">
                    💾 حفظ التغييرات
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const preview = document.getElementById('preview');
        const previewDiv = document.getElementById('imagePreview');
        preview.src = reader.result;
        previewDiv.style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

@endsection
