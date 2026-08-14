<?php

namespace App\Http\Controllers;

use App\Models\EmailPreference;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * تفضيلات البريد وإلغاء الاشتراك عبر رابط موقَّع (خطّة البريد P4) — بلا تسجيل دخول.
 * الروابط تُولَّد بـemail_unsubscribe_url($user) وتُدرَج في تذييل البريد + ترويسة List-Unsubscribe.
 */
class UnsubscribeController extends Controller
{
    public function show(User $user)
    {
        $pref = EmailPreference::firstOrCreate(['user_id' => $user->id]);

        return view('emails.preferences', compact('user', 'pref'));
    }

    public function update(Request $request, User $user)
    {
        $pref = EmailPreference::firstOrCreate(['user_id' => $user->id]);

        if ($request->input('action') === 'unsubscribe_all') {
            $pref->update([
                'unsubscribed_all' => true,
                'digests' => false,
                'announcements' => false,
                'events' => false,
            ]);

            return view('emails.preferences', ['user' => $user, 'pref' => $pref->fresh(), 'done' => 'أُلغِي اشتراكك في كل الرسائل غير الأساسيّة.']);
        }

        $pref->update([
            'unsubscribed_all' => false,
            'digests' => $request->boolean('digests'),
            'announcements' => $request->boolean('announcements'),
            'events' => $request->boolean('events'),
        ]);

        return view('emails.preferences', ['user' => $user, 'pref' => $pref->fresh(), 'done' => 'حُفِظت تفضيلاتك بنجاح.']);
    }
}
