<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * حاجز مكافحة البوتات المشترك بين إرسال الرمز وحفظ الرسالة:
     *   1) Honeypot — حقل مخفيّ إن مُلئ فهو bot (نجاح كاذب صامت حتّى لا يعرف أنّه كُشِف).
     *   2) مفتاح إيقافٍ فوريّ (setting) يُمكّن المالك من تعطيل النموذج كلّياً لحظة تفاقم الهجوم.
     *   3) 🔒 «إثبات تنفيذ JavaScript» (جذريّ ريثما يُربَط Cloudflare/Turnstile): الرمز يُوضَع في
     *      data-attribute لا حقل نموذج، وJS ينقله لحقل cc_token عند التحميل. البوت يجلب HTML ويُعيد
     *      إرسال الحقول القياسيّة فقط دون تنفيذ JS — فيبقى cc_token فارغاً فيُرفَض بنجاحٍ كاذب صامت.
     *
     * يُرجِع استجابةً لإيقاف الطلب فوراً، أو null للمتابعة.
     */
    private function guardBots(Request $request, string $fakeSuccessMessage): ?\Illuminate\Http\JsonResponse
    {
        if ($request->filled('website')) {
            return response()->json(['success' => true, 'message' => $fakeSuccessMessage]);
        }

        if (! setting('contact_form_enabled', true)) {
            return response()->json([
                'success' => false,
                'message' => 'نموذج التواصل معطّل مؤقّتاً. راسلنا مباشرةً على ' . setting('contact_email', 'info@atheel-makkah.com'),
            ], 503);
        }

        $ccOpenedAt = null;
        try {
            $ccOpenedAt = (int) decrypt((string) $request->input('cc_token', ''));
        } catch (\Throwable $e) {
            $ccOpenedAt = null;
        }
        $ccAge = $ccOpenedAt ? (now()->timestamp - $ccOpenedAt) : null;
        if (! $ccOpenedAt || $ccAge < 1 || $ccAge > 7200) {
            return response()->json(['success' => true, 'message' => $fakeSuccessMessage]);
        }

        return null;
    }

    /**
     * الخطوة الأولى: إرسال رمز تحقّق (OTP) إلى البريد الذي أدخله الزائر ليُثبت ملكيّته قبل قبول
     * الرسالة. الجذر الحاسم للحماية: البوت يقصف بعناوين عشوائيّة/ضحايا لا يملك صناديقها، فلا يستطيع
     * قراءة الرمز أبداً — ومن ثمّ لا يعبر store(). محميّ بذاته من قصف الرموز (guardBots + سقف لكلّ
     * بريد + دائرة قطعٍ يوميّة عالميّة + محدِّد مسار). البريد مُصفَّف (العامل CLI وحده يصل SMTP).
     */
    public function sendCode(Request $request)
    {
        if ($resp = $this->guardBots($request, 'إن كان البريد صحيحاً فستصلك رسالة برمز التحقّق.')) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى إدخال بريد إلكتروني صحيح',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim((string) $request->input('email')));

        // سقفٌ لكلّ بريد: 5 طلبات رمز/10د كحدّ أقصى — يمنع قصف صندوق ضحيّة برموز متكرّرة.
        $reqKey = 'contact_code_req:' . sha1($email);
        $reqCount = (int) \Cache::get($reqKey, 0);
        if ($reqCount >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'لقد طلبتَ الرمز عدّة مرّات. انتظر قليلاً ثمّ حاول مجدّداً.',
            ], 429);
        }

        // دائرة قطعٍ يوميّة عالميّة لبريد الرموز — تحمي صندوق O365 من الحرق مهما كثُر الطلب (شبكة
        // أمان فوق محدِّد المسار ذي المفتاح الثابت). فشل الإرسال لا يُكشَف للزائر (يمنع الاستنزاف).
        $capKey = 'contact_code_mail:' . now()->format('Y-m-d');
        $sentToday = (int) \Cache::get($capKey, 0);
        if ($sentToday >= (int) setting('contact_code_mail_daily_cap', 300)) {
            return response()->json([
                'success' => false,
                'message' => 'تعذّر إرسال الرمز حالياً بسبب الضغط. حاول لاحقاً أو راسلنا مباشرةً على ' . setting('contact_email', 'info@atheel-makkah.com'),
            ], 429);
        }

        // توليد رمز 6-أرقام، وتخزينه **مُجزّأً** (لا نصّاً صريحاً) بمفتاح البريد، صالحاً 10د، يُستهلَك مرّة.
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        \Cache::put('contact_code:' . sha1($email), hash('sha256', $code), now()->addMinutes(10));
        \Cache::put($reqKey, $reqCount + 1, now()->addMinutes(10));
        \Cache::put($capKey, $sentToday + 1, now()->endOfDay());

        try {
            Mail::to($email)->queue(new \App\Mail\ContactVerificationCodeMail($code));
        } catch (\Throwable $e) {
            \Log::warning('Contact verification code mail failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'need_code' => true,
            'message' => 'أرسلنا رمز تحقّق إلى بريدك (قد يستغرق وصوله دقيقة). أدخِله أدناه لإتمام الإرسال.',
        ]);
    }

    /**
     * Store a newly created contact message.
     */
    public function store(Request $request)
    {
        if ($resp = $this->guardBots($request, 'تم إرسال رسالتك بنجاح')) {
            return $resp;
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'user_type' => 'required|in:school,teacher,parent,student,institution',
            'message' => 'required|string|max:2000',
        ], [
            'full_name.required' => 'الاسم الكامل مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'user_type.required' => 'نوع المستخدم مطلوب',
            'message.required' => 'الرسالة مطلوبة',
            'message.max' => 'الرسالة طويلة جداً',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى التحقق من البيانات المدخلة',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 🔑 إثبات ملكيّة البريد: يجب أن يطابق الرمز المُرسَل إلى هذا البريد (يُستهلَك مرّة واحدة).
        // بلا رمزٍ صحيح لا تُخزَّن الرسالة — هذا هو الحاجز الحاسم: المهاجم لا يملك صندوق العنوان الذي
        // يقصف به فلا يقرأ الرمز أبداً. تُطابَق البصمة المُجزّأة عبر hash_equals (زمن ثابت).
        $emailNorm = strtolower(trim((string) $request->input('email')));
        $emailKey = 'contact_code:' . sha1($emailNorm);
        $triesKey = 'contact_code_tries:' . sha1($emailNorm);
        $expected = \Cache::get($emailKey);
        $provided = (string) $request->input('code', '');
        if (! $expected || ! hash_equals((string) $expected, hash('sha256', $provided))) {
            // عدّاد محاولات: بعد 5 محاولاتٍ خاطئة يُبطَل الرمز (يسدّ أيّ تخمينٍ تدريجيّ لفضاء 10⁶).
            if ($expected) {
                $tries = (int) \Cache::get($triesKey, 0) + 1;
                \Cache::put($triesKey, $tries, now()->addMinutes(10));
                if ($tries >= 5) {
                    \Cache::forget($emailKey);
                    \Cache::forget($triesKey);
                }
            }

            return response()->json([
                'success' => false,
                'need_code' => true,
                'message' => 'رمز التحقّق غير صحيح أو انتهت صلاحيته. اطلب رمزاً جديداً وأعد المحاولة.',
            ], 422);
        }
        \Cache::forget($emailKey);   // استهلاك مرّة واحدة
        \Cache::forget($triesKey);

        // XSS Protection - Strip tags
        $cleanData = [
            'full_name' => strip_tags($request->full_name),
            'email' => strip_tags($request->email),
            'user_type' => $request->user_type,
            'message' => strip_tags($request->message),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        // كشف تكرار المحتوى: نفس (البريد + الرسالة) خلال 10 دقائق = فيضان آليّ → نجاح كاذب صامت،
        // لا صفٌّ جديد ولا بريد (يوقف تكرار نفس الرسالة دون إزعاج المستخدم الشرعيّ).
        $isDuplicate = ContactMessage::where('email', $cleanData['email'])
            ->where('message', $cleanData['message'])
            ->where('created_at', '>=', now()->subMinutes(10))
            ->exists();
        if ($isDuplicate) {
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رسالتك بنجاح',
            ], 200);
        }

        // الحفظ في قاعدة البيانات هو مصدر الحقيقة: تُحفَظ الرسالة أوّلاً، وتبقى مستردَّةً من
        // لوحة الإدارة حتى لو تعطّل البريد. فشل الحفظ وحده يُرجِع خطأً للزائر.
        try {
            ContactMessage::create($cleanData);
        } catch (\Exception $e) {
            \Log::error('Contact form DB error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.',
            ], 500);
        }

        // الإشعارات البريديّة «أفضل جهد»: تعطُّل SMTP يجب ألّا يُضيّع الرسالة أو يُظهر خطأً للزائر.
        $adminEmail = setting('contact_email', 'info@atheel-makkah.com'); // كان مرمَّزاً خطأً لنطاق مهجور

        // إشعار الأدمن «أفضل جهد» — خلف **دائرة قطعٍ يوميّة** تحمي صندوق O365 من الحرق مهما كثُر
        // الوارد (شبكة أمان فوق محدِّد المسار). وجهته ثابتة (setting) فلا تضخيم لعنوان مهاجم.
        // مُصفَّف لا متزامن: عمليّة الويب محجوبة عن المنفذ 587، والعامل (CLI) وحده يوصِل SMTP.
        $mailCap = (int) setting('contact_admin_mail_daily_cap', 200);
        $mailKey = 'contact_admin_mail:' . now()->format('Y-m-d');
        $sentToday = (int) \Cache::get($mailKey, 0);
        if ($sentToday < $mailCap) {
            \Cache::put($mailKey, $sentToday + 1, now()->endOfDay());
            try {
                Mail::to($adminEmail)->queue(new \App\Mail\ContactMessageReceivedMail($cleanData));
            } catch (\Throwable $e) {
                \Log::warning('Contact admin-notify failed (message saved): ' . $e->getMessage());
                \App\Models\EmailLog::recordFailure(
                    $adminEmail,
                    $e->getMessage(),
                    'رسالة تواصل جديدة من ' . $cleanData['full_name'],
                );
            }
        }

        // تأكيدٌ للمُرسِل بأنّ رسالته وصلت: **أُعيد بأمان**. سابقاً كان backscatter (يُرسَل لعنوانٍ
        // غير مُتحقَّق يتحكّم به المهاجم). الآن store() لا يُبلَغ إلّا بعد **إثبات ملكيّة البريد برمز
        // OTP** — فالعنوان مِلكُ صاحبه وقد بدأ الطلب بنفسه، فلا تضخيم ولا قصف طرفٍ ثالث. خلف دائرة
        // قطعٍ يوميّة تحمي صندوق O365، ومُصفَّف، وأفضل جهد (فشله لا يمسّ حفظ الرسالة).
        $confirmCap = (int) setting('contact_confirm_mail_daily_cap', 200);
        $confirmKey = 'contact_confirm_mail:' . now()->format('Y-m-d');
        $confirmSent = (int) \Cache::get($confirmKey, 0);
        if ($confirmSent < $confirmCap) {
            \Cache::put($confirmKey, $confirmSent + 1, now()->endOfDay());
            try {
                Mail::to($cleanData['email'])->queue(new \App\Mail\ContactConfirmationMail($cleanData));
            } catch (\Throwable $e) {
                \Log::warning('Contact confirmation mail failed (message saved): ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.',
        ], 200);
    }
}
