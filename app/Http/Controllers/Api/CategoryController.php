<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductCategory::query()->active();
        if ($request->boolean('with_inactive')) {
            $query = ProductCategory::query();
        }
        $categories = $query->orderByTranslatedName()->with('translations')->get();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Each active category paired with its first active product (by name).
     * Categories without active products are excluded.
     */
    public function withFirstProduct(): JsonResponse
    {
        $categories = ProductCategory::query()->active()
            ->whereHas('products', fn ($q) => $q->active())
            ->orderByTranslatedName()
            ->with('translations')
            ->get();

        $data = $categories->map(function (ProductCategory $category) {
            $product = $category->products()
                ->active()
                ->with(['images', 'category.translations', 'features.featureName.translations', 'translations'])
                ->orderByTranslatedName()
                ->first();

            return [
                'category' => (new CategoryResource($category))->resolve(),
                'product' => $product ? (new ProductResource($product))->resolve() : null,
            ];
        })->filter(fn ($row) => $row['product'] !== null)->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
