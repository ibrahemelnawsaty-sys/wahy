<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\PageBuilder\Models\FormSubmission;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * استقبال رسائل نموذج التواصل في صفحات المحرّر v2 — عامّ، محميّ بـCSRF (web) + throttle + honeypot.
 * يُخزّن الرسالة ويُشعِر السوبر أدمن. يُعيد للصفحة المُرسِلة بـ?sent=1.
 */
class PageFormController extends Controller
{
    public function submit(Request $request)
    {
        // honeypot: حقل مخفيّ يملؤه الروبوت فقط
        if ($request->filled('website')) {
            return redirect()->to($this->backUrl($request));
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:180',
            'message' => 'required|string|max:3000',
        ]);

        FormSubmission::create([
            'page_slug' => Str::limit((string) $request->input('page_slug'), 190, ''),
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);

        foreach (User::where('role', 'super_admin')->pluck('id') as $id) {
            NotificationService::send($id, 'رسالة جديدة من نموذج صفحة',
                $data['name'] . ': ' . Str::limit($data['message'], 90), 'form', url('/admin'));
        }

        return redirect()->to($this->backUrl($request));
    }

    /** إعادة للصفحة المُرسِلة (نفس الأصل فقط، منعاً لإعادة توجيه مفتوح) بعلامة النجاح. */
    private function backUrl(Request $request): string
    {
        $ref = (string) $request->headers->get('referer');
        $base = ($ref !== '' && str_starts_with($ref, url('/'))) ? strtok($ref, '?') : url('/');

        return $base . '?sent=1';
    }
}
