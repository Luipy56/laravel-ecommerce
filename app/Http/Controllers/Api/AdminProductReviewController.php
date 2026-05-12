<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminProductReviewResource;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));

        $query = ProductReview::query()
            ->with(['client.contacts', 'product'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', (int) $request->get('product_id'));
        }

        if ($request->filled('search')) {
            $term = $request->get('search');
            $query->where(function ($q) use ($term) {
                $q->where('comment', 'like', '%'.$term.'%')
                    ->orWhereHas('client', function ($cq) use ($term) {
                        $cq->where('login_email', 'like', '%'.$term.'%')
                            ->orWhereHas('contacts', function ($ccq) use ($term) {
                                $ccq->where('name', 'like', '%'.$term.'%')
                                    ->orWhere('surname', 'like', '%'.$term.'%');
                            });
                    });
            });
        }

        $reviews = $query->paginate($perPage);

        $hiddenCount = ProductReview::hidden()->count();

        return response()->json([
            'success' => true,
            'data' => AdminProductReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'hidden_count' => $hiddenCount,
            ],
        ]);
    }

    public function show(ProductReview $review): JsonResponse
    {
        $review->load(['client.contacts', 'product']);

        return response()->json([
            'success' => true,
            'data' => new AdminProductReviewResource($review),
        ]);
    }

    /**
     * Toggle visibility: published ↔ hidden.
     */
    public function toggleVisibility(ProductReview $review): JsonResponse
    {
        $newStatus = $review->status === ProductReview::STATUS_HIDDEN
            ? ProductReview::STATUS_PUBLISHED
            : ProductReview::STATUS_HIDDEN;

        $review->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'data' => new AdminProductReviewResource($review->fresh()->load(['client.contacts', 'product'])),
        ]);
    }

    public function destroy(ProductReview $review): JsonResponse
    {
        $review->delete();

        return response()->json(['success' => true]);
    }
}
