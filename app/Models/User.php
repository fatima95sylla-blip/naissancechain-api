<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telephone',
        'prefecture',
        'zone',
        'actif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
        ];
    }

    /**
     * Get the naissances created by the user.
     */
    public function naissances()
    {
        return $this->hasMany(Naissance::class);
    }

    /**
     * Check if user has admin role.
     */
    public function isAdmin()
    {
        return $this->hasRole('ADMIN');
    }

    /**
     * Check if user has specific role.
     */
    public function hasRoleName($role)
    {
        return $this->hasRole($role);
    }

    /**
     * Get user's primary role.
     */
    public function getPrimaryRoleAttribute()
    {
        return $this->roles->first()?->name;
    }
}
