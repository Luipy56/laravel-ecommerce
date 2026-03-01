<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            // 1 Material
            ['feature_name_id' => 1, 'value' => 'Fusta', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Alumini', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'PVC', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Ferro', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Acer', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Fusta-massissa', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Laminat', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Alumini amb ruptura de pont tèrmic', 'is_active' => true],
            // 2 Color
            ['feature_name_id' => 2, 'value' => 'Blanc', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Maró', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Negre', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Gris', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Antracita', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Ral 7016', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Ral 9005', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Natura fusta', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Vernís', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Crom', 'is_active' => true],
            // 3 Mida
            ['feature_name_id' => 3, 'value' => '70cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '80cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '90cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '100cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '120cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '80x60cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '100x80cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '120x100cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '150x120cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '250cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '350cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '180x150cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '200x150cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => 'Estàndard', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => 'A mida', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '30mm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '40mm', 'is_active' => true],
            // 4 Acabament
            ['feature_name_id' => 4, 'value' => 'Lacat', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => 'Pintat', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => 'Laminat', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => 'Pulit', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => 'Texturitzat', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => 'Satinat', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => 'Vernissat', 'is_active' => true],
            // 5 Tipus obertura
            ['feature_name_id' => 5, 'value' => 'Batent', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => 'Corredissa', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => 'Oscilobatent', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => 'Projectant', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => 'Fixa', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => 'Enfonsar', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => 'Pivotant', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => 'Basculant', 'is_active' => true],
            // 6 Resistència foc
            ['feature_name_id' => 6, 'value' => 'RF-30', 'is_active' => true],
            ['feature_name_id' => 6, 'value' => 'RF-60', 'is_active' => true],
            ['feature_name_id' => 6, 'value' => 'RF-90', 'is_active' => true],
            ['feature_name_id' => 6, 'value' => 'RF-120', 'is_active' => true],
            ['feature_name_id' => 6, 'value' => 'No certificat', 'is_active' => true],
            // 7 Aïllament tèrmic
            ['feature_name_id' => 7, 'value' => 'Uw 1,4', 'is_active' => true],
            ['feature_name_id' => 7, 'value' => 'Uw 1,2', 'is_active' => true],
            ['feature_name_id' => 7, 'value' => 'Uw 1,0', 'is_active' => true],
            ['feature_name_id' => 7, 'value' => 'Uw 0,9', 'is_active' => true],
            ['feature_name_id' => 7, 'value' => 'Baix', 'is_active' => true],
            ['feature_name_id' => 7, 'value' => 'Mitjà', 'is_active' => true],
            ['feature_name_id' => 7, 'value' => 'Alt', 'is_active' => true],
            // 8 Aïllament acústic
            ['feature_name_id' => 8, 'value' => 'Rw 30 dB', 'is_active' => true],
            ['feature_name_id' => 8, 'value' => 'Rw 35 dB', 'is_active' => true],
            ['feature_name_id' => 8, 'value' => 'Rw 40 dB', 'is_active' => true],
            ['feature_name_id' => 8, 'value' => 'Rw 45 dB', 'is_active' => true],
            ['feature_name_id' => 8, 'value' => 'Sense certificar', 'is_active' => true],
            // 9 Seguretat
            ['feature_name_id' => 9, 'value' => 'RC2', 'is_active' => true],
            ['feature_name_id' => 9, 'value' => 'RC3', 'is_active' => true],
            ['feature_name_id' => 9, 'value' => 'RC4', 'is_active' => true],
            ['feature_name_id' => 9, 'value' => 'Blindat', 'is_active' => true],
            ['feature_name_id' => 9, 'value' => 'Multipunt', 'is_active' => true],
            ['feature_name_id' => 9, 'value' => 'Bàsic', 'is_active' => true],
            // 10 Normativa
            ['feature_name_id' => 10, 'value' => 'CE', 'is_active' => true],
            ['feature_name_id' => 10, 'value' => 'CTE', 'is_active' => true],
            ['feature_name_id' => 10, 'value' => 'DB-SUA', 'is_active' => true],
            ['feature_name_id' => 10, 'value' => 'Passivhaus', 'is_active' => true],
            ['feature_name_id' => 10, 'value' => 'Etiqueta A', 'is_active' => true],
            // 11 Orientació
            ['feature_name_id' => 11, 'value' => 'Interior', 'is_active' => true],
            ['feature_name_id' => 11, 'value' => 'Exterior', 'is_active' => true],
            ['feature_name_id' => 11, 'value' => 'Interior/Exterior', 'is_active' => true],
            // 12 Tipus muntatge
            ['feature_name_id' => 12, 'value' => 'A sobre', 'is_active' => true],
            ['feature_name_id' => 12, 'value' => 'Enfonsat', 'is_active' => true],
            ['feature_name_id' => 12, 'value' => 'Integrat', 'is_active' => true],
            ['feature_name_id' => 12, 'value' => 'Sense muntatge', 'is_active' => true],
            // 13 Vidre
            ['feature_name_id' => 13, 'value' => 'Clar 4+4', 'is_active' => true],
            ['feature_name_id' => 13, 'value' => 'Clar 6+6', 'is_active' => true],
            ['feature_name_id' => 13, 'value' => 'Climalit', 'is_active' => true],
            ['feature_name_id' => 13, 'value' => 'Baix emissiu', 'is_active' => true],
            ['feature_name_id' => 13, 'value' => 'Seguretat', 'is_active' => true],
            ['feature_name_id' => 13, 'value' => 'Sense vidre', 'is_active' => true],
            // 14 Marc
            ['feature_name_id' => 14, 'value' => 'Fusta', 'is_active' => true],
            ['feature_name_id' => 14, 'value' => 'Alumini', 'is_active' => true],
            ['feature_name_id' => 14, 'value' => 'PVC', 'is_active' => true],
            ['feature_name_id' => 14, 'value' => 'Mixt', 'is_active' => true],
            // 15 Tipus persiana
            ['feature_name_id' => 15, 'value' => 'Enrollable', 'is_active' => true],
            ['feature_name_id' => 15, 'value' => 'Persiana veneciana', 'is_active' => true],
            ['feature_name_id' => 15, 'value' => 'Persiana plisada', 'is_active' => true],
            ['feature_name_id' => 15, 'value' => 'Persiana romana', 'is_active' => true],
            ['feature_name_id' => 15, 'value' => 'Toldó', 'is_active' => true],
        ];

        DB::table('features')->insert($features);
    }
}
