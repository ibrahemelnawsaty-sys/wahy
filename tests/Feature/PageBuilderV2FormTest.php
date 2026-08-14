<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * كتلة نموذج التواصل: تُصيَّر بحقول + إرسال عامّ يُخزّن الرسالة (CSRF/throttle/honeypot).
 */
class PageBuilderV2FormTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_block_renders_with_fields(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $html = $this->actingAs($admin)->post(route('admin.pb.preview'), ['body' => [
            ['type' => 'form', 'props' => ['title' => 'راسلنا', 'button_text' => 'أرسل']],
        ]])->assertOk()->getContent();

        $this->assertStringContainsString('pb-form', $html);
        $this->assertStringContainsString('name="message"', $html);
        $this->assertStringContainsString('/pb/form-submit', $html);
    }

    public function test_submission_stores_message(): void
    {
        User::factory()->create(['role' => 'super_admin']); // متلقّي الإشعار

        $this->post(route('pb.form-submit'), [
            'name' => 'زائر', 'email' => 'a@b.com', 'message' => 'رسالة تجريبيّة', 'page_slug' => 'about',
        ])->assertRedirect();

        $this->assertDatabaseHas('pb_form_submissions', ['name' => 'زائر', 'email' => 'a@b.com', 'page_slug' => 'about']);
    }

    public function test_honeypot_blocks_bot_submissions(): void
    {
        $this->post(route('pb.form-submit'), [
            'name' => 'بوت', 'email' => 'bot@x.com', 'message' => 'سبام', 'website' => 'http://spam',
        ])->assertRedirect();

        $this->assertDatabaseCount('pb_form_submissions', 0);
    }
}
