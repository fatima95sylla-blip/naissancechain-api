<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Naissance;
use App\Services\BlockchainService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Blockchain",
 *     description="Endpoints pour la gestion blockchain des actes de naissance"
 * )
 */

class BlockchainController extends Controller
{
    protected BlockchainService $blockchainService;

    public function __construct(BlockchainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    /**
     * @OA\Post(
     *     path="/blockchain/store/{id}",
     *     tags={"Blockchain"},
     *     summary="Stocker un acte sur la blockchain",
     *     description="Stocke un acte de naissance sur la blockchain avec hashage et chaînage",
     *     operationId="storeBlockchain",
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'acte de naissance",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enregistrement blockchain créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enregistrement blockchain créé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="naissance_id", type="integer", example=1),
     *                 @OA\Property(property="blockchain_record_id", type="integer", example=1),
     *                 @OA\Property(property="hash", type="string", example="abc123def456..."),
     *                 @OA\Property(property="block_number", type="integer", example=1),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2026-04-30T10:00:00Z"),
     *                 @OA\Property(property="previous_hash", type="string", example="null")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Acte de naissance non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Acte de naissance non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     )
     * )
     */
    public function store(Request $request, int $id): JsonResponse
    {
        try {
            $naissance = Naissance::findOrFail($id);
            $blockchainRecord = $this->blockchainService->storeOnChain($naissance);

            return ApiResponse::success([
                'naissance_id' => $naissance->id,
                'blockchain_record_id' => $blockchainRecord->id,
                'hash' => $blockchainRecord->hash,
                'block_number' => $blockchainRecord->block_number,
                'timestamp' => $blockchainRecord->timestamp,
                'previous_hash' => $blockchainRecord->previous_hash,
            ], 'Enregistrement blockchain créé avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors du stockage blockchain', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/blockchain/verify/{id}",
     *     tags={"Blockchain"},
     *     summary="Vérifier l'intégrité d'un acte",
     *     description="Vérifie l'intégrité d'un acte de naissance sur la blockchain",
     *     operationId="verifyBlockchain",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'acte de naissance",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vérification réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vérification réussie"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="verified", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="L'intégrité de l'acte est vérifiée"),
     *                 @OA\Property(property="hash", type="string", example="abc123def456..."),
     *                 @OA\Property(property="block_number", type="integer", example=1),
     *                 @OA\Property(property="timestamp", type="string", format="date-time"),
     *                 @OA\Property(property="previous_hash", type="string", example="null")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Acte de naissance non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Acte de naissance non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur de vérification",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la vérification")
     *         )
     *     )
     * )
     */
    public function verify(int $id): JsonResponse
    {
        try {
            $naissance = Naissance::findOrFail($id);
            $verification = $this->blockchainService->verifyIntegrity($naissance);

            return ApiResponse::success($verification, 'Vérification blockchain terminée');
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la vérification blockchain', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get blockchain statistics.
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->blockchainService->getStats();

            return ApiResponse::success($stats, 'Statistiques blockchain récupérées');
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la récupération des statistiques', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get blockchain records by date range.
     */
    public function records(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $records = $this->blockchainService->getRecordsByDateRange(
                $request->start_date,
                $request->end_date
            );

            return ApiResponse::success($records, 'Enregistrements blockchain récupérés');
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la récupération des enregistrements', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get full blockchain chain.
     */
    public function chain(): JsonResponse
    {
        try {
            $records = \App\Models\BlockchainRecord::with('naissance')
                ->ordered()
                ->get()
                ->map(function ($record) {
                    return [
                        'block_number' => $record->block_number,
                        'hash' => $record->hash,
                        'previous_hash' => $record->previous_hash,
                        'timestamp' => $record->timestamp,
                        'naissance_id' => $record->naissance_id,
                        'verified' => $record->verified,
                        'data_signature' => $record->data_signature,
                    ];
                })
                ->toArray();

            return ApiResponse::success($records, 'Chaîne blockchain complète');
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la récupération de la chaîne', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify full chain integrity.
     */
    public function verifyChain(): JsonResponse
    {
        try {
            $records = \App\Models\BlockchainRecord::ordered()->get();
            $issues = [];

            foreach ($records as $record) {
                if (!$record->isValidChain()) {
                    $issues[] = [
                        'block_number' => $record->block_number,
                        'hash' => $record->hash,
                        'issue' => 'Invalid chain link',
                        'expected_previous_hash' => $record->previousRecord()?->hash ?? '0',
                        'actual_previous_hash' => $record->previous_hash,
                    ];
                }
            }

            $isChainValid = empty($issues);

            return ApiResponse::success([
                'chain_valid' => $isChainValid,
                'total_blocks' => $records->count(),
                'issues' => $issues,
                'verification_timestamp' => now(),
            ], 'Vérification de la chaîne terminée');
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la vérification de la chaîne', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
