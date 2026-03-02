<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $query = Product::query()->with(['category'])->orderBy('name');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }
        if ($request->filled('search')) {
            $term = $request->get('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('code', 'like', '%' . $term . '%');
            });
        }
        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->boolean('is_active'));
        }

        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => AdminProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:product_categories,id'],
            'variant_group_id' => ['nullable', 'exists:product_variant_groups,id'],
            'code' => ['nullable', 'string', 'max:50', 'unique:products,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_installable' => ['boolean'],
            'installation_price' => ['nullable', 'numeric', 'min:0'],
            'is_extra_keys_available' => ['boolean'],
            'extra_key_unit_price' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['boolean'],
            'is_trending' => ['boolean'],
            'is_active' => ['boolean'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);

        $defaults = [
            'is_installable' => false,
            'is_extra_keys_available' => false,
            'is_featured' => false,
            'is_trending' => false,
            'is_active' => true,
        ];
        $product = Product::create(array_merge($defaults, collect($validated)->except('feature_ids')->all()));
        $product->features()->sync($validated['feature_ids'] ?? []);

        return response()->json([
            'success' => true,
            'data' => new AdminProductResource($product->load(['category', 'features.featureName'])),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'features.featureName', 'images']);

        return response()->json([
            'success' => true,
            'data' => new AdminProductResource($product),
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:product_categories,id'],
            'variant_group_id' => ['nullable', 'exists:product_variant_groups,id'],
            'code' => ['nullable', 'string', 'max:50', 'unique:products,code,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_installable' => ['boolean'],
            'installation_price' => ['nullable', 'numeric', 'min:0'],
            'is_extra_keys_available' => ['boolean'],
            'extra_key_unit_price' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['boolean'],
            'is_trending' => ['boolean'],
            'is_active' => ['boolean'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);

        $product->update(collect($validated)->except('feature_ids')->all());
        $product->features()->sync($validated['feature_ids'] ?? []);

        return response()->json([
            'success' => true,
            'data' => new AdminProductResource($product->fresh()->load(['category', 'features.featureName'])),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }
}
