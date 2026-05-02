<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NaissanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VerificationController extends Controller
{
    protected NaissanceService $naissanceService;

    public function __construct(NaissanceService $naissanceService)
    {
        $this->naissanceService = $naissanceService;
    }

    /**
     * Verify birth record by unique number.
     */
    public function verify($numero): JsonResponse
    {
        try {
            $result = $this->naissanceService->verify($numero);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acte de naissance non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'naissance' => $result['naissance'],
                    'valide' => $result['valide'],
                    'qr_code_url' => $result['naissance']->qr_code_url,
                    'verification_url' => route('verification.show', $result['naissance']->numero_unique)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification de l\'acte de naissance'
            ], 500);
        }
    }

    /**
     * Get verification details for public display.
     */
    public function show($numero): JsonResponse
    {
        try {
            $naissance = $this->naissanceService->findByNumero($numero);

            if (!$naissance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acte de naissance non trouvé'
                ], 404);
            }

            // Public information only
            $publicData = [
                'numero_unique' => $naissance->numero_unique,
                'nom_enfant' => $naissance->nom_enfant,
                'prenom_enfant' => $naissance->prenom_enfant,
                'date_naissance' => $naissance->date_naissance,
                'lieu_naissance' => $naissance->lieu_naissance,
                'sexe' => $naissance->sexe,
                'date_enregistrement' => $naissance->date_enregistrement,
                'officier_etat_civil' => $naissance->officier_etat_civil,
                'qr_code_url' => $naissance->qr_code_url,
                'logo_url' => url($naissance->logo_path),
                'valide' => $naissance->statut === 'valide'
            ];

            return response()->json([
                'success' => true,
                'data' => $publicData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations'
            ], 500);
        }
    }
}
