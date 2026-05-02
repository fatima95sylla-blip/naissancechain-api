<?php

namespace Database\Factories;

use App\Models\Naissance;
use App\Models\Agent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Naissance>
 */
class NaissanceFactory extends Factory
{
    protected $model = Naissance::class;

    public function definition(): array
    {
        return [
            'numero_unique' => 'NS-' . $this->faker->unique()->numerify('##########'),
            'nom_enfant' => $this->faker->lastName,
            'prenom_enfant' => $this->faker->firstName,
            'date_naissance' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'lieu_naissance' => $this->faker->city,
            'heure_naissance' => $this->faker->time('H:i'),
            'sexe' => $this->faker->randomElement(['M', 'F']),
            'nom_pere' => $this->faker->lastName,
            'prenom_pere' => $this->faker->firstNameMale,
            'profession_pere' => $this->faker->jobTitle,
            'nom_mere' => $this->faker->lastName,
            'prenom_mere' => $this->faker->firstNameFemale,
            'profession_mere' => $this->faker->jobTitle,
            'adresse_parents' => $this->faker->address,
            'telephone_parents' => $this->faker->phoneNumber,
            'declarant_nom' => $this->faker->lastName,
            'declarant_prenom' => $this->faker->firstName,
            'declarant_lien' => $this->faker->randomElement(['Père', 'Mère', 'Grand-père', 'Grand-mère', 'Oncle', 'Tante']),
            'officier_etat_civil' => $this->faker->name,
            'numero_acte' => 'ACTE-' . $this->faker->unique()->numerify('##########'),
            'date_enregistrement' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'latitude' => $this->faker->latitude(-90, 90),
            'longitude' => $this->faker->longitude(-180, 180),
            'agent_id' => Agent::factory(),
            'hash_sha256' => hash('sha256', $this->faker->text),
            'hash_precedent' => null,
            'qr_code_path' => null,
            'qr_code_url' => null,
            'statut' => $this->faker->randomElement(['en_attente', 'synchronise', 'valide', 'rejete']),
            'hors_ligne' => false,
            'synced_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
