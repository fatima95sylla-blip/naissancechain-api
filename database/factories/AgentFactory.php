<?php

namespace Database\Factories;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent>
 */
class AgentFactory extends Factory
{
    protected $model = Agent::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'),
            'telephone' => $this->faker->phoneNumber,
            'prefecture' => $this->faker->city,
            'zone' => $this->faker->randomElement(['Zone 1', 'Zone 2', 'Zone 3']),
            'role' => $this->faker->randomElement(['admin', 'agent', 'ecole', 'sante']),
            'actif' => true,
            'derniere_connexion' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
