<?php

namespace App\Exceptions;

use App\Models\Tree;
use Exception;
use Illuminate\Http\JsonResponse;

class TreeNotCarableException extends Exception
{
    public function __construct(public readonly string $treeStatus)
    {
        parent::__construct(match ($treeStatus) {
            Tree::DEAD => 'El árbol está muerto y ya no puede recibir cuidados.',
            Tree::MATURE => 'El árbol ya alcanzó la madurez y no puede recibir más cuidados.',
            Tree::HARVESTED => 'El árbol ya fue cosechado y no puede recibir cuidados.',
            default => 'El árbol no puede recibir cuidados en su estado actual.',
        });
    }

    /**
     * Es un error esperado del dominio: no se registra en el log.
     */
    public function report(): void
    {
        //
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => $this->getMessage(),
            'code' => 'TREE_NOT_CAREABLE',
            'status' => $this->treeStatus,
        ], 409);
    }
}
