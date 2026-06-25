<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Login مفتوح لكل الزوار
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور قصيرة جداً',
        ];
    }

    /**
     * مفتاح الـ throttle (لـ RateLimiter::for('login') المعرّف في AppServiceProvider).
     */
    public function throttleKey(): string
    {
        return strtolower((string) $this->input('email')) . '|' . $this->ip();
    }
}
