<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    public function test_register_creates_user_successfully(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!@#'
        ];

        $result = $this->authService->register($userData);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('token_type', $result);

        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals('John Doe', $result['user']->name);
        $this->assertEquals('john@example.com', $result['user']->email);
        $this->assertEquals('Bearer', $result['token_type']);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe'
        ]);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!@#'
        ];

        $this->expectException(ValidationException::class);
        $this->authService->register($userData);
    }

    public function test_login_authenticates_user_successfully(): void
    {
        $password = 'Password123!@#';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password)
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => $password
        ];

        $result = $this->authService->login($credentials);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('token_type', $result);

        $this->assertEquals($user->id, $result['user']->id);
        $this->assertEquals('Bearer', $result['token_type']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!@#')
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $this->expectException(\Illuminate\Auth\AuthenticationException::class);
        $this->authService->login($credentials);
    }

    public function test_login_fails_with_nonexistent_user(): void
    {
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123!@#'
        ];

        $this->expectException(\Illuminate\Auth\AuthenticationException::class);
        $this->authService->login($credentials);
    }

    public function test_logout_revokes_tokens(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->assertEquals(1, $user->tokens()->count());

        $result = $this->authService->logout($user);

        $this->assertTrue($result);
        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_get_profile_returns_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $profile = $this->authService->getProfile($user);

        $this->assertEquals($user->id, $profile->id);
        $this->assertEquals('Test User', $profile->name);
        $this->assertEquals('test@example.com', $profile->email);
    }
}
