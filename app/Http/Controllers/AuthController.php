<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * صفحة تسجيل الدخول
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * معالجة تسجيل الدخول
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|max:255',
        ]);

        $credentials = $request->only('email', 'password');

        // التحقق من البيانات (بدون eager loading لتحسين الأداء)
        $user = User::where('email', $request->email)->first();

        // تحديد المعرف: (IP الطالب | بصمة البريد) — لا يمكن استهدافه ضد ضحية.
        // مهاجم يفشل بريد ضحية من IP الخاص به يقفل (attackerIp|victimEmailHash) فقط؛
        // الضحية من IP مختلف لها مفتاح مختلف فلا تتأثر. credential-stuffing من مصدر واحد لا يزال يُقفل بعد 4.
        $identifier = $request->ip() . '|' . hash('sha256', strtolower((string) $request->email));
        $cacheKey = 'login_attempts_' . $identifier;
        $lockoutKey = 'login_lockout_' . $identifier;

        // فحص الحظر مرة واحدة فقط
        $lockoutUntil = cache()->get($lockoutKey);

        if ($lockoutUntil && now()->timestamp < $lockoutUntil) {
            $remainingMinutes = ceil(($lockoutUntil - now()->timestamp) / 60);
            $remainingHours = floor($remainingMinutes / 60);
            $remainingMins = $remainingMinutes % 60;

            $timeMessage = $remainingHours > 0
                ? "{$remainingHours} ساعة و {$remainingMins} دقيقة"
                : "{$remainingMins} دقيقة";

            return back()->withErrors([
                'error' => "تم حظر محاولات تسجيل الدخول مؤقتاً. يرجى المحاولة بعد {$timeMessage}.",
            ])->onlyInput('email');
        }

        // التحقق من صحة بيانات الدخول
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // زيادة عدد المحاولات الفاشلة
            $attempts = cache()->get($cacheKey, 0) + 1;
            cache()->put($cacheKey, $attempts, now()->addHours(2));

            // تطبيق Exponential Backoff بعد المحاولة الرابعة
            if ($attempts >= 4) {
                $lockoutMinutes = 30 * ($attempts - 3);
                $lockoutUntil = now()->addMinutes($lockoutMinutes)->timestamp;
                cache()->put($lockoutKey, $lockoutUntil, now()->addMinutes($lockoutMinutes));

                $lockoutHours = floor($lockoutMinutes / 60);
                $lockoutMins = $lockoutMinutes % 60;

                $timeMessage = $lockoutHours > 0
                    ? "{$lockoutHours} ساعة" . ($lockoutMins > 0 ? " و {$lockoutMins} دقيقة" : '')
                    : "{$lockoutMins} دقيقة";

                return back()->withErrors([
                    'error' => "عدد كبير من المحاولات الخاطئة. تم حظر تسجيل الدخول لمدة {$timeMessage}.",
                ])->onlyInput('email');
            }

            // رسالة عامة موحّدة — لا تكشف وجود البريد ولا عدد المحاولات المتبقية (منع user enumeration)
            return back()->withErrors([
                'error' => 'بيانات الدخول غير صحيحة.',
            ])->onlyInput('email');
        }

        // التحقق من حالة الحساب
        if ($user->status === 'inactive') {
            return back()->withErrors([
                'error' => 'الحساب غير نشط حالياً. يرجى التواصل مع إدارة المنصة.',
            ])->onlyInput('email');
        }

        // مسح محاولات الفشل عند النجاح (batch operation)
        cache()->deleteMultiple([$cacheKey, $lockoutKey]);

        // إذا كان المستخدم لديه 2FA مفعل والميزة مفعّلة عالمياً (enable_2fa — كان مفتاحاً بلا أثر)
        if ($user->two_factor_enabled && setting('enable_2fa', true)) {
            // توليد كود آمن من 6 أرقام باستخدام random_int
            $code = random_int(100000, 999999);

            // حفظ الكود مع وقت انتهاء الصلاحية (10 دقائق)
            $user->two_factor_code = $code;
            $user->two_factor_expires_at = Carbon::now()->addMinutes(10);
            $user->save();

            // إرسال الكود عبر البريد الإلكتروني (في الخلفية - async)
            try {
                Mail::to($user->email)->queue(new TwoFactorCodeMail($code, $user->name));
            } catch (\Exception $e) {
                return back()->withErrors([
                    'email' => 'حدث خطأ في إرسال كود التحقق. يرجى المحاولة لاحقاً.',
                ])->withInput($request->except('password'));
            }

            // حفظ بيانات المستخدم في الجلسة مؤقتاً
            $request->session()->put('two_factor_user_id', $user->id);
            $request->session()->put('two_factor_remember', $request->has('remember'));

            return redirect()->route('two-factor.verify');
        }

        // تسجيل الدخول العادي
        $remember = $request->has('remember');
        Auth::login($user, $remember);

        // تجديد الجلسة للأمان (منع Session Fixation)
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * صفحة إدخال كود التحقق
     */
    public function showTwoFactorVerify(Request $request)
    {
        if (! session()->has('two_factor_user_id')) {
            return redirect()->route('login')->withErrors(['error' => 'انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى']);
        }

        // تجديد الجلسة لضمان عدم انتهاء صلاحيتها أثناء إدخال الكود
        $request->session()->regenerateToken();
        $request->session()->put('two_factor_last_activity', now()->timestamp);

        return view('auth.two-factor-verify');
    }

    /**
     * التحقق من الكود
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        // التحقق من وجود الجلسة وعدم انتهاء صلاحيتها
        $userId = session('two_factor_user_id');
        $lastActivity = session('two_factor_last_activity');

        if (! $userId) {
            return redirect()->route('login')->withErrors(['error' => 'انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى']);
        }

        // التحقق من أن الجلسة لم تنته (15 دقيقة)
        if ($lastActivity && (now()->timestamp - $lastActivity) > 900) {
            $request->session()->flush();

            return redirect()->route('login')->withErrors(['error' => 'انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى']);
        }

        $user = User::find($userId);

        if (! $user) {
            return redirect()->route('login')->withErrors(['error' => 'المستخدم غير موجود']);
        }

        // نظام Exponential Backoff - التحقق من المحاولات الفاشلة
        $cacheKey = 'two_factor_attempts_' . $user->id;
        $attempts = cache()->get($cacheKey, 0);

        // التحقق من وقت الحظر
        $lockoutKey = 'two_factor_lockout_' . $user->id;
        $lockoutUntil = cache()->get($lockoutKey);

        if ($lockoutUntil && now()->timestamp < $lockoutUntil) {
            $remainingMinutes = ceil(($lockoutUntil - now()->timestamp) / 60);
            $remainingHours = floor($remainingMinutes / 60);
            $remainingMins = $remainingMinutes % 60;

            $timeMessage = $remainingHours > 0
                ? "{$remainingHours} ساعة و {$remainingMins} دقيقة"
                : "{$remainingMins} دقيقة";

            return back()->withErrors(['code' => "عدد كبير من المحاولات الخاطئة. الرجاء المحاولة بعد {$timeMessage}."])->withInput();
        }

        // التحقق من الكود وانتهاء الصلاحية (مقارنة آمنة)
        if (! hash_equals((string) $user->two_factor_code, (string) $request->code)) {
            // زيادة عدد المحاولات الفاشلة
            $attempts++;
            cache()->put($cacheKey, $attempts, now()->addHour());

            // تطبيق Exponential Backoff بعد المحاولة الرابعة
            if ($attempts >= 4) {
                // المحاولة 4: 30 دقيقة
                // المحاولة 5: 60 دقيقة (ساعة)
                // المحاولة 6: 90 دقيقة (ساعة ونصف)
                // المحاولة 7: 120 دقيقة (ساعتين)
                // وهكذا...
                $lockoutMinutes = 30 * ($attempts - 3);
                $lockoutUntil = now()->addMinutes($lockoutMinutes)->timestamp;
                cache()->put($lockoutKey, $lockoutUntil, now()->addMinutes($lockoutMinutes));

                $lockoutHours = floor($lockoutMinutes / 60);
                $lockoutMins = $lockoutMinutes % 60;

                $timeMessage = $lockoutHours > 0
                    ? "{$lockoutHours} ساعة" . ($lockoutMins > 0 ? " و {$lockoutMins} دقيقة" : '')
                    : "{$lockoutMins} دقيقة";

                return back()->withErrors(['code' => "كود التحقق غير صحيح. تم حظر المحاولات لمدة {$timeMessage}."]);
            }

            $remaining = 3 - $attempts;

            return back()->withErrors(['code' => "كود التحقق غير صحيح. لديك {$remaining} محاولة متبقية."]);
        }

        if (Carbon::now()->greaterThan($user->two_factor_expires_at)) {
            return back()->withErrors(['code' => 'انتهت صلاحية كود التحقق. يرجى تسجيل الدخول مرة أخرى']);
        }

        // مسح الكود ومحاولات الفشل
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        cache()->forget($cacheKey);
        cache()->forget($lockoutKey);

        // تسجيل الدخول
        $remember = session('two_factor_remember', false);
        Auth::login($user, $remember);

        // تجديد الجلسة للأمان
        $request->session()->regenerate();

        // مسح بيانات الجلسة المؤقتة
        $request->session()->forget(['two_factor_user_id', 'two_factor_remember', 'two_factor_last_activity']);
        $request->session()->regenerate();
        $request->session()->save();

        return redirect()->route('dashboard');
    }

    /**
     * إعادة إرسال الكود
     */
    public function resendTwoFactorCode(Request $request)
    {
        $userId = session('two_factor_user_id');
        if (! $userId) {
            return redirect()->route('login')->withErrors(['error' => 'انتهت صلاحية الجلسة']);
        }

        $user = User::find($userId);

        if (! $user) {
            return redirect()->route('login')->withErrors(['error' => 'المستخدم غير موجود']);
        }

        // حد إضافي على عدد الإرسالات لكل مستخدم خلال 15 دقيقة (دفاع في العمق فوق throttle:5,1)
        $rateKey = "2fa_resend:user:{$user->id}";
        $attempts = (int) \Illuminate\Support\Facades\Cache::get($rateKey, 0);
        if ($attempts >= 5) {
            return back()->withErrors(['error' => 'تجاوزت الحد المسموح به لإعادة إرسال الكود. حاول بعد 15 دقيقة.']);
        }
        \Illuminate\Support\Facades\Cache::put($rateKey, $attempts + 1, now()->addMinutes(15));

        // توليد كود آمن جديد
        $code = random_int(100000, 999999);

        $user->two_factor_code = $code;
        $user->two_factor_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // إرسال الكود
        if (empty($user->email) || ! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors(['error' => 'البريد الإلكتروني غير صالح']);
        }

        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($code, $user->name));

            return back()->with('success', 'تم إرسال كود جديد إلى بريدك الإلكتروني');
        } catch (\Exception $e) {
            \Log::error('فشل إرسال 2FA code', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'حدث خطأ في إرسال الكود']);
        }
    }

    /**
     * تسجيل مستخدم جديد
     */
    public function register(Request $request)
    {
        // احترام مفتاح enable_registration (كان مُعرَّفاً بلا أثر) — الافتراضي مفعّل
        if (! setting('enable_registration', true)) {
            return back()->with('error', 'التسجيل مغلق حالياً. يرجى التواصل مع الإدارة.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:teacher,student,parent,school_admin',
            // اسم المدرسة مطلوب فقط لمدير المدرسة
            'school_name' => 'required_if:role,school_admin|nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً',
            'role.required' => 'نوع الحساب مطلوب',
            'role.in' => 'نوع الحساب غير صحيح',
            'school_name.required_if' => 'اسم المدرسة مطلوب لمدير المدرسة',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);

        // تحويل الدور للقيمة الصحيحة في قاعدة البيانات
        $role = $request->role;

        try {
            $user = DB::transaction(function () use ($request, $role) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'role' => $role,
                    'status' => 'inactive', // الحساب غير نشط حتى الموافقة عليه من الإدارة
                ]);

                // تعيين الدور باستخدام Spatie
                if ($request->role) {
                    $user->assignRole($request->role);
                }

                // مدير مدرسة: أنشئ المدرسة (غير نشطة) واربطها بالحساب — كلاهما بانتظار موافقة
                // الإدارة (لا تفعيل فوريّ = لا تصعيد صلاحيات). admin() = belongsTo(User, created_by).
                if ($role === 'school_admin') {
                    $school = \App\Models\School::create([
                        'name' => trim((string) $request->school_name),
                        'created_by' => $user->id,
                        'status' => 'inactive',
                    ]);

                    // school_id حقل حسّاس محروس عند التحديث (منع تصعيد الصلاحيات)؛ التسجيل سياق
                    // موثوق ننشئ فيه المدرسة للتوّ، فنستخدم saveQuietly لتجاوز حارس الحقول الحسّاسة.
                    $user->school_id = $school->id;
                    $user->saveQuietly();
                }

                return $user;
            });

            // إرسال إيميل تأكيد استلام الطلب
            try {
                Mail::to($user->email)->send(new \App\Mail\RegistrationPendingMail($user));
            } catch (\Exception $e) {
                Log::warning('Failed to send registration email: ' . $e->getMessage());
            }

            // إعادة التوجيه مع رسالة نجاح للـ popup
            return redirect()->route('register')->with('registration_success', true)->with('user_name', $user->name);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());

            return back()->withErrors(['error' => 'حدث خطأ أثناء إنشاء الحساب. يرجى المحاولة لاحقاً.'])->withInput();
        }
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * توجيه المستخدم للوحة التحكم المناسبة
     */
    public function dashboard()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // التحقق من كلمة المرور المؤقتة
        if ($user->password_change_required) {
            return redirect()->route('password.change');
        }

        // الحصول على الدور النشط (يدعم تبديل الأدوار)
        $activeRole = session('active_role_' . $user->id, $user->active_role ?? $user->role);

        // دفاع: لو الدور النشط لا يملكه المستخدم فعلاً (قيمة عالقة/فاسدة في العمود أو الجلسة)
        // نعود للدور الأساسيّ بدل توجيهه للوحة دور لا يخصّه (تنكسر أو تُظهر خطأ).
        if (! in_array($activeRole, $user->getAllRoles(), true)) {
            $activeRole = $user->role;
        }

        // شبكة أمان: دور ثانويّ مملوك لكنه معطوب (مثلاً مرتبط بمدرسة بلا مدرسة) → نوضّح
        // السبب ونعرض خيار العودة للأساسيّ بدل توجيهه للوحة تنكسر. (لا نحجب الدور الأساسيّ.)
        if ($activeRole !== $user->role) {
            $blockReason = $user->roleBlockReason($activeRole);
            if ($blockReason !== null) {
                return view('auth.role-unavailable', [
                    'roleName' => $user->getRoleNameAr($activeRole),
                    'reason' => $blockReason,
                    'primaryRoleName' => $user->getRoleNameAr($user->role),
                ]);
            }
        }

        switch ($activeRole) {
            case 'super_admin':
                return redirect()->route('admin.dashboard');
            case 'school_admin':
                return redirect()->route('school-admin.dashboard');
            case 'teacher':
                return redirect()->route('teacher.dashboard');
            case 'student':
                return redirect()->route('student.dashboard');
            case 'parent':
                return redirect()->route('parent.dashboard');
            case 'technical_support':
                return redirect()->route('support.dashboard');
            default:
                Auth::logout();

                return redirect()->route('login')->withErrors(['error' => 'نوع الحساب غير صحيح']);
        }
    }

    /**
     * عرض صفحة تغيير كلمة المرور
     */
    public function showPasswordChange()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.change-password');
    }

    /**
     * تحديث كلمة المرور
     */
    public function updatePassword(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'current_password.required' => 'يرجى إدخال كلمة المرور الحالية',
            'password.required' => 'يرجى إدخال كلمة المرور الجديدة',
            'password.min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل',
            'password.confirmed' => 'كلمة المرور غير متطابقة',
            'password.different' => 'يجب أن تكون كلمة المرور الجديدة مختلفة عن الحالية',
        ]);

        $user = Auth::user();

        // التحقق من كلمة المرور الحالية
        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة']);
        }

        // تحديث كلمة المرور + remember_token جديد لإبطال أي session سابق
        $user->password = Hash::make($request->password);
        $user->password_change_required = false;
        $user->setRememberToken(\Illuminate\Support\Str::random(60));
        // password_change_required حقل محروس في User::booted (يتطلّب super_admin/school_admin).
        // هذه كتابة self-service مُخوَّلة (المستخدم يُصفّي علَمَ نفسه بعد إثبات كلمة المرور
        // الحالية) — نتجاوز الحارس بـsaveQuietly، وإلا صار 403 على المستخدم الجديد المجبَر.
        $user->saveQuietly();

        // تجديد session id لمنع session hijacking عند تغيير كلمة المرور
        $request->session()->regenerate();

        // إنهاء جلسات أخرى للمستخدم نفسه (لو driver=database)
        try {
            \Illuminate\Support\Facades\Auth::logoutOtherDevices($request->password);
        } catch (\Throwable $e) {
            // ليس فادحًا — السجل يكفي
            \Log::warning('logoutOtherDevices failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('dashboard')->with('success', 'تم تغيير كلمة المرور بنجاح! تم تسجيل الخروج من باقي الأجهزة.');
    }

    /**
     * عرض صفحة نسيت كلمة المرور
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * إرسال رابط إعادة تعيين كلمة المرور
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'يرجى إدخال البريد الإلكتروني',
            'email.email' => 'البريد الإلكتروني غير صحيح',
        ]);

        // رسالة محايدة دائماً — لا نكشف ما إذا كان البريد مسجلاً (منع user enumeration)
        $neutral = 'إن وُجد البريد، أُرسل رابط إعادة التعيين';

        // نولّد الـ token دائماً قبل فرع الوجود حتى يكون كلفة الـ hash متماثلة
        // في كلتا الحالتين (وجود/عدم وجود البريد) — منع timing oracle.
        $token = bin2hex(random_bytes(32));
        $hashedToken = Hash::make($token);

        // لا نرسل/ننشئ token إلا إذا كان المستخدم موجوداً فعلاً، لكن الرد يبقى محايداً في كل الحالات
        if (User::where('email', $request->email)->exists()) {
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                ['token' => $hashedToken, 'created_at' => now()],
            );
            try {
                Mail::to($request->email)->send(new \App\Mail\ResetPasswordMail($token, $request->email));
            } catch (\Exception $e) {
                Log::error('فشل إرسال رابط إعادة التعيين: ' . $e->getMessage());
            }
        }

        return back()->with('status', $neutral);
    }

    /**
     * عرض صفحة إعادة تعيين كلمة المرور
     */
    public function showResetPassword($token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    /**
     * إعادة تعيين كلمة المرور
     */
    public function resetPassword(Request $request)
    {
        // لا نستخدم exists:users,email — كان يكشف ما إذا كان البريد مسجلاً (user enumeration).
        // غياب سجل token صالح يعطي نفس الرسالة العامة سواء وُجد البريد أم لا.
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'يرجى إدخال البريد الإلكتروني',
            'password.required' => 'يرجى إدخال كلمة المرور الجديدة',
            'password.min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل',
            'password.confirmed' => 'كلمة المرور غير متطابقة',
        ]);

        // التحقق من صحة الـ token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $resetRecord) {
            return back()->withErrors(['email' => 'رابط إعادة التعيين غير صحيح']);
        }

        // التحقق من أن الـ token لم ينته (60 دقيقة)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withErrors(['email' => 'انتهت صلاحية رابط إعادة التعيين. يرجى طلب رابط جديد']);
        }

        // التحقق من الـ token
        if (! Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'رابط إعادة التعيين غير صحيح']);
        }

        // تحديث كلمة المرور + remember_token جديد لإبطال الـ remember-me القديم
        $user = User::where('email', $request->email)->first();
        if (! $user) {
            // سجل token بلا مستخدم — رسالة عامة موحّدة (لا تكشف الوجود)
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withErrors(['email' => 'رابط إعادة التعيين غير صحيح']);
        }
        $user->password = Hash::make($request->password);
        $user->password_change_required = false;
        $user->setRememberToken(\Illuminate\Support\Str::random(60));
        // إعادة تعيين مُثبَتة بـtoken (زائر بلا جلسة) — تُصفّي علَم password_change_required
        // المحروس؛ نتجاوز حارس User::booted بـsaveQuietly (الحارس يرفض الزائر بـ403).
        $user->saveQuietly();

        // حذف الـ token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // إنهاء كل الجلسات النشطة للمستخدم — لا نسمح بإعادة استخدام جلسة محتمل سرقتها
        try {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        } catch (\Throwable $e) {
            \Log::warning('Failed to clear sessions on password reset', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('login')->with('success', 'تم إعادة تعيين كلمة المرور بنجاح! يرجى تسجيل الدخول بكلمة المرور الجديدة');
    }
}
