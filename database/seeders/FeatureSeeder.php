<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureSeeder extends Seeder
{
    /**
     * Insert a feature with per-locale translations.
     *
     * @param  array<string, string>  $valuesByLocale  keys: ca, es, en
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

        // Brand names are proper nouns — same in every locale
        foreach (['Securemme', 'M&C', 'Keso', 'Abus', 'DMC', 'Disec'] as $brand) {
            $this->insertFeatureWithTranslations('brand', ['ca' => $brand, 'es' => $brand, 'en' => $brand], $now);
        }

        // Colors
        $this->insertFeatureWithTranslations('color', ['ca' => 'Plata',  'es' => 'Plata',  'en' => 'Silver'], $now);
        $this->insertFeatureWithTranslations('color', ['ca' => 'Daurat', 'es' => 'Dorado', 'en' => 'Gold'],   $now);

        // Key types
        $this->insertFeatureWithTranslations('key_type', [
            'ca' => 'Punts copiables',
            'es' => 'Puntos copiables',
            'en' => 'Copyable pins',
        ], $now);
        $this->insertFeatureWithTranslations('key_type', [
            'ca' => 'Punts no copiables',
            'es' => 'Puntos no copiables',
            'en' => 'Non-copyable pins',
        ], $now);
        $this->insertFeatureWithTranslations('key_type', [
            'ca' => 'Element mòbil',
            'es' => 'Elemento móvil',
            'en' => 'Moving element',
        ], $now);
        $this->insertFeatureWithTranslations('key_type', [
            'ca' => 'Codificació magnètica',
            'es' => 'Codificación magnética',
            'en' => 'Magnetic coding',
        ], $now);

        // Measures — numeric, same in all locales
        foreach (['30mm', '32mm', '40mm'] as $mm) {
            $this->insertFeatureWithTranslations('inner_measure', ['ca' => $mm, 'es' => $mm, 'en' => $mm], $now);
            $this->insertFeatureWithTranslations('outer_measure', ['ca' => $mm, 'es' => $mm, 'en' => $mm], $now);
        }
    }
}
