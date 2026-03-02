<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariantGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin CRUD for product variant groups (siblings).
 * A group has no name; it is just a set of products (e.g. same screw 30mm, 40mm, 50mm).
 */
class AdminVariantGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductVariantGroup::query()->with(['products' => fn ($q) => $q->orderBy('name')]);

        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->trim() . '%';
            $query->whereHas('products', fn ($q) => $q->where('name', 'like', $term)->orWhere('code', 'like', $term));
        }

        $groups = $query->orderBy('id')->get();

        $data = $groups->map(fn ($g) => [
            'id' => $g->id,
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
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $group = ProductVariantGroup::create([]);
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
        $variant_group->load(['products' => fn ($q) => $q->orderBy('name')]);

        return response()->json([
            'success' => true,
            'data' => $this->groupToArray($variant_group),
        ]);
    }

    public function update(Request $request, ProductVariantGroup $variant_group): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

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
            'product_ids' => $g->products->pluck('id')->values()->all(),
            'products' => $g->products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
            ])->values()->all(),
        ];
    }
}
