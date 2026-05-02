<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Notifications\NaissanceCreated;
use App\Notifications\NaissanceVerified;

class SendNotificationJob implements ShouldQueue
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
    public $timeout = 60;

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public $failOnTimeout = true;

    protected $notificationType;
    protected $naissance;
    protected $user;
    protected $verificationResult;

    /**
     * Create a new job instance.
     */
    public function __construct(string $notificationType, $naissance, $user, $verificationResult = null)
    {
        $this->notificationType = $notificationType;
        $this->naissance = $naissance;
        $this->user = $user;
        $this->verificationResult = $verificationResult;

        // Set queue based on notification type
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $notification = $this->createNotification();
            
            // Send notification
            $this->user->notify($notification);

            // Log successful notification
            Log::info('Notification sent successfully', [
                'type' => $this->notificationType,
                'naissance_id' => $this->naissance->id,
                'user_id' => $this->user->id,
                'user_email' => $this->user->email,
                'sent_at' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            // Log error and fail the job
            Log::error('Failed to send notification', [
                'type' => $this->notificationType,
                'naissance_id' => $this->naissance->id,
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);

            // Re-throw to trigger job retry mechanism
            throw $e;
        }
    }

    /**
     * Create the appropriate notification instance.
     */
    protected function createNotification()
    {
        switch ($this->notificationType) {
            case 'naissance_created':
                return new NaissanceCreated($this->naissance, $this->user);
            
            case 'naissance_verified':
                return new NaissanceVerified($this->naissance, $this->user, $this->verificationResult);
            
            default:
                throw new \InvalidArgumentException("Unknown notification type: {$this->notificationType}");
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'notification',
            $this->notificationType,
            'naissance:' . $this->naissance->id,
            'user:' . $this->user->id,
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(5);
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryAfter(): int
    {
        return $this->attempts() * 30; // 30s, 60s, 90s
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Notification job failed permanently', [
            'type' => $this->notificationType,
            'naissance_id' => $this->naissance->id,
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'failed_at' => now()->toISOString(),
        ]);
    }
}
