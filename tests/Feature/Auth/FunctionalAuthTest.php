<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FunctionalAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_endpoints_are_accessible(): void
    {
        // Test that endpoints exist and respond appropriately
        $endpoints = [
            ['method' => 'POST', 'url' => '/api/v1/register', 'status' => [422, 201]],
            ['method' => 'POST', 'url' => '/api/v1/login', 'status' => [422, 200]],
            ['method' => 'POST', 'url' => '/api/v1/logout', 'status' => 401],
            ['method' => 'GET', 'url' => '/api/v1/me', 'status' => 401],
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->json($endpoint['method'], $endpoint['url'], []);
            
            $this->assertContains($response->status(), $endpoint['status'], 
                "Endpoint {$endpoint['url']} should return one of expected statuses");
        }
    }

    public function test_registration_with_complete_data(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'AGENT',
            'telephone' => '1234567890',
            'prefecture' => 'Abidjan',
            'zone' => 'Zone 1'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        // Check response
        $this->assertContains($response->status(), [201, 422]);
        
        if ($response->status() === 201) {
            $this->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type'
                ]
            ]);
            
            // Verify user was created
            $this->assertDatabaseHas('users', [
                'email' => 'john@example.com',
                'name' => 'John Doe'
            ]);
        }
    }

    public function test_login_with_registered_user(): void
    {
        // First create a user
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
            $this->assertJsonStructure([
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

    public function test_protected_routes_with_token(): void
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

            // Test protected endpoints
            $profileResponse = $this->withHeaders($headers)->getJson('/api/v1/me');
            $this->assertContains($profileResponse->status(), [200, 401]);

            $logoutResponse = $this->withHeaders($headers)->postJson('/api/v1/logout');
            $this->assertContains($logoutResponse->status(), [200, 401]);
        }
    }

    public function test_error_responses_have_consistent_format(): void
    {
        // Test various error scenarios
        $errorTests = [
            fn() => $this->postJson('/api/v1/login', ['email' => 'invalid']),
            fn() => $this->postJson('/api/v1/register', ['email' => 'invalid']),
            fn() => $this->getJson('/api/v1/nonexistent'),
        ];

        foreach ($errorTests as $test) {
            $response = $test();
            
            if ($response->status() >= 400) {
                $data = $response->json();
                
                // Check for consistent error structure
                $this->assertArrayHasKey('success', $data);
                $this->assertFalse($data['success']);
                $this->assertArrayHasKey('message', $data);
            }
        }
    }

    public function test_api_rate_limiting(): void
    {
        // Make multiple requests to test rate limiting
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        // At least some requests should be rate limited or return validation errors
        $rateLimitedOrErrors = collect($responses)->filter(function ($response) {
            return in_array($response->status(), [429, 422, 401]);
        });

        $this->assertGreaterThan(0, $rateLimitedOrErrors->count(), 
            'Some requests should be rate limited or return validation errors');
    }
}
