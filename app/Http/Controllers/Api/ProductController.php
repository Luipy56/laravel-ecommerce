<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackResource;
use App\Http\Resources\ProductResource;
use App\Models\Feature;
use App\Models\Pack;
use App\Models\Product;
use App\Services\HomeFeaturedProductIds;
use App\Services\Search\CatalogProductSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * REST API for storefront product discovery: listing, search, featured items, and product detail.
 *
 * Supports optional inclusion of packs in the catalog index and applies category and feature filters
 * consistently across products and packs.
 */
class ProductController extends Controller
{
    /**
     * Paginated product catalog (optionally merged with packs when include_packs=1).
     *
     * @param  Request  $request  Query string: per_page (1–50), page, category_id, feature_ids, search, include_packs, packs_only.
     * @return JsonResponse JSON envelope with success, data (ProductResource collection or mixed catalog rows), and meta pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(50, (int) $request->get('per_page', 15)));

        if ($request->boolean('packs_only')) {
            return $this->indexPacksOnly($request, $perPage);
        }

        $includePacks = $request->boolean('include_packs');

        if (! $includePacks) {
            return $this->indexProductsOnly($request, $perPage);
        }

        return $this->indexCatalog($request, $perPage);
    }

    /**
     * Packs only: same filters as mixed catalog (category, features, search) but only pack rows, SQL-paginated.
     */
    private function indexPacksOnly(Request $request, int $perPage): JsonResponse
    {
        $query = $this->buildPackQuery($request);
        $packs = $query->with(['items.product', 'images'])->orderBy('name')->paginate($perPage);

        $data = $packs->getCollection()->map(fn (Pack $pack) => [
            'type' => 'pack',
            'data' => (new PackResource($pack))->resolve(),
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $packs->currentPage(),
                'last_page' => $packs->lastPage(),
                'per_page' => $packs->perPage(),
                'total' => $packs->total(),
            ],
        ]);
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

        if (($categoryId = $this->requestedCategoryId($request)) !== null) {
            $query->where('category_id', $categoryId);
        }
        $this->applyFeatureGroupFiltersToProductQuery($query, $request);
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
        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float) $request->input('price_max'));
        }

        return $query;
    }

    private function buildPackQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Pack::query()->active();

        if (($categoryId = $this->requestedCategoryId($request)) !== null) {
            $query->whereHas('items.product', fn ($q) => $q->where('category_id', $categoryId));
        }
        $this->applyFeatureGroupFiltersToPackQuery($query, $request);
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
        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float) $request->input('price_max'));
        }

        return $query;
    }

    /**
     * Single category filter. Products have one category_id; only one category can be applied.
     * If the client sends multiple values (legacy URLs), the first valid id is used.
     */
    private function requestedCategoryId(Request $request): ?int
    {
        $raw = $request->get('category_id');
        if ($raw === null || $raw === '') {
            return null;
        }

        $ids = is_array($raw) ? $raw : array_filter(explode(',', (string) $raw));
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return null;
        }

        return $ids[0];
    }

    /**
     * Parsed feature filter IDs from the request.
     *
     * @return list<int>
     */
    private function requestedFeatureIds(Request $request): array
    {
        if (! $request->filled('feature_ids')) {
            return [];
        }

        $raw = $request->feature_ids;
        $ids = is_array($raw) ? $raw : explode(',', (string) $raw);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        return $ids;
    }

    /**
     * Group selected feature IDs by characteristic (feature_name_id).
     * Multiple values of the same characteristic are OR; different characteristics are AND.
     *
     * @return array<int, list<int>> feature_name_id => feature ids
     */
    private function featureGroupsByNameId(Request $request): array
    {
        $ids = $this->requestedFeatureIds($request);
        if ($ids === []) {
            return [];
        }

        $features = Feature::query()
            ->whereIn('id', $ids)
            ->get(['id', 'feature_name_id']);

        $groups = [];
        foreach ($features as $feature) {
            $nameId = (int) $feature->feature_name_id;
            if (! isset($groups[$nameId])) {
                $groups[$nameId] = [];
            }
            if (! in_array($feature->id, $groups[$nameId], true)) {
                $groups[$nameId][] = (int) $feature->id;
            }
        }

        return $groups;
    }

    private function applyFeatureGroupFiltersToProductQuery(\Illuminate\Database\Eloquent\Builder $query, Request $request): void
    {
        foreach ($this->featureGroupsByNameId($request) as $groupFeatureIds) {
            $query->whereHas('features', fn ($q) => $q->whereIn('features.id', $groupFeatureIds));
        }
    }

    private function applyFeatureGroupFiltersToPackQuery(\Illuminate\Database\Eloquent\Builder $query, Request $request): void
    {
        foreach ($this->featureGroupsByNameId($request) as $groupFeatureIds) {
            $query->whereHas(
                'items.product',
                fn ($p) => $p->whereHas('features', fn ($f) => $f->whereIn('features.id', $groupFeatureIds))
            );
        }
    }

    /**
     * Active products marked as featured or trending for home or promotional sections.
     *
     * @return JsonResponse JSON envelope with success and data as a ProductResource collection.
     */
    public function featured(HomeFeaturedProductIds $homeFeatured): JsonResponse
    {
        $ids = $homeFeatured->orderedIds();
        if ($ids === []) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $products = Product::query()->active()
            ->whereIn('id', $ids)
            ->with(['category', 'features.featureName', 'images'])
            ->get();

        $byId = $products->keyBy('id');
        $ordered = collect($ids)
            ->map(fn (int $id) => $byId->get($id))
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($ordered),
        ]);
    }

    /**
     * Catalog search: Elasticsearch when scout.driver is elasticsearch, otherwise ProductSearchService
     * (PostgreSQL trigram or SQL token match). Query param suggest=1 returns autocomplete entries (text + product_id).
     */
    public function search(Request $request, CatalogProductSearchService $catalogSearch): JsonResponse
    {
        $request->validate([
            'q' => ['sometimes', 'nullable', 'string', 'max:500'],
            'search' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $term = (string) ($request->input('q') ?? $request->input('search') ?? '');
        $suggest = $request->boolean('suggest');
        $limit = max(1, min(100, (int) config('product_search.api_limit', 20)));

        $result = $catalogSearch->search($term, $suggest, $limit);

        if ($suggest) {
            return response()->json([
                'success' => true,
                'data' => $result['suggestions'],
                'meta' => [
                    'engine' => $result['engine'],
                    'suggest' => true,
                ],
            ]);
        }

        $products = $result['products'];
        $products->loadMissing(['category', 'images']);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'engine' => $result['engine'],
                'suggest' => false,
            ],
        ]);
    }

    /**
     * Global min/max price across all active products and packs.
     * Used by the storefront price-range filter to set slider bounds.
     */
    public function priceRange(): JsonResponse
    {
        $productMin = Product::query()->active()->min('price');
        $productMax = Product::query()->active()->max('price');
        $packMin = Pack::query()->active()->min('price');
        $packMax = Pack::query()->active()->max('price');

        $min = min(array_filter([$productMin, $packMin], fn ($v) => $v !== null)) ?: 0;
        $max = max(array_filter([$productMax, $packMax], fn ($v) => $v !== null)) ?: 0;

        return response()->json([
            'success' => true,
            'data' => [
                'min' => (float) floor((float) $min),
                'max' => (float) ceil((float) $max),
            ],
        ]);
    }

    /**
     * Single active product with category, features, images, and sibling variants in the same group.
     *
     * @param  Product  $product  Route-model-bound product; inactive products yield 404.
     * @return JsonResponse JSON envelope with success and data as ProductResource.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException When the product is not active (404).
     */
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
