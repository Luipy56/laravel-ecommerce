<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Deterministic catalog rows for manual search demos (typo, partial SKU, multi-token query).
 * Requires {@see ProductCategorySeeder} (category id 1 = cylinders).
 */
class SearchDemoProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $base = [
            'variant_group_id' => null,
            'category_id' => 1,
            'is_extra_keys_available' => false,
            'extra_key_unit_price' => null,
            'is_featured' => false,
            'is_trending' => false,
            'is_active' => true,
            'is_double_clutch' => false,
            'has_card' => false,
            'purchase_price' => null,
            'weight_kg' => null,
            'security_level' => 'standard',
            'competitor_url' => null,
            'discount_percent' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $rows = [
            [
                'code' => 'SEARCH-DEMO-TYPO',
                'name' => 'Cilindro demostración typo',
                'description' => 'Fixture for typo-tolerant search (e.g. cilimdro).',
                'price' => 9.99,
                'stock' => 5,
            ],
            [
                'code' => 'SEARCH-DEMO-K1-PARTIAL',
                'name' => 'Cilindro partial SKU demo',
                'description' => 'Code contains K1 for partial token tests.',
                'price' => 19.99,
                'stock' => 3,
            ],
            [
                'code' => 'SEARCH-DEMO-MIX-3030-K1',
                'name' => 'Cilindro 30x30 mm níquel mix query',
                'description' => 'Cilindre de seguretat de dimensions 30x30 mm amb acabat níquel. Compatible amb perfil K1.',
                'price' => 29.99,
                'stock' => 2,
            ],
        ];

        foreach ($rows as $row) {
            $merged = array_merge($base, $row);
            $merged['search_text'] = Product::normalizeSearchText(
                $merged['name'],
                $merged['code'],
                $merged['description'] ?? null
            );
            DB::table('products')->updateOrInsert(
                ['code' => $merged['code']],
                $merged
            );
        }
    }
}
