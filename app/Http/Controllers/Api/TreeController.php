<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTreeRequest;
use App\Models\Tree;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    public function index(Request $request)
    {
        $trees = $request->user()
            ->trees()
            ->with('seedType')
            ->get();

        return response()->json($trees);
    }

    public function show(Request $request, Tree $tree)
    {
        $this->authorize('view', $tree);

        return response()->json($tree->load('seedType'));
    }

    public function store(StoreTreeRequest $request)
    {
        $tree = $request->user()->trees()->create([
            'seed_type_id' => $request->seed_type_id,
            'nivel' => 0,
            'salud' => 100,
            'progreso' => 0,
            'estado' => Tree::ACTIVE,
        ]);

        return response()->json($tree->load('seedType'), 201);
    }
}
