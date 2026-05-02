<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncRequest extends FormRequest
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
            'naissances' => ['required', 'array', 'min:1'],
            'naissances.*.id' => ['required', 'integer'],
            'naissances.*.numero_unique' => ['required', 'string', 'max:50'],
            'naissances.*.nom_enfant' => ['required', 'string', 'max:255'],
            'naissances.*.prenom_enfant' => ['required', 'string', 'max:255'],
            'naissances.*.date_naissance' => ['required', 'date'],
            'naissances.*.lieu_naissance' => ['required', 'string', 'max:255'],
            'naissances.*.sexe' => ['required', 'string', 'in:M,F'],
            'naissances.*.nom_pere' => ['required', 'string', 'max:255'],
            'naissances.*.prenom_pere' => ['required', 'string', 'max:255'],
            'naissances.*.nom_mere' => ['required', 'string', 'max:255'],
            'naissances.*.prenom_mere' => ['required', 'string', 'max:255'],
            'naissances.*.adresse_parents' => ['required', 'string', 'max:500'],
            'naissances.*.declarant_nom' => ['required', 'string', 'max:255'],
            'naissances.*.declarant_prenom' => ['required', 'string', 'max:255'],
            'naissances.*.declarant_lien' => ['required', 'string', 'max:100'],
            'naissances.*.officier_etat_civil' => ['required', 'string', 'max:255'],
            'naissances.*.numero_acte' => ['required', 'string', 'max:50'],
            'naissances.*.date_enregistrement' => ['required', 'date'],
            'naissances.*.agent_id' => ['required', 'integer'],
            'naissances.*.hash_sha256' => ['required', 'string', 'size:64'],
            'naissances.*.hash_precedent' => ['nullable', 'string', 'size:64'],
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
            'naissances.required' => 'Les données de naissance sont obligatoires',
            'naissances.array' => 'Les données de naissance doivent être un tableau',
            'naissances.min' => 'Au moins une donnée de naissance est requise',
        ];
    }
}
