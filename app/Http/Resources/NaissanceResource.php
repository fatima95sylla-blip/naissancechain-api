<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NaissanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_unique' => $this->numero_unique,
            'nom_enfant' => $this->nom_enfant,
            'prenom_enfant' => $this->prenom_enfant,
            'nom_complet_enfant' => $this->nom_complet_enfant,
            'date_naissance' => $this->date_naissance,
            'lieu_naissance' => $this->lieu_naissance,
            'heure_naissance' => $this->heure_naissance,
            'sexe' => $this->sexe,
            'nom_pere' => $this->nom_pere,
            'prenom_pere' => $this->prenom_pere,
            'nom_complet_pere' => $this->nom_complet_pere,
            'profession_pere' => $this->profession_pere,
            'nom_mere' => $this->nom_mere,
            'prenom_mere' => $this->prenom_mere,
            'nom_complet_mere' => $this->nom_complet_mere,
            'profession_mere' => $this->profession_mere,
            'adresse_parents' => $this->adresse_parents,
            'telephone_parents' => $this->telephone_parents,
            'declarant_nom' => $this->declarant_nom,
            'declarant_prenom' => $this->declarant_prenom,
            'declarant_lien' => $this->declarant_lien,
            'officier_etat_civil' => $this->officier_etat_civil,
            'numero_acte' => $this->numero_acte,
            'date_enregistrement' => $this->date_enregistrement,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'hash_sha256' => $this->hash_sha256,
            'hash_precedent' => $this->hash_precedent,
            'qr_code_path' => $this->qr_code_path,
            'qr_code_url' => $this->qr_code_url,
            'logo_path' => $this->logo_path,
            'logo_url' => url($this->logo_path),
            'statut' => $this->statut,
            'hors_ligne' => $this->hors_ligne,
            'synced_at' => $this->synced_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'agent' => new AgentResource($this->whenLoaded('agent')),
        ];
    }
}
