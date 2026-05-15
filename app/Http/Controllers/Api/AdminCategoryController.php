<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Support\CatalogLocale;
use App\Support\CatalogTranslationSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    /**
     * List categories for admin. Query params: search (optional), is_active (optional).
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductCategory::query()->with('translations')->orderByTranslatedName();

        if ($request->filled('search')) {
            $term = '%'.$request->string('search')->trim().'%';
            $loc = CatalogLocale::normalize(app()->getLocale());
            $query->where(function ($q) use ($term, $loc) {
                $q->where('code', 'like', $term)
                    ->orWhereHas('translations', fn ($t) => $t->where('locale', $loc)->where('name', 'like', $term));
            });
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $categories = $query->paginate($perPage);
        $data = $categories->getCollection()->map(fn (ProductCategory $c) => [
            'id' => $c->id,
            'code' => $c->code,
            'name' => $c->name,
            'is_active' => (bool) $c->is_active,
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:product_categories,code'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'translations' => ['nullable', 'array'],
        ]);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $cat = ProductCategory::create([
            'code' => $validated['code'] ?? null,
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
        CatalogTranslationSync::syncCategoryTranslations($cat, $by);

        return response()->json(['success' => true, 'data' => $this->categoryToArray($cat->fresh()->load('translations'))], 201);
    }

    public function show(ProductCategory $category): JsonResponse
    {
        $category->load('translations');

        return response()->json([
            'success' => true,
            'data' => $this->categoryToArray($category),
        ]);
    }

    public function update(Request $request, ProductCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:product_categories,code,'.$category->id],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'translations' => ['nullable', 'array'],
        ]);
        $category->update([
            'code' => $validated['code'] ?? $category->code,
            'is_active' => $validated['is_active'] ?? $category->is_active,
        ]);
        $by = ['ca' => ['name' => $validated['name']]];
        if (is_array($request->input('translations'))) {
            foreach ($request->input('translations') as $loc => $payload) {
                if (in_array((string) $loc, CatalogLocale::SUPPORTED, true) && is_array($payload)) {
                    $by[(string) $loc] = array_merge($by[(string) $loc] ?? [], $payload);
                }
            }
        }
        CatalogTranslationSync::syncCategoryTranslations($category, $by);

        return response()->json(['success' => true, 'data' => $this->categoryToArray($category->fresh()->load('translations'))]);
    }

    public function destroy(ProductCategory $category): JsonResponse
    {
        $category->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    private function categoryToArray(ProductCategory $c): array
    {
        return [
            'id' => $c->id,
            'code' => $c->code,
            'name' => $c->name,
            'is_active' => (bool) $c->is_active,
            'translations' => $c->relationLoaded('translations')
                ? $c->translations->keyBy('locale')->map(fn ($t) => ['name' => $t->name])->all()
                : [],
        ];
    }
}
