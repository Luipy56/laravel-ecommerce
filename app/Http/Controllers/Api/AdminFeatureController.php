<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminFeatureController extends Controller
{
    /**
     * List all features for admin (management list and product form). Includes is_active.
     * Query params: search (optional, matches type name or value), feature_name_id (optional), active_only (optional).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Feature::query()->with('featureName:id,name')->orderBy('feature_name_id')->orderBy('value');

        if ($request->has('active_only') && $request->boolean('active_only')) {
            $query->active();
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        if ($request->filled('feature_name_id')) {
            $query->where('feature_name_id', $request->integer('feature_name_id'));
        }
        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->trim() . '%';
            $query->where(function ($q) use ($term) {
                $q->where('value', 'like', $term)
                    ->orWhereHas('featureName', fn ($sub) => $sub->where('name', 'like', $term));
            });
        }

        $features = $query->get(['id', 'feature_name_id', 'value', 'is_active']);

        $data = $features->map(fn ($f) => [
            'id' => $f->id,
            'feature_name_id' => $f->feature_name_id,
            'feature_name' => $f->featureName?->name,
            'value' => $f->value,
            'is_active' => (bool) $f->is_active,
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'feature_name_id' => ['required', 'exists:feature_names,id'],
            'value' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $feature = Feature::create($validated);

        return response()->json([
            'success' => true,
            'data' => $this->featureToArray($feature->load('featureName')),
        ], 201);
    }

    public function show(Feature $feature): JsonResponse
    {
        $feature->load('featureName');

        return response()->json([
            'success' => true,
            'data' => $this->featureToArray($feature),
        ]);
    }

    public function update(Request $request, Feature $feature): JsonResponse
    {
        $validated = $request->validate([
            'feature_name_id' => ['required', 'exists:feature_names,id'],
            'value' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $feature->update($validated);

        return response()->json([
            'success' => true,
            'data' => $this->featureToArray($feature->fresh()->load('featureName')),
        ]);
    }

    public function destroy(Feature $feature): JsonResponse
    {
        $feature->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    private function featureToArray(Feature $f): array
    {
        return [
            'id' => $f->id,
            'feature_name_id' => $f->feature_name_id,
            'feature_name' => $f->featureName?->name,
            'value' => $f->value,
            'is_active' => (bool) $f->is_active,
        ];
    }
}
