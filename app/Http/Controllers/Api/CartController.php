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
            'data' => [
                'id' => $cart?->id,
                'lines' => $lines,
                'total' => round($total, 2),
            ],
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
            $wantsInstallation = (bool) ($item['wants_installation'] ?? false);
            if ($item['product_id'] ?? null) {
                $product = Product::with(['images', 'features.featureName'])->find($item['product_id']);
                if ($product && $product->is_active) {
                    $unitPrice = (float) $product->price;
                    $installPrice = $wantsInstallation && $product->is_installable
                        ? $qty * (float) ($product->installation_price ?? 0)
                        : 0;
                    $extraKeysQty = (int) ($item['extra_keys_qty'] ?? 0);
                    $extraKeyPrice = $product->is_extra_keys_available && $product->extra_key_unit_price
                        ? (float) $product->extra_key_unit_price
                        : null;
                    $extraKeysPrice = $extraKeyPrice !== null ? $extraKeysQty * $extraKeyPrice : 0;
                    $lineTotal = $included ? round($qty * $unitPrice + $installPrice + $extraKeysPrice, 2) : 0;
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
                        'is_installation_requested' => $wantsInstallation,
                    ];
                }
            } elseif ($item['pack_id'] ?? null) {
                $pack = Pack::with('images')->find($item['pack_id']);
                if ($pack && $pack->is_active) {
                    $unitPrice = (float) $pack->price;
                    $installPrice = $wantsInstallation && ($pack->is_installable ?? false)
                        ? $qty * (float) ($pack->installation_price ?? 0)
                        : 0;
                    $lineTotal = $included ? round($qty * $unitPrice + $installPrice, 2) : 0;
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
                        'is_installation_requested' => $wantsInstallation,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => null,
                'lines' => $lines,
                'total' => round($total, 2),
            ],
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
            'is_installable' => (bool) ($pack->is_installable ?? false),
            'installation_price' => isset($pack->installation_price) && $pack->installation_price !== null
                ? (float) $pack->installation_price
                : null,
        ];
    }

    /** @return array<string, mixed> */
    private function formatProductForCart(Product $product): array
    {
        $firstImage = $product->images->first();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'image_url' => $firstImage ? $firstImage->url : null,
            'is_installable' => (bool) $product->is_installable,
            'installation_price' => isset($product->installation_price) && $product->installation_price !== null
                ? (float) $product->installation_price
                : null,
            'is_extra_keys_available' => (bool) $product->is_extra_keys_available,
            'extra_key_unit_price' => $product->extra_key_unit_price ? (float) $product->extra_key_unit_price : null,
            'features' => $product->features->take(2)->map(fn ($f) => [
                'name' => $f->featureName?->name,
                'value' => $f->value,
            ])->values()->all(),
        ];
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
            $unitPrice = $productId ? Product::find($productId)?->price : Pack::find($packId)?->price;
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

        return response()->json([
            'success' => true,
            'data' => [
                'line' => $this->formatLine($line),
                'lines' => $lines,
                'total' => round($total, 2),
            ],
        ]);
    }

    private function addLineSession(Request $request, array $validated): JsonResponse
    {
        $key = ($validated['product_id'] ?? null) ? 'p-' . $validated['product_id'] : 'k-' . $validated['pack_id'];
        $session = $request->session();
        $lines = $session->get(self::SESSION_CART_KEY, []);
        $current = $lines[$key] ?? [
            'quantity' => 0,
            'product_id' => $validated['product_id'] ?? null,
            'pack_id' => $validated['pack_id'] ?? null,
            'included' => true,
            'wants_installation' => false,
            'extra_keys_qty' => 0,
        ];
        $current['quantity'] = ($current['quantity'] ?? 0) + (int) $validated['quantity'];
        $current['included'] = $current['included'] ?? true;
        $current['wants_installation'] = (bool) ($current['wants_installation'] ?? false);
        $lines[$key] = $current;
        $session->put(self::SESSION_CART_KEY, $lines);

        return $this->showSessionCart($request);
    }

    public function updateLine(Request $request, string $line): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
            'included' => ['sometimes', 'boolean'],
            'is_installation_requested' => ['sometimes', 'boolean'],
            'extra_keys_qty' => ['sometimes', 'integer', 'min:0', 'max:99'],
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
                if (array_key_exists('is_installation_requested', $validated)) {
                    $updates['is_installation_requested'] = $validated['is_installation_requested'];
                    $installable = $orderLine->product?->is_installable || $orderLine->pack?->is_installable;
                    $installPrice = $orderLine->product?->is_installable
                        ? $orderLine->product->installation_price
                        : ($orderLine->pack?->is_installable ? $orderLine->pack->installation_price : null);
                    $updates['installation_price'] = $validated['is_installation_requested'] && $installable ? $installPrice : null;
                }
                if (array_key_exists('extra_keys_qty', $validated)) {
                    $updates['extra_keys_qty'] = $validated['extra_keys_qty'];
                    $updates['extra_key_unit_price'] = $orderLine->product?->is_extra_keys_available
                        ? $orderLine->product->extra_key_unit_price
                        : null;
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
            if (array_key_exists('is_installation_requested', $validated)) {
                $lines[$line]['wants_installation'] = $validated['is_installation_requested'];
            }
            if (array_key_exists('extra_keys_qty', $validated)) {
                $lines[$line]['extra_keys_qty'] = $validated['extra_keys_qty'];
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
        $request->session()->forget(self::SESSION_CART_KEY);

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
                if (isset($item['wants_installation'])) {
                    $updates['is_installation_requested'] = (bool) $item['wants_installation'];
                    $installable = $existing->product?->is_installable || $existing->pack?->is_installable;
                    $updates['installation_price'] = $updates['is_installation_requested'] && $installable
                        ? ($existing->product?->installation_price ?? $existing->pack?->installation_price)
                        : null;
                }
                $existing->update($updates);
            } else {
                $unitPrice = $productId ? Product::find($productId)?->price : Pack::find($packId)?->price;
                $product = $productId ? Product::find($productId) : null;
                $pack = $packId ? Pack::find($packId) : null;
                $extraKeysQty = (int) ($item['extra_keys_qty'] ?? 0);
                $extraKeyUnitPrice = $product?->is_extra_keys_available ? $product->extra_key_unit_price : null;
                $wantsInstallation = (bool) ($item['wants_installation'] ?? false);
                $installable = $product?->is_installable || $pack?->is_installable;
                $installationPrice = $wantsInstallation && $installable
                    ? ($product?->installation_price ?? $pack?->installation_price)
                    : null;
                $cart->lines()->create([
                    'product_id' => $productId,
                    'pack_id' => $packId,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'extra_keys_qty' => $extraKeysQty,
                    'extra_key_unit_price' => $extraKeyUnitPrice,
                    'is_installation_requested' => $wantsInstallation,
                    'installation_price' => $installationPrice,
                ]);
            }
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
            'is_installation_requested' => (bool) ($line->is_installation_requested ?? false),
        ];
    }
}
