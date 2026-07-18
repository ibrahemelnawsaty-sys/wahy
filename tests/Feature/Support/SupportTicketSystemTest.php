<?php

namespace Tests\Feature\Support;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * نظام الدعم الفنيّ: الدور الجديد + تذاكر المستخدم + لوحة الدعم (تذاكر/مستخدمون).
 * يغطّي: هجرة الدور (role→string عبر SQLite)، توجيه الدخول، رفع/ردّ التذاكر،
 * التفويض/IDOR، حماية حسابات السوبر أدمن (بما فيها الدور الثانويّ)، وتدفّق الحالات.
 */
class SupportTicketSystemTest extends TestCase
{
    use RefreshDatabase;

    private function support(): User
    {
        // الدور الجديد يُخزَّن بعد تحويل العمود إلى string — نجاح هذا الإنشاء يُثبت الهجرة على SQLite
        return User::factory()->create(['role' => 'technical_support', 'school_id' => null, 'status' => 'active']);
    }

    private function ticketFor(User $owner, array $overrides = []): SupportTicket
    {
        return SupportTicket::create(array_merge([
            'user_id' => $owner->id,
            'school_id' => $owner->school_id,
            'subject' => 'مشكلة في الدخول',
            'message' => 'لا أستطيع تسجيل الدخول',
            'category' => 'technical',
            'priority' => 'normal',
            'status' => 'open',
            'last_reply_at' => now(),
        ], $overrides));
    }

    // ---------- الدور + الهجرة + التوجيه ----------

    public function test_technical_support_role_persists_after_role_column_migration(): void
    {
        $u = $this->support();
        $this->assertSame('technical_support', $u->fresh()->role);
        $this->assertTrue($u->isTechnicalSupport());
    }

    public function test_dashboard_redirects_technical_support_to_support_panel(): void
    {
        $this->actingAs($this->support())
            ->get('/dashboard')
            ->assertRedirect(route('support.dashboard'));
    }

    public function test_support_routes_are_forbidden_for_regular_users(): void
    {
        $student = User::factory()->student()->create();
        $this->actingAs($student)->get('/support/dashboard')->assertForbidden();
        $this->actingAs($student)->get('/support/users')->assertForbidden();
    }

    public function test_super_admin_can_reach_support_panel(): void
    {
        // CheckRole يمرّر السوبر أدمن دائماً — يفتح التذاكر المُصعّدة من رابط الإشعار
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get('/support/dashboard')
            ->assertOk();
    }

    // ---------- رفع التذاكر ----------

