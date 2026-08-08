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
        // Honeypot — إن مُلئ الحقل المخفي فهذا bot
        if ($request->filled('website')) {
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
        $adminEmail = setting('contact_email', 'info@qiyamm.sa'); // كان مرمَّزاً خطأً لنطاق مهجور
        $siteName = setting('site_name', 'أثيل مكة');

        try {
            Mail::send('emails.contact', ['data' => $cleanData], function ($message) use ($cleanData, $adminEmail) {
                $message->to($adminEmail)
                    ->subject('رسالة تواصل جديدة من ' . $cleanData['full_name']);
            });
        } catch (\Exception $e) {
            \Log::warning('Contact admin-notify failed (message saved): ' . $e->getMessage());
        }

        try {
            Mail::send('emails.contact-confirmation', ['data' => $cleanData], function ($message) use ($cleanData, $siteName) {
                $message->to($cleanData['email'])
                    ->subject('تم استلام رسالتك - ' . $siteName);
            });
        } catch (\Exception $e) {
            \Log::warning('Contact confirmation failed (message saved): ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.',
        ], 200);
    }
}
