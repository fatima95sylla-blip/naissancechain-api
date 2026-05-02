<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Naissance",
 *     title="Acte de Naissance",
 *     description="Modèle représentant un acte de naissance dans NaissanceChain",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID unique de l'acte",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="numero_unique",
 *         type="string",
 *         description="Numéro unique de l'acte généré par le système",
 *         example="NC-2026-000001"
 *     ),
 *     @OA\Property(
 *         property="nom_enfant",
 *         type="string",
 *         description="Nom de famille de l'enfant",
 *         example="TOURE"
 *     ),
 *     @OA\Property(
 *         property="prenom_enfant",
 *         type="string",
 *         description="Prénom de l'enfant",
 *         example="Ibrahim"
 *     ),
 *     @OA\Property(
 *         property="date_naissance",
 *         type="string",
 *         format="date",
 *         description="Date de naissance de l'enfant",
 *         example="2026-03-20"
 *     ),
 *     @OA\Property(
 *         property="lieu_naissance",
 *         type="string",
 *         description="Lieu de naissance",
 *         example="Hôpital Donka, Conakry"
 *     ),
 *     @OA\Property(
 *         property="sexe",
 *         type="string",
 *         enum={"M", "F"},
 *         description="Sexe de l'enfant",
 *         example="M"
 *     ),
 *     @OA\Property(
 *         property="numero_acte",
 *         type="string",
 *         description="Numéro officiel de l'acte",
 *         example="ACTE-2026-002"
 *     ),
 *     @OA\Property(
 *         property="statut",
 *         type="string",
 *         enum={"valide", "en_attente", "synchronise", "annule"},
 *         description="Statut de l'acte",
 *         example="valide"
 *     ),
 *     @OA\Property(
 *         property="hash_sha256",
 *         type="string",
 *         description="Hash SHA256 pour l'intégrité blockchain",
 *         example="abc123def456789..."
 *     ),
 *     @OA\Property(
 *         property="qr_code_url",
 *         type="string",
 *         description="URL du code QR généré",
 *         example="/storage/qr/abc123.png"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Date de création de l'enregistrement",
 *         example="2026-04-30T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Date de dernière modification",
 *         example="2026-04-30T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="agent",
 *         type="object",
 *         description="Agent qui a créé l'acte",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="nom", type="string", example="BARRY"),
 *         @OA\Property(property="prenom", type="string", example="Mamadou")
 *     )
 * )
 */

class Naissance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_unique',
        'nom_complet_enfant',
        'sexe_enfant',
        'date_naissance_enfant',
        'heure_naissance_enfant',
        'nom_complet_pere',
        'profession_pere',
        'date_naissance_pere',
        'nom_complet_mere',
        'profession_mere',
        'date_naissance_mere',
        'ville_region',
        'quartier_secteur',
        'lieu_naissance_enfant',
        'nom_declarant',
        'numero_declarant',
        'officier_etat_civil',
        'numero_acte',
        'date_enregistrement',
        'latitude',
        'longitude',
        'hash_sha256',
        'hash_precedent',
        'qr_code_path',
        'qr_code_url',
        'logo_path',
        'statut',
        'hors_ligne',
        'synced_at',
        'agent_id',
    ];

    protected $casts = [
        'date_naissance_enfant' => 'date',
        'date_naissance_pere' => 'date',
        'date_naissance_mere' => 'date',
        'heure_naissance_enfant' => 'datetime:H:i',
        'date_enregistrement' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'hors_ligne' => 'boolean',
        'synced_at' => 'datetime',
    ];

    /**
     * Get the agent that created the birth record.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the child's name (already stored as complete name).
     */
    public function getNomCompletEnfantAttribute()
    {
        return $this->nom_complet_enfant;
    }

    /**
     * Get the father's name (already stored as complete name).
     */
    public function getNomCompletPereAttribute()
    {
        return $this->nom_complet_pere;
    }

    /**
     * Get the mother's name (already stored as complete name).
     */
    public function getNomCompletMereAttribute()
    {
        return $this->nom_complet_mere;
    }

    /**
     * Check if the record is synchronized.
     */
    public function estSynchronise()
    {
        return $this->statut === 'synchronise' || $this->statut === 'valide';
    }

    /**
     * Scope a query to only include offline records.
     */
    public function scopeHorsLigne($query)
    {
        return $query->where('hors_ligne', true);
    }

    /**
     * Scope a query to only include pending sync records.
     */
    public function scopeEnAttenteSync($query)
    {
        return $query->where('statut', 'en_attente');
    }
}
