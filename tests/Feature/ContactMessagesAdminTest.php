<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * صفحة عرض رسائل «تواصل معنا» في لوحة الأدمن — تُغلق ثغرة الرسائل المحبوسة بلا واجهة.
 */
class ContactMessagesAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    private function msg(array $over = []): ContactMessage
    {
        return ContactMessage::create(array_merge([
            'full_name' => 'زائر',
            'email' => 'v@example.com',
            'user_type' => 'teacher',
            'message' => 'محتوى الرسالة',
            'status' => 'unread',
        ], $over));
    }

    public function test_admin_sees_index_with_messages(): void
    {
        $this->msg(['full_name' => 'محمد الزائر']);
        $this->actingAs($this->admin())->get(route('admin.contact-messages.index'))
            ->assertOk()->assertSee('محمد الزائر')->assertSee('رسائل «تواصل معنا»');
    }

    public function test_non_admin_forbidden(): void
    {
        $this->actingAs(User::factory()->student()->create())
            ->get(route('admin.contact-messages.index'))->assertForbidden();
    }

    public function test_show_marks_unread_as_read(): void
    {
        $m = $this->msg();
        $this->actingAs($this->admin())->get(route('admin.contact-messages.show', $m))
            ->assertOk()->assertSee('محتوى الرسالة');

        $this->assertSame('read', $m->fresh()->status);
    }

    public function test_status_replied_sets_timestamp(): void
    {
        $m = $this->msg(['status' => 'read']);
        $this->actingAs($this->admin())
            ->post(route('admin.contact-messages.status', $m), ['status' => 'replied'])
            ->assertRedirect();

        $m->refresh();
        $this->assertSame('replied', $m->status);
        $this->assertNotNull($m->replied_at);
    }

    public function test_status_rejects_invalid_value(): void
    {
        $m = $this->msg();
        $this->actingAs($this->admin())
            ->post(route('admin.contact-messages.status', $m), ['status' => 'nonsense'])
            ->assertSessionHasErrors('status');
    }

    public function test_destroy_deletes_message(): void
    {
        $m = $this->msg();
        $this->actingAs($this->admin())->delete(route('admin.contact-messages.destroy', $m))
            ->assertRedirect(route('admin.contact-messages.index'));

        $this->assertDatabaseMissing('contact_messages', ['id' => $m->id]);
    }

    public function test_status_filter_scopes_list(): void
    {
        $this->msg(['full_name' => 'غير مقروءة', 'status' => 'unread']);
        $this->msg(['full_name' => 'مقروءة قديمة', 'status' => 'read']);

        $this->actingAs($this->admin())->get(route('admin.contact-messages.index', ['status' => 'unread']))
            ->assertOk()->assertSee('غير مقروءة')->assertDontSee('مقروءة قديمة');
    }

    /**
     * الوضع الليلي **يجب** أن يتبع مفتاح التطبيق (`html[data-theme="dark"]`) لا تفضيلَ نظام
     * التشغيل. كان القالبان يستعملان `@media (prefers-color-scheme: dark)`، فيبقى الجدول
     * داكناً في الوضع النهاريّ على أيّ جهاز نظامه داكن — وزرّ الثيم بلا أثر عليه.
     */
    #[DataProvider('themedContactViews')]
    public function test_dark_styles_follow_the_app_theme_not_the_os(string $routeName, bool $needsMessage): void
    {
        $params = $needsMessage ? [$this->msg()] : [];

        $html = $this->actingAs($this->admin())
            ->get(route($routeName, $params))
            ->assertOk()
            ->getContent();

        // نقتصر على كتلة أنماط الصفحة (.cm-*) — لا نحكم على أنماط اللايوت أو الطرف الثالث.
        preg_match_all('/@media\s*\(prefers-color-scheme:\s*dark\)\s*\{[^}]*\.cm-/i', (string) $html, $bad);

        $this->assertSame([], $bad[0], "أنماط .cm-* الليليّة مربوطة بنظام التشغيل في {$routeName}");
        $this->assertStringContainsString('html[data-theme="dark"] .cm-', (string) $html);
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function themedContactViews(): array
    {
        return [
            'القائمة' => ['admin.contact-messages.index', false],
            'التفاصيل' => ['admin.contact-messages.show', true],
        ];
    }
}
