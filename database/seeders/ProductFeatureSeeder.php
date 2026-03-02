<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\FeatureName;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductFeatureSeeder extends Seeder
{
    /**
     * Map product code => list of [feature_name, feature_value].
     * Només productes de les categories Cilindres, Escut, Segon pany.
     * Cilindre length/diameter go in features (Mida, Longitud), not in product title.
     */
    private function getProductFeatureMap(): array
    {
        $map = [
            'CIL-30' => [['Mida', '30mm'], ['Orientació', 'Interior'], ['Orientació', 'Exterior']],
            'CIL-40' => [['Mida', '40mm']],
            'CIL-PERFIL' => [['Seguretat', 'Multipunt']],
            'CIL-SEG' => [['Seguretat', 'RC3']],
            'CIL-DOBLE' => [['Mida', 'Estàndard']],
            'ESC-EST' => [['Acabament', 'Pintat'], ['Color', 'Crom']],
            'ESC-SEG' => [['Seguretat', 'Blindat']],
            'ESC-DISS' => [['Color', 'Crom'], ['Color', 'Negre']],
            'SP-EST' => [['Material', 'Fusta'], ['Material', 'Acer']],
            'SP-SEG' => [['Seguretat', 'RC3']],
            'SP-EMBUTIR' => [['Tipus muntatge', 'Enfonsat']],
        ];

        foreach ([35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 90, 100] as $len) {
            $map['CIL-30-' . $len] = [['Mida', '30mm'], ['Longitud', $len . 'mm']];
        }
        foreach ([40, 50, 60, 70, 80] as $len) {
            $map['CIL-40-' . $len] = [['Mida', '40mm'], ['Longitud', $len . 'mm']];
        }

        foreach (['EST', 'SEG', 'DISS'] as $t) {
            foreach (['A', 'B', 'C', 'D', 'E'] as $v) {
                $map['ESC-' . $t . '-' . $v] = $map['ESC-' . $t];
            }
        }

        return $map;
    }

    public function run(): void
    {
        $map = $this->getProductFeatureMap();
        $featureNameCache = [];
        $featureCache = [];

        foreach ($map as $productCode => $pairs) {
            $product = Product::where('code', $productCode)->first();
            if (! $product) {
                continue;
            }

            $featureIds = [];
            foreach ($pairs as [$name, $value]) {
                if (! isset($featureNameCache[$name])) {
                    $featureNameCache[$name] = FeatureName::where('name', $name)->first();
                }
                $fn = $featureNameCache[$name];
                if (! $fn) {
                    continue;
                }
                $key = $fn->id . ':' . $value;
                if (! isset($featureCache[$key])) {
                    $featureCache[$key] = Feature::where('feature_name_id', $fn->id)->where('value', $value)->first();
                }
                $feature = $featureCache[$key];
                if ($feature && ! in_array($feature->id, $featureIds, true)) {
                    $featureIds[] = $feature->id;
                }
            }

            if (! empty($featureIds)) {
                $product->features()->sync($featureIds);
            }
        }
    }
}
