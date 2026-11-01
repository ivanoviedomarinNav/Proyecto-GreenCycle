<?php

namespace App\Exceptions;

use Carbon\CarbonInterface;
use Exception;
use Illuminate\Http\JsonResponse;

class TreeOnCooldownException extends Exception
{
    public function __construct(public readonly CarbonInterface $nextCareAt)
    {
        parent::__construct('El árbol se encuentra en período de enfriamiento.');
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
            'code' => 'TREE_ON_COOLDOWN',
            'next_care_at' => $this->nextCareAt->toIso8601ZuluString(),
        ], 409);
    }
}
