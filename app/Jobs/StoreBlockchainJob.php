<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Naissance;
use App\Models\BlockchainRecord;
use App\Services\BlockchainService;

class StoreBlockchainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 180;

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public $failOnTimeout = true;

    protected $naissance;
    protected $blockchainService;

    /**
     * Create a new job instance.
     */
    public function __construct(Naissance $naissance, BlockchainService $blockchainService = null)
    {
        $this->naissance = $naissance;
        $this->blockchainService = $blockchainService;
        
        // Set queue for blockchain operations
        $this->onQueue('blockchain');
    }

    /**
     * Execute the job.
     */
    public function handle(BlockchainService $blockchainService): void
    {
        try {
            // Use injected service or resolve from container
            $service = $this->blockchainService ?: $blockchainService;
            
            // Check cache first
            $cacheKey = "blockchain_stored_{$this->naissance->id}";
            if (Cache::store('naissancechain_blockchain')->has($cacheKey)) {
                Log::info('Blockchain record already stored and cached', [
                    'naissance_id' => $this->naissance->id,
                    'cache_key' => $cacheKey,
                ]);
                return;
            }

            // Mark as processing to prevent duplicate jobs
            $this->markAsProcessing();

            // Use database transaction for atomic operation
            DB::transaction(function () use ($service) {
                // Get previous hash for chaining
                $previousHash = $this->getPreviousHash();
                
                // Generate hash for current record
                $currentHash = $service->generateHash($this->naissance);
                
                // Update naissance with hash information
                $this->naissance->update([
                    'hash_sha256' => $currentHash,
                    'hash_precedent' => $previousHash,
                ]);
                
                // Create blockchain record
                $blockchainRecord = BlockchainRecord::create([
                    'naissance_id' => $this->naissance->id,
                    'hash' => $currentHash,
                    'previous_hash' => $previousHash,
                    'block_number' => $this->getNextBlockNumber(),
                    'data' => json_encode([
                        'naissance_id' => $this->naissance->id,
                        'numero_acte' => $this->naissance->numero_acte,
                        'nom_enfant' => $this->naissance->nom_enfant,
                        'prenom_enfant' => $this->naissance->prenom_enfant,
                        'date_naissance' => $this->naissance->date_naissance,
                        'created_at' => $this->naissance->created_at,
                    ]),
                    'timestamp' => now(),
                ]);
                
                Log::info('Blockchain record created', [
                    'naissance_id' => $this->naissance->id,
                    'blockchain_record_id' => $blockchainRecord->id,
                    'block_number' => $blockchainRecord->block_number,
                    'hash' => substr($currentHash, 0, 20) . '...',
                ]);
                
                // Cache the result
                Cache::store('naissancechain_blockchain')->put(
                    $cacheKey,
                    [
                        'stored_at' => now()->toISOString(),
                        'block_number' => $blockchainRecord->block_number,
                        'hash' => $currentHash,
                        'previous_hash' => $previousHash,
                    ],
                    now()->addDays(90) // Cache for 90 days
                );
            });

            Log::info('Blockchain storage completed successfully', [
                'naissance_id' => $this->naissance->id,
                'completed_at' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to store on blockchain', [
                'naissance_id' => $this->naissance->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);
            
            // Clear processing flag on failure
            $this->clearProcessingFlag();
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Get previous hash for chaining.
     */
    protected function getPreviousHash(): ?string
    {
        // Get the most recent blockchain record
        $lastRecord = BlockchainRecord::orderBy('block_number', 'desc')->first();
        
        return $lastRecord ? $lastRecord->hash : null;
    }

    /**
     * Get next block number.
     */
    protected function getNextBlockNumber(): int
    {
        $lastRecord = BlockchainRecord::orderBy('block_number', 'desc')->first();
        
        return $lastRecord ? $lastRecord->block_number + 1 : 1;
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'blockchain',
            'naissance:' . $this->naissance->id,
            'agent:' . $this->naissance->agent_id,
            'block_number:' . ($this->getNextBlockNumber() - 1),
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(15);
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryAfter(): int
    {
        return $this->attempts() * 90; // 90s, 180s, 270s
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Blockchain storage job failed permanently', [
            'naissance_id' => $this->naissance->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'failed_at' => now()->toISOString(),
        ]);
        
        // Clear processing flag
        $this->clearProcessingFlag();
        
        // Optionally update naissance record to indicate blockchain failure
        $this->naissance->update([
            'hash_sha256' => null,
            'hash_precedent' => null,
        ]);
    }

    /**
     * Get the cache key for this job.
     */
    protected function getCacheKey(): string
    {
        return "blockchain_job_{$this->naissance->id}";
    }

    /**
     * Get the processing cache key.
     */
    protected function getProcessingCacheKey(): string
    {
        return "blockchain_processing_{$this->naissance->id}";
    }

    /**
     * Check if blockchain storage is already being processed.
     */
    public function isAlreadyProcessing(): bool
    {
        $cacheKey = $this->getProcessingCacheKey();
        return Cache::store('naissancechain_blockchain')->has($cacheKey);
    }

    /**
     * Mark job as processing.
     */
    public function markAsProcessing(): void
    {
        $cacheKey = $this->getProcessingCacheKey();
        Cache::store('naissancechain_blockchain')->put(
            $cacheKey,
            [
                'processing' => true,
                'started_at' => now()->toISOString(),
                'naissance_id' => $this->naissance->id,
            ],
            now()->addMinutes(30)
        );
    }

    /**
     * Clear processing flag.
     */
    public function clearProcessingFlag(): void
    {
        $cacheKey = $this->getProcessingCacheKey();
        Cache::store('naissancechain_blockchain')->forget($cacheKey);
    }

    /**
     * Get blockchain statistics for monitoring.
     */
    public static function getBlockchainStats(): array
    {
        return [
            'total_records' => BlockchainRecord::count(),
            'last_block_number' => BlockchainRecord::max('block_number') ?? 0,
            'last_processed_at' => BlockchainRecord::latest()->first()?->timestamp,
            'cache_keys' => [
                'stored_count' => Cache::store('naissancechain_blockchain')->getKeys('blockchain_stored_*')->count(),
                'processing_count' => Cache::store('naissancechain_blockchain')->getKeys('blockchain_processing_*')->count(),
            ],
        ];
    }

    /**
     * Clear blockchain cache for maintenance.
     */
    public static function clearBlockchainCache(): int
    {
        $cache = Cache::store('naissancechain_blockchain');
        
        $storedKeys = $cache->getKeys('blockchain_stored_*');
        $processingKeys = $cache->getKeys('blockchain_processing_*');
        
        foreach ($storedKeys as $key) {
            $cache->forget($key);
        }
        
        foreach ($processingKeys as $key) {
            $cache->forget($key);
        }
        
        return count($storedKeys) + count($processingKeys);
    }
}
