<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackReviewResource;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Pack;
use App\Models\PackReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackReviewController extends Controller
{
    /**
     * Paginated list of published reviews for a pack, plus aggregate stats.
     */
    public function index(Request $request, Pack $pack): JsonResponse
    {
        $perPage = max(1, min(20, (int) $request->get('per_page', 5)));

        $reviews = PackReview::query()
            ->where('pack_id', $pack->id)
            ->published()
            ->with(['client.contacts'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $distribution = PackReview::query()
            ->where('pack_id', $pack->id)
            ->published()
            ->selectRaw('rating, COUNT(*) as cnt')
            ->groupBy('rating')
            ->pluck('cnt', 'rating')
            ->all();

        $dist = [];
        for ($i = 1; $i <= 5; $i++) {
            $dist[$i] = (int) ($distribution[$i] ?? 0);
        }

        $aggregate = PackReview::query()
            ->where('pack_id', $pack->id)
            ->published()
            ->selectRaw('COUNT(*) as reviews_count, AVG(rating) as avg_rating')
            ->first();

        return response()->json([
            'success' => true,
            'data' => PackReviewResource::collection($reviews),
            'aggregate' => [
                'avg_rating' => $aggregate?->avg_rating !== null ? round((float) $aggregate->avg_rating, 2) : null,
                'reviews_count' => (int) ($aggregate?->reviews_count ?? 0),
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
     * Submit or update the authenticated client's review for a pack.
     */
    public function store(Request $request, Pack $pack): JsonResponse
    {
        /** @var \App\Models\Client $client */
        $client = Auth::user();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $orderId = $this->findVerifiedOrderId($client->id, $pack->id);

        $existing = PackReview::query()
            ->where('client_id', $client->id)
            ->where('pack_id', $pack->id)
            ->first();

        if ($existing) {
            $existing->update([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'order_id' => $orderId ?? $existing->order_id,
            ]);
            $review = $existing->fresh();
        } else {
            $review = PackReview::create([
                'pack_id' => $pack->id,
                'client_id' => $client->id,
                'order_id' => $orderId,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'status' => PackReview::STATUS_PUBLISHED,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => new PackReviewResource($review),
        ], $existing ? 200 : 201);
    }

    /**
     * Return the authenticated client's own review for a pack (or null).
     */
    public function mine(Pack $pack): JsonResponse
    {
        /** @var \App\Models\Client $client */
        $client = Auth::user();

        $review = PackReview::query()
            ->where('client_id', $client->id)
            ->where('pack_id', $pack->id)
            ->first();

        $canReview = $this->findVerifiedOrderId($client->id, $pack->id) !== null;

        return response()->json([
            'success' => true,
            'data' => $review ? new PackReviewResource($review) : null,
            'can_review' => $canReview,
        ]);
    }

    /**
     * Find an order ID that confirms the client purchased this pack.
     */
    private function findVerifiedOrderId(int $clientId, int $packId): ?int
    {
        $confirmedStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_IN_TRANSIT,
            Order::STATUS_SENT,
            Order::STATUS_INSTALLATION_PENDING,
            Order::STATUS_INSTALLATION_CONFIRMED,
        ];

        $orderLine = OrderLine::query()
            ->where('pack_id', $packId)
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
