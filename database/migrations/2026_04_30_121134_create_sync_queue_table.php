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
        Schema::create('sync_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents');
            $table->json('donnees'); // Données brutes envoyées
            $table->string('hash_local', 64); // Hash calculé offline
            $table->enum('statut', [
                'en_attente',
                'traite',
                'erreur'
            ])->default('en_attente');
            $table->text('erreur_message')->nullable();
            $table->integer('tentatives')->default(0);
            $table->timestamp('traite_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_queue');
    }
};
