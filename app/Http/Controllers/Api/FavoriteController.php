<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackResource;
use App\Http\Resources\ProductResource;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Pack;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    private function likeOrderForClient(int $clientId): Order
    {
        return Order::firstOrCreate(
            ['client_id' => $clientId, 'kind' => Order::KIND_LIKE],
            ['status' => null]
        );
    }

    public function ids(Request $request): JsonResponse
    {
        $client = $request->user();
        $likeOrder = Order::query()
            ->where('client_id', $client->id)
            ->where('kind', Order::KIND_LIKE)
            ->first();

        if (! $likeOrder) {
            return response()->json([
                'success' => true,
                'data' => ['product_ids' => [], 'pack_ids' => []],
            ]);
        }

        $productIds = $likeOrder->lines()->whereNotNull('product_id')->pluck('product_id')->unique()->values()->all();
        $packIds = $likeOrder->lines()->whereNotNull('pack_id')->pluck('pack_id')->unique()->values()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'product_ids' => array_map('intval', $productIds),
                'pack_ids' => array_map('intval', $packIds),
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $client = $request->user();
        $likeOrder = Order::query()
            ->where('client_id', $client->id)
            ->where('kind', Order::KIND_LIKE)
            ->with([
                'lines.product.images',
                'lines.product.category',
                'lines.product.features.featureName',
                'lines.product.variantGroup.products.features.featureName',
                'lines.product.variantGroup.products.images',
                'lines.pack.images',
                'lines.pack.items.product',
            ])
            ->first();

        if (! $likeOrder) {
            return response()->json(['success' => true, 'data' => ['items' => []]]);
        }

        $likeOrder->load([
            'lines.product.images',
            'lines.product.category',
            'lines.product.features.featureName',
            'lines.product.variantGroup.products.features.featureName',
            'lines.product.variantGroup.products.images',
            'lines.pack.images',
            'lines.pack.items.product',
        ]);

        $items = [];
        foreach ($likeOrder->lines as $line) {
            $items[] = $this->formatFavoriteItem($line);
        }

        return response()->json(['success' => true, 'data' => ['items' => $items]]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatFavoriteItem(OrderLine $line): array
    {
        $product = $line->product;
        $pack = $line->pack;

        if ($line->product_id === null && $line->pack_id === null) {
            return [
                'line_id' => (string) $line->id,
                'kind' => 'unknown',
                'unavailable' => true,
                'reason' => 'orphan',
                'product' => null,
                'pack' => null,
            ];
        }

        if ($line->product_id !== null) {
            if (! $product) {
                return [
                    'line_id' => (string) $line->id,
                    'kind' => 'product',
                    'unavailable' => true,
                    'reason' => 'orphan',
                    'product' => null,
                    'pack' => null,
                ];
            }
            if (! $product->is_active) {
                return [
                    'line_id' => (string) $line->id,
                    'kind' => 'product',
                    'unavailable' => true,
                    'reason' => 'inactive',
                    'product' => (new ProductResource($product))->resolve(),
                    'pack' => null,
                ];
            }

            return [
                'line_id' => (string) $line->id,
                'kind' => 'product',
                'unavailable' => false,
                'reason' => null,
                'product' => (new ProductResource($product))->resolve(),
                'pack' => null,
            ];
        }

        // pack line
        if (! $pack) {
            return [
                'line_id' => (string) $line->id,
                'kind' => 'pack',
                'unavailable' => true,
                'reason' => 'orphan',
                'product' => null,
                'pack' => null,
            ];
        }
        if (! $pack->is_active) {
            return [
                'line_id' => (string) $line->id,
                'kind' => 'pack',
                'unavailable' => true,
                'reason' => 'inactive',
                'product' => null,
                'pack' => (new PackResource($pack))->resolve(),
            ];
        }

        return [
            'line_id' => (string) $line->id,
            'kind' => 'pack',
            'unavailable' => false,
            'reason' => null,
            'product' => null,
            'pack' => (new PackResource($pack))->resolve(),
        ];
    }

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'pack_id' => ['nullable', 'integer', 'exists:packs,id'],
        ]);

        $productId = $validated['product_id'] ?? null;
        $packId = $validated['pack_id'] ?? null;

        if (($productId && $packId) || (! $productId && ! $packId)) {
            return response()->json([
                'success' => false,
                'message' => __('shop.favorites.specify_one'),
            ], 422);
        }

        $client = $request->user();
        $activeProduct = null;
        $activePack = null;

        if ($productId) {
            $activeProduct = Product::query()->whereKey($productId)->where('is_active', true)->first();
            if (! $activeProduct) {
                return response()->json([
                    'success' => false,
                    'message' => __('shop.favorites.product_unavailable'),
                ], 422);
            }
        } else {
            $activePack = Pack::query()->whereKey($packId)->where('is_active', true)->first();
            if (! $activePack) {
                return response()->json([
                    'success' => false,
                    'message' => __('shop.favorites.pack_unavailable'),
                ], 422);
            }
        }

        $unitPrice = $activeProduct
            ? $activeProduct->effectivePrice()
            : (float) $activePack->price;

        return DB::transaction(function () use ($client, $productId, $packId, $unitPrice) {
            $likeOrder = $this->likeOrderForClient($client->id);

            $query = $likeOrder->lines();
            if ($productId) {
                $query->where('product_id', $productId)->whereNull('pack_id');
            } else {
                $query->where('pack_id', $packId)->whereNull('product_id');
            }

            $existing = $query->first();

            if ($existing) {
                $existing->delete();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'liked' => false,
                        'product_id' => $productId,
                        'pack_id' => $packId,
                    ],
                ]);
            }

            $likeOrder->lines()->create([
                'product_id' => $productId,
                'pack_id' => $packId,
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'is_included' => true,
                'extra_keys_qty' => 0,
                'keys_all_same' => false,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'liked' => true,
                    'product_id' => $productId,
                    'pack_id' => $packId,
                ],
            ]);
        });
    }

    public function destroyLine(Request $request, OrderLine $orderLine): JsonResponse
    {
        $client = $request->user();
        $orderLine->loadMissing('order');

        if (! $orderLine->order
            || $orderLine->order->client_id !== $client->id
            || $orderLine->order->kind !== Order::KIND_LIKE) {
            return response()->json(['success' => false, 'message' => __('shop.favorites.line_not_found')], 404);
        }

        $orderLine->delete();

        return response()->json(['success' => true, 'data' => ['deleted' => true]]);
    }
}
