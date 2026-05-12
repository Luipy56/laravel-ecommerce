<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;

class ProductReviewSeeder extends Seeder
{
    /**
     * Seeds product_reviews. All reviews go live immediately (published).
     * One is hidden by admin to demonstrate moderation.
     *
     * Verified-purchase links follow real order_lines:
     *  - client 1 (maria.garcia) → order 1 (pending)   → cilStd
     *  - client 1 (maria.garcia) → order 2 (in_transit) → escEst
     *  - client 1 (maria.garcia) → order 3 (sent)       → cilStd + escEst (already covered above)
     *  - client 2 (joan.martinez) → order 5 (installation_confirmed) → spEst
     */
    public function run(): void
    {
        $now = now();

        $cilStd = Product::where('code', '192 evoK1C 3030 N')->first();
        $cilSeg = Product::where('code', '192 evoK1D 3040 N')->first();
        $escEst = Product::where('code', 'ESC-ABUS-PLATA')->first();
        $spEst = Product::where('code', 'SP-MC-EZC-OFR')->first();

        // Try to get a fallback product from any active product if codes not found
        $fallback = Product::where('is_active', true)->first();

        $reviews = [];

        // --- client 1, cilStd: approved, 5 stars ---
        if ($cilStd) {
            $reviews[] = [
                'product_id' => $cilStd->id,
                'client_id' => 1,
                'order_id' => 1,
                'rating' => 5,
                'comment' => 'Excel·lent producte. La qualitat és molt bona i va arribar en perfectes condicions. Molt recomanable.',
                'status' => ProductReview::STATUS_PUBLISHED,
                'admin_note' => null,
                'created_at' => $now->copy()->subDays(4),
                'updated_at' => $now->copy()->subDays(4),
            ];
        }

        // --- client 1, escEst: approved, 4 stars ---
        if ($escEst) {
            $reviews[] = [
                'product_id' => $escEst->id,
                'client_id' => 1,
                'order_id' => 2,
                'rating' => 4,
                'comment' => 'Bona qualitat preu. Fàcil de muntar. Li trec una estrella perquè el manual podria ser més detallat.',
                'status' => ProductReview::STATUS_PUBLISHED,
                'admin_note' => null,
                'created_at' => $now->copy()->subDays(6),
                'updated_at' => $now->copy()->subDays(6),
            ];
        }

        // --- client 2, spEst: approved, 5 stars ---
        if ($spEst) {
            $reviews[] = [
                'product_id' => $spEst->id,
                'client_id' => 2,
                'order_id' => 5,
                'rating' => 5,
                'comment' => 'Molt satisfet. Enviament ràpid i el producte és exactament el que buscava. Repetiré.',
                'status' => ProductReview::STATUS_PUBLISHED,
                'admin_note' => null,
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subDays(1),
            ];
        }

        // --- client 2, cilSeg: published (no verified order → order_id null) ---
        if ($cilSeg) {
            $reviews[] = [
                'product_id' => $cilSeg->id,
                'client_id' => 2,
                'order_id' => null,
                'rating' => 3,
                'comment' => 'El producte compleix, però el temps d\'espera va ser més llarg del previst.',
                'status' => ProductReview::STATUS_PUBLISHED,
                'admin_note' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ];
        }

        // --- client 3, cilStd: published, no comment ---
        if ($cilStd) {
            $reviews[] = [
                'product_id' => $cilStd->id,
                'client_id' => 3,
                'order_id' => null,
                'rating' => 4,
                'comment' => null,
                'status' => ProductReview::STATUS_PUBLISHED,
                'admin_note' => null,
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subDays(1),
            ];
        }

        // --- client 3, escEst: hidden by admin (spam/inappropriate) ---
        if ($escEst) {
            $reviews[] = [
                'product_id' => $escEst->id,
                'client_id' => 3,
                'order_id' => null,
                'rating' => 1,
                'comment' => 'Enllaç de spam eliminat per l\'admin.',
                'status' => ProductReview::STATUS_HIDDEN,
                'admin_note' => 'Ressenya amagada per contingut inapropiat.',
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(3),
            ];
        }

        // If no real products found, seed one generic review with the fallback product
        if (empty($reviews) && $fallback) {
            $reviews[] = [
                'product_id' => $fallback->id,
                'client_id' => 1,
                'order_id' => null,
                'rating' => 5,
                'comment' => 'Producte de qualitat. Molt recomanable.',
                'status' => ProductReview::STATUS_PUBLISHED,
                'admin_note' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($reviews as $data) {
            ProductReview::create($data);
        }
    }
}
