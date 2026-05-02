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
        Schema::create('blockchain_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('naissance_id')->constrained()->onDelete('cascade');
            $table->string('hash', 64)->unique();
            $table->string('previous_hash', 64)->nullable();
            $table->timestamp('timestamp');
            $table->integer('block_number');
            $table->string('data_signature', 64);
            $table->boolean('verified')->default(true);
            $table->timestamps();

            $table->index(['naissance_id', 'hash']);
            $table->index(['block_number']);
            $table->index(['timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_records');
    }
};
