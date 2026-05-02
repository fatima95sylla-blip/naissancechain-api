<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NaissanceController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\BlockchainController;
use App\Http\Controllers\Api\UploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Verification routes (public)
    Route::get('/verification/{numero}', [VerificationController::class, 'verify']);
    Route::get('/verification/{numero}/show', [VerificationController::class, 'show']);
});

// Protected routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Birth records routes
    Route::apiResource('naissances', NaissanceController::class)->only([
        'index', 'store', 'show'
    ]);
    
    Route::get('/naissances/pending-sync', [NaissanceController::class, 'pendingSync']);
    
    // Sync routes
    Route::post('/sync', [SyncController::class, 'sync']);
    Route::get('/sync/pending', [SyncController::class, 'pending']);
    Route::post('/sync/process-queue', [SyncController::class, 'processQueue']);
    
    // Blockchain routes
    Route::prefix('blockchain')->group(function () {
        Route::post('/store/{id}', [BlockchainController::class, 'store']);
        Route::get('/verify/{id}', [BlockchainController::class, 'verify']);
        Route::get('/stats', [BlockchainController::class, 'stats']);
        Route::get('/records', [BlockchainController::class, 'records']);
        Route::get('/chain', [BlockchainController::class, 'chain']);
        Route::get('/verify-chain', [BlockchainController::class, 'verifyChain']);
    });

    // File upload routes
    Route::prefix('upload')->group(function () {
        Route::post('/', [UploadController::class, 'upload']);
        Route::post('/logo', [UploadController::class, 'uploadLogo']);
        Route::post('/document', [UploadController::class, 'uploadDocument']);
        Route::delete('/', [UploadController::class, 'delete']);
        Route::get('/info', [UploadController::class, 'info']);
        Route::get('/list', [UploadController::class, 'list']);
    });
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint non trouvé'
    ], 404);
});
