<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índice para buscar efectos vigentes por árbol e ítem
        Schema::table('active_effects', function (Blueprint $table) {
            $table->index(['tree_id', 'item_id', 'expires_at'], 'active_effects_tree_item_expires_idx');
        });

        // El resto es específico de PostgreSQL (los tests usan SQLite en memoria)
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE inventories ADD CONSTRAINT inventories_quantity_nonnegative CHECK (quantity >= 0)');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_coins_nonnegative CHECK (coins >= 0)');
        DB::statement('ALTER TABLE items ADD CONSTRAINT items_cost_nonnegative CHECK (cost >= 0)');
        DB::statement('ALTER TABLE trees ADD CONSTRAINT trees_salud_range CHECK (salud BETWEEN 0 AND 100)');
        DB::statement('ALTER TABLE trees ADD CONSTRAINT trees_nivel_range CHECK (nivel BETWEEN 0 AND 10)');
        DB::statement('ALTER TABLE trees ADD CONSTRAINT trees_progreso_nonnegative CHECK (progreso >= 0)');
        DB::statement("ALTER TABLE trees ADD CONSTRAINT trees_estado_valid CHECK (estado IN ('ACTIVE','MATURE','DEAD','HARVESTED'))");

        // Observación 4: conservar el historial al borrar usuarios
        Schema::table('trees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('active_effects', function (Blueprint $table) {
            $table->dropIndex('active_effects_tree_item_expires_idx');
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE inventories DROP CONSTRAINT inventories_quantity_nonnegative');
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_coins_nonnegative');
        DB::statement('ALTER TABLE items DROP CONSTRAINT items_cost_nonnegative');
        DB::statement('ALTER TABLE trees DROP CONSTRAINT trees_salud_range');
        DB::statement('ALTER TABLE trees DROP CONSTRAINT trees_nivel_range');
        DB::statement('ALTER TABLE trees DROP CONSTRAINT trees_progreso_nonnegative');
        DB::statement('ALTER TABLE trees DROP CONSTRAINT trees_estado_valid');

        Schema::table('trees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
