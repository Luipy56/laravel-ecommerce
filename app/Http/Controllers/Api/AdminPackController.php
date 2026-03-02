<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPackResource;
use App\Models\Pack;
use App\Models\PackItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Pack::query()->withCount('packItems')->orderBy('name');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->trim() . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)->orWhere('description', 'like', $term);
            });
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        if ($request->has('is_trending')) {
            $query->where('is_trending', $request->boolean('is_trending'));
        }

        $packs = $query->get();

        $data = $packs->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'price' => (float) $p->price,
            'is_trending' => (bool) $p->is_trending,
            'is_active' => (bool) $p->is_active,
            'pack_items_count' => $p->pack_items_count ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_trending' => ['boolean'],
            'is_active' => ['boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $defaults = ['is_trending' => false, 'is_active' => true];
        $pack = Pack::create(array_merge($defaults, collect($validated)->except('product_ids')->all()));

        $this->syncPackItems($pack, $validated['product_ids'] ?? []);

        return response()->json([
            'success' => true,
            'data' => new AdminPackResource($pack->load('packItems.product')),
        ], 201);
    }

    public function show(Pack $pack): JsonResponse
    {
        $pack->load(['packItems.product', 'images']);

        return response()->json([
            'success' => true,
            'data' => new AdminPackResource($pack),
        ]);
    }

    public function update(Request $request, Pack $pack): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_trending' => ['boolean'],
            'is_active' => ['boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $pack->update(collect($validated)->except('product_ids')->all());
        $this->syncPackItems($pack, $validated['product_ids'] ?? []);

        return response()->json([
            'success' => true,
            'data' => new AdminPackResource($pack->fresh()->load('packItems.product')),
        ]);
    }

    public function destroy(Pack $pack): JsonResponse
    {
        $pack->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    private function syncPackItems(Pack $pack, array $productIds): void
    {
        $pack->packItems()->delete();
        foreach (array_unique($productIds) as $productId) {
            PackItem::create([
                'pack_id' => $pack->id,
                'product_id' => $productId,
                'is_active' => true,
            ]);
        }
    }
}
