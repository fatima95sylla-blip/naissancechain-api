<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z\s\-\']+$/' // Noms avec accents, tirets, apostrophes
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'max:128',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/' // Au moins 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial
            ],
            'password_confirmation' => [
                'required',
                'string',
                'same:password'
            ],
            'telephone' => [
                'required',
                'string',
                'max:20',
                'min:8',
                'regex:/^[+]?[0-9\s\-\(\)]+$/'
            ],
            'prefecture' => [
                'required',
                'string',
                'max:255',
                'min:2'
            ],
            'zone' => [
                'required',
                'string',
                'max:255',
                'min:2'
            ],
            'role' => [
                'required',
                'string',
                'in:ADMIN,AGENT,ECOLE,SANTE'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire',
            'name.string' => 'Le nom doit être une chaîne de caractères',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères',
            'name.min' => 'Le nom doit contenir au moins 2 caractères',
            'name.regex' => 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes',
            
            'email.required' => 'L\'adresse email est obligatoire',
            'email.string' => 'L\'adresse email doit être une chaîne de caractères',
            'email.email' => 'L\'adresse email doit être valide',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères',
            'email.unique' => 'Cette adresse email est déjà utilisée',
            'email.regex' => 'Le format de l\'adresse email est invalide',
            
            'password.required' => 'Le mot de passe est obligatoire',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.max' => 'Le mot de passe ne peut pas dépasser 128 caractères',
            'password.confirmed' => 'La confirmation du mot de passe est obligatoire',
            'password.regex' => 'Le mot de passe doit contenir au moins 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial',
            
            'password_confirmation.required' => 'La confirmation du mot de passe est obligatoire',
            'password_confirmation.same' => 'La confirmation du mot de passe ne correspond pas',
            
            'telephone.required' => 'Le téléphone est obligatoire',
            'telephone.string' => 'Le téléphone doit être une chaîne de caractères',
            'telephone.max' => 'Le téléphone ne peut pas dépasser 20 caractères',
            'telephone.min' => 'Le téléphone doit contenir au moins 8 caractères',
            'telephone.regex' => 'Le format du téléphone est invalide',
            
            'prefecture.required' => 'La préfecture est obligatoire',
            'prefecture.string' => 'La préfecture doit être une chaîne de caractères',
            'prefecture.max' => 'La préfecture ne peut pas dépasser 255 caractères',
            'prefecture.min' => 'La préfecture doit contenir au moins 2 caractères',
            
            'zone.required' => 'La zone est obligatoire',
            'zone.string' => 'La zone doit être une chaîne de caractères',
            'zone.max' => 'La zone ne peut pas dépasser 255 caractères',
            'zone.min' => 'La zone doit contenir au moins 2 caractères',
            
            'role.required' => 'Le rôle est obligatoire',
            'role.string' => 'Le rôle doit être une chaîne de caractères',
            'role.in' => 'Le rôle doit être l\'un des suivants: ADMIN, AGENT, ECOLE, SANTE',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nom complet',
            'email' => 'Adresse email',
            'password' => 'Mot de passe',
            'password_confirmation' => 'Confirmation du mot de passe',
            'telephone' => 'Téléphone',
            'prefecture' => 'Préfecture',
            'zone' => 'Zone',
            'role' => 'Rôle',
        ];
    }
}
