<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNaissanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Champs enfant
            'nom_complet_enfant' => ['required', 'string', 'max:255'],
            'sexe_enfant' => ['required', 'string', 'in:M,F'],
            'date_naissance_enfant' => ['required', 'date', 'before_or_equal:today'],
            'heure_naissance_enfant' => ['required', 'string', 'max:10'],
            
            // Champs père
            'nom_complet_pere' => ['required', 'string', 'max:255'],
            'profession_pere' => ['required', 'string', 'max:255'],
            'date_naissance_pere' => ['nullable', 'date', 'before_or_equal:today'],
            
            // Champs mère
            'nom_complet_mere' => ['required', 'string', 'max:255'],
            'profession_mere' => ['required', 'string', 'max:255'],
            'date_naissance_mere' => ['nullable', 'date', 'before_or_equal:today'],
            
            // Autres informations
            'ville_region' => ['required', 'string', 'max:255'],
            'quartier_secteur' => ['required', 'string', 'max:255'],
            'lieu_naissance_enfant' => ['required', 'string', 'max:255'],
            'nom_declarant' => ['required', 'string', 'max:255'],
            'numero_declarant' => ['required', 'string', 'max:50'],
            
            // Champs système
            'officier_etat_civil' => ['required', 'string', 'max:255'],
            'numero_acte' => ['required', 'string', 'max:50', 'unique:naissances,numero_acte'],
            'date_enregistrement' => ['required', 'date', 'before_or_equal:today'],
            'latitude' => ['nullable', 'decimal:7'],
            'longitude' => ['nullable', 'decimal:7'],
            'hors_ligne' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            // Messages champs enfant
            'nom_complet_enfant.required' => 'Le nom complet de l\'enfant est obligatoire',
            'sexe_enfant.required' => 'Le sexe de l\'enfant est obligatoire',
            'sexe_enfant.in' => 'Le sexe doit être M (Masculin) ou F (Féminin)',
            'date_naissance_enfant.required' => 'La date de naissance de l\'enfant est obligatoire',
            'date_naissance_enfant.before_or_equal' => 'La date de naissance ne peut pas être dans le futur',
            'heure_naissance_enfant.required' => 'L\'heure de naissance est obligatoire',
            
            // Messages champs père
            'nom_complet_pere.required' => 'Le nom complet du père est obligatoire',
            'profession_pere.required' => 'La profession du père est obligatoire',
            'date_naissance_pere.before_or_equal' => 'La date de naissance du père ne peut pas être dans le futur',
            
            // Messages champs mère
            'nom_complet_mere.required' => 'Le nom complet de la mère est obligatoire',
            'profession_mere.required' => 'La profession de la mère est obligatoire',
            'date_naissance_mere.before_or_equal' => 'La date de naissance de la mère ne peut pas être dans le futur',
            
            // Messages autres informations
            'ville_region.required' => 'La ville/région est obligatoire',
            'quartier_secteur.required' => 'Le quartier/secteur est obligatoire',
            'lieu_naissance_enfant.required' => 'Le lieu de naissance est obligatoire',
            'nom_declarant.required' => 'Le nom du déclarant est obligatoire',
            'numero_declarant.required' => 'Le numéro du déclarant est obligatoire',
            
            // Messages champs système
            'officier_etat_civil.required' => 'Le nom de l\'officier de l\'état civil est obligatoire',
            'numero_acte.required' => 'Le numéro de l\'acte est obligatoire',
            'numero_acte.unique' => 'Ce numéro d\'acte existe déjà',
            'date_enregistrement.required' => 'La date d\'enregistrement est obligatoire',
            'date_enregistrement.before_or_equal' => 'La date d\'enregistrement ne peut pas être dans le futur',
        ];
    }
}
