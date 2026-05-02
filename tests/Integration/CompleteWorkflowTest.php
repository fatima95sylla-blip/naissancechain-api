<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Naissance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessNaissanceBlockchain;

class CompleteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Queue::fake();
    }

    public function test_complete_user_journey_from_registration_to_blockchain_verification(): void
    {
        // Step 1: User Registration
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
        $userId = $registerResponse->json('data.user.id');

        // Step 2: Verify user exists in database
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'email' => 'john@example.com',
            'name' => 'John Doe'
        ]);

        // Step 3: Create Naissance Record
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

        $createNaissanceResponse = $this->withHeaders($headers)
            ->postJson('/api/naissances', $naissanceData);
        $this->assertApiResponse($createNaissanceResponse, 201);

        $naissanceId = $createNaissanceResponse->json('data.id');
        $blockchainHash = $createNaissanceResponse->json('data.hash_blockchain');

        // Step 4: Verify naissance exists in database
        $this->assertDatabaseHas('naissances', [
            'id' => $naissanceId,
            'user_id' => $userId,
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            'sexe' => 'M',
            'hash_blockchain' => $blockchainHash
        ]);

        // Step 5: List User's Naissances
        $listResponse = $this->withHeaders($headers)
            ->getJson('/api/naissances');
        $this->assertApiResponse($listResponse);

        $this->assertCount(1, $listResponse->json('data'));
        $this->assertEquals($naissanceId, $listResponse->json('data.0.id'));

        // Step 6: Get Naissance Details
        $detailsResponse = $this->withHeaders($headers)
            ->getJson("/api/naissances/{$naissanceId}");
        $this->assertApiResponse($detailsResponse);

        $this->assertEquals('Doe', $detailsResponse->json('data.nom_enfant'));
        $this->assertEquals($blockchainHash, $detailsResponse->json('data.hash_blockchain'));

        // Step 7: Blockchain Verification
        $verifyResponse = $this->withHeaders($headers)
            ->postJson("/api/blockchain/verify/{$naissanceId}");
        $this->assertApiResponse($verifyResponse);

        $this->assertEquals($naissanceId, $verifyResponse->json('data.naissance_id'));
        $this->assertEquals($blockchainHash, $verifyResponse->json('data.hash_blockchain'));
        $this->assertTrue($verifyResponse->json('data.is_valid'));

        // Step 8: Get Blockchain Info
        $blockchainInfoResponse = $this->withHeaders($headers)
            ->getJson("/api/blockchain/info/{$naissanceId}");
        $this->assertApiResponse($blockchainInfoResponse);

        $this->assertArrayHasKey('blockchain_data', $blockchainInfoResponse->json('data'));

        // Step 9: Get Transaction History
        $transactionResponse = $this->withHeaders($headers)
            ->getJson('/api/blockchain/transactions');
        $this->assertApiResponse($transactionResponse);

        $this->assertArrayHasKey('transactions', $transactionResponse->json('data'));

        // Step 10: Update Naissance
        $updateData = [
            'lieu_naissance' => 'Lyon',
            'nom_pere' => 'Robert Doe Sr.'
        ];

        $updateResponse = $this->withHeaders($headers)
            ->putJson("/api/naissances/{$naissanceId}", $updateData);
        $this->assertApiResponse($updateResponse);

        $this->assertEquals('Lyon', $updateResponse->json('data.lieu_naissance'));

        // Step 11: Verify Update in Database
        $this->assertDatabaseHas('naissances', [
            'id' => $naissanceId,
            'lieu_naissance' => 'Lyon',
            'nom_pere' => 'Robert Doe Sr.'
        ]);

        // Step 12: Batch Verification
        $batchResponse = $this->withHeaders($headers)
            ->postJson('/api/blockchain/batch-verify', [
                'naissance_ids' => [$naissanceId]
            ]);
        $this->assertApiResponse($batchResponse);

        $this->assertEquals(1, $batchResponse->json('data.summary.total_verified'));
        $this->assertEquals(1, $batchResponse->json('data.summary.valid_count'));

        // Step 13: Get User Profile
        $profileResponse = $this->withHeaders($headers)
            ->getJson('/api/auth/profile');
        $this->assertApiResponse($profileResponse);

        $this->assertEquals('john@example.com', $profileResponse->json('data.email'));

        // Step 14: Logout
        $logoutResponse = $this->withHeaders($headers)
            ->postJson('/api/auth/logout');
        $this->assertApiResponse($logoutResponse);

        // Step 15: Verify Token is Revoked
        $this->assertEquals(0, User::find($userId)->tokens()->count());

        // Step 16: Attempt to Access Protected Resource
        $protectedResponse = $this->withHeaders($headers)
            ->getJson('/api/naissances');
        $protectedResponse->assertStatus(401);

        // Step 17: Login Again
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!@#'
        ]);
        $this->assertApiResponse($loginResponse);

        $newToken = $loginResponse->json('data.token');
        $newHeaders = ['Authorization' => 'Bearer ' . $newToken];

        // Step 18: Verify Access with New Token
        $accessResponse = $this->withHeaders($newHeaders)
            ->getJson('/api/naissances');
        $this->assertApiResponse($accessResponse);

        // Step 19: Delete Naissance
        $deleteResponse = $this->withHeaders($newHeaders)
            ->deleteJson("/api/naissances/{$naissanceId}");
        $this->assertApiResponse($deleteResponse);

        // Step 20: Verify Deletion
        $this->assertDatabaseMissing('naissances', ['id' => $naissanceId]);
    }

    public function test_multi_user_isolation(): void
    {
        // Create two users
        $auth1 = $this->createAuthenticatedUser(['email' => 'user1@example.com']);
        $auth2 = $this->createAuthenticatedUser(['email' => 'user2@example.com']);

        // User 1 creates naissance
        $naissance1 = Naissance::factory()->create([
            'user_id' => $auth1['user']->id,
            'nom_enfant' => 'Child1'
        ]);

        // User 2 creates naissance
        $naissance2 = Naissance::factory()->create([
            'user_id' => $auth2['user']->id,
            'nom_enfant' => 'Child2'
        ]);

        // User 1 can only see their naissance
        $user1Naissances = $this->withHeaders($auth1['headers'])
            ->getJson('/api/naissances');
        $this->assertApiResponse($user1Naissances);
        $this->assertCount(1, $user1Naissances->json('data'));
        $this->assertEquals('Child1', $user1Naissances->json('data.0.nom_enfant'));

        // User 2 can only see their naissance
        $user2Naissances = $this->withHeaders($auth2['headers'])
            ->getJson('/api/naissances');
        $this->assertApiResponse($user2Naissances);
        $this->assertCount(1, $user2Naissances->json('data'));
        $this->assertEquals('Child2', $user2Naissances->json('data.0.nom_enfant'));

        // User 1 cannot access User 2's naissance
        $accessResponse = $this->withHeaders($auth1['headers'])
            ->getJson("/api/naissances/{$naissance2->id}");
        $accessResponse->assertStatus(404);

        // User 2 cannot access User 1's naissance
        $accessResponse = $this->withHeaders($auth2['headers'])
            ->getJson("/api/naissances/{$naissance1->id}");
        $accessResponse->assertStatus(404);
    }

    public function test_error_handling_workflow(): void
    {
        // Test invalid registration
        $invalidResponse = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'invalid',
            'password' => 'weak'
        ]);
        $this->assertValidationError($invalidResponse, 'name');

        // Test invalid login
        $this->createTestUser(['email' => 'test@example.com']);
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong'
        ]);
        $loginResponse->assertStatus(401);

        // Test unauthorized access
        $unauthResponse = $this->getJson('/api/naissances');
        $unauthResponse->assertStatus(401);

        // Test not found
        $auth = $this->createAuthenticatedUser();
        $notFoundResponse = $this->withHeaders($auth['headers'])
            ->getJson('/api/naissances/999');
        $notFoundResponse->assertStatus(404);
    }

    public function test_security_features_workflow(): void
    {
        $auth = $this->createAuthenticatedUser();

        // Test XSS protection
        $xssData = [
            'nom_enfant' => '<script>alert("xss")</script>Child',
            'prenom_enfant' => 'John',
            'date_naissance' => '2024-01-15',
            'lieu_naissance' => 'Paris',
            'nom_mere' => 'Jane',
            'nom_pere' => 'Robert',
            'sexe' => 'M'
        ];

        $xssResponse = $this->withHeaders($auth['headers'])
            ->postJson('/api/naissances', $xssData);
        
        // Should be sanitized or rejected
        if ($xssResponse->status() === 422) {
            $this->assertValidationError($xssResponse, 'nom_enfant');
        } else {
            $this->assertApiResponse($xssResponse, 201);
            // Verify XSS was sanitized
            $this->assertStringNotContainsString('<script>', 
                Naissance::first()->nom_enfant);
        }

        // Test rate limiting
        $responses = [];
        for ($i = 0; $i < 65; $i++) {
            $responses[] = $this->withHeaders($auth['headers'])
                ->getJson('/api/naissances');
        }

        // Should trigger rate limiting
        $rateLimitHit = false;
        foreach ($responses as $response) {
            if ($response->status() === 429) {
                $rateLimitHit = true;
                break;
            }
        }
        $this->assertTrue($rateLimitHit, 'Rate limiting should be triggered');
    }
}
