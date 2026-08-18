<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * المستخدم بلا صورة يجب أن يظهر بحرف اسمه الأوّل — في **كلّ** مكان، ومنها صفحة المتصلين.
 *
 * الجذر: `User::getAvatarUrlAttribute()` هو مصدر الحقيقة الوحيد لصورة المستخدم، ويولّد عند
 * غياب الصورة SVG بحرف الاسم ولونٍ ثابت مشتقّ من المعرّف. لكنّ صفحة المتصلين تبني رابط الصورة
 * يدويّاً وتسقط إلى ملفّ `default-avatar.webp` ثابت — بلا أيّ حرف. سبب الالتفاف أنّ الاستعلام
 * يُرجِع صفوف query-builder مجرّدة لا نماذج User، فالـaccessor غير متاح عليها.
 */
class OnlineUsersAvatarTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    private function markOnline(User $user): void
    {
        DB::table('sessions')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'payload' => base64_encode('x'),
            'last_activity' => now()->timestamp,
        ]);
    }

    public function test_user_without_avatar_gets_an_initial_letter_avatar(): void
    {
        $user = User::factory()->create(['role' => 'student', 'name' => 'سعيد الحارثي', 'avatar' => null]);
        $this->markOnline($user);

        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.online-users.api'))
            ->assertOk()
            ->json();

        $row = collect($json['onlineUsers'] ?? [])->firstWhere('id', $user->id);
        $this->assertNotNull($row, 'المستخدم المتصل يجب أن يظهر في القائمة');

        $this->assertStringStartsWith('data:image/svg+xml', $row['avatar_url'], 'يجب توليد صورة حرف');
        // الحرف الأوّل داخل الـSVG (المُرمَّز بـrawurlencode).
        $this->assertStringContainsString('س', rawurldecode($row['avatar_url']));
    }

    public function test_online_users_page_does_not_fall_back_to_the_letterless_default_image(): void
    {
        $user = User::factory()->create(['role' => 'student', 'name' => 'نورة', 'avatar' => null]);
        $this->markOnline($user);

        $html = (string) $this->actingAs($this->admin())
            ->get(route('admin.online-users'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('default-avatar.webp', $html, 'الصورة الافتراضية بلا حرف أُزيلت');
    }

    public function test_user_with_an_avatar_keeps_their_picture(): void
    {
        // حارس عدم-إفراط: لا نستبدل صورة موجودة بحرف.
        $user = User::factory()->create([
            'role' => 'teacher',
            'name' => 'خالد',
            'avatar' => 'https://example.test/pic.png',
        ]);
        $this->markOnline($user);

        $json = $this->actingAs($this->admin())
            ->getJson(route('admin.online-users.api'))
            ->assertOk()
            ->json();

        $row = collect($json['onlineUsers'] ?? [])->firstWhere('id', $user->id);
        $this->assertSame('https://example.test/pic.png', $row['avatar_url']);
    }
}
