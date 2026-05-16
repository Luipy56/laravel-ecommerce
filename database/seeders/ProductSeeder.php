<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Products from the supplier spreadsheet. Each product carries per-locale
     * name and description instead of duplicating the Spanish text across locales.
     *
     * Categories: cilindros · escudo · segundo-cerrojo
     */
    public function run(): void
    {
        $catIds = DB::table('product_categories')->pluck('id', 'code');
        $now = now();

        $base = [
            'variant_group_id' => null,
            'is_extra_keys_available' => false,
            'extra_key_unit_price' => null,
            'is_featured' => false,
            'is_trending' => false,
            'is_active' => true,
            'is_double_clutch' => false,
            'has_card' => false,
            'purchase_price' => null,
            'weight_kg' => null,
            'security_level' => null,
            'competitor_url' => null,
            'discount_percent' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // ── Securemme descriptions ────────────────────────────────────────────────
        $descSec = [
            'ca' => 'Sistema de seguretat amb xifratge de 6 pins actius, clau d\'obra; totes les claus són de llautó. Lleva DIN 30. Antibumping i antimanipulació.',
            'es' => 'Sistema de seguridad con cifrado de 6 pines activos, llave de obra; todas las llaves son de latón. Leva DIN 30. Antibumping y antimanipulación.',
            'en' => 'Security system with 6 active pin encoding, construction key included; all keys are brass. DIN 30 cam. Anti-bump and anti-manipulation.',
        ];
        $descSecDe = [
            'ca' => $descSec['ca'].' Inclou doble embragament.',
            'es' => $descSec['es'].' Incluye doble embrague.',
            'en' => $descSec['en'].' Includes double clutch.',
        ];

        // ── Cylinders ─────────────────────────────────────────────────────────────
        $cil = array_merge($base, [
            'category_id' => $catIds['cilindros'],
            'has_card' => true,
        ]);

        // [code, locales[ca|es|en][name|description], is_double_clutch, purchase_price, price, stock, security_level, is_trending, is_featured, extra_keys, extra_key_price, competitor_url]
        $cylinders = [
            [
                'code' => '192 evoK1C 3030 N',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 30×30 mm níquel Securemme K1', 'description' => $descSec['ca']],
                    'es' => ['name' => 'Cilindro 30×30 mm níquel Securemme K1', 'description' => $descSec['es']],
                    'en' => ['name' => 'Securemme K1 cylinder 30×30 mm nickel', 'description' => $descSec['en']],
                ],
                'is_double_clutch' => false, 'purchase_price' => 14.30, 'price' => 26.50,
                'stock' => 2, 'security_level' => 'standard', 'is_trending' => false, 'is_featured' => true,
            ],
            [
                'code' => '192 evoK1C 3030 L',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 30×30 mm llautó Securemme K1', 'description' => $descSec['ca']],
                    'es' => ['name' => 'Cilindro 30×30 mm latón Securemme K1', 'description' => $descSec['es']],
                    'en' => ['name' => 'Securemme K1 cylinder 30×30 mm brass', 'description' => $descSec['en']],
                ],
                'is_double_clutch' => false, 'purchase_price' => 14.30, 'price' => 26.50,
                'stock' => 2, 'security_level' => 'standard', 'is_trending' => false, 'is_featured' => false,
            ],
            [
                'code' => '192 evoK1D 3030 N',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 30×30 mm níquel Securemme K1 doble embragament', 'description' => $descSecDe['ca']],
                    'es' => ['name' => 'Cilindro 30×30 mm níquel Securemme K1 doble embrague', 'description' => $descSecDe['es']],
                    'en' => ['name' => 'Securemme K1 cylinder 30×30 mm nickel double clutch', 'description' => $descSecDe['en']],
                ],
                'is_double_clutch' => true, 'purchase_price' => 22.75, 'price' => 42.00,
                'stock' => 1, 'security_level' => 'standard', 'is_trending' => false, 'is_featured' => false,
            ],
            [
                'code' => '192 evoK1D 3030 L',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 30×30 mm llautó Securemme K1 doble embragament', 'description' => $descSecDe['ca']],
                    'es' => ['name' => 'Cilindro 30×30 mm latón Securemme K1 doble embrague', 'description' => $descSecDe['es']],
                    'en' => ['name' => 'Securemme K1 cylinder 30×30 mm brass double clutch', 'description' => $descSecDe['en']],
                ],
                'is_double_clutch' => true, 'purchase_price' => 22.75, 'price' => 42.00,
                'stock' => 1, 'security_level' => 'standard', 'is_trending' => false, 'is_featured' => false,
            ],
            [
                'code' => '192 evoK1C 3040 N',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 30×40 mm níquel Securemme K1', 'description' => $descSec['ca']],
                    'es' => ['name' => 'Cilindro 30×40 mm níquel Securemme K1', 'description' => $descSec['es']],
                    'en' => ['name' => 'Securemme K1 cylinder 30×40 mm nickel', 'description' => $descSec['en']],
                ],
                'is_double_clutch' => false, 'purchase_price' => 16.25, 'price' => 30.00,
                'stock' => 2, 'security_level' => 'standard', 'is_trending' => false, 'is_featured' => false,
            ],
            [
                'code' => '192 evoK1C 3040 L',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 30×40 mm llautó Securemme K1', 'description' => $descSec['ca']],
                    'es' => ['name' => 'Cilindro 30×40 mm latón Securemme K1', 'description' => $descSec['es']],
                    'en' => ['name' => 'Securemme K1 cylinder 30×40 mm brass', 'description' => $descSec['en']],
                ],
                'is_double_clutch' => false, 'purchase_price' => 16.25, 'price' => 30.00,
                'stock' => 2, 'security_level' => 'standard', 'is_trending' => false, 'is_featured' => false,
            ],
            [
                'code' => '192 evoK1D 3040 N',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 30×40 mm níquel Securemme K1 doble embragament', 'description' => $descSecDe['ca']],
                    'es' => ['name' => 'Cilindro 30×40 mm níquel Securemme K1 doble embrague', 'description' => $descSecDe['es']],
                    'en' => ['name' => 'Securemme K1 cylinder 30×40 mm nickel double clutch', 'description' => $descSecDe['en']],
                ],
                'is_double_clutch' => true, 'purchase_price' => 25.35, 'price' => 47.00,
                'stock' => 1, 'security_level' => 'standard', 'is_trending' => true, 'is_featured' => false,
            ],
            [
                'code' => '192 evoK1D 3040 L',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 30×40 mm llautó Securemme K1 doble embragament', 'description' => $descSecDe['ca']],
                    'es' => ['name' => 'Cilindro 30×40 mm latón Securemme K1 doble embrague', 'description' => $descSecDe['es']],
                    'en' => ['name' => 'Securemme K1 cylinder 30×40 mm brass double clutch', 'description' => $descSecDe['en']],
                ],
                'is_double_clutch' => true, 'purchase_price' => 25.35, 'price' => 47.00,
                'stock' => 1, 'security_level' => 'standard', 'is_trending' => false, 'is_featured' => false,
            ],
            [
                'code' => 'MC-MOVE-3232-N',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 32×32 mm níquel M&C', 'description' => null],
                    'es' => ['name' => 'Cilindro 32×32 mm níquel M&C', 'description' => null],
                    'en' => ['name' => 'M&C cylinder 32×32 mm nickel', 'description' => null],
                ],
                'is_double_clutch' => true, 'purchase_price' => null, 'price' => 85.00,
                'stock' => 2, 'security_level' => 'high', 'is_trending' => false, 'is_featured' => true,
                'has_card' => true,
                'competitor_url' => 'https://keylaseguridad.es/bombines-de-perfil-europeo/1622-200835-bombin-mc-move.html#/26-acabado-latonado/93-leva-larga/192-embrague-pomo_interior_estandar/193-medidas-32x32',
            ],
            [
                'code' => 'MC-MOVE-3232-L',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 32×32 mm llautó M&C', 'description' => null],
                    'es' => ['name' => 'Cilindro 32×32 mm latón M&C', 'description' => null],
                    'en' => ['name' => 'M&C cylinder 32×32 mm brass', 'description' => null],
                ],
                'is_double_clutch' => true, 'purchase_price' => null, 'price' => 85.00,
                'stock' => 2, 'security_level' => 'high', 'is_trending' => false, 'is_featured' => false,
                'has_card' => true,
            ],
            [
                'code' => 'KESO-O2-3030-N',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 30×30 mm níquel Keso Omega 2 MASTER', 'description' => null],
                    'es' => ['name' => 'Cilindro 30×30 mm níquel Keso Omega 2 MASTER', 'description' => null],
                    'en' => ['name' => 'Keso Omega 2 MASTER cylinder 30×30 mm nickel', 'description' => null],
                ],
                'is_double_clutch' => true, 'purchase_price' => null, 'price' => 181.74,
                'stock' => 1, 'security_level' => 'very_high', 'is_trending' => false, 'is_featured' => true,
                'is_extra_keys_available' => true, 'extra_key_unit_price' => 26.00, 'has_card' => true,
                'competitor_url' => 'https://www.seguridadbilma.com/catalogo/keso-cilindro-8000-omega-2-europeo-a-master-cromo-60-30-30-5-llaves-combi-extralarga/',
            ],
            [
                'code' => 'KESO-O2-3030-L',
                'locales' => [
                    'ca' => ['name' => 'Cilindre 30×30 mm llautó Keso Omega 2 MASTER', 'description' => null],
                    'es' => ['name' => 'Cilindro 30×30 mm latón Keso Omega 2 MASTER', 'description' => null],
                    'en' => ['name' => 'Keso Omega 2 MASTER cylinder 30×30 mm brass', 'description' => null],
                ],
                'is_double_clutch' => true, 'purchase_price' => null, 'price' => 181.74,
                'stock' => 1, 'security_level' => 'very_high', 'is_trending' => false, 'is_featured' => false,
                'is_extra_keys_available' => true, 'extra_key_unit_price' => 26.00, 'has_card' => true,
            ],
        ];

        // ── Shields ───────────────────────────────────────────────────────────────
        $esc = array_merge($base, ['category_id' => $catIds['escudo']]);

        $shields = [
            [
                'code' => 'ESC-ABUS-PLATA',
                'locales' => [
                    'ca' => ['name' => 'Escut Abus plata', 'description' => null],
                    'es' => ['name' => 'Escudo Abus plata', 'description' => null],
                    'en' => ['name' => 'Abus silver shield', 'description' => null],
                ],
                'price' => 52.00, 'stock' => 2, 'security_level' => 'standard',
            ],
            [
                'code' => 'ESC-ABUS-DORADO',
                'locales' => [
                    'ca' => ['name' => 'Escut Abus daurat', 'description' => null],
                    'es' => ['name' => 'Escudo Abus dorado', 'description' => null],
                    'en' => ['name' => 'Abus gold shield', 'description' => null],
                ],
                'price' => 52.00, 'stock' => 2, 'security_level' => 'standard',
            ],
            [
                'code' => 'ESC-DMC-PLATA',
                'locales' => [
                    'ca' => ['name' => 'Escut DMC plata', 'description' => null],
                    'es' => ['name' => 'Escudo DMC plata', 'description' => null],
                    'en' => ['name' => 'DMC silver shield', 'description' => null],
                ],
                'price' => 68.00, 'stock' => 2, 'security_level' => 'high',
            ],
            [
                'code' => 'ESC-DMC-DORADO',
                'locales' => [
                    'ca' => ['name' => 'Escut DMC daurat', 'description' => null],
                    'es' => ['name' => 'Escudo DMC dorado', 'description' => null],
                    'en' => ['name' => 'DMC gold shield', 'description' => null],
                ],
                'price' => 68.00, 'stock' => 3, 'security_level' => 'high',
            ],
            [
                'code' => 'ESC-DMC-BOX-PLATA',
                'locales' => [
                    'ca' => ['name' => 'Escut DMC Boxer plata', 'description' => null],
                    'es' => ['name' => 'Escudo DMC Boxer plata', 'description' => null],
                    'en' => ['name' => 'DMC Boxer silver shield', 'description' => null],
                ],
                'price' => 74.00, 'stock' => 2, 'security_level' => 'high',
            ],
            [
                'code' => 'ESC-DMC-BOX-DORADO',
                'locales' => [
                    'ca' => ['name' => 'Escut DMC Boxer daurat', 'description' => null],
                    'es' => ['name' => 'Escudo DMC Boxer dorado', 'description' => null],
                    'en' => ['name' => 'DMC Boxer gold shield', 'description' => null],
                ],
                'price' => 74.00, 'stock' => 2, 'security_level' => 'high',
            ],
            [
                'code' => 'ESC-DISEC-BD180-PLATA',
                'locales' => [
                    'ca' => ['name' => 'Escut Disec BD180 plata', 'description' => null],
                    'es' => ['name' => 'Escudo Disec BD180 plata', 'description' => null],
                    'en' => ['name' => 'Disec BD180 silver shield', 'description' => null],
                ],
                'price' => 79.00, 'stock' => 2, 'security_level' => 'high',
            ],
            [
                'code' => 'ESC-DISEC-BD180-DORADO',
                'locales' => [
                    'ca' => ['name' => 'Escut Disec BD180 daurat', 'description' => null],
                    'es' => ['name' => 'Escudo Disec BD180 dorado', 'description' => null],
                    'en' => ['name' => 'Disec BD180 gold shield', 'description' => null],
                ],
                'price' => 79.00, 'stock' => 2, 'security_level' => 'high',
            ],
            [
                'code' => 'ESC-DISEC-BD280-PLATA',
                'locales' => [
                    'ca' => ['name' => 'Escut Disec BD280 plata', 'description' => null],
                    'es' => ['name' => 'Escudo Disec BD280 plata', 'description' => null],
                    'en' => ['name' => 'Disec BD280 silver shield', 'description' => null],
                ],
                'price' => 113.50, 'purchase_price' => 60.97, 'stock' => 1, 'security_level' => 'very_high',
            ],
            [
                'code' => 'ESC-DISEC-BD280-DORADO',
                'locales' => [
                    'ca' => ['name' => 'Escut Disec BD280 daurat', 'description' => null],
                    'es' => ['name' => 'Escudo Disec BD280 dorado', 'description' => null],
                    'en' => ['name' => 'Disec BD280 gold shield', 'description' => null],
                ],
                'price' => 113.50, 'purchase_price' => 60.97, 'stock' => 1, 'security_level' => 'very_high',
            ],
            [
                'code' => 'ESC-DISEC-LG280-PLATA',
                'locales' => [
                    'ca' => ['name' => 'Escut Disec LG280 plata', 'description' => null],
                    'es' => ['name' => 'Escudo Disec LG280 plata', 'description' => null],
                    'en' => ['name' => 'Disec LG280 silver shield', 'description' => null],
                ],
                'price' => 130.44, 'purchase_price' => 70.07, 'stock' => 1, 'security_level' => 'very_high',
            ],
            [
                'code' => 'ESC-DISEC-LG280-DORADO',
                'locales' => [
                    'ca' => ['name' => 'Escut Disec LG280 daurat', 'description' => null],
                    'es' => ['name' => 'Escudo Disec LG280 dorado', 'description' => null],
                    'en' => ['name' => 'Disec LG280 gold shield', 'description' => null],
                ],
                'price' => 130.44, 'purchase_price' => 70.07, 'stock' => 1, 'security_level' => 'very_high',
            ],
            [
                'code' => 'ESC-DISEC-MG210-PLATA',
                'locales' => [
                    'ca' => ['name' => 'Escut Disec MG210 plata', 'description' => null],
                    'es' => ['name' => 'Escudo Disec MG210 plata', 'description' => null],
                    'en' => ['name' => 'Disec MG210 silver shield', 'description' => null],
                ],
                'price' => 199.53, 'purchase_price' => 107.19, 'stock' => 1, 'security_level' => 'very_high', 'has_card' => true,
            ],
            [
                'code' => 'ESC-DISEC-MG210-DORADO',
                'locales' => [
                    'ca' => ['name' => 'Escut Disec MG210 daurat', 'description' => null],
                    'es' => ['name' => 'Escudo Disec MG210 dorado', 'description' => null],
                    'en' => ['name' => 'Disec MG210 gold shield', 'description' => null],
                ],
                'price' => 199.53, 'purchase_price' => 107.19, 'stock' => 1, 'security_level' => 'very_high', 'has_card' => true,
            ],
            [
                'code' => 'ESC-DISEC-MRM29-PLATA',
                'locales' => [
                    'ca' => ['name' => 'Escut Disec MRM 29 plata', 'description' => null],
                    'es' => ['name' => 'Escudo Disec MRM 29 plata', 'description' => null],
                    'en' => ['name' => 'Disec MRM 29 silver shield', 'description' => null],
                ],
                'price' => 201.22, 'purchase_price' => 108.10, 'stock' => 1, 'security_level' => 'very_high', 'has_card' => true,
            ],
            [
                'code' => 'ESC-DISEC-MRM29-DORADO',
                'locales' => [
                    'ca' => ['name' => 'Escut Disec MRM 29 daurat', 'description' => null],
                    'es' => ['name' => 'Escudo Disec MRM 29 dorado', 'description' => null],
                    'en' => ['name' => 'Disec MRM 29 gold shield', 'description' => null],
                ],
                'price' => 201.22, 'purchase_price' => 108.10, 'stock' => 1, 'security_level' => 'very_high', 'has_card' => true,
            ],
        ];

        // ── Second deadbolts ──────────────────────────────────────────────────────
        $sp = array_merge($base, ['category_id' => $catIds['segundo-cerrojo']]);

        $deadbolts = [
            [
                'code' => 'SP-MC-EZC-OFR',
                'locales' => [
                    'ca' => ['name' => 'Oferta segon pestell M&C Ezcurra', 'description' => 'Pany Ezcurra amb cilindre M&C.'],
                    'es' => ['name' => 'Oferta segundo cerrojo M&C Ezcurra', 'description' => 'Cerrojo Ezcurra con cilindro M&C.'],
                    'en' => ['name' => 'M&C Ezcurra second deadbolt deal', 'description' => 'Ezcurra deadbolt with M&C cylinder.'],
                ],
                'price' => 150.00, 'purchase_price' => 95.00, 'stock' => 1,
                'security_level' => 'high', 'has_card' => true,
            ],
            [
                'code' => 'SP-MC-SAG',
                'locales' => [
                    'ca' => ['name' => 'Segon pestell SAG amb M&C', 'description' => 'Pany SAG amb cilindre M&C.'],
                    'es' => ['name' => 'Segundo cerrojo SAG con M&C', 'description' => 'Cerrojo SAG con cilindro M&C.'],
                    'en' => ['name' => 'SAG second deadbolt with M&C', 'description' => 'SAG deadbolt with M&C cylinder.'],
                ],
                'price' => 165.00, 'purchase_price' => null, 'stock' => 1,
                'security_level' => 'high', 'has_card' => true,
            ],
        ];

        // ── Insert all groups ─────────────────────────────────────────────────────
        $groups = [
            [$cil, $cylinders],
            [$esc, $shields],
            [$sp, $deadbolts],
        ];

        foreach ($groups as [$defaults, $items]) {
            foreach ($items as $item) {
                $locales = $item['locales'];
                $overrides = array_diff_key($item, ['locales' => true]);

                $row = array_merge($defaults, $overrides);
                $pid = DB::table('products')->insertGetId($row);

                foreach (['ca', 'es', 'en'] as $loc) {
                    $t = $locales[$loc];
                    $name = $t['name'] ?? null;
                    $desc = $t['description'] ?? null;
                    DB::table('product_translations')->insert([
                        'product_id' => $pid,
                        'locale' => $loc,
                        'name' => $name,
                        'description' => $desc,
                        'search_text' => Product::normalizeSearchText($name, $item['code'], $desc),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
