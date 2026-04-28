<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Pack;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private const SESSION_CART_KEY = 'cart_lines';

    private const SESSION_CART_INSTALLATION = 'cart_installation_requested';

    /** @return array{shipping_flat_eur: float, total_with_shipping: float} */
    private function cartShippingMeta(float $linesTotal): array
    {
        $flat = (float) Order::SHIPPING_FLAT_EUR;

        return [
            'shipping_flat_eur' => $flat,
            'total_with_shipping' => round($linesTotal + $flat, 2),
        ];
    }

    /**
     * @return array{installation_quote_required: bool, installation_fee_eur: float|null}
     */
    private function installationCartMeta(bool $installationRequested, float $merchandiseTotal): array
    {
        if (! $installationRequested) {
            return [
                'installation_quote_required' => false,
                'installation_fee_eur' => null,
            ];
        }

        $quoteRequired = $merchandiseTotal > Order::INSTALLATION_MERCHANDISE_AUTOMATIC_MAX_EUR;
        $fee = Order::automaticInstallationFeeFromMerchandiseSubtotal($merchandiseTotal);

        return [
            'installation_quote_required' => $quoteRequired,
            'installation_fee_eur' => $fee !== null ? round((float) $fee, 2) : null,
        ];
    }

    public function show(Request $request): JsonResponse
    {
        if ($request->user()) {
            return $this->showDbCart($request->user());
        }

        return $this->showSessionCart($request);
    }

    private function showDbCart($client): JsonResponse
    {
        $cart = Order::query()
            ->where('client_id', $client->id)
            ->where('kind', Order::KIND_CART)
            ->with(['lines.product.images', 'lines.product.features.featureName', 'lines.pack.images'])
            ->first();
        $lines = $cart ? $this->formatLines($cart->lines) : [];
        $total = $cart ? $cart->lines->sum(fn ($l) => $l->line_total) : 0;

        return response()->json([
            'success' => true,
            'data' => array_merge([
                'id' => $cart?->id,
                'lines' => $lines,
                'total' => round($total, 2),
                'installation_requested' => (bool) ($cart?->installation_requested ?? false),
            ], $this->installationCartMeta((bool) ($cart?->installation_requested ?? false), (float) $total), $this->cartShippingMeta((float) $total)),
        ]);
    }

    private function showSessionCart(Request $request): JsonResponse
    {
        $raw = $request->session()->get(self::SESSION_CART_KEY, []);
        $lines = [];
        $total = 0;
        foreach ($raw as $key => $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $included = $item['included'] ?? true;
            if ($item['product_id'] ?? null) {
                $product = Product::with(['images', 'features.featureName'])->find($item['product_id']);
                if ($product && $product->is_active) {
                    $unitPrice = $product->effectivePrice();
                    $extraKeysQty = (int) ($item['extra_keys_qty'] ?? 0);
                    $extraKeyPrice = $product->is_extra_keys_available && $product->extra_key_unit_price
                        ? (float) $product->extra_key_unit_price
                        : null;
                    $extraKeysPrice = $extraKeyPrice !== null ? $extraKeysQty * $extraKeyPrice : 0;
                    $lineTotal = $included ? round($qty * $unitPrice + $extraKeysPrice, 2) : 0;
                    $total += $lineTotal;
                    $lines[] = [
                        'id' => $key,
                        'product_id' => $product->id,
                        'pack_id' => null,
                        'product' => $this->formatProductForCart($product),
                        'pack' => null,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'extra_keys_qty' => $extraKeysQty,
                        'extra_key_unit_price' => $extraKeyPrice,
                        'line_total' => $lineTotal,
                        'is_included' => $included,
                        'keys_all_same' => false,
                    ];
                }
            } elseif ($item['pack_id'] ?? null) {
                $pack = Pack::with('images')->find($item['pack_id']);
                if ($pack && $pack->is_active) {
                    $unitPrice = (float) $pack->price;
                    $lineTotal = $included ? round($qty * $unitPrice, 2) : 0;
                    $total += $lineTotal;
                    $lines[] = [
                        'id' => $key,
                        'product_id' => null,
                        'pack_id' => $pack->id,
                        'product' => null,
                        'pack' => $this->formatPackForCart($pack),
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                        'is_included' => $included,
                        'keys_all_same' => (bool) ($item['keys_all_same'] ?? false),
                    ];
                }
            }
        }

        $installationRequested = (bool) $request->session()->get(self::SESSION_CART_INSTALLATION, false);

        return response()->json([
            'success' => true,
            'data' => array_merge([
                'id' => null,
                'lines' => $lines,
                'total' => round($total, 2),
                'installation_requested' => $installationRequested,
            ], $this->installationCartMeta($installationRequested, (float) $total), $this->cartShippingMeta((float) $total)),
        ]);
    }

    /** @return array<string, mixed> */
    private function formatPackForCart(Pack $pack): array
    {
        $firstImage = $pack->relationLoaded('images') ? $pack->images->first() : $pack->images()->first();

        return [
            'id' => $pack->id,
            'name' => $pack->name,
            'price' => (float) $pack->price,
            'image_url' => $firstImage ? $firstImage->url : null,
            'contains_keys' => (bool) ($pack->contains_keys ?? false),
        ];
    }

    /** @return array<string, mixed> */
    private function formatProductForCart(Product $product): array
    {
        $firstImage = $product->images->first();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->effectivePrice(),
            'image_url' => $firstImage ? $firstImage->url : null,
            'is_extra_keys_available' => (bool) $product->is_extra_keys_available,
            'extra_key_unit_price' => $product->extra_key_unit_price ? (float) $product->extra_key_unit_price : null,
            'features' => $product->features->take(2)->map(fn ($f) => [
                'name' => $f->featureName?->name,
                'value' => $f->value,
            ])->values()->all(),
        ];
    }

    public function updateInstallation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'installation_requested' => ['required', 'boolean'],
        ]);

        if ($request->user()) {
            $cart = Order::firstOrCreate(
                ['client_id' => $request->user()->id, 'kind' => Order::KIND_CART],
                ['status' => null]
            );
            $cart->update([
                'installation_requested' => $validated['installation_requested'],
                'installation_status' => null,
                'installation_price' => null,
            ]);

            return $this->showDbCart($request->user());
        }

        $request->session()->put(self::SESSION_CART_INSTALLATION, $validated['installation_requested']);

        return $this->showSessionCart($request);
    }

    public function addLine(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'pack_id' => ['nullable', 'integer', 'exists:packs,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);
        if (($validated['product_id'] ?? null) && ($validated['pack_id'] ?? null)) {
            return response()->json(['success' => false, 'message' => 'Specify product_id or pack_id, not both.'], 422);
        }
        if (! ($validated['product_id'] ?? $validated['pack_id'] ?? null)) {
            return response()->json(['success' => false, 'message' => 'Specify product_id or pack_id.'], 422);
        }

        if ($request->user()) {
            return $this->addLineDb($request->user(), $validated);
        }

        return $this->addLineSession($request, $validated);
    }

    private function addLineDb($client, array $validated): JsonResponse
    {
        $cart = Order::firstOrCreate(
            ['client_id' => $client->id, 'kind' => Order::KIND_CART],
            ['status' => null]
        );

        $productId = $validated['product_id'] ?? null;
        $packId = $validated['pack_id'] ?? null;
        $quantity = (int) $validated['quantity'];

        $existing = $cart->lines()->where('product_id', $productId)->where('pack_id', $packId)->first();
        if ($existing) {
            $existing->update(['quantity' => $existing->quantity + $quantity]);
            $line = $existing;
        } else {
            $unitPrice = $productId ? Product::find($productId)?->effectivePrice() : Pack::find($packId)?->price;
            $line = $cart->lines()->create([
                'product_id' => $productId,
                'pack_id' => $packId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'is_included' => true,
            ]);
        }

        $cart->load(['lines.product.images', 'lines.product.features.featureName', 'lines.pack']);
        $lines = $this->formatLines($cart->lines);
        $total = $cart->lines->sum(fn ($l) => $l->line_total);
        $installationRequested = (bool) $cart->installation_requested;

        return response()->json([
            'success' => true,
            'data' => array_merge([
                'id' => $cart->id,
                'line' => $this->formatLine($line),
                'lines' => $lines,
                'total' => round($total, 2),
                'installation_requested' => $installationRequested,
            ], $this->installationCartMeta($installationRequested, (float) $total), $this->cartShippingMeta((float) $total)),
        ]);
    }

    private function addLineSession(Request $request, array $validated): JsonResponse
    {
        $key = ($validated['product_id'] ?? null) ? 'p-'.$validated['product_id'] : 'k-'.$validated['pack_id'];
        $session = $request->session();
        $lines = $session->get(self::SESSION_CART_KEY, []);
        $current = $lines[$key] ?? [
            'quantity' => 0,
            'product_id' => $validated['product_id'] ?? null,
            'pack_id' => $validated['pack_id'] ?? null,
            'included' => true,
            'extra_keys_qty' => 0,
            'keys_all_same' => false,
        ];
        $current['quantity'] = ($current['quantity'] ?? 0) + (int) $validated['quantity'];
        $current['included'] = $current['included'] ?? true;
        $lines[$key] = $current;
        $session->put(self::SESSION_CART_KEY, $lines);

        return $this->showSessionCart($request);
    }

    public function updateLine(Request $request, string $line): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
            'included' => ['sometimes', 'boolean'],
            'extra_keys_qty' => ['sometimes', 'integer', 'min:0', 'max:99'],
            'keys_all_same' => ['sometimes', 'boolean'],
        ]);
        $quantity = (int) $validated['quantity'];

        if ($request->user()) {
            $orderLine = OrderLine::with(['product', 'pack'])->find($line);
            if (! $orderLine || $orderLine->order->client_id !== $request->user()->id || $orderLine->order->kind !== Order::KIND_CART) {
                return response()->json(['success' => false, 'message' => 'Line not found.'], 404);
            }
            if ($quantity === 0) {
                $orderLine->delete();
            } else {
                $updates = ['quantity' => $quantity];
                if (array_key_exists('included', $validated)) {
                    $updates['is_included'] = $validated['included'];
                }
                if (array_key_exists('extra_keys_qty', $validated)) {
                    $updates['extra_keys_qty'] = $validated['extra_keys_qty'];
                    $updates['extra_key_unit_price'] = $orderLine->product?->is_extra_keys_available
                        ? $orderLine->product->extra_key_unit_price
                        : null;
                }
                if (array_key_exists('keys_all_same', $validated) && $orderLine->pack_id && $orderLine->pack?->contains_keys) {
                    $updates['keys_all_same'] = $validated['keys_all_same'];
                }
                $orderLine->update($updates);
            }

            return $this->showDbCart($request->user());
        }

        $session = $request->session();
        $lines = $session->get(self::SESSION_CART_KEY, []);
        if (! isset($lines[$line])) {
            return response()->json(['success' => false, 'message' => 'Line not found.'], 404);
        }
        if ($quantity === 0) {
            unset($lines[$line]);
        } else {
            $lines[$line]['quantity'] = $quantity;
            if (array_key_exists('included', $validated)) {
                $lines[$line]['included'] = $validated['included'];
            }
            if (array_key_exists('extra_keys_qty', $validated)) {
                $lines[$line]['extra_keys_qty'] = $validated['extra_keys_qty'];
            }
            if (array_key_exists('keys_all_same', $validated) && ! empty($lines[$line]['pack_id'])) {
                $lines[$line]['keys_all_same'] = $validated['keys_all_same'];
            }
        }
        $session->put(self::SESSION_CART_KEY, $lines);

        return $this->showSessionCart($request);
    }

    public function removeLine(Request $request, string $line): JsonResponse
    {
        return $this->updateLine($request->merge(['quantity' => 0]), $line);
    }

    public function merge(Request $request): JsonResponse
    {
        $sessionLines = $request->session()->get(self::SESSION_CART_KEY, []);
        $sessionInstallation = (bool) $request->session()->get(self::SESSION_CART_INSTALLATION, false);
        $request->session()->forget(self::SESSION_CART_KEY);
        $request->session()->forget(self::SESSION_CART_INSTALLATION);

        $client = $request->user();
        $cart = Order::firstOrCreate(
            ['client_id' => $client->id, 'kind' => Order::KIND_CART],
            ['status' => null]
        );

        foreach ($sessionLines as $item) {
            $productId = $item['product_id'] ?? null;
            $packId = $item['pack_id'] ?? null;
            $qty = (int) ($item['quantity'] ?? 1);
            if (! $productId && ! $packId) {
                continue;
            }
            $existing = $cart->lines()->where('product_id', $productId)->where('pack_id', $packId)->first();
            if ($existing) {
                $updates = ['quantity' => $existing->quantity + $qty];
                if (isset($item['extra_keys_qty'])) {
                    $updates['extra_keys_qty'] = (int) $item['extra_keys_qty'];
                    $product = $existing->product;
                    $updates['extra_key_unit_price'] = $product?->is_extra_keys_available ? $product->extra_key_unit_price : null;
                }
                if (isset($item['keys_all_same']) && $existing->pack_id && $existing->pack?->contains_keys) {
                    $updates['keys_all_same'] = (bool) $item['keys_all_same'];
                }
                $existing->update($updates);
            } else {
                $unitPrice = $productId ? Product::find($productId)?->effectivePrice() : Pack::find($packId)?->price;
                $product = $productId ? Product::find($productId) : null;
                $pack = $packId ? Pack::find($packId) : null;
                $extraKeysQty = (int) ($item['extra_keys_qty'] ?? 0);
                $extraKeyUnitPrice = $product?->is_extra_keys_available ? $product->extra_key_unit_price : null;
                $keysAllSame = $packId && $pack?->contains_keys ? (bool) ($item['keys_all_same'] ?? false) : false;
                $cart->lines()->create([
                    'product_id' => $productId,
                    'pack_id' => $packId,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'extra_keys_qty' => $extraKeysQty,
                    'extra_key_unit_price' => $extraKeyUnitPrice,
                    'keys_all_same' => $keysAllSame,
                ]);
            }
        }

        if ($sessionInstallation || $cart->installation_requested) {
            $cart->update([
                'installation_requested' => true,
                'installation_status' => null,
                'installation_price' => null,
            ]);
        }

        return $this->showDbCart($client);
    }

    private function formatLines($lines): array
    {
        return $lines->map(fn ($l) => $this->formatLine($l))->values()->all();
    }

    private function formatLine(OrderLine $line): array
    {
        $product = $line->product;
        $pack = $line->pack;

        return [
            'id' => (string) $line->id,
            'product_id' => $line->product_id,
            'pack_id' => $line->pack_id,
            'product' => $product ? $this->formatProductForCart($product) : null,
            'pack' => $pack ? $this->formatPackForCart($pack) : null,
            'quantity' => $line->quantity,
            'unit_price' => (float) $line->unit_price,
            'extra_keys_qty' => (int) ($line->extra_keys_qty ?? 0),
            'extra_key_unit_price' => $line->extra_key_unit_price !== null ? (float) $line->extra_key_unit_price : null,
            'line_total' => (float) $line->line_total,
            'is_included' => (bool) ($line->is_included ?? true),
            'keys_all_same' => (bool) ($line->keys_all_same ?? false),
        ];
    }
}
