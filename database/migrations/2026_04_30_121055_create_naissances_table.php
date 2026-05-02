<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('naissances', function (Blueprint $table) {
            $table->id();
            $table->string('numero_unique', 50)->unique();
            $table->string('nom_enfant');
            $table->string('prenom_enfant');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('heure_naissance')->nullable();
            $table->enum('sexe', ['M', 'F']);
            $table->string('nom_pere');
            $table->string('prenom_pere');
            $table->string('profession_pere')->nullable();
            $table->string('nom_mere');
            $table->string('prenom_mere');
            $table->string('profession_mere')->nullable();
            $table->string('adresse_parents');
            $table->string('telephone_parents')->nullable();
            $table->string('declarant_nom');
            $table->string('declarant_prenom');
            $table->string('declarant_lien');
            $table->string('officier_etat_civil');
            $table->string('numero_acte')->unique();
            $table->date('date_enregistrement');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('agent_id')->constrained('agents');
            $table->string('hash_sha256', 64)->unique(); // SHA-256
            $table->string('hash_precedent', 64)->nullable(); // Chainage
            $table->string('qr_code_path')->nullable();
            $table->string('qr_code_url')->nullable();
            $table->string('logo_path')->default('storage/logos/logo_naissancechain.png');
            $table->enum('statut', [
                'en_attente',
                'synchronise',
                'valide',
                'rejete'
            ])->default('en_attente');
            $table->boolean('hors_ligne')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('naissances');
    }
};
