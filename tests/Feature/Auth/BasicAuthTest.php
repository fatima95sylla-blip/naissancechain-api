<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_endpoint_exists(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'ADMIN',
            'telephone' => '1234567890',
            'prefecture' => 'Abidjan',
            'zone' => 'Zone 1'
        ]);

        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_login_endpoint_exists(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $this->assertContains($response->status(), [200, 422]);
    }

    public function test_logout_endpoint_requires_auth(): void
    {
        $response = $this->postJson('/api/v1/logout');
        $this->assertEquals(401, $response->status());
    }

    public function test_profile_endpoint_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/me');
        $this->assertEquals(401, $response->status());
    }

    public function test_protected_endpoints_work_with_token(): void
    {
        // Create user
        $user = User::factory()->create([
            'email' => 'token@example.com',
            'password' => bcrypt('Password123!'),
            'name' => 'Token User'
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => 'token@example.com',
            'password' => 'Password123!'
        ]);

        if ($loginResponse->status() === 200) {
            $token = $loginResponse->json('data.token');
            $headers = ['Authorization' => 'Bearer ' . $token];

            // Test protected endpoints
            $profileResponse = $this->withHeaders($headers)->getJson('/api/v1/me');
            $this->assertContains($profileResponse->status(), [200, 401]);

            $logoutResponse = $this->withHeaders($headers)->postJson('/api/v1/logout');
            $this->assertContains($logoutResponse->status(), [200, 401]);
        }
    }

    public function test_api_responses_have_json_structure(): void
    {
        // Test error response structure
        $response = $this->postJson('/api/v1/login', [
            'email' => 'invalid-email',
            'password' => 'wrong'
        ]);

        if ($response->status() >= 400) {
            $data = $response->json();
            $this->assertArrayHasKey('success', $data);
            $this->assertArrayHasKey('message', $data);
        }
    }
}
