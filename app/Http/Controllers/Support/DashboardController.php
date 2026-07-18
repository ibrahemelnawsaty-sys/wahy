<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * لوحة تحكّم الدعم الفنيّ — نظرة شاملة على التذاكر والمستخدمين.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tickets' => SupportTicket::count(),
            'open_tickets' => SupportTicket::where('status', SupportTicket::STATUS_OPEN)->count(),
            'answered_tickets' => SupportTicket::where('status', SupportTicket::STATUS_ANSWERED)->count(),
            'resolved_tickets' => SupportTicket::where('status', SupportTicket::STATUS_RESOLVED)->count(),
            'closed_tickets' => SupportTicket::where('status', SupportTicket::STATUS_CLOSED)->count(),
            'escalated_tickets' => SupportTicket::where('escalated', true)->count(),
            'total_users' => User::count(),
            'my_resolved' => SupportTicket::where('resolved_by', Auth::id())
                ->where('status', SupportTicket::STATUS_RESOLVED)
                ->count(),
        ];

        // أحدث التذاكر
        $recent_tickets = SupportTicket::with(['user', 'assignee'])
            ->latest('updated_at')
            ->take(10)
            ->get();

        return view('support.dashboard', compact('stats', 'recent_tickets'));
    }
}
