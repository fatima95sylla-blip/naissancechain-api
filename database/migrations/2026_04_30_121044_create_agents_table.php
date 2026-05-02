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
    Schema::create('agents', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('prenom');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('telephone')->nullable();
        $table->string('prefecture');
        $table->string('zone')->nullable();
        $table->enum('role', ['admin', 'agent', 'ecole', 'sante'])
              ->default('agent');
        $table->boolean('actif')->default(true);
        $table->timestamp('derniere_connexion')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
