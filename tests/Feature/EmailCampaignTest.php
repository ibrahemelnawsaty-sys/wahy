<?php

namespace Tests\Feature;

use App\Jobs\DispatchCampaignJob;
use App\Mail\CampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailPreference;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * المُرسِل الجماعيّ/المخصّص (خطّة البريد P5): إنشاء حملة يُصفّف وظيفة التوزيع،
 * والتوزيع يُصفّف رسالة لكل مستلِم في الجمهور مع احترام إلغاء الاشتراك.
 */
class EmailCampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creating_campaign_dispatches_distribution_job(): void
    {
        Bus::fake();
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->post(route('admin.email-campaigns.store'), [
            'subject' => 'تحديث المنصّة',
            'body' => '<p>مرحبًا {{name}}</p>',
            'audience_type' => 'role',
            'audience_role' => 'student',
            'action' => 'send',
        ])->assertRedirect();

        $this->assertDatabaseHas('email_campaigns', [
            'audience_type' => 'role', 'audience_value' => 'student', 'status' => 'queued',
        ]);
        Bus::assertDispatched(DispatchCampaignJob::class);

        $this->get(route('admin.email-campaigns.index'))->assertOk(); // اللوحة تُصيَّر
    }

    public function test_distribution_queues_to_audience_and_skips_optout(): void
    {
        $s1 = User::factory()->create(['role' => 'student', 'email' => 's1@example.com']);
        $s2 = User::factory()->create(['role' => 'student', 'email' => 's2@example.com']);
        User::factory()->create(['role' => 'teacher', 'email' => 't@example.com']); // خارج الجمهور
        EmailPreference::create(['user_id' => $s2->id, 'unsubscribed_all' => true]); // ملغى

        $campaign = EmailCampaign::create([
            'subject' => 'س', 'body' => '<p>ن</p>',
            'audience_type' => 'role', 'audience_value' => 'student', 'status' => 'queued',
        ]);

        Mail::fake();
        (new DispatchCampaignJob($campaign->id))->handle();

        Mail::assertQueued(CampaignMail::class, 1); // s1 فقط (s2 ملغى، المعلّم خارج الدور)
        Mail::assertQueued(CampaignMail::class, fn ($m) => $m->hasTo('s1@example.com'));
        $this->assertSame('sent', $campaign->fresh()->status);
        $this->assertSame(1, $campaign->fresh()->total_recipients);
    }

    public function test_create_and_index_pages_render(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->get(route('admin.email-campaigns.create'))->assertOk()->assertSee('إرسال بريد جماعيّ');
        $this->actingAs($admin)->get(route('admin.email-campaigns.index'))->assertOk();
    }

    public function test_master_switch_off_blocks_campaign_distribution(): void
    {
        Setting::set('email_master_enabled', false, 'boolean');
        User::factory()->create(['role' => 'student', 'email' => 'x@example.com']);
        $campaign = EmailCampaign::create([
            'subject' => 'س', 'body' => '<p>ن</p>',
            'audience_type' => 'role', 'audience_value' => 'student', 'status' => 'queued',
        ]);

        Mail::fake();
        (new DispatchCampaignJob($campaign->id))->handle();

        Mail::assertNothingQueued();
        $this->assertSame('queued', $campaign->fresh()->status); // لم تُدَّعَ — تُستأنَف لاحقًا
    }
}
