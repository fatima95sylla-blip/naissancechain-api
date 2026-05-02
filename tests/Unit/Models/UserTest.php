<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNotNull($user->password);
    }

    public function test_user_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['email' => 'test@example.com']);
    }

    public function test_user_can_have_naissances(): void
    {
        $user = User::factory()->create();
        $naissance = $user->naissances()->create([
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            'date_naissance' => '2024-01-15',
            'lieu_naissance' => 'Paris',
            'nom_mere' => 'Jane',
            'nom_pere' => 'Robert',
            'sexe' => 'M',
            'hash_blockchain' => '0x1234567890abcdef1234567890abcdef12345678'
        ]);

        $this->assertCount(1, $user->naissances);
        $this->assertEquals('Doe', $user->naissances->first()->nom_enfant);
    }

    public function test_user_can_create_tokens(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $this->assertNotNull($token->accessToken);
        $this->assertEquals('test-token', $token->accessToken->name);
    }

    public function test_user_password_is_hashed(): void
    {
        $password = 'Password123!';
        $user = User::factory()->create(['password' => $password]);

        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(\Hash::check($password, $user->password));
    }
}
