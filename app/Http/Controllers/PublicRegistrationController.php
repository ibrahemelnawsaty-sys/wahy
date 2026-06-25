<?php

namespace App\Http\Controllers;

use App\Mail\NewRegistrationNotificationMail;
use App\Mail\RegistrationSubmittedMail;
use App\Models\RegistrationRequest;
use App\Models\School;
use App\Models\User;
use App\Notifications\NewRegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PublicRegistrationController extends Controller
{
    /**
     * عرض صفحة تسجيل المعلم
     */
    public function showTeacherForm($token)
    {
        $school = School::where('teacher_token', $token)
            ->where('enable_teacher_registration', true)
            ->firstOrFail();

        return view('public.register.teacher', compact('school', 'token'));
    }

    /**
     * تسجيل معلم جديد
     */
    public function registerTeacher(Request $request, $token)
    {
        $school = School::where('teacher_token', $token)
            ->where('enable_teacher_registration', true)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:registration_requests,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
            'qualifications' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'specialization' => 'nullable|string|max:255',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique' => 'هذا البريد الإلكتروني مسجل مسبقاً',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);

        $registrationRequest = RegistrationRequest::create([
            'school_id' => $school->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt($validated['password']),
            'role' => 'teacher',
            'status' => 'pending',
            'data' => json_encode([
                'qualifications' => $validated['qualifications'] ?? null,
                'experience_years' => $validated['experience_years'] ?? null,
                'specialization' => $validated['specialization'] ?? null,
            ]),
        ]);

        // إرسال إيميل للمتقدم
        try {
            Mail::to($validated['email'])->send(new RegistrationSubmittedMail($registrationRequest));
        } catch (\Exception $e) {
            \Log::error('فشل إرسال إيميل تسجيل المعلم: ' . $e->getMessage());
        }

        // إرسال إشعار وإيميل لمدير المدرسة
        $schoolAdmin = User::where('school_id', $school->id)
            ->where('role', 'school_admin')
            ->first();

        if ($schoolAdmin) {
            $schoolAdmin->notify(new NewRegistrationNotification($registrationRequest));
            try {
                if ($schoolAdmin->email) {
                    Mail::to($schoolAdmin->email)->send(new NewRegistrationNotificationMail($registrationRequest));
                }
            } catch (\Exception $e) {
                \Log::error('فشل إرسال إيميل إشعار مدير المدرسة: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'تم إرسال طلب التسجيل بنجاح! سيتم مراجعته من قبل إدارة المدرسة.');
    }

    /**
     * عرض صفحة تسجيل الطالب
     */
    public function showStudentForm($token)
    {
        $school = School::where('student_token', $token)
            ->where('enable_student_registration', true)
            ->firstOrFail();

        return view('public.register.student', compact('school', 'token'));
    }

    /**
     * تسجيل طالب جديد
     */
    public function registerStudent(Request $request, $token)
    {
        $school = School::where('student_token', $token)
            ->where('enable_student_registration', true)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:registration_requests,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
            'birth_date' => 'nullable|date|before:today',
            'grade_level' => 'nullable|string|max:50',
            'parent_name' => 'nullable|string|max:255',
            'parent_email' => 'nullable|email',
            'parent_phone' => 'nullable|string|max:20',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique' => 'هذا البريد الإلكتروني مسجل مسبقاً',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'birth_date.date' => 'تاريخ الميلاد غير صحيح',
            'birth_date.before' => 'تاريخ الميلاد يجب أن يكون قبل اليوم',
            'parent_email.email' => 'بريد ولي الأمر غير صحيح',
        ]);

        $registrationRequest = RegistrationRequest::create([
            'school_id' => $school->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt($validated['password']),
            'role' => 'student',
            'status' => 'pending',
            'data' => json_encode([
                'birth_date' => $validated['birth_date'] ?? null,
                'grade_level' => $validated['grade_level'] ?? null,
                'parent_name' => $validated['parent_name'] ?? null,
                'parent_email' => $validated['parent_email'] ?? null,
                'parent_phone' => $validated['parent_phone'] ?? null,
            ]),
        ]);

        // إرسال إيميل للطالب — مع التحقق المزدوج من صيغة الإيميل
        if (! empty($validated['email']) && filter_var($validated['email'], FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($validated['email'])->send(new RegistrationSubmittedMail($registrationRequest));
            } catch (\Exception $e) {
                \Log::error('فشل إرسال إيميل تسجيل الطالب', ['error' => $e->getMessage()]);
            }
        }

        // إرسال إيميل لولي الأمر إذا كان موجود
        if (! empty($validated['parent_email']) && filter_var($validated['parent_email'], FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($validated['parent_email'])->send(new RegistrationSubmittedMail($registrationRequest));
            } catch (\Exception $e) {
                \Log::error('فشل إرسال إيميل ولي الأمر', ['error' => $e->getMessage()]);
            }
        }

        // إرسال إشعار وإيميل لمدير المدرسة
        $schoolAdmin = User::where('school_id', $school->id)
            ->where('role', 'school_admin')
            ->first();

        if ($schoolAdmin) {
            $schoolAdmin->notify(new NewRegistrationNotification($registrationRequest));
            try {
                if (! empty($schoolAdmin->email) && filter_var($schoolAdmin->email, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($schoolAdmin->email)->send(new NewRegistrationNotificationMail($registrationRequest));
                }
            } catch (\Exception $e) {
                \Log::error('فشل إرسال إيميل إشعار مدير المدرسة', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->back()->with('success', 'تم إرسال طلب التسجيل بنجاح! سيتم مراجعته من قبل إدارة المدرسة.');
    }

    /**
     * عرض صفحة تسجيل ولي الأمر
     */
    public function showParentForm($token)
    {
        $school = School::where('parent_token', $token)
            ->where('enable_parent_registration', true)
            ->firstOrFail();

        return view('public.register.parent', compact('school', 'token'));
    }

    /**
     * تسجيل ولي أمر جديد
     */
    public function registerParent(Request $request, $token)
    {
        $school = School::where('parent_token', $token)
            ->where('enable_parent_registration', true)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:registration_requests,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|min:8|confirmed',
            'relationship' => 'nullable|string|max:50',
            'children_names' => 'nullable|string',
            'address' => 'nullable|string',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique' => 'هذا البريد الإلكتروني مسجل مسبقاً',
            'phone.required' => 'رقم الهاتف مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);

        $registrationRequest = RegistrationRequest::create([
            'school_id' => $school->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
            'role' => 'parent',
            'status' => 'pending',
            'data' => json_encode([
                'relationship' => $validated['relationship'] ?? 'parent',
                'children_names' => $validated['children_names'] ?? null,
                'address' => $validated['address'] ?? null,
            ]),
        ]);

        // إرسال إيميل لولي الأمر
        try {
            Mail::to($validated['email'])->send(new RegistrationSubmittedMail($registrationRequest));
        } catch (\Exception $e) {
            \Log::error('فشل إرسال إيميل تسجيل ولي الأمر: ' . $e->getMessage());
        }

        // إرسال إشعار وإيميل لمدير المدرسة
        $schoolAdmin = User::where('school_id', $school->id)
            ->where('role', 'school_admin')
            ->first();

        if ($schoolAdmin) {
            $schoolAdmin->notify(new NewRegistrationNotification($registrationRequest));
            try {
                if ($schoolAdmin->email) {
                    Mail::to($schoolAdmin->email)->send(new NewRegistrationNotificationMail($registrationRequest));
                }
            } catch (\Exception $e) {
                \Log::error('فشل إرسال إيميل إشعار مدير المدرسة: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'تم إرسال طلب التسجيل بنجاح! سيتم مراجعته من قبل إدارة المدرسة.');
    }
}
