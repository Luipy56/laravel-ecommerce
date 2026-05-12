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

        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $names = $query->paginate($perPage, ['id', 'name', 'is_active']);

        $data = $names->getCollection()->map(fn ($n) => [
            'id' => $n->id,
            'name' => $n->name,
            'is_active' => (bool) $n->is_active,
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $names->currentPage(),
                'last_page' => $names->lastPage(),
                'per_page' => $names->perPage(),
                'total' => $names->total(),
            ],
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

    /**
     * Return all feature names with their nested features.
     * Supports search (matches feature name or feature value) and is_active filter.
     */
    public function indexWithFeatures(Request $request): JsonResponse
    {
        $query = FeatureName::query()->with(['features' => fn ($q) => $q->orderBy('value')]);

        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->trim() . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhereHas('features', fn ($sub) => $sub->where('value', 'like', $term));
            });
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $names = $query->orderBy('name')->get();

        $data = $names->map(fn (FeatureName $n) => [
            'id' => $n->id,
            'name' => $n->name,
            'is_active' => (bool) $n->is_active,
            'features' => $n->features->map(fn ($f) => [
                'id' => $f->id,
                'value' => $f->value,
                'is_active' => (bool) $f->is_active,
            ])->values()->all(),
        ])->values()->all();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function toggle(FeatureName $featureName): JsonResponse
    {
        $featureName->update(['is_active' => !$featureName->is_active]);

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
