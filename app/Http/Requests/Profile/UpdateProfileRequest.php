<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'phone'            => ['nullable', 'string', 'max:20'],
            'avatar'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password'     => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'              => 'الاسم مطلوب',
            'email.required'             => 'البريد الإلكتروني مطلوب',
            'email.email'                => 'البريد الإلكتروني غير صحيح',
            'email.unique'               => 'البريد الإلكتروني مستخدم مسبقاً',
            'avatar.image'               => 'يجب أن تكون الصورة بصيغة jpg/png/webp',
            'avatar.max'                 => 'الصورة يجب أن لا تتجاوز 2MB',
            'current_password.required_with' => 'يرجى إدخال كلمة المرور الحالية لتغييرها',
            'new_password.min'           => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل',
            'new_password.confirmed'     => 'تأكيد كلمة المرور غير متطابق',
        ];
    }
}
