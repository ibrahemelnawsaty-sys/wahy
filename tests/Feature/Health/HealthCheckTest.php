<?php

namespace Tests\Feature\Health;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_ping_returns_200(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);
        $response->assertJsonStructure(['status', 'timestamp']);
    }

    public function test_health_ping_is_public(): void
    {
        // لا actingAs — ضيف
        $response = $this->get('/health');
        $response->assertOk();
    }

    public function test_detailed_health_requires_super_admin(): void
    {
        // ضيف
        $this->get('/health/detailed')->assertRedirect();

        // طالب
        $student = User::factory()->student()->create();
        $this->actingAs($student)
            ->get('/health/detailed')
            ->assertForbidden();
    }

    public function test_detailed_health_returns_components_for_super_admin(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/health/detailed');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'app' => ['name', 'environment'],
            'checks' => [
                'database' => ['healthy'],
                'cache' => ['healthy'],
                'storage' => ['healthy'],
                'queue' => ['healthy'],
            ],
        ]);
    }
}
