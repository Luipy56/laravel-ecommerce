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
                'price' => 9.99,
                'stock' => 5,
                'locales' => [
                    'ca' => ['name' => 'Cilindre demostració typo', 'description' => 'Fixture for typo-tolerant search (e.g. cilimdro).'],
                    'es' => ['name' => 'Cilindro demostración typo', 'description' => 'Fixture for typo-tolerant search (e.g. cilimdro).'],
                    'en' => ['name' => 'Demo cylinder typo', 'description' => 'Fixture for typo-tolerant search (e.g. cilimdro).'],
                ],
            ],
            [
                'code' => 'SEARCH-DEMO-K1-PARTIAL',
                'price' => 19.99,
                'stock' => 3,
                'locales' => [
                    'ca' => ['name' => 'Cilindre partial SKU demo', 'description' => 'Code contains K1 for partial token tests.'],
                    'es' => ['name' => 'Cilindro partial SKU demo', 'description' => 'Code contains K1 for partial token tests.'],
                    'en' => ['name' => 'Partial SKU demo cylinder', 'description' => 'Code contains K1 for partial token tests.'],
                ],
            ],
            [
                'code' => 'SEARCH-DEMO-MIX-3030-K1',
                'price' => 29.99,
                'stock' => 2,
                'locales' => [
                    'ca' => [
                        'name' => 'Cilindre 30x30 mm níquel mix query',
                        'description' => 'Cilindre de seguretat 30x30 mm. Text únic català UNIQCA_DEMOCA_X7 per proves de cerca per locale.',
                    ],
                    'es' => [
                        'name' => 'Cilindro 30x30 mm níquel mix query',
                        'description' => 'Cilindro de seguridad de dimensiones 30x30 mm con acabado níquel. Compatible con perfil K1.',
                    ],
                    'en' => [
                        'name' => 'Cylinder 30x30 mm nickel mix query',
                        'description' => '30x30 mm security cylinder, nickel finish. K1 profile compatible.',
                    ],
                ],
            ],
        ];

        foreach ($rows as $row) {
            $locales = $row['locales'];
            unset($row['locales']);
            $merged = array_merge($base, $row);
            DB::table('products')->updateOrInsert(
                ['code' => $merged['code']],
                $merged
            );
            $pid = (int) DB::table('products')->where('code', $merged['code'])->value('id');
            if ($pid === 0) {
                continue;
            }
            foreach (['ca', 'es', 'en'] as $loc) {
                $nv = $locales[$loc];
                $nm = $nv['name'] ?? '';
                $dc = $nv['description'] ?? null;
                $st = Product::normalizeSearchText($nm, $merged['code'], is_string($dc) ? $dc : null);
                DB::table('product_translations')->updateOrInsert(
                    ['product_id' => $pid, 'locale' => $loc],
                    [
                        'name' => $nm,
                        'description' => is_string($dc) ? $dc : null,
                        'search_text' => $st,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