    public function test_any_user_can_raise_a_ticket(): void
    {
        $parent = User::factory()->parent()->create();

        $this->actingAs($parent)->post(route('tickets.store'), [
            'subject' => 'استفسار',
            'message' => 'عندي سؤال عن حساب ابني',
            'category' => 'account',
            'priority' => 'normal',
        ])->assertRedirect();

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $parent->id,
            'subject' => 'استفسار',
            'status' => 'open',
        ]);
    }

    public function test_ticket_creation_notifies_staff(): void
    {
        $this->support(); // موظّف دعم واحد على الأقلّ لاستقبال الإشعار
        $student = User::factory()->student()->create();

        $this->actingAs($student)->post(route('tickets.store'), [
            'subject' => 'مشكلة',
            'message' => 'التطبيق لا يعمل',
            'category' => 'technical',
            'priority' => 'high',
        ]);

        $this->assertDatabaseHas('notifications', ['type' => 'support_ticket']);
    }

    public function test_empty_or_whitespace_message_is_rejected(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)->post(route('tickets.store'), [
            'subject' => 'فارغة',
            'message' => '<div><br></div>',   // يُطبَّع إلى فراغ
            'category' => 'other',
            'priority' => 'low',
        ])->assertSessionHasErrors('message');

        $this->assertSame(0, SupportTicket::count());
    }

    // ---------- التفويض / IDOR ----------

    public function test_user_cannot_view_or_reply_to_another_users_ticket(): void
    {
        $owner = User::factory()->student()->create();
        $intruder = User::factory()->student()->create();
        $ticket = $this->ticketFor($owner);

        $this->actingAs($intruder)->get(route('tickets.show', $ticket))->assertForbidden();
        $this->actingAs($intruder)->post(route('tickets.reply', $ticket), ['message' => 'اختراق'])->assertForbidden();
    }

    // ---------- تدفّق الردّ / الحالة ----------

    public function test_staff_reply_marks_answered_and_notifies_owner(): void
    {
        $owner = User::factory()->student()->create();
        $ticket = $this->ticketFor($owner);

        $this->actingAs($this->support())
            ->post(route('support.tickets.reply', $ticket), ['message' => 'جرّب إعادة التشغيل'])
            ->assertRedirect();

        $this->assertSame('answered', $ticket->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $owner->id,
            'type' => 'support_ticket',
        ]);
    }

    public function test_owner_reply_reopens_and_clears_resolution(): void
    {
        $support = $this->support();
        $owner = User::factory()->student()->create();
        $ticket = $this->ticketFor($owner, [
            'status' => 'resolved',
            'resolved_by' => $support->id,
            'resolved_at' => now(),
        ]);

        $this->actingAs($owner)->post(route('tickets.reply', $ticket), ['message' => 'ما زالت المشكلة']);

        $ticket->refresh();
        $this->assertSame('open', $ticket->status);
        $this->assertNull($ticket->resolved_by);
        $this->assertNull($ticket->resolved_at);
    }

    public function test_resolve_sets_resolver_and_clears_escalation(): void
    {
        $support = $this->support();
        $ticket = $this->ticketFor(User::factory()->student()->create(), [
            'escalated' => true,
            'escalated_at' => now(),
        ]);

        $this->actingAs($support)->post(route('support.tickets.resolve', $ticket));

        $ticket->refresh();
        $this->assertSame('resolved', $ticket->status);
        $this->assertSame($support->id, $ticket->resolved_by);
        $this->assertFalse((bool) $ticket->escalated);
    }

    public function test_cannot_escalate_a_resolved_ticket(): void
    {
        $support = $this->support();
        $ticket = $this->ticketFor(User::factory()->student()->create(), ['status' => 'resolved']);

        $this->actingAs($support)->post(route('support.tickets.escalate', $ticket));

        $this->assertFalse((bool) $ticket->fresh()->escalated);
    }

    public function test_my_resolved_counter_excludes_reopened_tickets(): void
    {
        $support = $this->support();
        $owner = User::factory()->student()->create();
        // محلولة بواسطة الدعم ثم أعاد المالك فتحها
        $ticket = $this->ticketFor($owner, [
            'status' => 'resolved',
            'resolved_by' => $support->id,
            'resolved_at' => now(),
        ]);
        $this->actingAs($owner)->post(route('tickets.reply', $ticket), ['message' => 'رجعت المشكلة']);

        // عدّاد «محلولاتي» يجب أن يكون صفراً (خرجت من الحلّ)
        $count = SupportTicket::where('resolved_by', $support->id)->where('status', 'resolved')->count();
        $this->assertSame(0, $count);
    }

    // ---------- صلاحيات الدعم على الحسابات ----------

    public function test_support_can_reset_non_super_admin_password(): void
    {
        $support = $this->support();
        $student = User::factory()->student()->create();

        $this->actingAs($support)->post(route('support.users.reset-password', $student), [
            'password' => 'newpass1234',
            'password_confirmation' => 'newpass1234',
            'force' => '1',
        ])->assertRedirect();

        $student->refresh();
        $this->assertTrue(Hash::check('newpass1234', $student->password));
        $this->assertTrue((bool) $student->password_change_required);
    }

    public function test_support_toggle_status_flips_and_is_audit_logged(): void
    {
        $support = $this->support();
        $student = User::factory()->student()->create(['status' => 'active']);

        $this->actingAs($support)->post(route('support.users.toggle-status', $student))->assertRedirect();

        $this->assertSame('inactive', $student->fresh()->status);
        $this->assertDatabaseHas('activity_log', [
            'description' => 'support_toggle_status',
            'subject_id' => $student->id,
        ]);
    }

    public function test_support_cannot_touch_super_admin_account(): void
    {
        $support = $this->support();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($support)->post(route('support.users.reset-password', $admin), [
            'password' => 'hackpass1234',
            'password_confirmation' => 'hackpass1234',
        ])->assertForbidden();

        $this->actingAs($support)->post(route('support.users.toggle-status', $admin))->assertForbidden();
        $this->actingAs($support)->get(route('support.users.edit', $admin))->assertForbidden();
    }

    public function test_support_cannot_touch_effective_super_admin_via_secondary_role(): void
    {
        $support = $this->support();
        // دوره الأساسيّ معلّم لكنه يحمل super_admin كدور ثانويّ قابل للتبديل
        $hidden = User::factory()->teacher()->create(['secondary_roles' => ['super_admin']]);

        $this->actingAs($support)->post(route('support.users.toggle-status', $hidden))->assertForbidden();
        $this->actingAs($support)->post(route('support.users.reset-password', $hidden), [
            'password' => 'hackpass1234',
            'password_confirmation' => 'hackpass1234',
        ])->assertForbidden();
    }
}
