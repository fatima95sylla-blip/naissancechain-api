<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class BaseApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()->toArray()
        ], 422);
        
        throw new ValidationException($validator, $response);
    }
    
    protected function sanitizeInput(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $input[$key] = $this->sanitizeInput($value);
            }
        }
        
        return $input;
    }
    
    private function sanitizeString(string $value): string
    {
        // Suppression des tags HTML/PHP
        $value = strip_tags($value);
        
        // Échappement des caractères spéciaux
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        // Suppression des caractères de contrôle
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
        
        // Limitation de la longueur
        if (strlen($value) > 1000) {
            $value = substr($value, 0, 1000);
        }
        
        return trim($value);
    }
    
    public function validated(): array
    {
        $validated = parent::validated();
        return $this->sanitizeInput($validated);
    }
}
