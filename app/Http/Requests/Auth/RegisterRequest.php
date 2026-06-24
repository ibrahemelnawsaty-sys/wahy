<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Registration مفتوح
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'                 => ['nullable', 'string', 'max:20'],

            // ⚠️  أمن: لا نسمح للزائر بطلب SuperAdmin أو SchoolAdmin من النموذج العام.
            // الأدمن يُنشأ يدوياً عبر admin panel أو seeder فقط.
            'role'                  => ['required', Rule::in([
                UserRole::Teacher->value,
                UserRole::Student->value,
                UserRole::Parent->value,
            ])],

            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'الاسم مطلوب',
            'email.required'     => 'البريد الإلكتروني مطلوب',
            'email.email'        => 'البريد الإلكتروني غير صحيح',
            'email.unique'       => 'البريد الإلكتروني مستخدم مسبقاً',
            'role.required'      => 'نوع الحساب مطلوب',
            'role.in'            => 'نوع الحساب غير صحيح',
            'password.required'  => 'كلمة المرور مطلوبة',
            'password.min'       => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ];
    }

    /**
     * البيانات الجاهزة للحفظ — مع force على status='inactive' (لا يوافق على نفسه).
     */
    public function safeUserAttributes(): array
    {
        return [
            'name'   => $this->input('name'),
            'email'  => $this->input('email'),
            'phone'  => $this->input('phone'),
            'role'   => $this->input('role'),
            'status' => 'inactive', // الإدارة تُفعّل الحساب يدوياً
        ];
    }
}
