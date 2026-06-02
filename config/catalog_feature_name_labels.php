<?php

/**
 * Default display labels per locale for known feature_names.code values.
 * Used by catalog:backfill-legacy-translations when translation rows are empty
 * but a stable code exists (mirrors Database\Seeders\FeatureNameSeeder).
 *
 * @var array<string, array<string, string>>
 */
return [
    'brand' => ['ca' => 'Marca', 'es' => 'Marca', 'en' => 'Brand'],
    'color' => ['ca' => 'Color', 'es' => 'Color', 'en' => 'Colour'],
    'key_type' => ['ca' => 'Tipus de clau', 'es' => 'Tipo de llave', 'en' => 'Key type'],
    'measure' => ['ca' => 'Mesura', 'es' => 'Medida', 'en' => 'Size'],
    'inner_measure' => ['ca' => 'Mesura interna', 'es' => 'Medida interna', 'en' => 'Inner size'],
    'outer_measure' => ['ca' => 'Mesura externa', 'es' => 'Medida externa', 'en' => 'Outer size'],
];
