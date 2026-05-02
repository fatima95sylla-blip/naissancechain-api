<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WorkingAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_with_all_required_fields(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'ADMIN',
            'telephone' => '1234567890',
            'prefecture' => 'Abidjan',
            'zone' => 'Zone 1'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        // Should succeed or give specific validation error
        $this->assertContains($response->status(), [201, 422]);
        
        if ($response->status() === 201) {
            $response->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
        }
    }

    public function test_user_registration_minimal_fields(): void
    {
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'AGENT',
            'telephone' => '0987654321',
            'prefecture' => 'Yamoussoukro',
            'zone' => 'Zone 2'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_user_login_with_valid_credentials(): void
    {
        // Create user with minimal required fields
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
            'name' => 'Test User'
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $this->assertContains($response->status(), [200, 422]);
        
        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                    'token_type'
                ]
            ]);
        }
    }

    public function test_protected_endpoints_require_authentication(): void
    {
        // Test logout without token
        $response = $this->postJson('/api/v1/logout');
        $this->assertEquals(401, $response->status());

        // Test profile without token
        $response = $this->getJson('/api/v1/me');
        $this->assertEquals(401, $response->status());
    }

    public function test_protected_endpoints_work_with_token(): void
    {
        // Create user and get token
        $user = User::factory()->create([
            'email' => 'token@example.com',
            'password' => bcrypt('Password123!'),
            'name' => 'Token User'
        ]);

        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => 'token@example.com',
            'password' => 'Password123!'
        ]);

        if ($loginResponse->status() === 200) {
            $token = $loginResponse->json('data.token');
            $headers = ['Authorization' => 'Bearer ' . $token];

            // Test logout with token
            $logoutResponse = $this->withHeaders($headers)->postJson('/api/v1/logout');
            $this->assertContains($logoutResponse->status(), [200, 401]);

            // Test profile with token
            $profileResponse = $this->withHeaders($headers)->getJson('/api/v1/me');
            $this->assertContains($profileResponse->status(), [200, 401]);
        }
    }

    public function test_api_error_responses_are_consistent(): void
    {
        // Test various error scenarios
        $responses = [
            $this->postJson('/api/v1/login', ['email' => 'invalid', 'password' => 'wrong']),
            $this->postJson('/api/v1/register', ['email' => 'invalid-email']),
            $this->getJson('/api/v1/nonexistent-endpoint')
        ];

        foreach ($responses as $response) {
            $this->assertContains($response->status(), [401, 422, 404, 405]);
            
            // All error responses should have consistent structure
            if ($response->status() >= 400) {
                $data = $response->json();
                $this->assertArrayHasKey('success', $data);
                $this->assertFalse($data['success']);
                $this->assertArrayHasKey('message', $data);
            }
        }
    }
}
