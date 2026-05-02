<?php

namespace App\Services;

use App\Models\Naissance;
use App\Models\SyncQueue;
use App\Models\Agent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncService
{
    protected NaissanceService $naissanceService;

    public function __construct(NaissanceService $naissanceService)
    {
        $this->naissanceService = $naissanceService;
    }

    /**
     * Process offline data synchronization.
     */
    public function sync(array $naissances, Agent $agent): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($naissances as $naissanceData) {
                try {
                    // Check if record already exists
                    $existing = Naissance::where('numero_unique', $naissanceData['numero_unique'])->first();

                    if ($existing) {
                        // Update existing record
                        $this->updateExistingRecord($existing, $naissanceData);
                    } else {
                        // Create new record
                        $this->createNewRecord($naissanceData, $agent);
                    }

                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'numero_unique' => $naissanceData['numero_unique'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];

                    Log::error('Sync failed for record', [
                        'numero_unique' => $naissanceData['numero_unique'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get pending sync records for an agent.
     */
    public function getPendingForAgent(Agent $agent): \Illuminate\Database\Eloquent\Collection
    {
        return Naissance::where('agent_id', $agent->id)
            ->enAttenteSync()
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Add record to sync queue.
     */
    public function addToSyncQueue(string $modelType, int $modelId, string $action, array $data): SyncQueue
    {
        return SyncQueue::create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'action' => $action,
            'data' => $data,
            'status' => 'pending'
        ]);
    }

    /**
     * Process sync queue.
     */
    public function processSyncQueue(): array
    {
        $items = SyncQueue::pending()->limit(100)->get();
        $results = ['processed' => 0, 'failed' => 0];

        foreach ($items as $item) {
            try {
                $this->processSyncItem($item);
                $results['processed']++;
            } catch (\Exception $e) {
                $item->status = 'failed';
                $item->error_message = $e->getMessage();
                $item->retry_count++;
                $item->retry_at = now()->addMinutes(5 * $item->retry_count);
                $item->save();

                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Update existing record during sync.
     */
    protected function updateExistingRecord(Naissance $naissance, array $data): void
    {
        // Preserve critical fields
        unset($data['id'], $data['created_at'], $data['updated_at']);

        // Update record
        $naissance->update($data);

        // Mark as synchronized
        $this->naissanceService->markAsSynchronized($naissance);
    }

    /**
     * Create new record during sync.
     */
    protected function createNewRecord(array $data, Agent $agent): void
    {
        // Ensure agent_id is correct
        $data['agent_id'] = $agent->id;

        // Create record
        $naissance = Naissance::create($data);

        // Generate QR code if missing
        if (!$naissance->qr_code_path) {
            $this->naissanceService->generateQRCode($naissance);
        }

        // Mark as synchronized
        $this->naissanceService->markAsSynchronized($naissance);
    }

    /**
     * Process individual sync queue item.
     */
    protected function processSyncItem(SyncQueue $item): void
    {
        $item->status = 'processing';
        $item->save();

        // Process based on model type and action
        if ($item->model_type === 'Naissance') {
            $naissance = Naissance::find($item->model_id);
            
            if ($naissance) {
                if ($item->action === 'create' || $item->action === 'update') {
                    $naissance->update($item->data);
                } elseif ($item->action === 'delete') {
                    $naissance->delete();
                }
            }
        }

        $item->status = 'completed';
        $item->processed_at = now();
        $item->save();
    }
}
