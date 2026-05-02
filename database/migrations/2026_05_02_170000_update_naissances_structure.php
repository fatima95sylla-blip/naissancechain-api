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
        Schema::table('naissances', function (Blueprint $table) {
            // Supprimer les anciens champs
            $table->dropColumn([
                'nom_enfant',
                'prenom_enfant',
                'nom_pere',
                'prenom_pere',
                'profession_pere',
                'nom_mere',
                'prenom_mere',
                'profession_mere',
                'adresse_parents',
                'telephone_parents',
                'declarant_nom',
                'declarant_prenom',
                'declarant_lien',
                'lieu_naissance'
            ]);
        });

        Schema::table('naissances', function (Blueprint $table) {
            // Champs enfant
            $table->string('nom_complet_enfant');
            $table->enum('sexe_enfant', ['M', 'F']);
            $table->date('date_naissance_enfant');
            $table->time('heure_naissance_enfant');
            
            // Champs père
            $table->string('nom_complet_pere');
            $table->string('profession_pere');
            $table->date('date_naissance_pere')->nullable();
            
            // Champs mère
            $table->string('nom_complet_mere');
            $table->string('profession_mere');
            $table->date('date_naissance_mere')->nullable();
            
            // Autres informations
            $table->string('ville_region');
            $table->string('quartier_secteur');
            $table->string('lieu_naissance_enfant');
            $table->string('nom_declarant');
            $table->string('numero_declarant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('naissances', function (Blueprint $table) {
            // Supprimer les nouveaux champs
            $table->dropColumn([
                'nom_complet_enfant',
                'sexe_enfant',
                'date_naissance_enfant',
                'heure_naissance_enfant',
                'nom_complet_pere',
                'profession_pere',
                'date_naissance_pere',
                'nom_complet_mere',
                'profession_mere',
                'date_naissance_mere',
                'ville_region',
                'quartier_secteur',
                'lieu_naissance_enfant',
                'nom_declarant',
                'numero_declarant'
            ]);
        });

        Schema::table('naissances', function (Blueprint $table) {
            // Restaurer les anciens champs
            $table->string('nom_enfant');
            $table->string('prenom_enfant');
            $table->string('nom_pere');
            $table->string('prenom_pere');
            $table->string('profession_pere');
            $table->string('nom_mere');
            $table->string('prenom_mere');
            $table->string('profession_mere');
            $table->string('adresse_parents');
            $table->string('telephone_parents');
            $table->string('declarant_nom');
            $table->string('declarant_prenom');
            $table->string('declarant_lien');
            $table->string('lieu_naissance');
        });
    }
};
