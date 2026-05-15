<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeatureName;
use App\Support\CatalogLocale;
use App\Support\CatalogTranslationSync;
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
        $query = FeatureName::query()->with('translations')->orderByTranslatedName();

        if ($request->filled('search')) {
            $term = '%'.$request->string('search')->trim().'%';
            $loc = CatalogLocale::normalize(app()->getLocale());
            $query->where(function ($q) use ($term, $loc) {
                $q->where('code', 'like', $term)
                    ->orWhereHas('translations', fn ($t) => $t->where('locale', $loc)->where('name', 'like', $term));
            });
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $names = $query->paginate($perPage);

        $data = collect($names->items())->map(fn (FeatureName $n) => [
            'id' => $n->id,
            'code' => $n->code,
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
            'code' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9][a-z0-9_-]*$/', 'unique:feature_names,code'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'translations' => ['nullable', 'array'],
        ]);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $featureName = FeatureName::create([
            'code' => $validated['code'],
            'is_active' => $validated['is_active'],
        ]);
        $by = ['ca' => ['name' => $validated['name']]];
        if (is_array($request->input('translations'))) {
            foreach ($request->input('translations') as $loc => $payload) {
                if (in_array((string) $loc, CatalogLocale::SUPPORTED, true) && is_array($payload)) {
                    $by[(string) $loc] = array_merge($by[(string) $loc] ?? [], $payload);
                }
            }
        }
        CatalogTranslationSync::syncFeatureNameTranslations($featureName, $by);
        $featureName->load('translations');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $featureName->id,
                'code' => $featureName->code,
                'name' => $featureName->name,
                'is_active' => (bool) $featureName->is_active,
                'translations' => $featureName->translations->keyBy('locale')->map(fn ($t) => ['name' => $t->name])->all(),
            ],
        ], 201);
    }

    public function show(FeatureName $featureName): JsonResponse
    {
        $featureName->load('translations');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $featureName->id,
                'code' => $featureName->code,
                'name' => $featureName->name,
                'is_active' => (bool) $featureName->is_active,
                'translations' => $featureName->translations->keyBy('locale')->map(fn ($t) => ['name' => $t->name])->all(),
            ],
        ]);
    }

    public function update(Request $request, FeatureName $featureName): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9][a-z0-9_-]*$/', 'unique:feature_names,code,'.$featureName->id],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'translations' => ['nullable', 'array'],
        ]);
        $featureName->update([
            'code' => $validated['code'],
            'is_active' => $validated['is_active'] ?? $featureName->is_active,
        ]);
        $by = ['ca' => ['name' => $validated['name']]];
        if (is_array($request->input('translations'))) {
            foreach ($request->input('translations') as $loc => $payload) {
                if (in_array((string) $loc, CatalogLocale::SUPPORTED, true) && is_array($payload)) {
                    $by[(string) $loc] = array_merge($by[(string) $loc] ?? [], $payload);
                }
            }
        }
        CatalogTranslationSync::syncFeatureNameTranslations($featureName, $by);
        $featureName->load('translations');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $featureName->id,
                'code' => $featureName->code,
                'name' => $featureName->name,
                'is_active' => (bool) $featureName->is_active,
                'translations' => $featureName->translations->keyBy('locale')->map(fn ($t) => ['name' => $t->name])->all(),
            ],
        ]);
    }

    /**
     * Return all feature names with their nested features.
     * Supports search (matches feature name or feature value) and is_active filter.
     */
    public function indexWithFeatures(Request $request): JsonResponse
    {
        $loc = CatalogLocale::normalize(app()->getLocale());
        $query = FeatureName::query()
            ->with(['translations', 'features' => fn ($q) => $q->orderByTranslatedValue()->with('translations')]);

        if ($request->filled('search')) {
            $term = '%'.$request->string('search')->trim().'%';
            $query->where(function ($q) use ($term, $loc) {
                $q->where('code', 'like', $term)
                    ->orWhereHas('translations', fn ($t) => $t->where('locale', $loc)->where('name', 'like', $term))
                    ->orWhereHas('features', fn ($sub) => $sub->whereHas(
                        'translations',
                        fn ($ft) => $ft->where('locale', $loc)->where('value', 'like', $term)
                    ));
            });
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $names = $query->orderByTranslatedName()->get();

        $data = $names->map(fn (FeatureName $n) => [
            'id' => $n->id,
            'code' => $n->code,
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
        $featureName->update(['is_active' => ! $featureName->is_active]);
        $featureName->load('translations');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $featureName->id,
                'code' => $featureName->code,
                'name' => $featureName->name,
                'is_active' => (bool) $featureName->is_active,
            ],
        ]);
    }
}
