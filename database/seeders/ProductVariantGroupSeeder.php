<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariantGroup;
use Illuminate\Database\Seeder;

class ProductVariantGroupSeeder extends Seeder
{
    /**
     * Create variant groups and assign products (e.g. Cilindre 30mm lengths, Cilindre 40mm lengths).
     */
    public function run(): void
    {
        $cil30Lengths = [35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 90, 100];
        $cil40Lengths = [40, 50, 60, 70, 80];

        $groupCil30 = ProductVariantGroup::create();
        $groupCil40 = ProductVariantGroup::create();

        $codesCil30 = array_map(fn ($len) => 'CIL-30-' . $len, $cil30Lengths);
        $codesCil40 = array_map(fn ($len) => 'CIL-40-' . $len, $cil40Lengths);

        Product::whereIn('code', $codesCil30)->update(['variant_group_id' => $groupCil30->id]);
        Product::whereIn('code', $codesCil40)->update(['variant_group_id' => $groupCil40->id]);
    }
}
