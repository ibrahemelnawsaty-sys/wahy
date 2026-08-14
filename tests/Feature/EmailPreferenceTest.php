<?php

namespace Tests\Feature;

use App\Models\EmailPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * تفضيلات البريد وإلغاء الاشتراك (خطّة البريد P4): البوّابة allows() + الرابط الموقَّع.
 */
class EmailPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_respects_optout_but_critical_always_passes(): void
    {
        $u = User::factory()->create(['role' => 'student']);

        // بلا تفضيلات محفوظة ⟶ مسموح
        $this->assertTrue(EmailPreference::allows($u, 'event'));
        $this->assertTrue(EmailPreference::allows($u, 'auth'));

        EmailPreference::create([
            'user_id' => $u->id, 'unsubscribed_all' => true,
            'events' => false, 'announcements' => false, 'digests' => false,
        ]);

        $this->assertFalse(EmailPreference::allows($u->fresh(), 'event'));
        $this->assertFalse(EmailPreference::allows($u->fresh(), 'campaign'));
        $this->assertTrue(EmailPreference::allows($u->fresh(), 'auth'));         // حرِج يتجاوز
        $this->assertTrue(EmailPreference::allows(null, 'event'));               // ضيف
    }

    public function test_signed_page_opens_and_unsigned_is_forbidden(): void
    {
        $u = User::factory()->create();

        $this->get(URL::signedRoute('email.unsubscribe', ['user' => $u->id]))
            ->assertOk()->assertSee('تفضيلات البريد');

        $this->get(route('email.unsubscribe', ['user' => $u->id]))->assertForbidden();
    }
}
