<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KeyColor;
use Illuminate\Http\JsonResponse;

class KeyColorController extends Controller
{
    public function index(): JsonResponse
    {
        $colors = KeyColor::query()
            ->active()
            ->with('translations')
            ->orderBySort()
            ->get();

        $data = $colors->map(fn (KeyColor $c) => [
            'id' => $c->id,
            'rgb_code' => $c->rgb_code,
            'name' => $c->name,
        ])->values()->all();

        return response()->json(['success' => true, 'data' => $data]);
    }
}
