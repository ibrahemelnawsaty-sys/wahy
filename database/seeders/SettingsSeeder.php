<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
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
                'description' => 'نظام الثيم (light, dark, custom)'
            ],
            [
                'key' => 'primary_color',
                'value' => '#3CCB8A',
                'type' => 'string',
                'description' => 'اللون الأساسي للموقع'
            ],
            [
                'key' => 'secondary_color',
                'value' => '#3B82F6',
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
                'description' => 'لون خلفية الموقع'
            ],
            [
                'key' => 'font_family',
                'value' => 'IBM Plex Sans Arabic',
                'type' => 'string',
                'description' => 'خط الموقع'
            ],
            [
                'key' => 'layout_style',
                'value' => 'wide',
                'type' => 'string',
                'description' => 'نمط التخطيط (full-width, boxed, wide)'
            ],
            // Site Information
            [
                'key' => 'site_name',
                'value' => 'منصة قيمّ',
                'type' => 'string',
                'description' => 'اسم الموقع'
            ],
            [
                'key' => 'site_description',
                'value' => 'منصة تعليمية لتعزيز القيم والأخلاق',
                'type' => 'string',
                'description' => 'وصف الموقع'
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@qiyamm.sa',
                'type' => 'string',
                'description' => 'بريد التواصل'
            ],
            [
                'key' => 'contact_phone',
                'value' => '0112345678',
                'type' => 'string',
                'description' => 'رقم التواصل'
            ]
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
    }
}
