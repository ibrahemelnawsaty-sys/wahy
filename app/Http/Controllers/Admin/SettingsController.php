<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $settingsToSave = [
                'site_name' => $request->site_name,
                'site_description' => $request->site_description,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'facebook_url' => $request->facebook_url,
                'twitter_url' => $request->twitter_url,
                'instagram_url' => $request->instagram_url,
                'linkedin_url' => $request->linkedin_url,
                'footer_text' => $request->footer_text,
                'maintenance_mode' => $request->has('maintenance_mode') ? 1 : 0,
                'maintenance_message' => $request->maintenance_message,
            ];

            foreach ($settingsToSave as $key => $value) {
                $type = $key === 'maintenance_mode' ? 'boolean' : 'string';
                $description = $this->getSettingDescription($key);
                
                Setting::set($key, $value ?? '', $type, $description);
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
        ];
        
        return $descriptions[$key] ?? $key;
    }
}

