<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ProcessBulkNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 2;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public $timeout = 300; // 5 minutes

    protected $notificationType;
    protected $naissance;
    protected $userIds;
    protected $verificationResult;

    /**
     * Create a new job instance.
     */
    public function __construct(string $notificationType, $naissance, array $userIds, $verificationResult = null)
    {
        $this->notificationType = $notificationType;
        $this->naissance = $naissance;
        $this->userIds = $userIds;
        $this->verificationResult = $verificationResult;

        $this->onQueue('bulk_notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::whereIn('id', $this->userIds)->get();
        $sentCount = 0;
        $failedCount = 0;

        foreach ($users as $user) {
            try {
                // Dispatch individual notification job
                SendNotificationJob::dispatch(
                    $this->notificationType,
                    $this->naissance,
                    $user,
                    $this->verificationResult
                );

                $sentCount++;

            } catch (\Exception $e) {
                $failedCount++;
                
                Log::error('Failed to dispatch notification for user', [
                    'notification_type' => $this->notificationType,
                    'naissance_id' => $this->naissance->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Bulk notification job completed', [
            'notification_type' => $this->notificationType,
            'naissance_id' => $this->naissance->id,
            'total_users' => $users->count(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'processed_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'bulk_notification',
            $this->notificationType,
            'naissance:' . $this->naissance->id,
            'users:' . count($this->userIds),
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk notification job failed permanently', [
            'notification_type' => $this->notificationType,
            'naissance_id' => $this->naissance->id,
            'user_ids' => $this->userIds,
            'error' => $exception->getMessage(),
            'failed_at' => now()->toISOString(),
        ]);
    }
}
