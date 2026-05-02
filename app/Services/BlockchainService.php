<?php

namespace App\Services;

use App\Models\Naissance;
use App\Models\BlockchainRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BlockchainService
{
    /**
     * Generate SHA-256 hash from naissance data.
     */
    public function generateHash(Naissance $naissance): string
    {
        $dataString = $this->buildDataString($naissance);
        return hash('sha256', $dataString);
    }

    /**
     * Build data string for hashing.
     */
    private function buildDataString(Naissance $naissance): string
    {
        return implode('|', [
            $naissance->nom_enfant,
            $naissance->prenom_enfant,
            $naissance->date_naissance,
            $naissance->lieu_naissance,
            $naissance->sexe,
            $naissance->nom_pere,
            $naissance->prenom_pere,
            $naissance->nom_mere,
            $naissance->prenom_mere,
            $naissance->date_enregistrement,
        ]);
    }

    /**
     * Store naissance record on blockchain.
     */
    public function storeOnChain(Naissance $naissance): BlockchainRecord
    {
        return DB::transaction(function () use ($naissance) {
            $lastRecord = BlockchainRecord::ordered()->latest()->first();
            $blockNumber = $lastRecord ? $lastRecord->block_number + 1 : 1;
            $previousHash = $lastRecord ? $lastRecord->hash : '0';
            
            $hash = $this->generateHash($naissance);
            $dataSignature = $this->generateDataSignature($naissance, $previousHash);
            
            $record = BlockchainRecord::create([
                'naissance_id' => $naissance->id,
                'hash' => $hash,
                'previous_hash' => $previousHash,
                'timestamp' => now(),
                'block_number' => $blockNumber,
                'data_signature' => $dataSignature,
                'verified' => true,
            ]);

            // Update naissance with blockchain hash
            $naissance->update(['blockchain_hash' => $hash]);

            Log::info('Blockchain record created', [
                'naissance_id' => $naissance->id,
                'hash' => $hash,
                'block_number' => $blockNumber,
            ]);

            return $record;
        });
    }

    /**
     * Generate data signature for additional security.
     */
    private function generateDataSignature(Naissance $naissance, string $previousHash): string
    {
        $data = $this->buildDataString($naissance) . $previousHash . now()->timestamp;
        return hash('sha256', $data);
    }

    /**
     * Verify integrity of a naissance record.
     */
    public function verifyIntegrity(Naissance $naissance): array
    {
        $blockchainRecord = BlockchainRecord::where('naissance_id', $naissance->id)->first();
        
        if (!$blockchainRecord) {
            return [
                'status' => 'not_found',
                'message' => 'Enregistrement blockchain non trouvé',
                'verified' => false,
            ];
        }

        $currentHash = $this->generateHash($naissance);
        $storedHash = $blockchainRecord->hash;
        
        if ($currentHash !== $storedHash) {
            Log::warning('Data tampering detected', [
                'naissance_id' => $naissance->id,
                'current_hash' => $currentHash,
                'stored_hash' => $storedHash,
            ]);

            return [
                'status' => 'tampered',
                'message' => 'Données modifiées - Hash non correspondant',
                'verified' => false,
                'current_hash' => $currentHash,
                'stored_hash' => $storedHash,
            ];
        }

        // Verify chain integrity
        $chainValid = $this->verifyChainIntegrity($blockchainRecord);
        
        if (!$chainValid) {
            return [
                'status' => 'chain_broken',
                'message' => 'Chaîne blockchain corrompue',
                'verified' => false,
            ];
        }

        return [
            'status' => 'verified',
            'message' => 'Données intactes et vérifiées',
            'verified' => true,
            'block_number' => $blockchainRecord->block_number,
            'timestamp' => $blockchainRecord->timestamp,
            'hash' => $storedHash,
        ];
    }

    /**
     * Verify the integrity of the entire chain.
     */
    private function verifyChainIntegrity(BlockchainRecord $record): bool
    {
        $current = $record;
        
        while ($current && $current->previous_hash !== '0') {
            $previous = $current->previousRecord();
            
            if (!$previous || $current->previous_hash !== $previous->hash) {
                Log::error('Chain integrity broken', [
                    'current_id' => $current->id,
                    'current_previous_hash' => $current->previous_hash,
                    'previous_hash' => $previous?->hash,
                ]);
                return false;
            }
            
            $current = $previous;
        }
        
        return true;
    }

    /**
     * Get blockchain statistics.
     */
    public function getStats(): array
    {
        $totalRecords = BlockchainRecord::count();
        $verifiedRecords = BlockchainRecord::verified()->count();
        $latestBlock = BlockchainRecord::ordered()->latest()->first();
        
        return [
            'total_records' => $totalRecords,
            'verified_records' => $verifiedRecords,
            'latest_block_number' => $latestBlock?->block_number ?? 0,
            'chain_integrity' => $this->verifyFullChainIntegrity(),
        ];
    }

    /**
     * Verify full chain integrity.
     */
    private function verifyFullChainIntegrity(): bool
    {
        $records = BlockchainRecord::ordered()->get();
        
        foreach ($records as $record) {
            if (!$record->isValidChain()) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get blockchain records by date range.
     */
    public function getRecordsByDateRange(string $startDate, string $endDate): array
    {
        return BlockchainRecord::whereBetween('timestamp', [$startDate, $endDate])
            ->with('naissance')
            ->ordered()
            ->get()
            ->map(function ($record) {
                return [
                    'block_number' => $record->block_number,
                    'hash' => $record->hash,
                    'timestamp' => $record->timestamp,
                    'naissance_id' => $record->naissance_id,
                    'verified' => $record->verified,
                ];
            })
            ->toArray();
    }
}
