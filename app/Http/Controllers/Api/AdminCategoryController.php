<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ProductCategory::orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:product_categories,code'],
            'name' => ['required', 'string', 'max:255'],
        ]);
        $validated['is_active'] = true;
        $cat = ProductCategory::create($validated);
        return response()->json(['success' => true, 'data' => $cat], 201);
    }

    public function update(Request $request, ProductCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:product_categories,code,' . $category->id],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $category->update($validated);
        return response()->json(['success' => true, 'data' => $category->fresh()]);
    }

    public function destroy(ProductCategory $category): JsonResponse
    {
        $category->update(['is_active' => false]);
        return response()->json(['success' => true]);
    }
}
