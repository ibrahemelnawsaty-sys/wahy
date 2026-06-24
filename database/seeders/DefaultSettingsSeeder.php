<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class DefaultSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // Theme Settings
            [
                'key' => 'site_theme',
                'value' => 'light',
                'type' => 'string',
                'description' => 'نمط الثيم للموقع (light, dark, custom)'
            ],
            [
                'key' => 'layout_style',
                'value' => 'wide',
                'type' => 'string',
                'description' => 'نمط التخطيط (full-width, boxed, wide)'
            ],
            
            // Colors
            [
                'key' => 'primary_color',
                'value' => '#667eea',
                'type' => 'string',
                'description' => 'اللون الأساسي للموقع'
            ],
            [
                'key' => 'secondary_color',
                'value' => '#764ba2',
                'type' => 'string',
                'description' => 'اللون الثانوي للموقع'
            ],
            [
                'key' => 'text_color',
                'value' => '#334155',
                'type' => 'string',
                'description' => 'لون النصوص الأساسية'
            ],
            [
                'key' => 'background_color',
                'value' => '#ffffff',
                'type' => 'string',
                'description' => 'لون الخلفية'
            ],
            
            // Typography
            [
                'key' => 'font_family',
                'value' => 'IBM Plex Sans Arabic',
                'type' => 'string',
                'description' => 'نوع الخط المستخدم'
            ],
            
            // Site Info
            [
                'key' => 'site_name',
                'value' => 'منصة قيمّ',
                'type' => 'string',
                'description' => 'اسم الموقع'
            ],
            [
                'key' => 'site_description',
                'value' => 'منصة تعليمية لتعزيز القيم الأخلاقية',
                'type' => 'string',
                'description' => 'وصف الموقع'
            ],
            [
                'key' => 'site_keywords',
                'value' => 'تعليم, قيم, أخلاق, طلاب',
                'type' => 'string',
                'description' => 'الكلمات المفتاحية للموقع'
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'string',
                'description' => 'شعار الموقع'
            ],
            [
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'string',
                'description' => 'أيقونة الموقع'
            ],
            [
                'key' => 'hero_background',
                'value' => null,
                'type' => 'string',
                'description' => 'خلفية قسم البطل'
            ],
            
            // Contact
            [
                'key' => 'contact_email',
                'value' => 'info@qiyam.edu.sa',
                'type' => 'string',
                'description' => 'البريد الإلكتروني للتواصل'
            ],
            [
                'key' => 'contact_phone',
                'value' => '+966 50 000 0000',
                'type' => 'string',
                'description' => 'رقم الهاتف للتواصل'
            ],
            [
                'key' => 'contact_address',
                'value' => 'الرياض، المملكة العربية السعودية',
                'type' => 'string',
                'description' => 'العنوان'
            ],
            
            // Social Media
            [
                'key' => 'social_facebook',
                'value' => null,
                'type' => 'string',
                'description' => 'رابط فيسبوك'
            ],
            [
                'key' => 'social_twitter',
                'value' => null,
                'type' => 'string',
                'description' => 'رابط تويتر/X'
            ],
            [
                'key' => 'social_instagram',
                'value' => null,
                'type' => 'string',
                'description' => 'رابط إنستقرام'
            ],
            [
                'key' => 'social_linkedin',
                'value' => null,
                'type' => 'string',
                'description' => 'رابط لينكد إن'
            ],
            [
                'key' => 'social_youtube',
                'value' => null,
                'type' => 'string',
                'description' => 'رابط يوتيوب'
            ],
            
            // Features
            [
                'key' => 'enable_registration',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'تفعيل التسجيل للمستخدمين الجدد'
            ],
            [
                'key' => 'enable_2fa',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'تفعيل المصادقة الثنائية'
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'وضع الصيانة'
            ],
            
            // Landing Page
            [
                'key' => 'hero_title',
                'value' => 'منصة قيمّ التعليمية',
                'type' => 'string',
                'description' => 'عنوان قسم البطل'
            ],
            [
                'key' => 'hero_subtitle',
                'value' => 'رحلة تعليمية تفاعلية لبناء القيم والأخلاق',
                'type' => 'string',
                'description' => 'نص فرعي لقسم البطل'
            ],
            [
                'key' => 'hero_cta_text',
                'value' => 'ابدأ رحلتك الآن',
                'type' => 'string',
                'description' => 'نص زر الإجراء'
            ],
            [
                'key' => 'hero_cta_link',
                'value' => '/register',
                'type' => 'string',
                'description' => 'رابط زر الإجراء'
            ],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'description' => $setting['description']
                ]
            );
        }

        $this->command->info('✅ تم إضافة الإعدادات الافتراضية بنجاح!');
    }
}
