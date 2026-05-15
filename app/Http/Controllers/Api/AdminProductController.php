<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\CatalogLocale;
use App\Support\CatalogTranslationSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $query = Product::query()->with(['category.translations', 'translations'])->orderByTranslatedName();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }
        if ($request->filled('search')) {
            $term = '%'.$request->get('search').'%';
            $loc = CatalogLocale::normalize(app()->getLocale());
            $query->where(function ($q) use ($term, $loc) {
                $q->where('code', 'like', $term)
                    ->orWhereHas('translations', fn ($t) => $t->where('locale', $loc)->where('name', 'like', $term));
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
        if ($request->input('competitor_url') === '') {
            $request->merge(['competitor_url' => null]);
        }
        if ($request->input('security_level') === '') {
            $request->merge(['security_level' => null]);
        }
        if ($request->input('discount_percent') === '' || $request->input('discount_percent') === null) {
            $request->merge(['discount_percent' => null]);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:product_categories,id'],
            'variant_group_id' => ['nullable', 'exists:product_variant_groups,id'],
            'code' => ['nullable', 'string', 'max:50', 'unique:products,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'translations' => ['nullable', 'array'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'is_double_clutch' => ['boolean'],
            'has_card' => ['boolean'],
            'security_level' => ['nullable', 'string', 'in:standard,high,very_high'],
            'competitor_url' => ['nullable', 'string', 'max:2048', 'url'],
            'is_extra_keys_available' => ['boolean'],
            'extra_key_unit_price' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'max:10240'],
        ]);

        $defaults = [
            'is_double_clutch' => false,
            'has_card' => false,
            'is_extra_keys_available' => false,
            'is_featured' => false,
            'is_trending' => false,
            'is_active' => true,
            'discount_percent' => null,
        ];
        $row = array_merge($defaults, collect($validated)->except(['feature_ids', 'images', 'name', 'description', 'translations'])->all());
        if (! array_key_exists('discount_percent', $row) || $row['discount_percent'] === '' || $row['discount_percent'] === null) {
            $row['discount_percent'] = null;
        }
        $product = Product::create($row);
        $byLocale = ['ca' => ['name' => $validated['name'], 'description' => $validated['description'] ?? null]];
        if (is_array($request->input('translations'))) {
            foreach ($request->input('translations') as $loc => $payload) {
                if (in_array((string) $loc, CatalogLocale::SUPPORTED, true) && is_array($payload)) {
                    $byLocale[(string) $loc] = array_merge($byLocale[(string) $loc] ?? [], $payload);
                }
            }
        }
        CatalogTranslationSync::syncProductTranslations($product, $byLocale);
        $product->features()->sync($validated['feature_ids'] ?? []);

        if ($request->hasFile('images')) {
            $this->storeProductImages($product, $request->file('images'));
        }

        return response()->json([
            'success' => true,
            'data' => new AdminProductResource($product->load(['category.translations', 'features.featureName.translations', 'translations', 'images'])),
        ], 201);
    }

    public function storeImages(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['required', 'file', 'image', 'max:10240'],
        ]);

        $this->storeProductImages($product, $validated['images']);

        return response()->json([
            'success' => true,
            'data' => new AdminProductResource($product->load(['category.translations', 'features.featureName.translations', 'translations', 'images'])),
        ]);
    }

    public function destroyImage(Product $product, ProductImage $productImage): JsonResponse
    {
        if ($productImage->product_id !== $product->id) {
            abort(404);
        }
        $productImage->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    private function storeProductImages(Product $product, array $files): void
    {
        $maxSort = (int) ProductImage::where('product_id', $product->id)->max('sort_order');
        foreach ($files as $file) {
            $maxSort++;
            $path = $file->store('products/'.$product->id, 'uploads');
            ProductImage::create([
                'product_id' => $product->id,
                'storage_path' => $path,
                'filename' => $file->getClientOriginalName(),
                'size_bytes' => $file->getSize(),
                'checksum' => null,
                'content_type' => $file->getMimeType(),
                'sort_order' => $maxSort,
                'is_active' => true,
            ]);
        }
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category.translations', 'features.featureName.translations', 'translations', 'images']);

        return response()->json([
            'success' => true,
            'data' => new AdminProductResource($product),
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        if ($request->input('competitor_url') === '') {
            $request->merge(['competitor_url' => null]);
        }
        if ($request->input('security_level') === '') {
            $request->merge(['security_level' => null]);
        }
        if ($request->input('discount_percent') === '' || $request->input('discount_percent') === null) {
            $request->merge(['discount_percent' => null]);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:product_categories,id'],
            'variant_group_id' => ['nullable', 'exists:product_variant_groups,id'],
            'code' => ['nullable', 'string', 'max:50', 'unique:products,code,'.$product->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'translations' => ['nullable', 'array'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'is_double_clutch' => ['boolean'],
            'has_card' => ['boolean'],
            'security_level' => ['nullable', 'string', 'in:standard,high,very_high'],
            'competitor_url' => ['nullable', 'string', 'max:2048', 'url'],
            'is_extra_keys_available' => ['boolean'],
            'extra_key_unit_price' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);

        $product->update(collect($validated)->except(['feature_ids', 'name', 'description', 'translations'])->all());
        $byLocale = ['ca' => ['name' => $validated['name'], 'description' => $validated['description'] ?? null]];
        if (is_array($request->input('translations'))) {
            foreach ($request->input('translations') as $loc => $payload) {
                if (in_array((string) $loc, CatalogLocale::SUPPORTED, true) && is_array($payload)) {
                    $byLocale[(string) $loc] = array_merge($byLocale[(string) $loc] ?? [], $payload);
                }
            }
        }
        CatalogTranslationSync::syncProductTranslations($product, $byLocale);
        $product->features()->sync($validated['feature_ids'] ?? []);

        return response()->json([
            'success' => true,
            'data' => new AdminProductResource($product->fresh()->load(['category.translations', 'features.featureName.translations', 'translations', 'images'])),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }
}
