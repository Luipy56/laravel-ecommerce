<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackResource;
use App\Models\Pack;
use Illuminate\Http\JsonResponse;

class PackController extends Controller
{
    public function index(): JsonResponse
    {
        $packs = Pack::query()->active()->with(['items.product', 'images'])->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => PackResource::collection($packs),
        ]);
    }

    public function show(Pack $pack): JsonResponse
    {
        if (! $pack->is_active) {
            abort(404);
        }
        $pack->load(['items.product', 'images']);

        return response()->json([
            'success' => true,
            'data' => new PackResource($pack),
        ]);
    }
}
