<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncRequest;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Synchronisation",
 *     description="Endpoints pour la synchronisation hors ligne"
 * )
 */

class SyncController extends Controller
{
    protected SyncService $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * @OA\Post(
     *     path="/sync",
     *     tags={"Synchronisation"},
     *     summary="Synchroniser les données hors ligne",
     *     description="Synchronise les actes de naissance créés hors ligne avec le serveur central",
     *     operationId="syncData",
     *     security={{"BearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"naissances"},
     *                 @OA\Property(
     *                     property="naissances",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="nom_enfant", type="string", example="TOURE"),
     *                         @OA\Property(property="prenom_enfant", type="string", example="Ibrahim"),
     *                         @OA\Property(property="date_naissance", type="string", format="date", example="2026-03-20"),
     *                         @OA\Property(property="lieu_naissance", type="string", example="Hôpital Donka, Conakry"),
     *                         @OA\Property(property="sexe", type="string", enum={"M", "F"}, example="M"),
     *                         @OA\Property(property="numero_acte", type="string", example="ACTE-2026-002"),
     *                         @OA\Property(property="latitude", type="number", format="float", example=9.6412),
     *                         @OA\Property(property="longitude", type="number", format="float", example=-13.5784),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="hash_temporaire", type="string", example="temp123...")
     *                     )
     *                 ),
     *                 description="Liste des actes de naissance à synchroniser"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Synchronisation terminée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Synchronisation terminée"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=10),
     *                 @OA\Property(property="synchronized", type="integer", example=8),
     *                 @OA\Property(property="failed", type="integer", example=2),
     *                 @OA\Property(
     *                     property="results",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="success", type="boolean", example=true),
     *                         @OA\Property(property="naissance_id", type="integer", example=1),
     *                         @OA\Property(property="message", type="string", example="Synchronisé")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur de validation")
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
    public function sync(SyncRequest $request): JsonResponse
    {
        try {
            $results = $this->syncService->sync(
                $request->validated()['naissances'],
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Synchronisation terminée',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la synchronisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/sync/pending",
     *     tags={"Synchronisation"},
     *     summary="Obtenir les enregistrements en attente",
     *     description="Récupère les actes de naissance en attente de synchronisation pour l'agent authentifié",
     *     operationId="getPendingSync",
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Enregistrements en attente récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enregistrements en attente récupérés"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=5),
     *                 @OA\Property(
     *                     property="naissances",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nom_enfant", type="string", example="TOURE"),
     *                         @OA\Property(property="prenom_enfant", type="string", example="Ibrahim"),
     *                         @OA\Property(property="date_naissance", type="string", format="date", example="2026-03-20"),
     *                         @OA\Property(property="numero_acte", type="string", example="ACTE-2026-002"),
     *                         @OA\Property(property="statut", type="string", example="en_attente"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="sync_needed", type="boolean", example=true)
     *                     )
     *                 )
     *             )
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
    public function pending(Request $request): JsonResponse
    {
        try {
            $naissances = $this->syncService->getPendingForAgent($request->user());

            return response()->json([
                'success' => true,
                'data' => $naissances
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des enregistrements en attente'
            ], 500);
        }
    }

    /**
     * Process sync queue (admin only).
     */
    public function processQueue(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $results = $this->syncService->processSyncQueue();

            return response()->json([
                'success' => true,
                'message' => 'Queue de synchronisation traitée',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement de la queue',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
