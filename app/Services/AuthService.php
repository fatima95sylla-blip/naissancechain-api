<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate user and return token.
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        if (!$user->actif) {
            throw ValidationException::withMessages([
                'email' => ['Votre compte a été désactivé.'],
            ]);
        }

        // Create token
        $token = $user->createToken('naissancechain-api')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Register new user and return token.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telephone' => $data['telephone'] ?? null,
            'prefecture' => $data['prefecture'] ?? null,
            'zone' => $data['zone'] ?? null,
            'actif' => true,
        ]);

        // Assign role
        $user->assignRole($data['role']);

        // Create token
        $token = $user->createToken('naissancechain-api')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Logout user by revoking tokens.
     */
    public function logout($user): bool
    {
        $user->tokens()->delete();
        return true;
    }

    /**
     * Get authenticated user info.
     */
    public function me($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'telephone' => $user->telephone,
            'prefecture' => $user->prefecture,
            'zone' => $user->zone,
            'actif' => $user->actif,
            'role' => $user->getPrimaryRoleAttribute(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'created_at' => $user->created_at,
        ];
    }
}
