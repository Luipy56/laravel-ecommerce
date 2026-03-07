<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;

class FeatureController extends Controller
{
    /**
     * List features (name + value) that are used by at least one active product.
     * For use in product list filters.
     */
    public function index(): JsonResponse
    {
        $features = Feature::query()
            ->active()
            ->with('featureName:id,name')
            ->whereHas('products', fn ($q) => $q->active())
            ->orderBy('feature_name_id')
            ->orderBy('value')
            ->get(['id', 'feature_name_id', 'value']);

        $data = $features->map(fn ($f) => [
            'id' => $f->id,
            'feature_name_id' => $f->feature_name_id,
            'feature_name' => $f->featureName?->name,
            'value' => $f->value,
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
