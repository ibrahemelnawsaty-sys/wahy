<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmitActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isStudent();
    }

    public function rules(): array
    {
        return [
            // إجابة المهمة — قد تكون نص حر، JSON encoded answers، أو ملف
            'answer' => ['nullable', 'string', 'max:65535'],

            // الإجابات لأسئلة الاختيار (للـ quizzes)
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable'],

            // ملف مرفق (إن طُلب)
            'file' => ['nullable', 'file', 'max:10240', 'mimes:jpeg,png,jpg,webp,pdf,doc,docx,mp4,mp3'],

            // وقت بدء المحاولة (للحساب timer)
            'started_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'حجم الملف يجب أن لا يتجاوز 10MB',
            'file.mimes' => 'نوع الملف غير مسموح به',
            'answer.max' => 'الإجابة طويلة جداً',
        ];
    }
}
