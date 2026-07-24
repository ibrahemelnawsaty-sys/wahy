<?php

namespace Tests\Feature\Activities;

use App\Exports\Concerns\SanitizesCsvOutput;
use App\Imports\StudentsImport;
use App\Models\School;
use App\Models\ShopItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * إصلاحات أمن السوبر أدمن + لوحة التحكّم (المراجعة الخصميّة الشاملة):
 *  - استيراد الطلاب لا يمنح كلمة مرور ثابتة «123456» ويفرض تغييرها.
 *  - تصدير CSV يحيّد حقن الصيغ.
 *  - حذف منتج المتجر محروسٌ ضدّ إتلاف مشتريات الطلاب.
 *  - قائمة الدعم لا تُعدِّد الحسابات المميّزة.
 *  - قوالب لوحة السوبر أدمن تُهرِّب القيم قبل حقنها عبر innerHTML (XSS مخزَّن).
 */
class SuperAdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin', 'status' => 'active']);
    }

    public function test_student_import_generates_random_password_and_forces_change(): void
    {
        $school = School::factory()->create();
        $import = new StudentsImport($school->id);

        // عمود كلمة المرور فارغ (كما يقترح القالب) → يجب ألّا تكون «123456»
        $user = $import->model(['طالب أوّل', 'stu1@example.com', '', '', '']);

        $this->assertNotNull($user);
        $this->assertTrue((bool) $user->password_change_required, 'إجبار تغيير كلمة المرور');
        $this->assertFalse(Hash::check('123456', $user->password), 'ليست الكلمة الثابتة «123456»');

        $creds = $import->getCredentials();
        $this->assertCount(1, $creds);
        $this->assertNotSame('123456', $creds[0]['password']);
        $this->assertTrue(Hash::check($creds[0]['password'], $user->password), 'الكلمة المعروضة للمدير تطابق المحفوظة');
    }

    public function test_csv_sanitizer_neutralizes_formula_injection(): void
    {
        $probe = new class
        {
            use SanitizesCsvOutput;

            public function run(array $row): array
            {
                return $this->sanitizeRow($row);
            }
        };

        $out = $probe->run(['=HYPERLINK("http://evil","x")', '+1', '-2', '@cmd', 'عادي']);

        $this->assertSame("'=HYPERLINK(\"http://evil\",\"x\")", $out[0]);
        $this->assertSame("'+1", $out[1]);
        $this->assertSame("'-2", $out[2]);
        $this->assertSame("'@cmd", $out[3]);
        $this->assertSame('عادي', $out[4], 'النصّ العاديّ لا يُمَسّ');
    }

    public function test_shop_item_delete_blocked_when_purchased(): void
    {
        $item = ShopItem::create([
            'name' => 'أڤاتار', 'type' => 'avatar', 'price' => 10, 'status' => 'active',
        ]);
        $student = User::factory()->student()->create();
        DB::table('user_purchases')->insert([
            'user_id' => $student->id, 'shop_item_id' => $item->id, 'price_paid' => 10,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->superAdmin())
            ->deleteJson(route('admin.shop.destroy', $item->id))
            ->assertStatus(422);

        $this->assertDatabaseHas('shop_items', ['id' => $item->id, 'status' => 'inactive']);
    }

    public function test_support_listing_excludes_privileged_accounts(): void
    {
        $support = User::factory()->create(['role' => 'technical_support', 'status' => 'active']);
        $victimAdmin = User::factory()->create(['role' => 'super_admin', 'email' => 'target-sa@example.com']);
        $student = User::factory()->student()->create(['email' => 'visible-stu@example.com']);

        $this->actingAs($support)
            ->get(route('support.users.index'))
            ->assertOk()
            ->assertDontSee('target-sa@example.com')
            ->assertSee('visible-stu@example.com');
    }

    public function test_online_users_page_escapes_values_before_innerHTML(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.online-users'))
            ->assertOk()
            ->assertSee('function escapeHtml')     // مُهرِّب موجود
            ->assertDontSee('${user.name}', false); // لم تعد القيمة الخام تُحقَن مباشرةً
    }
}
