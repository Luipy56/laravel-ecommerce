<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariantGroup;
use Illuminate\Database\Seeder;

class ProductVariantGroupSeeder extends Seeder
{
    /**
     * Variant groups: siblings by tamaño/acabado según el listado (misma referencia, distinto acabado o familia).
     */
    public function run(): void
    {
        $groups = [
            ['name' => 'Securemme K1 30x30 mm (níquel / latón)', 'codes' => ['192 evoK1C 3030 N', '192 evoK1C 3030 L']],
            ['name' => 'Securemme K1 30x30 mm doble embrague (níquel / latón)', 'codes' => ['192 evoK1D 3030 N', '192 evoK1D 3030 L']],
            ['name' => 'Securemme K1 30x40 mm (níquel / latón)', 'codes' => ['192 evoK1C 3040 N', '192 evoK1C 3040 L']],
            ['name' => 'Securemme K1 30x40 mm doble embrague (níquel / latón)', 'codes' => ['192 evoK1D 3040 N', '192 evoK1D 3040 L']],
            ['name' => 'M&C Move 32x32 mm', 'codes' => ['MC-MOVE-3232-N', 'MC-MOVE-3232-L']],
            ['name' => 'Keso Omega 2 MASTER 30x30 mm', 'codes' => ['KESO-O2-3030-N', 'KESO-O2-3030-L']],
            ['name' => 'Escudo Abus', 'codes' => ['ESC-ABUS-PLATA', 'ESC-ABUS-DORADO']],
            ['name' => 'Escudo DMC', 'codes' => ['ESC-DMC-PLATA', 'ESC-DMC-DORADO']],
            ['name' => 'Escudo DMC Boxer', 'codes' => ['ESC-DMC-BOX-PLATA', 'ESC-DMC-BOX-DORADO']],
            ['name' => 'Escudo Disec BD180', 'codes' => ['ESC-DISEC-BD180-PLATA', 'ESC-DISEC-BD180-DORADO']],
            ['name' => 'Escudo Disec BD280', 'codes' => ['ESC-DISEC-BD280-PLATA', 'ESC-DISEC-BD280-DORADO']],
            ['name' => 'Escudo Disec LG280', 'codes' => ['ESC-DISEC-LG280-PLATA', 'ESC-DISEC-LG280-DORADO']],
            ['name' => 'Escudo Disec MG210', 'codes' => ['ESC-DISEC-MG210-PLATA', 'ESC-DISEC-MG210-DORADO']],
            ['name' => 'Escudo Disec MRM 29', 'codes' => ['ESC-DISEC-MRM29-PLATA', 'ESC-DISEC-MRM29-DORADO']],
        ];

        foreach ($groups as $g) {
            $group = ProductVariantGroup::create(['name' => $g['name']]);
            Product::whereIn('code', $g['codes'])->update(['variant_group_id' => $group->id]);
        }
    }
}
