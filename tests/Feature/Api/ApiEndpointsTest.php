<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Naissance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_complete_user_registration_and_login_flow(): void
    {
        // Register user
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!@#',
            'password_confirmation' => 'Password123!@#'
        ];

        $registerResponse = $this->postJson('/api/auth/register', $userData);
        $this->assertApiResponse($registerResponse, 201);

        $token = $registerResponse->json('data.token');
        $headers = ['Authorization' => 'Bearer ' . $token];

        // Login with same credentials
        $loginData = [
            'email' => 'john@example.com',
            'password' => 'Password123!@#'
        ];

        $loginResponse = $this->postJson('/api/auth/login', $loginData);
        $this->assertApiResponse($loginResponse);

        // Get profile
        $profileResponse = $this->withHeaders($headers)
            ->getJson('/api/auth/profile');
        $this->assertApiResponse($profileResponse);

        $this->assertEquals('john@example.com', $profileResponse->json('data.email'));
    }

    public function test_complete_naissance_creation_and_verification_flow(): void
    {
        // Create and authenticate user
        $auth = $this->createAuthenticatedUser();

        // Create naissance
        $naissanceData = [
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            'date_naissance' => '2024-01-15',
            'lieu_naissance' => 'Paris',
            'nom_mere' => 'Jane Doe',
            'nom_pere' => 'Robert Doe',
            'sexe' => 'M',
            'certificat_medical' => UploadedFile::fake()->create('certificat.pdf', 1024),
            'acte_naissance' => UploadedFile::fake()->create('acte.pdf', 2048),
        ];

        $createResponse = $this->withHeaders($auth['headers'])
            ->postJson('/api/naissances', $naissanceData);
        $this->assertApiResponse($createResponse, 201);

        $naissanceId = $createResponse->json('data.id');
        $blockchainHash = $createResponse->json('data.hash_blockchain');

        // Verify naissance in blockchain
        $verifyResponse = $this->withHeaders($auth['headers'])
            ->postJson("/api/blockchain/verify/{$naissanceId}");
        $this->assertApiResponse($verifyResponse);

        $this->assertEquals($blockchainHash, $verifyResponse->json('data.hash_blockchain'));

        // Get blockchain info
        $infoResponse = $this->withHeaders($auth['headers'])
            ->getJson("/api/blockchain/info/{$naissanceId}");
        $this->assertApiResponse($infoResponse);

        // List user's naissances
        $listResponse = $this->withHeaders($auth['headers'])
            ->getJson('/api/naissances');
        $this->assertApiResponse($listResponse);

        $this->assertCount(1, $listResponse->json('data'));
    }

    public function test_api_rate_limiting(): void
    {
        $auth = $this->createAuthenticatedUser();

        // Make multiple requests to trigger rate limiting
        $responses = [];
        for ($i = 0; $i < 65; $i++) {
            $responses[] = $this->withHeaders($auth['headers'])
                ->getJson('/api/naissances');
        }

        // First 60 should succeed
        for ($i = 0; $i < 60; $i++) {
            $this->assertApiResponse($responses[$i]);
        }

        // Requests 61+ should be rate limited
        for ($i = 60; $i < 65; $i++) {
            $responses[$i]->assertStatus(429);
            $responses[$i]->assertJson(['success' => false]);
        }
    }

    public function test_api_cors_headers(): void
    {
        $response = $this->options('/api/auth/register');
        
        $response->assertHeader('Access-Control-Allow-Origin');
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Allow-Headers');
        $response->assertHeader('Access-Control-Max-Age');
    }

    public function test_api_security_headers(): void
    {
        $response = $this->getJson('/api/auth/profile');
        
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Content-Security-Policy');
    }

    public function test_api_validation_errors_format(): void
    {
        // Test registration validation
        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'weak',
            'password_confirmation' => 'different'
        ];

        $response = $this->postJson('/api/auth/register', $invalidData);

        $this->assertValidationError($response, 'name');
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'name',
                'email',
                'password',
                'password_confirmation'
            ]
        ]);
    }

    public function test_api_not_found_response(): void
    {
        $auth = $this->createAuthenticatedUser();

        $response = $this->withHeaders($auth['headers'])
            ->getJson('/api/naissances/999');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Resource not found'
        ]);
    }

    public function test_api_unauthorized_response(): void
    {
        $response = $this->getJson('/api/auth/profile');

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Unauthenticated'
        ]);
    }

    public function test_api_forbidden_response(): void
    {
        $auth1 = $this->createAuthenticatedUser();
        $auth2 = $this->createAuthenticatedUser();
        
        $naissance = Naissance::factory()->create(['user_id' => $auth2['user']->id]);

        $response = $this->withHeaders($auth1['headers'])
            ->getJson("/api/naissances/{$naissance->id}");

        $response->assertStatus(404); // Returns 404 instead of 403 for security
    }

    public function test_api_server_error_handling(): void
    {
        // This test would need to trigger an actual server error
        // For now, we'll test the error format
        $response = $this->getJson('/api/nonexistent-endpoint');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false
        ]);
    }

    public function test_api_json_response_format(): void
    {
        $auth = $this->createAuthenticatedUser();

        $response = $this->withHeaders($auth['headers'])
            ->getJson('/api/naissances');

        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'success',
            'data' => []
        ]);
    }

    public function test_api_pagination_format(): void
    {
        $auth = $this->createAuthenticatedUser();
        
        // Create multiple naissances
        Naissance::factory()->count(15)->create(['user_id' => $auth['user']->id]);

        $response = $this->withHeaders($auth['headers'])
            ->getJson('/api/naissances?page=1&per_page=10');

        $this->assertApiResponse($response);
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'nom_enfant',
                    'prenom_enfant',
                    'date_naissance',
                    'hash_blockchain',
                    'created_at'
                ]
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next'
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'per_page',
                'to',
                'total'
            ]
        ]);
    }
}
