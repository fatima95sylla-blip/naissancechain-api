<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Events\QueryExecuted;

class SecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    
    public function boot(): void
    {
        // Logs des requêtes SQL pour détection d'injections
        if (config('app.debug') || config('security.log_sql_queries')) {
            DB::listen(function (QueryExecuted $query) {
                if ($this->isSuspiciousQuery($query->sql, $query->bindings)) {
                    Log::channel('security')->warning('SUSPICIOUS_SQL_QUERY', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time,
                        'connection' => $query->connectionName,
                        'timestamp' => now()->toISOString()
                    ]);
                }
            });
        }
    }
    
    private function isSuspiciousQuery(string $sql, array $bindings): bool
    {
        $suspiciousPatterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/delete\s+from\s+\w+\s+where\s+1\s*=\s*1/i',
            '/insert\s+into\s+\w+\s*\(.*\)\s*values\s*\(.*\)\s*;\s*drop/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/eval\s*\(/i',
            '/--/',
            '/\/\*/',
            '/\*\//',
            '/;\s*drop/i',
            '/;\s*delete/i',
            '/;\s*update/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                return true;
            }
        }
        
        // Vérification des bindings suspects
        foreach ($bindings as $binding) {
            if (is_string($binding) && $this->isSuspiciousBinding($binding)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function isSuspiciousBinding(string $binding): bool
    {
        $suspiciousStrings = [
            'union select',
            'drop table',
            'delete from',
            'insert into',
            'exec(',
            'system(',
            'eval(',
            '--',
            '/*',
            '*/',
            ';drop',
            ';delete',
            ';update',
            '<script',
            'javascript:',
            'vbscript:'
        ];
        
        $bindingLower = strtolower($binding);
        
        foreach ($suspiciousStrings as $suspicious) {
            if (str_contains($bindingLower, $suspicious)) {
                return true;
            }
        }
        
        return false;
    }
}
