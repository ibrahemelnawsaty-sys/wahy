@extends('layouts.school-admin')

@section('title', 'تجربة نظام الإشعارات الزجاجي')
@section('page-title', 'نظام الإشعارات الزجاجي الفاخر')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 40px; margin-bottom: 30px; color: white; text-align: center;">
        <h1 style="font-size: 36px; font-weight: 800; margin-bottom: 15px;">✨ نظام الإشعارات الزجاجي</h1>
        <p style="font-size: 18px; opacity: 0.95;">تصميم Glassmorphism احترافي بأنيميشن ناعمة</p>
    </div>

    <!-- Modal Notifications -->
    <div style="background: white; border-radius: 20px; padding: 35px; margin-bottom: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
        <h2 style="font-size: 24px; font-weight: 700; color: #2d3748; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 32px;">🪟</span> إشعارات Modal
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <button onclick="testSuccess()" style="background: linear-gradient(135deg, #48c774 0%, #3ec46d 100%); color: white; border: none; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                ✓ إشعار نجاح
            </button>
            
            <button onclick="testError()" style="background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); color: white; border: none; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                ✕ إشعار خطأ
            </button>
            
            <button onclick="testWarning()" style="background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%); color: white; border: none; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                ⚠ إشعار تحذير
            </button>
            
            <button onclick="testInfo()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                ℹ إشعار معلومات
            </button>
            
            <button onclick="testConfirm()" style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; border: none; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                ? مربع تأكيد
            </button>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div style="background: white; border-radius: 20px; padding: 35px; margin-bottom: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
        <h2 style="font-size: 24px; font-weight: 700; color: #2d3748; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 32px;">🍞</span> إشعارات Toast (سريعة)
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <button onclick="testToastSuccess()" style="background: rgba(72, 199, 116, 0.15); color: #3ec46d; border: 2px solid #3ec46d; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                ✓ Toast نجاح
            </button>
            
            <button onclick="testToastError()" style="background: rgba(245, 101, 101, 0.15); color: #e53e3e; border: 2px solid #e53e3e; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                ✕ Toast خطأ
            </button>
            
            <button onclick="testToastWarning()" style="background: rgba(246, 173, 85, 0.15); color: #ed8936; border: 2px solid #ed8936; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                ⚠ Toast تحذير
            </button>
            
            <button onclick="testToastInfo()" style="background: rgba(102, 126, 234, 0.15); color: #667eea; border: 2px solid #667eea; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                ℹ Toast معلومات
            </button>
        </div>
    </div>

    <!-- Custom Examples -->
    <div style="background: white; border-radius: 20px; padding: 35px; margin-bottom: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
        <h2 style="font-size: 24px; font-weight: 700; color: #2d3748; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 32px;">🎨</span> أمثلة متقدمة
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <button onclick="testCustomEmoji()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer;">
                🎉 إشعار مع Emoji
            </button>
            
            <button onclick="testCustomIcon()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer;">
                🚀 إشعار مع Icon
            </button>
            
            <button onclick="testMultipleButtons()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer;">
                🎯 أزرار متعددة
            </button>
            
            <button onclick="testPersistentToast()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer;">
                📌 Toast دائم
            </button>
        </div>
    </div>

    <!-- Use Cases -->
    <div style="background: white; border-radius: 20px; padding: 35px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
        <h2 style="font-size: 24px; font-weight: 700; color: #2d3748; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 32px;">💼</span> حالات استخدام عملية
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <button onclick="testDeleteConfirm()" style="background: rgba(245, 101, 101, 0.15); color: #e53e3e; border: 2px solid #e53e3e; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer;">
                🗑️ تأكيد حذف عنصر
            </button>
            
            <button onclick="testSaveSuccess()" style="background: rgba(72, 199, 116, 0.15); color: #3ec46d; border: 2px solid #3ec46d; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer;">
                💾 نجاح الحفظ
            </button>
            
            <button onclick="testValidationError()" style="background: rgba(245, 101, 101, 0.15); color: #e53e3e; border: 2px solid #e53e3e; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer;">
                ❌ خطأ في التحقق
            </button>
            
            <button onclick="testLogoutConfirm()" style="background: rgba(102, 126, 234, 0.15); color: #667eea; border: 2px solid #667eea; padding: 15px 25px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer;">
                🚪 تأكيد تسجيل الخروج
            </button>
        </div>
    </div>

