<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\FeatureName;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductFeatureSeeder extends Seeder
{
    /**
     * Map product code => list of [feature_name, feature_value] (Spanish).
     * Medidas como características; marca, color y tipo de llave según el listado.
     */
    private function getProductFeatureMap(): array
    {
        $pCop = 'Puntos copiables';
        $pNoCop = 'Puntos no copiables';
        $movil = 'Elemento móvil';
        $magn = 'Codificación magnética';

        return [
            '192 evoK1C 3030 N' => [['Marca', 'Securemme'], ['Color', 'Plata'], ['Tipo de llave', $pCop], ['Medida interna', '30mm'], ['Medida externa', '30mm']],
            '192 evoK1C 3030 L' => [['Marca', 'Securemme'], ['Color', 'Dorado'], ['Tipo de llave', $pCop], ['Medida interna', '30mm'], ['Medida externa', '30mm']],
            '192 evoK1D 3030 N' => [['Marca', 'Securemme'], ['Color', 'Plata'], ['Tipo de llave', $pCop], ['Medida interna', '30mm'], ['Medida externa', '30mm']],
            '192 evoK1D 3030 L' => [['Marca', 'Securemme'], ['Color', 'Dorado'], ['Tipo de llave', $pCop], ['Medida interna', '30mm'], ['Medida externa', '30mm']],
            '192 evoK1C 3040 N' => [['Marca', 'Securemme'], ['Color', 'Plata'], ['Tipo de llave', $pCop], ['Medida interna', '30mm'], ['Medida externa', '40mm']],
            '192 evoK1C 3040 L' => [['Marca', 'Securemme'], ['Color', 'Dorado'], ['Tipo de llave', $pCop], ['Medida interna', '30mm'], ['Medida externa', '40mm']],
            '192 evoK1D 3040 N' => [['Marca', 'Securemme'], ['Color', 'Plata'], ['Tipo de llave', $pCop], ['Medida interna', '30mm'], ['Medida externa', '40mm']],
            '192 evoK1D 3040 L' => [['Marca', 'Securemme'], ['Color', 'Dorado'], ['Tipo de llave', $pCop], ['Medida interna', '30mm'], ['Medida externa', '40mm']],
            'MC-MOVE-3232-N' => [['Marca', 'M&C'], ['Color', 'Plata'], ['Tipo de llave', $pNoCop], ['Medida interna', '32mm'], ['Medida externa', '32mm']],
            'MC-MOVE-3232-L' => [['Marca', 'M&C'], ['Color', 'Dorado'], ['Tipo de llave', $pNoCop], ['Medida interna', '32mm'], ['Medida externa', '32mm']],
            'KESO-O2-3030-N' => [['Marca', 'Keso'], ['Color', 'Plata'], ['Tipo de llave', $movil], ['Medida interna', '30mm'], ['Medida externa', '30mm']],
            'KESO-O2-3030-L' => [['Marca', 'Keso'], ['Color', 'Dorado'], ['Tipo de llave', $movil], ['Medida interna', '30mm'], ['Medida externa', '30mm']],
            'ESC-ABUS-PLATA' => [['Marca', 'Abus'], ['Color', 'Plata']],
            'ESC-ABUS-DORADO' => [['Marca', 'Abus'], ['Color', 'Dorado']],
            'ESC-DMC-PLATA' => [['Marca', 'DMC'], ['Color', 'Plata']],
            'ESC-DMC-DORADO' => [['Marca', 'DMC'], ['Color', 'Dorado']],
            'ESC-DMC-BOX-PLATA' => [['Marca', 'DMC'], ['Color', 'Plata']],
            'ESC-DMC-BOX-DORADO' => [['Marca', 'DMC'], ['Color', 'Dorado']],
            'ESC-DISEC-BD180-PLATA' => [['Marca', 'Disec'], ['Color', 'Plata']],
            'ESC-DISEC-BD180-DORADO' => [['Marca', 'Disec'], ['Color', 'Dorado']],
            'ESC-DISEC-BD280-PLATA' => [['Marca', 'Disec'], ['Color', 'Plata']],
            'ESC-DISEC-BD280-DORADO' => [['Marca', 'Disec'], ['Color', 'Dorado']],
            'ESC-DISEC-LG280-PLATA' => [['Marca', 'Disec'], ['Color', 'Plata']],
            'ESC-DISEC-LG280-DORADO' => [['Marca', 'Disec'], ['Color', 'Dorado']],
            'ESC-DISEC-MG210-PLATA' => [['Marca', 'Disec'], ['Color', 'Plata'], ['Tipo de llave', $magn]],
            'ESC-DISEC-MG210-DORADO' => [['Marca', 'Disec'], ['Color', 'Dorado'], ['Tipo de llave', $magn]],
            'ESC-DISEC-MRM29-PLATA' => [['Marca', 'Disec'], ['Color', 'Plata'], ['Tipo de llave', $magn]],
            'ESC-DISEC-MRM29-DORADO' => [['Marca', 'Disec'], ['Color', 'Dorado'], ['Tipo de llave', $magn]],
            'SP-MC-EZC-OFR' => [['Marca', 'M&C'], ['Color', 'Plata'], ['Tipo de llave', $pCop]],
            'SP-MC-SAG' => [['Marca', 'M&C'], ['Color', 'Plata'], ['Tipo de llave', $pCop]],
        ];
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
                $key = $fn->id.':'.$value;
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
