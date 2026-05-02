<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!@#',
            'password_confirmation' => 'Password123!@#',
            'role' => 'ADMIN',
            'telephone' => '1234567890',
            'prefecture' => 'Abidjan',
            'zone' => 'Zone 1'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $this->assertApiResponse($response, 201);
        
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at'
                ],
                'token',
                'token_type'
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe'
        ]);
    }

    public function test_registration_fails_with_invalid_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'Password123!@#',
            'password_confirmation' => 'Password123!@#'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $this->assertValidationError($response, 'email');
    }

    public function test_registration_fails_with_weak_password(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $this->assertValidationError($response, 'password');
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        $this->createTestUser(['email' => 'john@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!@#',
            'password_confirmation' => 'Password123!@#',
            'role' => 'ADMIN',
            'telephone' => '1234567890',
            'prefecture' => 'Abidjan',
            'zone' => 'Zone 1'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $this->assertValidationError($response, 'email');
    }

    public function test_user_can_login_successfully(): void
    {
        $user = $this->createTestUser([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!@#')
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'Password123!@#'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $this->assertApiResponse($response);
        
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email'
                ],
                'token',
                'token_type'
            ]
        ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->createTestUser([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!@#')
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Erreur de validation'
        ]);
    }

    public function test_login_fails_with_nonexistent_user(): void
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123!@#'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Erreur de validation'
        ]);
    }

    public function test_user_can_logout_successfully(): void
    {
        $auth = $this->createAuthenticatedUser();

        $response = $this->withHeaders($auth['headers'])
            ->postJson('/api/v1/logout');

        $this->assertApiResponse($response);
        
        // Verify token is revoked
        $this->assertEquals(0, $auth['user']->tokens()->count());
    }

    public function test_logout_fails_without_authentication(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }

    public function test_user_can_get_profile(): void
    {
        $auth = $this->createAuthenticatedUser();

        $response = $this->withHeaders($auth['headers'])
            ->getJson('/api/v1/me');

        $this->assertApiResponse($response);
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'email',
                'created_at'
            ]
        ]);
    }

    public function test_profile_fails_without_authentication(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }
}
