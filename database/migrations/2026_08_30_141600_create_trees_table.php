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
        Schema::create('trees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seed_type_id')->constrained()->restrictOnDelete();

            $table->unsignedTinyInteger('nivel')->default(0);
            $table->unsignedTinyInteger('salud')->default(100);
            $table->unsignedTinyInteger('progreso')->default(0);
            $table->string('estado')->default('ACTIVE'); // ACTIVE | MATURE | DEAD | HARVESTED

            $table->timestamp('last_cared_at')->nullable();
            $table->timestamp('next_care_at')->nullable();
            $table->timestamp('last_decay_at')->nullable();
            $table->timestamp('harvested_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trees');
    }
};
