<?php

namespace Database\Seeders;

use App\Support\PostgresSequenceHelper;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'pgsql') {
            PostgresSequenceHelper::restartForSeeding();
        }

        $this->call([
            UserSeeder::class,
            ClientContactSeeder::class,
            AdminSeeder::class,
            FaqSeeder::class,
            ShopSettingSeeder::class,
            ProductCategorySeeder::class,
            FeatureNameSeeder::class,
            KeyColorSeeder::class,
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

}
