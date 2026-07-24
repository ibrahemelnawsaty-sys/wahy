<?php

namespace Tests\Feature\Activities;

use App\Models\School;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * إصلاحات أمن دور الدعم الفنيّ + نظام التذاكر (المراجعة الخصميّة الشاملة):
 *  - الدعم لا يمسّ الحسابات المميّزة (سوبر أدمن/مدير مدرسة/دعم آخر) ولا نفسه.
 *  - resetPassword يفرض تغيير كلمة المرور دائماً (لا انتحال صامت).
 *  - تذاكر المستخدم معزولة (IDOR) ولا يُقبَل ردّ على تذكرة مغلقة.
 *  - safe_html يزيل معالج الحدث الملاصق لعلامة الاقتباس (تجاوز XSS الحرِج).
 */
class TechSupportSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function support(): User
    {
        return User::factory()->create(['role' => 'technical_support', 'status' => 'active']);
    }

    public function test_support_cannot_reset_password_of_school_admin(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['role' => 'school_admin', 'school_id' => $school->id]);
        $originalHash = $admin->password;

        $this->actingAs($this->support())
            ->post(route('support.users.reset-password', $admin), [
                'password' => 'BrandNew123', 'password_confirmation' => 'BrandNew123',
            ])->assertStatus(403);

        $this->assertSame($originalHash, $admin->fresh()->password, 'كلمة مرور مدير المدرسة لم تتغيّر');
    }

    public function test_support_cannot_toggle_status_of_school_admin(): void
    {
        $admin = User::factory()->create(['role' => 'school_admin', 'status' => 'active']);

        $this->actingAs($this->support())
            ->post(route('support.users.toggle-status', $admin))
            ->assertStatus(403);

        $this->assertSame('active', $admin->fresh()->status);
    }

    public function test_support_cannot_manage_another_support_or_self(): void
    {
        $support = $this->support();
        $peer = User::factory()->create(['role' => 'technical_support']);

        // نظير دعم آخر
        $this->actingAs($support)
            ->post(route('support.users.toggle-status', $peer))->assertStatus(403);

        // النفس
        $this->actingAs($support)
            ->post(route('support.users.toggle-status', $support))->assertStatus(403);
    }

    public function test_support_reset_password_forces_change_for_student(): void
    {
        $student = User::factory()->student()->create(['password_change_required' => false]);

        $this->actingAs($this->support())
            ->post(route('support.users.reset-password', $student), [
                'password' => 'FreshPass99', 'password_confirmation' => 'FreshPass99',
            ])->assertRedirect();

        $student->refresh();
        $this->assertTrue((bool) $student->password_change_required, 'إجبار تغيير كلمة المرور مفعَّل دائماً');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('FreshPass99', $student->password));
    }

    public function test_ticket_is_owner_scoped(): void
    {
        $owner = User::factory()->student()->create();
        $other = User::factory()->student()->create();
        $ticket = SupportTicket::create([
            'user_id' => $owner->id, 'subject' => 'مشكلة', 'message' => 'وصف',
            'category' => 'technical', 'priority' => 'normal',
            'status' => SupportTicket::STATUS_OPEN, 'last_reply_at' => now(),
        ]);

        $this->actingAs($other)->get(route('tickets.show', $ticket))->assertStatus(403);
        $this->actingAs($owner)->get(route('tickets.show', $ticket))->assertOk();
    }

    public function test_reply_rejected_on_closed_ticket(): void
    {
        $owner = User::factory()->student()->create();
        $ticket = SupportTicket::create([
            'user_id' => $owner->id, 'subject' => 'مغلقة', 'message' => 'وصف',
            'category' => 'technical', 'priority' => 'normal',
            'status' => SupportTicket::STATUS_CLOSED, 'last_reply_at' => now(),
        ]);

        $this->actingAs($owner)
            ->post(route('tickets.reply', $ticket), ['message' => 'ردّ متأخّر'])
            ->assertRedirect();

        $this->assertSame(0, TicketReply::where('ticket_id', $ticket->id)->count(), 'لا ردّ على تذكرة مغلقة');
        $this->assertSame(SupportTicket::STATUS_CLOSED, $ticket->fresh()->status);
    }

    public function test_safe_html_strips_quote_adjacent_event_handler(): void
    {
        $out = safe_html('<img src="x"onerror="alert(document.cookie)">');
        $this->assertStringNotContainsStringIgnoringCase('onerror', $out, 'معالج الحدث الملاصق للاقتباس أُزيل');
        $this->assertStringContainsString('src="x"', $out, 'السمة السابقة بقيت سليمة');

        // الاقتباس المفرد والنمط غير المُقتبَس كذلك
        $this->assertStringNotContainsStringIgnoringCase('onerror', safe_html("<img src='x'onerror='x()'>"));
        $this->assertStringNotContainsStringIgnoringCase('onmouseover', safe_html('<span title="a"onmouseover="x()">h</span>'));
    }
}
