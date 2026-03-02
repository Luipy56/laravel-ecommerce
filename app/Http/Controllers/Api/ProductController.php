<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackResource;
use App\Http\Resources\ProductResource;
use App\Models\Pack;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(50, (int) $request->get('per_page', 15)));
        $includePacks = $request->boolean('include_packs');

        if (! $includePacks) {
            return $this->indexProductsOnly($request, $perPage);
        }

        return $this->indexCatalog($request, $perPage);
    }

    /**
     * Products only (original behaviour).
     */
    private function indexProductsOnly(Request $request, int $perPage): JsonResponse
    {
        $query = $this->buildProductQuery($request);
        $products = $query->with(['category', 'features.featureName', 'images'])->orderBy('name')->paginate($perPage);

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

    /**
     * Mixed catalog: products + packs with same filters, merged and sorted by name.
     */
    private function indexCatalog(Request $request, int $perPage): JsonResponse
    {
        $productQuery = $this->buildProductQuery($request);
        $packQuery = $this->buildPackQuery($request);

        $productRows = $productQuery->get(['id', 'name'])->map(fn ($p) => ['type' => 'product', 'id' => $p->id, 'name' => $p->name]);
        $packRows = $packQuery->get(['id', 'name'])->map(fn ($p) => ['type' => 'pack', 'id' => $p->id, 'name' => $p->name]);

        $merged = $productRows->concat($packRows)->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        $total = $merged->count();
        $page = max(1, (int) $request->get('page', 1));
        $slice = $merged->slice(($page - 1) * $perPage, $perPage);

        $productIds = $slice->where('type', 'product')->pluck('id')->all();
        $packIds = $slice->where('type', 'pack')->pluck('id')->all();

        $productsById = Product::query()->active()
            ->with(['category', 'features.featureName', 'images'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');
        $packsById = Pack::query()->active()
            ->with(['items.product', 'images'])
            ->whereIn('id', $packIds)
            ->get()
            ->keyBy('id');

        $data = $slice->map(function ($row) use ($productsById, $packsById) {
            if ($row['type'] === 'product') {
                $product = $productsById->get($row['id']);
                return $product ? ['type' => 'product', 'data' => (new ProductResource($product))->resolve()] : null;
            }
            $pack = $packsById->get($row['id']);
            return $pack ? ['type' => 'pack', 'data' => (new PackResource($pack))->resolve()] : null;
        })->filter()->values()->all();

        $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }

    private function buildProductQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Product::query()->active();

        $categoryIds = $request->get('category_id');
        if ($categoryIds !== null && $categoryIds !== '') {
            $ids = is_array($categoryIds) ? $categoryIds : array_filter(explode(',', (string) $categoryIds));
            if (! empty($ids)) {
                $query->whereIn('category_id', $ids);
            }
        }
        if ($request->filled('feature_ids')) {
            $ids = is_array($request->feature_ids) ? $request->feature_ids : explode(',', $request->feature_ids);
            $ids = array_filter(array_map('intval', $ids));
            if (! empty($ids)) {
                $query->whereHas('features', fn ($q) => $q->whereIn('features.id', $ids));
            }
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhereHas('features', function ($f) use ($term) {
                        $f->where('value', 'like', "%{$term}%")
                            ->orWhereHas('featureName', fn ($n) => $n->where('name', 'like', "%{$term}%"));
                    });
            });
        }

        return $query;
    }

    private function buildPackQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Pack::query()->active();

        $categoryIds = $request->get('category_id');
        if ($categoryIds !== null && $categoryIds !== '') {
            $ids = is_array($categoryIds) ? $categoryIds : array_filter(explode(',', (string) $categoryIds));
            if (! empty($ids)) {
                $query->whereHas('items.product', fn ($q) => $q->whereIn('category_id', $ids));
            }
        }
        if ($request->filled('feature_ids')) {
            $ids = is_array($request->feature_ids) ? $request->feature_ids : explode(',', $request->feature_ids);
            $ids = array_filter(array_map('intval', $ids));
            if (! empty($ids)) {
                $query->whereHas('items.product', fn ($q) => $q->whereHas('features', fn ($f) => $f->whereIn('features.id', $ids)));
            }
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('packs.name', 'like', "%{$term}%")
                    ->orWhere('packs.description', 'like', "%{$term}%")
                    ->orWhereHas('items.product', function ($p) use ($term) {
                        $p->where('name', 'like', "%{$term}%")
                            ->orWhere('description', 'like', "%{$term}%")
                            ->orWhere('code', 'like', "%{$term}%")
                            ->orWhereHas('features', function ($f) use ($term) {
                                $f->where('value', 'like', "%{$term}%")
                                    ->orWhereHas('featureName', fn ($n) => $n->where('name', 'like', "%{$term}%"));
                            });
                    });
            });
        }

        return $query;
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
            'variantGroup.products' => fn ($q) => $q->active()->orderBy('name')->with(['images', 'features.featureName']),
        ]);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }
}
