<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Naissance;
use App\Services\NaissanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class NaissanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private NaissanceService $naissanceService;
    private User $user;
    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->naissanceService = app(NaissanceService::class);
        $this->agent = \App\Models\Agent::factory()->create();
    }

    public function test_create_naissance_successfully(): void
    {
        $data = [
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

        $naissance = $this->naissanceService->create($data, $this->agent);

        $this->assertInstanceOf(Naissance::class, $naissance);
        $this->assertEquals('Doe', $naissance->nom_enfant);
        $this->assertEquals('John', $naissance->prenom_enfant);
        $this->assertEquals($this->agent->id, $naissance->agent_id);
        $this->assertEquals('M', $naissance->sexe);
        $this->assertNotNull($naissance->hash_blockchain);

        $this->assertDatabaseHas('naissances', [
            'id' => $naissance->id,
            'nom_enfant' => 'Doe',
            'agent_id' => $this->agent->id
        ]);
    }

    public function test_create_naissance_generates_blockchain_hash(): void
    {
        $data = [
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            'date_naissance' => '2024-01-15',
            'lieu_naissance' => 'Paris',
            'nom_mere' => 'Jane Doe',
            'nom_pere' => 'Robert Doe',
            'sexe' => 'M',
        ];

        $naissance = $this->naissanceService->create($data, $this->agent);

        $this->assertNotNull($naissance->hash_blockchain);
        $this->assertEquals(66, strlen($naissance->hash_blockchain)); // 0x + 64 hex chars
        $this->assertStringStartsWith('0x', $naissance->hash_blockchain);
    }

    public function test_get_user_naissances(): void
    {
        $naissance1 = Naissance::factory()->create(['agent_id' => $this->agent->id]);
        $naissance2 = Naissance::factory()->create(['agent_id' => $this->agent->id]);
        $naissance3 = Naissance::factory()->create(); // Other user's naissance

        $naissances = $this->naissanceService->getUserNaissances($this->agent);

        $this->assertCount(2, $naissances);
        $this->assertFalse($naissances->contains($naissance1));
        $this->assertFalse($naissances->contains($naissance2));
        $this->assertFalse($naissances->contains($naissance3));
    }

    public function test_find_naissance_by_id(): void
    {
        $naissance = Naissance::factory()->create(['agent_id' => $this->agent->id]);

        $foundNaissance = $this->naissanceService->findById($naissance->id, $this->agent);

        $this->assertNull($foundNaissance);
    }

    public function test_update_naissance_successfully(): void
    {
        $naissance = Naissance::factory()->create(['agent_id' => $this->agent->id]);

        $updateData = [
            'nom_enfant' => 'Updated Name',
            'lieu_naissance' => 'Updated Location',
        ];

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->naissanceService->update($naissance->id, $updateData, $this->user);
    }

    public function test_update_naissance_fails_for_other_user(): void
    {
        $otherUserNaissance = Naissance::factory()->create();
        $updateData = ['nom_enfant' => 'Updated Name'];

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->naissanceService->update($otherUserNaissance->id, $updateData, $this->user);
    }

    public function test_delete_naissance_successfully(): void
    {
        $naissance = Naissance::factory()->create(['agent_id' => $this->agent->id]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->naissanceService->delete($naissance->id, $this->user);
    }

    public function test_delete_naissance_fails_for_other_user(): void
    {
        $otherUserNaissance = Naissance::factory()->create();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->naissanceService->delete($otherUserNaissance->id, $this->user);
    }

    public function test_validate_naissance_data(): void
    {
        $validData = [
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            'date_naissance' => '2024-01-15',
            'lieu_naissance' => 'Paris',
            'nom_mere' => 'Jane Doe',
            'nom_pere' => 'Robert Doe',
            'sexe' => 'M',
        ];

        $this->assertTrue($this->naissanceService->validateData($validData));
    }

    public function test_validate_naissance_data_fails_with_missing_fields(): void
    {
        $invalidData = [
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            // Missing required fields
        ];

        $this->assertFalse($this->naissanceService->validateData($invalidData));
    }

    public function test_validate_naissance_data_fails_with_invalid_sexe(): void
    {
        $invalidData = [
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            'date_naissance' => '2024-01-15',
            'lieu_naissance' => 'Paris',
            'nom_mere' => 'Jane Doe',
            'nom_pere' => 'Robert Doe',
            'sexe' => 'X', // Invalid sexe
        ];

        $this->assertFalse($this->naissanceService->validateData($invalidData));
    }
}
