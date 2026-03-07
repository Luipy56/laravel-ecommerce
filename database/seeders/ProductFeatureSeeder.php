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
     * Names and values in Catalan (Color, Tipus de clau, Mida, Mida interna, Mida externa).
     */
    private function getProductFeatureMap(): array
    {
        $map = [
            'CIL-30' => [['Mida', '30mm'], ['Color', 'Negre'], ['Tipus de clau', 'General']],
            'CIL-40' => [['Mida', '40mm'], ['Color', 'Plata'], ['Tipus de clau', 'General']],
            'CIL-PERFIL' => [['Mida', '80x10mm'], ['Mida interna', '80mm'], ['Mida externa', '10mm'], ['Color', 'Plata'], ['Tipus de clau', 'Seguretat']],
            'CIL-SEG' => [['Mida', '40mm'], ['Color', 'Negre'], ['Tipus de clau', 'Alta seguretat']],
            'CIL-DOBLE' => [['Mida', '60mm'], ['Color', 'Daurat'], ['Tipus de clau', 'General']],
            'ESC-EST' => [['Color', 'Plata'], ['Tipus de clau', 'General']],
            'ESC-SEG' => [['Color', 'Negre'], ['Tipus de clau', 'Seguretat']],
            'ESC-DISS' => [['Color', 'Plata'], ['Color', 'Daurat'], ['Tipus de clau', 'Seguretat']],
            'SP-EST' => [['Color', 'Negre'], ['Tipus de clau', 'General']],
            'SP-SEG' => [['Color', 'Plata'], ['Tipus de clau', 'Seguretat']],
            'SP-EMBUTIR' => [['Color', 'Daurat'], ['Tipus de clau', 'Alta seguretat']],
        ];

        foreach ([35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 90, 100] as $len) {
            $map['CIL-30-' . $len] = [['Mida', $len . 'mm'], ['Color', 'Negre'], ['Tipus de clau', 'General']];
        }
        foreach ([40, 50, 60, 70, 80] as $len) {
            $map['CIL-40-' . $len] = [['Mida', $len . 'mm'], ['Color', 'Plata'], ['Tipus de clau', 'General']];
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
