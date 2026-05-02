<?php

namespace App\Services;

use App\Models\Naissance;
use App\Models\Agent;
use App\Models\User;
use App\Jobs\SendNotificationJob;
use App\Jobs\ProcessBulkNotificationsJob;
use App\Jobs\GenerateQrJob;
use App\Jobs\StoreBlockchainJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class NaissanceService
{
    protected QRCodeService $qrCodeService;
    protected BlockchainService $blockchainService;

    public function __construct(QRCodeService $qrCodeService, BlockchainService $blockchainService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->blockchainService = $blockchainService;
    }

    /**
     * Create a new birth record with async processing and caching.
     */
    public function create(array $data, Agent $agent): Naissance
    {
        // Generate unique number with cache
        $data['numero_unique'] = $this->generateUniqueNumberCached();
        
        // Set agent
        $data['agent_id'] = $agent->id;
        
        // Set default status
        $data['statut'] = $data['hors_ligne'] ?? false ? 'en_attente' : 'valide';
        
        // Create birth record
        $naissance = Naissance::create($data);
        
        // Cache the new naissance record
        $this->cacheNaissance($naissance);
        
        // Dispatch async jobs for non-blocking operations
        $this->dispatchAsyncJobs($naissance, $agent);
        
        // Send notifications (still async but separate from heavy operations)
        $this->sendNaissanceCreatedNotification($naissance, $agent);
        
        return $naissance;
    }

    
    /**
     * Verify birth record authenticity.
     */
    public function verify(string $numero): ?array
    {
        $naissance = $this->findByNumero($numero);
        
        if (!$naissance) {
            return null;
        }

        // Verify blockchain integrity
        $blockchainVerification = $this->blockchainService->verifyIntegrity($naissance);

        return [
            'naissance' => $naissance,
            'valide' => $blockchainVerification['verified'],
            'blockchain_status' => $blockchainVerification['status'],
            'message' => $blockchainVerification['message'],
            'blockchain_data' => $blockchainVerification,
        ];
    }

    /**
     * Generate unique number for birth record.
     */
    protected function generateUniqueNumber(): string
    {
        do {
            $numero = 'NC-' . date('Y') . '-' . strtoupper(Str::random(8));
        } while (Naissance::where('numero_unique', $numero)->exists());

        return $numero;
    }

    /**
     * Generate SHA-256 hash for data integrity.
     */
    protected function generateHash(array $data): string
    {
        // Remove fields that shouldn't be part of hash
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
        
        return hash('sha256', json_encode($data, JSON_SORT_KEYS));
    }

    /**
     * Get previous hash for blockchain chaining.
     */
    protected function getPreviousHash(): ?string
    {
        $lastNaissance = Naissance::orderBy('id', 'desc')->first();
        return $lastNaissance ? $lastNaissance->hash_sha256 : null;
    }

    /**
     * Generate QR code for birth record.
     */
    protected function generateQRCode(Naissance $naissance): void
    {
        $verificationUrl = route('verification.show', $naissance->numero_unique);
        $qrCodePath = $this->qrCodeService->generate($verificationUrl, $naissance->numero_unique);
        
        $naissance->qr_code_path = $qrCodePath;
        $naissance->qr_code_url = url(Storage::url($qrCodePath));
        $naissance->save();
    }

    /**
     * Get pending sync records.
     */
    public function getPendingSync(): \Illuminate\Database\Eloquent\Collection
    {
        return Naissance::enAttenteSync()
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Mark record as synchronized.
     */
    public function markAsSynchronized(Naissance $naissance): void
    {
        $naissance->statut = 'synchronise';
        $naissance->synced_at = now();
        $naissance->hors_ligne = false;
        $naissance->save();
    }

    /**
     * Send notification when birth record is created.
     */
    protected function sendNaissanceCreatedNotification(Naissance $naissance, Agent $agent): void
    {
        try {
            // Get users to notify
            $users = $this->getUsersToNotify($naissance, $agent);
            
            foreach ($users as $user) {
                // Dispatch notification job
                SendNotificationJob::dispatch(
                    'naissance_created',
                    $naissance,
                    $user
                );
            }
        } catch (\Exception $e) {
            // Log error but don't fail the creation process
            \Log::error('Failed to send creation notification', [
                'naissance_id' => $naissance->id,
                'agent_id' => $agent->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification when birth record is verified.
     */
    public function sendNaissanceVerifiedNotification(Naissance $naissance, array $verificationResult = null): void
    {
        try {
            // Get users to notify
            $users = $this->getUsersToNotify($naissance, $naissance->agent);
            
            foreach ($users as $user) {
                // Dispatch notification job
                SendNotificationJob::dispatch(
                    'naissance_verified',
                    $naissance,
                    $user,
                    $verificationResult
                );
            }
        } catch (\Exception $e) {
            // Log error but don't fail the verification process
            \Log::error('Failed to send verification notification', [
                'naissance_id' => $naissance->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send bulk notifications to multiple users.
     */
    public function sendBulkNotification(string $type, Naissance $naissance, array $userIds, array $verificationResult = null): void
    {
        try {
            ProcessBulkNotificationsJob::dispatch(
                $type,
                $naissance,
                $userIds,
                $verificationResult
            );
        } catch (\Exception $e) {
            \Log::error('Failed to dispatch bulk notification', [
                'type' => $type,
                'naissance_id' => $naissance->id,
                'user_ids' => $userIds,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get users who should be notified for a birth record.
     */
    protected function getUsersToNotify(Naissance $naissance, Agent $agent): array
    {
        $users = [];
        
        // Add the agent who created the record
        if ($agent->user) {
            $users[] = $agent->user;
        }
        
        // Add admin users
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'ADMIN');
        })->get();
        
        foreach ($adminUsers as $admin) {
            $users[] = $admin;
        }
        
        // Add users from the same prefecture as the birth record
        if (!empty($naissance->agent->prefecture)) {
            $localUsers = User::where('prefecture', $naissance->agent->prefecture)
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['AGENT', 'SANTE', 'ECOLE']);
                })
                ->where('id', '!=', $agent->user_id ?? null)
                ->get();
            
            foreach ($localUsers as $localUser) {
                $users[] = $localUser;
            }
        }
        
        return $users;
    }

    /**
     * Verify birth record and send notification with caching.
     */
    public function verifyAndNotify(Naissance $naissance): array
    {
        // Check cache first
        $cacheKey = "verification_{$naissance->id}";
        $cachedResult = Cache::store('naissancechain_verifications')->get($cacheKey);
        
        if ($cachedResult && $cachedResult['cached_at'] > now()->subMinutes(30)) {
            Log::info('Verification result from cache', [
                'naissance_id' => $naissance->id,
                'cache_age' => now()->diffInMinutes($cachedResult['cached_at']),
            ]);
            
            // Send notification if cache hit
            $this->sendNaissanceVerifiedNotification($naissance, $cachedResult['result']);
            return $cachedResult['result'];
        }
        
        // Perform verification
        $verificationResult = $this->blockchainService->verifyIntegrity($naissance);
        
        // Cache the result for 30 minutes
        Cache::store('naissancechain_verifications')->put(
            $cacheKey,
            [
                'result' => $verificationResult,
                'cached_at' => now(),
            ],
            now()->addMinutes(30)
        );
        
        // Send verification notification
        $this->sendNaissanceVerifiedNotification($naissance, $verificationResult);
        
        return $verificationResult;
    }

    /**
     * Generate unique number with caching.
     */
    protected function generateUniqueNumberCached(): string
    {
        $cacheKey = 'unique_number_counter';
        
        // Simple cache implementation without lock for now
        $counter = Cache::store('naissancechain')->get($cacheKey, 0);
        $counter++;
        
        $uniqueNumber = 'NC-' . date('Y') . '-' . str_pad($counter, 6, '0', STR_PAD_LEFT);
        
        Cache::store('naissancechain')->put($cacheKey, $counter, now()->addYear());
        
        return $uniqueNumber;
    }

    /**
     * Cache naissance record for fast retrieval.
     */
    protected function cacheNaissance(Naissance $naissance): void
    {
        $cacheKey = "naissance_{$naissance->id}";
        $numeroCacheKey = "naissance_numero_{$naissance->numero_unique}";
        
        $cacheData = [
            'id' => $naissance->id,
            'numero_unique' => $naissance->numero_unique,
            'nom_enfant' => $naissance->nom_enfant,
            'prenom_enfant' => $naissance->prenom_enfant,
            'date_naissance' => $naissance->date_naissance,
            'lieu_naissance' => $naissance->lieu_naissance,
            'sexe' => $naissance->sexe,
            'numero_acte' => $naissance->numero_acte,
            'statut' => $naissance->statut,
            'agent_id' => $naissance->agent_id,
            'hash_sha256' => $naissance->hash_sha256,
            'qr_code_url' => $naissance->qr_code_url,
            'created_at' => $naissance->created_at,
            'cached_at' => now(),
        ];
        
        // Cache by ID and by numero_unique
        Cache::store('naissancechain')->put($cacheKey, $cacheData, now()->addHours(24));
        Cache::store('naissancechain')->put($numeroCacheKey, $cacheData, now()->addHours(24));
        
        Log::debug('Naissance cached', [
            'naissance_id' => $naissance->id,
            'cache_keys' => [$cacheKey, $numeroCacheKey],
        ]);
    }

    /**
     * Find birth record by unique number with cache.
     */
    public function findByNumero(string $numero): ?Naissance
    {
        $cacheKey = "naissance_numero_{$numero}";
        
        // Try cache first
        $cached = Cache::store('naissancechain')->get($cacheKey);
        if ($cached) {
            Log::debug('Naissance found in cache', [
                'numero' => $numero,
                'cache_age' => now()->diffInMinutes($cached['cached_at']),
            ]);
            
            return Naissance::find($cached['id']);
        }
        
        // Fallback to database
        $naissance = Naissance::where('numero_unique', $numero)->first();
        
        if ($naissance) {
            $this->cacheNaissance($naissance);
        }
        
        return $naissance;
    }

    /**
     * Get naissance by ID with cache.
     */
    public function findByIdCached(int $id): ?Naissance
    {
        $cacheKey = "naissance_{$id}";
        
        // Try cache first
        $cached = Cache::store('naissancechain')->get($cacheKey);
        if ($cached) {
            Log::debug('Naissance found in cache', [
                'id' => $id,
                'cache_age' => now()->diffInMinutes($cached['cached_at']),
            ]);
            
            return Naissance::find($cached['id']);
        }
        
        // Fallback to database
        $naissance = Naissance::find($id);
        
        if ($naissance) {
            $this->cacheNaissance($naissance);
        }
        
        return $naissance;
    }

    /**
     * Dispatch async jobs for QR code and blockchain processing.
     */
    protected function dispatchAsyncJobs(Naissance $naissance, Agent $agent): void
    {
        try {
            // Check if jobs are already being processed
            $qrJob = new GenerateQrJob($naissance, $this->qrCodeService);
            $blockchainJob = new StoreBlockchainJob($naissance, $this->blockchainService);
            
            // Only dispatch if not already processing
            if (!$qrJob->isAlreadyProcessing()) {
                $qrJob->markAsProcessing();
                GenerateQrJob::dispatch($naissance, $this->qrCodeService);
                Log::info('QR Code generation job dispatched', [
                    'naissance_id' => $naissance->id,
                ]);
            }
            
            if (!$blockchainJob->isAlreadyProcessing()) {
                $blockchainJob->markAsProcessing();
                StoreBlockchainJob::dispatch($naissance, $this->blockchainService);
                Log::info('Blockchain storage job dispatched', [
                    'naissance_id' => $naissance->id,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to dispatch async jobs', [
                'naissance_id' => $naissance->id,
                'error' => $e->getMessage(),
            ]);
            
            // Fallback to synchronous processing if job dispatch fails
            $this->fallbackSynchronousProcessing($naissance);
        }
    }

    /**
     * Fallback synchronous processing for critical operations.
     */
    protected function fallbackSynchronousProcessing(Naissance $naissance): void
    {
        try {
            Log::warning('Using fallback synchronous processing', [
                'naissance_id' => $naissance->id,
            ]);
            
            // Generate QR code synchronously
            $this->generateQRCode($naissance);
            
            // Store on blockchain synchronously
            $this->blockchainService->storeOnChain($naissance);
            
        } catch (\Exception $e) {
            Log::error('Fallback synchronous processing failed', [
                'naissance_id' => $naissance->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear cache for a specific naissance.
     */
    public function clearNaissanceCache(Naissance $naissance): void
    {
        $cacheKeys = [
            "naissance_{$naissance->id}",
            "naissance_numero_{$naissance->numero_unique}",
            "verification_{$naissance->id}",
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::store('naissancechain')->forget($key);
            Cache::store('naissancechain_verifications')->forget($key);
        }
        
        Log::info('Naissance cache cleared', [
            'naissance_id' => $naissance->id,
            'cleared_keys' => $cacheKeys,
        ]);
    }

    /**
     * Get cache statistics for monitoring.
     */
    public function getCacheStats(): array
    {
        return [
            'naissancechain_cache' => [
                'keys_count' => Cache::store('naissancechain')->getKeys('*')->count(),
                'naissance_keys' => Cache::store('naissancechain')->getKeys('naissance_*')->count(),
                'verification_keys' => Cache::store('naissancechain_verifications')->getKeys('*')->count(),
            ],
            'qr_cache' => [
                'generated_keys' => Cache::store('naissancechain_qr')->getKeys('qr_generated_*')->count(),
                'processing_keys' => Cache::store('naissancechain_qr')->getKeys('qr_job_*')->count(),
            ],
            'blockchain_cache' => [
                'stored_keys' => Cache::store('naissancechain_blockchain')->getKeys('blockchain_stored_*')->count(),
                'processing_keys' => Cache::store('naissancechain_blockchain')->getKeys('blockchain_processing_*')->count(),
            ],
        ];
    }

    /**
     * Warm up cache for frequently accessed naissances.
     */
    public function warmUpCache(array $naissanceIds): int
    {
        $warmed = 0;
        
        foreach ($naissanceIds as $id) {
            $naissance = Naissance::find($id);
            if ($naissance) {
                $this->cacheNaissance($naissance);
                $warmed++;
            }
        }
        
        Log::info('Cache warm-up completed', [
            'requested_count' => count($naissanceIds),
            'warmed_count' => $warmed,
        ]);
        
        return $warmed;
    }

    // Additional methods for testing compatibility
    public function getUserNaissances($agent)
    {
        if ($agent instanceof Agent) {
            return Naissance::where('agent_id', $agent->id)->get();
        }
        
        // Fallback for User model if needed
        return Naissance::all();
    }

    public function findById(int $id, $agent = null): ?Naissance
    {
        $query = Naissance::where('id', $id);
        
        if ($agent && $agent instanceof Agent) {
            $query->where('agent_id', $agent->id);
        }
        
        return $query->first();
    }

    public function update(int $id, array $data, $agent = null): Naissance
    {
        $naissance = $this->findById($id, $agent);
        
        if (!$naissance) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Naissance not found');
        }

        $naissance->update($data);
        return $naissance->fresh();
    }

    public function delete(int $id, $agent = null): bool
    {
        $naissance = $this->findById($id, $agent);
        
        if (!$naissance) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Naissance not found');
        }

        return $naissance->delete();
    }

    public function validateData(array $data): bool
    {
        $requiredFields = [
            'nom_enfant',
            'prenom_enfant', 
            'date_naissance',
            'lieu_naissance',
            'nom_pere',
            'nom_mere',
            'sexe'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }

        // Validate sexe
        if (isset($data['sexe']) && !in_array($data['sexe'], ['M', 'F'])) {
            return false;
        }

        // Validate date format
        if (isset($data['date_naissance'])) {
            $date = \DateTime::createFromFormat('Y-m-d', $data['date_naissance']);
            if (!$date || $date->format('Y-m-d') !== $data['date_naissance']) {
                return false;
            }
        }

        return true;
    }
}