</div>

@push('scripts')
<script>
// Modal Notifications Tests
function testSuccess() {
    glassNotify.success('نجح!', 'تم إتمام العملية بنجاح');
}

function testError() {
    glassNotify.error('حدث خطأ!', 'فشل في تنفيذ العملية. يرجى المحاولة مرة أخرى.');
}

function testWarning() {
    glassNotify.warning('تنبيه!', 'يرجى التحقق من البيانات قبل المتابعة.');
}

function testInfo() {
    glassNotify.info('معلومة', 'هذه معلومة مفيدة قد تحتاج إلى معرفتها.');
}

function testConfirm() {
    glassNotify.confirm(
        'هل أنت متأكد؟',
        'هذا الإجراء سيتطلب تأكيدك قبل المتابعة.',
        function() {
            glassNotify.toastSuccess('تم التأكيد!');
        },
        {
            confirmText: 'نعم، متأكد',
            cancelText: 'إلغاء'
        }
    );
}

// Toast Notifications Tests
function testToastSuccess() {
    glassNotify.toastSuccess('تم الحفظ بنجاح!');
}

function testToastError() {
    glassNotify.toastError('فشلت العملية!');
}

function testToastWarning() {
    glassNotify.toastWarning('يرجى الانتباه لهذا التنبيه');
}

function testToastInfo() {
    glassNotify.toastInfo('معلومة جديدة متاحة');
}

// Advanced Examples
function testCustomEmoji() {
    glassNotify.show({
        type: 'success',
        icon: '🎉',
        title: 'مبروك!',
        message: 'لقد أكملت المهمة بنجاح وحصلت على 100 نقطة!'
    });
}

function testCustomIcon() {
    glassNotify.show({
        type: 'info',
        icon: 'fas fa-rocket',
        title: 'ميزة جديدة!',
        message: 'تم إضافة ميزات جديدة ومثيرة للمنصة'
    });
}

function testMultipleButtons() {
    glassNotify.show({
        type: 'question',
        title: 'اختر إجراءً',
        message: 'ماذا تريد أن تفعل بهذا العنصر؟',
        buttons: [
            {
                text: 'حذف',
                type: 'danger',
                callback: () => glassNotify.toastSuccess('تم الحذف'),
                action: 'close'
            },
            {
                text: 'تعديل',
                type: 'primary',
                callback: () => glassNotify.toastInfo('فتح صفحة التعديل'),
                action: 'close'
            },
            {
                text: 'إلغاء',
                type: 'secondary',
                action: 'close'
            }
        ]
    });
}

function testPersistentToast() {
    glassNotify.toast({
        type: 'warning',
        title: 'رسالة مهمة!',
        message: 'هذه الرسالة لن تختفي حتى تقوم بإغلاقها يدوياً',
        duration: 0,
        closable: true
    });
}

// Use Cases
function testDeleteConfirm() {
    glassNotify.confirm(
        'تأكيد الحذف',
        'هل أنت متأكد من حذف هذا العنصر؟ لا يمكن التراجع عن هذا الإجراء.',
        function() {
            // Simulate delete
            setTimeout(() => {
                glassNotify.toastSuccess('تم الحذف بنجاح');
            }, 500);
        },
        {
            confirmText: 'نعم، احذف',
            cancelText: 'إلغاء',
            confirmType: 'danger'
        }
    );
}

function testSaveSuccess() {
    glassNotify.toast({
        type: 'success',
        title: 'تم الحفظ',
        message: 'تم حفظ التغييرات بنجاح في قاعدة البيانات',
        duration: 4000
    });
}

function testValidationError() {
    glassNotify.show({
        type: 'error',
        title: 'خطأ في التحقق',
        message: `
            يرجى تصحيح الأخطاء التالية:<br>
            • حقل الاسم مطلوب<br>
            • البريد الإلكتروني غير صحيح<br>
            • كلمة المرور قصيرة جداً
        `
    });
}

function testLogoutConfirm() {
    glassNotify.confirm(
        'تسجيل الخروج',
        'هل تريد تسجيل الخروج من حسابك؟',
        function() {
            glassNotify.toastInfo('جارٍ تسجيل الخروج...');
            // window.location.href = '/logout';
        },
        {
            confirmText: 'تسجيل الخروج',
            cancelText: 'إلغاء',
            confirmType: 'primary'
        }
    );
}
</script>
@endpush

@endsection
