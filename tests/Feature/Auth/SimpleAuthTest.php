<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SimpleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_endpoint_exists(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        // Should return 422 for validation, not 404
        $this->assertNotEquals(404, $response->status());
    }

    public function test_register_endpoint_exists(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        // Should return 422 for validation, not 404
        $this->assertNotEquals(404, $response->status());
    }

    public function test_logout_endpoint_exists(): void
    {
        $response = $this->postJson('/api/v1/logout');

        // Should return 401 for unauthenticated, not 404
        $this->assertEquals(401, $response->status());
    }

    public function test_me_endpoint_exists(): void
    {
        $response = $this->getJson('/api/v1/me');

        // Should return 401 for unauthenticated, not 404
        $this->assertEquals(401, $response->status());
    }

    public function test_api_structure_is_consistent(): void
    {
        // Test that failed responses have consistent structure
        $response = $this->postJson('/api/v1/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors'
        ]);
    }
}
