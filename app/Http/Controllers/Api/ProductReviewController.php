<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductReviewResource;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    /**
     * Paginated list of approved reviews for a product, plus aggregate stats.
     */
    public function index(Request $request, Product $product): JsonResponse
    {
        $perPage = max(1, min(20, (int) $request->get('per_page', 5)));

        $reviews = ProductReview::query()
            ->where('product_id', $product->id)
            ->approved()
            ->with(['client.contacts'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        // Distribution: count per star level (1-5)
        $distribution = ProductReview::query()
            ->where('product_id', $product->id)
            ->approved()
            ->selectRaw('rating, COUNT(*) as cnt')
            ->groupBy('rating')
            ->pluck('cnt', 'rating')
            ->all();

        $dist = [];
        for ($i = 1; $i <= 5; $i++) {
            $dist[$i] = (int) ($distribution[$i] ?? 0);
        }

        return response()->json([
            'success' => true,
            'data' => ProductReviewResource::collection($reviews),
            'aggregate' => [
                'avg_rating' => $product->avg_rating !== null ? (float) $product->avg_rating : null,
                'reviews_count' => (int) $product->reviews_count,
                'distribution' => $dist,
            ],
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Submit or update the authenticated client's review for a product.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        /** @var \App\Models\Client $client */
        $client = Auth::user();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $orderId = $this->findVerifiedOrderId($client->id, $product->id);

        $existing = ProductReview::query()
            ->where('client_id', $client->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->update([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'status' => ProductReview::STATUS_PENDING,
                'admin_note' => null,
                'order_id' => $orderId ?? $existing->order_id,
            ]);
            $review = $existing->fresh();
        } else {
            $review = ProductReview::create([
                'product_id' => $product->id,
                'client_id' => $client->id,
                'order_id' => $orderId,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'status' => ProductReview::STATUS_PENDING,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductReviewResource($review),
        ], $existing ? 200 : 201);
    }

    /**
     * Return the authenticated client's own review for a product (or null).
     */
    public function mine(Product $product): JsonResponse
    {
        /** @var \App\Models\Client $client */
        $client = Auth::user();

        $review = ProductReview::query()
            ->where('client_id', $client->id)
            ->where('product_id', $product->id)
            ->first();

        $canReview = $this->findVerifiedOrderId($client->id, $product->id) !== null;

        return response()->json([
            'success' => true,
            'data' => $review ? new ProductReviewResource($review) : null,
            'can_review' => $canReview,
        ]);
    }

    /**
     * Find an order ID that confirms the client purchased this product
     * (kind=order, payment completed, product in order_lines).
     */
    private function findVerifiedOrderId(int $clientId, int $productId): ?int
    {
        $confirmedStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_IN_TRANSIT,
            Order::STATUS_SENT,
            Order::STATUS_INSTALLATION_PENDING,
            Order::STATUS_INSTALLATION_CONFIRMED,
        ];

        $orderLine = OrderLine::query()
            ->where('product_id', $productId)
            ->whereHas('order', function ($q) use ($clientId, $confirmedStatuses) {
                $q->where('client_id', $clientId)
                    ->where('kind', Order::KIND_ORDER)
                    ->whereIn('status', $confirmedStatuses);
            })
            ->with('order')
            ->first();

        return $orderLine?->order_id;
    }
}
