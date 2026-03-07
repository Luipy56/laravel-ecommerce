<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariantGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin CRUD for product variant groups (siblings).
 * A group may have an optional name; it is a set of products (e.g. same screw 30mm, 40mm, 50mm).
 */
class AdminVariantGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductVariantGroup::query()->with(['products' => fn ($q) => $q->orderBy('name')]);

        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->trim() . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhereHas('products', fn ($pq) => $pq->where('name', 'like', $term)->orWhere('code', 'like', $term));
            });
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $groups = $query->orderBy('id')->paginate($perPage);

        $data = $groups->getCollection()->map(fn ($g) => [
            'id' => $g->id,
            'name' => $g->name,
            'products_count' => $g->products->count(),
            'products' => $g->products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
            ])->values()->all(),
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $groups->currentPage(),
                'last_page' => $groups->lastPage(),
                'per_page' => $groups->perPage(),
                'total' => $groups->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $group = ProductVariantGroup::create([
            'name' => $validated['name'] ?? null,
        ]);
        $productIds = array_unique($validated['product_ids'] ?? []);

        $this->assignProductsToGroup($group->id, $productIds);

        $group->load(['products' => fn ($q) => $q->orderBy('name')]);

        return response()->json([
            'success' => true,
            'data' => $this->groupToArray($group),
        ], 201);
    }

    public function show(ProductVariantGroup $variant_group): JsonResponse
    {
        $variant_group->load([
            'products' => fn ($q) => $q->orderBy('name')->with(['category', 'images']),
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->groupToArray($variant_group),
        ]);
    }

    public function update(Request $request, ProductVariantGroup $variant_group): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $variant_group->update(['name' => $validated['name'] ?? null]);

        $productIds = array_unique($validated['product_ids'] ?? []);

        Product::query()->where('variant_group_id', $variant_group->id)->update(['variant_group_id' => null]);
        $this->assignProductsToGroup($variant_group->id, $productIds);

        $variant_group->load(['products' => fn ($q) => $q->orderBy('name')]);

        return response()->json([
            'success' => true,
            'data' => $this->groupToArray($variant_group),
        ]);
    }

    public function destroy(ProductVariantGroup $variant_group): JsonResponse
    {
        Product::query()->where('variant_group_id', $variant_group->id)->update(['variant_group_id' => null]);
        $variant_group->delete();

        return response()->json(['success' => true]);
    }

    private function assignProductsToGroup(int $groupId, array $productIds): void
    {
        foreach ($productIds as $productId) {
            Product::query()->where('id', $productId)->update(['variant_group_id' => $groupId]);
        }
    }

    private function groupToArray(ProductVariantGroup $g): array
    {
        return [
            'id' => $g->id,
            'name' => $g->name,
            'product_ids' => $g->products->pluck('id')->values()->all(),
            'products' => $g->products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'price' => $p->price !== null ? (float) $p->price : null,
                'stock' => (int) $p->stock,
                'is_active' => (bool) $p->is_active,
                'category' => $p->relationLoaded('category') && $p->category
                    ? ['id' => $p->category->id, 'name' => $p->category->name]
                    : null,
                'image_url' => $p->relationLoaded('images') && $p->images->isNotEmpty()
                    ? $p->images->first()->url
                    : null,
            ])->values()->all(),
        ];
    }
}
