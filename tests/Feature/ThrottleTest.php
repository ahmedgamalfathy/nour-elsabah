<?php

namespace Tests\Feature;

use Tests\TestCase;

class ThrottleTest extends TestCase
{
    public function test_admin_login_returns_api_response_when_rate_limited(): void
    {
        $payload = [
            'username' => 'rate-limit-test@example.com',
            'password' => 'invalid-password',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/admin/auth/login', $payload);
        }

        $this->postJson('/api/v1/admin/auth/login', $payload)
            ->assertStatus(429)
            ->assertJson([
                'success' => false,
                'errors' => [],
            ]);
    }
}
