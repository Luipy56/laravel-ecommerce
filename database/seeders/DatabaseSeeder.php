<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'pgsql') {
            $this->resetPostgresSequences();
        }

        $this->call([
            UserSeeder::class,
            ClientContactSeeder::class,
            AdminSeeder::class,
            FaqSeeder::class,
            ShopSettingSeeder::class,
            ProductCategorySeeder::class,
            FeatureNameSeeder::class,
            ProductSeeder::class,
            SearchDemoProductSeeder::class,
            ProductVariantGroupSeeder::class,
            FeatureSeeder::class,
            ProductFeatureSeeder::class,
            ProductImageSeeder::class,
            AddressSeeder::class,
            PackSeeder::class,
            PackImageSeeder::class,
            PackDetailSeeder::class,
            OrderSeeder::class,
            OrderAddressSeeder::class,
            OrderDetailSeeder::class,
            PaymentSeeder::class,
            ReturnRequestSeeder::class,
            HistoricalSalesSeeder::class,
            PersonalizedSolutionSeeder::class,
            PersonalizedSolutionAttachmentSeeder::class,
            ProductReviewSeeder::class,
        ]);
    }

    /**
     * Reset all PostgreSQL auto-increment sequences to 1.
     *
     * RefreshDatabase uses transactions that rollback data but not sequences,
     * so hardcoded FK IDs in seeders break on subsequent test runs.
     */
    private function resetPostgresSequences(): void
    {
        $sequences = DB::select(
            "SELECT c.relname FROM pg_class c WHERE c.relkind = 'S'"
        );

        foreach ($sequences as $seq) {
            DB::statement("ALTER SEQUENCE \"{$seq->relname}\" RESTART WITH 1");
        }
    }
}
