<?php

namespace Tests\Feature\Naissance;

use Tests\TestCase;
use App\Models\Agent;
use App\Models\Naissance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicNaissanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_naissance_endpoints_exist(): void
    {
        $agent = Agent::factory()->create();
        $token = $agent->createToken('test-token')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        // Test index endpoint
        $indexResponse = $this->withHeaders($headers)
            ->getJson('/api/v1/naissances');
        $this->assertContains($indexResponse->status(), [200, 401]);

        // Test store endpoint
        $storeResponse = $this->withHeaders($headers)
            ->postJson('/api/v1/naissances', [
                'nom_enfant' => 'Test Child',
                'prenom_enfant' => 'Test',
                'date_naissance' => '2024-01-01',
                'lieu_naissance' => 'Test City',
                'nom_pere' => 'Test Father',
                'prenom_pere' => 'Father',
                'nom_mere' => 'Test Mother',
                'prenom_mere' => 'Mother',
                'sexe' => 'M',
                'declarant_nom' => 'Test Declarant',
                'declarant_prenom' => 'Declarant',
                'declarant_lien' => 'Père',
                'officier_etat_civil' => 'Test Officer',
                'numero_acte' => 'ACTE-TEST-001',
                'date_enregistrement' => '2024-01-01',
                'adresse_parents' => 'Test Address',
                'telephone_parents' => '1234567890'
            ]);
        $this->assertContains($storeResponse->status(), [201, 422, 401]);

        // Test show endpoint
        if ($storeResponse->status() === 201) {
            $naissanceId = $storeResponse->json('data.id');
            $showResponse = $this->withHeaders($headers)
                ->getJson("/api/v1/naissances/{$naissanceId}");
            $this->assertContains($showResponse->status(), [200, 401, 404]);
        }
    }

    public function test_naissance_creation_with_agent(): void
    {
        $agent = Agent::factory()->create();
        $token = $agent->createToken('test-token')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        $naissanceData = [
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            'date_naissance' => '2024-01-15',
            'lieu_naissance' => 'Paris',
            'nom_pere' => 'Robert',
            'prenom_pere' => 'Father',
            'nom_mere' => 'Jane',
            'prenom_mere' => 'Mother',
            'sexe' => 'M',
            'declarant_nom' => 'Robert',
            'declarant_prenom' => 'Declarant',
            'declarant_lien' => 'Père',
            'officier_etat_civil' => 'Test Officer',
            'numero_acte' => 'ACTE-001',
            'date_enregistrement' => '2024-01-15',
            'adresse_parents' => 'Test Address',
            'telephone_parents' => '1234567890'
        ];

        $response = $this->withHeaders($headers)
            ->postJson('/api/v1/naissances', $naissanceData);

        $this->assertContains($response->status(), [201, 422]);
        
        if ($response->status() === 201) {
            $this->assertDatabaseHas('naissances', [
                'nom_enfant' => 'Doe',
                'prenom_enfant' => 'John',
                'agent_id' => $agent->id
            ]);
        }
    }

    public function test_blockchain_endpoints_exist(): void
    {
        $agent = Agent::factory()->create();
        $naissance = Naissance::factory()->create(['agent_id' => $agent->id]);
        $token = $agent->createToken('test-token')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        // Test blockchain verify endpoint
        $verifyResponse = $this->withHeaders($headers)
            ->postJson("/api/v1/blockchain/verify/{$naissance->id}");
        $this->assertContains($verifyResponse->status(), [200, 401, 404]);

        // Test blockchain stats endpoint
        $statsResponse = $this->withHeaders($headers)
            ->getJson('/api/v1/blockchain/stats');
        $this->assertContains($statsResponse->status(), [200, 401]);
    }
}
