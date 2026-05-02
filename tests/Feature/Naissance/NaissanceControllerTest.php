<?php

namespace Tests\Feature\Naissance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Naissance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class NaissanceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
    }

    public function test_user_can_create_naissance_successfully(): void
    {
        $auth = $this->createAuthenticatedUser();

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

        $response = $this->withHeaders($auth['headers'])
            ->postJson('/api/naissances', $naissanceData);

        $this->assertApiResponse($response, 201);
        
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'nom_enfant',
                'prenom_enfant',
                'date_naissance',
                'lieu_naissance',
                'nom_mere',
                'nom_pere',
                'sexe',
                'certificat_medical_path',
                'acte_naissance_path',
                'hash_blockchain',
                'created_at'
            ]
        ]);

        $this->assertDatabaseHas('naissances', [
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            'user_id' => $auth['user']->id,
            'sexe' => 'M'
        ]);
    }

    public function test_create_naissance_fails_without_authentication(): void
    {
        $naissanceData = [
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            'date_naissance' => '2024-01-15',
            'lieu_naissance' => 'Paris',
            'nom_mere' => 'Jane Doe',
            'nom_pere' => 'Robert Doe',
            'sexe' => 'M',
        ];

        $response = $this->postJson('/api/naissances', $naissanceData);

        $response->assertStatus(401);
    }

    public function test_create_naissance_fails_with_invalid_data(): void
    {
        $auth = $this->createAuthenticatedUser();

        $invalidData = [
            'nom_enfant' => '',
            'prenom_enfant' => '',
            'date_naissance' => 'invalid-date',
            'sexe' => 'X',
        ];

        $response = $this->withHeaders($auth['headers'])
            ->postJson('/api/naissances', $invalidData);

        $this->assertValidationError($response, 'nom_enfant');
    }

    public function test_create_naissance_fails_with_invalid_file_type(): void
    {
        $auth = $this->createAuthenticatedUser();

        $naissanceData = [
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            'date_naissance' => '2024-01-15',
            'lieu_naissance' => 'Paris',
            'nom_mere' => 'Jane Doe',
            'nom_pere' => 'Robert Doe',
            'sexe' => 'M',
            'certificat_medical' => UploadedFile::fake()->create('certificat.exe', 1024),
        ];

        $response = $this->withHeaders($auth['headers'])
            ->postJson('/api/naissances', $naissanceData);

        $this->assertValidationError($response, 'certificat_medical');
    }

    public function test_user_can_list_their_naissances(): void
    {
        $auth = $this->createAuthenticatedUser();
        
        $naissance1 = Naissance::factory()->create(['user_id' => $auth['user']->id]);
        $naissance2 = Naissance::factory()->create(['user_id' => $auth['user']->id]);
        $naissance3 = Naissance::factory()->create(); // Other user's naissance

        $response = $this->withHeaders($auth['headers'])
            ->getJson('/api/naissances');

        $this->assertApiResponse($response);
        
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'nom_enfant',
                    'prenom_enfant',
                    'date_naissance',
                    'lieu_naissance',
                    'hash_blockchain',
                    'created_at'
                ]
            ]
        ]);
    }

    public function test_user_can_view_their_naissance(): void
    {
        $auth = $this->createAuthenticatedUser();
        $naissance = Naissance::factory()->create(['user_id' => $auth['user']->id]);

        $response = $this->withHeaders($auth['headers'])
            ->getJson("/api/naissances/{$naissance->id}");

        $this->assertApiResponse($response);
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'nom_enfant',
                'prenom_enfant',
                'date_naissance',
                'lieu_naissance',
                'hash_blockchain',
                'created_at'
            ]
        ]);
    }

    public function test_user_cannot_view_others_naissance(): void
    {
        $auth = $this->createAuthenticatedUser();
        $otherUserNaissance = Naissance::factory()->create();

        $response = $this->withHeaders($auth['headers'])
            ->getJson("/api/naissances/{$otherUserNaissance->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_update_their_naissance(): void
    {
        $auth = $this->createAuthenticatedUser();
        $naissance = Naissance::factory()->create(['user_id' => $auth['user']->id]);

        $updateData = [
            'nom_enfant' => 'Updated Name',
            'lieu_naissance' => 'Updated Location',
        ];

        $response = $this->withHeaders($auth['headers'])
            ->putJson("/api/naissances/{$naissance->id}", $updateData);

        $this->assertApiResponse($response);
        
        $this->assertDatabaseHas('naissances', [
            'id' => $naissance->id,
            'nom_enfant' => 'Updated Name',
            'lieu_naissance' => 'Updated Location'
        ]);
    }

    public function test_user_cannot_update_others_naissance(): void
    {
        $auth = $this->createAuthenticatedUser();
        $otherUserNaissance = Naissance::factory()->create();

        $updateData = [
            'nom_enfant' => 'Updated Name',
        ];

        $response = $this->withHeaders($auth['headers'])
            ->putJson("/api/naissances/{$otherUserNaissance->id}", $updateData);

        $response->assertStatus(404);
    }

    public function test_user_can_delete_their_naissance(): void
    {
        $auth = $this->createAuthenticatedUser();
        $naissance = Naissance::factory()->create(['user_id' => $auth['user']->id]);

        $response = $this->withHeaders($auth['headers'])
            ->deleteJson("/api/naissances/{$naissance->id}");

        $this->assertApiResponse($response);
        
        $this->assertDatabaseMissing('naissances', [
            'id' => $naissance->id
        ]);
    }

    public function test_user_cannot_delete_others_naissance(): void
    {
        $auth = $this->createAuthenticatedUser();
        $otherUserNaissance = Naissance::factory()->create();

        $response = $this->withHeaders($auth['headers'])
            ->deleteJson("/api/naissances/{$otherUserNaissance->id}");

        $response->assertStatus(404);
        
        $this->assertDatabaseHas('naissances', [
            'id' => $otherUserNaissance->id
        ]);
    }
}
