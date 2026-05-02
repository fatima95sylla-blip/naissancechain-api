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
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('naissance_id')->constrained('naissances');
            $table->string('institution')->nullable();
            $table->enum('type_institution', [
                'ecole',
                'hopital',
                'administration',
                'autre'
            ])->default('autre');
            $table->string('nom_verificateur')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('resultat')->default(true); // true=valide
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
