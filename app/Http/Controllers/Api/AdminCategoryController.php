<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    /**
     * List categories for admin. Query params: search (optional), is_active (optional).
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductCategory::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->trim() . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)->orWhere('code', 'like', $term);
            });
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $categories = $query->paginate($perPage, ['id', 'code', 'name', 'is_active']);
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
        ]);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $cat = ProductCategory::create($validated);
        return response()->json(['success' => true, 'data' => $this->categoryToArray($cat)], 201);
    }

    public function show(ProductCategory $category): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->categoryToArray($category),
        ]);
    }

    public function update(Request $request, ProductCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:product_categories,code,' . $category->id],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $category->update($validated);
        return response()->json(['success' => true, 'data' => $this->categoryToArray($category->fresh())]);
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
        ];
    }
}
