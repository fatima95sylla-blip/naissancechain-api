<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Naissance;
use App\Models\User;
use App\Models\Agent;
use App\Jobs\SendNotificationJob;
use App\Notifications\NaissanceCreated;
use App\Notifications\NaissanceVerified;

class TestNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'naissancechain:test-notification 
                            {type=naissance_created : Type of notification (naissance_created|naissance_verified)}
                            {--naissance-id= : ID of naissance record}
                            {--user-id= : ID of user to notify}
                            {--sync : Process synchronously}';

    /**
     * The console command description.
     */
    protected $description = 'Test notification system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $naissanceId = $this->option('naissance-id');
        $userId = $this->option('user-id');
        $sync = $this->option('sync');

        // Get naissance
        if ($naissanceId) {
            $naissance = Naissance::find($naissanceId);
            if (!$naissance) {
                $this->error("Naissance with ID {$naissanceId} not found");
                return Command::FAILURE;
            }
        } else {
            // Get latest naissance
            $naissance = Naissance::latest()->first();
            if (!$naissance) {
                $this->error('No naissance records found');
                return Command::FAILURE;
            }
        }

        // Get user
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found");
                return Command::FAILURE;
            }
        } else {
            // Get admin user
            $user = User::whereHas('roles', function ($query) {
                $query->where('name', 'ADMIN');
            })->first();
            
            if (!$user) {
                // Get first user
                $user = User::first();
                if (!$user) {
                    $this->error('No users found');
                    return Command::FAILURE;
                }
            }
        }

        $this->info("Sending {$type} notification:");
        $this->info("  Naissance: {$naissance->nom_enfant} {$naissance->prenom_enfant} (ID: {$naissance->id})");
        $this->info("  User: {$user->name} ({$user->email})");
        $this->info("  Sync: " . ($sync ? 'Yes' : 'No'));

        try {
            if ($sync) {
                // Send synchronously
                $notification = $this->createNotification($type, $naissance, $user);
                $user->notify($notification);
                $this->info("✅ Notification sent synchronously");
            } else {
                // Send via queue
                SendNotificationJob::dispatch($type, $naissance, $user);
                $this->info("✅ Notification job dispatched to queue");
            }

            // Show queue status
            if (!$sync) {
                $this->showQueueStatus();
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to send notification: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Create notification instance.
     */
    protected function createNotification(string $type, Naissance $naissance, User $user)
    {
        switch ($type) {
            case 'naissance_created':
                return new NaissanceCreated($naissance, $user);
            
            case 'naissance_verified':
                $verificationResult = [
                    'verified' => true,
                    'message' => 'Test verification successful',
                    'block_number' => 1,
                    'timestamp' => now(),
                ];
                return new NaissanceVerified($naissance, $user, $verificationResult);
            
            default:
                throw new \InvalidArgumentException("Unknown notification type: {$type}");
        }
    }

    /**
     * Show queue status.
     */
    protected function showQueueStatus(): void
    {
        $this->info("\n📊 Queue Status:");
        
        // Check if queue worker is running
        $this->call('queue:failed', ['--count' => 1]);
        
        // Show jobs in queue
        try {
            $jobs = \DB::table('jobs')->count();
            $this->info("  Jobs in queue: {$jobs}");
            
            $failedJobs = \DB::table('failed_jobs')->count();
            $this->info("  Failed jobs: {$failedJobs}");
            
            if ($jobs > 0) {
                $this->info("\n💡 To process queued jobs, run:");
                $this->info("  php artisan queue:work --queue=notifications");
            }
            
        } catch (\Exception $e) {
            $this->warn("Could not check queue status: " . $e->getMessage());
        }
    }
}
