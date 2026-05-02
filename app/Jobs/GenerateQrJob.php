<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Naissance;
use App\Services\QRCodeService;

class GenerateQrJob implements ShouldQueue
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
    public $timeout = 120;

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public $failOnTimeout = true;

    protected $naissance;
    protected $qrCodeService;

    /**
     * Create a new job instance.
     */
    public function __construct(Naissance $naissance, QRCodeService $qrCodeService = null)
    {
        $this->naissance = $naissance;
        $this->qrCodeService = $qrCodeService;
        
        // Set queue for QR generation
        $this->onQueue('qr_generation');
    }

    /**
     * Execute the job.
     */
    public function handle(QRCodeService $qrCodeService): void
    {
        try {
            // Use injected service or resolve from container
            $qrService = $this->qrCodeService ?: $qrCodeService;
            
            // Check cache first
            $cacheKey = "qr_generated_{$this->naissance->id}";
            if (Cache::store('naissancechain_qr')->has($cacheKey)) {
                Log::info('QR Code already generated and cached', [
                    'naissance_id' => $this->naissance->id,
                    'cache_key' => $cacheKey,
                ]);
                return;
            }

            // Generate QR code
            $qrData = $qrService->generate($this->naissance);
            
            // Store QR code file
            $this->storeQrCode($qrData);
            
            // Update naissance record
            $this->updateNaissanceRecord($qrData);
            
            // Cache the result
            Cache::store('naissancechain_qr')->put(
                $cacheKey,
                [
                    'generated_at' => now()->toISOString(),
                    'qr_code_path' => $qrData['qr_code_path'] ?? null,
                    'qr_code_url' => $qrData['qr_code_url'] ?? null,
                ],
                now()->addDays(30) // Cache for 30 days
            );
            
            Log::info('QR Code generated successfully', [
                'naissance_id' => $this->naissance->id,
                'qr_code_path' => $qrData['qr_code_path'] ?? null,
                'generated_at' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate QR Code', [
                'naissance_id' => $this->naissance->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Store QR code file.
     */
    protected function storeQrCode(array $qrData): void
    {
        if (!isset($qrData['qr_code_content']) || !isset($qrData['qr_code_path'])) {
            throw new \InvalidArgumentException('QR code data is incomplete');
        }

        // Store in public storage
        $storedPath = Storage::disk('public')->put(
            $qrData['qr_code_path'],
            $qrData['qr_code_content']
        );

        if (!$storedPath) {
            throw new \RuntimeException('Failed to store QR code file');
        }

        // Verify file exists
        if (!Storage::disk('public')->exists($qrData['qr_code_path'])) {
            throw new \RuntimeException('QR code file was not stored successfully');
        }
    }

    /**
     * Update naissance record with QR code information.
     */
    protected function updateNaissanceRecord(array $qrData): void
    {
        $this->naissance->update([
            'qr_code_path' => $qrData['qr_code_path'] ?? null,
            'qr_code_url' => $qrData['qr_code_url'] ?? null,
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'qr_generation',
            'naissance:' . $this->naissance->id,
            'agent:' . $this->naissance->agent_id,
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryAfter(): int
    {
        return $this->attempts() * 60; // 60s, 120s, 180s
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('QR Code generation job failed permanently', [
            'naissance_id' => $this->naissance->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'failed_at' => now()->toISOString(),
        ]);
        
        // Optionally update naissance record to indicate QR generation failure
        $this->naissance->update([
            'qr_code_path' => null,
            'qr_code_url' => null,
        ]);
    }

    /**
     * Get the cache key for this job.
     */
    protected function getCacheKey(): string
    {
        return "qr_job_{$this->naissance->id}";
    }

    /**
     * Check if QR code is already being processed.
     */
    public function isAlreadyProcessing(): bool
    {
        $cacheKey = $this->getCacheKey();
        return Cache::store('naissancechain_qr')->has($cacheKey);
    }

    /**
     * Mark job as processing.
     */
    public function markAsProcessing(): void
    {
        $cacheKey = $this->getCacheKey();
        Cache::store('naissancechain_qr')->put(
            $cacheKey,
            ['processing' => true, 'started_at' => now()->toISOString()],
            now()->addMinutes(30)
        );
    }

    /**
     * Clear processing flag.
     */
    public function clearProcessingFlag(): void
    {
        $cacheKey = $this->getCacheKey();
        Cache::store('naissancechain_qr')->forget($cacheKey);
    }
}
