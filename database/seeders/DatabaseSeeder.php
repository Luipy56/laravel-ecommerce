<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ClientContactSeeder::class,
            AdminSeeder::class,
            ProductCategorySeeder::class,
            FeatureNameSeeder::class,
            ProductSeeder::class,
            FeatureSeeder::class,
            ProductFeatureSeeder::class,
            ProductImageSeeder::class,
            AddressSeeder::class,
            PackSeeder::class,
            PackImageSeeder::class,
            PackDetailSeeder::class,
            OrderSeeder::class,
            OrderAddressSeeder::class,
            PaymentSeeder::class,
            OrderDetailSeeder::class,
            PersonalizedSolutionSeeder::class,
            PersonalizedSolutionAttachmentSeeder::class,
        ]);
    }
}
