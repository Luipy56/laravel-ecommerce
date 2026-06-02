<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\FeatureName;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductFeatureSeeder extends Seeder
{
    /**
     * Map product code => list of [feature_name_code, feature_value (Spanish, matches feature_translations.locale=es)].
     */
    private function getProductFeatureMap(): array
    {
        $pCop = 'Puntos copiables';
        $pNoCop = 'Puntos no copiables';
        $movil = 'Elemento móvil';
        $magn = 'Codificación magnética';

        return [
            '192 evoK1C 3030 N' => [['brand', 'Securemme'], ['color', 'Plata'], ['key_type', $pCop], ['inner_measure', '30mm'], ['outer_measure', '30mm']],
            '192 evoK1C 3030 L' => [['brand', 'Securemme'], ['color', 'Dorado'], ['key_type', $pCop], ['inner_measure', '30mm'], ['outer_measure', '30mm']],
            '192 evoK1D 3030 N' => [['brand', 'Securemme'], ['color', 'Plata'], ['key_type', $pCop], ['inner_measure', '30mm'], ['outer_measure', '30mm']],
            '192 evoK1D 3030 L' => [['brand', 'Securemme'], ['color', 'Dorado'], ['key_type', $pCop], ['inner_measure', '30mm'], ['outer_measure', '30mm']],
            '192 evoK1C 3040 N' => [['brand', 'Securemme'], ['color', 'Plata'], ['key_type', $pCop], ['inner_measure', '30mm'], ['outer_measure', '40mm']],
            '192 evoK1C 3040 L' => [['brand', 'Securemme'], ['color', 'Dorado'], ['key_type', $pCop], ['inner_measure', '30mm'], ['outer_measure', '40mm']],
            '192 evoK1D 3040 N' => [['brand', 'Securemme'], ['color', 'Plata'], ['key_type', $pCop], ['inner_measure', '30mm'], ['outer_measure', '40mm']],
            '192 evoK1D 3040 L' => [['brand', 'Securemme'], ['color', 'Dorado'], ['key_type', $pCop], ['inner_measure', '30mm'], ['outer_measure', '40mm']],
            'MC-MOVE-3232-N' => [['brand', 'M&C'], ['color', 'Plata'], ['key_type', $pNoCop], ['inner_measure', '32mm'], ['outer_measure', '32mm']],
            'MC-MOVE-3232-L' => [['brand', 'M&C'], ['color', 'Dorado'], ['key_type', $pNoCop], ['inner_measure', '32mm'], ['outer_measure', '32mm']],
            'KESO-O2-3030-N' => [['brand', 'Keso'], ['color', 'Plata'], ['key_type', $movil], ['inner_measure', '30mm'], ['outer_measure', '30mm']],
            'KESO-O2-3030-L' => [['brand', 'Keso'], ['color', 'Dorado'], ['key_type', $movil], ['inner_measure', '30mm'], ['outer_measure', '30mm']],
            'ESC-ABUS-PLATA' => [['brand', 'Abus'], ['color', 'Plata']],
            'ESC-ABUS-DORADO' => [['brand', 'Abus'], ['color', 'Dorado']],
            'ESC-DMC-PLATA' => [['brand', 'DMC'], ['color', 'Plata']],
            'ESC-DMC-DORADO' => [['brand', 'DMC'], ['color', 'Dorado']],
            'ESC-DMC-BOX-PLATA' => [['brand', 'DMC'], ['color', 'Plata']],
            'ESC-DMC-BOX-DORADO' => [['brand', 'DMC'], ['color', 'Dorado']],
            'ESC-DISEC-BD180-PLATA' => [['brand', 'Disec'], ['color', 'Plata']],
            'ESC-DISEC-BD180-DORADO' => [['brand', 'Disec'], ['color', 'Dorado']],
            'ESC-DISEC-BD280-PLATA' => [['brand', 'Disec'], ['color', 'Plata']],
            'ESC-DISEC-BD280-DORADO' => [['brand', 'Disec'], ['color', 'Dorado']],
            'ESC-DISEC-LG280-PLATA' => [['brand', 'Disec'], ['color', 'Plata']],
            'ESC-DISEC-LG280-DORADO' => [['brand', 'Disec'], ['color', 'Dorado']],
            'ESC-DISEC-MG210-PLATA' => [['brand', 'Disec'], ['color', 'Plata'], ['key_type', $magn]],
            'ESC-DISEC-MG210-DORADO' => [['brand', 'Disec'], ['color', 'Dorado'], ['key_type', $magn]],
            'ESC-DISEC-MRM29-PLATA' => [['brand', 'Disec'], ['color', 'Plata'], ['key_type', $magn]],
            'ESC-DISEC-MRM29-DORADO' => [['brand', 'Disec'], ['color', 'Dorado'], ['key_type', $magn]],
            'SP-MC-EZC-OFR' => [['brand', 'M&C'], ['color', 'Plata'], ['key_type', $pCop]],
            'SP-MC-SAG' => [['brand', 'M&C'], ['color', 'Plata'], ['key_type', $pCop]],
        ];
    }

    public function run(): void
    {
        $map = $this->getProductFeatureMap();
        $featureCache = [];

        foreach ($map as $productCode => $pairs) {
            $product = Product::where('code', $productCode)->first();
            if (! $product) {
                continue;
            }

            $featureIds = [];
            foreach ($pairs as [$nameCode, $valueEs]) {
                $cacheKey = $nameCode.':'.$valueEs;
                if (! isset($featureCache[$cacheKey])) {
                    $fnId = FeatureName::query()->where('code', $nameCode)->value('id');
                    if (! $fnId) {
                        continue;
                    }
                    $featureCache[$cacheKey] = Feature::query()
                        ->where('feature_name_id', $fnId)
                        ->whereHas('translations', fn ($q) => $q->where('locale', 'es')->where('value', $valueEs))
                        ->value('id');
                }
                $fid = $featureCache[$cacheKey];
                if ($fid && ! in_array((int) $fid, $featureIds, true)) {
                    $featureIds[] = (int) $fid;
                }
            }

            if (! empty($featureIds)) {
                $product->features()->sync($featureIds);
            }
        }
    }
}
