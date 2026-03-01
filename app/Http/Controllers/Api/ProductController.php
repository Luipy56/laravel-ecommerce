<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->active()->with(['category', 'features.featureName', 'images']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('feature_ids')) {
            $ids = is_array($request->feature_ids) ? $request->feature_ids : explode(',', $request->feature_ids);
            $query->whereHas('features', fn ($q) => $q->whereIn('features.id', $ids));
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%"));
        }

        $perPage = max(1, min(50, (int) $request->get('per_page', 15)));
        $products = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function featured(): JsonResponse
    {
        $products = Product::query()->active()->featured()
            ->with(['category', 'features.featureName', 'images'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $term = $request->get('q', '');
        if (strlen($term) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $products = Product::query()->active()
            ->with(['category', 'images'])
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%"))
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        if (! $product->is_active) {
            abort(404);
        }
        $product->load([
            'category',
            'features.featureName',
            'images',
            'variantGroup.products' => fn ($q) => $q->active()->orderBy('name'),
        ]);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }
}
