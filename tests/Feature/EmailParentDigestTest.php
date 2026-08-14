<?php

namespace Tests\Feature;

use App\Mail\ParentWeeklyDigestMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** الملخّص الأسبوعيّ لوليّ الأمر (خطّة أدوار البريد P8). */
class EmailParentDigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_digest_renders(): void
    {
        $p = User::factory()->create(['role' => 'parent', 'name' => 'الأب']);
        $html = (new ParentWeeklyDigestMail($p, [['name' => 'الابن', 'points' => 40]]))->render();
        $this->assertStringContainsString('الابن', $html);
        $this->assertStringContainsString('40', $html);
    }

    public function test_command_sends_only_when_a_child_was_active(): void
    {
        Mail::fake();
        $parent = User::factory()->create(['role' => 'parent', 'email' => 'p@example.com']);
        $child = User::factory()->create(['role' => 'student']);
        $parent->children()->attach($child->id);
        $child->points()->create(['points' => 30, 'reason' => 'ت']);

        $idle = User::factory()->create(['role' => 'parent', 'email' => 'idle@example.com']);
        $idleChild = User::factory()->create(['role' => 'student']);
        $idle->children()->attach($idleChild->id);

        $this->artisan('emails:digest-parent-weekly')->assertSuccessful();

        Mail::assertSent(ParentWeeklyDigestMail::class, 1);
        Mail::assertSent(ParentWeeklyDigestMail::class, fn ($m) => $m->hasTo('p@example.com'));
    }
}
