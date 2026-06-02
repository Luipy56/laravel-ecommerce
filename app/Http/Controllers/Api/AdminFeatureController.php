<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Support\CatalogLocale;
use App\Support\CatalogTranslationSync;
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
        $query = Feature::query()
            ->with(['featureName.translations', 'translations'])
            ->orderBy('feature_name_id')
            ->orderByTranslatedValue();

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
            $term = '%'.$request->string('search')->trim().'%';
            $loc = CatalogLocale::normalize(app()->getLocale());
            $query->where(function ($q) use ($term, $loc) {
                $q->whereHas('translations', fn ($ft) => $ft->where('locale', $loc)->where('value', 'like', $term))
                    ->orWhereHas('featureName.translations', fn ($nt) => $nt->where('locale', $loc)->where('name', 'like', $term));
            });
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $features = $query->paginate($perPage);

        $data = $features->getCollection()->map(fn ($f) => [
            'id' => $f->id,
            'feature_name_id' => $f->feature_name_id,
            'feature_name' => $f->featureName?->name,
            'feature_name_code' => $f->featureName?->code,
            'value' => $f->value,
            'is_active' => (bool) $f->is_active,
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $features->currentPage(),
                'last_page' => $features->lastPage(),
                'per_page' => $features->perPage(),
                'total' => $features->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'feature_name_id' => ['required', 'exists:feature_names,id'],
            'value' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'translations' => ['nullable', 'array'],
        ]);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $feature = Feature::create([
            'feature_name_id' => $validated['feature_name_id'],
            'is_active' => $validated['is_active'],
        ]);
        $by = ['ca' => ['value' => $validated['value']]];
        if (is_array($request->input('translations'))) {
            foreach ($request->input('translations') as $loc => $payload) {
                if (in_array((string) $loc, CatalogLocale::SUPPORTED, true) && is_array($payload)) {
                    $by[(string) $loc] = array_merge($by[(string) $loc] ?? [], $payload);
                }
            }
        }
        CatalogTranslationSync::syncFeatureTranslations($feature, $by);

        return response()->json([
            'success' => true,
            'data' => $this->featureToArray($feature->load(['featureName.translations', 'translations'])),
        ], 201);
    }

    public function show(Feature $feature): JsonResponse
    {
        $feature->load(['featureName.translations', 'translations']);

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
            'translations' => ['nullable', 'array'],
        ]);
        $feature->update([
            'feature_name_id' => $validated['feature_name_id'],
            'is_active' => $validated['is_active'] ?? $feature->is_active,
        ]);
        $by = ['ca' => ['value' => $validated['value']]];
        if (is_array($request->input('translations'))) {
            foreach ($request->input('translations') as $loc => $payload) {
                if (in_array((string) $loc, CatalogLocale::SUPPORTED, true) && is_array($payload)) {
                    $by[(string) $loc] = array_merge($by[(string) $loc] ?? [], $payload);
                }
            }
        }
        CatalogTranslationSync::syncFeatureTranslations($feature, $by);

        return response()->json([
            'success' => true,
            'data' => $this->featureToArray($feature->fresh()->load(['featureName.translations', 'translations'])),
        ]);
    }

    public function destroy(Feature $feature): JsonResponse
    {
        $feature->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    public function toggle(Feature $feature): JsonResponse
    {
        $feature->update(['is_active' => ! $feature->is_active]);

        return response()->json([
            'success' => true,
            'data' => $this->featureToArray($feature->fresh()->load(['featureName.translations', 'translations'])),
        ]);
    }

    private function featureToArray(Feature $f): array
    {
        return [
            'id' => $f->id,
            'feature_name_id' => $f->feature_name_id,
            'feature_name' => $f->featureName?->name,
            'feature_name_code' => $f->featureName?->code,
            'value' => $f->value,
            'is_active' => (bool) $f->is_active,
            'translations' => $f->relationLoaded('translations')
                ? $f->translations->keyBy('locale')->map(fn ($t) => ['value' => $t->value])->all()
                : [],
        ];
    }
}
