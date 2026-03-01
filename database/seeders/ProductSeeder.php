<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Productes segons enunciat: només categories Cilindres (1), Escut (2), Segon pany (3).
     * Molts productes per provar la paginació.
     */
    public function run(): void
    {
        $base = [
            'is_installable' => false,
            'installation_price' => null,
            'is_extra_keys_available' => false,
            'extra_key_unit_price' => null,
            'is_featured' => false,
            'is_trending' => false,
            'is_active' => true,
        ];

        $products = [];

        // --- Cilindres (category_id 1) ---
        $cilBase = array_merge($base, ['category_id' => 1, 'is_extra_keys_available' => true]);
        $products[] = array_merge($cilBase, [
            'code' => 'CIL-30', 'name' => 'Cilindre 30mm', 'description' => 'Cilindre de seguretat 30 mm. Diverses longituds.', 'price' => 28.00, 'stock' => 80, 'extra_key_unit_price' => 8.00, 'is_featured' => true,
        ]);
        $products[] = array_merge($cilBase, [
            'code' => 'CIL-40', 'name' => 'Cilindre 40mm', 'description' => 'Cilindre 40 mm. Seguretat estàndard.', 'price' => 35.00, 'stock' => 60, 'extra_key_unit_price' => 10.00,
        ]);
        $products[] = array_merge($cilBase, [
            'code' => 'CIL-PERFIL', 'name' => 'Cilindre perfil europeu', 'description' => 'Cilindre perfil europeu. 5 pines.', 'price' => 42.00, 'stock' => 45, 'extra_key_unit_price' => 12.00, 'is_trending' => true,
        ]);
        $products[] = array_merge($cilBase, [
            'code' => 'CIL-SEG', 'name' => 'Cilindre alta seguretat', 'description' => 'Cilindre alta seguretat. Anti-bumping, anti-taladro.', 'price' => 95.00, 'stock' => 25, 'extra_key_unit_price' => 22.00, 'is_featured' => true,
        ]);
        $products[] = array_merge($cilBase, [
            'code' => 'CIL-DOBLE', 'name' => 'Cilindre doble', 'description' => 'Cilindre doble per portes de dos costats.', 'price' => 48.00, 'stock' => 35, 'extra_key_unit_price' => 12.00,
        ]);
        foreach ([35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 90, 100] as $len) {
            $products[] = array_merge($cilBase, [
                'code' => 'CIL-30-' . $len,
                'name' => "Cilindre 30mm longitud {$len}mm",
                'description' => "Cilindre seguretat 30mm, longitud {$len} mm.",
                'price' => round(22 + $len * 0.2, 2),
                'stock' => rand(30, 70),
                'extra_key_unit_price' => 8.00,
            ]);
        }
        foreach ([40, 50, 60, 70, 80] as $len) {
            $products[] = array_merge($cilBase, [
                'code' => 'CIL-40-' . $len,
                'name' => "Cilindre 40mm longitud {$len}mm",
                'description' => "Cilindre 40mm, longitud {$len} mm.",
                'price' => round(28 + $len * 0.25, 2),
                'stock' => rand(25, 55),
                'extra_key_unit_price' => 10.00,
            ]);
        }

        // --- Escut (category_id 2) ---
        $products[] = array_merge($base, [
            'category_id' => 2, 'code' => 'ESC-EST', 'name' => 'Escut estàndard', 'description' => 'Escut per cilindre estàndard. Diversos acabaments.', 'price' => 18.00, 'stock' => 100,
        ]);
        $products[] = array_merge($base, [
            'category_id' => 2, 'code' => 'ESC-SEG', 'name' => 'Escut seguretat', 'description' => 'Escut reforçat anti-arrencament.', 'price' => 35.00, 'stock' => 50,
        ]);
        $products[] = array_merge($base, [
            'category_id' => 2, 'code' => 'ESC-DISS', 'name' => 'Escut disseny', 'description' => 'Escut disseny. Crom, negre o daurat.', 'price' => 42.00, 'stock' => 30, 'is_trending' => true,
        ]);
        foreach (['EST', 'SEG', 'DISS'] as $t) {
            $p = $t === 'EST' ? 18 : ($t === 'SEG' ? 35 : 42);
            foreach (['A', 'B', 'C', 'D', 'E'] as $v) {
                $products[] = array_merge($base, [
                    'category_id' => 2,
                    'code' => 'ESC-' . $t . '-' . $v,
                    'name' => "Escut {$t} variant {$v}",
                    'description' => 'Escut cilindre.',
                    'price' => (float) $p,
                    'stock' => rand(25, 65),
                ]);
            }
        }

        // --- Segon pany (category_id 3) ---
        $spBase = array_merge($base, ['category_id' => 3, 'is_extra_keys_available' => true]);
        $products[] = array_merge($spBase, [
            'code' => 'SP-EST', 'name' => 'Segon pany estàndard', 'description' => 'Segon pany per porta. Fusta o metall.', 'price' => 45.00, 'stock' => 55, 'extra_key_unit_price' => 6.00,
        ]);
        $products[] = array_merge($spBase, [
            'code' => 'SP-SEG', 'name' => 'Segon pany seguretat', 'description' => 'Segon pany 5 pines. Alta seguretat.', 'price' => 78.00, 'stock' => 28, 'extra_key_unit_price' => 15.00,
        ]);
        $products[] = array_merge($spBase, [
            'code' => 'SP-EMBUTIR', 'name' => 'Segon pany embutir', 'description' => 'Segon pany embutir. Per portes de fusta.', 'price' => 62.00, 'stock' => 40, 'extra_key_unit_price' => 12.00,
        ]);
        foreach (['SP-EST-2', 'SP-EST-3', 'SP-SEG-2', 'SP-EMB-2', 'SP-EST-4', 'SP-SEG-3'] as $code) {
            $products[] = array_merge($spBase, [
                'code' => $code,
                'name' => 'Segon pany ' . $code,
                'description' => 'Segon pany per porta.',
                'price' => (float) rand(42, 85),
                'stock' => rand(20, 50),
                'extra_key_unit_price' => 10.00,
            ]);
        }
        foreach (range(1, 25) as $i) {
            $products[] = array_merge($spBase, [
                'code' => 'SP-VAR-' . $i,
                'name' => "Segon pany varietat {$i}",
                'description' => 'Segon pany per porta. Diversos models.',
                'price' => round(38 + $i * 1.5, 2),
                'stock' => rand(15, 45),
                'extra_key_unit_price' => 8.00,
            ]);
        }

        DB::table('products')->insert($products);
    }
}
