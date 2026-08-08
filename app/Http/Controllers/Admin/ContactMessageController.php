<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

/**
 * عرض وإدارة رسائل «تواصل معنا» (تُغلق ثغرة الرسائل المحبوسة: كانت تُحفَظ بلا واجهة عرض).
 * محميّة can:access-admin. لا إدخال HTML — كل العرض مُهرَّب في Blade.
 */
class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $query = ContactMessage::query()->latest();

        if (in_array($status, ['unread', 'read', 'replied'], true)) {
            $query->where('status', $status);
        }

        return view('admin.contact-messages.index', [
            'messages' => $query->paginate(20)->withQueryString(),
            'activeStatus' => $status,
            'counts' => [
                'all' => ContactMessage::count(),
                'unread' => ContactMessage::where('status', 'unread')->count(),
                'read' => ContactMessage::where('status', 'read')->count(),
                'replied' => ContactMessage::where('status', 'replied')->count(),
            ],
        ]);
    }

    public function show(ContactMessage $contactMessage)
    {
        // فتح رسالة غير مقروءة يعلّمها مقروءة تلقائيّاً.
        if ($contactMessage->status === 'unread') {
            $contactMessage->update(['status' => 'read']);
        }

        return view('admin.contact-messages.show', ['message' => $contactMessage]);
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate(['status' => 'required|in:unread,read,replied']);

        $contactMessage->update([
            'status' => $data['status'],
            'replied_at' => $data['status'] === 'replied' ? now() : $contactMessage->replied_at,
        ]);

        return back()->with('success', 'تم تحديث حالة الرسالة.');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact-messages.index')->with('success', 'تم حذف الرسالة.');
    }
}
