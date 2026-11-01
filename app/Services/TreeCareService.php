<?php

namespace App\Services;

use App\Exceptions\TreeNotCarableException;
use App\Exceptions\TreeOnCooldownException;
use App\Models\Tree;
use Illuminate\Support\Facades\DB;

class TreeCareService
{
    /**
     * Aplica un cuidado a un árbol. Toda la regla vive aquí; el cliente no
     * envía ningún dato que influya en el resultado.
     *
     * Orden de validación: estado terminal, cooldown y, solo entonces, cambios.
     *
     * @throws TreeNotCarableException
     * @throws TreeOnCooldownException
     */
    public function care(Tree $tree): Tree
    {
        return DB::transaction(function () use ($tree) {
            // Se vuelve a leer la fila bloqueada: dos peticiones simultáneas
            // no pueden aplicar el mismo cuidado dos veces.
            $tree = Tree::query()
                ->with('seedType')
                ->lockForUpdate()
                ->findOrFail($tree->getKey());

            $this->ensureCanReceiveCare($tree);
            $this->ensureNotOnCooldown($tree);
            $this->applyCare($tree);

            return $tree;
        });
    }

    private function ensureCanReceiveCare(Tree $tree): void
    {
        if ($tree->estado !== Tree::ACTIVE) {
            throw new TreeNotCarableException($tree->estado);
        }
    }

    private function ensureNotOnCooldown(Tree $tree): void
    {
        if ($tree->next_care_at !== null && now()->lt($tree->next_care_at)) {
            throw new TreeOnCooldownException($tree->next_care_at);
        }
    }

    private function applyCare(Tree $tree): void
    {
        $now = now();
        $careForNextLevel = max(1, $tree->seedType->cuidados_por_nivel);
        $maxLevel = config('greencycle.max_level');

        $tree->progreso += 1;
        $tree->salud = min(
            config('greencycle.max_health'),
            $tree->salud + config('greencycle.health_per_care'),
        );

        if ($tree->progreso >= $careForNextLevel) {
            $tree->nivel = min($maxLevel, $tree->nivel + 1);
            $tree->progreso = 0;
        }

        if ($tree->nivel >= $maxLevel) {
            $tree->estado = Tree::MATURE;
        }

        $tree->last_cared_at = $now;
        $tree->next_care_at = $now->copy()->addMinutes(config('greencycle.care_cooldown_minutes'));

        $tree->save();
    }
}
