<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
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
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'prefecture' => $this->prefecture,
            'zone' => $this->zone,
            'role' => $this->role,
            'actif' => $this->actif,
            'derniere_connexion' => $this->derniere_connexion,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'naissances_count' => $this->whenCounted('naissances'),
        ];
    }
}
