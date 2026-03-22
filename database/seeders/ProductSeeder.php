<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Products from the supplier spreadsheet (castellano), categories: 1 Cilindros, 2 Escudo, 3 Segundo cerrojo.
     * Escudos sin PVP en el listado: precio de venta demo hasta concretar.
     */
    public function run(): void
    {
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
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $dCil = self::securemmeDescription(false);
        $dCilDe = self::securemmeDescription(true);

        $products = [];

        $cil = array_merge($base, [
            'category_id' => 1,
            'is_extra_keys_available' => false,
            'extra_key_unit_price' => null,
            'has_card' => true,
        ]);

        $securemmeRows = [
            ['192 evoK1C 3030 N', 'Cilindro 30x30 mm níquel Securemme K1', $dCil, false, 14.30, 26.50, 2, 'standard', false, true],
            ['192 evoK1C 3030 L', 'Cilindro 30x30 mm latón Securemme K1', $dCil, false, 14.30, 26.50, 2, 'standard', false, false],
            ['192 evoK1D 3030 N', 'Cilindro 30x30 mm níquel Securemme K1 doble embrague', $dCilDe, true, 22.75, 42.00, 1, 'standard', false, false],
            ['192 evoK1D 3030 L', 'Cilindro 30x30 mm latón Securemme K1 doble embrague', $dCilDe, true, 22.75, 42.00, 1, 'standard', false, false],
            ['192 evoK1C 3040 N', 'Cilindro 30x40 mm níquel Securemme K1', $dCil, false, 16.25, 30.00, 2, 'standard', false, false],
            ['192 evoK1C 3040 L', 'Cilindro 30x40 mm latón Securemme K1', $dCil, false, 16.25, 30.00, 2, 'standard', false, false],
            ['192 evoK1D 3040 N', 'Cilindro 30x40 mm níquel Securemme K1 doble embrague', $dCilDe, true, 25.35, 47.00, 1, 'standard', true, false],
            ['192 evoK1D 3040 L', 'Cilindro 30x40 mm latón Securemme K1 doble embrague', $dCilDe, true, 25.35, 47.00, 1, 'standard', false, false],
        ];
        foreach ($securemmeRows as $row) {
            [$code, $name, $desc, $dbl, $pc, $pv, $stock, $sec, $trend, $feat] = $row;
            $products[] = array_merge($cil, [
                'code' => $code,
                'name' => $name,
                'description' => $desc,
                'is_double_clutch' => $dbl,
                'purchase_price' => $pc,
                'price' => $pv,
                'stock' => $stock,
                'security_level' => $sec,
                'is_trending' => $trend,
                'is_featured' => $feat,
            ]);
        }

        $products[] = array_merge($cil, [
            'code' => 'MC-MOVE-3232-N',
            'name' => 'Cilindro 32x32 mm níquel M&C',
            'description' => null,
            'is_double_clutch' => true,
            'purchase_price' => null,
            'price' => 85.00,
            'stock' => 2,
            'security_level' => 'high',
            'competitor_url' => 'https://keylaseguridad.es/bombines-de-perfil-europeo/1622-200835-bombin-mc-move.html#/26-acabado-latonado/93-leva-larga/192-embrague-pomo_interior_estandar/193-medidas-32x32',
            'is_featured' => true,
            'has_card' => true,
        ]);
        $products[] = array_merge($cil, [
            'code' => 'MC-MOVE-3232-L',
            'name' => 'Cilindro 32x32 mm latón M&C',
            'description' => null,
            'is_double_clutch' => true,
            'purchase_price' => null,
            'price' => 85.00,
            'stock' => 2,
            'security_level' => 'high',
            'has_card' => true,
        ]);

        $products[] = array_merge($cil, [
            'code' => 'KESO-O2-3030-N',
            'name' => 'Cilindro 30x30 mm níquel Keso Omega 2 MASTER',
            'description' => null,
            'is_double_clutch' => true,
            'purchase_price' => null,
            'price' => 181.74,
            'stock' => 1,
            'security_level' => 'very_high',
            'competitor_url' => 'https://www.seguridadbilma.com/catalogo/keso-cilindro-8000-omega-2-europeo-a-master-cromo-60-30-30-5-llaves-combi-extralarga/',
            'is_extra_keys_available' => true,
            'extra_key_unit_price' => 26.00,
            'is_featured' => true,
            'has_card' => true,
        ]);
        $products[] = array_merge($cil, [
            'code' => 'KESO-O2-3030-L',
            'name' => 'Cilindro 30x30 mm latón Keso Omega 2 MASTER',
            'description' => null,
            'is_double_clutch' => true,
            'purchase_price' => null,
            'price' => 181.74,
            'stock' => 1,
            'security_level' => 'very_high',
            'is_extra_keys_available' => true,
            'extra_key_unit_price' => 26.00,
            'has_card' => true,
        ]);

        $esc = array_merge($base, ['category_id' => 2, 'is_double_clutch' => false, 'has_card' => false]);

        // code, name, stock, security_level, price, purchase_price|null, has_card
        $escudos = [
            ['ESC-ABUS-PLATA', 'Escudo Abus plata', 2, 'standard', 52.00, null, false],
            ['ESC-ABUS-DORADO', 'Escudo Abus dorado', 2, 'standard', 52.00, null, false],
            ['ESC-DMC-PLATA', 'Escudo DMC plata', 2, 'high', 68.00, null, false],
            ['ESC-DMC-DORADO', 'Escudo DMC dorado', 3, 'high', 68.00, null, false],
            ['ESC-DMC-BOX-PLATA', 'Escudo DMC Boxer plata', 2, 'high', 74.00, null, false],
            ['ESC-DMC-BOX-DORADO', 'Escudo DMC Boxer dorado', 2, 'high', 74.00, null, false],
            ['ESC-DISEC-BD180-PLATA', 'Escudo Disec BD180 plata', 2, 'high', 79.00, null, false],
            ['ESC-DISEC-BD180-DORADO', 'Escudo Disec BD180 dorado', 2, 'high', 79.00, null, false],
            ['ESC-DISEC-BD280-PLATA', 'Escudo Disec BD280 plata', 1, 'very_high', 113.50, 60.97, false],
            ['ESC-DISEC-BD280-DORADO', 'Escudo Disec BD280 dorado', 1, 'very_high', 113.50, 60.97, false],
            ['ESC-DISEC-LG280-PLATA', 'Escudo Disec LG280 plata', 1, 'very_high', 130.44, 70.07, false],
            ['ESC-DISEC-LG280-DORADO', 'Escudo Disec LG280 dorado', 1, 'very_high', 130.44, 70.07, false],
            ['ESC-DISEC-MG210-PLATA', 'Escudo Disec MG210 plata', 1, 'very_high', 199.53, 107.19, true],
            ['ESC-DISEC-MG210-DORADO', 'Escudo Disec MG210 dorado', 1, 'very_high', 199.53, 107.19, true],
            ['ESC-DISEC-MRM29-PLATA', 'Escudo Disec MRM 29 plata', 1, 'very_high', 201.22, 108.10, true],
            ['ESC-DISEC-MRM29-DORADO', 'Escudo Disec MRM 29 dorado', 1, 'very_high', 201.22, 108.10, true],
        ];
        foreach ($escudos as $e) {
            [$code, $name, $stock, $sec, $price, $purchase, $card] = $e;
            $products[] = array_merge($esc, [
                'code' => $code,
                'name' => $name,
                'description' => null,
                'price' => $price,
                'purchase_price' => $purchase,
                'stock' => $stock,
                'security_level' => $sec,
                'has_card' => $card,
            ]);
        }

        $products[] = array_merge($base, [
            'category_id' => 3,
            'code' => 'SP-MC-EZC-OFR',
            'name' => 'Oferta segundo cerrojo M&C Ezcurra',
            'description' => 'Cerrojo Ezcurra con cilindro M&C.',
            'price' => 150.00,
            'purchase_price' => 95.00,
            'stock' => 1,
            'security_level' => 'high',
            'has_card' => true,
            'is_double_clutch' => false,
        ]);
        $products[] = array_merge($base, [
            'category_id' => 3,
            'code' => 'SP-MC-SAG',
            'name' => 'Segundo cerrojo SAG con M&C',
            'description' => 'Cerrojo Sag con cilindro M&C.',
            'price' => 165.00,
            'purchase_price' => null,
            'stock' => 1,
            'security_level' => 'high',
            'has_card' => true,
            'is_double_clutch' => false,
        ]);

        DB::table('products')->insert($products);
    }

    private static function securemmeDescription(bool $doubleClutch): string
    {
        $base = 'Sistema de seguridad con cifrado de 6 pines activos, llave de obra; todas las llaves son de latón. Leva DIN 30. Antibumping y antimanipulación.';

        return $doubleClutch ? $base.' Incluye doble embrague.' : $base;
    }
}
