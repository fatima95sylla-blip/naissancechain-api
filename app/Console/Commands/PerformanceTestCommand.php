<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Naissance;
use App\Models\Agent;
use App\Services\NaissanceService;
use App\Jobs\GenerateQrJob;
use App\Jobs\StoreBlockchainJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'naissancechain:performance-test 
                            {--count=10 : Number of test records to create}
                            {--cache : Test cache performance}
                            {--async : Test async job performance}
                            {--benchmark : Run comprehensive benchmark}';

    /**
     * The console command description.
     */
    protected $description = 'Test performance optimizations and caching';

    /**
     * Execute the console command.
     */
    public function handle(NaissanceService $naissanceService): int
    {
        $count = $this->option('count');
        $testCache = $this->option('cache');
        $testAsync = $this->option('async');
        $benchmark = $this->option('benchmark');

        if ($benchmark) {
            return $this->runComprehensiveBenchmark($naissanceService);
        }

        $this->info('🚀 NaissanceChain Performance Test');
        $this->info("Creating {$count} test records...");

        // Test synchronous vs async performance
        $syncTime = $this->measureSynchronousPerformance($naissanceService, $count);
        $this->info("Synchronous: {$syncTime}s");

        if ($testAsync) {
            $asyncTime = $this->measureAsyncPerformance($naissanceService, $count);
            $this->info("Async: {$asyncTime}s");
            $this->info("Speedup: " . round($syncTime / $asyncTime, 2) . "x");
        }

        if ($testCache) {
            $this->testCachePerformance($naissanceService);
        }

        $this->showCacheStats();
        $this->showQueueStats();

        return Command::SUCCESS;
    }

    /**
     * Run comprehensive benchmark.
     */
    protected function runComprehensiveBenchmark(NaissanceService $naissanceService): int
    {
        $this->info('📊 Running Comprehensive Benchmark...');

        $results = [];

        // Test 1: Database operations
        $results['database'] = $this->benchmarkDatabaseOperations();

        // Test 2: Cache operations
        $results['cache'] = $this->benchmarkCacheOperations();

        // Test 3: Queue operations
        $results['queue'] = $this->benchmarkQueueOperations($naissanceService);

        // Test 4: End-to-end performance
        $results['e2e'] = $this->benchmarkEndToEnd($naissanceService);

        $this->displayBenchmarkResults($results);

        return Command::SUCCESS;
    }

    /**
     * Measure synchronous performance.
     */
    protected function measureSynchronousPerformance(NaissanceService $naissanceService, int $count): float
    {
        $startTime = microtime(true);

        for ($i = 0; $i < $count; $i++) {
            $data = $this->generateTestData($i);
            $agent = $this->getOrCreateTestAgent();
            
            // Temporarily disable async for sync test
            $naissance = new Naissance();
            $naissance->numero_unique = $data['numero_unique'];
            $naissance->fill($data);
            $naissance->agent_id = $agent->id;
            $naissance->save();
        }

        return round(microtime(true) - $startTime, 3);
    }

    /**
     * Measure async performance.
     */
    protected function measureAsyncPerformance(NaissanceService $naissanceService, int $count): float
    {
        $startTime = microtime(true);

        for ($i = 0; $i < $count; $i++) {
            $data = $this->generateTestData($i);
            $agent = $this->getOrCreateTestAgent();
            
            $naissanceService->create($data, $agent);
        }

        return round(microtime(true) - $startTime, 3);
    }

    /**
     * Test cache performance.
     */
    protected function testCachePerformance(NaissanceService $naissanceService): void
    {
        $this->info('📈 Testing Cache Performance...');

        // Create test data
        $naissance = $this->createTestNaissance();
        $numero = $naissance->numero_unique;

        // Test cache hit vs miss
        $iterations = 1000;

        // Cache miss (first time)
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $naissanceService->findByNumero($numero);
        }
        $cacheMissTime = microtime(true) - $startTime;

        // Clear cache and test again
        Cache::store('naissancechain')->flush();

        // Cache hit (after caching)
        $naissanceService->findByNumero($numero); // Cache it
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $naissanceService->findByNumero($numero);
        }
        $cacheHitTime = microtime(true) - $startTime;

        $this->info("Cache Miss ({$iterations} calls): " . round($cacheMissTime, 3) . "s");
        $this->info("Cache Hit ({$iterations} calls): " . round($cacheHitTime, 3) . "s");
        $this->info("Cache Speedup: " . round($cacheMissTime / $cacheHitTime, 2) . "x");
    }

    /**
     * Benchmark database operations.
     */
    protected function benchmarkDatabaseOperations(): array
    {
        $this->info('🗄️ Benchmarking Database Operations...');

        $results = [];
        $iterations = 100;

        // Benchmark inserts
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $naissance = new Naissance();
            $naissance->numero_unique = 'BENCH-' . $i;
            $naissance->nom_enfant = 'Test' . $i;
            $naissance->prenom_enfant = 'User';
            $naissance->date_naissance = '2026-01-01';
            $naissance->lieu_naissance = 'Test Location';
            $naissance->sexe = 'M';
            $naissance->nom_pere = 'Test Father';
            $naissance->prenom_pere = 'Father';
            $naissance->nom_mere = 'Test Mother';
            $naissance->prenom_mere = 'Mother';
            $naissance->adresse_parents = 'Test Address';
            $naissance->telephone_parents = '+224123456789';
            $naissance->declarant_nom = 'Test Declarant';
            $naissance->declarant_prenom = 'Declarant';
            $naissance->declarant_lien = 'Père';
            $naissance->officier_etat_civil = 'Test Officer';
            $naissance->numero_acte = 'ACTE-' . $i;
            $naissance->date_enregistrement = now();
            $naissance->latitude = 9.5;
            $naissance->longitude = -13.5;
            $naissance->agent_id = 1;
            $naissance->statut = 'valide';
            $naissance->hors_ligne = false;
            $naissance->hash_sha256 = hash('sha256', 'bench_' . $i . time());
            $naissance->save();
        }
        $results['inserts'] = [
            'operations' => $iterations,
            'time' => round(microtime(true) - $startTime, 3),
            'ops_per_second' => round($iterations / (microtime(true) - $startTime), 0),
        ];

        // Benchmark selects
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Naissance::find($i + 1);
        }
        $results['selects'] = [
            'operations' => $iterations,
            'time' => round(microtime(true) - $startTime, 3),
            'ops_per_second' => round($iterations / (microtime(true) - $startTime), 0),
        ];

        // Clean up test data
        Naissance::where('numero_unique', 'like', 'BENCH-%')->delete();

        return $results;
    }

    /**
     * Benchmark cache operations.
     */
    protected function benchmarkCacheOperations(): array
    {
        $this->info('💾 Benchmarking Cache Operations...');

        $results = [];
        $iterations = 1000;

        // Benchmark cache writes
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Cache::store('naissancechain')->put("test_key_{$i}", "test_value_{$i}", 3600);
        }
        $results['writes'] = [
            'operations' => $iterations,
            'time' => round(microtime(true) - $startTime, 3),
            'ops_per_second' => round($iterations / (microtime(true) - $startTime), 0),
        ];

        // Benchmark cache reads
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Cache::store('naissancechain')->get("test_key_{$i}");
        }
        $results['reads'] = [
            'operations' => $iterations,
            'time' => round(microtime(true) - $startTime, 3),
            'ops_per_second' => round($iterations / (microtime(true) - $startTime), 0),
        ];

        // Clean up test data
        for ($i = 0; $i < $iterations; $i++) {
            Cache::store('naissancechain')->forget("test_key_{$i}");
        }

        return $results;
    }

    /**
     * Benchmark queue operations.
     */
    protected function benchmarkQueueOperations(NaissanceService $naissanceService): array
    {
        $this->info('⚡ Benchmarking Queue Operations...');

        $results = [];
        $iterations = 50;

        // Benchmark job dispatching
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $naissance = $this->createTestNaissance();
            GenerateQrJob::dispatch($naissance);
            StoreBlockchainJob::dispatch($naissance);
        }
        $results['dispatch'] = [
            'operations' => $iterations * 2, // 2 jobs per iteration
            'time' => round(microtime(true) - $startTime, 3),
            'ops_per_second' => round(($iterations * 2) / (microtime(true) - $startTime), 0),
        ];

        return $results;
    }

    /**
     * Benchmark end-to-end operations.
     */
    protected function benchmarkEndToEnd(NaissanceService $naissanceService): array
    {
        $this->info('🔄 Benchmarking End-to-End Operations...');

        $results = [];
        $iterations = 10;

        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $data = $this->generateTestData($i);
            $agent = $this->getOrCreateTestAgent();
            $naissance = $naissanceService->create($data, $agent);
            
            // Test verification
            $naissanceService->verifyAndNotify($naissance);
        }
        $totalTime = microtime(true) - $startTime;

        $results['e2e'] = [
            'operations' => $iterations,
            'time' => round($totalTime, 3),
            'ops_per_second' => round($iterations / $totalTime, 2),
            'avg_time_per_op' => round($totalTime / $iterations, 3),
        ];

        return $results;
    }

    /**
     * Display benchmark results.
     */
    protected function displayBenchmarkResults(array $results): void
    {
        $this->info("\n📊 Benchmark Results:");
        $this->info("==================");

        foreach ($results as $category => $data) {
            $this->info("\n{$category}:");
            foreach ($data as $operation => $metrics) {
                if (is_array($metrics)) {
                    $this->info("  {$operation}:");
                    foreach ($metrics as $metric => $value) {
                        $this->info("    {$metric}: {$value}");
                    }
                }
            }
        }
    }

    /**
     * Show cache statistics.
     */
    protected function showCacheStats(): void
    {
        try {
            // Alternative method for Redis - get info instead of keys
            $redis = Cache::store('naissancechain')->getStore();
            if (method_exists($redis, 'getRedis')) {
                $info = $redis->getRedis()->info();
                $keyspace = $info['keyspace'] ?? [];
                $dbInfo = $keyspace['db2'] ?? 'keys=0';
                $keyCount = (int) substr($dbInfo, strpos($dbInfo, 'keys=') + 5);
            } else {
                $keyCount = 'N/A';
            }
        } catch (\Exception $e) {
            $keyCount = 'Error: ' . $e->getMessage();
        }
        
        $this->info("\n💾 Cache Stats:");
        $this->info("Total keys: {$keyCount}");
    }

    /**
     * Show queue statistics.
     */
    protected function showQueueStats(): void
    {
        $jobs = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')->count();
        
        $this->info("\n⚡ Queue Stats:");
        $this->info("Pending jobs: {$jobs}");
        $this->info("Failed jobs: {$failed}");
    }

    /**
     * Generate test data.
     */
    protected function generateTestData(int $index): array
    {
        return [
            'numero_unique' => 'PERF-' . str_pad($index, 6, '0', STR_PAD_LEFT) . '-' . time() . rand(100, 999),
            'nom_enfant' => 'Test' . $index,
            'prenom_enfant' => 'User',
            'date_naissance' => '2026-01-01',
            'lieu_naissance' => 'Test Location',
            'sexe' => 'M',
            'nom_pere' => 'Test Father',
            'prenom_pere' => 'Father',
            'nom_mere' => 'Test Mother',
            'prenom_mere' => 'Mother',
            'adresse_parents' => 'Test Address',
            'telephone_parents' => '+224123456789',
            'declarant_nom' => 'Test Declarant',
            'declarant_prenom' => 'Declarant',
            'declarant_lien' => 'Père',
            'officier_etat_civil' => 'Test Officer',
            'numero_acte' => 'PERF-' . str_pad($index, 6, '0', STR_PAD_LEFT) . '-' . time(),
            'date_enregistrement' => now(),
            'latitude' => 9.5,
            'longitude' => -13.5,
            'statut' => 'valide',
            'hors_ligne' => false,
            'hash_sha256' => hash('sha256', 'test_hash_' . $index . time() . rand()),
        ];
    }

    /**
     * Get or create test agent.
     */
    protected function getOrCreateTestAgent(): Agent
    {
        return Agent::firstOrCreate(
            ['email' => 'test@performance.com'],
            [
                'nom' => 'Test',
                'prenom' => 'Performance',
                'password' => bcrypt('password'),
                'telephone' => '+224123456789',
                'role' => 'agent',
                'prefecture' => 'Test',
                'zone' => 'Test',
                'actif' => true,
            ]
        );
    }

    /**
     * Create test naissance.
     */
    protected function createTestNaissance(): Naissance
    {
        $data = $this->generateTestData(rand(1000, 9999));
        $agent = $this->getOrCreateTestAgent();
        
        $naissance = new Naissance();
        $naissance->fill($data);
        $naissance->agent_id = $agent->id;
        $naissance->save();
        
        return $naissance;
    }
}
