<?php

namespace Tests\Feature\Blockchain;

use Tests\TestCase;
use App\Models\User;
use App\Models\Naissance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BlockchainControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_verify_naissance_blockchain(): void
    {
        $auth = $this->createAuthenticatedUser();
        $naissance = Naissance::factory()->create([
            'user_id' => $auth['user']->id,
            'hash_blockchain' => '0x1234567890abcdef1234567890abcdef12345678'
        ]);

        $response = $this->withHeaders($auth['headers'])
            ->postJson("/api/blockchain/verify/{$naissance->id}");

        $this->assertApiResponse($response);
        
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'naissance_id',
                'hash_blockchain',
                'is_valid',
                'verification_timestamp',
                'block_number',
                'transaction_hash'
            ]
        ]);
    }

    public function test_verification_fails_for_nonexistent_naissance(): void
    {
        $auth = $this->createAuthenticatedUser();

        $response = $this->withHeaders($auth['headers'])
            ->postJson('/api/blockchain/verify/999');

        $response->assertStatus(404);
    }

    public function test_user_cannot_verify_others_naissance(): void
    {
        $auth = $this->createAuthenticatedUser();
        $otherUserNaissance = Naissance::factory()->create([
            'hash_blockchain' => '0x1234567890abcdef1234567890abcdef12345678'
        ]);

        $response = $this->withHeaders($auth['headers'])
            ->postJson("/api/blockchain/verify/{$otherUserNaissance->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_get_blockchain_info(): void
    {
        $auth = $this->createAuthenticatedUser();
        $naissance = Naissance::factory()->create([
            'user_id' => $auth['user']->id,
            'hash_blockchain' => '0x1234567890abcdef1234567890abcdef12345678'
        ]);

        $response = $this->withHeaders($auth['headers'])
            ->getJson("/api/blockchain/info/{$naissance->id}");

        $this->assertApiResponse($response);
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                'naissance_id',
                'hash_blockchain',
                'blockchain_data' => [
                    'block_number',
                    'block_hash',
                    'transaction_hash',
                    'timestamp',
                    'gas_used',
                    'status'
                ]
            ]
        ]);
    }

    public function test_blockchain_info_fails_without_authentication(): void
    {
        $naissance = Naissance::factory()->create();

        $response = $this->getJson("/api/blockchain/info/{$naissance->id}");

        $response->assertStatus(401);
    }

    public function test_user_can_get_transaction_history(): void
    {
        $auth = $this->createAuthenticatedUser();
        $naissance = Naissance::factory()->create(['user_id' => $auth['user']->id]);

        $response = $this->withHeaders($auth['headers'])
            ->getJson('/api/blockchain/transactions');

        $this->assertApiResponse($response);
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                'transactions' => [
                    '*' => [
                        'transaction_hash',
                        'block_number',
                        'from_address',
                        'to_address',
                        'gas_used',
                        'timestamp',
                        'status'
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ]
            ]
        ]);
    }

    public function test_transaction_history_pagination(): void
    {
        $auth = $this->createAuthenticatedUser();

        $response = $this->withHeaders($auth['headers'])
            ->getJson('/api/blockchain/transactions?page=1&per_page=5');

        $this->assertApiResponse($response);
        
        $response->assertJsonPath('data.pagination.per_page', 5);
        $response->assertJsonPath('data.pagination.current_page', 1);
    }

    public function test_user_can_get_blockchain_stats(): void
    {
        $auth = $this->createAuthenticatedUser();

        $response = $this->withHeaders($auth['headers'])
            ->getJson('/api/blockchain/stats');

        $this->assertApiResponse($response);
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_transactions',
                'total_blocks',
                'latest_block_number',
                'network_status',
                'gas_price',
                'average_block_time'
            ]
        ]);
    }

    public function test_user_can_validate_multiple_naissances(): void
    {
        $auth = $this->createAuthenticatedUser();
        
        $naissance1 = Naissance::factory()->create([
            'user_id' => $auth['user']->id,
            'hash_blockchain' => '0x1111111111111111111111111111111111111111'
        ]);
        $naissance2 = Naissance::factory()->create([
            'user_id' => $auth['user']->id,
            'hash_blockchain' => '0x2222222222222222222222222222222222222222'
        ]);

        $requestData = [
            'naissance_ids' => [$naissance1->id, $naissance2->id]
        ];

        $response = $this->withHeaders($auth['headers'])
            ->postJson('/api/blockchain/batch-verify', $requestData);

        $this->assertApiResponse($response);
        
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'verifications' => [
                    '*' => [
                        'naissance_id',
                        'hash_blockchain',
                        'is_valid',
                        'verification_timestamp'
                    ]
                ],
                'summary' => [
                    'total_verified',
                    'valid_count',
                    'invalid_count'
                ]
            ]
        ]);
    }

    public function test_batch_verify_fails_with_invalid_ids(): void
    {
        $auth = $this->createAuthenticatedUser();

        $requestData = [
            'naissance_ids' => [999, 1000]
        ];

        $response = $this->withHeaders($auth['headers'])
            ->postJson('/api/blockchain/batch-verify', $requestData);

        $this->assertValidationError($response, 'naissance_ids');
    }

    public function test_user_can_get_contract_info(): void
    {
        $auth = $this->createAuthenticatedUser();

        $response = $this->withHeaders($auth['headers'])
            ->getJson('/api/blockchain/contract');

        $this->assertApiResponse($response);
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                'contract_address',
                'contract_name',
                'contract_symbol',
                'total_supply',
                'decimals',
                'owner_address',
                'deployment_block'
            ]
        ]);
    }

    public function test_network_status_endpoint(): void
    {
        $auth = $this->createAuthenticatedUser();

        $response = $this->withHeaders($auth['headers'])
            ->getJson('/api/blockchain/network');

        $this->assertApiResponse($response);
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                'network_name',
                'chain_id',
                'block_number',
                'gas_price',
                'sync_status',
                'peer_count'
            ]
        ]);
    }
}
