<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Naissance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NaissanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_naissance_can_be_created(): void
    {
        $user = User::factory()->create();
        $naissance = Naissance::factory()->create([
            'user_id' => $user->id,
            'nom_enfant' => 'Doe',
            'prenom_enfant' => 'John',
            'date_naissance' => '2024-01-15',
        ]);

        $this->assertInstanceOf(Naissance::class, $naissance);
        $this->assertEquals('Doe', $naissance->nom_enfant);
        $this->assertEquals('John', $naissance->prenom_enfant);
        $this->assertEquals($user->id, $naissance->user_id);
    }

    public function test_naissance_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $naissance = Naissance::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $naissance->user);
        $this->assertEquals($user->id, $naissance->user->id);
    }

    public function test_naissance_hash_blockchain_is_required(): void
    {
        $user = User::factory()->create();
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Naissance::factory()->create([
            'user_id' => $user->id,
            'hash_blockchain' => null
        ]);
    }

    public function test_naissance_sexe_must_be_valid(): void
    {
        $user = User::factory()->create();
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Naissance::factory()->create([
            'user_id' => $user->id,
            'sexe' => 'X'
        ]);
    }

    public function test_naissance_date_naissance_is_cast_to_date(): void
    {
        $user = User::factory()->create();
        $naissance = Naissance::factory()->create([
            'user_id' => $user->id,
            'date_naissance' => '2024-01-15'
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $naissance->date_naissance);
        $this->assertEquals('2024-01-15', $naissance->date_naissance->format('Y-m-d'));
    }

    public function test_naissance_scope_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $naissance1 = Naissance::factory()->create(['user_id' => $user1->id]);
        $naissance2 = Naissance::factory()->create(['user_id' => $user1->id]);
        $naissance3 = Naissance::factory()->create(['user_id' => $user2->id]);

        $user1Naissances = Naissance::byUser($user1->id)->get();
        $user2Naissances = Naissance::byUser($user2->id)->get();

        $this->assertCount(2, $user1Naissances);
        $this->assertCount(1, $user2Naissances);
    }
}
