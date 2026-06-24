<?php

namespace Tests\Unit\Support;

use App\Support\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_ok_returns_200_with_data(): void
    {
        $response = ApiResponse::ok(['name' => 'علي'], 'تم');

        $this->assertEquals(200, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        $this->assertTrue($payload['success']);
        $this->assertEquals(['name' => 'علي'], $payload['data']);
        $this->assertEquals('تم', $payload['message']);
    }

    public function test_created_returns_201(): void
    {
        $response = ApiResponse::created(['id' => 1]);

        $this->assertEquals(201, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        $this->assertEquals('تم الإنشاء بنجاح', $payload['message']);
    }

    public function test_error_returns_400_by_default(): void
    {
        $response = ApiResponse::error('خطأ');

        $this->assertEquals(400, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        $this->assertFalse($payload['success']);
        $this->assertEquals('خطأ', $payload['message']);
    }

    public function test_unauthorized_returns_401(): void
    {
        $this->assertEquals(401, ApiResponse::unauthorized()->getStatusCode());
    }

    public function test_forbidden_returns_403(): void
    {
        $this->assertEquals(403, ApiResponse::forbidden()->getStatusCode());
    }

    public function test_not_found_returns_404(): void
    {
        $this->assertEquals(404, ApiResponse::notFound()->getStatusCode());
    }

    public function test_validation_error_returns_422_with_errors(): void
    {
        $response = ApiResponse::validationError(['email' => ['البريد مستخدم']]);

        $this->assertEquals(422, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $payload);
        $this->assertEquals(['البريد مستخدم'], $payload['errors']['email']);
    }

    public function test_no_content_returns_204(): void
    {
        $this->assertEquals(204, ApiResponse::noContent()->getStatusCode());
    }
}
