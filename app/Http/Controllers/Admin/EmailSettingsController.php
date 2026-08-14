<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * تحكّم الأدمن في بريد الأحداث (خطّة البريد P6): مفتاح رئيسيّ + مفتاح لكل نوع.
 * تحترمها MailGate قبل الإرسال. محميّ can:access-admin.
 */
class EmailSettingsController extends Controller
{
    private array $types = [
        'welcome' => 'ترحيب بالطالب',
        'activity_graded' => 'إشعار تصحيح نشاط',
        'badge_earned' => 'إشعار منح شارة',
        'level_up' => 'إشعار ترقية المستوى',
    ];

    public function edit()
    {
        $flags = [];
        foreach (array_keys($this->types) as $k) {
            $flags[$k] = (bool) setting('email_type_' . $k, true);
        }

        return view('admin.email-settings', [
            'types' => $this->types,
            'flags' => $flags,
            'master' => (bool) setting('email_master_enabled', true),
        ]);
    }

    public function update(Request $request)
    {
        Setting::set('email_master_enabled', $request->boolean('email_master_enabled'), 'boolean');

        foreach (array_keys($this->types) as $k) {
            Setting::set('email_type_' . $k, $request->boolean('type_' . $k), 'boolean');
        }

        return back()->with('success', 'حُفِظت إعدادات البريد بنجاح.');
    }
}
