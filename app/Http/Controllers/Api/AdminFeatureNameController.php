<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeatureName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminFeatureNameController extends Controller
{
    /**
     * List all feature names for admin (e.g. dropdown when creating/editing a feature).
     * Query params: search (optional), is_active (optional: 1|0).
     */
    public function index(Request $request): JsonResponse
    {
        $query = FeatureName::query()->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search')->trim() . '%');
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $names = $query->get(['id', 'name', 'is_active']);

        $data = $names->map(fn ($n) => [
            'id' => $n->id,
            'name' => $n->name,
            'is_active' => (bool) $n->is_active,
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $featureName = FeatureName::create($validated);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $featureName->id,
                'name' => $featureName->name,
                'is_active' => (bool) $featureName->is_active,
            ],
        ], 201);
    }

    public function show(FeatureName $featureName): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $featureName->id,
                'name' => $featureName->name,
                'is_active' => (bool) $featureName->is_active,
            ],
        ]);
    }

    public function update(Request $request, FeatureName $featureName): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $featureName->update($validated);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $featureName->id,
                'name' => $featureName->name,
                'is_active' => (bool) $featureName->is_active,
            ],
        ]);
    }
}
