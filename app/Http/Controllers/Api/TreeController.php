<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTreeRequest;
use App\Http\Resources\TreeResource;
use App\Models\Tree;
use App\Services\TreeCareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TreeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $trees = $request->user()
            ->trees()
            ->with('seedType')
            ->latest('id')
            ->get();

        return TreeResource::collection($trees);
    }

    public function show(Request $request, Tree $tree): TreeResource
    {
        $this->authorize('view', $tree);

        return new TreeResource($tree->load('seedType'));
    }

    public function store(StoreTreeRequest $request): TreeResource
    {
        $tree = $request->user()->trees()->create([
            'seed_type_id' => $request->validated('seed_type_id'),
            'nivel' => 0,
            'salud' => 100,
            'progreso' => 0,
            'estado' => Tree::ACTIVE,
        ]);

        // JsonResource responde 201 automáticamente al ser un registro recién creado.
        return new TreeResource($tree->load('seedType'));
    }

    /**
     * POST /api/trees/{tree}/care
     *
     * 1) Propiedad (403) 2) Estado terminal (409) 3) Cooldown (409) 4) Cuidado (200).
     */
    public function care(Tree $tree, TreeCareService $service): JsonResponse
    {
        $this->authorize('care', $tree);

        $tree = $service->care($tree);

        return response()->json([
            'message' => 'Cuidado aplicado con éxito',
            'tree' => new TreeResource($tree),
        ]);
    }
}
