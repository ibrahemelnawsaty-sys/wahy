<?php

namespace Tests\Feature;

use App\Mail\ParentChildActivatedMail;
use App\Mail\ParentChildActivityGradedMail;
use App\Mail\ParentChildInactiveMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * بريد أولياء الأمور (خطّة أدوار البريد P1/P2/P7): تصيير الرسائل + أمر تنبيه الخمول.
 */
class EmailParentTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_mailables_render(): void
    {
        $parent = User::factory()->create(['role' => 'parent', 'name' => 'الأب']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'الابن']);

        $this->assertStringContainsString('الابن', (new ParentChildActivatedMail($parent, $student))->render());
        $this->assertStringContainsString('نشاط تجريبيّ', (new ParentChildActivityGradedMail($parent, $student, 'نشاط تجريبيّ', 8))->render());
        $this->assertStringContainsString('يدخل', (new ParentChildInactiveMail($parent, $student, 2))->render());
    }

    public function test_inactive_children_command_emails_parents(): void
    {
        Mail::fake();
        $student = User::factory()->create(['role' => 'student', 'email' => 's@example.com']);
        $parent = User::factory()->create(['role' => 'parent', 'email' => 'p@example.com']);
        $student->parents()->attach($parent->id);

        DB::table('streaks')->insert([
            'user_id' => $student->id, 'current_streak' => 0, 'longest_streak' => 0,
            'last_activity_date' => now()->subDays(2)->toDateString(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // طالب نشِط اليوم — يجب ألّا يُنبَّه أهله
        $active = User::factory()->create(['role' => 'student']);
        $activeParent = User::factory()->create(['role' => 'parent', 'email' => 'ap@example.com']);
        $active->parents()->attach($activeParent->id);
        DB::table('streaks')->insert([
            'user_id' => $active->id, 'current_streak' => 5, 'longest_streak' => 5,
            'last_activity_date' => now()->toDateString(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->artisan('emails:parent-inactive-children --days=2')->assertSuccessful();

        Mail::assertQueued(ParentChildInactiveMail::class, 1);
        Mail::assertQueued(ParentChildInactiveMail::class, fn ($m) => $m->hasTo('p@example.com'));
    }
}
