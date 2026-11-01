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
     * Único punto que define qué atributos de un árbol se exponen en el API.
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
            'nivel' => $this->nivel,
            'salud' => $this->salud,
            'progreso' => $this->progreso,
            'estado' => $this->estado,
            'last_cared_at' => $this->last_cared_at,
            'next_care_at' => $this->next_care_at,
            'harvested_at' => $this->harvested_at,
            'created_at' => $this->created_at,
        ];
    }
}
