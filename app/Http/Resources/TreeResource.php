<?php

namespace App\Http\Resources;

use App\Models\Tree;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tree
 */
class TreeResource extends JsonResource
{
    /**
     * Único punto que define el contrato JSON de un árbol (level, health,
     * progress, status). Las columnas de la base de datos no se exponen tal cual.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seed_type' => $this->whenLoaded('seedType', fn () => [
                'id' => $this->seedType->id,
                'name' => $this->seedType->name,
                'cuidados_por_nivel' => $this->seedType->cuidados_por_nivel,
            ]),
            'level' => $this->nivel,
            'health' => $this->salud,
            'progress' => $this->progreso,
            'status' => $this->estado,
            'last_cared_at' => $this->last_cared_at?->toIso8601ZuluString(),
            'next_care_at' => $this->next_care_at?->toIso8601ZuluString(),
            'harvested_at' => $this->harvested_at?->toIso8601ZuluString(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
