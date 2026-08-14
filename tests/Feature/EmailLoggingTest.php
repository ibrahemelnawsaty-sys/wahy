<?php

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * التتبّع التلقائيّ (خطّة البريد P3): كل بريد صادر يُسجَّل عبر مستمع أحداث Mail،
 * ولوحة الأدمن تعرضه. محميّة can:access-admin.
 */
class EmailLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_mail_is_auto_logged_via_mail_events(): void
    {
        Mail::raw('نصّ الاختبار', function ($m) {
            $m->to('recipient@example.com', 'مستلِم')->subject('عنوان الاختبار');
            $m->getHeaders()->addTextHeader('X-Wahy-Category', 'auth');
        });

        $log = EmailLog::where('to_email', 'recipient@example.com')->first();
        $this->assertNotNull($log, 'يجب إنشاء سجلّ بريد تلقائيًّا');
        $this->assertSame('عنوان الاختبار', $log->subject);
        $this->assertSame('auth', $log->category);
        // بعد MessageSent يصبح sent (النقل array/log يُطلق الحدثَين)
        $this->assertContains($log->status, ['sent', 'sending']);
    }

    public function test_sensitive_category_body_is_not_stored(): void
    {
        Mail::raw('رمز الدخول السرّي 123456', function ($m) {
            $m->to('secret@example.com')->subject('رمز الدخول');
            $m->getHeaders()->addTextHeader('X-Wahy-Category', 'auth');
        });

        $log = EmailLog::where('to_email', 'secret@example.com')->first();
        $this->assertNotNull($log);
        $this->assertSame('auth', $log->category);
        $this->assertNull($log->body, 'يجب ألّا يُخزَّن جسم الرسائل الحسّاسة (رموز/روابط)');
    }

    public function test_mark_stuck_as_failed_reconciles_only_old_sending(): void
    {
        $stuck = EmailLog::create(['to_email' => 's@example.com', 'status' => 'sending']);
        $stuck->created_at = now()->subMinutes(30);
        $stuck->save();
        $recent = EmailLog::create(['to_email' => 'r@example.com', 'status' => 'sending']);

        $this->assertSame(1, EmailLog::markStuckAsFailed(20));
        $this->assertSame('failed', $stuck->fresh()->status);
        $this->assertNotNull($stuck->fresh()->error_message);
        $this->assertSame('sending', $recent->fresh()->status);
    }

    public function test_admin_dashboard_lists_logs_and_guest_is_blocked(): void
    {
        EmailLog::create(['to_email' => 'seen@example.com', 'subject' => 'مرئيّة', 'status' => 'sent', 'category' => 'transactional']);

        $this->get(route('admin.email-logs.index'))->assertRedirect(); // ضيف

        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->get(route('admin.email-logs.index'))
            ->assertOk()
            ->assertSee('seen@example.com')
            ->assertSee('سجلّ البريد');
    }
}
