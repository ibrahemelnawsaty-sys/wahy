<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Store a newly created contact message.
     */
    public function store(Request $request)
    {
        // Honeypot — إن مُلئ الحقل المخفي فهذا bot (نجاح كاذب صامت حتّى لا يعرف البوت أنّه كُشِف)
        if ($request->filled('website')) {
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رسالتك بنجاح',
            ]);
        }

        // فخّ زمنيّ: النموذج البشريّ يُملأ في ثوانٍ؛ البوت يُرسل فوراً. الطابع مُشفَّر فلا يُزوَّر.
        // متساهل مع غيابه (صفحة مُخزَّنة قديمة) — لا يُعاقَب إلّا التسليم السريع المؤكَّد.
        $openedAt = null;
        try {
            $openedAt = (int) decrypt((string) $request->input('form_ts', ''));
        } catch (\Throwable $e) {
            $openedAt = null;
        }
        if ($openedAt && (now()->timestamp - $openedAt) < 3) {
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رسالتك بنجاح',
            ]);
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

        // **حُذف بريد التأكيد للمُرسِل**: كان يُرسَل إلى عنوانٍ يتحكّم به المهاجم وغير مُتحقَّق
        // (Mail::to($cleanData['email'])) — ناقلُ backscatter/قصف بريد وحرقِ صندوق O365 (حظرُه يوقف
        // كلّ بريد المنصّة بما فيه رمز 2FA واستعادة كلمة المرور). لا قيمة أمنيّة له: الأدمن يُشعَر
        // أعلاه، والرسالة محفوظة (مصدر الحقيقة)، ورسالة الشكر تظهر للزائر في استجابة JSON أدناه.

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.',
        ], 200);
    }
}
