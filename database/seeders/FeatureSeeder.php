<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureSeeder extends Seeder
{
    /**
     * Feature rows (per distinct value) with translations; values match Spanish catalog copy used by ProductFeatureSeeder.
     *
     * @param  array<string, string>  $valuesByLocale  keys ca|es|en
     */
    private function insertFeatureWithTranslations(string $featureNameCode, array $valuesByLocale, \Illuminate\Support\Carbon $now): void
    {
        $fnId = (int) DB::table('feature_names')->where('code', $featureNameCode)->value('id');
        if ($fnId === 0) {
            return;
        }
        $es = $valuesByLocale['es'] ?? '';
        $ca = $valuesByLocale['ca'] ?? $es;
        $en = $valuesByLocale['en'] ?? $es;
        $fid = DB::table('features')->insertGetId([
            'feature_name_id' => $fnId,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        foreach (['ca' => $ca, 'es' => $es, 'en' => $en] as $loc => $val) {
            DB::table('feature_translations')->insert([
                'feature_id' => $fid,
                'locale' => $loc,
                'value' => $val,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function run(): void
    {
        $now = now();
        $same = fn (string $v): array => ['es' => $v, 'ca' => $v, 'en' => $v];

        foreach (['Securemme', 'M&C', 'Keso', 'Abus', 'DMC', 'Disec'] as $brand) {
            $this->insertFeatureWithTranslations('brand', $same($brand), $now);
        }
        foreach (['Plata', 'Dorado'] as $color) {
            $this->insertFeatureWithTranslations('color', $same($color), $now);
        }
        foreach (['Puntos copiables', 'Puntos no copiables', 'Elemento móvil', 'Codificación magnética'] as $kt) {
            $this->insertFeatureWithTranslations('key_type', $same($kt), $now);
        }
        foreach (['30mm', '32mm', '40mm'] as $mm) {
            $this->insertFeatureWithTranslations('inner_measure', $same($mm), $now);
        }
        foreach (['30mm', '32mm', '40mm'] as $mm) {
            $this->insertFeatureWithTranslations('outer_measure', $same($mm), $now);
        }
    }
}
