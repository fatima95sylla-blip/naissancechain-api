<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNaissanceRequest;
use App\Services\NaissanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Naissances",
 *     description="Endpoints pour la gestion des actes de naissance"
 * )
 */

class NaissanceController extends Controller
{
    protected NaissanceService $naissanceService;

    public function __construct(NaissanceService $naissanceService)
    {
        $this->naissanceService = $naissanceService;
    }

    /**
     * @OA\Post(
     *     path="/naissances",
     *     tags={"Naissances"},
     *     summary="Créer un nouvel acte de naissance",
     *     description="Crée un nouvel acte de naissance avec génération QR code et stockage blockchain",
     *     operationId="createNaissance",
     *     security={{"BearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"nom_complet_enfant", "sexe_enfant", "date_naissance_enfant", "heure_naissance_enfant", "nom_complet_pere", "profession_pere", "nom_complet_mere", "profession_mere", "ville_region", "quartier_secteur", "lieu_naissance_enfant", "nom_declarant", "numero_declarant", "officier_etat_civil", "numero_acte", "date_enregistrement"},
     *                 @OA\Property(property="nom_complet_enfant", type="string", example="TOURE Ibrahim", description="Nom complet de l'enfant"),
     *                 @OA\Property(property="sexe_enfant", type="string", enum={"M", "F"}, example="M", description="Sexe de l'enfant"),
     *                 @OA\Property(property="date_naissance_enfant", type="string", format="date", example="2026-03-20", description="Date de naissance de l'enfant"),
     *                 @OA\Property(property="heure_naissance_enfant", type="string", example="14:30", description="Heure de naissance de l'enfant"),
     *                 @OA\Property(property="nom_complet_pere", type="string", example="TOURE Mamadou", description="Nom complet du père"),
     *                 @OA\Property(property="profession_pere", type="string", example="Ingénieur", description="Profession du père"),
     *                 @OA\Property(property="date_naissance_pere", type="string", format="date", example="1980-05-15", description="Date de naissance du père"),
     *                 @OA\Property(property="nom_complet_mere", type="string", example="DIALLO Aicha", description="Nom complet de la mère"),
     *                 @OA\Property(property="profession_mere", type="string", example="Enseignante", description="Profession de la mère"),
     *                 @OA\Property(property="date_naissance_mere", type="string", format="date", example="1985-08-20", description="Date de naissance de la mère"),
     *                 @OA\Property(property="ville_region", type="string", example="Abidjan", description="Ville ou région"),
     *                 @OA\Property(property="quartier_secteur", type="string", example="Cocody", description="Quartier ou secteur"),
     *                 @OA\Property(property="lieu_naissance_enfant", type="string", example="Hôpital Donka, Conakry", description="Lieu de naissance de l'enfant"),
     *                 @OA\Property(property="nom_declarant", type="string", example="TOURE Mamadou", description="Nom du déclarant"),
     *                 @OA\Property(property="numero_declarant", type="string", example="1234567890", description="Numéro du déclarant"),
     *                 @OA\Property(property="officier_etat_civil", type="string", example="M. Barry", description="Officier d'état civil"),
     *                 @OA\Property(property="numero_acte", type="string", example="ACTE-2026-002", description="Numéro de l'acte"),
     *                 @OA\Property(property="date_enregistrement", type="string", format="date-time", example="2026-04-30T10:00:00Z", description="Date d'enregistrement"),
     *                 @OA\Property(property="latitude", type="number", format="float", example=9.6412, description="Latitude GPS"),
     *                 @OA\Property(property="longitude", type="number", format="float", example=-13.5784, description="Longitude GPS"),
     *                 @OA\Property(property="hors_ligne", type="boolean", example=false, description="Mode hors ligne")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Acte de naissance créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Acte de naissance créé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="numero_unique", type="string", example="NC-2026-000001"),
     *                 @OA\Property(property="nom_complet_enfant", type="string", example="TOURE Ibrahim"),
     *                 @OA\Property(property="sexe_enfant", type="string", example="M"),
     *                 @OA\Property(property="date_naissance_enfant", type="string", format="date", example="2026-03-20"),
     *                 @OA\Property(property="heure_naissance_enfant", type="string", example="14:30"),
     *                 @OA\Property(property="nom_complet_pere", type="string", example="TOURE Mamadou"),
     *                 @OA\Property(property="profession_pere", type="string", example="Ingénieur"),
     *                 @OA\Property(property="nom_complet_mere", type="string", example="DIALLO Aicha"),
     *                 @OA\Property(property="profession_mere", type="string", example="Enseignante"),
     *                 @OA\Property(property="ville_region", type="string", example="Abidjan"),
     *                 @OA\Property(property="quartier_secteur", type="string", example="Cocody"),
     *                 @OA\Property(property="lieu_naissance_enfant", type="string", example="Hôpital Donka, Conakry"),
     *                 @OA\Property(property="nom_declarant", type="string", example="TOURE Mamadou"),
     *                 @OA\Property(property="numero_declarant", type="string", example="1234567890"),
     *                 @OA\Property(property="numero_acte", type="string", example="ACTE-2026-002"),
     *                 @OA\Property(property="statut", type="string", example="valide"),
     *                 @OA\Property(property="hash_sha256", type="string", example="abc123..."),
     *                 @OA\Property(property="qr_code_url", type="string", example="/storage/qr/abc123.png"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="agent",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="BARRY"),
     *                     @OA\Property(property="prenom", type="string", example="Mamadou")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object")
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
    public function store(StoreNaissanceRequest $request): JsonResponse
    {
        try {
            $naissance = $this->naissanceService->create(
                $request->validated(),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Acte de naissance créé avec succès',
                'data' => $naissance->load('agent')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'acte de naissance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific birth record.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $naissance = $request->user()->naissances()->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $naissance->load('agent')
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Acte de naissance non trouvé'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'acte de naissance'
            ], 500);
        }
    }

    /**
     * Get all birth records for the authenticated agent.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $naissances = $request->user()->naissances()
                ->with('agent')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $naissances
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des actes de naissance'
            ], 500);
        }
    }

    /**
     * Get pending sync records.
     */
    public function pendingSync(Request $request): JsonResponse
    {
        try {
            $naissances = $this->naissanceService->getPendingSync();

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
}
