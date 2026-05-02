<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NaissanceRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'nom_enfant' => 'required|string|max:100|min:2',
            'prenom_enfant' => 'required|string|max:100|min:2',
            'date_naissance' => 'required|date|before:today|after:1900-01-01',
            'lieu_naissance' => 'required|string|max:255|min:3',
            'sexe' => 'required|in:M,F',
            
            'nom_pere' => 'required|string|max:100|min:2',
            'prenom_pere' => 'required|string|max:100|min:2',
            
            'nom_mere' => 'required|string|max:100|min:2',
            'prenom_mere' => 'required|string|max:100|min:2',
            
            'adresse_parents' => 'required|string|max:255|min:5',
            'telephone_parents' => 'required|string|max:20|min:8|regex:/^[+]?[0-9\s\-\(\)]+$/',
            
            'declarant_nom' => 'required|string|max:100|min:2',
            'declarant_prenom' => 'required|string|max:100|min:2',
            'declarant_lien' => 'required|string|max:50|in:Père,Mère,Tuteur,Autre',
            
            'officier_etat_civil' => 'required|string|max:100|min:3',
            'numero_acte' => 'required|string|max:50|min:3|unique:naissances,numero_acte',
            'date_enregistrement' => 'required|date|before_or_equal:today',
            
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            
            'hors_ligne' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'nom_enfant.required' => 'Le nom de l\'enfant est obligatoire',
            'nom_enfant.string' => 'Le nom de l\'enfant doit être une chaîne de caractères',
            'nom_enfant.max' => 'Le nom de l\'enfant ne peut pas dépasser 100 caractères',
            'nom_enfant.min' => 'Le nom de l\'enfant doit contenir au moins 2 caractères',
            
            'prenom_enfant.required' => 'Le prénom de l\'enfant est obligatoire',
            'prenom_enfant.string' => 'Le prénom de l\'enfant doit être une chaîne de caractères',
            'prenom_enfant.max' => 'Le prénom de l\'enfant ne peut pas dépasser 100 caractères',
            'prenom_enfant.min' => 'Le prénom de l\'enfant doit contenir au moins 2 caractères',
            
            'date_naissance.required' => 'La date de naissance est obligatoire',
            'date_naissance.date' => 'La date de naissance doit être une date valide',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui',
            'date_naissance.after' => 'La date de naissance doit être postérieure au 01/01/1900',
            
            'lieu_naissance.required' => 'Le lieu de naissance est obligatoire',
            'lieu_naissance.string' => 'Le lieu de naissance doit être une chaîne de caractères',
            'lieu_naissance.max' => 'Le lieu de naissance ne peut pas dépasser 255 caractères',
            'lieu_naissance.min' => 'Le lieu de naissance doit contenir au moins 3 caractères',
            
            'sexe.required' => 'Le sexe de l\'enfant est obligatoire',
            'sexe.in' => 'Le sexe doit être M (Masculin) ou F (Féminin)',
            
            'nom_pere.required' => 'Le nom du père est obligatoire',
            'nom_pere.string' => 'Le nom du père doit être une chaîne de caractères',
            'nom_pere.max' => 'Le nom du père ne peut pas dépasser 100 caractères',
            'nom_pere.min' => 'Le nom du père doit contenir au moins 2 caractères',
            
            'prenom_pere.required' => 'Le prénom du père est obligatoire',
            'prenom_pere.string' => 'Le prénom du père doit être une chaîne de caractères',
            'prenom_pere.max' => 'Le prénom du père ne peut pas dépasser 100 caractères',
            'prenom_pere.min' => 'Le prénom du père doit contenir au moins 2 caractères',
            
            'nom_mere.required' => 'Le nom de la mère est obligatoire',
            'nom_mere.string' => 'Le nom de la mère doit être une chaîne de caractères',
            'nom_mere.max' => 'Le nom de la mère ne peut pas dépasser 100 caractères',
            'nom_mere.min' => 'Le nom de la mère doit contenir au moins 2 caractères',
            
            'prenom_mere.required' => 'Le prénom de la mère est obligatoire',
            'prenom_mere.string' => 'Le prénom de la mère doit être une chaîne de caractères',
            'prenom_mere.max' => 'Le prénom de la mère ne peut pas dépasser 100 caractères',
            'prenom_mere.min' => 'Le prénom de la mère doit contenir au moins 2 caractères',
            
            'adresse_parents.required' => 'L\'adresse des parents est obligatoire',
            'adresse_parents.string' => 'L\'adresse des parents doit être une chaîne de caractères',
            'adresse_parents.max' => 'L\'adresse des parents ne peut pas dépasser 255 caractères',
            'adresse_parents.min' => 'L\'adresse des parents doit contenir au moins 5 caractères',
            
            'telephone_parents.required' => 'Le téléphone des parents est obligatoire',
            'telephone_parents.string' => 'Le téléphone des parents doit être une chaîne de caractères',
            'telephone_parents.max' => 'Le téléphone des parents ne peut pas dépasser 20 caractères',
            'telephone_parents.min' => 'Le téléphone des parents doit contenir au moins 8 caractères',
            'telephone_parents.regex' => 'Le format du téléphone est invalide',
            
            'declarant_nom.required' => 'Le nom du déclarant est obligatoire',
            'declarant_nom.string' => 'Le nom du déclarant doit être une chaîne de caractères',
            'declarant_nom.max' => 'Le nom du déclarant ne peut pas dépasser 100 caractères',
            'declarant_nom.min' => 'Le nom du déclarant doit contenir au moins 2 caractères',
            
            'declarant_prenom.required' => 'Le prénom du déclarant est obligatoire',
            'declarant_prenom.string' => 'Le prénom du déclarant doit être une chaîne de caractères',
            'declarant_prenom.max' => 'Le prénom du déclarant ne peut pas dépasser 100 caractères',
            'declarant_prenom.min' => 'Le prénom du déclarant doit contenir au moins 2 caractères',
            
            'declarant_lien.required' => 'Le lien du déclarant est obligatoire',
            'declarant_lien.string' => 'Le lien du déclarant doit être une chaîne de caractères',
            'declarant_lien.max' => 'Le lien du déclarant ne peut pas dépasser 50 caractères',
            'declarant_lien.in' => 'Le lien du déclarant doit être Père, Mère, Tuteur ou Autre',
            
            'officier_etat_civil.required' => 'Le nom de l\'officier d\'état civil est obligatoire',
            'officier_etat_civil.string' => 'Le nom de l\'officier d\'état civil doit être une chaîne de caractères',
            'officier_etat_civil.max' => 'Le nom de l\'officier d\'état civil ne peut pas dépasser 100 caractères',
            'officier_etat_civil.min' => 'Le nom de l\'officier d\'état civil doit contenir au moins 3 caractères',
            
            'numero_acte.required' => 'Le numéro d\'acte est obligatoire',
            'numero_acte.string' => 'Le numéro d\'acte doit être une chaîne de caractères',
            'numero_acte.max' => 'Le numéro d\'acte ne peut pas dépasser 50 caractères',
            'numero_acte.min' => 'Le numéro d\'acte doit contenir au moins 3 caractères',
            'numero_acte.unique' => 'Ce numéro d\'acte existe déjà',
            
            'date_enregistrement.required' => 'La date d\'enregistrement est obligatoire',
            'date_enregistrement.date' => 'La date d\'enregistrement doit être une date valide',
            'date_enregistrement.before_or_equal' => 'La date d\'enregistrement ne peut pas être postérieure à aujourd\'hui',
            
            'latitude.required' => 'La latitude est obligatoire',
            'latitude.numeric' => 'La latitude doit être un nombre',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90',
            
            'longitude.required' => 'La longitude est obligatoire',
            'longitude.numeric' => 'La longitude doit être un nombre',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180',
            
            'hors_ligne.boolean' => 'Le champ hors ligne doit être vrai ou faux',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nom_enfant' => 'Nom de l\'enfant',
            'prenom_enfant' => 'Prénom de l\'enfant',
            'date_naissance' => 'Date de naissance',
            'lieu_naissance' => 'Lieu de naissance',
            'sexe' => 'Sexe',
            'nom_pere' => 'Nom du père',
            'prenom_pere' => 'Prénom du père',
            'nom_mere' => 'Nom de la mère',
            'prenom_mere' => 'Prénom de la mère',
            'adresse_parents' => 'Adresse des parents',
            'telephone_parents' => 'Téléphone des parents',
            'declarant_nom' => 'Nom du déclarant',
            'declarant_prenom' => 'Prénom du déclarant',
            'declarant_lien' => 'Lien du déclarant',
            'officier_etat_civil' => 'Officier d\'état civil',
            'numero_acte' => 'Numéro d\'acte',
            'date_enregistrement' => 'Date d\'enregistrement',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'hors_ligne' => 'Hors ligne',
        ];
    }
}
