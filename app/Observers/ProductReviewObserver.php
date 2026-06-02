<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductReview;

/**
 * Keeps products.avg_rating and products.reviews_count in sync
 * whenever a review is created, updated, or deleted.
 */
class ProductReviewObserver
{
    public function saved(ProductReview $review): void
    {
        $this->recalculate($review->product_id);
    }

    public function deleted(ProductReview $review): void
    {
        $this->recalculate($review->product_id);
    }

    private function recalculate(int $productId): void
    {
        $agg = ProductReview::query()
            ->where('product_id', $productId)
            ->where('status', ProductReview::STATUS_PUBLISHED)
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_r')
            ->first();

        $count = (int) ($agg?->cnt ?? 0);
        $avg = $count > 0 ? round((float) ($agg?->avg_r ?? 0), 2) : null;

        Product::where('id', $productId)->update([
            'reviews_count' => $count,
            'avg_rating' => $avg,
        ]);
    }
}
