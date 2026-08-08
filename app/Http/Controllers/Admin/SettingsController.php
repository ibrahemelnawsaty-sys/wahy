<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'site_name' => setting('site_name', 'منصة قيمّ'),
            'site_description' => setting('site_description', 'منصة تعليمية رائدة لبناء القيم الإنسانية'),
            'contact_email' => setting('contact_email', 'info@qiyamm.sa'),
            'contact_phone' => setting('contact_phone', '0112345678'),
            'facebook_url' => setting('facebook_url', ''),
            'twitter_url' => setting('twitter_url', ''),
            'instagram_url' => setting('instagram_url', ''),
            'linkedin_url' => setting('linkedin_url', ''),
            'footer_text' => setting('footer_text', '© ' . date('Y') . ' منصة قيمّ. جميع الحقوق محفوظة'),
            'maintenance_mode' => setting('maintenance_mode', false),
            'maintenance_message' => setting('maintenance_message', 'نعتذر عن الإزعاج. نقوم حالياً بإجراء بعض التحسينات والصيانة لتقديم تجربة أفضل لك.'),
            // ظهور أقسام الصفحة الرئيسية (الدفعة 0.a) — إخفاء/إعادة بلا نشر كود
            'show_hero_stats' => setting('show_hero_stats', false),
            'show_coop_benefits' => setting('show_coop_benefits', true),
            'show_partners' => setting('show_partners', false),
            // الفيديو والتواصل السريع (الدفعة 0.c)
            'hero_video_enabled' => setting('hero_video_enabled', false),
            'hero_video_url' => setting('hero_video_url', 'videos/hero-main.mp4'),
            'hero_video_poster' => setting('hero_video_poster', ''),
            'whatsapp_number' => setting('whatsapp_number', ''),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'facebook_url' => 'nullable|url|max:500',
            'twitter_url' => 'nullable|url|max:500',
            'instagram_url' => 'nullable|url|max:500',
            'linkedin_url' => 'nullable|url|max:500',
            'footer_text' => 'nullable|string|max:500',
            'hero_video_url' => 'nullable|string|max:500',
            'hero_video_poster' => 'nullable|string|max:500',
            'whatsapp_number' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]*$/'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $stringSettings = [
                'site_name' => $request->site_name,
                'site_description' => $request->site_description,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'facebook_url' => $request->facebook_url,
                'twitter_url' => $request->twitter_url,
                'instagram_url' => $request->instagram_url,
                'linkedin_url' => $request->linkedin_url,
                'footer_text' => $request->footer_text,
                'maintenance_message' => $request->maintenance_message,
                'hero_video_url' => $request->hero_video_url,
                'hero_video_poster' => $request->hero_video_poster,
                'whatsapp_number' => $request->whatsapp_number,
            ];

            // كل الأعلام المنطقيّة (checkbox): الغائب = 0. يجب أن يظهر كلٌّ منها في النموذج
            // وإلّا صُفِّر عند الحفظ. (الدفعة 0.a رؤية الأقسام + 0.c تفعيل الفيديو + الصيانة)
            $booleanSettings = [
                'maintenance_mode', 'show_hero_stats', 'show_coop_benefits', 'show_partners', 'hero_video_enabled',
            ];

            foreach ($stringSettings as $key => $value) {
                Setting::set($key, $value ?? '', 'string', $this->getSettingDescription($key));
            }
            foreach ($booleanSettings as $key) {
                Setting::set($key, $request->has($key) ? 1 : 0, 'boolean', $this->getSettingDescription($key));
            }

            // مسح الكاش
            Setting::clearCache();

            return redirect()->route('admin.settings')
                ->with('success', 'تم حفظ الإعدادات العامة بنجاح! ✅');

        } catch (\Exception $e) {
            Log::error('Error updating settings: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حفظ الإعدادات!')
                ->withInput();
        }
    }

    /**
     * الحصول على وصف الإعداد
     */
    private function getSettingDescription($key)
    {
        $descriptions = [
            'site_name' => 'اسم الموقع',
            'site_description' => 'وصف الموقع',
            'contact_email' => 'البريد الإلكتروني',
            'contact_phone' => 'رقم الهاتف',
            'facebook_url' => 'رابط فيسبوك',
            'twitter_url' => 'رابط تويتر',
            'instagram_url' => 'رابط إنستغرام',
            'linkedin_url' => 'رابط لينكد إن',
            'footer_text' => 'نص الفوتر',
            'maintenance_mode' => 'وضع الصيانة',
            'maintenance_message' => 'رسالة الصيانة',
            'show_hero_stats' => 'إظهار إحصائيات الصفحة الرئيسية',
            'show_coop_benefits' => 'إظهار قسم فوائد التعلم التعاوني',
            'show_partners' => 'إظهار قسم الشركاء',
            'hero_video_enabled' => 'تفعيل فيديو الصفحة الرئيسية',
            'hero_video_url' => 'رابط/مسار فيديو الهيرو',
            'hero_video_poster' => 'صورة غلاف الفيديو',
            'whatsapp_number' => 'رقم واتساب للتواصل السريع',
        ];

        return $descriptions[$key] ?? $key;
    }
}
