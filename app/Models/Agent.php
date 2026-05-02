<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Agent extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'prefecture',
        'zone',
        'role',
        'actif',
        'derniere_connexion',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'derniere_connexion' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the naissances created by the agent.
     */
    public function naissances()
    {
        return $this->hasMany(Naissance::class);
    }

    /**
     * Check if agent has admin role.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if agent has specific role.
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }
}
