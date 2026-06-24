<?php

namespace Database\Seeders;

use App\Models\KeyColor;
use App\Support\CatalogTranslationSync;
use Illuminate\Database\Seeder;

class KeyColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            [
                'rgb_code' => '#C0C0C0',
                'sort_order' => 10,
                'translations' => [
                    'ca' => ['name' => 'Plata'],
                    'es' => ['name' => 'Plata'],
                    'en' => ['name' => 'Silver'],
                ],
            ],
            [
                'rgb_code' => '#FFD700',
                'sort_order' => 20,
                'translations' => [
                    'ca' => ['name' => 'Or'],
                    'es' => ['name' => 'Oro'],
                    'en' => ['name' => 'Gold'],
                ],
            ],
            [
                'rgb_code' => '#CD7F32',
                'sort_order' => 30,
                'translations' => [
                    'ca' => ['name' => 'Bronze'],
                    'es' => ['name' => 'Bronce'],
                    'en' => ['name' => 'Bronze'],
                ],
            ],
            [
                'rgb_code' => '#1A1A1A',
                'sort_order' => 40,
                'translations' => [
                    'ca' => ['name' => 'Negre'],
                    'es' => ['name' => 'Negro'],
                    'en' => ['name' => 'Black'],
                ],
            ],
        ];

        foreach ($colors as $entry) {
            $color = KeyColor::query()->firstOrCreate(
                ['rgb_code' => $entry['rgb_code']],
                ['sort_order' => $entry['sort_order'], 'is_active' => true],
            );
            CatalogTranslationSync::syncKeyColorTranslations($color, $entry['translations']);
        }
    }
}
