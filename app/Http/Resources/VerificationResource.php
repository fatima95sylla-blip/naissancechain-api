<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'numero_unique' => $this->numero_unique,
            'nom_enfant' => $this->nom_enfant,
            'prenom_enfant' => $this->prenom_enfant,
            'date_naissance' => $this->date_naissance,
            'lieu_naissance' => $this->lieu_naissance,
            'sexe' => $this->sexe,
            'date_enregistrement' => $this->date_enregistrement,
            'officier_etat_civil' => $this->officier_etat_civil,
            'qr_code_url' => $this->qr_code_url,
            'logo_url' => url($this->logo_path),
            'statut' => $this->statut,
            'valide' => $this->resource['valide'] ?? false,
            'message' => $this->resource['message'] ?? 'Acte vérifié',
            'verification_url' => route('verification.show', $this->numero_unique),
        ];
    }
}
