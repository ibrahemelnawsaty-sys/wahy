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
