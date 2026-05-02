<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createTestUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
        ], $overrides));
    }

    protected function createAuthenticatedUser(array $overrides = []): array
    {
        $user = $this->createTestUser($overrides);
        $token = $user->createToken('test-token')->plainTextToken;
        
        return [
            'user' => $user,
            'token' => $token,
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ];
    }

    protected function assertApiResponse($response, int $status = 200, bool $success = true): void
    {
        $response->assertStatus($status);
        $response->assertJson(['success' => $success]);
    }

    protected function assertValidationError($response, string $field): void
    {
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => [$field]
        ]);
    }
}
