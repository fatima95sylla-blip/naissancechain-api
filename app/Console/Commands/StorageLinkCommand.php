<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StorageLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'naissancechain:storage-link';

    /**
     * The console command description.
     */
    protected $description = 'Create symbolic links for NaissanceChain storage directories';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $links = [
            public_path('storage') => storage_path('app/public'),
            public_path('storage/logos') => storage_path('app/public/logos'),
            public_path('storage/documents') => storage_path('app/public/documents'),
        ];

        $success = true;

        foreach ($links as $link => $target) {
            if (file_exists($link)) {
                if (is_link($link) && readlink($link) === $target) {
                    $this->info("The [$link] link already exists and points to correct target.");
                    continue;
                }
                
                $this->error("The [$link] link already exists but points to wrong target.");
                $success = false;
                continue;
            }

            if (!is_dir(dirname($target))) {
                mkdir(dirname($target), 0755, true);
            }

            if (!is_dir(dirname($link))) {
                mkdir(dirname($link), 0755, true);
            }

            // Windows-specific handling
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $target = str_replace('/', '\\', $target);
                $link = str_replace('/', '\\', $link);
                
                // Remove existing if directory
                if (is_dir($link)) {
                    rmdir($link);
                }
                
                // Try mklink with admin privileges
                $command = "mklink /D \"$link\" \"$target\" 2>nul";
                $output = [];
                $returnCode = 0;
                
                exec($command, $output, $returnCode);
                
                if ($returnCode === 0) {
                    $this->info("The [$link] link has been created to [$target] (Windows mklink).");
                    continue;
                }
                
                // Fallback: copy directory structure
                if (!is_dir($link)) {
                    mkdir($link, 0755, true);
                    $this->info("Created directory [$link] as fallback (no symlink available).");
                    continue;
                }
            } else {
                // Linux/Mac: try symlink
                if (symlink($target, $link)) {
                    $this->info("The [$link] link has been created to [$target].");
                    continue;
                }
            }
            
            $this->error("The [$link] link could not be created.");
            $success = false;
        }

        return $success ? Command::SUCCESS : Command::FAILURE;
    }
}
